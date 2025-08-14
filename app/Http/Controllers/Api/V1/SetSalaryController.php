<?php

namespace App\Http\Controllers\API\v1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Employee;
use App\Models\PayslipType;
use App\Models\Allowance;
use App\Models\Commission;
use App\Models\Loan;
use App\Models\SaturationDeduction;
use App\Models\OtherPayment;
use App\Models\Overtime;
use Illuminate\Support\Facades\Auth;

class SetSalaryController extends Controller
{
    public function show($employeeId)
    {
        $user = Auth::user();
        $employee = Employee::where('created_by', $user->creatorId())->find($employeeId);

        if (!$employee) {
            return response()->json(['error' => 'Employee not found.'], 404);
        }

        $payslip_type = PayslipType::where('created_by', $user->creatorId())->get();
        $allowances = Allowance::where('employee_id', $employeeId)->get();
        $commissions = Commission::where('employee_id', $employeeId)->get();
        $loans = Loan::where('employee_id', $employeeId)->get();
        $saturationdeductions = SaturationDeduction::where('employee_id', $employeeId)->get();
        $otherpayments = OtherPayment::where('employee_id', $employeeId)->get();
        $overtimes = Overtime::where('employee_id', $employeeId)->get();

        $data = [
            'employee' => $employee,
            'payslip_type' => $payslip_type,
            'allowances' => $allowances,
            'commissions' => $commissions,
            'loans' => $loans,
            'saturation_deductions' => $saturationdeductions,
            'other_payments' => $otherpayments,
            'overtimes' => $overtimes,
        ];

        return response()->json(['data' => $data]);
    }

    public function update(Request $request, $employeeId)
    {
        $user = Auth::user();
        $employee = Employee::where('created_by', $user->creatorId())->find($employeeId);

        if (!$employee) {
            return response()->json(['error' => 'Employee not found.'], 404);
        }

        $validator = \Validator::make($request->all(), [
            'salary_type' => 'required',
            'salary' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()], 400);
        }

        $employee->fill($request->all())->save();

        return response()->json(['message' => 'Employee Salary Updated.', 'data' => $employee]);
    }
}
