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

    public function discussionStore(Request $request, Lead $lead)
    {
        if (Gate::denies('edit lead')) {
            return response()->json(['error' => 'Permission denied.'], 403);
        }

        if ($lead->created_by != Auth::user()->creatorId()) {
            return response()->json(['error' => 'Permission denied.'], 403);
        }

        $validator = \Validator::make($request->all(), ['comment' => 'required']);
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()], 422);
        }

        $discussion = \App\Models\LeadDiscussion::create([
            'lead_id' => $lead->id,
            'comment' => $request->comment,
            'created_by' => Auth::user()->id,
        ]);

        \App\Models\LeadActivityLog::create([
            'user_id' => Auth::user()->id,
            'lead_id' => $lead->id,
            'log_type' => 'Add Discussion',
            'remark' => json_encode(['title' => 'Discussion added']),
        ]);

        return (new \App\Http\Resources\LeadDiscussionResource($discussion))->additional(['message' => 'Discussion successfully added.']);
    }

    public function callStore(Request $request, Lead $lead)
    {
        if (Gate::denies('edit lead')) {
            return response()->json(['error' => 'Permission denied.'], 403);
        }

        if ($lead->created_by != Auth::user()->creatorId()) {
            return response()->json(['error' => 'Permission denied.'], 403);
        }

        $validator = \Validator::make($request->all(), [
            'subject' => 'required',
            'call_type' => 'required',
            'user_id' => 'required|exists:users,id',
        ]);
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()], 422);
        }

        $call = \App\Models\LeadCall::create([
            'lead_id' => $lead->id,
            'subject' => $request->subject,
            'call_type' => $request->call_type,
            'duration' => $request->duration,
            'user_id' => $request->user_id,
            'description' => $request->description,
            'call_result' => $request->call_result,
        ]);

        \App\Models\LeadActivityLog::create([
            'user_id' => Auth::user()->id,
            'lead_id' => $lead->id,
            'log_type' => 'Add Call',
            'remark' => json_encode(['title' => 'Call added']),
        ]);

        return (new \App\Http\Resources\LeadCallResource($call))->additional(['message' => 'Call successfully created.']);
    }

    public function emailStore(Request $request, Lead $lead)
    {
        if (Gate::denies('edit lead')) {
            return response()->json(['error' => 'Permission denied.'], 403);
        }

        if ($lead->created_by != Auth::user()->creatorId()) {
            return response()->json(['error' => 'Permission denied.'], 403);
        }

        $validator = \Validator::make($request->all(), [
            'to' => 'required|email',
            'subject' => 'required',
            'description' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()], 422);
        }

        $email = \App\Models\LeadEmail::create([
            'lead_id' => $lead->id,
            'to' => $request->to,
            'subject' => $request->subject,
            'description' => $request->description,
        ]);

        \App\Models\LeadActivityLog::create([
            'user_id' => Auth::user()->id,
            'lead_id' => $lead->id,
            'log_type' => 'Add Email',
            'remark' => json_encode(['title' => 'Email sent']),
        ]);

        return (new \App\Http\Resources\LeadEmailResource($email))->additional(['message' => 'Email successfully created.']);
    }

    public function order(Request $request)
    {
        if (Gate::denies('edit lead')) {
            return response()->json(['error' => 'Permission denied.'], 403);
        }

        $user = Auth::user();
        $creatorId = $user->creatorId();

        $leads = Lead::where('created_by', '=', $creatorId)->get();

        foreach ($leads as $lead) {
            $lead->order = 0;
            $lead->save();
        }

        foreach ($request->all() as $key => $items) {
            if ($key == 'owner' || $key == 'usr') {
                continue;
            }

            foreach ($items as $item) {
                $lead = Lead::find($item);
                if ($lead && $lead->created_by == $creatorId) {
                    $lead->stage_id = $key;
                    $lead->save();
                }
            }
        }

        return response()->json(['message' => 'Leads successfully ordered.']);
    }

    public function convertToDeal(Request $request, Lead $lead)
    {
        if (Gate::denies('convert lead to deal')) {
            return response()->json(['error' => 'Permission denied.'], 403);
        }

        if ($lead->created_by != Auth::user()->creatorId()) {
            return response()->json(['error' => 'Permission denied.'], 403);
        }

        $validator = \Validator::make($request->all(), [
            'name' => 'required',
            'price' => 'required|numeric|min:0',
            'clients' => 'required|array|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()], 422);
        }

        $deal = \App\Models\Deal::create([
            'name' => $request->name,
            'price' => $request->price,
            'pipeline_id' => $lead->pipeline_id,
            'stage_id' => $lead->stage_id,
            'sources' => $lead->sources,
            'products' => $lead->products,
            'notes' => $lead->notes,
            'created_by' => $lead->created_by,
        ]);

        foreach ($request->clients as $client_id) {
            \App\Models\ClientDeal::create([
                'deal_id' => $deal->id,
                'client_id' => $client_id,
            ]);
        }

        $lead->is_converted = 1;
        $lead->save();

        return (new \App\Http\Resources\DealResource($deal->load('pipeline', 'stage', 'sources', 'products')))->additional(['message' => 'Lead successfully converted to deal.']);
    }
}
