<?php

namespace App\Http\Controllers\API\v1;

use App\Http\Controllers\Controller;
use App\Http\Resources\RevenueResource;
use App\Models\Revenue;
use App\Models\Customer;
use App\Models\BankAccount;
use App\Models\ProductServiceCategory;
use App\Models\Transaction;
use App\Models\Utility;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RevenueController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        if (Auth::user()->can('manage revenue')) {
            $query = Revenue::where('created_by', Auth::user()->creatorId());

            if ($request->has('customer_id')) {
                $query->where('customer_id', $request->customer_id);
            }
            if ($request->has('account_id')) {
                $query->where('account_id', $request->account_id);
            }
            if ($request->has('category_id')) {
                $query->where('category_id', $request->category_id);
            }

            $revenues = $query->with(['bankAccount', 'customer', 'category'])->get();
            return RevenueResource::collection($revenues);
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
        if (Auth::user()->can('create revenue')) {
            $validator = \Validator::make($request->all(), [
                'date' => 'required|date',
                'amount' => 'required|numeric|min:0',
                'account_id' => 'required|exists:bank_accounts,id',
                'customer_id' => 'required|exists:customers,id',
                'category_id' => 'required|exists:product_service_categories,id',
                'description' => 'required|string',
            ]);

            if ($validator->fails()) {
                return response()->json(['error' => $validator->errors()->first()], 400);
            }

            $revenue = new Revenue();
            $revenue->date = $request->date;
            $revenue->amount = $request->amount;
            $revenue->account_id = $request->account_id;
            $revenue->customer_id = $request->customer_id;
            $revenue->category_id = $request->category_id;
            $revenue->reference = $request->reference;
            $revenue->description = $request->description;
            $revenue->created_by = Auth::user()->creatorId();
            $revenue->save();

            // Create transaction
            $category = ProductServiceCategory::find($request->category_id);
            $revenue->category = $category->name;
            $revenue->payment_id = $revenue->id;
            $revenue->type = 'Revenue';
            Transaction::addTransaction($revenue);

            // Update customer balance
            $customer = Customer::find($request->customer_id);
            Utility::updateUserBalance('customer', $customer->id, $request->amount, 'debit');

            // Update bank account balance
            Utility::bankAccountBalance($request->account_id, $request->amount, 'credit');

            return new RevenueResource($revenue);
        }

        return response()->json(['error' => __('Permission denied.')], 403);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Revenue  $revenue
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Revenue $revenue)
    {
        if (Auth::user()->can('manage revenue') && $revenue->created_by == Auth::user()->creatorId()) {
            return new RevenueResource($revenue->load(['bankAccount', 'customer', 'category']));
        }

        return response()->json(['error' => __('Permission denied.')], 403);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Revenue  $revenue
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, Revenue $revenue)
    {
        if (Auth::user()->can('edit revenue') && $revenue->created_by == Auth::user()->creatorId()) {
            $validator = \Validator::make($request->all(), [
                'date' => 'required|date',
                'amount' => 'required|numeric|min:0',
                'account_id' => 'required|exists:bank_accounts,id',
                'customer_id' => 'required|exists:customers,id',
                'category_id' => 'required|exists:product_service_categories,id',
                'description' => 'required|string',
            ]);

            if ($validator->fails()) {
                return response()->json(['error' => $validator->errors()->first()], 400);
            }

            // Revert old balances
            Utility::updateUserBalance('customer', $revenue->customer_id, $revenue->amount, 'credit');
            Utility::bankAccountBalance($revenue->account_id, $revenue->amount, 'debit');

            // Update revenue
            $revenue->date = $request->date;
            $revenue->amount = $request->amount;
            $revenue->account_id = $request->account_id;
            $revenue->customer_id = $request->customer_id;
            $revenue->category_id = $request->category_id;
            $revenue->reference = $request->reference;
            $revenue->description = $request->description;
            $revenue->save();

            // Edit transaction
            $category = ProductServiceCategory::find($request->category_id);
            $revenue->category = $category->name;
            $revenue->payment_id = $revenue->id;
            $revenue->type = 'Revenue';
            Transaction::editTransaction($revenue);

            // Apply new balances
            Utility::updateUserBalance('customer', $request->customer_id, $request->amount, 'debit');
            Utility::bankAccountBalance($request->account_id, $request->amount, 'credit');

            return new RevenueResource($revenue);
        }

        return response()->json(['error' => __('Permission denied.')], 403);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Revenue  $revenue
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Revenue $revenue)
    {
        if (Auth::user()->can('delete revenue') && $revenue->created_by == Auth::user()->creatorId()) {
            // Revert balances and delete transaction
            Utility::updateUserBalance('customer', $revenue->customer_id, $revenue->amount, 'credit');
            Utility::bankAccountBalance($revenue->account_id, $revenue->amount, 'debit');
            Transaction::destroyTransaction($revenue->id, 'Revenue', 'Customer');

            $revenue->delete();

            return response()->json(null, 204);
        }

        return response()->json(['error' => __('Permission denied.')], 403);
    }
}
