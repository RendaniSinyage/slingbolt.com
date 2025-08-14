<?php

namespace App\Http\Controllers\API\v1;

use App\Http\Controllers\Controller;
use App\Models\ProductService;
use App\Models\Utility;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class ProductServiceController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        if (Auth::user()->can('manage product & service')) {
            $query = ProductService::where('created_by', Auth::user()->creatorId());

            if ($request->has('category_id')) {
                $query->where('category_id', $request->category_id);
            }

            $productServices = $query->with(['category', 'unit'])->get();

            return response()->json($productServices);
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
        if (Auth::user()->can('create product & service')) {
            $rules = [
                'name' => 'required|string|max:255',
                'sku' => [
                    'required',
                    'string',
                    Rule::unique('product_services')->where(function ($query) {
                        return $query->where('created_by', Auth::user()->creatorId());
                    })
                ],
                'sale_price' => 'required|numeric',
                'purchase_price' => 'required|numeric',
                'category_id' => 'required|exists:product_service_categories,id',
                'unit_id' => 'required|exists:product_service_units,id',
                'type' => 'required|in:product,service',
                'tax_id' => 'nullable|array',
            ];

            $validator = \Validator::make($request->all(), $rules);

            if ($validator->fails()) {
                return response()->json(['error' => $validator->errors()->first()], 400);
            }

            $productService = new ProductService();
            $productService->name = $request->name;
            $productService->description = $request->description;
            $productService->sku = $request->sku;
            $productService->sale_price = $request->sale_price;
            $productService->purchase_price = $request->purchase_price;
            $productService->tax_id = !empty($request->tax_id) ? implode(',', $request->tax_id) : '';
            $productService->unit_id = $request->unit_id;
            $productService->quantity = $request->quantity ?? 0;
            $productService->type = $request->type;
            $productService->sale_chartaccount_id = $request->sale_chartaccount_id;
            $productService->expense_chartaccount_id = $request->expense_chartaccount_id;
            $productService->category_id = $request->category_id;
            $productService->created_by = Auth::user()->creatorId();
            $productService->save();

            // Note: Image uploads and custom fields are not handled in this API version.

            return response()->json($productService, 201);
        }

        return response()->json(['error' => __('Permission denied.')], 403);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\ProductService  $productservice
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(ProductService $productservice)
    {
        if (Auth::user()->can('manage product & service') && $productservice->created_by == Auth::user()->creatorId()) {
            return response()->json($productservice->load(['category', 'unit', 'taxes']));
        }

        return response()->json(['error' => __('Permission denied.')], 403);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\ProductService  $productservice
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, ProductService $productservice)
    {
        if (Auth::user()->can('edit product & service') && $productservice->created_by == Auth::user()->creatorId()) {
             $rules = [
                'name' => 'required|string|max:255',
                'sku' => [
                    'required',
                    'string',
                    Rule::unique('product_services')->ignore($productservice->id)->where(function ($query) {
                        return $query->where('created_by', Auth::user()->creatorId());
                    })
                ],
                'sale_price' => 'required|numeric',
                'purchase_price' => 'required|numeric',
                'category_id' => 'required|exists:product_service_categories,id',
                'unit_id' => 'required|exists:product_service_units,id',
                'type' => 'required|in:product,service',
            ];

            $validator = \Validator::make($request->all(), $rules);

            if ($validator->fails()) {
                return response()->json(['error' => $validator->errors()->first()], 400);
            }

            $productservice->fill($request->except(['tax_id']));
            $productservice->tax_id = !empty($request->tax_id) ? implode(',', $request->tax_id) : '';
            $productservice->save();

            return response()->json($productservice);
        }

        return response()->json(['error' => __('Permission denied.')], 403);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\ProductService  $productservice
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(ProductService $productservice)
    {
        if (Auth::user()->can('delete product & service') && $productservice->created_by == Auth::user()->creatorId()) {
            if (!empty($productservice->pro_image)) {
                $file_path = '/uploads/pro_image/' . $productservice->pro_image;
                Utility::changeStorageLimit(Auth::user()->creatorId(), $file_path);
            }
            $productservice->delete();

            return response()->json(null, 204);
        }

        return response()->json(['error' => __('Permission denied.')], 403);
    }
}
