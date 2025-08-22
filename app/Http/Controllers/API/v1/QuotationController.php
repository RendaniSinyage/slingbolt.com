<?php

namespace App\Http\Controllers\API\v1;

use App\Http\Controllers\Controller;
use App\Models\Quotation;
use App\Models\QuotationProduct;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class QuotationController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        if (Auth::user()->can('manage quotation')) {
            $query = Quotation::where('created_by', Auth::user()->creatorId());

            if ($request->has('customer_id')) {
                $query->where('customer_id', $request->customer_id);
            }
             if ($request->has('warehouse_id')) {
                $query->where('warehouse_id', $request->warehouse_id);
            }

            $quotations = $query->with(['customer', 'warehouse'])->get();
            return response()->json($quotations);
        }

        return response()->json(['error' => __('Permission denied.')], 403);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        if (Auth::user()->can('create quotation')) {
            $validator = \Validator::make($request->all(), [
                'customer_id' => 'required|exists:customers,id',
                'warehouse_id' => 'required|exists:warehouses,id',
                'quotation_date' => 'required|date',
                'items' => 'required|array|min:1',
                'items.*.item' => 'required|exists:product_services,id',
                'items.*.quantity' => 'required|numeric|min:1',
                'items.*.price' => 'required|numeric|min:0',
                'items.*.discount' => 'nullable|numeric|min:0',
            ]);

            if ($validator->fails()) {
                return response()->json(['error' => $validator->errors()->first()], 400);
            }

            $quotation = new Quotation();
            $quotation->quotation_id = $this->quotationNumber();
            $quotation->customer_id = $request->customer_id;
            $quotation->warehouse_id = $request->warehouse_id;
            $quotation->quotation_date = $request->quotation_date;
            $quotation->status = 0; // Draft status
            $quotation->category_id = 0; // Not used in web controller, setting default
            $quotation->created_by = Auth::user()->creatorId();
            $quotation->save();

            foreach ($request->items as $item) {
                $quotationProduct = new QuotationProduct();
                $quotationProduct->quotation_id = $quotation->id;
                $quotationProduct->product_id = $item['item'];
                $quotationProduct->quantity = $item['quantity'];
                $quotationProduct->tax = $item['tax'] ?? null;
                $quotationProduct->discount = $item['discount'] ?? 0;
                $quotationProduct->price = $item['price'];
                $quotationProduct->description = $item['description'] ?? null;
                $quotationProduct->save();
            }

            return response()->json($quotation->load('items'), 201);
        }

        return response()->json(['error' => __('Permission denied.')], 403);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Quotation  $quotation
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Quotation $quotation)
    {
        if (Auth::user()->can('show quotation') && $quotation->created_by == Auth::user()->creatorId()) {
            return response()->json($quotation->load(['items.product', 'customer', 'warehouse']));
        }

        return response()->json(['error' => __('Permission denied.')], 403);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Quotation  $quotation
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, Quotation $quotation)
    {
        if (Auth::user()->can('edit quotation') && $quotation->created_by == Auth::user()->creatorId()) {
            $validator = \Validator::make($request->all(), [
                'customer_id' => 'required|exists:customers,id',
                'warehouse_id' => 'required|exists:warehouses,id',
                'quotation_date' => 'required|date',
                'items' => 'sometimes|array',
            ]);

            if ($validator->fails()) {
                return response()->json(['error' => $validator->errors()->first()], 400);
            }

            $quotation->customer_id = $request->customer_id;
            $quotation->warehouse_id = $request->warehouse_id;
            $quotation->quotation_date = $request->quotation_date;
            $quotation->save();

            // Note: For simplicity, this update does not handle line item updates.

            return response()->json($quotation);
        }

        return response()->json(['error' => __('Permission denied.')], 403);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Quotation  $quotation
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Quotation $quotation)
    {
        if (Auth::user()->can('delete quotation') && $quotation->created_by == Auth::user()->creatorId()) {
            $quotation->items()->delete();
            $quotation->delete();

            return response()->json(null, 204);
        }

        return response()->json(['error' => __('Permission denied.')], 403);
    }

    private function quotationNumber()
    {
        $latest = Quotation::where('created_by', Auth::user()->creatorId())->latest('quotation_id')->first();
        return ($latest ? $latest->quotation_id : 0) + 1;
    }

    public function pdf($quotation_id)
    {
        $settings = \App\Models\Utility::settings();
        $quotation = Quotation::where('id', $quotation_id)->first();

        if (!$quotation) {
            return response()->json(['error' => 'Quotation not found.'], 404);
        }
        if (\Illuminate\Support\Facades\Gate::denies('show quotation', $quotation)) {
            return response()->json(['error' => 'Permission denied.'], 403);
        }

        $data = \Illuminate\Support\Facades\DB::table('settings');
        $data = $data->where('created_by', '=', $quotation->created_by);
        $data1 = $data->get();

        foreach ($data1 as $row) {
            $settings[$row->name] = $row->value;
        }

        $customer = $quotation->customer;
        $totalTaxPrice = 0;
        $totalQuantity = 0;
        $totalRate = 0;
        $totalDiscount = 0;
        $taxesData = [];
        $items = [];

        foreach ($quotation->items as $product) {
            $item = new \stdClass();
            $item->name = !empty($product->product()) ? $product->product()->name : '';
            $item->quantity = $product->quantity;
            $item->tax = $product->tax;
            $item->discount = $product->discount;
            $item->price = $product->price;
            $item->description = $product->description;
            $totalQuantity += $item->quantity;
            $totalRate += $item->price;
            $totalDiscount += $item->discount;
            $taxes = \App\Models\Utility::tax($product->tax);
            $itemTaxes = [];
            if (!empty($item->tax)) {
                foreach ($taxes as $tax) {
                    $taxPrice = \App\Models\Utility::taxRate($tax->rate, $item->price, $item->quantity);
                    $totalTaxPrice += $taxPrice;

                    $itemTax['name'] = $tax->name;
                    $itemTax['rate'] = $tax->rate . '%';
                    $itemTax['price'] = \App\Models\Utility::priceFormat($settings, $taxPrice);
                    $itemTaxes[] = $itemTax;

                    if (array_key_exists($tax->name, $taxesData)) {
                        $taxesData[$tax->name] = $taxesData[$tax->name] + $taxPrice;
                    } else {
                        $taxesData[$tax->name] = $taxPrice;
                    }
                }
                $item->itemTax = $itemTaxes;
            } else {
                $item->itemTax = [];
            }
            $items[] = $item;
        }

        $quotation->itemData = $items;
        $quotation->totalTaxPrice = $totalTaxPrice;
        $quotation->totalQuantity = $totalQuantity;
        $quotation->totalRate = $totalRate;
        $quotation->totalDiscount = $totalDiscount;
        $quotation->taxesData = $taxesData;

        $logo = asset(\Illuminate\Support\Facades\Storage::url('uploads/logo/'));
        $company_logo = \App\Models\Utility::getValByName('company_logo_dark');
        $quotation_logo = \App\Models\Utility::getValByName('quotation_logo');
        if (isset($quotation_logo) && !empty($quotation_logo)) {
            $img = \App\Models\Utility::get_file('quotation_logo/') . $quotation_logo;
        } else {
            $img = asset($logo . '/' . (isset($company_logo) && !empty($company_logo) ? $company_logo : 'logo-dark.png'));
        }

        $color = '#' . $settings['quotation_color'];
        $font_color = \App\Models\Utility::getFontColor($color);

        $html = view('quotation.templates.' . $settings['quotation_template'], compact('quotation', 'color', 'settings', 'customer', 'img', 'font_color'))->render();
        $pdf = \Spatie\Browsershot\Browsershot::html($html)->setChromeExecutablePath(config('browsershot.chrome_executable_path'))->margins(0, 0, 0, 0)->pdf();

        return response($pdf, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . \App\Models\Utility::quotationNumberFormat($settings, $quotation->quotation_id) . '.pdf"',
        ]);
    }
}
