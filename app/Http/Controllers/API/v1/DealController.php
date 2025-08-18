<?php

namespace App\Http\Controllers\API\v1;

use App\Http\Controllers\Controller;
use App\Models\Deal;
use App\Models\Pipeline;
use App\Models\Stage;
use App\Models\User;
use App\Models\UserDeal;
use App\Models\ClientDeal;
use Illuminate\Http\Request;
use App\Http\Resources\DealResource;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Auth;

class DealController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        if (Gate::denies('manage deal')) {
            return response()->json(['error' => 'Permission denied.'], 403);
        }

        $user = Auth::user();
        $pipeline_id = $request->input('pipeline_id', $user->default_pipeline);

        if (!$pipeline_id) {
            $pipeline = Pipeline::where('created_by', '=', $user->ownerId())->first();
        } else {
            $pipeline = Pipeline::where('created_by', '=', $user->ownerId())->where('id', '=', $pipeline_id)->first();
        }

        if (!$pipeline) {
            return response()->json(['data' => [], 'message' => 'No pipeline found.']);
        }

        if ($user->type == 'client') {
            $id_deals = $user->clientDeals->pluck('id');
        } else {
            $id_deals = $user->deals->pluck('id');
        }

        $query = Deal::whereIn('id', $id_deals)->where('pipeline_id', '=', $pipeline->id);

        if ($request->has('stage_id')) {
            $query->where('stage_id', $request->stage_id);
        }

        $deals = $query->orderBy('order')->get();

        return DealResource::collection($deals);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        if (Gate::denies('create deal')) {
            return response()->json(['error' => 'Permission denied.'], 403);
        }

        $validator = \Validator::make(
            $request->all(), [
                'name' => 'required',
                'clients' => 'required|array',
            ]
        );

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user = Auth::user();

        if ($user->default_pipeline) {
            $pipeline = Pipeline::where('created_by', '=', $user->ownerId())->where('id', '=', $user->default_pipeline)->first();
        } else {
            $pipeline = Pipeline::where('created_by', '=', $user->ownerId())->first();
        }

        if (!$pipeline) {
            return response()->json(['error' => 'No pipeline found.'], 404);
        }

        $stage = Stage::where('pipeline_id', '=', $pipeline->id)->first();

        if (!$stage) {
            return response()->json(['error' => 'Please create a stage for this pipeline.'], 404);
        }

        $deal = new Deal();
        $deal->name = $request->name;
        $deal->phone = $request->phone;
        $deal->price = $request->price ?? 0;
        $deal->pipeline_id = $pipeline->id;
        $deal->stage_id = $stage->id;
        $deal->status = 'Active';
        $deal->created_by = $user->ownerId();
        $deal->save();

        foreach ($request->clients as $client_id) {
            ClientDeal::create([
                'deal_id' => $deal->id,
                'client_id' => $client_id,
            ]);
        }

        $assignees = [$user->id];
        if ($user->type != 'company') {
           $assignees[] = $user->ownerId();
        }

        foreach ($assignees as $assignee_id) {
            UserDeal::create([
                'user_id' => $assignee_id,
                'deal_id' => $deal->id,
            ]);
        }

        return (new DealResource($deal->load('pipeline', 'stage', 'users', 'clients')))->additional(['message' => 'Deal successfully created.']);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Deal  $deal
     * @return \Illuminate\Http\Response
     */
    public function show(Deal $deal)
    {
        if (Gate::denies('manage deal')) {
            return response()->json(['error' => 'Permission denied.'], 403);
        }

        if ($deal->created_by != Auth::user()->ownerId()) {
            return response()->json(['error' => 'Permission denied.'], 403);
        }

        return new DealResource($deal->load('pipeline', 'stage', 'users', 'clients', 'discussions', 'files', 'tasks'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Deal  $deal
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Deal $deal)
    {
        if (Gate::denies('edit deal')) {
            return response()->json(['error' => 'Permission denied.'], 403);
        }

        if ($deal->created_by != Auth::user()->ownerId()) {
            return response()->json(['error' => 'Permission denied.'], 403);
        }

        $validator = \Validator::make(
            $request->all(), [
                'name' => 'sometimes|required|max:20',
                'pipeline_id' => 'sometimes|required|exists:pipelines,id',
                'stage_id' => 'sometimes|required|exists:stages,id',
            ]
        );

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $deal->fill($request->only([
            'name', 'phone', 'price', 'pipeline_id', 'stage_id', 'notes'
        ]));

        if($request->has('sources')) {
            $deal->sources = implode(",", array_filter($request->sources));
        }
        if($request->has('products')) {
            $deal->products = implode(",", array_filter($request->products));
        }

        $deal->save();

        return (new DealResource($deal->load('pipeline', 'stage', 'users', 'clients')))->additional(['message' => 'Deal successfully updated.']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Deal  $deal
     * @return \Illuminate\Http\Response
     */
    public function destroy(Deal $deal)
    {
        if (Gate::denies('delete deal')) {
            return response()->json(['error' => 'Permission denied.'], 403);
        }

        if ($deal->created_by != Auth::user()->ownerId()) {
            return response()->json(['error' => 'Permission denied.'], 403);
        }

        $deal->delete();

        return response()->json(['message' => 'Deal successfully deleted.']);
    }
}
