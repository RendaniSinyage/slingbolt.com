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
        if(!$latest)
        {
            $setting = \App\Models\Utility::settings();
            return (isset($setting['quotation_starting_number']) ? $setting['quotation_starting_number'] : 1);
        }
        return $latest->quotation_id + 1;
    }
}
