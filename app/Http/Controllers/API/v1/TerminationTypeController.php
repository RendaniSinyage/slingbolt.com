<?php

namespace App\Http\Controllers\API\v1;

use App\Http\Controllers\Controller;
use App\Models\TerminationType;
use Illuminate\Http\Request;
use App\Http\Resources\TerminationTypeResource;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Auth;

class TerminationTypeController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if (Gate::denies('manage termination type')) {
            return response()->json(['error' => 'Permission denied.'], 403);
        }

        $terminationtypes = TerminationType::where('created_by', '=', Auth::user()->creatorId())->get();

        return TerminationTypeResource::collection($terminationtypes);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        if (Gate::denies('create termination type')) {
            return response()->json(['error' => 'Permission denied.'], 403);
        }

        $validator = \Validator::make(
            $request->all(), [
                               'name' => 'required|max:255',
                           ]
        );

        if($validator->fails())
        {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $terminationtype             = new TerminationType();
        $terminationtype->name       = $request->name;
        $terminationtype->created_by = Auth::user()->creatorId();
        $terminationtype->save();

        return (new TerminationTypeResource($terminationtype))->additional(['message' => __('TerminationType successfully created.')]);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\TerminationType  $terminationType
     * @return \Illuminate\Http\Response
     */
    public function show(TerminationType $terminationType)
    {
        if (Gate::denies('manage termination type')) {
            return response()->json(['error' => 'Permission denied.'], 403);
        }

        if($terminationType->created_by != Auth::user()->creatorId())
        {
            return response()->json(['error' => __('Permission denied.')], 403);
        }

        return new TerminationTypeResource($terminationType);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\TerminationType  $terminationType
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, TerminationType $terminationType)
    {
        if (Gate::denies('edit termination type')) {
            return response()->json(['error' => 'Permission denied.'], 403);
        }

        if($terminationType->created_by != Auth::user()->creatorId())
        {
            return response()->json(['error' => __('Permission denied.')], 403);
        }

        $validator = \Validator::make(
            $request->all(), [
                               'name' => 'required|max:255',
                           ]
        );

        if($validator->fails())
        {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $terminationType->name = $request->name;
        $terminationType->save();

        return (new TerminationTypeResource($terminationType))->additional(['message' => __('TerminationType successfully updated.')]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\TerminationType  $terminationType
     * @return \Illuminate\Http\Response
     */
    public function destroy(TerminationType $terminationType)
    {
        if (Gate::denies('delete termination type')) {
            return response()->json(['error' => 'Permission denied.'], 403);
        }

        if($terminationType->created_by != Auth::user()->creatorId())
        {
            return response()->json(['error' => __('Permission denied.')], 403);
        }

        $terminationType->delete();

        return response()->json(['message' => __('TerminationType successfully deleted.')]);
    }
}
