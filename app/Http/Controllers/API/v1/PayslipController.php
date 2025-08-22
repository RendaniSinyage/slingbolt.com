<?php

namespace App\Http\Controllers\API\v1;

use App\Http\Controllers\Controller;
use App\Models\PaySlip;
use App\Models\Employee;
use Illuminate\Http\Request;
use App\Http\Resources\PayslipResource;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Auth;

class PayslipController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        if (Gate::denies('manage pay slip')) {
            return response()->json(['error' => 'Permission denied.'], 403);
        }

        $query = PaySlip::where('created_by', '=', Auth::user()->creatorId());

        if ($request->has('month')) {
            $query->where('salary_month', 'like', '%' . $request->month . '%');
        }
        if ($request->has('year')) {
            $query->where('salary_month', 'like', $request->year . '%');
        }

        $payslips = $query->get();

        return PayslipResource::collection($payslips);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        if (Gate::denies('create pay slip')) {
            return response()->json(['error' => 'Permission denied.'], 403);
        }

        $validator = \Validator::make(
            $request->all(), [
                'month' => 'required',
                'year' => 'required',
            ]
        );

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $month = $request->month;
        $year = $request->year;

        $formate_month_year = $year . '-' . $month;
        $validatePaysilp = PaySlip::where('salary_month', '=', $formate_month_year)->where('created_by', Auth::user()->creatorId())->pluck('employee_id');

        $employees = Employee::where('created_by', Auth::user()->creatorId())->where('company_doj', '<=', date($year . '-' . $month . '-t'))->whereNotIn('id', $validatePaysilp)->get();

        if ($employees->isEmpty()) {
            return response()->json(['error' => 'Payslips already created for this month.'], 422);
        }

        $payslips = [];
        foreach ($employees as $employee) {
            $payslipEmployee = new PaySlip();
            $payslipEmployee->employee_id = $employee->id;
            $payslipEmployee->net_payble = $employee->get_net_salary();
            $payslipEmployee->salary_month = $formate_month_year;
            $payslipEmployee->status = 0;
            $payslipEmployee->basic_salary = !empty($employee->salary) ? $employee->salary : 0;
            $payslipEmployee->allowance = Employee::allowance($employee->id);
            $payslipEmployee->commission = Employee::commission($employee->id);
            $payslipEmployee->loan = Employee::loan($employee->id);
            $payslipEmployee->saturation_deduction = Employee::saturation_deduction($employee->id);
            $payslipEmployee->other_payment = Employee::other_payment($employee->id);
            $payslipEmployee->overtime = Employee::overtime($employee->id);
            $payslipEmployee->created_by = Auth::user()->creatorId();
            $payslipEmployee->save();
            $payslips[] = $payslipEmployee;
        }

        return PayslipResource::collection($payslips)->additional(['message' => 'Payslips successfully created.']);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\PaySlip  $paySlip
     * @return \Illuminate\Http\Response
     */
    public function show(PaySlip $paySlip)
    {
        if (Gate::denies('manage pay slip')) {
            return response()->json(['error' => 'Permission denied.'], 403);
        }

        if ($paySlip->created_by != Auth::user()->creatorId()) {
            return response()->json(['error' => 'Permission denied.'], 403);
        }

        return new PayslipResource($paySlip->load('employee'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\PaySlip  $paySlip
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, PaySlip $paySlip)
    {
        // This is handled by the editEmployee method in the web controller
        // and is not suitable for a standard REST API update.
        return response()->json(['error' => 'Not implemented.'], 501);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\PaySlip  $paySlip
     * @return \Illuminate\Http\Response
     */
    public function destroy(PaySlip $paySlip)
    {
        if (Gate::denies('delete pay slip')) {
            return response()->json(['error' => 'Permission denied.'], 403);
        }

        if ($paySlip->created_by != Auth::user()->creatorId()) {
            return response()->json(['error' => 'Permission denied.'], 403);
        }

        $paySlip->delete();

        return response()->json(['message' => 'Payslip successfully deleted.']);
    }

    public function pdf($id, $month)
    {
        $payslip  = PaySlip::where('employee_id', $id)->where('salary_month', $month)->where('created_by', Auth::user()->creatorId())->first();
        if (!$payslip) {
            return response()->json(['error' => 'Payslip not found.'], 404);
        }

        $employee = Employee::find($payslip->employee_id);

        if (Gate::denies('show pay slip', $payslip)) {
            return response()->json(['error' => 'Permission denied.'], 403);
        }

        $payslipDetail = \App\Models\Utility::employeePayslipDetail($id, $month);

        $html = view('payslip.pdf', compact('payslip', 'employee', 'payslipDetail'))->render();
        $pdf = \Spatie\Browsershot\Browsershot::html($html)->setChromeExecutablePath(config('browsershot.chrome_executable_path'))->margins(0, 0, 0, 0)->pdf();

        return response($pdf, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . $employee->name . ' ' . $month . '.pdf"',
        ]);
    }
}
