<?php

namespace App\Http\Controllers\API\v1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\PaySlip;
use App\Models\Employee;
use App\Models\PayslipType;
use App\Models\Utility;
use Illuminate\Support\Facades\Auth;

class PayslipController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $employee = Employee::where('user_id', '=', $user->id)->first();

        if (!$employee) {
            return response()->json(['error' => 'Employee not found.'], 404);
        }

        $payslip_type = PayslipType::where('created_by', '=', $user->creatorId())->get();
        $payslips = PaySlip::where('employee_id', '=', $employee->id)
                           ->where('salary_month', '=', $request->month)
                           ->get();

        $data = [
            'employee' => $employee,
            'payslip_type' => $payslip_type,
            'payslips' => $payslips,
        ];

        return response()->json(['data' => $data]);
    }
}
