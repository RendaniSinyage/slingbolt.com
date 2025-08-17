<?php

namespace App\Http\Controllers\API\v1;

use App\Http\Controllers\Controller;
use App\Models\Meeting;
use App\Models\MeetingEmployee;
use Illuminate\Http\Request;
use App\Http\Resources\MeetingResource;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Auth;

class MeetingController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if (Gate::denies('manage meeting')) {
            return response()->json(['error' => 'Permission denied.'], 403);
        }

        if (Auth::user()->type == 'employee') {
            $current_employee = \App\Models\Employee::where('user_id', '=', Auth::user()->id)->first();
            $meetings = Meeting::orderBy('meetings.id', 'desc')
                ->leftjoin('meeting_employees', 'meetings.id', '=', 'meeting_employees.meeting_id')
                ->where('meeting_employees.employee_id', '=', $current_employee->id)
                ->orWhere(function ($q) {
                    $q->where('meetings.department_id', '["0"]')->where('meetings.employee_id', '["0"]');
                })
                ->get();
        } else {
            $meetings = Meeting::where('created_by', '=', Auth::user()->creatorId())->get();
        }

        return MeetingResource::collection($meetings);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        if (Gate::denies('create meeting')) {
            return response()->json(['error' => 'Permission denied.'], 403);
        }

        $validator = \Validator::make(
            $request->all(), [
                'branch_id' => 'required',
                'department_id' => 'required|array',
                'employee_id' => 'required|array',
                'title' => 'required',
                'date' => 'required|date',
                'time' => 'required',
            ]
        );

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $meeting = new Meeting();
        $meeting->branch_id = $request->branch_id;
        $meeting->department_id = json_encode($request->department_id);
        $meeting->employee_id = json_encode($request->employee_id);
        $meeting->title = $request->title;
        $meeting->date = $request->date;
        $meeting->time = $request->time;
        $meeting->note = $request->note;
        $meeting->created_by = Auth::user()->creatorId();
        $meeting->save();

        if (in_array('0', $request->employee_id)) {
            $departmentEmployee = \App\Models\Employee::whereIn('department_id', $request->department_id)->get()->pluck('id');
        } else {
            $departmentEmployee = $request->employee_id;
        }
        foreach ($departmentEmployee as $employee) {
            $meetingEmployee = new MeetingEmployee();
            $meetingEmployee->meeting_id = $meeting->id;
            $meetingEmployee->employee_id = $employee;
            $meetingEmployee->created_by = Auth::user()->creatorId();
            $meetingEmployee->save();
        }

        return (new MeetingResource($meeting))->additional(['message' => 'Meeting successfully created.']);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Meeting  $meeting
     * @return \Illuminate\Http\Response
     */
    public function show(Meeting $meeting)
    {
        if (Gate::denies('manage meeting')) {
            return response()->json(['error' => 'Permission denied.'], 403);
        }

        if ($meeting->created_by != Auth::user()->creatorId()) {
            return response()->json(['error' => 'Permission denied.'], 403);
        }

        return new MeetingResource($meeting);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Meeting  $meeting
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Meeting $meeting)
    {
        if (Gate::denies('edit meeting')) {
            return response()->json(['error' => 'Permission denied.'], 403);
        }

        if ($meeting->created_by != Auth::user()->creatorId()) {
            return response()->json(['error' => 'Permission denied.'], 403);
        }

        $validator = \Validator::make(
            $request->all(), [
                'title' => 'sometimes|required',
                'date' => 'sometimes|required|date',
                'time' => 'sometimes|required',
            ]
        );

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $meeting->update($request->all());

        return (new MeetingResource($meeting))->additional(['message' => 'Meeting successfully updated.']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Meeting  $meeting
     * @return \Illuminate\Http\Response
     */
    public function destroy(Meeting $meeting)
    {
        if (Gate::denies('delete meeting')) {
            return response()->json(['error' => 'Permission denied.'], 403);
        }

        if ($meeting->created_by != Auth::user()->creatorId()) {
            return response()->json(['error' => 'Permission denied.'], 403);
        }

        $meeting->delete();

        return response()->json(['message' => 'Meeting successfully deleted.']);
    }
}
