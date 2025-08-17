<?php

namespace App\Http\Controllers\API\v1;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use App\Models\AnnouncementEmployee;
use Illuminate\Http\Request;
use App\Http\Resources\AnnouncementResource;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Auth;

class AnnouncementController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if (Gate::denies('manage announcement')) {
            return response()->json(['error' => 'Permission denied.'], 403);
        }

        if (Auth::user()->type == 'employee') {
            $employee = Employee::where('user_id', '=', Auth::user()->id)->first();
            $announcements = Announcement::orderBy('announcements.id', 'desc')
                ->leftjoin('announcement_employees', 'announcements.id', '=', 'announcement_employees.announcement_id')
                ->where('announcement_employees.employee_id', '=', $employee->id)
                ->orWhere(
                    function ($q) {
                        $q->where('announcements.department_id', '["0"]')->where('announcements.employee_id', '["0"]');
                    }
                )->get();
        } else {
            $announcements = Announcement::where('created_by', '=', Auth::user()->creatorId())->get();
        }

        return AnnouncementResource::collection($announcements);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        if (Gate::denies('create announcement')) {
            return response()->json(['error' => 'Permission denied.'], 403);
        }

        $validator = \Validator::make(
            $request->all(), [
                'title' => 'required',
                'start_date' => 'required|date',
                'end_date' => 'required|date',
                'branch_id' => 'required',
                'department_id' => 'required|array',
                'employee_id' => 'required|array',
            ]
        );

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $announcement = new Announcement();
        $announcement->title = $request->title;
        $announcement->start_date = $request->start_date;
        $announcement->end_date = $request->end_date;
        $announcement->branch_id = $request->branch_id;
        $announcement->department_id = json_encode($request->department_id);
        $announcement->employee_id = json_encode($request->employee_id);
        $announcement->description = $request->description;
        $announcement->created_by = Auth::user()->creatorId();
        $announcement->save();

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
            $announcementEmployee = new AnnouncementEmployee();
            $announcementEmployee->announcement_id = $announcement->id;
            $announcementEmployee->employee_id = $employee_id;
            $announcementEmployee->created_by = Auth::user()->creatorId();
            $announcementEmployee->save();
        }


        return (new AnnouncementResource($announcement))->additional(['message' => 'Announcement successfully created.']);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Announcement  $announcement
     * @return \Illuminate\Http\Response
     */
    public function show(Announcement $announcement)
    {
        if (Gate::denies('manage announcement')) {
            return response()->json(['error' => 'Permission denied.'], 403);
        }

        if ($announcement->created_by != Auth::user()->creatorId()) {
            return response()->json(['error' => 'Permission denied.'], 403);
        }

        return new AnnouncementResource($announcement);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Announcement  $announcement
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Announcement $announcement)
    {
        if (Gate::denies('edit announcement')) {
            return response()->json(['error' => 'Permission denied.'], 403);
        }

        if ($announcement->created_by != Auth::user()->creatorId()) {
            return response()->json(['error' => 'Permission denied.'], 403);
        }

        $validator = \Validator::make(
            $request->all(), [
                'title' => 'sometimes|required',
                'start_date' => 'sometimes|required|date',
                'end_date' => 'sometimes|required|date',
                'branch_id' => 'sometimes|required',
                'department_id' => 'sometimes|required|array',
            ]
        );

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $announcement->update($request->all());

        return (new AnnouncementResource($announcement))->additional(['message' => 'Announcement successfully updated.']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Announcement  $announcement
     * @return \Illuminate\Http\Response
     */
    public function destroy(Announcement $announcement)
    {
        if (Gate::denies('delete announcement')) {
            return response()->json(['error' => 'Permission denied.'], 403);
        }

        if ($announcement->created_by != Auth::user()->creatorId()) {
            return response()->json(['error' => 'Permission denied.'], 403);
        }

        $announcement->delete();

        return response()->json(['message' => 'Announcement successfully deleted.']);
    }
}
