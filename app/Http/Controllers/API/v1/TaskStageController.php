<?php

namespace App\Http\Controllers\API\v1;

use App\Http\Controllers\Controller;
use App\Models\TaskStage;
use Illuminate\Http\Request;
use App\Http\Resources\TaskStageResource;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Auth;

class TaskStageController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if (Gate::denies('manage project task stage')) {
            return response()->json(['error' => 'Permission denied.'], 403);
        }

        $task_stages = TaskStage::where('created_by', '=', Auth::user()->creatorId())->orderBy('order', 'asc')->get();

        return TaskStageResource::collection($task_stages);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        if (Gate::denies('create project task stage')) {
            return response()->json(['error' => 'Permission denied.'], 403);
        }

        $validator = \Validator::make(
            $request->all(), [
                'name' => 'required|max:20',
            ]
        );

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $order = TaskStage::where('created_by', Auth::user()->creatorId())->count();

        $task_stage = new TaskStage();
        $task_stage->name = $request->name;
        $task_stage->order = $order + 1;
        $task_stage->created_by = Auth::user()->creatorId();
        $task_stage->save();

        return (new TaskStageResource($task_stage))->additional(['message' => 'Task Stage successfully created.']);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\TaskStage  $taskStage
     * @return \Illuminate\Http\Response
     */
    public function show(TaskStage $taskStage)
    {
        if (Gate::denies('manage project task stage')) {
            return response()->json(['error' => 'Permission denied.'], 403);
        }

        if ($taskStage->created_by != Auth::user()->creatorId()) {
            return response()->json(['error' => 'Permission denied.'], 403);
        }

        return new TaskStageResource($taskStage);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\TaskStage  $taskStage
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, TaskStage $taskStage)
    {
        if (Gate::denies('edit project task stage')) {
            return response()->json(['error' => 'Permission denied.'], 403);
        }

        if ($taskStage->created_by != Auth::user()->creatorId()) {
            return response()->json(['error' => 'Permission denied.'], 403);
        }

        $validator = \Validator::make(
            $request->all(), [
                'name' => 'required|max:20',
            ]
        );

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $taskStage->name = $request->name;
        $taskStage->save();

        return (new TaskStageResource($taskStage))->additional(['message' => 'Task Stage successfully updated.']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\TaskStage  $taskStage
     * @return \Illuminate\Http\Response
     */
    public function destroy(TaskStage $taskStage)
    {
        if (Gate::denies('delete project task stage')) {
            return response()->json(['error' => 'Permission denied.'], 403);
        }

        if ($taskStage->created_by != Auth::user()->creatorId()) {
            return response()->json(['error' => 'Permission denied.'], 403);
        }

        $taskStage->delete();

        return response()->json(['message' => 'Task Stage successfully deleted.']);
    }
}
