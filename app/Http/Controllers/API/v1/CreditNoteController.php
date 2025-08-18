<?php

namespace App\Http\Controllers\API\v1;

use App\Http\Controllers\Controller;
use App\Models\CreditNote;
use App\Models\Invoice;
use Illuminate\Http\Request;
use App\Http\Resources\CreditNoteResource;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Auth;
use App\Traits\updateNotesStatus;

class CreditNoteController extends Controller
{
    use updateNotesStatus;
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if (Gate::denies('manage credit note')) {
            return response()->json(['error' => 'Permission denied.'], 403);
        }

        $creditNotes = CreditNote::where('created_by', Auth::user()->creatorId())->get();

        return CreditNoteResource::collection($creditNotes);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        if (Gate::denies('create credit note')) {
            return response()->json(['error' => 'Permission denied.'], 403);
        }

        $validator = \Validator::make(
            $request->all(), [
                'invoice' => 'required|exists:invoices,id',
                'amount' => 'required|numeric|gt:0',
                'date' => 'required|date',
            ]
        );

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $invoiceDue = Invoice::find($request->invoice);

        if ($request->amount > $invoiceDue->getDue()) {
            return response()->json(['error' => 'Maximum ' . Auth::user()->priceFormat($invoiceDue->getDue()) . ' credit limit of this invoice.'], 422);
        }

        $credit = new CreditNote();
        $credit->invoice = $request->invoice;
        $credit->customer = 0;
        $credit->date = $request->date;
        $credit->amount = $request->amount;
        $credit->description = $request->description;
        $credit->save();

        if($invoiceDue->getDue() <= 0)
        {
            $invoiceDue->status = 4;
            $invoiceDue->save();
        } else {
            $invoiceDue->status = 3;
            $invoiceDue->save();
        }

        $this->updateCreditNoteStatus($credit);

        return (new CreditNoteResource($credit))->additional(['message' => 'Credit Note successfully created.']);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\CreditNote  $creditNote
     * @return \Illuminate\Http\Response
     */
    public function show(CreditNote $creditNote)
    {
        if (Gate::denies('manage credit note')) {
            return response()->json(['error' => 'Permission denied.'], 403);
        }

        return new CreditNoteResource($creditNote);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\CreditNote  $creditNote
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, CreditNote $creditNote)
    {
        if (Gate::denies('edit credit note')) {
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

        $invoiceDue = Invoice::find($creditNote->invoice);
        if ($request->amount > $invoiceDue->getDue() + $creditNote->amount) {
            return response()->json(['error' => 'Maximum ' . Auth::user()->priceFormat($invoiceDue->getDue() + $creditNote->amount) . ' credit limit of this invoice.'], 422);
        }

        if(($invoiceDue->getDue() + $creditNote->amount ) - $request->amount <= 0)
        {
            $invoiceDue->status = 4;
            $invoiceDue->save();
        } else {
            $invoiceDue->status = 3;
            $invoiceDue->save();
        }

        $creditNote->update($request->all());

        $this->updateCreditNoteStatus($creditNote);

        return (new CreditNoteResource($creditNote))->additional(['message' => 'Credit Note successfully updated.']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\CreditNote  $creditNote
     * @return \Illuminate\Http\Response
     */
    public function destroy(CreditNote $creditNote)
    {
        if (Gate::denies('delete credit note')) {
            return response()->json(['error' => 'Permission denied.'], 403);
        }

        $invoice = Invoice::find($creditNote->invoice);
        $invoiceDue = $invoice->getDue() + $creditNote->amount;
        $total   = $invoice->getTotal();

        if ( $invoiceDue > 0 && $invoiceDue != $total) {
            $invoice->status = 3;
        } elseif($invoiceDue == $total) {
            $invoice->status = 2;
        }
        $invoice->save();

        $this->updateCreditNoteStatus($creditNote , 'delete');

        $creditNote->delete();

        return response()->json(['message' => 'Credit Note successfully deleted.']);
    }
}
