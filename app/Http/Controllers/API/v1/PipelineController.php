<?php

namespace App\Http\Controllers\API\v1;

use App\Http\Controllers\Controller;
use App\Http\Resources\PipelineResource;
use App\Models\Pipeline;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class PipelineController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        if (Gate::denies('manage pipeline')) {
            return response()->json(['error' => __('Permission Denied.')], 403);
        }

        $pipelines = Pipeline::where('created_by', '=', Auth::user()->creatorId())->get();

        return PipelineResource::collection($pipelines);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        if (Gate::denies('create pipeline')) {
            return response()->json(['error' => __('Permission Denied.')], 403);
        }

        $validator = \Validator::make(
            $request->all(), [
                'name' => 'required|max:20',
            ]
        );

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()], 422);
        }

        $pipeline = new Pipeline();
        $pipeline->name = $request->name;
        $pipeline->created_by = Auth::user()->creatorId();
        $pipeline->save();

        return (new PipelineResource($pipeline))->additional(['message' => __('Pipeline successfully created.')]);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Pipeline  $pipeline
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Pipeline $pipeline)
    {
        if (Gate::denies('manage pipeline') || $pipeline->created_by != Auth::user()->creatorId()) {
            return response()->json(['error' => __('Permission Denied.')], 403);
        }

        return new PipelineResource($pipeline);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Pipeline  $pipeline
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, Pipeline $pipeline)
    {
        if (Gate::denies('edit pipeline') || $pipeline->created_by != Auth::user()->creatorId()) {
            return response()->json(['error' => __('Permission Denied.')], 403);
        }

        $validator = \Validator::make(
            $request->all(), [
                'name' => 'required|max:20',
            ]
        );

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()], 422);
        }

        $pipeline->name = $request->name;
        $pipeline->save();

        return (new PipelineResource($pipeline))->additional(['message' => __('Pipeline successfully updated.')]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Pipeline  $pipeline
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Pipeline $pipeline)
    {
        if (Gate::denies('delete pipeline') || $pipeline->created_by != Auth::user()->creatorId()) {
            return response()->json(['error' => __('Permission Denied.')], 403);
        }

        if ($pipeline->stages()->count() > 0) {
            return response()->json(['error' => __('There are some Stages and Deals on Pipeline, please remove it first!')], 422);
        }

        $pipeline->delete();

        return response()->json(['message' => __('Pipeline successfully deleted.')], 200);
    }
}
