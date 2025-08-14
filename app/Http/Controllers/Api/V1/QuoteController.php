<?php

namespace App\Http\Controllers\API\v1;

use App\Http\Controllers\Controller;
use App\Models\Quote;
use App\Models\QuoteProduct;
use App\Models\Customer;
use App\Models\ProductServiceCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class QuoteController extends Controller
{
    private function getQuoteNumber()
    {
        $latest = Quote::latest()->first();
        return $latest ? $latest->quote_id + 1 : 1;
    }

    public function index(Request $request)
    {
        $user = $request->user();
        $quotes = Quote::where('created_by', $user->creatorId())->with(['customer', 'category'])->get();
        return response()->json($quotes);
    }

    public function show($id)
    {
        $user = request()->user();
        $quote = Quote::with(['items.product', 'customer', 'category'])
            ->where('created_by', $user->creatorId())
            ->find($id);

        if (!$quote) {
            return response()->json(['error' => 'Quote not found or you do not have permission.'], 404);
        }
        return response()->json($quote);
    }

    public function store(Request $request)
    {
        $user = $request->user();
        $ownerId = $user->ownerId();

        $validator = Validator::make($request->all(), [
            'customer_id' => 'required|exists:customers,id',
            'issue_date' => 'required|date',
            'category_id' => 'required|exists:product_service_categories,id',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:product_services,id',
            'items.*.quantity' => 'required|numeric|min:1',
            'items.*.price' => 'required|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()], 400);
        }

        $customer = Customer::where('created_by', $ownerId)->find($request->customer_id);
        if (!$customer) {
            return response()->json(['error' => 'Invalid customer.'], 403);
        }

        $quote = new Quote();
        $quote->quote_id = $this->getQuoteNumber();
        $quote->customer_id = $request->customer_id;
        $quote->issue_date = $request->issue_date;
        $quote->due_date = $request->due_date ?? $request->issue_date;
        $quote->category_id = $request->category_id;
        $quote->status = 0; // Draft
        $quote->created_by = $ownerId;
        $quote->save();

        foreach ($request->items as $itemData) {
            QuoteProduct::create([
                'quote_id' => $quote->id,
                'product_id' => $itemData['product_id'],
                'quantity' => $itemData['quantity'],
                'price' => $itemData['price'],
                'tax' => $itemData['tax'] ?? 0,
                'discount' => $itemData['discount'] ?? 0,
            ]);
        }

        return response()->json(Quote::with('items')->find($quote->id), 201);
    }

    public function update(Request $request, $id)
    {
        $user = $request->user();
        $ownerId = $user->ownerId();

        $quote = Quote::where('created_by', $ownerId)->find($id);
        if (!$quote) {
            return response()->json(['error' => 'Quote not found.'], 404);
        }

        $validator = Validator::make($request->all(), [
            'customer_id' => 'required|exists:customers,id',
            'issue_date' => 'required|date',
            'category_id' => 'required|exists:product_service_categories,id',
            'items' => 'required|array|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()], 400);
        }

        $quote->customer_id = $request->customer_id;
        $quote->issue_date = $request->issue_date;
        $quote->due_date = $request->due_date ?? $request->issue_date;
        $quote->category_id = $request->category_id;
        $quote->save();

        QuoteProduct::where('quote_id', $quote->id)->delete();
        foreach ($request->items as $itemData) {
             QuoteProduct::create([
                'quote_id' => $quote->id,
                'product_id' => $itemData['product_id'],
                'quantity' => $itemData['quantity'],
                'price' => $itemData['price'],
                'tax' => $itemData['tax'] ?? 0,
                'discount' => $itemData['discount'] ?? 0,
            ]);
        }

        return response()->json(Quote::with('items')->find($quote->id));
    }

    public function destroy($id)
    {
        $user = request()->user();
        $ownerId = $user->ownerId();

        $quote = Quote::where('created_by', $ownerId)->find($id);
        if (!$quote) {
            return response()->json(['error' => 'Quote not found.'], 404);
        }

        QuoteProduct::where('quote_id', $quote->id)->delete();
        $quote->delete();

        return response()->json(['success' => 'Quote successfully deleted.'], 200);
    }
}
