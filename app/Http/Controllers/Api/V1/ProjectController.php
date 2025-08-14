<?php

namespace App\Http\Controllers\API\v1;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\ProjectUser;
use App\Models\User;
use App\Models\ProjectType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ProjectController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $user_projects = $user->projects()->pluck('project_id', 'project_id')->toArray();
        $projects = Project::whereIn('id', array_keys($user_projects))->with(['client', 'users'])->get();

        return response()->json($projects);
    }

    public function show($id)
    {
        $user = request()->user();
        $project = Project::with(['client', 'users', 'tasks', 'milestones'])->find($id);

        if (!$project || !$user->can('view project', $project)) {
            return response()->json(['error' => 'Project not found or permission denied.'], 404);
        }

        return response()->json($project);
    }

    public function store(Request $request)
    {
        $user = $request->user();
        $creatorId = $user->creatorId();

        $validator = Validator::make($request->all(), [
            'project_name' => 'required',
            'type' => 'required|in:' . implode(',', array_keys(ProjectType::getTypes())),
            'start_date' => 'required|date',
            'end_date' => 'nullable|date',
            'client_id' => 'nullable|exists:users,id',
            'users' => 'nullable|array',
            'users.*' => 'exists:users,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()], 400);
        }

        $project = new Project();
        $project->project_name = $request->project_name;
        $project->type = $request->type;
        $project->start_date = $request->start_date;
        $project->end_date = $request->end_date;
        $project->client_id = $request->client_id ?? 0;
        $project->budget = $request->budget ?? 0;
        $project->description = $request->description;
        $project->status = $request->status ?? 'in_progress';
        $project->created_by = $creatorId;
        $project->save();

        // Assign the creator
        ProjectUser::create([
            'project_id' => $project->id,
            'user_id' => $user->id,
        ]);

        // Assign other users
        if ($request->has('users')) {
            foreach ($request->users as $user_id) {
                ProjectUser::create([
                    'project_id' => $project->id,
                    'user_id' => $user_id,
                ]);
            }
        }

        return response()->json($project, 201);
    }

    public function update(Request $request, $id)
    {
        $user = $request->user();
        $project = Project::where('created_by', $user->creatorId())->find($id);

        if (!$project) {
            return response()->json(['error' => 'Project not found.'], 404);
        }

        $validator = Validator::make($request->all(), [
            'project_name' => 'required',
            'type' => 'required|in:' . implode(',', array_keys(ProjectType::getTypes())),
            'start_date' => 'required|date',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()], 400);
        }

        $project->update($request->all());

        return response()->json($project);
    }

    public function destroy($id)
    {
        $user = $request->user();
        $project = Project::where('created_by', $user->creatorId())->find($id);

        if (!$project) {
            return response()->json(['error' => 'Project not found.'], 404);
        }

        // Using the static method from the web controller for cleanup
        Project::deleteProject($id);

        return response()->json(['success' => 'Project successfully deleted.'], 200);
    }
}
