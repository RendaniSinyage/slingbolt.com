<?php

namespace App\Http\Controllers\API\v1;

use App\Http\Controllers\Controller;
use App\Models\CustomerCreditNotes;
use App\Models\Invoice;
use App\Models\InvoiceProduct;
use App\Models\ProductService;
use App\Models\Utility;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class CustomerCreditNotesController extends Controller
{
    public function index()
    {
        if (Auth::user()->can('manage credit note')) {
            $customcreditNotes = CustomerCreditNotes::whereHas('invoices', function ($query) {
                $query->where('created_by', Auth::user()->creatorId());
            })->with(['invoices'])->get();
            return response()->json($customcreditNotes);
        } else {
            return response()->json(['error' => __('Permission denied.')], 403);
        }
    }

    public function store(Request $request)
    {
        if (Auth::user()->can('create credit note')) {
            $validator = Validator::make(
                $request->all(),
                [
                    'invoice_id' => 'required|numeric',
                    'amount' => 'required|numeric|gt:0',
                    'date' => 'required|date_format:Y-m-d',
                ]
            );
            if ($validator->fails()) {
                return response()->json(['error' => $validator->errors()->first()], 422);
            }
            $invoice = Invoice::where('id', $request->invoice_id)->where('created_by', Auth::user()->creatorId())->first();
            if (!$invoice) {
                return response()->json(['error' => __('Invoice not found.')], 404);
            }

            $creditAmount = floatval($request->amount);
            $invoicePaid = $invoice->getTotal() - $invoice->getDue() - $invoice->invoiceTotalCreditNote();
            $customerCreditNotes = CustomerCreditNotes::where('invoice', $request->invoice_id)->sum('amount');

            if ($creditAmount > $invoicePaid || ($customerCreditNotes + $creditAmount) > $invoicePaid) {
                return response()->json(['error' => 'Maximum ' . Auth::user()->priceFormat($invoicePaid - $customerCreditNotes) . ' credit limit of this invoice.'], 400);
            }

            $credit = new CustomerCreditNotes();
            $credit->credit_id = $this->creditNoteNumber();
            $credit->invoice = $request->invoice_id;
            $credit->date = $request->date;
            $credit->amount = $creditAmount;
            $credit->description = $request->description;
            $credit->save();

            return response()->json($credit, 201);
        } else {
            return response()->json(['error' => __('Permission denied.')], 403);
        }
    }

    public function show($id)
    {
        if (Auth::user()->can('manage credit note')) {
            $creditNote = CustomerCreditNotes::with('invoices')->where('id', $id)->whereHas('invoices', function ($query) {
                $query->where('created_by', Auth::user()->creatorId());
            })->first();

            if ($creditNote) {
                return response()->json($creditNote);
            } else {
                return response()->json(['error' => __('Credit note not found.')], 404);
            }
        } else {
            return response()->json(['error' => __('Permission denied.')], 403);
        }
    }

    public function update(Request $request, $id)
    {
        if (Auth::user()->can('edit credit note')) {
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

            $credit = CustomerCreditNotes::find($id);
            if (!$credit || !$credit->invoices || $credit->invoices->created_by != Auth::user()->creatorId()) {
                return response()->json(['error' => __('Permission denied.')], 403);
            }

            $invoice = Invoice::find($credit->invoice);
            $creditAmount = floatval($request->amount);
            $invoicePaid = $invoice->getTotal() - $invoice->getDue() - $invoice->invoiceTotalCreditNote();
            $existingCredits = CustomerCreditNotes::where('invoice', $credit->invoice)->where('id', '!=', $id)->sum('amount');

            if (($existingCredits + $creditAmount) > $invoicePaid) {
                return response()->json(['error' => 'Maximum ' . Auth::user()->priceFormat($invoicePaid - $existingCredits) . ' credit to this invoice.'], 400);
            }

            $credit->date = $request->date;
            $credit->amount = $creditAmount;
            $credit->description = $request->description;
            $credit->save();

            return response()->json($credit);
        } else {
            return response()->json(['error' => __('Permission denied.')], 403);
        }
    }

    public function destroy($id)
    {
        if (Auth::user()->can('delete credit note')) {
            $creditNote = CustomerCreditNotes::find($id);
            if (!$creditNote || !$creditNote->invoices || $creditNote->invoices->created_by != Auth::user()->creatorId()) {
                return response()->json(['error' => __('Permission denied.')], 403);
            }

            $creditNote->delete();
            return response()->json(null, 204);
        } else {
            return response()->json(['error' => __('Permission denied.')], 403);
        }
    }

    private function creditNoteNumber()
    {
        $latest = CustomerCreditNotes::whereHas('invoices', function ($query) {
            $query->where('created_by', Auth::user()->creatorId());
        })->latest('credit_id')->first();
        if ($latest == null) {
            return 1;
        } else {
            return $latest->credit_id + 1;
        }
    }
}
