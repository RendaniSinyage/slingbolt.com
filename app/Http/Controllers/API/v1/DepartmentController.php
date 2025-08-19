<?php

namespace App\Http\Controllers\API\v1;

use App\Http\Controllers\Controller;
use App\Http\Resources\DepartmentResource;
use App\Models\Department;
use App\Models\Designation;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DepartmentController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        if (Auth::user()->can('manage department')) {
            $departments = Department::where('created_by', Auth::user()->creatorId())->with('branch')->get();
            return DepartmentResource::collection($departments);
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
        if (Auth::user()->can('create department')) {
            $validator = \Validator::make($request->all(), [
                'branch_id' => 'required|exists:branches,id',
                'name' => 'required|string|max:255',
            ]);

            if ($validator->fails()) {
                return response()->json(['error' => $validator->errors()->first()], 400);
            }

            $department = new Department();
            $department->branch_id = $request->branch_id;
            $department->name = $request->name;
            $department->created_by = Auth::user()->creatorId();
            $department->save();

            return new DepartmentResource($department);
        }

        return response()->json(['error' => __('Permission denied.')], 403);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Department  $department
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Department $department)
    {
        if (Auth::user()->can('manage department') && $department->created_by == Auth::user()->creatorId()) {
            return new DepartmentResource($department->load('branch'));
        }

        return response()->json(['error' => __('Permission denied.')], 403);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Department  $department
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, Department $department)
    {
        if (Auth::user()->can('edit department') && $department->created_by == Auth::user()->creatorId()) {
            $validator = \Validator::make($request->all(), [
                'branch_id' => 'required|exists:branches,id',
                'name' => 'required|string|max:255',
            ]);

            if ($validator->fails()) {
                return response()->json(['error' => $validator->errors()->first()], 400);
            }

            $department->branch_id = $request->branch_id;
            $department->name = $request->name;
            $department->save();

            return new DepartmentResource($department);
        }

        return response()->json(['error' => __('Permission denied.')], 403);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Department  $department
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Department $department)
    {
        if (Auth::user()->can('delete department') && $department->created_by == Auth::user()->creatorId()) {
            if (Employee::where('department_id', $department->id)->exists()) {
                return response()->json(['error' => __('This department has employees. Please remove the employee from this department.')], 422);
            }

            Designation::where('department_id', $department->id)->delete();
            $department->delete();

            return response()->json(null, 204);
        }

        return response()->json(['error' => __('Permission denied.')], 403);
    }
}
