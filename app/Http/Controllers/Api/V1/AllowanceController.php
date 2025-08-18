<?php

namespace App\Http\Controllers\API\v1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Allowance;
use App\Models\AllowanceOption;
use App\Models\Employee;
use Illuminate\Support\Facades\Auth;

class AllowanceController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $allowances = Allowance::where('created_by', $user->creatorId());

        if ($request->has('employee_id')) {
            $allowances->where('employee_id', $request->employee_id);
        }

        return response()->json($allowances->get());
    }

    public function store(Request $request)
    {
        $validator = \Validator::make($request->all(), [
            'employee_id' => 'required|exists:employees,id',
            'allowance_option' => 'required',
            'title' => 'required',
            'amount' => 'required|numeric|min:0',
            'type' => 'required|in:fixed,percentage',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()], 400);
        }

        $allowance = new Allowance();
        $allowance->employee_id = $request->employee_id;
        $allowance->allowance_option = $request->allowance_option;
        $allowance->title = $request->title;
        $allowance->type = $request->type;
        $allowance->amount = $request->amount;
        $allowance->created_by = Auth::user()->creatorId();
        $allowance->save();

        return response()->json(['message' => 'Allowance successfully created.', 'data' => $allowance], 201);
    }

    public function show($id)
    {
        $allowance = Allowance::find($id);
        if (!$allowance || $allowance->created_by != Auth::user()->creatorId()) {
            return response()->json(['error' => 'Allowance not found.'], 404);
        }
        return response()->json($allowance);
    }

    public function update(Request $request, $id)
    {
        $allowance = Allowance::find($id);
        if (!$allowance || $allowance->created_by != Auth::user()->creatorId()) {
            return response()->json(['error' => 'Allowance not found.'], 404);
        }

        $validator = \Validator::make($request->all(), [
            'allowance_option' => 'required',
            'title' => 'required',
            'amount' => 'required|numeric|min:0',
            'type' => 'required|in:fixed,percentage',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()], 400);
        }

        $allowance->allowance_option = $request->allowance_option;
        $allowance->title = $request->title;
        $allowance->type = $request->type;
        $allowance->amount = $request->amount;
        $allowance->save();

        return response()->json(['message' => 'Allowance successfully updated.', 'data' => $allowance]);
    }

    public function destroy($id)
    {
        $allowance = Allowance::find($id);
        if (!$allowance || $allowance->created_by != Auth::user()->creatorId()) {
            return response()->json(['error' => 'Allowance not found.'], 404);
        }

        $allowance->delete();
        return response()->json(['message' => 'Allowance successfully deleted.']);
    }
}
