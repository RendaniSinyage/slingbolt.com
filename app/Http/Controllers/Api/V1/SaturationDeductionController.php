<?php

namespace App\Http\Controllers\API\v1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\SaturationDeduction;
use App\Models\Employee;
use Illuminate\Support\Facades\Auth;

class SaturationDeductionController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $deductions = SaturationDeduction::where('created_by', $user->creatorId());

        if ($request->has('employee_id')) {
            $deductions->where('employee_id', $request->employee_id);
        }

        return response()->json($deductions->get());
    }

    public function store(Request $request)
    {
        $validator = \Validator::make($request->all(), [
            'employee_id' => 'required|exists:employees,id',
            'deduction_option' => 'required',
            'title' => 'required',
            'amount' => 'required|numeric|min:0',
            'type' => 'required|in:fixed,percentage',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()], 400);
        }

        $deduction = new SaturationDeduction();
        $deduction->employee_id = $request->employee_id;
        $deduction->deduction_option = $request->deduction_option;
        $deduction->title = $request->title;
        $deduction->amount = $request->amount;
        $deduction->type = $request->type;
        $deduction->created_by = Auth::user()->creatorId();
        $deduction->save();

        return response()->json(['message' => 'Saturation Deduction successfully created.', 'data' => $deduction], 201);
    }

    public function show($id)
    {
        $deduction = SaturationDeduction::find($id);
        if (!$deduction || $deduction->created_by != Auth::user()->creatorId()) {
            return response()->json(['error' => 'Saturation Deduction not found.'], 404);
        }
        return response()->json($deduction);
    }

    public function update(Request $request, $id)
    {
        $deduction = SaturationDeduction::find($id);
        if (!$deduction || $deduction->created_by != Auth::user()->creatorId()) {
            return response()->json(['error' => 'Saturation Deduction not found.'], 404);
        }

        $validator = \Validator::make($request->all(), [
            'deduction_option' => 'required',
            'title' => 'required',
            'amount' => 'required|numeric|min:0',
            'type' => 'required|in:fixed,percentage',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()], 400);
        }

        $deduction->deduction_option = $request->deduction_option;
        $deduction->title = $request->title;
        $deduction->amount = $request->amount;
        $deduction->type = $request->type;
        $deduction->save();

        return response()->json(['message' => 'Saturation Deduction successfully updated.', 'data' => $deduction]);
    }

    public function destroy($id)
    {
        $deduction = SaturationDeduction::find($id);
        if (!$deduction || $deduction->created_by != Auth::user()->creatorId()) {
            return response()->json(['error' => 'Saturation Deduction not found.'], 404);
        }

        $deduction->delete();
        return response()->json(['message' => 'Saturation Deduction successfully deleted.']);
    }
}
