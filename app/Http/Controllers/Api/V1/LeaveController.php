<?php

namespace App\Http\Controllers\API\v1;

use App\Http\Controllers\Controller;
use App\Models\Leave;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class LeaveController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        if ($user->type == 'company' || $user->type == 'HR') {
            $leaves = Leave::where('created_by', $user->creatorId())->with(['leaveType', 'employees'])->get();
        } else {
            $employee = Employee::where('user_id', $user->id)->first();
            if (!$employee) {
                return response()->json(['error' => 'Employee record not found for this user.'], 404);
            }
            $leaves = Leave::where('employee_id', $employee->id)->with(['leaveType', 'employees'])->get();
        }
        return response()->json($leaves);
    }

    public function show($id)
    {
        $user = request()->user();
        $leave = Leave::with(['leaveType', 'employees'])->find($id);

        if (!$leave || $leave->created_by != $user->creatorId()) {
            return response()->json(['error' => 'Leave not found or permission denied.'], 404);
        }
        return response()->json($leave);
    }

    public function store(Request $request)
    {
        $user = $request->user();
        $validator = Validator::make($request->all(), [
            'leave_type_id' => 'required|exists:leave_types,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'leave_reason' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()], 400);
        }

        $employee = Employee::where('user_id', $user->id)->first();
        if (!$employee) {
            return response()->json(['error' => 'Employee record not found for this user.'], 404);
        }

        $startDate = new \DateTime($request->start_date);
        $endDate = new \DateTime($request->end_date);
        $endDate->add(new \DateInterval('P1D'));
        $total_leave_days = !empty($startDate->diff($endDate)) ? $startDate->diff($endDate)->days : 0;

        $leave = new Leave();
        $leave->employee_id = $employee->id;
        $leave->leave_type_id = $request->leave_type_id;
        $leave->applied_on = date('Y-m-d');
        $leave->start_date = $request->start_date;
        $leave->end_date = $request->end_date;
        $leave->total_leave_days = $total_leave_days;
        $leave->leave_reason = $request->leave_reason;
        $leave->remark = $request->remark ?? '';
        $leave->status = 'Pending';
        $leave->created_by = $user->creatorId();
        $leave->save();

        return response()->json($leave, 201);
    }

    public function update(Request $request, $id)
    {
        // For simplicity, we'll focus on status changes via approve/reject.
        // A full update might be more complex.
        return response()->json(['error' => 'Not implemented'], 501);
    }

    public function destroy($id)
    {
        $user = request()->user();
        $leave = Leave::where('created_by', $user->creatorId())->find($id);

        if (!$leave) {
            return response()->json(['error' => 'Leave not found.'], 404);
        }

        $leave->delete();
        return response()->json(['success' => 'Leave successfully deleted.'], 200);
    }

    public function approve(Request $request, $id)
    {
        return $this->changeLeaveStatus($request, $id, 'Approved');
    }

    public function reject(Request $request, $id)
    {
        return $this->changeLeaveStatus($request, $id, 'Reject');
    }

    private function changeLeaveStatus(Request $request, $id, $status)
    {
        $user = $request->user();
        if ($user->type != 'company' && $user->type != 'HR') {
            return response()->json(['error' => 'Permission denied.'], 403);
        }

        $leave = Leave::where('created_by', $user->creatorId())->find($id);
        if (!$leave) {
            return response()->json(['error' => 'Leave not found.'], 404);
        }

        $leave->status = $status;
        $leave->save();

        return response()->json($leave);
    }
}
