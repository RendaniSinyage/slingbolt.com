<?php

namespace App\Http\Controllers\API\v1;

use App\Http\Controllers\Controller;
use App\Models\CustomerCreditNotes;
use App\Models\Invoice;
use Illuminate\Http\Request;
use App\Http\Resources\CustomerCreditNoteResource;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Auth;

class CustomerCreditNotesController extends Controller
{
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

        $customcreditNotes = CustomerCreditNotes::whereHas('invoices', function ($query) {
            $query->where('created_by', Auth::user()->creatorId());
        })->with(['invoices'])->get();

        return CustomerCreditNoteResource::collection($customcreditNotes);
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
                'invoice' => 'required|numeric|exists:invoices,id',
                'amount' => 'required|numeric|gt:0',
                'date' => 'required|date',
            ]
        );

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $invoice_id = $request->invoice;
        $invoiceDue = Invoice::find($invoice_id);

        if ($request->amount > $invoiceDue->getDue()) {
            return response()->json(['error' => 'Maximum ' . Auth::user()->priceFormat($invoiceDue->getDue()) . ' credit limit of this invoice.'], 422);
        }

        $credit = new CustomerCreditNotes();
        $credit->credit_id = $this->creditNoteNumber();
        $credit->invoice = $invoice_id;
        $credit->date = $request->date;
        $credit->amount = $request->amount;
        $credit->status = 0;
        $credit->description = $request->description;
        $credit->save();

        return (new CustomerCreditNoteResource($credit->load('invoices')))->additional(['message' => 'Credit Note successfully created.']);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\CustomerCreditNotes  $customerCreditNotes
     * @return \Illuminate\Http\Response
     */
    public function show(CustomerCreditNotes $customerCreditNote)
    {
        if (Gate::denies('manage credit note')) {
            return response()->json(['error' => 'Permission denied.'], 403);
        }

        return new CustomerCreditNoteResource($customerCreditNote->load('invoices'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\CustomerCreditNotes  $customerCreditNotes
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, CustomerCreditNotes $customerCreditNote)
    {
        if (Gate::denies('edit credit note')) {
            return response()->json(['error' => 'Permission denied.'], 403);
        }

        $validator = \Validator::make(
            $request->all(), [
                'amount' => 'sometimes|required|numeric|gt:0',
                'date' => 'sometimes|required|date_format:Y-m-d',
            ]
        );

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $invoiceDue = Invoice::find($customerCreditNote->invoice);
        $creditAmount = floatval($request->amount);
        $invoicePaid = $invoiceDue->getTotal() - $invoiceDue->getDue() - $invoiceDue->invoiceTotalCreditNote();
        $existingCredits = CustomerCreditNotes::where('invoice', $customerCreditNote->invoice)->where('id', '!=', $customerCreditNote->id)->get()->sum('amount');

        if (($existingCredits + $creditAmount) > $invoicePaid) {
            return response()->json(['error' => 'Maximum ' . Auth::user()->priceFormat($invoicePaid - $existingCredits) . ' credit to this invoice.'], 422);
        }

        $customerCreditNote->update($request->all());

        return (new CustomerCreditNoteResource($customerCreditNote->load('invoices')))->additional(['message' => 'Credit Note successfully updated.']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\CustomerCreditNotes  $customerCreditNotes
     * @return \Illuminate\Http\Response
     */
    public function destroy(CustomerCreditNotes $customerCreditNote)
    {
        if (Gate::denies('delete credit note')) {
            return response()->json(['error' => 'Permission denied.'], 403);
        }

        $customerCreditNote->delete();

        return response()->json(['message' => 'Credit Note successfully deleted.']);
    }

    function creditNoteNumber()
    {
        $latest = CustomerCreditNotes::whereHas('invoices', function ($query) {
                    $query->where('created_by', Auth::user()->creatorId());
                     })->with(['invoices'])->latest()->first();
        if ($latest == null) {
            return 1;
        } else {
            return $latest->credit_id + 1;
        }
    }
}
