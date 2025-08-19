<?php

namespace App\Http\Controllers\API\v1;

use App\Http\Controllers\Controller;
use App\Models\TimeTracker;
use App\Models\ProjectTask;
use App\Models\TrackPhoto;
use App\Models\Utility;
use Illuminate\Http\Request;
use App\Http\Resources\TimeTrackerResource;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Traits\ApiResponser;

class TimeTrackerController extends Controller
{
    use ApiResponser;
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

    public function start(Request $request)
    {
        if (Gate::denies('create time tracker')) {
            return $this->error('Permission denied.', 403);
        }

        $validator = Validator::make($request->all(), [
            'task_id' => 'required|integer|exists:project_tasks,id',
        ]);

        if ($validator->fails()) {
            return $this->error($validator->errors()->first(), 422);
        }

        $user = auth()->user();
        $task = ProjectTask::find($request->task_id);

        // Stop any currently running tracker
        TimeTracker::where('created_by', '=', $user->creatorId())->where('is_active', '=', 1)->update(['end_time' => now()]);

        $track = TimeTracker::create([
            'name'        => $request->input('name', $task->name),
            'project_id'  => $task->project_id,
            'is_billable' => $request->input('is_billable', 0),
            'tag_id'      => $request->input('tag_id'),
            'start_time'  => now(),
            'task_id'     => $request->task_id,
            'user_id'     => $user->id,
            'created_by'  => $user->creatorId(),
            'is_active'   => 1,
        ]);

        return $this->success(new TimeTrackerResource($track), 'Tracker started successfully.');
    }

    public function stop(Request $request)
    {
        if (Gate::denies('create time tracker')) {
            return $this->error('Permission denied.', 403);
        }

        $validator = Validator::make($request->all(), [
            'tracker_id' => 'required|integer|exists:time_trackers,id',
        ]);

        if ($validator->fails()) {
            return $this->error($validator->errors()->first(), 422);
        }

        $tracker = TimeTracker::where('id', $request->tracker_id)->where('user_id', auth()->id())->first();

        if ($tracker) {
            $tracker->end_time   = now();
            $tracker->is_active  = 0;
            $tracker->total_time = Utility::diffance_to_time($tracker->start_time, $tracker->end_time);
            $tracker->save();
            return $this->success(new TimeTrackerResource($tracker), 'Tracker stopped successfully.');
        }

        return $this->error('Tracker not found or not owned by user.', 404);
    }

    public function uploadImage(Request $request)
    {
        if (Gate::denies('create time tracker')) {
            return $this->error('Permission denied.', 403);
        }

        $user = auth()->user();
        $validator = Validator::make($request->all(), [
            'tracker_id' => 'required|integer|exists:time_trackers,id',
            'img' => 'required|string', // base64 image
            'imgName' => 'required|string',
        ]);

        if ($validator->fails()) {
            return $this->error($validator->errors()->first(), 422);
        }

        $image_base64 = base64_decode($request->img);
        $file = $request->imgName;

        $tracker = TimeTracker::where('id', $request->tracker_id)->where('user_id', $user->id)->first();
        if(!$tracker) {
            return $this->error('Tracker not found or not owned by user.', 404);
        }

        $app_path = storage_path('uploads/traker_images/') . $request->tracker_id . '/';
        if (!file_exists($app_path)) {
            mkdir($app_path, 0777, true);
        }

        $file_name = $app_path . $file;
        file_put_contents($file_name, $image_base64);

        $new = TrackPhoto::create([
            'track_id' => $request->tracker_id,
            'user_id'  => $user->id,
            'img_path' => 'uploads/traker_images/' . $request->tracker_id . '/' . $file,
            'time'     => now(),
            'status'   => 1,
            'created_by' => $user->creatorId(),
        ]);

        return $this->success([], 'Image uploaded successfully.');
    }
}
