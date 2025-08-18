<?php

namespace App\Http\Controllers\API\v1;

use App\Http\Controllers\Controller;
use App\Models\Transfer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TransferController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        if (Auth::user()->can('manage transfer')) {
            if (Auth::user()->type == 'Employee') {
                $employee = Auth::user()->employee;
                $transfers = Transfer::where('created_by', Auth::user()->creatorId())->where('employee_id', $employee->id)->with(['employee', 'branch', 'department'])->get();
            } else {
                $transfers = Transfer::where('created_by', Auth::user()->creatorId())->with(['employee', 'branch', 'department'])->get();
            }
            return response()->json($transfers);
        }

        return response()->json(['error' => __('Permission denied.')], 403);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        if (Auth::user()->can('create transfer')) {
            $validator = \Validator::make($request->all(), [
                'employee_id' => 'required|exists:employees,id',
                'branch_id' => 'required|exists:branches,id',
                'department_id' => 'required|exists:departments,id',
                'transfer_date' => 'required|date',
                'description' => 'nullable|string',
            ]);

            if ($validator->fails()) {
                return response()->json(['error' => $validator->errors()->first()], 400);
            }

            $transfer = new Transfer();
            $transfer->employee_id = $request->employee_id;
            $transfer->branch_id = $request->branch_id;
            $transfer->department_id = $request->department_id;
            $transfer->transfer_date = $request->transfer_date;
            $transfer->description = $request->description;
            $transfer->created_by = Auth::user()->creatorId();
            $transfer->save();

            return response()->json($transfer, 201);
        }

        return response()->json(['error' => __('Permission denied.')], 403);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Transfer  $transfer
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Transfer $transfer)
    {
        if (Auth::user()->can('manage transfer') && $transfer->created_by == Auth::user()->creatorId()) {
            return response()->json($transfer->load(['employee', 'branch', 'department']));
        }

        return response()->json(['error' => __('Permission denied.')], 403);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Transfer  $transfer
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, Transfer $transfer)
    {
        if (Auth::user()->can('edit transfer') && $transfer->created_by == Auth::user()->creatorId()) {
            $validator = \Validator::make($request->all(), [
                'employee_id' => 'required|exists:employees,id',
                'branch_id' => 'required|exists:branches,id',
                'department_id' => 'required|exists:departments,id',
                'transfer_date' => 'required|date',
                'description' => 'nullable|string',
            ]);

            if ($validator->fails()) {
                return response()->json(['error' => $validator->errors()->first()], 400);
            }

            $transfer->employee_id = $request->employee_id;
            $transfer->branch_id = $request->branch_id;
            $transfer->department_id = $request->department_id;
            $transfer->transfer_date = $request->transfer_date;
            $transfer->description = $request->description;
            $transfer->save();

            return response()->json($transfer);
        }

        return response()->json(['error' => __('Permission denied.')], 403);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Transfer  $transfer
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Transfer $transfer)
    {
        if (Auth::user()->can('delete transfer') && $transfer->created_by == Auth::user()->creatorId()) {
            $transfer->delete();
            return response()->json(null, 204);
        }

        return response()->json(['error' => __('Permission denied.')], 403);
    }
}
