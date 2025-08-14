<?php

namespace App\Http\Controllers\API\v1;

use App\Http\Controllers\Controller;
use App\Models\Milestone;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class MilestoneController extends Controller
{
    public function index(Request $request, $projectId)
    {
        $user = $request->user();
        $project = Project::find($projectId);

        if (!$project || !$user->can('view project', $project)) {
            return response()->json(['error' => 'Project not found or permission denied.'], 404);
        }

        $milestones = Milestone::where('project_id', $projectId)->get();
        return response()->json($milestones);
    }

    public function store(Request $request, $projectId)
    {
        $user = $request->user();
        $project = Project::find($projectId);

        if (!$project || !$user->can('create milestone', $project)) {
            return response()->json(['error' => 'Project not found or permission denied.'], 403);
        }

        $validator = Validator::make($request->all(), [
            'title' => 'required',
            'status' => 'required',
            'cost' => 'required|numeric',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()], 400);
        }

        $milestone = new Milestone();
        $milestone->project_id = $projectId;
        $milestone->title = $request->title;
        $milestone->status = $request->status;
        $milestone->cost = $request->cost;
        $milestone->description = $request->description;
        $milestone->save();

        return response()->json($milestone, 201);
    }

    public function update(Request $request, $milestoneId)
    {
        $user = $request->user();
        $milestone = Milestone::find($milestoneId);

        if (!$milestone || !$user->can('edit milestone', $milestone->project)) {
            return response()->json(['error' => 'Milestone not found or permission denied.'], 404);
        }

        $milestone->update($request->all());
        return response()->json($milestone);
    }

    public function destroy($milestoneId)
    {
        $user = request()->user();
        $milestone = Milestone::find($milestoneId);

        if (!$milestone || !$user->can('delete milestone', $milestone->project)) {
            return response()->json(['error' => 'Milestone not found or permission denied.'], 404);
        }

        $milestone->delete();
        return response()->json(['success' => 'Milestone successfully deleted.'], 200);
    }
}
