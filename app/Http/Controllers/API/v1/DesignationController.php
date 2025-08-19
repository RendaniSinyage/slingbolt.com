<?php

namespace App\Http\Controllers\API\v1;

use App\Http\Controllers\Controller;
use App\Http\Resources\DesignationResource;
use App\Models\Department;
use App\Models\Designation;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DesignationController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        if (Auth::user()->can('manage designation')) {
            $designations = Designation::where('created_by', Auth::user()->creatorId())->with(['department', 'branch'])->get();
            return DesignationResource::collection($designations);
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
        if (Auth::user()->can('create designation')) {
            $validator = \Validator::make($request->all(), [
                'department_id' => 'required|exists:departments,id',
                'name' => 'required|string|max:255',
            ]);

            if ($validator->fails()) {
                return response()->json(['error' => $validator->errors()->first()], 400);
            }

            $department = Department::find($request->department_id);
            if(!$department || $department->created_by != Auth::user()->creatorId()){
                 return response()->json(['error' => __('Invalid department.')], 400);
            }

            $designation = new Designation();
            $designation->department_id = $request->department_id;
            $designation->name = $request->name;
            $designation->branch_id = $department->branch_id;
            $designation->created_by = Auth::user()->creatorId();
            $designation->save();

            return new DesignationResource($designation);
        }

        return response()->json(['error' => __('Permission denied.')], 403);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Designation  $designation
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Designation $designation)
    {
        if (Auth::user()->can('manage designation') && $designation->created_by == Auth::user()->creatorId()) {
            return new DesignationResource($designation->load(['department', 'branch']));
        }

        return response()->json(['error' => __('Permission denied.')], 403);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Designation  $designation
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, Designation $designation)
    {
        if (Auth::user()->can('edit designation') && $designation->created_by == Auth::user()->creatorId()) {
            $validator = \Validator::make($request->all(), [
                'department_id' => 'required|exists:departments,id',
                'name' => 'required|string|max:255',
            ]);

            if ($validator->fails()) {
                return response()->json(['error' => $validator->errors()->first()], 400);
            }

            $department = Department::find($request->department_id);
            if(!$department || $department->created_by != Auth::user()->creatorId()){
                 return response()->json(['error' => __('Invalid department.')], 400);
            }

            $designation->department_id = $request->department_id;
            $designation->name = $request->name;
            $designation->branch_id = $department->branch_id;
            $designation->save();

            return new DesignationResource($designation);
        }

        return response()->json(['error' => __('Permission denied.')], 403);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Designation  $designation
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Designation $designation)
    {
        if (Auth::user()->can('delete designation') && $designation->created_by == Auth::user()->creatorId()) {
            if (Employee::where('designation_id', $designation->id)->exists()) {
                return response()->json(['error' => __('This designation has employees. Please remove the employee from this designation.')], 422);
            }

            $designation->delete();

            return response()->json(null, 204);
        }

        return response()->json(['error' => __('Permission denied.')], 403);
    }
}
