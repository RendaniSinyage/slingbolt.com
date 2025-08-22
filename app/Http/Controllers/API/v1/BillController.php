<?php

namespace App\Http\Controllers\API\v1;

use App\Http\Controllers\Controller;
use App\Http\Resources\BillResource;
use App\Models\Bill;
use App\Models\BillProduct;
use App\Models\Utility;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BillController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        if (Auth::user()->can('manage bill')) {
            $query = Bill::where('created_by', Auth::user()->creatorId())->where('type', 'Bill');

            if ($request->has('vender_id')) {
                $query->where('vender_id', $request->vender_id);
            }
            if ($request->has('status')) {
                $query->where('status', $request->status);
            }

            $bills = $query->with(['vender', 'category'])->get();
            return BillResource::collection($bills);
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
        if (Auth::user()->can('create bill')) {
            $validator = \Validator::make($request->all(), [
                'vender_id' => 'required|exists:venders,id',
                'bill_date' => 'required|date',
                'due_date' => 'required|date|after_or_equal:bill_date',
                'category_id' => 'required|exists:product_service_categories,id',
                'items' => 'required|array|min:1',
                'items.*.item' => 'required|exists:product_services,id',
                'items.*.quantity' => 'required|numeric|min:1',
                'items.*.price' => 'required|numeric|min:0',
            ]);

            if ($validator->fails()) {
                return response()->json(['error' => $validator->errors()->first()], 400);
            }

            $bill = new Bill();
            $bill->bill_id = $this->billNumber();
            $bill->vender_id = $request->vender_id;
            $bill->bill_date = $request->bill_date;
            $bill->due_date = $request->due_date;
            $bill->category_id = $request->category_id;
            $bill->order_number = $request->order_number ?? 0;
            $bill->status = 0; // Draft
            $bill->type = 'Bill';
            $bill->created_by = Auth::user()->creatorId();
            $bill->save();

            foreach ($request->items as $item) {
                $billProduct = new BillProduct();
                $billProduct->bill_id = $bill->id;
                $billProduct->product_id = $item['item'];
                $billProduct->quantity = $item['quantity'];
                $billProduct->tax = $item['tax'] ?? null;
                $billProduct->discount = $item['discount'] ?? 0;
                $billProduct->price = $item['price'];
                $billProduct->description = $item['description'] ?? null;
                $billProduct->save();

                // Update inventory
                Utility::total_quantity('plus', $billProduct->quantity, $billProduct->product_id);

                // Add to stock report
                $description = $billProduct->quantity . ' ' . __('quantity purchase in bill') . ' ' . Auth::user()->billNumberFormat($bill->bill_id);
                Utility::addProductStock($billProduct->product_id, $billProduct->quantity, 'bill', $description, $bill->id);
            }

            return new BillResource($bill->load('items'));
        }

        return response()->json(['error' => __('Permission denied.')], 403);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Bill  $bill
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Bill $bill)
    {
        if (Auth::user()->can('show bill') && $bill->created_by == Auth::user()->creatorId()) {
            return new BillResource($bill->load(['items.product', 'vender', 'category', 'payments']));
        }

        return response()->json(['error' => __('Permission denied.')], 403);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Bill  $bill
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, Bill $bill)
    {
        if (Auth::user()->can('edit bill') && $bill->created_by == Auth::user()->creatorId()) {
            $validator = \Validator::make($request->all(), [
                'vender_id' => 'required|exists:venders,id',
                'bill_date' => 'required|date',
                'due_date' => 'required|date|after_or_equal:bill_date',
                'category_id' => 'required|exists:product_service_categories,id',
            ]);

            if ($validator->fails()) {
                return response()->json(['error' => $validator->errors()->first()], 400);
            }

            $bill->vender_id = $request->vender_id;
            $bill->bill_date = $request->bill_date;
            $bill->due_date = $request->due_date;
            $bill->category_id = $request->category_id;
            $bill->order_number = $request->order_number ?? 0;
            $bill->save();

            // Note: For simplicity, this update does not handle line item updates.

            return new BillResource($bill);
        }

        return response()->json(['error' => __('Permission denied.')], 403);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Bill  $bill
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Bill $bill)
    {
        if (Auth::user()->can('delete bill') && $bill->created_by == Auth::user()->creatorId()) {
            // Note: This is a simplified deletion. The web controller has complex logic
            // for deleting payments, transactions, and updating balances, which is
            // omitted here for brevity in this initial API.

            $bill->items()->delete();
            $bill->delete();

            return response()->json(null, 204);
        }

        return response()->json(['error' => __('Permission denied.')], 403);
    }

    private function billNumber()
    {
        $latest = Bill::where('created_by', Auth::user()->creatorId())->latest('bill_id')->first();
        return ($latest ? $latest->bill_id : 0) + 1;
    }

    public function createPayment(Request $request, Bill $bill)
    {
        if (Auth::user()->can('create payment bill')) {
            return response()->json(['error' => 'Permission denied.'], 403);
        }

        if ($bill->created_by != Auth::user()->creatorId()) {
            return response()->json(['error' => 'Permission denied.'], 403);
        }

        $validator = \Validator::make(
            $request->all(), [
                'amount' => 'required|numeric|min:0',
                'date' => 'required|date',
                'account_id' => 'required|exists:bank_accounts,id',
            ]
        );

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }

        $billPayment = new \App\Models\BillPayment();
        $billPayment->bill_id = $bill->id;
        $billPayment->date = $request->date;
        $billPayment->amount = $request->amount;
        $billPayment->account_id = $request->account_id;
        $billPayment->payment_method = $request->payment_method ?? 0;
        $billPayment->reference = $request->reference;
        $billPayment->description = $request->description;
        $billPayment->save();

        if($bill->getDue() == 0)
        {
            $bill->status = 4; // Paid
            $bill->save();
        }

        return new BillResource($bill->fresh()->load('payments'));
    }

    public function paymentDestroy(Bill $bill, \App\Models\BillPayment $payment)
    {
        if (Auth::user()->can('delete payment bill')) {
            return response()->json(['error' => 'Permission denied.'], 403);
        }

        if ($bill->created_by != Auth::user()->creatorId()) {
            return response()->json(['error' => 'Permission denied.'], 403);
        }

        $payment->delete();

        if($bill->getDue() > 0)
        {
            $bill->status = 3; // Partially Paid
            $bill->save();
        }

        return new BillResource($bill->fresh()->load('payments'));
    }

    public function duplicate(Bill $bill)
    {
        if (Auth::user()->can('create bill')) {
            return response()->json(['error' => 'Permission denied.'], 403);
        }

        if ($bill->created_by != Auth::user()->creatorId()) {
            return response()->json(['error' => 'Permission denied.'], 403);
        }

        $new_bill = $bill->replicate();
        $new_bill->bill_id = $this->billNumber();
        $new_bill->status = 0;
        $new_bill->save();

        foreach($bill->items as $item) {
            $new_item = $item->replicate();
            $new_item->bill_id = $new_bill->id;
            $new_item->save();
        }

        return new BillResource($new_bill->load('items'));
    }

    public function sent(Bill $bill)
    {
        if (Auth::user()->can('send bill')) {
            return response()->json(['error' => 'Permission denied.'], 403);
        }

        if ($bill->created_by != Auth::user()->creatorId()) {
            return response()->json(['error' => 'Permission denied.'], 403);
        }

        try {
            $bill->sendStatus();
        } catch (\Exception $e) {
            return response()->json(['error' => 'E-Mail has been not sent due to SMTP configuration.'], 500);
        }

        $bill->status = 1; // Sent
        $bill->save();

        return new BillResource($bill->fresh()->load('vender'));
    }

    public function resent(Bill $bill)
    {
        if (Auth::user()->can('send bill')) {
            return response()->json(['error' => 'Permission denied.'], 403);
        }

        if ($bill->created_by != Auth::user()->creatorId()) {
            return response()->json(['error' => 'Permission denied.'], 403);
        }

        try {
            $bill->sendBill();
        } catch (\Exception $e) {
            return response()->json(['error' => 'E-Mail has been not sent due to SMTP configuration.'], 500);
        }

        return response()->json(['message' => 'Bill successfully resent.']);
    }

    public function productDestroy(Request $request)
    {
        if (Auth::user()->can('delete bill')) {
            return response()->json(['error' => __('Permission denied.')], 403);
        }

        BillProduct::where('id', '=', $request->id)->delete();

        return response()->json(['message' => 'Bill product successfully deleted.']);
    }

    public function pdf($bill_id)
    {
        $settings = \App\Models\Utility::settings();
        $bill = Bill::where('id', $bill_id)->first();

        if (!$bill) {
            return response()->json(['error' => 'Bill not found.'], 404);
        }
        if (\Illuminate\Support\Facades\Gate::denies('show bill', $bill)) {
            return response()->json(['error' => 'Permission denied.'], 403);
        }

        $data = \Illuminate\Support\Facades\DB::table('settings');
        $data = $data->where('created_by', '=', $bill->created_by);
        $data1 = $data->get();

        foreach ($data1 as $row) {
            $settings[$row->name] = $row->value;
        }

        $vendor = $bill->vender;
        $totalTaxPrice = 0;
        $totalQuantity = 0;
        $totalRate = 0;
        $totalDiscount = 0;
        $taxesData = [];
        $items = [];

        foreach ($bill->items as $product) {
            $item = new \stdClass();
            $item->name = !empty($product->product) ? $product->product->name : '';
            $item->quantity = $product->quantity;
            $item->unit = !empty($product->product) ? $product->product->unit_id : '';
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

        $bill->itemData = $items;
        $bill->totalTaxPrice = $totalTaxPrice;
        $bill->totalQuantity = $totalQuantity;
        $bill->totalRate = $totalRate;
        $bill->totalDiscount = $totalDiscount;
        $bill->taxesData = $taxesData;
        $bill->customField = \App\Models\CustomField::getData($bill, 'bill');
        $customFields = \App\Models\CustomField::where('created_by', '=', $bill->created_by)->where('module', '=', 'bill')->get();

        $logo = asset(\Illuminate\Support\Facades\Storage::url('uploads/logo/'));
        $company_logo = \App\Models\Utility::getValByName('company_logo_dark');
        $settings_data = \App\Models\Utility::settingsById($bill->created_by);
        $bill_logo = $settings_data['bill_logo'];
        if (isset($bill_logo) && !empty($bill_logo)) {
            $img = \App\Models\Utility::get_file('bill_logo/') . $bill_logo;
        } else {
            $img = asset($logo . '/' . (isset($company_logo) && !empty($company_logo) ? $company_logo : 'logo-dark.png'));
        }

        $color = '#' . $settings['bill_color'];
        $font_color = \App\Models\Utility::getFontColor($color);

        $html = view('bill.templates.' . $settings['bill_template'], compact('bill', 'color', 'settings', 'vendor', 'img', 'font_color', 'customFields'))->render();
        $pdf = \Spatie\Browsershot\Browsershot::html($html)->setChromeExecutablePath(config('browsershot.chrome_executable_path'))->margins(0, 0, 0, 0)->pdf();

        return response($pdf, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . \App\Models\Utility::billNumberFormat($settings, $bill->bill_id) . '.pdf"',
        ]);
    }
}
