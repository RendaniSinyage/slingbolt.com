<?php

namespace App\Http\Controllers\API\v1;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\InvoiceProduct;
use App\Models\Utility;
use App\Models\Customer;
use App\Models\ProductServiceCategory;

class InvoiceController extends Controller
{
    private function getInvoiceNumber()
    {
        $latest = Invoice::latest()->first();
        if (!$latest) {
            return 1;
        }
        return $latest->invoice_id + 1;
    }

    public function index(Request $request)
    {
        $user = $request->user();
        $invoices = Invoice::where('created_by', '=', $user->creatorId())->get();

        return response()->json($invoices);
    }

    public function show($id)
    {
        $user = request()->user();
        $invoice = Invoice::with(['items.product', 'payments.bankAccount', 'creditNote'])
            ->where('created_by', '=', $user->creatorId())
            ->find($id);

        if (!$invoice) {
            return response()->json(['error' => 'Invoice not found or you do not have permission to view it'], 404);
        }

        return response()->json($invoice);
    }

    public function store(Request $request)
    {
        $user = $request->user();
        $ownerId = $user->ownerId();

        $validator = Validator::make(
            $request->all(), [
                'customer_id' => 'required|exists:customers,id',
                'issue_date' => 'required|date',
                'due_date' => 'required|date',
                'category_id' => 'required|exists:product_service_categories,id',
                'items' => 'required|array|min:1',
                'items.*.product_id' => 'required|exists:product_services,id',
                'items.*.quantity' => 'required|numeric|min:1',
                'items.*.price' => 'required|numeric|min:0',
                'items.*.tax' => 'nullable|numeric',
                'items.*.discount' => 'nullable|numeric',
            ]
        );

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()], 400);
        }

        // Permission checks
        $customer = Customer::where('created_by', $ownerId)->find($request->customer_id);
        if (!$customer) {
            return response()->json(['error' => 'Invalid customer selected.'], 403);
        }
        $category = ProductServiceCategory::where('created_by', $ownerId)->find($request->category_id);
        if (!$category) {
            return response()->json(['error' => 'Invalid category selected.'], 403);
        }

        $invoice = new Invoice();
        $invoice->invoice_id = $this->getInvoiceNumber();
        $invoice->customer_id = $request->customer_id;
        $invoice->issue_date = $request->issue_date;
        $invoice->due_date = $request->due_date;
        $invoice->category_id = $request->category_id;
        $invoice->ref_number = $request->ref_number ?? null;
        $invoice->status = 0; // Draft
        $invoice->created_by = $ownerId;
        $invoice->save();

        foreach ($request->items as $itemData) {
            $invoiceProduct = new InvoiceProduct();
            $invoiceProduct->invoice_id = $invoice->id;
            $invoiceProduct->product_id = $itemData['product_id'];
            $invoiceProduct->quantity = $itemData['quantity'];
            $invoiceProduct->price = $itemData['price'];
            $invoiceProduct->tax = $itemData['tax'] ?? 0;
            $invoiceProduct->discount = $itemData['discount'] ?? 0;
            $invoiceProduct->save();
        }

        return response()->json(Invoice::with('items')->find($invoice->id), 201);
    }

    public function update(Request $request, $id)
    {
        $user = $request->user();
        $ownerId = $user->ownerId();

        $invoice = Invoice::where('created_by', $ownerId)->find($id);
        if (!$invoice) {
            return response()->json(['error' => 'Invoice not found.'], 404);
        }

        $validator = Validator::make(
            $request->all(), [
                'customer_id' => 'required|exists:customers,id',
                'issue_date' => 'required|date',
                'due_date' => 'required|date',
                'category_id' => 'required|exists:product_service_categories,id',
                'items' => 'required|array|min:1',
                'items.*.product_id' => 'required|exists:product_services,id',
                'items.*.quantity' => 'required|numeric|min:1',
                'items.*.price' => 'required|numeric|min:0',
            ]
        );

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()], 400);
        }

        // Permission checks
        $customer = Customer::where('created_by', $ownerId)->find($request->customer_id);
        if (!$customer) {
            return response()->json(['error' => 'Invalid customer selected.'], 403);
        }
        $category = ProductServiceCategory::where('created_by', $ownerId)->find($request->category_id);
        if (!$category) {
            return response()->json(['error' => 'Invalid category selected.'], 403);
        }

        $invoice->customer_id = $request->customer_id;
        $invoice->issue_date = $request->issue_date;
        $invoice->due_date = $request->due_date;
        $invoice->category_id = $request->category_id;
        $invoice->ref_number = $request->ref_number ?? null;
        $invoice->save();

        // Delete old items and add new ones
        InvoiceProduct::where('invoice_id', $invoice->id)->delete();
        foreach ($request->items as $itemData) {
            $invoiceProduct = new InvoiceProduct();
            $invoiceProduct->invoice_id = $invoice->id;
            $invoiceProduct->product_id = $itemData['product_id'];
            $invoiceProduct->quantity = $itemData['quantity'];
            $invoiceProduct->price = $itemData['price'];
            $invoiceProduct->tax = $itemData['tax'] ?? 0;
            $invoiceProduct->discount = $itemData['discount'] ?? 0;
            $invoiceProduct->save();
        }

        return response()->json(Invoice::with('items')->find($invoice->id));
    }

    public function destroy($id)
    {
        $user = request()->user();
        $ownerId = $user->ownerId();

        $invoice = Invoice::where('created_by', $ownerId)->find($id);
        if (!$invoice) {
            return response()->json(['error' => 'Invoice not found.'], 404);
        }

        // Delete associated items first
        InvoiceProduct::where('invoice_id', $invoice->id)->delete();
        // We should also delete payments, credit notes etc. if they exist
        // For now, just deleting items and the invoice itself.

        $invoice->delete();

        return response()->json(['success' => 'Invoice successfully deleted.'], 200);
    }
}
