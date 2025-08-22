<?php

namespace App\Http\Controllers\API\v1;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\InvoiceProduct;
use Illuminate\Http\Request;
use App\Http\Resources\InvoiceResource;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class InvoiceController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        if (Gate::denies('manage invoice')) {
            return response()->json(['error' => 'Permission denied.'], 403);
        }

        $query = Invoice::where('created_by', '=', Auth::user()->creatorId());

        if ($request->has('customer_id')) {
            $query->where('customer_id', '=', $request->customer_id);
        }
        if ($request->has('status')) {
            $query->where('status', '=', $request->status);
        }

        $invoices = $query->get();

        return InvoiceResource::collection($invoices);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        if (Gate::denies('create invoice')) {
            return response()->json(['error' => 'Permission denied.'], 403);
        }

        $validator = \Validator::make(
            $request->all(), [
                'customer_id' => 'required|exists:customers,id',
                'issue_date' => 'required|date',
                'due_date' => 'required|date',
                'category_id' => 'required|exists:product_service_categories,id',
                'items' => 'required|array',
                'items.*.item' => 'required|exists:product_services,id',
                'items.*.quantity' => 'required|numeric|min:1',
                'items.*.price' => 'required|numeric|min:0',
            ]
        );

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $invoice = new Invoice();
        $invoice->invoice_id = $this->invoiceNumber();
        $invoice->customer_id = $request->customer_id;
        $invoice->status = 0;
        $invoice->issue_date = $request->issue_date;
        $invoice->due_date = $request->due_date;
        $invoice->category_id = $request->category_id;
        $invoice->ref_number = $request->ref_number;
        $invoice->created_by = Auth::user()->creatorId();
        $invoice->save();

        foreach ($request->items as $item) {
            $invoiceProduct = new InvoiceProduct();
            $invoiceProduct->invoice_id = $invoice->id;
            $invoiceProduct->product_id = $item['item'];
            $invoiceProduct->quantity = $item['quantity'];
            $invoiceProduct->tax = $item['tax'] ?? null;
            $invoiceProduct->discount = $item['discount'] ?? 0;
            $invoiceProduct->price = $item['price'];
            $invoiceProduct->description = $item['description'] ?? null;
            $invoiceProduct->save();
        }

        return (new InvoiceResource($invoice->load('items')))->additional(['message' => 'Invoice successfully created.']);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Invoice  $invoice
     * @return \Illuminate\Http\Response
     */
    public function show(Invoice $invoice)
    {
        if (Gate::denies('show invoice')) {
            return response()->json(['error' => 'Permission denied.'], 403);
        }

        if ($invoice->created_by != Auth::user()->creatorId()) {
            return response()->json(['error' => 'Permission denied.'], 403);
        }

        return new InvoiceResource($invoice->load('items.product', 'customer', 'payments'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Invoice  $invoice
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Invoice $invoice)
    {
        if (Gate::denies('edit invoice')) {
            return response()->json(['error' => 'Permission denied.'], 403);
        }

        if ($invoice->created_by != Auth::user()->creatorId()) {
            return response()->json(['error' => 'Permission denied.'], 403);
        }

        $validator = \Validator::make($request->all(), [
            'customer_id' => 'sometimes|required|exists:customers,id',
            'issue_date' => 'sometimes|required|date',
            'due_date' => 'sometimes|required|date',
            'category_id' => 'sometimes|required|exists:product_service_categories,id',
            'items' => 'sometimes|array',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $invoice->update($request->all());

        if ($request->has('items')) {
            $products = $request->items;
            $itemIds = collect($products)->pluck('id')->filter()->all();
            InvoiceProduct::where('invoice_id', $invoice->id)->whereNotIn('id', $itemIds)->delete();

            for ($i = 0; $i < count($products); $i++) {
                $invoiceProduct = InvoiceProduct::find($products[$i]['id'] ?? 0);
                if ($invoiceProduct == null) {
                    $invoiceProduct = new InvoiceProduct();
                    $invoiceProduct->invoice_id = $invoice->id;
                }

                $invoiceProduct->product_id = $products[$i]['item'];
                $invoiceProduct->quantity = $products[$i]['quantity'];
                $invoiceProduct->tax = $products[$i]['tax'] ?? null;
                $invoiceProduct->discount = $products[$i]['discount'] ?? 0;
                $invoiceProduct->price = $products[$i]['price'];
                $invoiceProduct->description = $products[$i]['description'] ?? null;
                $invoiceProduct->save();
            }
        }

        return (new InvoiceResource($invoice->fresh()->load('items')))->additional(['message' => 'Invoice successfully updated.']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Invoice  $invoice
     * @return \Illuminate\Http\Response
     */
    public function destroy(Invoice $invoice)
    {
        if (Gate::denies('delete invoice')) {
            return response()->json(['error' => 'Permission denied.'], 403);
        }

        if ($invoice->created_by != Auth::user()->creatorId()) {
            return response()->json(['error' => 'Permission denied.'], 403);
        }

        $invoice->delete();

        return response()->json(['message' => 'Invoice successfully deleted.']);
    }

    private function invoiceNumber()
    {
        $latest = Invoice::where('created_by', '=', Auth::user()->creatorId())->latest('invoice_id')->first();
        if (!$latest) {
            $setting = \App\Models\Utility::settings();
            return (isset($setting['invoice_starting_number']) ? $setting['invoice_starting_number'] : 1);
        }

        return $latest->invoice_id + 1;
    }

    public function createPayment(Request $request, Invoice $invoice)
    {
        if (Gate::denies('create payment invoice')) {
            return response()->json(['error' => 'Permission denied.'], 403);
        }

        if ($invoice->created_by != Auth::user()->creatorId()) {
            return response()->json(['error' => 'Permission denied.'], 403);
        }

        $validator = \Validator::make(
            $request->all(), [
                'amount' => 'required|numeric|min:0',
                'date' => 'required|date',
                'payment_id' => 'required',
            ]
        );

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $invoicePayment = new \App\Models\InvoicePayment();
        $invoicePayment->invoice_id = $invoice->id;
        $invoicePayment->date = $request->date;
        $invoicePayment->amount = $request->amount;
        $invoicePayment->payment_id = $request->payment_id;
        $invoicePayment->notes = $request->notes;
        $invoicePayment->save();

        if($invoice->getDue() == 0)
        {
            $invoice->status = 4; // Paid
            $invoice->save();
        }

        return (new InvoiceResource($invoice->fresh()->load('payments')))->additional(['message' => 'Payment successfully created.']);
    }

    public function paymentDestroy(Invoice $invoice, \App\Models\InvoicePayment $payment)
    {
        if (Gate::denies('delete payment invoice')) {
            return response()->json(['error' => 'Permission denied.'], 403);
        }

        if ($invoice->created_by != Auth::user()->creatorId()) {
            return response()->json(['error' => 'Permission denied.'], 403);
        }

        $payment->delete();

        if($invoice->getDue() > 0)
        {
            $invoice->status = 3; // Partially Paid
            $invoice->save();
        }

        return (new InvoiceResource($invoice->fresh()->load('payments')))->additional(['message' => 'Payment successfully deleted.']);
    }

    public function duplicate(Invoice $invoice)
    {
        if (Gate::denies('create invoice')) {
            return response()->json(['error' => 'Permission denied.'], 403);
        }

        if ($invoice->created_by != Auth::user()->creatorId()) {
            return response()->json(['error' => 'Permission denied.'], 403);
        }

        $new_invoice = $invoice->replicate();
        $new_invoice->invoice_id = $this->invoiceNumber();
        $new_invoice->status = 0;
        $new_invoice->save();

        foreach($invoice->items as $item) {
            $new_item = $item->replicate();
            $new_item->invoice_id = $new_invoice->id;
            $new_item->save();
        }

        return (new InvoiceResource($new_invoice->load('items')))->additional(['message' => 'Invoice successfully duplicated.']);
    }

    public function paymentReminder(Invoice $invoice)
    {
        if (Gate::denies('send invoice')) {
            return response()->json(['error' => 'Permission denied.'], 403);
        }

        if ($invoice->created_by != Auth::user()->creatorId()) {
            return response()->json(['error' => 'Permission denied.'], 403);
        }

        try {
            $invoice->sendPaymentReminder();
        } catch (\Exception $e) {
            return response()->json(['error' => 'E-Mail has been not sent due to SMTP configuration.'], 500);
        }

        return response()->json(['message' => 'Payment reminder successfully sent.']);
    }

    public function sent(Invoice $invoice)
    {
        if (Gate::denies('send invoice')) {
            return response()->json(['error' => 'Permission denied.'], 403);
        }

        if ($invoice->created_by != Auth::user()->creatorId()) {
            return response()->json(['error' => 'Permission denied.'], 403);
        }

        try {
            $invoice->sendStatus();
        } catch (\Exception $e) {
            return response()->json(['error' => 'E-Mail has been not sent due to SMTP configuration.'], 500);
        }

        $invoice->status = 1; // Sent
        $invoice->save();

        return (new InvoiceResource($invoice->fresh()->load('customer')))->additional(['message' => 'Invoice successfully sent.']);
    }

    public function resent(Invoice $invoice)
    {
        if (Gate::denies('send invoice')) {
            return response()->json(['error' => 'Permission denied.'], 403);
        }

        if ($invoice->created_by != Auth::user()->creatorId()) {
            return response()->json(['error' => 'Permission denied.'], 403);
        }

        try {
            $invoice->sendInvoice();
        } catch (\Exception $e) {
            return response()->json(['error' => 'E-Mail has been not sent due to SMTP configuration.'], 500);
        }

        return response()->json(['message' => 'Invoice successfully resent.']);
    }

    public function productDestroy(Request $request)
    {
        if (Gate::denies('delete invoice')) {
            return response()->json(['error' => 'Permission denied.'], 403);
        }

        InvoiceProduct::where('id', '=', $request->id)->delete();

        return response()->json(['message' => 'Invoice product successfully deleted.']);
    }

    public function pdf($invoice_id)
    {
        $settings = \App\Models\Utility::settings();

        $invoice = Invoice::where('id', $invoice_id)->first();

        if (!$invoice) {
            return response()->json(['error' => 'Invoice not found.'], 404);
        }
        if (Gate::denies('show invoice', $invoice)) {
            return response()->json(['error' => 'Permission denied.'], 403);
        }

        $data = \Illuminate\Support\Facades\DB::table('settings');
        $data = $data->where('created_by', '=', $invoice->created_by);
        $data1 = $data->get();

        foreach ($data1 as $row) {
            $settings[$row->name] = $row->value;
        }

        $customer = $invoice->customer;
        $items = [];
        $totalTaxPrice = 0;
        $totalQuantity = 0;
        $totalRate = 0;
        $totalDiscount = 0;
        $taxesData = [];
        foreach ($invoice->items as $product) {
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

        $invoice->itemData = $items;
        $invoice->totalTaxPrice = $totalTaxPrice;
        $invoice->totalQuantity = $totalQuantity;
        $invoice->totalRate = $totalRate;
        $invoice->totalDiscount = $totalDiscount;
        $invoice->taxesData = $taxesData;
        $invoice->customField = \App\Models\CustomField::getData($invoice, 'invoice');
        $customFields = \App\Models\CustomField::where('created_by', '=', $invoice->created_by)->where('module', '=', 'invoice')->get();

        $logo = asset(\Illuminate\Support\Facades\Storage::url('uploads/logo/'));
        $company_logo = \App\Models\Utility::getValByName('company_logo_dark');
        $settings_data = \App\Models\Utility::settingsById($invoice->created_by);
        $invoice_logo = $settings_data['invoice_logo'];
        if (isset($invoice_logo) && !empty($invoice_logo)) {
            $img = \App\Models\Utility::get_file('invoice_logo/') . $invoice_logo;
        } else {
            $img = asset($logo . '/' . (isset($company_logo) && !empty($company_logo) ? $company_logo : 'logo-dark.png'));
        }

        $color = '#' . $settings['invoice_color'];
        $font_color = \App\Models\Utility::getFontColor($color);

        $html = view('invoice.templates.' . $settings['invoice_template'], compact('invoice', 'color', 'settings', 'customer', 'img', 'font_color', 'customFields'))->render();
        $pdf = \Spatie\Browsershot\Browsershot::html($html)->setChromeExecutablePath(config('browsershot.chrome_executable_path'))->margins(0, 0, 0, 0)->pdf();

        return response($pdf, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . \App\Models\Utility::invoiceNumberFormat($settings,$invoice->invoice_id) . '.pdf"',
        ]);
    }
}
