<?php

namespace App\Http\Controllers\API\v1;

use App\Http\Controllers\Controller;
use App\Http\Resources\LeaveResource;
use App\Models\Leave;
use App\Models\LeaveType;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LeaveController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        if (Auth::user()->can('manage leave')) {
            $user = Auth::user();
            if ($user->type == 'company' || $user->type == 'HR') {
                $leaves = Leave::where('created_by', $user->creatorId())->with(['leaveType', 'employees'])->get();
            } else {
                $employee = Employee::where('user_id', $user->id)->first();
                $leaves = Leave::where('employee_id', $employee->id)->with(['leaveType', 'employees'])->get();
            }
            return LeaveResource::collection($leaves);
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
        if (Auth::user()->can('create leave')) {
            $validator = \Validator::make($request->all(), [
                'leave_type_id' => 'required|exists:leave_types,id',
                'start_date' => 'required|date',
                'end_date' => 'required|date|after_or_equal:start_date',
                'leave_reason' => 'required|string',
                'remark' => 'nullable|string',
                'employee_id' => 'required_if:auth_user_type,company,HR|exists:employees,id',
            ]);

            $validator->addImplicitExtension('auth_user_type', function($attribute, $value, $parameters) {
                return Auth::user()->type;
            });

            if ($validator->fails()) {
                return response()->json(['error' => $validator->errors()->first()], 400);
            }

            $leave_type = LeaveType::find($request->leave_type_id);
            $startDate = new \DateTime($request->start_date);
            $endDate = new \DateTime($request->end_date);
            $total_leave_days = $startDate->diff($endDate)->days + 1;

            if ($leave_type->days < $total_leave_days) {
                return response()->json(['error' => __('Leave request exceeds the allowed days for this type.')], 422);
            }

            $leave = new Leave();
            $leave->employee_id = (Auth::user()->type == 'company' || Auth::user()->type == 'HR') ? $request->employee_id : Auth::user()->employee->id;
            $leave->leave_type_id = $request->leave_type_id;
            $leave->applied_on = date('Y-m-d');
            $leave->start_date = $request->start_date;
            $leave->end_date = $request->end_date;
            $leave->total_leave_days = $total_leave_days;
            $leave->leave_reason = $request->leave_reason;
            $leave->remark = $request->remark;
            $leave->status = 'Pending';
            $leave->created_by = Auth::user()->creatorId();
            $leave->save();

            return new LeaveResource($leave);
        }

        return response()->json(['error' => __('Permission denied.')], 403);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Leave  $leave
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Leave $leave)
    {
        if (Auth::user()->can('manage leave') && $leave->created_by == Auth::user()->creatorId()) {
            return new LeaveResource($leave->load(['leaveType', 'employees']));
        }

        return response()->json(['error' => __('Permission denied.')], 403);
    }

    public function destroy(Leave $leave)
    {
        if (Auth::user()->can('delete leave') && $leave->created_by == Auth::user()->creatorId()) {
            $leave->delete();
            return response()->json(null, 204);
        }

        return response()->json(['error' => __('Permission denied.')], 403);
    }

    public function approve(Request $request, Leave $leave)
    {
        if (Auth::user()->can('edit leave') && $leave->created_by == Auth::user()->creatorId()) {
            $leave->status = 'Approved';
            $leave->save();
            return response()->json(['message' => 'Leave approved successfully.', 'data' => new LeaveResource($leave)]);
        }
        return response()->json(['error' => __('Permission denied.')], 403);
    }

    public function reject(Request $request, Leave $leave)
    {
        if (Auth::user()->can('edit leave') && $leave->created_by == Auth::user()->creatorId()) {
            $leave->status = 'Rejected';
            $leave->save();
            return response()->json(['message' => 'Leave rejected successfully.', 'data' => new LeaveResource($leave)]);
        }
        return response()->json(['error' => __('Permission denied.')], 403);
    }
}
