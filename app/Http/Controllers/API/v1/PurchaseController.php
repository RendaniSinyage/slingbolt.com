<?php

namespace App\Http\Controllers\API\v1;

use App\Http\Controllers\Controller;
use App\Http\Resources\PurchaseResource;
use App\Models\Purchase;
use App\Models\PurchaseProduct;
use App\Models\Utility;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PurchaseController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        if (Auth::user()->can('manage purchase')) {
            $query = Purchase::where('created_by', Auth::user()->creatorId());

            if ($request->has('vender_id')) {
                $query->where('vender_id', $request->vender_id);
            }
            if ($request->has('status')) {
                $query->where('status', $request->status);
            }

            $purchases = $query->with(['vender', 'category'])->get();
            return PurchaseResource::collection($purchases);
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
        if (Auth::user()->can('create purchase')) {
            $validator = \Validator::make($request->all(), [
                'vender_id' => 'required|exists:venders,id',
                'purchase_date' => 'required|date',
                'category_id' => 'required|exists:product_service_categories,id',
                'items' => 'required|array|min:1',
                'items.*.item' => 'required|exists:product_services,id',
                'items.*.quantity' => 'required|numeric|min:1',
                'items.*.price' => 'required|numeric|min:0',
            ]);

            if ($validator->fails()) {
                return response()->json(['error' => $validator->errors()->first()], 400);
            }

            $purchase = new Purchase();
            $purchase->purchase_id = $this->purchaseNumber();
            $purchase->vender_id = $request->vender_id;
            $purchase->purchase_date = $request->purchase_date;
            $purchase->category_id = $request->category_id;
            $purchase->status = 0; // Draft
            $purchase->created_by = Auth::user()->creatorId();
            $purchase->save();

            foreach ($request->items as $item) {
                $purchaseProduct = new PurchaseProduct();
                $purchaseProduct->purchase_id = $purchase->id;
                $purchaseProduct->product_id = $item['item'];
                $purchaseProduct->quantity = $item['quantity'];
                $purchaseProduct->tax = $item['tax'] ?? null;
                $purchaseProduct->discount = $item['discount'] ?? 0;
                $purchaseProduct->price = $item['price'];
                $purchaseProduct->description = $item['description'] ?? null;
                $purchaseProduct->save();

                // Update inventory
                Utility::total_quantity('plus', $purchaseProduct->quantity, $purchaseProduct->product_id);

                // Add to stock report
                $description = $purchaseProduct->quantity . ' ' . __('quantity purchase in purchase') . ' ' . Auth::user()->purchaseNumberFormat($purchase->purchase_id);
                Utility::addProductStock($purchaseProduct->product_id, $purchaseProduct->quantity, 'purchase', $description, $purchase->id);
            }

            return new PurchaseResource($purchase->load('items'));
        }

        return response()->json(['error' => __('Permission denied.')], 403);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Purchase  $purchase
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Purchase $purchase)
    {
        if (Auth::user()->can('show purchase') && $purchase->created_by == Auth::user()->creatorId()) {
            return new PurchaseResource($purchase->load(['items.product', 'vender', 'category', 'payments']));
        }

        return response()->json(['error' => __('Permission denied.')], 403);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Purchase  $purchase
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, Purchase $purchase)
    {
        if (Auth::user()->can('edit purchase') && $purchase->created_by == Auth::user()->creatorId()) {
            $validator = \Validator::make($request->all(), [
                'vender_id' => 'required|exists:venders,id',
                'purchase_date' => 'required|date',
                'category_id' => 'required|exists:product_service_categories,id',
            ]);

            if ($validator->fails()) {
                return response()->json(['error' => $validator->errors()->first()], 400);
            }

            $purchase->vender_id = $request->vender_id;
            $purchase->purchase_date = $request->purchase_date;
            $purchase->category_id = $request->category_id;
            $purchase->save();

            // Note: For simplicity, this update does not handle line item updates.

            return new PurchaseResource($purchase);
        }

        return response()->json(['error' => __('Permission denied.')], 403);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Purchase  $purchase
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Purchase $purchase)
    {
        if (Auth::user()->can('delete purchase') && $purchase->created_by == Auth::user()->creatorId()) {
            // Note: This is a simplified deletion.

            $purchase->items()->delete();
            $purchase->delete();

            return response()->json(null, 204);
        }

        return response()->json(['error' => __('Permission denied.')], 403);
    }

    function purchaseNumber()
    {
        $latest = Purchase::where('created_by', '=', \Auth::user()->creatorId())->latest('purchase_id')->first();
        if(!$latest)
        {
            $setting = \App\Models\Utility::settings();
            return (isset($setting['purchase_starting_number']) ? $setting['purchase_starting_number'] : 1);
        }

        return $latest->purchase_id + 1;
    }
}
