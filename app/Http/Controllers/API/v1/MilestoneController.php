<?php

namespace App\Http\Controllers\API\v1;

use App\Http\Controllers\Controller;
use App\Models\Milestone;
use App\Models\Project;
use Illuminate\Http\Request;
use App\Http\Resources\MilestoneResource;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Auth;

class MilestoneController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Project $project)
    {
        if (Gate::denies('view milestone')) {
            return response()->json(['error' => 'Permission denied.'], 403);
        }

        $milestones = $project->milestones;

        return MilestoneResource::collection($milestones);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request, Project $project)
    {
        if (Gate::denies('create milestone')) {
            return response()->json(['error' => 'Permission denied.'], 403);
        }

        $validator = \Validator::make(
            $request->all(), [
                'title' => 'required',
                'status' => 'required',
                'cost' => 'required',
                'start_date' => 'required',
                'due_date' => 'required',
            ]
        );

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $milestone = new Milestone();
        $milestone->project_id = $project->id;
        $milestone->title = $request->title;
        $milestone->status = $request->status;
        $milestone->cost = $request->cost;
        $milestone->start_date = $request->start_date;
        $milestone->due_date = $request->due_date;
        $milestone->description = $request->description;
        $milestone->save();

        return (new MilestoneResource($milestone))->additional(['message' => 'Milestone successfully created.']);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Milestone  $milestone
     * @return \Illuminate\Http\Response
     */
    public function show(Project $project, Milestone $milestone)
    {
        if (Gate::denies('view milestone')) {
            return response()->json(['error' => 'Permission denied.'], 403);
        }

        if ($milestone->project_id != $project->id) {
            return response()->json(['error' => 'Milestone not found in this project.'], 404);
        }

        return new MilestoneResource($milestone);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Milestone  $milestone
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Project $project, Milestone $milestone)
    {
        if (Gate::denies('edit milestone')) {
            return response()->json(['error' => 'Permission denied.'], 403);
        }

        if ($milestone->project_id != $project->id) {
            return response()->json(['error' => 'Milestone not found in this project.'], 404);
        }

        $validator = \Validator::make(
            $request->all(), [
                'title' => 'sometimes|required',
                'status' => 'sometimes|required',
                'cost' => 'sometimes|required',
            ]
        );

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $milestone->update($request->all());

        return (new MilestoneResource($milestone))->additional(['message' => 'Milestone successfully updated.']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Milestone  $milestone
     * @return \Illuminate\Http\Response
     */
    public function destroy(Project $project, Milestone $milestone)
    {
        if (Gate::denies('delete milestone')) {
            return response()->json(['error' => 'Permission denied.'], 403);
        }

        if ($milestone->project_id != $project->id) {
            return response()->json(['error' => 'Milestone not found in this project.'], 404);
        }

        $milestone->delete();

        return response()->json(['message' => 'Milestone successfully deleted.']);
    }
}
