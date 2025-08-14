<?php

namespace App\Http\Controllers\API\v1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Loan;
use App\Models\Employee;
use Illuminate\Support\Facades\Auth;

class LoanController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $loans = Loan::where('created_by', $user->creatorId());

        if ($request->has('employee_id')) {
            $loans->where('employee_id', $request->employee_id);
        }

        return response()->json($loans->get());
    }

    public function store(Request $request)
    {
        $validator = \Validator::make($request->all(), [
            'employee_id' => 'required|exists:employees,id',
            'loan_option' => 'required',
            'title' => 'required',
            'amount' => 'required|numeric|min:0',
            'reason' => 'required',
            'type' => 'required|in:fixed,percentage',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()], 400);
        }

        $loan = new Loan();
        $loan->employee_id = $request->employee_id;
        $loan->loan_option = $request->loan_option;
        $loan->title = $request->title;
        $loan->amount = $request->amount;
        $loan->reason = $request->reason;
        $loan->type = $request->type;
        $loan->created_by = Auth::user()->creatorId();
        $loan->save();

        return response()->json(['message' => 'Loan successfully created.', 'data' => $loan], 201);
    }

    public function show($id)
    {
        $loan = Loan::find($id);
        if (!$loan || $loan->created_by != Auth::user()->creatorId()) {
            return response()->json(['error' => 'Loan not found.'], 404);
        }
        return response()->json($loan);
    }

    public function update(Request $request, $id)
    {
        $loan = Loan::find($id);
        if (!$loan || $loan->created_by != Auth::user()->creatorId()) {
            return response()->json(['error' => 'Loan not found.'], 404);
        }

        $validator = \Validator::make($request->all(), [
            'loan_option' => 'required',
            'title' => 'required',
            'amount' => 'required|numeric|min:0',
            'reason' => 'required',
            'type' => 'required|in:fixed,percentage',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()], 400);
        }

        $loan->loan_option = $request->loan_option;
        $loan->title = $request->title;
        $loan->amount = $request->amount;
        $loan->reason = $request->reason;
        $loan->type = $request->type;
        $loan->save();

        return response()->json(['message' => 'Loan successfully updated.', 'data' => $loan]);
    }

    public function destroy($id)
    {
        $loan = Loan::find($id);
        if (!$loan || $loan->created_by != Auth::user()->creatorId()) {
            return response()->json(['error' => 'Loan not found.'], 404);
        }

        $loan->delete();
        return response()->json(['message' => 'Loan successfully deleted.']);
    }
}
