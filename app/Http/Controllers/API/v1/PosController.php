<?php

namespace App\Http\Controllers\API\v1;

use App\Http\Controllers\Controller;
use App\Models\Pos;
use App\Models\PosPayment;
use App\Models\PosProduct;
use App\Models\ProductService;
use App\Models\Customer;
use App\Models\warehouse;
use Illuminate\Http\Request;
use App\Http\Resources\PosResource;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PosController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if (Gate::denies('manage pos')) {
            return response()->json(['error' => 'Permission denied.'], 403);
        }

        $pos = Pos::where('created_by', '=', Auth::user()->creatorId())->with(['customer', 'warehouse'])->get();

        return PosResource::collection($pos);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        if (Gate::denies('manage pos')) {
            return response()->json(['error' => 'Permission denied.'], 403);
        }

        $validator = \Validator::make(
            $request->all(), [
                'customer_id' => 'required|exists:customers,id',
                'warehouse_id' => 'required|exists:warehouses,id',
                'items' => 'required|array',
            ]
        );

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user_id = Auth::user()->creatorId();
        $pos_id = $this->invoicePosNumber();

        $pos = new Pos();
        $pos->pos_id = $pos_id;
        $pos->customer_id = $request->customer_id;
        $pos->warehouse_id = $request->warehouse_id;
        $pos->pos_date = date('Y-m-d');
        $pos->created_by = $user_id;
        $pos->save();

        $mainsubtotal = 0;
        foreach ($request->items as $item) {
            $product_id = $item['id'];
            $product = ProductService::find($product_id);

            $original_quantity = ($product == null) ? 0 : (int) $product->quantity;
            $product_quantity = $original_quantity - $item['quantity'];

            if ($product != null && !empty($product)) {
                ProductService::where('id', $product_id)->update(['quantity' => $product_quantity]);
            }

            $tax_id = ProductService::tax_id($product_id);

            $positems = new PosProduct();
            $positems->pos_id = $pos->id;
            $positems->product_id = $product_id;
            $positems->price = $item['price'];
            $positems->quantity = $item['quantity'];
            $positems->tax = $tax_id;
            $positems->discount = $item['discount'] ?? 0;
            $positems->save();

            $mainsubtotal += $item['price'] * $item['quantity'];
        }

        $posPayment = new PosPayment();
        $posPayment->pos_id = $pos->id;
        $posPayment->date = date('Y-m-d');
        $posPayment->amount = $mainsubtotal;
        $posPayment->discount = $request->discount ?? 0;
        $posPayment->discount_amount = $mainsubtotal - ($request->discount ?? 0);
        $posPayment->save();

        return (new PosResource($pos->load('items', 'customer', 'warehouse', 'payments')))->additional(['message' => 'POS successfully created.']);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Pos  $pos
     * @return \Illuminate\Http\Response
     */
    public function show(Pos $po)
    {
        if (Gate::denies('show pos')) {
            return response()->json(['error' => 'Permission denied.'], 403);
        }

        if ($po->created_by != Auth::user()->creatorId()) {
            return response()->json(['error' => 'Permission denied.'], 403);
        }

        return new PosResource($po->load('items', 'customer', 'warehouse', 'payments'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Pos  $pos
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Pos $po)
    {
        return response()->json(['error' => 'Not implemented.'], 501);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Pos  $pos
     * @return \Illuminate\Http\Response
     */
    public function destroy(Pos $po)
    {
        if (Gate::denies('delete pos')) {
            return response()->json(['error' => 'Permission denied.'], 403);
        }

        if ($po->created_by != Auth::user()->creatorId()) {
            return response()->json(['error' => 'Permission denied.'], 403);
        }

        $po->delete();

        return response()->json(['message' => 'POS successfully deleted.']);
    }

    private function invoicePosNumber()
    {
        $latest = Pos::where('created_by', '=', Auth::user()->creatorId())->latest('pos_id')->first();

        if(!$latest)
        {
            $setting = \App\Models\Utility::settings();
            $pos_starting_number = isset($setting['pos_starting_number']) ? (int)$setting['pos_starting_number'] : 1;
            return $pos_starting_number;
        }

        return $latest->pos_id + 1;
    }
}
