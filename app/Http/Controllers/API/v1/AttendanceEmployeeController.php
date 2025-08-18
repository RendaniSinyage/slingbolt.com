<?php

namespace App\Http\Controllers\API\v1;

use App\Http\Controllers\Controller;
use App\Models\AttendanceEmployee;
use App\Models\Employee;
use App\Models\IpRestrict;
use App\Models\Utility;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AttendanceEmployeeController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        if (Auth::user()->can('manage attendance')) {
            $query = AttendanceEmployee::whereHas('employee', function ($q) {
                $q->where('created_by', Auth::user()->creatorId());
            });

            if ($request->has('employee_id')) {
                $query->where('employee_id', $request->employee_id);
            }
            if ($request->has('start_date') && $request->has('end_date')) {
                $query->whereBetween('date', [$request->start_date, $request->end_date]);
            }

            $attendances = $query->with('employee')->get();
            return response()->json($attendances);
        }

        return response()->json(['error' => __('Permission denied.')], 403);
    }

    /**
     * Store a newly created resource in storage (Manual Entry by Admin).
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        if (Auth::user()->can('create attendance')) {
            $validator = \Validator::make($request->all(), [
                'employee_id' => 'required|exists:employees,id',
                'date' => 'required|date',
                'clock_in' => 'required|date_format:H:i',
                'clock_out' => 'required|date_format:H:i',
            ]);

            if ($validator->fails()) {
                return response()->json(['error' => $validator->errors()->first()], 400);
            }

            // This logic is simplified from the web controller for API context
            $employeeAttendance = new AttendanceEmployee();
            $employeeAttendance->employee_id = $request->employee_id;
            $employeeAttendance->date = $request->date;
            $employeeAttendance->status = 'Present';
            $employeeAttendance->clock_in = $request->clock_in . ':00';
            $employeeAttendance->clock_out = $request->clock_out . ':00';
            $employeeAttendance->created_by = Auth::user()->creatorId();
            $employeeAttendance->save();

            return response()->json($employeeAttendance, 201);
        }
        return response()->json(['error' => __('Permission denied.')], 403);
    }

    /**
     * Employee clocks in.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function clockIn(Request $request)
    {
        $employee = Auth::user()->employee;
        if (!$employee) {
            return response()->json(['error' => 'Only employees can clock in.'], 403);
        }

        // IP Restriction Check
        $settings = Utility::settings();
        if ($settings['ip_restrict'] == 'on') {
            if (empty(IpRestrict::where('ip', $request->ip())->first())) {
                return response()->json(['error' => __('This IP is not allowed.')], 403);
            }
        }

        $todayAttendance = AttendanceEmployee::where('employee_id', $employee->id)->where('date', date('Y-m-d'))->where('clock_out', '00:00:00')->first();

        if ($todayAttendance) {
            return response()->json(['error' => __('You have already clocked in today.')], 422);
        }

        $startTime = Utility::getValByName('company_start_time');
        $date = date("Y-m-d");
        $time = date("H:i:s");

        $totalLateSeconds = strtotime($time) - strtotime($startTime);
        $late = '00:00:00';
        if ($totalLateSeconds > 0) {
            $hours = floor($totalLateSeconds / 3600);
            $mins = floor($totalLateSeconds / 60 % 60);
            $secs = floor($totalLateSeconds % 60);
            $late = sprintf('%02d:%02d:%02d', $hours, $mins, $secs);
        }

        $attendance = AttendanceEmployee::create([
            'employee_id' => $employee->id,
            'date' => $date,
            'status' => 'Present',
            'clock_in' => $time,
            'clock_out' => '00:00:00',
            'late' => $late,
            'created_by' => Auth::user()->creatorId(),
        ]);

        return response()->json(['message' => 'Clocked in successfully.', 'data' => $attendance], 201);
    }

    /**
     * Employee clocks out.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function clockOut(Request $request)
    {
        $employee = Auth::user()->employee;
        if (!$employee) {
            return response()->json(['error' => 'Only employees can clock out.'], 403);
        }

        $attendance = AttendanceEmployee::where('employee_id', $employee->id)->where('date', date('Y-m-d'))->where('clock_out', '00:00:00')->first();

        if (!$attendance) {
            return response()->json(['error' => __('You have not clocked in yet or have already clocked out.')], 422);
        }

        $endTime = Utility::getValByName('company_end_time');
        $date = date("Y-m-d");
        $time = date("H:i:s");

        // Early Leaving
        $earlyLeaving = '00:00:00';
        if (strtotime($time) < strtotime($endTime)) {
            $totalEarlyLeavingSeconds = strtotime($endTime) - strtotime($time);
            $hours = floor($totalEarlyLeavingSeconds / 3600);
            $mins = floor($totalEarlyLeavingSeconds / 60 % 60);
            $secs = floor($totalEarlyLeavingSeconds % 60);
            $earlyLeaving = sprintf('%02d:%02d:%02d', $hours, $mins, $secs);
        }

        // Overtime
        $overtime = '00:00:00';
        if (strtotime($time) > strtotime($endTime)) {
            $totalOvertimeSeconds = strtotime($time) - strtotime($endTime);
            $hours = floor($totalOvertimeSeconds / 3600);
            $mins = floor($totalOvertimeSeconds / 60 % 60);
            $secs = floor($totalOvertimeSeconds % 60);
            $overtime = sprintf('%02d:%02d:%02d', $hours, $mins, $secs);
        }

        $attendance->update([
            'clock_out' => $time,
            'early_leaving' => $earlyLeaving,
            'overtime' => $overtime,
        ]);

        return response()->json(['message' => 'Clocked out successfully.', 'data' => $attendance]);
    }
}
