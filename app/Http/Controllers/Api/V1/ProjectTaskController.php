<?php

namespace App\Http\Controllers\API\v1;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\ProjectTask;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ProjectTaskController extends Controller
{
    public function index(Request $request, $projectId)
    {
        $user = $request->user();
        $project = Project::find($projectId);

        if (!$project || !$user->can('view project', $project)) {
            return response()->json(['error' => 'Project not found or permission denied.'], 404);
        }

        $tasks = ProjectTask::where('project_id', $projectId)->with(['users'])->get();

        return response()->json($tasks);
    }

    public function show($taskId)
    {
        $user = request()->user();
        $task = ProjectTask::with(['users', 'stage', 'milestone'])->find($taskId);

        if (!$task || !$user->can('view project task', $task->project)) {
             return response()->json(['error' => 'Task not found or permission denied.'], 404);
        }

        return response()->json($task);
    }

    public function store(Request $request, $projectId)
    {
        $user = $request->user();
        $project = Project::find($projectId);

        if (!$project || !$user->can('create project task', $project)) {
            return response()->json(['error' => 'Project not found or permission denied.'], 403);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'stage_id' => 'required|exists:task_stages,id',
            'assign_to' => 'required|array',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()], 400);
        }

        $task = new ProjectTask();
        $task->name = $request->name;
        $task->project_id = $projectId;
        $task->stage_id = $request->stage_id;
        $task->assign_to = implode(',', $request->assign_to);
        $task->priority = $request->priority ?? 'medium';
        $task->start_date = $request->start_date;
        $task->end_date = $request->end_date;
        $task->created_by = $user->creatorId();
        $task->save();

        return response()->json($task, 201);
    }

    public function update(Request $request, $taskId)
    {
        $user = $request->user();
        $task = ProjectTask::find($taskId);

        if (!$task || !$user->can('edit project task', $task->project)) {
            return response()->json(['error' => 'Task not found or permission denied.'], 404);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'stage_id' => 'required|exists:task_stages,id',
            'assign_to' => 'required|array',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()], 400);
        }

        $task->name = $request->name;
        $task->stage_id = $request->stage_id;
        $task->assign_to = implode(',', $request->assign_to);
        $task->priority = $request->priority ?? 'medium';
        $task->start_date = $request->start_date;
        $task->end_date = $request->end_date;
        $task->save();

        return response()->json($task);
    }

    public function destroy($taskId)
    {
        $user = request()->user();
        $task = ProjectTask::find($taskId);

        if (!$task || !$user->can('delete project task', $task->project)) {
            return response()->json(['error' => 'Task not found or permission denied.'], 404);
        }

        ProjectTask::deleteTask([$taskId]);

        return response()->json(['success' => 'Task successfully deleted.'], 200);
    }
}
