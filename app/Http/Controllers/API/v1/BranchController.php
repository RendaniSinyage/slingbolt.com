<?php

namespace App\Http\Controllers\API\v1;

use App\Http\Controllers\Controller;
use App\Http\Resources\BranchResource;
use App\Models\Branch;
use App\Models\Department;
use App\Models\Designation;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BranchController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        if (Auth::user()->can('manage branch')) {
            $branches = Branch::where('created_by', Auth::user()->creatorId())->get();
            return BranchResource::collection($branches);
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
        if (Auth::user()->can('create branch')) {
            $validator = \Validator::make($request->all(), ['name' => 'required|string|max:255']);

            if ($validator->fails()) {
                return response()->json(['error' => $validator->errors()->first()], 400);
            }

            $branch = new Branch();
            $branch->name = $request->name;
            $branch->created_by = Auth::user()->creatorId();
            $branch->save();

            event(new \App\Events\CreateBranch($request, $branch));

            return new BranchResource($branch);
        }

        return response()->json(['error' => __('Permission denied.')], 403);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Branch  $branch
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Branch $branch)
    {
        if (Auth::user()->can('manage branch') && $branch->created_by == Auth::user()->creatorId()) {
            return new BranchResource($branch);
        }

        return response()->json(['error' => __('Permission denied.')], 403);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Branch  $branch
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, Branch $branch)
    {
        if (Auth::user()->can('edit branch') && $branch->created_by == Auth::user()->creatorId()) {
            $validator = \Validator::make($request->all(), ['name' => 'required|string|max:255']);

            if ($validator->fails()) {
                return response()->json(['error' => $validator->errors()->first()], 400);
            }

            $branch->name = $request->name;
            $branch->save();

            event(new \App\Events\UpdateBranch($request, $branch));

            return new BranchResource($branch);
        }

        return response()->json(['error' => __('Permission denied.')], 403);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Branch  $branch
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Branch $branch)
    {
        if (Auth::user()->can('delete branch') && $branch->created_by == Auth::user()->creatorId()) {
            if (Employee::where('branch_id', $branch->id)->exists()) {
                return response()->json(['error' => __('This branch has employees. Please remove the employee from this branch.')], 422);
            }

            // Cascading delete logic from web controller
            $departments = Department::where('branch_id', $branch->id)->get();
            foreach($departments as $department) {
                Designation::where('department_id', $department->id)->delete();
                $department->delete();
            }

            event(new \App\Events\DeleteBranch($branch));
            $branch->delete();

            return response()->json(null, 204);
        }

        return response()->json(['error' => __('Permission denied.')], 403);
    }
}
