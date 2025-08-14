<?php

namespace App\Http\Controllers\API\v1;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\Vender;
use App\Models\BankAccount;
use App\Models\ProductServiceCategory;
use App\Models\Transaction;
use App\Models\TransactionLines;
use App\Models\Utility;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PaymentController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        if (Auth::user()->can('manage payment')) {
            $query = Payment::where('created_by', Auth::user()->creatorId());

            if ($request->has('vender_id')) {
                $query->where('vender_id', $request->vender_id);
            }
            if ($request->has('account_id')) {
                $query->where('account_id', $request->account_id);
            }
            if ($request->has('category_id')) {
                $query->where('category_id', $request->category_id);
            }

            $payments = $query->with(['bankAccount', 'vender', 'category'])->get();
            return response()->json($payments);
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
        if (Auth::user()->can('create payment')) {
            $validator = \Validator::make($request->all(), [
                'date' => 'required|date',
                'amount' => 'required|numeric|min:0',
                'account_id' => 'required|exists:bank_accounts,id',
                'vender_id' => 'required|exists:venders,id',
                'category_id' => 'required|exists:product_service_categories,id',
                'description' => 'required|string',
            ]);

            if ($validator->fails()) {
                return response()->json(['error' => $validator->errors()->first()], 400);
            }

            $payment = new Payment();
            $payment->date = $request->date;
            $payment->amount = $request->amount;
            $payment->account_id = $request->account_id;
            $payment->vender_id = $request->vender_id;
            $payment->category_id = $request->category_id;
            $payment->reference = $request->reference;
            $payment->description = $request->description;
            $payment->created_by = Auth::user()->creatorId();
            $payment->save();

            // Create transaction
            $category = ProductServiceCategory::find($request->category_id);
            $payment->category = $category->name;
            $payment->payment_id = $payment->id;
            $payment->type = 'Payment';
            Transaction::addTransaction($payment);

            // Update vendor balance
            $vender = Vender::find($request->vender_id);
            Utility::updateUserBalance('vendor', $vender->id, $request->amount, 'credit');

            // Update bank account balance
            Utility::bankAccountBalance($request->account_id, $request->amount, 'debit');

            return response()->json($payment, 201);
        }

        return response()->json(['error' => __('Permission denied.')], 403);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Payment  $payment
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Payment $payment)
    {
        if (Auth::user()->can('manage payment') && $payment->created_by == Auth::user()->creatorId()) {
            return response()->json($payment->load(['bankAccount', 'vender', 'category']));
        }

        return response()->json(['error' => __('Permission denied.')], 403);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Payment  $payment
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, Payment $payment)
    {
        if (Auth::user()->can('edit payment') && $payment->created_by == Auth::user()->creatorId()) {
            $validator = \Validator::make($request->all(), [
                'date' => 'required|date',
                'amount' => 'required|numeric|min:0',
                'account_id' => 'required|exists:bank_accounts,id',
                'vender_id' => 'required|exists:venders,id',
                'category_id' => 'required|exists:product_service_categories,id',
                'description' => 'required|string',
            ]);

            if ($validator->fails()) {
                return response()->json(['error' => $validator->errors()->first()], 400);
            }

            // Revert old balances
            Utility::updateUserBalance('vendor', $payment->vender_id, $payment->amount, 'debit');
            Utility::bankAccountBalance($payment->account_id, $payment->amount, 'credit');

            // Update payment
            $payment->date = $request->date;
            $payment->amount = $request->amount;
            $payment->account_id = $request->account_id;
            $payment->vender_id = $request->vender_id;
            $payment->category_id = $request->category_id;
            $payment->reference = $request->reference;
            $payment->description = $request->description;
            $payment->save();

            // Edit transaction
            $category = ProductServiceCategory::find($request->category_id);
            $payment->category = $category->name;
            $payment->payment_id = $payment->id;
            $payment->type = 'Payment';
            Transaction::editTransaction($payment);

            // Apply new balances
            Utility::updateUserBalance('vendor', $request->vender_id, $request->amount, 'credit');
            Utility::bankAccountBalance($request->account_id, $request->amount, 'debit');

            return response()->json($payment);
        }

        return response()->json(['error' => __('Permission denied.')], 403);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Payment  $payment
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Payment $payment)
    {
        if (Auth::user()->can('delete payment') && $payment->created_by == Auth::user()->creatorId()) {
            // Revert balances and delete transaction
            Utility::updateUserBalance('vendor', $payment->vender_id, $payment->amount, 'debit');
            Utility::bankAccountBalance($payment->account_id, $payment->amount, 'credit');
            Transaction::destroyTransaction($payment->id, 'Payment', 'Vender');

            $payment->delete();

            return response()->json(null, 204);
        }

        return response()->json(['error' => __('Permission denied.')], 403);
    }
}
