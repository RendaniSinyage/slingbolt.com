<?php

namespace App\Http\Controllers\API\v1;

use App\Http\Controllers\Controller;
use App\Models\Stage;
use App\Models\Pipeline;
use Illuminate\Http\Request;
use App\Http\Resources\StageResource;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Auth;

class StageController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        if (Gate::denies('manage stage')) {
            return response()->json(['error' => 'Permission denied.'], 403);
        }

        $query = Stage::query();

        if ($request->has('pipeline_id')) {
            $query->where('pipeline_id', $request->pipeline_id);
        }

        $stages = $query->where('created_by', '=', Auth::user()->ownerId())
                        ->orderBy('pipeline_id')
                        ->orderBy('order')
                        ->get();

        return StageResource::collection($stages);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        if (Gate::denies('create stage')) {
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

        $stage = new Stage();
        $stage->name = $request->name;
        $stage->pipeline_id = $request->pipeline_id;
        $stage->created_by = Auth::user()->ownerId();
        $stage->save();

        return (new StageResource($stage))->additional(['message' => 'Deal Stage successfully created.']);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Stage  $stage
     * @return \Illuminate\Http\Response
     */
    public function show(Stage $stage)
    {
        if (Gate::denies('manage stage')) {
            return response()->json(['error' => 'Permission denied.'], 403);
        }

        if ($stage->created_by != Auth::user()->ownerId()) {
            return response()->json(['error' => 'Permission denied.'], 403);
        }

        return new StageResource($stage);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Stage  $stage
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Stage $stage)
    {
        if (Gate::denies('edit stage')) {
            return response()->json(['error' => 'Permission denied.'], 403);
        }

        if ($stage->created_by != Auth::user()->ownerId()) {
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

        $stage->name = $request->name;
        $stage->pipeline_id = $request->pipeline_id;
        $stage->save();

        return (new StageResource($stage))->additional(['message' => 'Deal Stage successfully updated.']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Stage  $stage
     * @return \Illuminate\Http\Response
     */
    public function destroy(Stage $stage)
    {
        if (Gate::denies('delete stage')) {
            return response()->json(['error' => 'Permission denied.'], 403);
        }

        if ($stage->created_by != Auth::user()->ownerId()) {
            return response()->json(['error' => 'Permission denied.'], 403);
        }

        $stage->delete();

        return response()->json(['message' => 'Deal Stage successfully deleted.']);
    }
}
