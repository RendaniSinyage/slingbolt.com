<?php

namespace App\Http\Controllers\API\v1;

use App\Http\Controllers\Controller;
use App\Models\Lead;
use App\Models\LeadStage;
use App\Models\Pipeline;
use App\Models\User;
use App\Models\UserLead;
use Illuminate\Http\Request;
use App\Http\Resources\LeadResource;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Auth;

class LeadController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        if (Gate::denies('manage lead')) {
            return response()->json(['error' => 'Permission denied.'], 403);
        }

        $user = Auth::user();
        $pipeline_id = $request->input('pipeline_id', $user->default_pipeline);

        if (!$pipeline_id) {
            $pipeline = Pipeline::where('created_by', '=', $user->creatorId())->first();
        } else {
            $pipeline = Pipeline::where('created_by', '=', $user->creatorId())->where('id', '=', $pipeline_id)->first();
        }

        if (!$pipeline) {
            return response()->json(['data' => [], 'message' => 'No pipeline found.']);
        }

        $query = Lead::select('leads.*')
            ->join('user_leads', 'user_leads.lead_id', '=', 'leads.id')
            ->where('user_leads.user_id', '=', $user->id)
            ->where('leads.pipeline_id', '=', $pipeline->id);

        if ($request->has('stage_id')) {
            $query->where('leads.stage_id', $request->stage_id);
        }

        $leads = $query->orderBy('leads.order')->get();

        return LeadResource::collection($leads);
    }


    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        if (Gate::denies('create lead')) {
            return response()->json(['error' => 'Permission denied.'], 403);
        }

        $validator = \Validator::make(
            $request->all(), [
                'subject' => 'required',
                'name' => 'required',
                'email' => 'required|email',
                'user_id' => 'required|exists:users,id',
            ]
        );

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user = Auth::user();

        if ($user->default_pipeline) {
            $pipeline = Pipeline::where('created_by', '=', $user->creatorId())->where('id', '=', $user->default_pipeline)->first();
        } else {
            $pipeline = Pipeline::where('created_by', '=', $user->creatorId())->first();
        }

        if (!$pipeline) {
            return response()->json(['error' => 'No pipeline found.'], 404);
        }

        $stage = LeadStage::where('pipeline_id', '=', $pipeline->id)->first();

        if (!$stage) {
            return response()->json(['error' => 'Please create a stage for this pipeline.'], 404);
        }

        $lead = new Lead();
        $lead->name = $request->name;
        $lead->email = $request->email;
        $lead->phone = $request->phone;
        $lead->subject = $request->subject;
        $lead->user_id = $request->user_id;
        $lead->pipeline_id = $pipeline->id;
        $lead->stage_id = $stage->id;
        $lead->created_by = $user->creatorId();
        $lead->date = date('Y-m-d');
        $lead->save();

        // Assign lead to users
        $assignees = [$user->id];
        if ($request->user_id != $user->id) {
            $assignees[] = $request->user_id;
        }

        foreach ($assignees as $assignee_id) {
            UserLead::create([
                'user_id' => $assignee_id,
                'lead_id' => $lead->id,
            ]);
        }

        return (new LeadResource($lead->load('pipeline', 'stage', 'users')))->additional(['message' => 'Lead successfully created.']);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Lead  $lead
     * @return \Illuminate\Http\Response
     */
    public function show(Lead $lead)
    {
        if (Gate::denies('manage lead')) {
            return response()->json(['error' => 'Permission denied.'], 403);
        }

        if ($lead->created_by != Auth::user()->creatorId()) {
            return response()->json(['error' => 'Permission denied.'], 403);
        }

        return new LeadResource($lead->load('pipeline', 'stage', 'users', 'discussions', 'files'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Lead  $lead
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Lead $lead)
    {
        if (Gate::denies('edit lead')) {
            return response()->json(['error' => 'Permission denied.'], 403);
        }

        if ($lead->created_by != Auth::user()->creatorId()) {
            return response()->json(['error' => 'Permission denied.'], 403);
        }

        $validator = \Validator::make(
            $request->all(), [
                'subject' => 'sometimes|required',
                'name' => 'sometimes|required',
                'email' => 'sometimes|required|email',
                'pipeline_id' => 'sometimes|required|exists:pipelines,id',
                'stage_id' => 'sometimes|required|exists:lead_stages,id',
                'user_id' => 'sometimes|required|exists:users,id',
            ]
        );

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $lead->fill($request->only([
            'name', 'email', 'phone', 'subject', 'user_id', 'pipeline_id', 'stage_id', 'notes'
        ]));

        if($request->has('sources')) {
            $lead->sources = implode(",", array_filter($request->sources));
        }
        if($request->has('products')) {
            $lead->products = implode(",", array_filter($request->products));
        }

        $lead->save();

        return (new LeadResource($lead->load('pipeline', 'stage', 'users')))->additional(['message' => 'Lead successfully updated.']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Lead  $lead
     * @return \Illuminate\Http\Response
     */
    public function destroy(Lead $lead)
    {
        if (Gate::denies('delete lead')) {
            return response()->json(['error' => 'Permission denied.'], 403);
        }

        if ($lead->created_by != Auth::user()->creatorId()) {
            return response()->json(['error' => 'Permission denied.'], 403);
        }

        $lead->delete();

        return response()->json(['message' => 'Lead successfully deleted.']);
    }
}
