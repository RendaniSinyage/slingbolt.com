<?php

namespace App\Http\Controllers\API\v1;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\User;
use App\Models\ProjectUser;
use Illuminate\Http\Request;
use App\Http\Resources\ProjectResource;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Auth;

class ProjectController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if (Gate::denies('manage project')) {
            return response()->json(['error' => 'Permission denied.'], 403);
        }

        $user = Auth::user();
        if ($user->type == 'client') {
            $user_projects = Project::where('client_id', $user->id)->pluck('id', 'id')->toArray();
        } else {
            $user_projects = $user->projects()->pluck('project_id', 'project_id')->toArray();
        }

        $projects = Project::whereIn('id', array_keys($user_projects))->get();

        return ProjectResource::collection($projects);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        if (Gate::denies('create project')) {
            return response()->json(['error' => 'Permission denied.'], 403);
        }

        $validator = \Validator::make(
            $request->all(), [
                'project_name' => 'required',
                'users' => 'array',
            ]
        );

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $project = new Project();
        $project->project_name = $request->project_name;
        $project->start_date = date("Y-m-d H:i:s", strtotime($request->start_date));
        $project->end_date = date("Y-m-d H:i:s", strtotime($request->end_date));
        $project->client_id = $request->client_id;
        $project->budget = $request->budget ?? 0;
        $project->description = $request->description;
        $project->status = $request->status;
        $project->estimated_hrs = $request->estimated_hrs;
        $project->tags = $request->tags;
        $project->created_by = Auth::user()->creatorId();
        $project->save();

        $users = $request->users ?? [];
        $users[] = Auth::id(); // Add current user
        if(Auth::user()->type != 'company'){
            $users[] = Auth::user()->creatorId();
        }
        $users = array_unique($users);

        foreach ($users as $user_id) {
            ProjectUser::create([
                'project_id' => $project->id,
                'user_id' => $user_id,
            ]);
        }

        return (new ProjectResource($project->load('users', 'client')))->additional(['message' => 'Project successfully created.']);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Project  $project
     * @return \Illuminate\Http\Response
     */
    public function show(Project $project)
    {
        if (Gate::denies('view project')) {
            return response()->json(['error' => 'Permission denied.'], 403);
        }

        $user = Auth::user();
        if ($user->type == 'client') {
            $user_projects = Project::where('client_id', $user->id)->pluck('id', 'id')->toArray();
        } else {
            $user_projects = $user->projects()->pluck('project_id', 'project_id')->toArray();
        }

        if (!in_array($project->id, array_keys($user_projects))) {
            return response()->json(['error' => 'Permission denied.'], 403);
        }

        return new ProjectResource($project->load('users', 'client', 'tasks', 'milestones', 'expenses', 'bugs'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Project  $project
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Project $project)
    {
        if (Gate::denies('edit project')) {
            return response()->json(['error' => 'Permission denied.'], 403);
        }

        if ($project->created_by != Auth::user()->creatorId()) {
            return response()->json(['error' => 'Permission denied.'], 403);
        }

        $validator = \Validator::make(
            $request->all(), [
                'project_name' => 'sometimes|required',
            ]
        );

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $project->update($request->all());

        return (new ProjectResource($project->load('users', 'client')))->additional(['message' => 'Project successfully updated.']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Project  $project
     * @return \Illuminate\Http\Response
     */
    public function destroy(Project $project)
    {
        if (Gate::denies('delete project')) {
            return response()->json(['error' => 'Permission denied.'], 403);
        }

        if ($project->created_by != Auth::user()->creatorId()) {
            return response()->json(['error' => 'Permission denied.'], 403);
        }

        $project->delete();

        return response()->json(['message' => 'Project successfully deleted.']);
    }
}
