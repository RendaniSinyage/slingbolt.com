<?php

namespace App\Http\Controllers\API\v1;

use App\Http\Controllers\Controller;
use App\Http\Resources\BillResource;
use App\Models\Bill;
use App\Models\BillProduct;
use App\Models\BillPayment;
use App\Models\ProductService;
use App\Models\Vender;
use App\Models\Customer;
use App\Models\Employee;
use App\Models\Utility;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ExpenseController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        if (Auth::user()->can('manage bill')) {
            $query = Bill::where('created_by', Auth::user()->creatorId())->where('type', 'Expense');

            if ($request->has('vender_id')) {
                $query->where('vender_id', $request->vender_id);
            }
            if ($request->has('category_id')) {
                $query->where('category_id', $request->category_id);
            }

            $expenses = $query->with(['category'])->get();
            return BillResource::collection($expenses);
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
        if (Auth::user()->can('create bill')) {
            $validator = \Validator::make($request->all(), [
                'payment_date' => 'required|date',
                'account_id' => 'required|exists:bank_accounts,id',
                'totalAmount' => 'required|numeric|min:0',
                'user_type' => 'required|in:employee,customer,vendor',
                'user_id' => 'required|integer',
                'items' => 'required|array|min:1',
            ]);

            if ($validator->fails()) {
                return response()->json(['error' => $validator->errors()->first()], 400);
            }

            $expense = new Bill();
            $expense->bill_id = $this->expenseNumber();
            $expense->vender_id = $request->user_id;
            $expense->bill_date = $request->payment_date;
            $expense->due_date = $request->payment_date;
            $expense->status = 4; // Paid
            $expense->type = 'Expense';
            $expense->user_type = $request->user_type;
            $expense->created_by = Auth::user()->creatorId();
            $expense->save();

            foreach ($request->items as $item) {
                $expenseProduct = new BillProduct();
                $expenseProduct->bill_id = $expense->id;
                $expenseProduct->product_id = $item['item'];
                $expenseProduct->quantity = $item['quantity'];
                $expenseProduct->tax = $item['tax'] ?? null;
                $expenseProduct->discount = $item['discount'] ?? 0;
                $expenseProduct->price = $item['price'];
                $expenseProduct->description = $item['description'] ?? null;
                $expenseProduct->save();
            }

            $expensePayment = new BillPayment();
            $expensePayment->bill_id = $expense->id;
            $expensePayment->date = $request->payment_date;
            $expensePayment->amount = $request->totalAmount;
            $expensePayment->account_id = $request->account_id;
            $expensePayment->save();

            if ($request->user_type == 'customer') {
                Utility::updateUserBalance('customer', $expense->vender_id, $request->totalAmount, 'credit');
            } elseif ($request->user_type == 'vendor') {
                Utility::updateUserBalance('vendor', $expense->vender_id, $request->totalAmount, 'credit');
            }
            Utility::bankAccountBalance($request->account_id, $request->totalAmount, 'debit');

            return new BillResource($expense->load('items', 'payments'));
        }

        return response()->json(['error' => __('Permission denied.')], 403);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        if (Auth::user()->can('show bill')) {
            $expense = Bill::where('type', 'Expense')->where('created_by', Auth::user()->creatorId())->find($id);
            if($expense){
                 return new BillResource($expense->load(['items.product', 'payments']));
            }
            return response()->json(['error' => __('Expense not found.')], 404);
        }

        return response()->json(['error' => __('Permission denied.')], 403);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        if (Auth::user()->can('delete bill')) {
            $expense = Bill::where('type', 'Expense')->where('created_by', Auth::user()->creatorId())->find($id);
            if($expense){
                // Note: Simplified deletion
                $expense->items()->delete();
                $expense->payments()->delete();
                $expense->delete();
                return response()->json(null, 204);
            }
            return response()->json(['error' => __('Expense not found.')], 404);
        }

        return response()->json(['error' => __('Permission denied.')], 403);
    }

    private function expenseNumber()
    {
        $latest = Bill::where('created_by', Auth::user()->creatorId())->where('type', 'Expense')->latest('bill_id')->first();
        return ($latest ? $latest->bill_id : 0) + 1;
    }
}
