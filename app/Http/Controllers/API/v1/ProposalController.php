<?php

namespace App\Http\Controllers\API\v1;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProposalResource;
use App\Models\Proposal;
use App\Models\ProposalProduct;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProposalController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        if (Auth::user()->can('manage proposal')) {
            $query = Proposal::where('created_by', Auth::user()->creatorId());

            if ($request->has('customer_id')) {
                $query->where('customer_id', $request->customer_id);
            }
            if ($request->has('status')) {
                $query->where('status', $request->status);
            }

            $proposals = $query->with(['customer', 'category'])->get();
            return ProposalResource::collection($proposals);
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
        if (Auth::user()->can('create proposal')) {
            $validator = \Validator::make($request->all(), [
                'customer_id' => 'required|exists:customers,id',
                'issue_date' => 'required|date',
                'category_id' => 'required|exists:product_service_categories,id',
                'items' => 'required|array|min:1',
                'items.*.item' => 'required|exists:product_services,id',
                'items.*.quantity' => 'required|numeric|min:1',
                'items.*.price' => 'required|numeric|min:0',
                'items.*.discount' => 'nullable|numeric|min:0',
            ]);

            if ($validator->fails()) {
                return response()->json(['error' => $validator->errors()->first()], 400);
            }

            $proposal = new Proposal();
            $proposal->proposal_id = $this->proposalNumber();
            $proposal->customer_id = $request->customer_id;
            $proposal->status = 0; // Draft status
            $proposal->issue_date = $request->issue_date;
            $proposal->category_id = $request->category_id;
            $proposal->created_by = Auth::user()->creatorId();
            $proposal->save();

            foreach ($request->items as $item) {
                $proposalProduct = new ProposalProduct();
                $proposalProduct->proposal_id = $proposal->id;
                $proposalProduct->product_id = $item['item'];
                $proposalProduct->quantity = $item['quantity'];
                $proposalProduct->tax = $item['tax'] ?? null;
                $proposalProduct->discount = $item['discount'] ?? 0;
                $proposalProduct->price = $item['price'];
                $proposalProduct->description = $item['description'] ?? null;
                $proposalProduct->save();
            }

            return new ProposalResource($proposal->load('items'));
        }

        return response()->json(['error' => __('Permission denied.')], 403);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Proposal  $proposal
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Proposal $proposal)
    {
        if (Auth::user()->can('show proposal') && $proposal->created_by == Auth::user()->creatorId()) {
            return new ProposalResource($proposal->load(['items.product', 'customer', 'category']));
        }

        return response()->json(['error' => __('Permission denied.')], 403);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Proposal  $proposal
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, Proposal $proposal)
    {
        if (Auth::user()->can('edit proposal') && $proposal->created_by == Auth::user()->creatorId()) {
             $validator = \Validator::make($request->all(), [
                'customer_id' => 'required|exists:customers,id',
                'issue_date' => 'required|date',
                'category_id' => 'required|exists:product_service_categories,id',
                'items' => 'sometimes|array',
            ]);

            if ($validator->fails()) {
                return response()->json(['error' => $validator->errors()->first()], 400);
            }

            $proposal->customer_id = $request->customer_id;
            $proposal->issue_date = $request->issue_date;
            $proposal->category_id = $request->category_id;
            $proposal->save();

            // Note: For simplicity, this update does not handle line item updates.
            // A full implementation would require logic to add/update/delete line items.

            return new ProposalResource($proposal);
        }

        return response()->json(['error' => __('Permission denied.')], 403);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Proposal  $proposal
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Proposal $proposal)
    {
        if (Auth::user()->can('delete proposal') && $proposal->created_by == Auth::user()->creatorId()) {
            $proposal->items()->delete();
            $proposal->delete();

            return response()->json(null, 204);
        }

        return response()->json(['error' => __('Permission denied.')], 403);
    }

    private function proposalNumber()
    {
        $latest = Proposal::where('created_by', Auth::user()->creatorId())->latest('proposal_id')->first();
        return ($latest ? $latest->proposal_id : 0) + 1;
    }

    public function pdf($proposal_id)
    {
        $settings = \App\Models\Utility::settings();
        $proposal = Proposal::where('id', $proposal_id)->first();

        if (!$proposal) {
            return response()->json(['error' => 'Proposal not found.'], 404);
        }
        if (\Illuminate\Support\Facades\Gate::denies('show proposal', $proposal)) {
            return response()->json(['error' => 'Permission denied.'], 403);
        }

        $data = \Illuminate\Support\Facades\DB::table('settings');
        $data = $data->where('created_by', '=', $proposal->created_by);
        $data1 = $data->get();

        foreach ($data1 as $row) {
            $settings[$row->name] = $row->value;
        }

        $customer = $proposal->customer;
        $items = [];
        $totalTaxPrice = 0;
        $totalQuantity = 0;
        $totalRate = 0;
        $totalDiscount = 0;
        $taxesData = [];
        foreach ($proposal->items as $product) {
            $item = new \stdClass();
            $item->name = !empty($product->product) ? $product->product->name : '';
            $item->quantity = $product->quantity;
            $item->tax = $product->tax;
            $item->unit = !empty($product->product) ? $product->product->unit_id : '';
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
                    $taxPrice = \App\Models\Utility::taxRate($tax->rate, $item->price, $item->quantity, $item->discount);
                    $totalTaxPrice += $taxPrice;

                    $itemTax['name'] = $tax->name;
                    $itemTax['rate'] = $tax->rate . '%';
                    $itemTax['price'] = \App\Models\Utility::priceFormat($settings, $taxPrice);
                    $itemTax['tax_price'] = $taxPrice;
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

        $proposal->itemData = $items;
        $proposal->totalTaxPrice = $totalTaxPrice;
        $proposal->totalQuantity = $totalQuantity;
        $proposal->totalRate = $totalRate;
        $proposal->totalDiscount = $totalDiscount;
        $proposal->taxesData = $taxesData;
        $proposal->customField = \App\Models\CustomField::getData($proposal, 'proposal');
        $customFields = \App\Models\CustomField::where('created_by', '=', $proposal->created_by)->where('module', '=', 'proposal')->get();

        $logo = asset(\Illuminate\Support\Facades\Storage::url('uploads/logo/'));
        $settings_data = \App\Models\Utility::settingsById($proposal->created_by);
        $company_logo = $settings_data['company_logo_dark'] ?? \App\Models\Utility::getValByName('company_logo_dark');
        $proposal_logo = $settings_data['proposal_logo'];
        if (isset($proposal_logo) && !empty($proposal_logo)) {
            $img = \App\Models\Utility::get_file('proposal_logo/') . $proposal_logo;
        } else {
            $img = asset($logo . '/' . (isset($company_logo) && !empty($company_logo) ? $company_logo : 'logo-dark.png'));
        }

        $color = '#' . $settings['proposal_color'];
        $font_color = \App\Models\Utility::getFontColor($color);

        $html = view('proposal.templates.' . $settings['proposal_template'], compact('proposal', 'color', 'settings', 'customer', 'img', 'font_color', 'customFields'))->render();
        $pdf = \Spatie\Browsershot\Browsershot::html($html)->setChromeExecutablePath(config('browsershot.chrome_executable_path'))->margins(0, 0, 0, 0)->pdf();

        return response($pdf, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . \App\Models\Utility::proposalNumberFormat($settings, $proposal->proposal_id) . '.pdf"',
        ]);
    }
}
