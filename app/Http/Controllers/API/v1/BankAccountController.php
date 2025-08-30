<?php

namespace App\Http\Controllers\API\v1;

use App\Http\Controllers\Controller;
use App\Http\Resources\BankAccountResource;
use App\Models\BankAccount;
use App\Models\Revenue;
use App\Models\InvoicePayment;
use App\Models\Transaction;
use App\Models\Payment;
use App\Models\BillPayment;
use App\Models\TransactionLines;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BankAccountController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        if (Auth::user()->can('manage bank account')) {
            $accounts = BankAccount::where('created_by', Auth::user()->creatorId())->get();
            return BankAccountResource::collection($accounts);
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
        if (Auth::user()->can('create bank account')) {
            $rules = [
                'holder_name' => 'required|string|max:255',
                'bank_name' => 'required|string|max:255',
                'account_number' => 'required|string|max:255',
                'opening_balance' => 'nullable|numeric',
                'contact_number' => 'nullable|regex:/^([0-9\s\-\+\(\)]*)$/',
                'chart_account_id' => 'nullable|exists:chart_of_accounts,id',
            ];

            $validator = \Validator::make($request->all(), $rules);

            if ($validator->fails()) {
                return response()->json(['error' => $validator->errors()->first()], 400);
            }

            $account = new BankAccount();
            $account->holder_name = $request->holder_name;
            $account->bank_name = $request->bank_name;
            $account->account_number = $request->account_number;
            $account->opening_balance = $request->opening_balance ?? 0;
            $account->contact_number = $request->contact_number;
            $account->bank_address = $request->bank_address;
            $account->chart_account_id = $request->chart_account_id;
            $account->created_by = Auth::user()->creatorId();
            $account->save();

            event(new \App\Events\CreateBankAccount($request, $account));

            return new BankAccountResource($account);
        }

        return response()->json(['error' => __('Permission denied.')], 403);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\BankAccount  $bankAccount
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(BankAccount $bankAccount)
    {
        if (Auth::user()->can('manage bank account') && $bankAccount->created_by == Auth::user()->creatorId()) {
            return new BankAccountResource($bankAccount);
        }

        return response()->json(['error' => __('Permission denied.')], 403);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\BankAccount  $bankAccount
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, BankAccount $bankAccount)
    {
        if (Auth::user()->can('edit bank account') && $bankAccount->created_by == Auth::user()->creatorId()) {
            $rules = [
                'holder_name' => 'required|string|max:255',
                'bank_name' => 'required|string|max:255',
                'account_number' => 'required|string|max:255',
                'opening_balance' => 'nullable|numeric',
                'contact_number' => 'nullable|regex:/^([0-9\s\-\+\(\)]*)$/',
                'chart_account_id' => 'nullable|exists:chart_of_accounts,id',
            ];

            $validator = \Validator::make($request->all(), $rules);

            if ($validator->fails()) {
                return response()->json(['error' => $validator->errors()->first()], 400);
            }

            $bankAccount->update($request->all());

            event(new \App\Events\UpdateBankAccount($request, $bankAccount));

            return new BankAccountResource($bankAccount);
        }

        return response()->json(['error' => __('Permission denied.')], 403);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\BankAccount  $bankAccount
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(BankAccount $bankAccount)
    {
        if (Auth::user()->can('delete bank account') && $bankAccount->created_by == Auth::user()->creatorId()) {
            $revenue = Revenue::where('account_id', $bankAccount->id)->first();
            $invoicePayment = InvoicePayment::where('account_id', $bankAccount->id)->first();
            $transaction = Transaction::where('account', $bankAccount->id)->first();
            $payment = Payment::where('account_id', $bankAccount->id)->first();
            $billPayment = BillPayment::where('account_id', $bankAccount->id)->first();

            if (!empty($revenue) || !empty($invoicePayment) || !empty($transaction) || !empty($payment) || !empty($billPayment)) {
                return response()->json(['error' => __('Please delete related record of this account.')], 422);
            }

            TransactionLines::where('reference_id', $bankAccount->id)->where('reference', 'Bank Account')->delete();

            event(new \App\Events\DeleteBankAccount($bankAccount));
            $bankAccount->delete();

            return response()->json(null, 204);
        }

        return response()->json(['error' => __('Permission denied.')], 403);
    }
}
