<?php

namespace App\Http\Controllers\API\v1;

use App\Http\Controllers\Controller;
use App\Http\Resources\EmployeeResource;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class EmployeeController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $employees = Employee::where('created_by', '=', $user->creatorId())->with(['designation', 'branch', 'department'])->get();

        return EmployeeResource::collection($employees);
    }

    public function show($id)
    {
        $user = request()->user();
        $employee = Employee::with(['designation', 'branch', 'department'])
            ->where('created_by', '=', $user->creatorId())
            ->find($id);

        if (!$employee) {
            return response()->json(['error' => 'Employee not found or you do not have permission to view it'], 404);
        }

        return new EmployeeResource($employee);
    }

    public function store(Request $request)
    {
        $validator = Validator::make(
            $request->all(), [
                'name' => 'required|max:120',
                'dob' => 'required',
                'gender' => 'required',
                'phone' => 'required|numeric',
                'address' => 'required',
                'email' => 'required|unique:users',
                'password' => 'required',
                'branch_id' => 'required',
                'department_id' => 'required',
                'designation_id' => 'required',
            ]
        );
        if($validator->fails())
        {
            return response()->json(['error' => $validator->errors()->first()], 400);
        }

        $user = request()->user();
        $ownerId = $user->ownerId();
        $creatorId = $user->creatorId();

        $newUser = new User();
        $newUser->name = $request->name;
        $newUser->email = $request->email;
        $newUser->password = Hash::make($request->password);
        $newUser->type = 'employee';
        $newUser->lang = 'en';
        $newUser->created_by = $creatorId;
        $newUser->save();

        $employee = new Employee();
        $employee->user_id = $newUser->id;
        $employee->name = $request->name;
        $employee->dob = $request->dob;
        $employee->gender = $request->gender;
        $employee->phone = $request->phone;
        $employee->address = $request->address;
        $employee->email = $request->email;
        $employee->password = Hash::make($request->password);
        $employee->employee_id = $this->employeeNumber();
        $employee->branch_id = $request->branch_id;
        $employee->department_id = $request->department_id;
        $employee->designation_id = $request->designation_id;
        $employee->company_doj = $request->company_doj;
        $employee->created_by = $ownerId;
        $employee->save();

        return new EmployeeResource($employee);
    }

    public function update(Request $request, $id)
    {
        $employee = Employee::where('created_by', request()->user()->ownerId())->find($id);
        if(!$employee) {
            return response()->json(['error' => 'Employee not found.'], 404);
        }

        $validator = Validator::make(
            $request->all(), [
                'name' => 'required|max:120',
                'dob' => 'required',
                'gender' => 'required',
                'phone' => 'required|numeric',
                'address' => 'required',
                'branch_id' => 'required',
                'department_id' => 'required',
                'designation_id' => 'required',
            ]
        );
        if($validator->fails())
        {
            return response()->json(['error' => $validator->errors()->first()], 400);
        }

        $user = User::find($employee->user_id);
        if($user) {
            $user->name = $request->name;
            $user->save();
        }

        $employee->name = $request->name;
        $employee->dob = $request->dob;
        $employee->gender = $request->gender;
        $employee->phone = $request->phone;
        $employee->address = $request->address;
        $employee->branch_id = $request->branch_id;
        $employee->department_id = $request->department_id;
        $employee->designation_id = $request->designation_id;
        $employee->company_doj = $request->company_doj;
        $employee->save();

        return new EmployeeResource($employee);
    }

    public function destroy($id)
    {
        $employee = Employee::where('created_by', request()->user()->ownerId())->find($id);
        if(!$employee) {
            return response()->json(['error' => 'Employee not found.'], 404);
        }

        $user = User::find($employee->user_id);
        if($user) {
            $user->delete();
        }

        $employee->delete();

        return response()->json(['success' => 'Employee successfully deleted.']);
    }

    function employeeNumber()
    {
        $latest = Employee::latest()->first();
        if(!$latest)
        {
            return 1;
        }

        return $latest->employee_id + 1;
    }
}
