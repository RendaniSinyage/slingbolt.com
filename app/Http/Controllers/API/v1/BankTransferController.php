<?php

namespace App\Http\Controllers\API\v1;

use App\Http\Controllers\Controller;
use App\Models\BankTransfer;
use App\Models\BankAccount;
use Illuminate\Http\Request;
use App\Http\Resources\BankTransferResource;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Auth;
use App\Models\Utility;

class BankTransferController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        if (Gate::denies('manage bank transfer')) {
            return response()->json(['error' => 'Permission denied.'], 403);
        }

        $query = BankTransfer::where('created_by', '=', Auth::user()->creatorId());

        if ($request->has('from_account')) {
            $query->where('from_account', '=', $request->from_account);
        }
        if ($request->has('to_account')) {
            $query->where('to_account', '=', $request->to_account);
        }

        $transfers = $query->with(['fromBankAccount', 'toBankAccount'])->get();

        return BankTransferResource::collection($transfers);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        if (Gate::denies('create bank transfer')) {
            return response()->json(['error' => 'Permission denied.'], 403);
        }

        $validator = \Validator::make(
            $request->all(), [
                'from_account' => 'required|numeric|exists:bank_accounts,id',
                'to_account' => 'required|numeric|exists:bank_accounts,id',
                'amount' => 'required|numeric',
                'date' => 'required|date',
                'description' => 'required',
            ]
        );

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $from_bank = BankAccount::find($request->from_account);
        if ($from_bank->opening_balance < $request->amount) {
            return response()->json(['error' => 'You cannot transfer more than the available balance in the from account.'], 422);
        }

        $transfer = new BankTransfer();
        $transfer->from_account = $request->from_account;
        $transfer->to_account = $request->to_account;
        $transfer->amount = $request->amount;
        $transfer->date = $request->date;
        $transfer->payment_method = 0;
        $transfer->reference = $request->reference;
        $transfer->description = $request->description;
        $transfer->created_by = Auth::user()->creatorId();
        $transfer->save();

        Utility::bankAccountBalance($request->from_account, $request->amount, 'debit');
        Utility::bankAccountBalance($request->to_account, $request->amount, 'credit');

        return (new BankTransferResource($transfer->load(['fromBankAccount', 'toBankAccount'])))->additional(['message' => 'Amount successfully transferred.']);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\BankTransfer  $bankTransfer
     * @return \Illuminate\Http\Response
     */
    public function show(BankTransfer $bankTransfer)
    {
        if (Gate::denies('manage bank transfer')) {
            return response()->json(['error' => 'Permission denied.'], 403);
        }

        if ($bankTransfer->created_by != Auth::user()->creatorId()) {
            return response()->json(['error' => 'Permission denied.'], 403);
        }

        return new BankTransferResource($bankTransfer->load(['fromBankAccount', 'toBankAccount']));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\BankTransfer  $bankTransfer
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, BankTransfer $bankTransfer)
    {
        if (Gate::denies('edit bank transfer')) {
            return response()->json(['error' => 'Permission denied.'], 403);
        }

        if ($bankTransfer->created_by != Auth::user()->creatorId()) {
            return response()->json(['error' => 'Permission denied.'], 403);
        }

        $validator = \Validator::make(
            $request->all(), [
                'from_account' => 'sometimes|required|numeric|exists:bank_accounts,id',
                'to_account' => 'sometimes|required|numeric|exists:bank_accounts,id',
                'amount' => 'sometimes|required|numeric',
                'date' => 'sometimes|required|date',
                'description' => 'sometimes|required',
            ]
        );

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        Utility::bankAccountBalance($bankTransfer->from_account, $bankTransfer->amount, 'credit');
        Utility::bankAccountBalance($bankTransfer->to_account, $bankTransfer->amount, 'debit');

        $bankTransfer->update($request->all());

        Utility::bankAccountBalance($request->from_account, $request->amount, 'debit');
        Utility::bankAccountBalance($request->to_account, $request->amount, 'credit');

        return (new BankTransferResource($bankTransfer->load(['fromBankAccount', 'toBankAccount'])))->additional(['message' => 'Amount transfer successfully updated.']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\BankTransfer  $bankTransfer
     * @return \Illuminate\Http\Response
     */
    public function destroy(BankTransfer $bankTransfer)
    {
        if (Gate::denies('delete bank transfer')) {
            return response()->json(['error' => 'Permission denied.'], 403);
        }

        if ($bankTransfer->created_by != Auth::user()->creatorId()) {
            return response()->json(['error' => 'Permission denied.'], 403);
        }

        Utility::bankAccountBalance($bankTransfer->from_account, $bankTransfer->amount, 'credit');
        Utility::bankAccountBalance($bankTransfer->to_account, $bankTransfer->amount, 'debit');

        $bankTransfer->delete();

        return response()->json(['message' => 'Amount transfer successfully deleted.']);
    }
}
