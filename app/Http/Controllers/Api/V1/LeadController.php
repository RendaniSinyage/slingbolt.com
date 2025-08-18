<?php

namespace App\Http\Controllers\API\v1;

use App\Http\Controllers\Controller;
use App\Models\Lead;
use App\Models\LeadStage;
use App\Models\Pipeline;
use App\Models\UserLead;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class LeadController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        if ($user->default_pipeline) {
            $pipeline = Pipeline::where('created_by', $user->creatorId())->where('id', $user->default_pipeline)->first();
        } else {
            $pipeline = Pipeline::where('created_by', $user->creatorId())->first();
        }

        if (!$pipeline) {
            return response()->json(['error' => 'No pipeline found for this user.'], 404);
        }

        $leads = Lead::select('leads.*')
            ->join('user_leads', 'user_leads.lead_id', '=', 'leads.id')
            ->where('user_leads.user_id', '=', $user->id)
            ->where('leads.pipeline_id', '=', $pipeline->id)
            ->orderBy('leads.order')
            ->get();

        return response()->json($leads);
    }

    public function show($id)
    {
        $user = request()->user();
        $lead = Lead::with(['stage', 'users', 'products', 'sources'])->find($id);

        if (!$lead || $lead->created_by != $user->creatorId()) {
            return response()->json(['error' => 'Lead not found or permission denied.'], 404);
        }

        return response()->json($lead);
    }

    public function store(Request $request)
    {
        $user = $request->user();
        $creatorId = $user->creatorId();

        $validator = Validator::make($request->all(), [
            'subject' => 'required',
            'name' => 'required',
            'email' => 'required|email',
            'user_id' => 'required|exists:users,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()], 400);
        }

        if ($user->default_pipeline) {
            $pipeline = Pipeline::where('created_by', $creatorId)->where('id', $user->default_pipeline)->first();
        } else {
            $pipeline = Pipeline::where('created_by', $creatorId)->first();
        }

        if (!$pipeline) {
            return response()->json(['error' => 'No pipeline found for this user.'], 404);
        }
        $stage = LeadStage::where('pipeline_id', $pipeline->id)->first();
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
        $lead->created_by = $creatorId;
        $lead->date = date('Y-m-d');
        $lead->save();

        UserLead::create(['user_id' => $request->user_id, 'lead_id' => $lead->id]);
        if ($user->id != $request->user_id) {
             UserLead::create(['user_id' => $user->id, 'lead_id' => $lead->id]);
        }

        return response()->json($lead, 201);
    }

    public function update(Request $request, $id)
    {
        $user = $request->user();
        $lead = Lead::where('created_by', $user->creatorId())->find($id);
        if (!$lead) {
            return response()->json(['error' => 'Lead not found.'], 404);
        }

        $validator = Validator::make($request->all(), [
            'subject' => 'required',
            'name' => 'required',
            'email' => 'required|email',
            'pipeline_id' => 'required|exists:pipelines,id',
            'stage_id' => 'required|exists:lead_stages,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()], 400);
        }

        $lead->update($request->all());

        return response()->json($lead);
    }

    public function destroy($id)
    {
        $user = request()->user();
        $lead = Lead::where('created_by', $user->creatorId())->find($id);

        if (!$lead) {
            return response()->json(['error' => 'Lead not found.'], 404);
        }

        $lead->delete();
        return response()->json(['success' => 'Lead successfully deleted.'], 200);
    }
}
