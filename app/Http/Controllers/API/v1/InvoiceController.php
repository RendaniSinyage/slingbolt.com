<?php

namespace App\Http\Controllers\API\v1;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\InvoiceProduct;
use Illuminate\Http\Request;
use App\Http\Resources\InvoiceResource;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Auth;

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

        $validator = \Validator::make(
            $request->all(), [
                'customer_id' => 'sometimes|required|exists:customers,id',
                'issue_date' => 'sometimes|required|date',
                'due_date' => 'sometimes|required|date',
                'category_id' => 'sometimes|required|exists:product_service_categories,id',
                'items' => 'sometimes|array',
            ]
        );

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $invoice->update($request->all());

        if ($request->has('items')) {
            InvoiceProduct::where('invoice_id', $invoice->id)->delete();
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
        }

        return (new InvoiceResource($invoice->load('items')))->additional(['message' => 'Invoice successfully updated.']);
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
            return 1;
        }

        return $latest->invoice_id + 1;
    }
}
