<?php

namespace App\Http\Controllers\API\v1;

use App\Http\Controllers\Controller;
use App\Models\CustomerDebitNotes;
use App\Models\Bill;
use Illuminate\Http\Request;
use App\Http\Resources\CustomerDebitNoteResource;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Auth;

class CustomerDebitNotesController extends Controller
{
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

        $customDebitNotes = CustomerDebitNotes::whereHas('bills', function ($query) {
            $query->where('created_by', Auth::user()->creatorId());
        })->with(['bills'])->get();

        return CustomerDebitNoteResource::collection($customDebitNotes);
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
                'bill'   => 'required|numeric|exists:bills,id',
                'amount' => 'required|numeric|gt:0',
                'date'   => 'required|date',
            ]
        );
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }
        $bill_id = $request->bill;
        $billDue = Bill::find($bill_id);
        $debitAmount = floatval($request->amount);

        if($billDue){
            $billPaid = $billDue->getTotal() - $billDue->getDue() - $billDue->billTotalDebitNote();
            $customerDebitNotes = CustomerDebitNotes::where('bill',$bill_id)->get()->sum('amount');
            if($debitAmount > $billPaid || ($customerDebitNotes + $debitAmount)  > $billPaid)
            {
                return response()->json(['error' => 'Maximum ' . Auth::user()->priceFormat($billPaid-$customerDebitNotes) . ' debit limit of this bill.'], 422);
            }
            $debit = new CustomerDebitNotes();
            $debit->debit_id = $this->debitNoteNumber();
            $debit->bill = $bill_id;
            $debit->date = $request->date;
            $debit->amount = $debitAmount;
            $debit->status = 0;
            $debit->description = $request->description;
            $debit->save();

            return (new CustomerDebitNoteResource($debit->load('bills')))->additional(['message' => 'Debit Note successfully created.']);
        }else{
            return response()->json(['error' => 'The bill field is required.'], 422);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\CustomerDebitNotes  $customerDebitNote
     * @return \Illuminate\Http\Response
     */
    public function show(CustomerDebitNotes $customerDebitNote)
    {
        if (Gate::denies('manage debit note')) {
            return response()->json(['error' => 'Permission denied.'], 403);
        }

        return new CustomerDebitNoteResource($customerDebitNote->load('bills'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\CustomerDebitNotes  $customerDebitNote
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, CustomerDebitNotes $customerDebitNote)
    {
        if (Gate::denies('edit debit note')) {
            return response()->json(['error' => 'Permission denied.'], 403);
        }

        $validator = \Validator::make(
            $request->all(), [
                'amount' => 'sometimes|required|numeric|gt:0',
                'date'   => 'sometimes|required|date_format:Y-m-d',
            ]
        );

        if($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $billDue = Bill::find($customerDebitNote->bill);
        $debitAmount = floatval($request->amount);
        $billPaid = $billDue->getTotal() - $billDue->getDue() - $billDue->billTotalDebitNote();
        $existingDebits = CustomerDebitNotes::where('bill', $customerDebitNote->bill)->where('id', '!=', $customerDebitNote->id)->get()->sum('amount');
        if (($existingDebits + $debitAmount) > $billPaid) {
            return response()->json(['error' => 'Maximum ' . Auth::user()->priceFormat($billPaid - $existingDebits) . ' debit to this bill.'], 422);
        }

        $customerDebitNote->update($request->all());

        return (new CustomerDebitNoteResource($customerDebitNote->load('bills')))->additional(['message' => 'Debit Note successfully updated.']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\CustomerDebitNotes  $customerDebitNote
     * @return \Illuminate\Http\Response
     */
    public function destroy(CustomerDebitNotes $customerDebitNote)
    {
        if (Gate::denies('delete debit note')) {
            return response()->json(['error' => 'Permission denied.'], 403);
        }

        $customerDebitNote->delete();

        return response()->json(['message' => 'Debit Note successfully deleted.']);
    }

    function debitNoteNumber()
    {
        $latest = CustomerDebitNotes::whereHas('bills', function ($query) {
                    $query->where('created_by', Auth::user()->creatorId());
                     })->with(['bills'])->latest()->first();
        if ($latest == null) {
            return 1;
        } else {
            return $latest->debit_id + 1;
        }
    }
}
