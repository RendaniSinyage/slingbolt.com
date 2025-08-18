<?php

namespace App\Http\Controllers\API\v1;

use App\Http\Controllers\Controller;
use App\Models\Travel;
use App\Models\Employee;
use Illuminate\Http\Request;
use App\Http\Resources\TravelResource;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Auth;

class TravelController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if (Gate::denies('manage travel')) {
            return response()->json(['error' => 'Permission denied.'], 403);
        }

        if (Auth::user()->type == 'employee') {
            $employee = Employee::where('user_id', '=', Auth::user()->id)->first();
            $travels = Travel::where('employee_id', '=', $employee->id)->with('employee')->get();
        } else {
            $travels = Travel::where('created_by', '=', Auth::user()->creatorId())->with('employee')->get();
        }

        return TravelResource::collection($travels);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        if (Gate::denies('create travel')) {
            return response()->json(['error' => 'Permission denied.'], 403);
        }

        $validator = \Validator::make(
            $request->all(), [
                'employee_id' => 'required|exists:employees,id',
                'start_date' => 'required|date',
                'end_date' => 'required|date',
                'purpose_of_visit' => 'required',
                'place_of_visit' => 'required',
            ]
        );

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $travel = new Travel();
        $travel->employee_id = $request->employee_id;
        $travel->start_date = $request->start_date;
        $travel->end_date = $request->end_date;
        $travel->purpose_of_visit = $request->purpose_of_visit;
        $travel->place_of_visit = $request->place_of_visit;
        $travel->description = $request->description;
        $travel->created_by = Auth::user()->creatorId();
        $travel->save();

        return (new TravelResource($travel->load('employee')))->additional(['message' => 'Travel successfully created.']);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Travel  $travel
     * @return \Illuminate\Http\Response
     */
    public function show(Travel $travel)
    {
        if (Gate::denies('manage travel')) {
            return response()->json(['error' => 'Permission denied.'], 403);
        }

        if ($travel->created_by != Auth::user()->creatorId()) {
            return response()->json(['error' => 'Permission denied.'], 403);
        }

        return new TravelResource($travel->load('employee'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Travel  $travel
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Travel $travel)
    {
        if (Gate::denies('edit travel')) {
            return response()->json(['error' => 'Permission denied.'], 403);
        }

        if ($travel->created_by != Auth::user()->creatorId()) {
            return response()->json(['error' => 'Permission denied.'], 403);
        }

        $validator = \Validator::make(
            $request->all(), [
                'employee_id' => 'sometimes|required|exists:employees,id',
                'start_date' => 'sometimes|required|date',
                'end_date' => 'sometimes|required|date',
                'purpose_of_visit' => 'sometimes|required',
                'place_of_visit' => 'sometimes|required',
            ]
        );

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $travel->update($request->all());

        return (new TravelResource($travel->load('employee')))->additional(['message' => 'Travel successfully updated.']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Travel  $travel
     * @return \Illuminate\Http\Response
     */
    public function destroy(Travel $travel)
    {
        if (Gate::denies('delete travel')) {
            return response()->json(['error' => 'Permission denied.'], 403);
        }

        if ($travel->created_by != Auth::user()->creatorId()) {
            return response()->json(['error' => 'Permission denied.'], 403);
        }

        $travel->delete();

        return response()->json(['message' => 'Travel successfully deleted.']);
    }
}
