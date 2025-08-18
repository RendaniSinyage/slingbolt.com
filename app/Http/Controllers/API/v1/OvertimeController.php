<?php

namespace App\Http\Controllers\API\v1;

use App\Http\Controllers\Controller;
use App\Models\Overtime;
use Illuminate\Http\Request;
use App\Http\Resources\OvertimeResource;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Auth;

class OvertimeController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if (Gate::denies('manage overtime')) {
            return response()->json(['error' => 'Permission denied.'], 403);
        }

        $overtimes = Overtime::where('created_by', '=', Auth::user()->creatorId())->get();

        return OvertimeResource::collection($overtimes);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        if (Gate::denies('create overtime')) {
            return response()->json(['error' => 'Permission denied.'], 403);
        }

        $validator = \Validator::make(
            $request->all(), [
                'employee_id' => 'required|exists:employees,id',
                'title' => 'required',
                'number_of_days' => 'required|numeric',
                'hours' => 'required|numeric',
                'rate' => 'required|numeric',
            ]
        );

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $overtime = new Overtime();
        $overtime->employee_id = $request->employee_id;
        $overtime->title = $request->title;
        $overtime->number_of_days = $request->number_of_days;
        $overtime->hours = $request->hours;
        $overtime->rate = $request->rate;
        $overtime->created_by = Auth::user()->creatorId();
        $overtime->save();

        return (new OvertimeResource($overtime))->additional(['message' => 'Overtime successfully created.']);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Overtime  $overtime
     * @return \Illuminate\Http\Response
     */
    public function show(Overtime $overtime)
    {
        if (Gate::denies('manage overtime')) {
            return response()->json(['error' => 'Permission denied.'], 403);
        }

        if ($overtime->created_by != Auth::user()->creatorId()) {
            return response()->json(['error' => 'Permission denied.'], 403);
        }

        return new OvertimeResource($overtime);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Overtime  $overtime
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Overtime $overtime)
    {
        if (Gate::denies('edit overtime')) {
            return response()->json(['error' => 'Permission denied.'], 403);
        }

        if ($overtime->created_by != Auth::user()->creatorId()) {
            return response()->json(['error' => 'Permission denied.'], 403);
        }

        $validator = \Validator::make(
            $request->all(), [
                'title' => 'sometimes|required',
                'number_of_days' => 'sometimes|required|numeric',
                'hours' => 'sometimes|required|numeric',
                'rate' => 'sometimes|required|numeric',
            ]
        );

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $overtime->update($request->all());

        return (new OvertimeResource($overtime))->additional(['message' => 'Overtime successfully updated.']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Overtime  $overtime
     * @return \Illuminate\Http\Response
     */
    public function destroy(Overtime $overtime)
    {
        if (Gate::denies('delete overtime')) {
            return response()->json(['error' => 'Permission denied.'], 403);
        }

        if ($overtime->created_by != Auth::user()->creatorId()) {
            return response()->json(['error' => 'Permission denied.'], 403);
        }

        $overtime->delete();

        return response()->json(['message' => 'Overtime successfully deleted.']);
    }
}
