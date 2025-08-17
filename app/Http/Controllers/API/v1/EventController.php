<?php

namespace App\Http\Controllers\API\v1;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\EventEmployee;
use Illuminate\Http\Request;
use App\Http\Resources\EventResource;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Auth;

class EventController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if (Gate::denies('manage event')) {
            return response()->json(['error' => 'Permission denied.'], 403);
        }

        $events = Event::where('created_by', '=', Auth::user()->creatorId())->get();

        return EventResource::collection($events);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        if (Gate::denies('create event')) {
            return response()->json(['error' => 'Permission denied.'], 403);
        }

        $validator = \Validator::make(
            $request->all(), [
                'branch_id' => 'required',
                'department_id' => 'required|array',
                'employee_id' => 'required|array',
                'title' => 'required',
                'start_date' => 'required|date',
                'end_date' => 'required|date',
                'color' => 'required',
            ]
        );

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $event = new Event();
        $event->branch_id = $request->branch_id;
        $event->department_id = json_encode($request->department_id);
        $event->employee_id = json_encode($request->employee_id);
        $event->title = $request->title;
        $event->start_date = $request->start_date;
        $event->end_date = $request->end_date;
        $event->color = $request->color;
        $event->description = $request->description;
        $event->created_by = Auth::user()->creatorId();
        $event->save();

        $employee_ids = [];
        if (in_array('0', $request->employee_id)) {
            if (in_array('0', $request->department_id)) {
                if ($request->branch_id == 0) {
                    $employee_ids = \App\Models\Employee::where('created_by', Auth::user()->creatorId())->pluck('id')->toArray();
                } else {
                    $employee_ids = \App\Models\Employee::where('created_by', Auth::user()->creatorId())->where('branch_id', $request->branch_id)->pluck('id')->toArray();
                }
            } else {
                 $employee_ids = \App\Models\Employee::where('created_by', Auth::user()->creatorId())->whereIn('department_id', $request->department_id)->pluck('id')->toArray();
            }
        } else {
            $employee_ids = $request->employee_id;
        }

        foreach ($employee_ids as $employee_id) {
            $eventEmployee = new EventEmployee();
            $eventEmployee->event_id = $event->id;
            $eventEmployee->employee_id = $employee_id;
            $eventEmployee->created_by = Auth::user()->creatorId();
            $eventEmployee->save();
        }

        return (new EventResource($event))->additional(['message' => 'Event successfully created.']);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Event  $event
     * @return \Illuminate\Http\Response
     */
    public function show(Event $event)
    {
        if (Gate::denies('manage event')) {
            return response()->json(['error' => 'Permission denied.'], 403);
        }

        if ($event->created_by != Auth::user()->creatorId()) {
            return response()->json(['error' => 'Permission denied.'], 403);
        }

        return new EventResource($event);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Event  $event
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Event $event)
    {
        if (Gate::denies('edit event')) {
            return response()->json(['error' => 'Permission denied.'], 403);
        }

        if ($event->created_by != Auth::user()->creatorId()) {
            return response()->json(['error' => 'Permission denied.'], 403);
        }

        $validator = \Validator::make(
            $request->all(), [
                'title' => 'sometimes|required',
                'start_date' => 'sometimes|required|date',
                'end_date' => 'sometimes|required|date',
                'color' => 'sometimes|required',
            ]
        );

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $event->update($request->all());

        return (new EventResource($event))->additional(['message' => 'Event successfully updated.']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Event  $event
     * @return \Illuminate\Http\Response
     */
    public function destroy(Event $event)
    {
        if (Gate::denies('delete event')) {
            return response()->json(['error' => 'Permission denied.'], 403);
        }

        if ($event->created_by != Auth::user()->creatorId()) {
            return response()->json(['error' => 'Permission denied.'], 403);
        }

        $event->delete();

        return response()->json(['message' => 'Event successfully deleted.']);
    }
}
