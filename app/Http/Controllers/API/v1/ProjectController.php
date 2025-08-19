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

        if ($user->type == 'company') {
            $projects = Project::with('tasks')->where('created_by', $user->id)->get();
        } elseif ($user->type == 'client') {
            $user_projects = Project::where('client_id', $user->id)->pluck('id', 'id')->toArray();
            $projects = Project::with('tasks')->whereIn('id', array_keys($user_projects))->get();
        } else {
            $user_projects = $user->projects()->pluck('project_id', 'project_id')->toArray();
            $projects = Project::with('tasks')->whereIn('id', array_keys($user_projects))->get();
        }

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

    public function inviteProjectUserMember(Request $request, Project $project)
    {
        if (Gate::denies('edit project')) {
            return response()->json(['error' => 'Permission denied.'], 403);
        }

        if ($project->created_by != Auth::user()->creatorId()) {
            return response()->json(['error' => 'Permission denied.'], 403);
        }

        $validator = \Validator::make($request->all(), [
            'users' => 'required|array',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()], 422);
        }

        $users = array_filter($request->users);
        foreach ($users as $user_id) {
            ProjectUser::create([
                'project_id' => $project->id,
                'user_id' => $user_id,
            ]);
        }

        return (new ProjectResource($project->fresh()->load('users')))->additional(['message' => 'Users successfully invited.']);
    }

    public function destroyProjectUser(Project $project, User $user)
    {
        if (Gate::denies('edit project')) {
            return response()->json(['error' => 'Permission denied.'], 403);
        }

        if ($project->created_by != Auth::user()->creatorId()) {
            return response()->json(['error' => 'Permission denied.'], 403);
        }

        ProjectUser::where('project_id', '=', $project->id)->where('user_id', '=', $user->id)->delete();

        return (new ProjectResource($project->fresh()->load('users')))->additional(['message' => 'User successfully removed.']);
    }

    public function gantt(Request $request, Project $project)
    {
        if (Gate::denies('view project')) {
            return response()->json(['error' => 'Permission denied.'], 403);
        }

        if ($project->created_by != Auth::user()->creatorId()) {
            return response()->json(['error' => 'Permission denied.'], 403);
        }

        $tasks = \App\Models\ProjectTask::where('project_id', $project->id)->get();

        $gantt_data = [];
        foreach($tasks as $task) {
            $gantt_data[] = [
                'id' => $task->id,
                'name' => $task->name,
                'start' => $task->start_date,
                'end' => $task->end_date,
                'progress' => $task->progress,
                'custom_class' => 'bar-milestone',
            ];
        }

        return response()->json($gantt_data);
    }

    public function ganttPost(Request $request, Project $project)
    {
        if (Gate::denies('edit project')) {
            return response()->json(['error' => 'Permission denied.'], 403);
        }

        if ($project->created_by != Auth::user()->creatorId()) {
            return response()->json(['error' => 'Permission denied.'], 403);
        }

        $validator = \Validator::make($request->all(), [
            'start_date' => 'required|date',
            'end_date' => 'required|date',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()], 422);
        }

        $task = \App\Models\ProjectTask::find($request->task_id);
        if($task) {
            $task->start_date = $request->start_date;
            $task->end_date = $request->end_date;
            $task->save();
        }

        return response()->json(['message' => 'Task successfully updated.']);
    }

    public function userPermission(Request $request, Project $project, User $user)
    {
        if (Gate::denies('edit project')) {
            return response()->json(['error' => 'Permission denied.'], 403);
        }

        if ($project->created_by != Auth::user()->creatorId()) {
            return response()->json(['error' => 'Permission denied.'], 403);
        }

        $permissions = \App\Models\Permission::all()->pluck('name', 'id')->toArray();
        $user_permissions = $user->permission->pluck('name', 'id')->toArray();

        return response()->json([
            'permissions' => $permissions,
            'user_permissions' => $user_permissions,
        ]);
    }

    public function userPermissionStore(Request $request, Project $project, User $user)
    {
        if (Gate::denies('edit project')) {
            return response()->json(['error' => 'Permission denied.'], 403);
        }

        if ($project->created_by != Auth::user()->creatorId()) {
            return response()->json(['error' => 'Permission denied.'], 403);
        }

        $validator = \Validator::make($request->all(), [
            'permissions' => 'required|array',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()], 422);
        }

        $user->syncPermissions($request->permissions);

        return (new ProjectResource($project->fresh()->load('users')))->additional(['message' => 'Permissions successfully updated.']);
    }

    public function milestoneStore(Request $request, Project $project)
    {
        if (Gate::denies('create milestone')) {
            return response()->json(['error' => 'Permission denied.'], 403);
        }

        if ($project->created_by != Auth::user()->creatorId()) {
            return response()->json(['error' => 'Permission denied.'], 403);
        }

        $validator = \Validator::make($request->all(), [
            'title' => 'required',
            'status' => 'required',
            'cost' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()], 422);
        }

        $milestone = \App\Models\Milestone::create([
            'project_id' => $project->id,
            'title' => $request->title,
            'status' => $request->status,
            'cost' => $request->cost,
            'summary' => $request->summary,
        ]);

        return (new \App\Http\Resources\MilestoneResource($milestone))->additional(['message' => 'Milestone successfully created.']);
    }

    public function milestoneUpdate(Request $request, Project $project, \App\Models\Milestone $milestone)
    {
        if (Gate::denies('edit milestone')) {
            return response()->json(['error' => 'Permission denied.'], 403);
        }

        if ($project->created_by != Auth::user()->creatorId()) {
            return response()->json(['error' => 'Permission denied.'], 403);
        }

        $validator = \Validator::make($request->all(), [
            'title' => 'sometimes|required',
            'status' => 'sometimes|required',
            'cost' => 'sometimes|required',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()], 422);
        }

        $milestone->fill($request->all())->save();

        return (new \App\Http\Resources\MilestoneResource($milestone))->additional(['message' => 'Milestone successfully updated.']);
    }

    public function milestoneDestroy(Project $project, \App\Models\Milestone $milestone)
    {
        if (Gate::denies('delete milestone')) {
            return response()->json(['error' => 'Permission denied.'], 403);
        }

        if ($project->created_by != Auth::user()->creatorId()) {
            return response()->json(['error' => 'Permission denied.'], 403);
        }

        $milestone->delete();

        return response()->json(['message' => 'Milestone successfully deleted.']);
    }

    public function bugStore(Request $request, Project $project)
    {
        if (Gate::denies('create bug report')) {
            return response()->json(['error' => 'Permission denied.'], 403);
        }

        if ($project->created_by != Auth::user()->creatorId()) {
            return response()->json(['error' => 'Permission denied.'], 403);
        }

        $validator = \Validator::make($request->all(), [
            'title' => 'required',
            'priority' => 'required',
            'status' => 'required',
            'assign_to' => 'required|exists:users,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()], 422);
        }

        $bug = \App\Models\Bug::create([
            'project_id' => $project->id,
            'title' => $request->title,
            'priority' => $request->priority,
            'status' => $request->status,
            'assign_to' => $request->assign_to,
            'description' => $request->description,
            'start_date' => $request->start_date,
            'due_date' => $request->due_date,
        ]);

        return (new \App\Http\Resources\BugResource($bug))->additional(['message' => 'Bug successfully created.']);
    }

    public function bugUpdate(Request $request, Project $project, \App\Models\Bug $bug)
    {
        if (Gate::denies('edit bug report')) {
            return response()->json(['error' => 'Permission denied.'], 403);
        }

        if ($project->created_by != Auth::user()->creatorId()) {
            return response()->json(['error' => 'Permission denied.'], 403);
        }

        $validator = \Validator::make($request->all(), [
            'title' => 'sometimes|required',
            'priority' => 'sometimes|required',
            'status' => 'sometimes|required',
            'assign_to' => 'sometimes|required|exists:users,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()], 422);
        }

        $bug->fill($request->all())->save();

        return (new \App\Http\Resources\BugResource($bug))->additional(['message' => 'Bug successfully updated.']);
    }

    public function bugDestroy(Project $project, \App\Models\Bug $bug)
    {
        if (Gate::denies('delete bug report')) {
            return response()->json(['error' => 'Permission denied.'], 403);
        }

        if ($project->created_by != Auth::user()->creatorId()) {
            return response()->json(['error' => 'Permission denied.'], 403);
        }

        $bug->delete();

        return response()->json(['message' => 'Bug successfully deleted.']);
    }

    public function bugCommentStore(Request $request, Project $project, \App\Models\Bug $bug)
    {
        if (Gate::denies('edit bug report')) {
            return response()->json(['error' => 'Permission denied.'], 403);
        }

        if ($project->created_by != Auth::user()->creatorId()) {
            return response()->json(['error' => 'Permission denied.'], 403);
        }

        $validator = \Validator::make($request->all(), [
            'comment' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()], 422);
        }

        $comment = \App\Models\BugComment::create([
            'bug_id' => $bug->id,
            'comment' => $request->comment,
            'created_by' => Auth::user()->id,
        ]);

        return (new \App\Http\Resources\BugCommentResource($comment))->additional(['message' => 'Comment successfully added.']);
    }

    public function bugCommentStoreFile(Request $request, Project $project, \App\Models\Bug $bug)
    {
        if (Gate::denies('edit bug report')) {
            return response()->json(['error' => 'Permission denied.'], 403);
        }

        if ($project->created_by != Auth::user()->creatorId()) {
            return response()->json(['error' => 'Permission denied.'], 403);
        }

        $request->validate(['file' => 'required|mimes:jpeg,png,jpg,gif,svg,pdf,doc,docx|max:20480']);
        $file_name = $request->file->getClientOriginalName();
        $file_path = $bug->id . "_" . md5(time()) . "_" . $request->file->getClientOriginalName();
        $request->file->storeAs('bug_files', $file_path);

        $file = \App\Models\BugFile::create([
            'bug_id' => $bug->id,
            'file_name' => $file_name,
            'file_path' => $file_path,
        ]);

        return (new \App\Http\Resources\BugFileResource($file))->additional(['message' => 'File successfully uploaded.']);
    }

    public function copyProject(Request $request, Project $project)
    {
        if (Gate::denies('create project')) {
            return response()->json(['error' => 'Permission denied.'], 403);
        }

        $new_project = $project->replicate();
        $new_project->project_name = $project->project_name . ' (Copy)';
        $new_project->save();

        foreach($project->users as $user) {
            ProjectUser::create([
                'project_id' => $new_project->id,
                'user_id' => $user->id,
            ]);
        }

        if($request->get('milestone') == 'true') {
            foreach($project->milestones as $milestone) {
                $new_milestone = $milestone->replicate();
                $new_milestone->project_id = $new_project->id;
                $new_milestone->save();
            }
        }

        if($request->get('task') == 'true') {
            foreach($project->tasks as $task) {
                $new_task = $task->replicate();
                $new_task->project_id = $new_project->id;
                $new_task->save();
            }
        }

        return (new ProjectResource($new_project->load('users', 'client', 'tasks', 'milestones')))->additional(['message' => 'Project successfully copied.']);
    }
}
