<?php

namespace App\Http\Controllers\API\v1;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\ProjectTask;
use Illuminate\Http\Request;
use App\Http\Resources\ProjectTaskResource;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Auth;

class ProjectTaskController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Project $project)
    {
        if (Gate::denies('manage project task')) {
            return response()->json(['error' => 'Permission denied.'], 403);
        }

        $tasks = $project->tasks()->with('stage')->get();

        return ProjectTaskResource::collection($tasks);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request, Project $project)
    {
        if (Gate::denies('create project task')) {
            return response()->json(['error' => 'Permission denied.'], 403);
        }

        $validStageIds = $project->getAvailableStages()->pluck('id')->toArray();

        $validator = \Validator::make(
            $request->all(), [
                'name' => 'required',
                'estimated_hrs' => 'required',
                'priority' => 'required',
                'stage_id' => 'required|in:' . implode(',', $validStageIds),
                'assign_to' => 'required|exists:users,id',
            ]
        );

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $post = $request->all();
        $post['project_id'] = $project->id;
        $post['created_by'] = Auth::user()->creatorId();
        $post['start_date'] = date("Y-m-d H:i:s", strtotime($request->start_date));
        $post['end_date'] = date("Y-m-d H:i:s", strtotime($request->end_date));

        $task = ProjectTask::create($post);

        return (new ProjectTaskResource($task->load('stage')))->additional(['message' => 'Task successfully created.']);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\ProjectTask  $projectTask
     * @return \Illuminate\Http\Response
     */
    public function show(Project $project, ProjectTask $task)
    {
        if (Gate::denies('view project task')) {
            return response()->json(['error' => 'Permission denied.'], 403);
        }

        if ($task->project_id != $project->id) {
            return response()->json(['error' => 'Task not found in this project.'], 404);
        }

        return new ProjectTaskResource($task->load('stage', 'comments', 'taskFiles', 'checklist'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\ProjectTask  $projectTask
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Project $project, ProjectTask $task)
    {
        if (Gate::denies('edit project task')) {
            return response()->json(['error' => 'Permission denied.'], 403);
        }

        if ($task->project_id != $project->id) {
            return response()->json(['error' => 'Task not found in this project.'], 404);
        }

        $validStageIds = $project->getAvailableStages()->pluck('id')->toArray();

        $validator = \Validator::make(
            $request->all(), [
                'name' => 'sometimes|required',
                'estimated_hrs' => 'sometimes|required',
                'priority' => 'sometimes|required',
                'stage_id' => 'sometimes|required|in:' . implode(',', $validStageIds),
            ]
        );

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $task->update($request->all());

        return (new ProjectTaskResource($task->load('stage')))->additional(['message' => 'Task successfully updated.']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\ProjectTask  $projectTask
     * @return \Illuminate\Http\Response
     */
    public function destroy(Project $project, ProjectTask $task)
    {
        if (Gate::denies('delete project task')) {
            return response()->json(['error' => 'Permission denied.'], 403);
        }

        if ($task->project_id != $project->id) {
            return response()->json(['error' => 'Task not found in this project.'], 404);
        }

        $task->delete();

        return response()->json(['message' => 'Task successfully deleted.']);
    }
}
