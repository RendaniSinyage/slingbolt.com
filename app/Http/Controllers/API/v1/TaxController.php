<?php

namespace App\Http\Controllers\API\v1;

use App\Http\Controllers\Controller;
use App\Http\Resources\TaxResource;
use App\Models\Tax;
use App\Models\ProposalProduct;
use App\Models\BillProduct;
use App\Models\InvoiceProduct;
use App\Models\ProductService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TaxController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        if (Auth::user()->can('manage constant tax')) {
            $taxes = Tax::where('created_by', Auth::user()->creatorId())->get();
            return TaxResource::collection($taxes);
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
        if (Auth::user()->can('create constant tax')) {
            $validator = \Validator::make($request->all(), [
                'name' => 'required|string|max:20',
                'rate' => 'required|numeric|min:0',
            ]);

            if ($validator->fails()) {
                return response()->json(['error' => $validator->errors()->first()], 400);
            }

            $tax = new Tax();
            $tax->name = $request->name;
            $tax->rate = $request->rate;
            $tax->created_by = Auth::user()->creatorId();
            $tax->save();

            return new TaxResource($tax);
        }

        return response()->json(['error' => __('Permission denied.')], 403);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Tax  $tax
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Tax $tax)
    {
        if (Auth::user()->can('manage constant tax') && $tax->created_by == Auth::user()->creatorId()) {
            return new TaxResource($tax);
        }

        return response()->json(['error' => __('Permission denied.')], 403);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Tax  $tax
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, Tax $tax)
    {
        if (Auth::user()->can('edit constant tax') && $tax->created_by == Auth::user()->creatorId()) {
            $validator = \Validator::make($request->all(), [
                'name' => 'required|string|max:20',
                'rate' => 'required|numeric|min:0',
            ]);

            if ($validator->fails()) {
                return response()->json(['error' => $validator->errors()->first()], 400);
            }

            $tax->name = $request->name;
            $tax->rate = $request->rate;
            $tax->save();

            return new TaxResource($tax);
        }

        return response()->json(['error' => __('Permission denied.')], 403);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Tax  $tax
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Tax $tax)
    {
        if (Auth::user()->can('delete constant tax') && $tax->created_by == Auth::user()->creatorId()) {
            $proposalData = ProposalProduct::whereRaw("find_in_set(?,tax)", [$tax->id])->first();
            $billData = BillProduct::whereRaw("find_in_set(?,tax)", [$tax->id])->first();
            $invoiceData = InvoiceProduct::whereRaw("find_in_set(?,tax)", [$tax->id])->first();
            $productData = ProductService::whereRaw("find_in_set(?,tax_id)", [$tax->id])->first();

            if (!empty($proposalData) || !empty($billData) || !empty($invoiceData) || !empty($productData)) {
                return response()->json(['error' => __('this tax is already assign to proposal or bill or invoice or product&service so please move or remove this tax related data.')], 422);
            }

            $tax->delete();

            return response()->json(null, 204);
        }

        return response()->json(['error' => __('Permission denied.')], 403);
    }
}
