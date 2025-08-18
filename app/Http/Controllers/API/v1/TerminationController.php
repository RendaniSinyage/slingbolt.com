<?php

namespace App\Http\Controllers\API\v1;

use App\Http\Controllers\Controller;
use App\Models\Termination;
use App\Models\Employee;
use App\Models\TerminationType;
use Illuminate\Http\Request;
use App\Http\Resources\TerminationResource;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Auth;
use App\Mail\TerminationSend;
use Illuminate\Support\Facades\Mail;

class TerminationController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if (Gate::denies('manage termination')) {
            return response()->json(['error' => 'Permission denied.'], 403);
        }

        if(Auth::user()->type == 'employee')
        {
            $employee = Employee::where('user_id', '=', Auth::user()->id)->first();
            $terminations = Termination::where('created_by', '=', Auth::user()->creatorId())->where('employee_id', '=', $employee->id)->get();
        }
        else
        {
            $terminations = Termination::where('created_by', '=', Auth::user()->creatorId())->get();
        }

        return TerminationResource::collection($terminations);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        if (Gate::denies('create termination')) {
            return response()->json(['error' => 'Permission denied.'], 403);
        }

        $validator = \Validator::make(
            $request->all(), [
                'employee_id' => 'required',
                'termination_type' => 'required',
                'notice_date' => 'required',
                'termination_date' => 'required',
                'description' => 'required',
            ]
        );

        if($validator->fails())
        {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $termination = new Termination();
        $termination->employee_id = $request->employee_id;
        $termination->termination_type = $request->termination_type;
        $termination->notice_date = $request->notice_date;
        $termination->termination_date = $request->termination_date;
        $termination->description = $request->description;
        $termination->created_by = Auth::user()->creatorId();
        $termination->save();

        $terminationtype = TerminationType::find($request->termination_type);
        $employee = Employee::find($request->employee_id);

        try {
            Mail::to($employee->email)->send(new TerminationSend($employee, $terminationtype, $request->notice_date, $request->termination_date));
        } catch (\Exception $e) {
            // Do nothing
        }

        return (new TerminationResource($termination))->additional(['message' => __('Termination successfully created.')]);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Termination  $termination
     * @return \Illuminate\Http\Response
     */
    public function show(Termination $termination)
    {
        if (Gate::denies('manage termination')) {
            return response()->json(['error' => 'Permission denied.'], 403);
        }

        if($termination->created_by != Auth::user()->creatorId())
        {
            return response()->json(['error' => __('Permission denied.')], 403);
        }

        return new TerminationResource($termination);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Termination  $termination
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Termination $termination)
    {
        if (Gate::denies('edit termination')) {
            return response()->json(['error' => 'Permission denied.'], 403);
        }

        if($termination->created_by != Auth::user()->creatorId())
        {
            return response()->json(['error' => __('Permission denied.')], 403);
        }

        $validator = \Validator::make(
            $request->all(), [
                'termination_type' => 'required',
                'notice_date' => 'required',
                'termination_date' => 'required',
                'description' => 'required',
            ]
        );

        if($validator->fails())
        {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $termination->termination_type = $request->termination_type;
        $termination->notice_date = $request->notice_date;
        $termination->termination_date = $request->termination_date;
        $termination->description = $request->description;
        $termination->save();

        return (new TerminationResource($termination))->additional(['message' => __('Termination successfully updated.')]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Termination  $termination
     * @return \Illuminate\Http\Response
     */
    public function destroy(Termination $termination)
    {
        if (Gate::denies('delete termination')) {
            return response()->json(['error' => 'Permission denied.'], 403);
        }

        if($termination->created_by != Auth::user()->creatorId())
        {
            return response()->json(['error' => __('Permission denied.')], 403);
        }

        $termination->delete();

        return response()->json(['message' => __('Termination successfully deleted.')]);
    }
}
