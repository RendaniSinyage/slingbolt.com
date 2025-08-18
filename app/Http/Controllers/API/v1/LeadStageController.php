<?php

namespace App\Http\Controllers\API\v1;

use App\Http\Controllers\Controller;
use App\Models\LeadStage;
use App\Models\Pipeline;
use Illuminate\Http\Request;
use App\Http\Resources\LeadStageResource;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Auth;

class LeadStageController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        if (Gate::denies('manage lead stage')) {
            return response()->json(['error' => 'Permission denied.'], 403);
        }

        $query = LeadStage::query();

        if ($request->has('pipeline_id')) {
            $query->where('pipeline_id', $request->pipeline_id);
        }

        $lead_stages = $query->where('created_by', '=', Auth::user()->ownerId())
                             ->orderBy('pipeline_id')
                             ->orderBy('order')
                             ->get();

        return LeadStageResource::collection($lead_stages);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        if (Gate::denies('create lead stage')) {
            return response()->json(['error' => 'Permission denied.'], 403);
        }

        $validator = \Validator::make(
            $request->all(), [
                'name' => 'required|max:20',
                'pipeline_id' => 'required|exists:pipelines,id',
            ]
        );

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $lead_stage = new LeadStage();
        $lead_stage->name = $request->name;
        $lead_stage->pipeline_id = $request->pipeline_id;
        $lead_stage->created_by = Auth::user()->ownerId();
        $lead_stage->save();

        return (new LeadStageResource($lead_stage))->additional(['message' => 'Lead Stage successfully created.']);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\LeadStage  $leadStage
     * @return \Illuminate\Http\Response
     */
    public function show(LeadStage $leadStage)
    {
        if (Gate::denies('manage lead stage')) {
            return response()->json(['error' => 'Permission denied.'], 403);
        }

        if ($leadStage->created_by != Auth::user()->ownerId()) {
            return response()->json(['error' => 'Permission denied.'], 403);
        }

        return new LeadStageResource($leadStage);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\LeadStage  $leadStage
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, LeadStage $leadStage)
    {
        if (Gate::denies('edit lead stage')) {
            return response()->json(['error' => 'Permission denied.'], 403);
        }

        if ($leadStage->created_by != Auth::user()->ownerId()) {
            return response()->json(['error' => 'Permission denied.'], 403);
        }

        $validator = \Validator::make(
            $request->all(), [
                'name' => 'required|max:20',
                'pipeline_id' => 'required|exists:pipelines,id',
            ]
        );

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $leadStage->name = $request->name;
        $leadStage->pipeline_id = $request->pipeline_id;
        $leadStage->save();

        return (new LeadStageResource($leadStage))->additional(['message' => 'Lead Stage successfully updated.']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\LeadStage  $leadStage
     * @return \Illuminate\Http\Response
     */
    public function destroy(LeadStage $leadStage)
    {
        if (Gate::denies('delete lead stage')) {
            return response()->json(['error' => 'Permission denied.'], 403);
        }

        if ($leadStage->created_by != Auth::user()->ownerId()) {
            return response()->json(['error' => 'Permission denied.'], 403);
        }

        $leadStage->delete();

        return response()->json(['message' => 'Lead Stage successfully deleted.']);
    }
}
