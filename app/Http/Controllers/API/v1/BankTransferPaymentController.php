<?php

namespace App\Http\Controllers\API\v1;

use App\Http\Controllers\Controller;
use App\Models\BankTransfer;
use Illuminate\Http\Request;
use App\Http\Resources\BankTransferResource;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Auth;

class BankTransferPaymentController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if (Gate::denies('manage bank transfer')) {
            return response()->json(['error' => 'Permission denied.'], 403);
        }

        $transfers = BankTransfer::where('created_by', '=', Auth::user()->creatorId())->get();

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
                'from_account' => 'required|numeric',
                'to_account' => 'required|numeric',
                'amount' => 'required|numeric',
                'date' => 'required',
            ]
        );

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
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

        return (new BankTransferResource($transfer))->additional(['message' => 'Bank transfer successfully created.']);
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

        return new BankTransferResource($bankTransfer);
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
                'from_account' => 'sometimes|required|numeric',
                'to_account' => 'sometimes|required|numeric',
                'amount' => 'sometimes|required|numeric',
                'date' => 'sometimes|required',
            ]
        );

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $bankTransfer->update($request->all());

        return (new BankTransferResource($bankTransfer))->additional(['message' => 'Bank transfer successfully updated.']);
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

        $bankTransfer->delete();

        return response()->json(['message' => 'Bank transfer successfully deleted.']);
    }
}
