<?php

namespace App\Http\Controllers\API\v1;

use App\Http\Controllers\Controller;
use App\Models\Warning;
use App\Models\Employee;
use Illuminate\Http\Request;
use App\Http\Resources\WarningResource;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Auth;

class WarningController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if (Gate::denies('manage warning')) {
            return response()->json(['error' => 'Permission denied.'], 403);
        }

        if (Auth::user()->type == 'employee') {
            $employee = Employee::where('user_id', '=', Auth::user()->id)->first();
            $warnings = Warning::where('warning_by', '=', $employee->id)->with(['warningTo', 'warningBy'])->get();
        } else {
            $warnings = Warning::where('created_by', '=', Auth::user()->creatorId())->with(['warningTo', 'warningBy'])->get();
        }

        return WarningResource::collection($warnings);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        if (Gate::denies('create warning')) {
            return response()->json(['error' => 'Permission denied.'], 403);
        }

        $validator = \Validator::make(
            $request->all(), [
                'warning_to' => 'required|exists:employees,id',
                'subject' => 'required',
                'warning_date' => 'required|date',
            ]
        );

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $warning_by_id = null;
        if (Auth::user()->type == 'employee') {
            $employee = Employee::where('user_id', '=', Auth::user()->id)->first();
            $warning_by_id = $employee->id;
        } else {
            $validator = \Validator::make(
                $request->all(), [
                    'warning_by' => 'required|exists:employees,id',
                ]
            );
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            $warning_by_id = $request->warning_by;
        }

        $warning = new Warning();
        $warning->warning_by = $warning_by_id;
        $warning->warning_to = $request->warning_to;
        $warning->subject = $request->subject;
        $warning->warning_date = $request->warning_date;
        $warning->description = $request->description;
        $warning->created_by = Auth::user()->creatorId();
        $warning->save();

        return (new WarningResource($warning->load(['warningTo', 'warningBy'])))->additional(['message' => 'Warning successfully created.']);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Warning  $warning
     * @return \Illuminate\Http\Response
     */
    public function show(Warning $warning)
    {
        if (Gate::denies('manage warning')) {
            return response()->json(['error' => 'Permission denied.'], 403);
        }

        if ($warning->created_by != Auth::user()->creatorId()) {
            return response()->json(['error' => 'Permission denied.'], 403);
        }

        return new WarningResource($warning->load(['warningTo', 'warningBy']));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Warning  $warning
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Warning $warning)
    {
        if (Gate::denies('edit warning')) {
            return response()->json(['error' => 'Permission denied.'], 403);
        }

        if ($warning->created_by != Auth::user()->creatorId()) {
            return response()->json(['error' => 'Permission denied.'], 403);
        }

        $validator = \Validator::make(
            $request->all(), [
                'warning_to' => 'sometimes|required|exists:employees,id',
                'subject' => 'sometimes|required',
                'warning_date' => 'sometimes|required|date',
            ]
        );

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $warning->update($request->all());

        return (new WarningResource($warning->load(['warningTo', 'warningBy'])))->additional(['message' => 'Warning successfully updated.']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Warning  $warning
     * @return \Illuminate\Http\Response
     */
    public function destroy(Warning $warning)
    {
        if (Gate::denies('delete warning')) {
            return response()->json(['error' => 'Permission denied.'], 403);
        }

        if ($warning->created_by != Auth::user()->creatorId()) {
            return response()->json(['error' => 'Permission denied.'], 403);
        }

        $warning->delete();

        return response()->json(['message' => 'Warning successfully deleted.']);
    }
}
