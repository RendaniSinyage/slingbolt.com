<?php

namespace App\Http\Controllers\API\v1;

use App\Http\Controllers\Controller;
use App\Models\TimeTracker;
use Illuminate\Http\Request;
use App\Http\Resources\TimeTrackerResource;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Auth;

class TimeTrackerController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if (Gate::denies('manage time tracker')) {
            return response()->json(['error' => 'Permission denied.'], 403);
        }

        if (Auth::user()->type == 'company') {
            $treckers = TimeTracker::where('created_by', Auth::user()->creatorId())->get();
        } else {
            $treckers = TimeTracker::where('user_id', Auth::user()->id)->where('created_by', Auth::user()->creatorId())->get();
        }

        return TimeTrackerResource::collection($treckers);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        if (Gate::denies('create time tracker')) {
            return response()->json(['error' => 'Permission denied.'], 403);
        }

        $validator = \Validator::make(
            $request->all(), [
                'project_id' => 'required|exists:projects,id',
                'start_time' => 'required',
                'end_time' => 'required',
            ]
        );

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $timeTracker = new TimeTracker();
        $timeTracker->project_id = $request->project_id;
        $timeTracker->user_id = Auth::id();
        $timeTracker->start_time = $request->start_time;
        $timeTracker->end_time = $request->end_time;
        $timeTracker->total_time = $request->total_time;
        $timeTracker->created_by = Auth::user()->creatorId();
        $timeTracker->save();

        return (new TimeTrackerResource($timeTracker))->additional(['message' => 'Time tracker successfully created.']);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\TimeTracker  $timeTracker
     * @return \Illuminate\Http\Response
     */
    public function show(TimeTracker $timeTracker)
    {
        if (Gate::denies('manage time tracker')) {
            return response()->json(['error' => 'Permission denied.'], 403);
        }

        if ($timeTracker->created_by != Auth::user()->creatorId()) {
            return response()->json(['error' => 'Permission denied.'], 403);
        }

        return new TimeTrackerResource($timeTracker);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\TimeTracker  $timeTracker
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, TimeTracker $timeTracker)
    {
        if (Gate::denies('edit time tracker')) {
            return response()->json(['error' => 'Permission denied.'], 403);
        }

        if ($timeTracker->created_by != Auth::user()->creatorId()) {
            return response()->json(['error' => 'Permission denied.'], 403);
        }

        $validator = \Validator::make(
            $request->all(), [
                'project_id' => 'sometimes|required|exists:projects,id',
                'start_time' => 'sometimes|required',
                'end_time' => 'sometimes|required',
            ]
        );

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $timeTracker->update($request->all());

        return (new TimeTrackerResource($timeTracker))->additional(['message' => 'Time tracker successfully updated.']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\TimeTracker  $timeTracker
     * @return \Illuminate\Http\Response
     */
    public function destroy(TimeTracker $timeTracker)
    {
        if (Gate::denies('delete time tracker')) {
            return response()->json(['error' => 'Permission denied.'], 403);
        }

        if ($timeTracker->created_by != Auth::user()->creatorId()) {
            return response()->json(['error' => 'Permission denied.'], 403);
        }

        $timeTracker->delete();

        return response()->json(['message' => 'Time tracker successfully deleted.']);
    }
}
