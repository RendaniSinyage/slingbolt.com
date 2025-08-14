<?php

namespace App\Http\Controllers\API\v1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\OtherPayment;
use App\Models\Employee;
use Illuminate\Support\Facades\Auth;

class OtherPaymentController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $payments = OtherPayment::where('created_by', $user->creatorId());

        if ($request->has('employee_id')) {
            $payments->where('employee_id', $request->employee_id);
        }

        return response()->json($payments->get());
    }

    public function store(Request $request)
    {
        $validator = \Validator::make($request->all(), [
            'employee_id' => 'required|exists:employees,id',
            'title' => 'required',
            'amount' => 'required|numeric|min:0',
            'type' => 'required|in:fixed,percentage',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()], 400);
        }

        $payment = new OtherPayment();
        $payment->employee_id = $request->employee_id;
        $payment->title = $request->title;
        $payment->amount = $request->amount;
        $payment->type = $request->type;
        $payment->created_by = Auth::user()->creatorId();
        $payment->save();

        return response()->json(['message' => 'Other Payment successfully created.', 'data' => $payment], 201);
    }

    public function show($id)
    {
        $payment = OtherPayment::find($id);
        if (!$payment || $payment->created_by != Auth::user()->creatorId()) {
            return response()->json(['error' => 'Other Payment not found.'], 404);
        }
        return response()->json($payment);
    }

    public function update(Request $request, $id)
    {
        $payment = OtherPayment::find($id);
        if (!$payment || $payment->created_by != Auth::user()->creatorId()) {
            return response()->json(['error' => 'Other Payment not found.'], 404);
        }

        $validator = \Validator::make($request->all(), [
            'title' => 'required',
            'amount' => 'required|numeric|min:0',
            'type' => 'required|in:fixed,percentage',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()], 400);
        }

        $payment->title = $request->title;
        $payment->amount = $request->amount;
        $payment->type = $request->type;
        $payment->save();

        return response()->json(['message' => 'Other Payment successfully updated.', 'data' => $payment]);
    }

    public function destroy($id)
    {
        $payment = OtherPayment::find($id);
        if (!$payment || $payment->created_by != Auth::user()->creatorId()) {
            return response()->json(['error' => 'Other Payment not found.'], 404);
        }

        $payment->delete();
        return response()->json(['message' => 'Other Payment successfully deleted.']);
    }
}
