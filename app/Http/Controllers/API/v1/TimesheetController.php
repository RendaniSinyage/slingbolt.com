<?php

namespace App\Http\Controllers\API\v1;

use App\Http\Controllers\Controller;
use App\Models\Timesheet;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TimesheetController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param \App\Models\Project $project
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request, Project $project)
    {
        if (Auth::user()->can('manage timesheet') && $project->is_member(Auth::id())) {
            $query = Timesheet::where('project_id', $project->id);

            if ($request->has('task_id')) {
                $query->where('task_id', $request->task_id);
            }
            if ($request->has('start_date') && $request->has('end_date')) {
                $query->whereBetween('date', [$request->start_date, $request->end_date]);
            }

            $timesheets = $query->with('task')->get();
            return response()->json($timesheets);
        }

        return response()->json(['error' => __('Permission denied.')], 403);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param \App\Models\Project $project
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request, Project $project)
    {
        if (Auth::user()->can('create timesheet') && $project->is_member(Auth::id())) {
            $validator = \Validator::make($request->all(), [
                'task_id' => 'required|exists:project_tasks,id',
                'date' => 'required|date',
                'time' => 'required|date_format:H:i',
                'description' => 'nullable|string',
            ]);

            if ($validator->fails()) {
                return response()->json(['error' => $validator->errors()->first()], 400);
            }

            $timesheet = new Timesheet();
            $timesheet->project_id = $project->id;
            $timesheet->task_id = $request->task_id;
            $timesheet->date = $request->date;
            $timesheet->time = $request->time;
            $timesheet->description = $request->description;
            $timesheet->created_by = Auth::id();
            $timesheet->save();

            return response()->json($timesheet, 201);
        }

        return response()->json(['error' => __('Permission denied.')], 403);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Timesheet  $timesheet
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Timesheet $timesheet)
    {
        // Ensure user has access to the project this timesheet belongs to
        if (Auth::user()->can('manage timesheet') && $timesheet->project->is_member(Auth::id())) {
            return response()->json($timesheet->load('project', 'task'));
        }

        return response()->json(['error' => __('Permission denied.')], 403);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Timesheet  $timesheet
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, Timesheet $timesheet)
    {
        if (Auth::user()->can('edit timesheet') && $timesheet->created_by == Auth::id()) {
             $validator = \Validator::make($request->all(), [
                'task_id' => 'required|exists:project_tasks,id',
                'date' => 'required|date',
                'time' => 'required|date_format:H:i',
                'description' => 'nullable|string',
            ]);

            if ($validator->fails()) {
                return response()->json(['error' => $validator->errors()->first()], 400);
            }

            $timesheet->task_id = $request->task_id;
            $timesheet->date = $request->date;
            $timesheet->time = $request->time;
            $timesheet->description = $request->description;
            $timesheet->save();

            return response()->json($timesheet);
        }

        return response()->json(['error' => __('Permission denied.')], 403);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Timesheet  $timesheet
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Timesheet $timesheet)
    {
        if (Auth::user()->can('delete timesheet') && $timesheet->created_by == Auth::id()) {
            $timesheet->delete();
            return response()->json(null, 204);
        }

        return response()->json(['error' => __('Permission denied.')], 403);
    }
}
