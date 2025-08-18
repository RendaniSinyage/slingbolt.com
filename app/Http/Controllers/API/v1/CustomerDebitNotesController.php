<?php

namespace App\Http\Controllers\API\v1;

use App\Http\Controllers\Controller;
use App\Models\Bill;
use App\Models\CustomerDebitNotes;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class CustomerDebitNotesController extends Controller
{
    public function index()
    {
        if (Auth::user()->can('manage debit note')) {
            $customDebitNotes = CustomerDebitNotes::whereHas('bills', function ($query) {
                $query->where('created_by', Auth::user()->creatorId());
            })->with(['bills'])->get();
            return response()->json($customDebitNotes);
        } else {
            return response()->json(['error' => __('Permission denied.')], 403);
        }
    }

    public function store(Request $request)
    {
        if (Auth::user()->can('create debit note')) {
            $validator = Validator::make(
                $request->all(),
                [
                    'bill_id' => 'required|numeric',
                    'amount' => 'required|numeric|gt:0',
                    'date' => 'required|date_format:Y-m-d',
                ]
            );
            if ($validator->fails()) {
                return response()->json(['error' => $validator->errors()->first()], 422);
            }

            $bill = Bill::where('id', $request->bill_id)->where('created_by', Auth::user()->creatorId())->first();
            if (!$bill) {
                return response()->json(['error' => __('Bill not found.')], 404);
            }

            $debitAmount = floatval($request->amount);
            $billPaid = $bill->getTotal() - $bill->getDue() - $bill->billTotalDebitNote();
            $customerDebitNotes = CustomerDebitNotes::where('bill', $request->bill_id)->sum('amount');

            if ($debitAmount > $billPaid || ($customerDebitNotes + $debitAmount) > $billPaid) {
                return response()->json(['error' => 'Maximum ' . Auth::user()->priceFormat($billPaid - $customerDebitNotes) . ' debit limit of this bill.'], 400);
            }

            $debit = new CustomerDebitNotes();
            $debit->debit_id = $this->debitNoteNumber();
            $debit->bill = $request->bill_id;
            $debit->date = $request->date;
            $debit->amount = $debitAmount;
            $debit->description = $request->description;
            $debit->save();

            return response()->json($debit, 201);
        } else {
            return response()->json(['error' => __('Permission denied.')], 403);
        }
    }

    public function show($id)
    {
        if (Auth::user()->can('manage debit note')) {
            $debitNote = CustomerDebitNotes::with('bills')->where('id', $id)->whereHas('bills', function ($query) {
                $query->where('created_by', Auth::user()->creatorId());
            })->first();

            if ($debitNote) {
                return response()->json($debitNote);
            } else {
                return response()->json(['error' => __('Debit note not found.')], 404);
            }
        } else {
            return response()->json(['error' => __('Permission denied.')], 403);
        }
    }

    public function update(Request $request, $id)
    {
        if (Auth::user()->can('edit debit note')) {
            $validator = Validator::make(
                $request->all(),
                [
                    'amount' => 'required|numeric|gt:0',
                    'date' => 'required|date_format:Y-m-d',
                ]
            );

            if ($validator->fails()) {
                return response()->json(['error' => $validator->errors()->first()], 422);
            }

            $debit = CustomerDebitNotes::find($id);
            if (!$debit || !$debit->bills || $debit->bills->created_by != Auth::user()->creatorId()) {
                return response()->json(['error' => __('Permission denied.')], 403);
            }

            $bill = Bill::find($debit->bill);
            $debitAmount = floatval($request->amount);
            $billPaid = $bill->getTotal() - $bill->getDue() - $bill->billTotalDebitNote();
            $existingDebits = CustomerDebitNotes::where('bill', $debit->bill)->where('id', '!=', $id)->sum('amount');

            if (($existingDebits + $debitAmount) > $billPaid) {
                return response()->json(['error' => 'Maximum ' . Auth::user()->priceFormat($billPaid - $existingDebits) . ' debit to this bill.'], 400);
            }

            $debit->date = $request->date;
            $debit->amount = $debitAmount;
            $debit->description = $request->description;
            $debit->save();

            return response()->json($debit);
        } else {
            return response()->json(['error' => __('Permission denied.')], 403);
        }
    }

    public function destroy($id)
    {
        if (Auth::user()->can('delete debit note')) {
            $debitNote = CustomerDebitNotes::find($id);
            if (!$debitNote || !$debitNote->bills || $debitNote->bills->created_by != Auth::user()->creatorId()) {
                return response()->json(['error' => __('Permission denied.')], 403);
            }

            $debitNote->delete();
            return response()->json(null, 204);
        } else {
            return response()->json(['error' => __('Permission denied.')], 403);
        }
    }

    private function debitNoteNumber()
    {
        $latest = CustomerDebitNotes::whereHas('bills', function ($query) {
            $query->where('created_by', Auth::user()->creatorId());
        })->latest('debit_id')->first();

        if ($latest == null) {
            return 1;
        } else {
            return $latest->debit_id + 1;
        }
    }
}
