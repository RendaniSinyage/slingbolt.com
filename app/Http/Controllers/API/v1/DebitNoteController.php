<?php

namespace App\Http\Controllers\API\v1;

use App\Http\Controllers\Controller;
use App\Models\DebitNote;
use App\Models\Bill;
use Illuminate\Http\Request;
use App\Http\Resources\DebitNoteResource;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Auth;
use App\Traits\updateNotesStatus;

class DebitNoteController extends Controller
{
    use updateNotesStatus;
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if (Gate::denies('manage debit note')) {
            return response()->json(['error' => 'Permission denied.'], 403);
        }

        $debitNotes = DebitNote::where('created_by', Auth::user()->creatorId())->get();

        return DebitNoteResource::collection($debitNotes);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        if (Gate::denies('create debit note')) {
            return response()->json(['error' => 'Permission denied.'], 403);
        }

        $validator = \Validator::make(
            $request->all(), [
                'bill' => 'required|exists:bills,id',
                'amount' => 'required|numeric|gt:0',
                'date' => 'required|date',
            ]
        );

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $billDue = Bill::find($request->bill);

        if ($request->amount > $billDue->getDue()) {
            return response()->json(['error' => 'Maximum ' . Auth::user()->priceFormat($billDue->getDue()) . ' debit limit of this bill.'], 422);
        }

        $debit = new DebitNote();
        $debit->bill = $request->bill;
        $debit->date = $request->date;
        $debit->amount = $request->amount;
        $debit->description = $request->description;
        $debit->save();

        if($billDue->getDue() <= 0)
        {
            $billDue->status = 4;
            $billDue->save();
        } else {
            $billDue->status = 3;
            $billDue->save();
        }

        $this->updateDebitNoteStatus($debit);

        return (new DebitNoteResource($debit))->additional(['message' => 'Debit Note successfully created.']);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\DebitNote  $debitNote
     * @return \Illuminate\Http\Response
     */
    public function show(DebitNote $debitNote)
    {
        if (Gate::denies('manage debit note')) {
            return response()->json(['error' => 'Permission denied.'], 403);
        }

        return new DebitNoteResource($debitNote);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\DebitNote  $debitNote
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, DebitNote $debitNote)
    {
        if (Gate::denies('edit debit note')) {
            return response()->json(['error' => 'Permission denied.'], 403);
        }

        $validator = \Validator::make(
            $request->all(), [
                'amount' => 'sometimes|required|numeric|gt:0',
                'date' => 'sometimes|required|date',
            ]
        );

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $billDue = Bill::find($debitNote->bill);
        if ($request->amount > $billDue->getDue() + $debitNote->amount) {
            return response()->json(['error' => 'Maximum ' . Auth::user()->priceFormat($billDue->getDue() + $debitNote->amount) . ' debit limit of this bill.'], 422);
        }

        if(($billDue->getDue() + $debitNote->amount ) - $request->amount <= 0)
        {
            $billDue->status = 4;
            $billDue->save();
        } else {
            $billDue->status = 3;
            $billDue->save();
        }

        $debitNote->update($request->all());

        $this->updateDebitNoteStatus($debitNote);

        return (new DebitNoteResource($debitNote))->additional(['message' => 'Debit Note successfully updated.']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\DebitNote  $debitNote
     * @return \Illuminate\Http\Response
     */
    public function destroy(DebitNote $debitNote)
    {
        if (Gate::denies('delete debit note')) {
            return response()->json(['error' => 'Permission denied.'], 403);
        }

        $bill = Bill::find($debitNote->bill);
        $billDue = $bill->getDue() + $debitNote->amount;
        $total   = $bill->getTotal();

        if ( $billDue > 0 && $billDue != $total) {
            $bill->status = 3;
        } elseif($billDue == $total) {
            $bill->status = 2;
        }
        $bill->save();

        $this->updateDebitNoteStatus($debitNote , 'delete');

        $debitNote->delete();

        return response()->json(['message' => 'Debit Note successfully deleted.']);
    }
}
