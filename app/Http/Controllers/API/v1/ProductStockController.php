<?php

namespace App\Http\Controllers\API\v1;

use App\Http\Controllers\Controller;
use App\Models\ProductService;
use App\Models\Utility;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProductStockController extends Controller
{
    public function index()
    {
        if (Auth::user()->can('manage product & service')) {
            $productServices = ProductService::where('created_by', Auth::user()->creatorId())
                ->where('type', '=', 'product')
                ->get();

            return response()->json($productServices);
        } else {
            return response()->json(['error' => __('Permission denied.')], 403);
        }
    }

    public function update(Request $request, $id)
    {
        if (Auth::user()->can('edit product & service')) {
            $productService = ProductService::find($id);

            if ($productService && $productService->created_by == Auth::user()->creatorId()) {
                $validator = \Validator::make($request->all(), [
                    'quantity' => 'required|numeric',
                ]);

                if ($validator->fails()) {
                    return response()->json(['error' => $validator->errors()->first()], 422);
                }

                $total = $productService->quantity + $request->quantity;
                $productService->quantity = $total;
                $productService->save();

                // Add Product Stock Report
                Utility::addProductStock(
                    $productService->id,
                    $request->quantity,
                    'manually',
                    $request->quantity . '  ' . __('quantity added by manually'),
                    0
                );

                return response()->json($productService);
            } else {
                return response()->json(['error' => __('Product not found or permission denied.')], 404);
            }
        } else {
            return response()->json(['error' => __('Permission denied.')], 403);
        }
    }
}
