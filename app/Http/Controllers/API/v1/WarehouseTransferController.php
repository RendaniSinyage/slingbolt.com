<?php

namespace App\Http\Controllers\API\v1;

use App\Http\Controllers\Controller;
use App\Models\warehouse;
use App\Models\WarehouseProduct;
use App\Models\WarehouseTransfer;
use App\Models\Utility;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class WarehouseTransferController extends Controller
{
    public function index()
    {
        if (Auth::user()->can('manage warehouse transfer')) {
            $warehouse_transfers = WarehouseTransfer::where('created_by', Auth::user()->creatorId())
                ->with(['product', 'fromWarehouse', 'toWarehouse'])
                ->get();
            return response()->json($warehouse_transfers);
        } else {
            return response()->json(['error' => __('Permission denied.')], 403);
        }
    }

    public function store(Request $request)
    {
        if (Auth::user()->can('create warehouse transfer')) {
            $validator = Validator::make($request->all(), [
                'from_warehouse_id' => 'required|exists:warehouses,id',
                'to_warehouse_id' => 'required|exists:warehouses,id|different:from_warehouse_id',
                'product_id' => 'required|exists:product_services,id',
                'quantity' => 'required|numeric|min:1',
                'date' => 'required|date',
            ]);

            if ($validator->fails()) {
                return response()->json(['error' => $validator->errors()->first()], 422);
            }

            $fromWarehouseProduct = WarehouseProduct::where('warehouse_id', $request->from_warehouse_id)
                ->where('product_id', $request->product_id)->first();

            if (!$fromWarehouseProduct || $request->quantity > $fromWarehouseProduct->quantity) {
                return response()->json(['error' => __('Product out of stock!')], 400);
            }

            $warehouse_transfer = WarehouseTransfer::create([
                'from_warehouse' => $request->from_warehouse_id,
                'to_warehouse' => $request->to_warehouse_id,
                'product_id' => $request->product_id,
                'quantity' => $request->quantity,
                'date' => $request->date,
                'created_by' => Auth::user()->creatorId(),
            ]);

            Utility::warehouse_transfer_qty($request->from_warehouse_id, $request->to_warehouse_id, $request->product_id, $request->quantity);

            return response()->json($warehouse_transfer, 201);
        } else {
            return response()->json(['error' => __('Permission denied.')], 403);
        }
    }

    public function show(WarehouseTransfer $warehouseTransfer)
    {
        if (Auth::user()->can('manage warehouse transfer') && $warehouseTransfer->created_by == Auth::user()->creatorId()) {
            $warehouseTransfer->load(['product', 'fromWarehouse', 'toWarehouse']);
            return response()->json($warehouseTransfer);
        } else {
            return response()->json(['error' => __('Permission denied or transfer not found.')], 403);
        }
    }

    public function destroy(WarehouseTransfer $warehouseTransfer)
    {
        if (Auth::user()->can('delete warehouse transfer') && $warehouseTransfer->created_by == Auth::user()->creatorId()) {
            Utility::warehouse_transfer_qty($warehouseTransfer->to_warehouse, $warehouseTransfer->from_warehouse, $warehouseTransfer->product_id, $warehouseTransfer->quantity, 'delete');
            $warehouseTransfer->delete();
            return response()->json(null, 204);
        } else {
            return response()->json(['error' => __('Permission denied.')], 403);
        }
    }
}
