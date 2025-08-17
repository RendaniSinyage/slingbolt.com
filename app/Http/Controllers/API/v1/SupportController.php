<?php

namespace App\Http\Controllers\API\v1;

use App\Http\Controllers\Controller;
use App\Models\Support;
use Illuminate\Http\Request;
use App\Http\Resources\SupportResource;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Auth;

class SupportController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if (Gate::denies('manage support')) {
            return response()->json(['error' => 'Permission denied.'], 403);
        }

        if (Auth::user()->type == 'company') {
            $supports = Support::where('created_by', Auth::user()->creatorId())->with(['createdBy', 'assignUser'])->get();
        } else {
            $supports = Support::where('user', Auth::user()->id)->orWhere('ticket_created', Auth::user()->id)->where('created_by', Auth::user()->creatorId())->with(['createdBy', 'assignUser'])->get();
        }

        return SupportResource::collection($supports);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        if (Gate::denies('create support')) {
            return response()->json(['error' => 'Permission denied.'], 403);
        }

        $validator = \Validator::make(
            $request->all(), [
                'subject' => 'required',
                'priority' => 'required',
            ]
        );

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $support = new Support();
        $support->subject = $request->subject;
        $support->priority = $request->priority;
        $support->end_date = $request->end_date;
        $support->ticket_code = date('hms');
        $support->status = 'Open';
        $support->description = $request->description;
        $support->created_by = Auth::user()->creatorId();
        $support->ticket_created = Auth::id();

        if (Auth::user()->type == 'company') {
            $support->user = $request->user;
        } else {
            $support->user = Auth::id();
        }

        $support->save();

        return (new SupportResource($support->load(['createdBy', 'assignUser'])))->additional(['message' => 'Support successfully created.']);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Support  $support
     * @return \Illuminate\Http\Response
     */
    public function show(Support $support)
    {
        if (Gate::denies('manage support')) {
            return response()->json(['error' => 'Permission denied.'], 403);
        }

        if ($support->created_by != Auth::user()->creatorId() && $support->user != Auth::id() && $support->ticket_created != Auth::id()) {
            return response()->json(['error' => 'Permission denied.'], 403);
        }

        return new SupportResource($support->load(['createdBy', 'assignUser', 'replies']));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Support  $support
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Support $support)
    {
        if (Gate::denies('edit support')) {
            return response()->json(['error' => 'Permission denied.'], 403);
        }

        if ($support->created_by != Auth::user()->creatorId()) {
            return response()->json(['error' => 'Permission denied.'], 403);
        }

        $validator = \Validator::make(
            $request->all(), [
                'subject' => 'sometimes|required',
                'priority' => 'sometimes|required',
            ]
        );

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $support->update($request->all());

        return (new SupportResource($support->load(['createdBy', 'assignUser'])))->additional(['message' => 'Support successfully updated.']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Support  $support
     * @return \Illuminate\Http\Response
     */
    public function destroy(Support $support)
    {
        if (Gate::denies('delete support')) {
            return response()->json(['error' => 'Permission denied.'], 403);
        }

        if ($support->created_by != Auth::user()->creatorId()) {
            return response()->json(['error' => 'Permission denied.'], 403);
        }

        $support->delete();

        return response()->json(['message' => 'Support successfully deleted.']);
    }
}
