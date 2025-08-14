<?php

namespace App\Http\Controllers\API\v1;

use App\Http\Controllers\Controller;
use App\Models\Bill;
use App\Models\BillProduct;
use App\Models\Vender;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class BillController extends Controller
{
    private function getBillNumber()
    {
        $latest = Bill::latest()->first();
        return $latest ? $latest->bill_id + 1 : 1;
    }

    public function index(Request $request)
    {
        $user = $request->user();
        $bills = Bill::where('created_by', $user->creatorId())->with(['vender'])->get();
        return response()->json($bills);
    }

    public function show($id)
    {
        $user = request()->user();
        $bill = Bill::with(['items.product', 'vender'])
            ->where('created_by', $user->creatorId())
            ->find($id);

        if (!$bill) {
            return response()->json(['error' => 'Bill not found or you do not have permission.'], 404);
        }
        return response()->json($bill);
    }

    public function store(Request $request)
    {
        $user = $request->user();
        $ownerId = $user->ownerId();

        $validator = Validator::make($request->all(), [
            'vender_id' => 'required|exists:venders,id',
            'bill_date' => 'required|date',
            'due_date' => 'required|date',
            'category_id' => 'required|exists:product_service_categories,id',
            'items' => 'required|array|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()], 400);
        }

        $vender = Vender::where('created_by', $ownerId)->find($request->vender_id);
        if (!$vender) {
            return response()->json(['error' => 'Invalid vendor.'], 403);
        }

        $bill = new Bill();
        $bill->bill_id = $this->getBillNumber();
        $bill->vender_id = $request->vender_id;
        $bill->bill_date = $request->bill_date;
        $bill->due_date = $request->due_date;
        $bill->category_id = $request->category_id;
        $bill->order_number = $request->order_number ?? null;
        $bill->status = 0; // Draft
        $bill->created_by = $ownerId;
        $bill->save();

        foreach ($request->items as $itemData) {
            BillProduct::create([
                'bill_id' => $bill->id,
                'product_id' => $itemData['product_id'],
                'quantity' => $itemData['quantity'],
                'price' => $itemData['price'],
                'tax' => $itemData['tax'] ?? 0,
                'discount' => $itemData['discount'] ?? 0,
            ]);
        }

        return response()->json(Bill::with('items')->find($bill->id), 201);
    }

    public function update(Request $request, $id)
    {
        $user = $request->user();
        $ownerId = $user->ownerId();

        $bill = Bill::where('created_by', $ownerId)->find($id);
        if (!$bill) {
            return response()->json(['error' => 'Bill not found.'], 404);
        }

        $validator = Validator::make($request->all(), [
            'vender_id' => 'required|exists:venders,id',
            'bill_date' => 'required|date',
            'due_date' => 'required|date',
            'category_id' => 'required|exists:product_service_categories,id',
            'items' => 'required|array|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()], 400);
        }

        $bill->vender_id = $request->vender_id;
        $bill->bill_date = $request->bill_date;
        $bill->due_date = $request->due_date;
        $bill->category_id = $request->category_id;
        $bill->save();

        BillProduct::where('bill_id', $bill->id)->delete();
        foreach ($request->items as $itemData) {
             BillProduct::create([
                'bill_id' => $bill->id,
                'product_id' => $itemData['product_id'],
                'quantity' => $itemData['quantity'],
                'price' => $itemData['price'],
                'tax' => $itemData['tax'] ?? 0,
                'discount' => $itemData['discount'] ?? 0,
            ]);
        }

        return response()->json(Bill::with('items')->find($bill->id));
    }

    public function destroy($id)
    {
        $user = request()->user();
        $ownerId = $user->ownerId();

        $bill = Bill::where('created_by', $ownerId)->find($id);
        if (!$bill) {
            return response()->json(['error' => 'Bill not found.'], 404);
        }

        BillProduct::where('bill_id', $bill->id)->delete();
        $bill->delete();

        return response()->json(['success' => 'Bill successfully deleted.'], 200);
    }
}
