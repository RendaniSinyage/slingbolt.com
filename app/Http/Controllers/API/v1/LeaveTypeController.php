<?php

namespace App\Http\Controllers\API\v1;

use App\Http\Controllers\Controller;
use App\Models\LeaveType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LeaveTypeController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        if (Auth::user()->can('manage leave type')) {
            $leavetypes = LeaveType::where('created_by', Auth::user()->creatorId())->get();
            return response()->json($leavetypes);
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
        if (Auth::user()->can('create leave type')) {
            $validator = \Validator::make($request->all(), [
                'title' => 'required|string|max:255',
                'days' => 'required|integer|min:0',
            ]);

            if ($validator->fails()) {
                return response()->json(['error' => $validator->errors()->first()], 400);
            }

            $leavetype = new LeaveType();
            $leavetype->title = $request->title;
            $leavetype->days = $request->days;
            $leavetype->created_by = Auth::user()->creatorId();
            $leavetype->save();

            return response()->json($leavetype, 201);
        }

        return response()->json(['error' => __('Permission denied.')], 403);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\LeaveType  $leavetype
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(LeaveType $leavetype)
    {
        if (Auth::user()->can('manage leave type') && $leavetype->created_by == Auth::user()->creatorId()) {
            return response()->json($leavetype);
        }

        return response()->json(['error' => __('Permission denied.')], 403);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\LeaveType  $leavetype
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, LeaveType $leavetype)
    {
        if (Auth::user()->can('edit leave type') && $leavetype->created_by == Auth::user()->creatorId()) {
            $validator = \Validator::make($request->all(), [
                'title' => 'required|string|max:255',
                'days' => 'required|integer|min:0',
            ]);

            if ($validator->fails()) {
                return response()->json(['error' => $validator->errors()->first()], 400);
            }

            $leavetype->title = $request->title;
            $leavetype->days = $request->days;
            $leavetype->save();

            return response()->json($leavetype);
        }

        return response()->json(['error' => __('Permission denied.')], 403);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\LeaveType  $leavetype
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(LeaveType $leavetype)
    {
        if (Auth::user()->can('delete leave type') && $leavetype->created_by == Auth::user()->creatorId()) {
            $leavetype->delete();
            return response()->json(null, 204);
        }

        return response()->json(['error' => __('Permission denied.')], 403);
    }
}
