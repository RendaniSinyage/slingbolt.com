<?php

namespace App\Http\Controllers\API\v1;

use App\Http\Controllers\Controller;
use App\Models\Appraisal;
use App\Models\Employee;
use Illuminate\Http\Request;
use App\Http\Resources\AppraisalResource;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Auth;

class AppraisalController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if (Gate::denies('manage appraisal')) {
            return response()->json(['error' => 'Permission denied.'], 403);
        }

        if (Auth::user()->type == 'employee') {
            $employee = Employee::where('user_id', '=', Auth::user()->id)->first();
            $appraisals = Appraisal::where('employee', $employee->id)->with(['employees', 'branches'])->get();
        } else {
            $appraisals = Appraisal::where('created_by', '=', Auth::user()->creatorId())->with(['employees', 'branches'])->get();
        }

        return AppraisalResource::collection($appraisals);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        if (Gate::denies('create appraisal')) {
            return response()->json(['error' => 'Permission denied.'], 403);
        }

        $validator = \Validator::make(
            $request->all(), [
                'branch' => 'required',
                'employee' => 'required',
                'rating' => 'required|json',
            ]
        );

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $appraisal = new Appraisal();
        $appraisal->branch = $request->branch;
        $appraisal->employee = $request->employee;
        $appraisal->appraisal_date = $request->appraisal_date;
        $appraisal->rating = $request->rating;
        $appraisal->remark = $request->remark;
        $appraisal->created_by = Auth::user()->creatorId();
        $appraisal->save();

        event(new \App\Events\CreateAppraisal($request, $appraisal));

        return (new AppraisalResource($appraisal->load(['employees', 'branches'])))->additional(['message' => 'Appraisal successfully created.']);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Appraisal  $appraisal
     * @return \Illuminate\Http\Response
     */
    public function show(Appraisal $appraisal)
    {
        if (Gate::denies('manage appraisal')) {
            return response()->json(['error' => 'Permission denied.'], 403);
        }

        if ($appraisal->created_by != Auth::user()->creatorId()) {
            return response()->json(['error' => 'Permission denied.'], 403);
        }

        return new AppraisalResource($appraisal->load(['employees', 'branches']));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Appraisal  $appraisal
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Appraisal $appraisal)
    {
        if (Gate::denies('edit appraisal')) {
            return response()->json(['error' => 'Permission denied.'], 403);
        }

        if ($appraisal->created_by != Auth::user()->creatorId()) {
            return response()->json(['error' => 'Permission denied.'], 403);
        }

        $validator = \Validator::make(
            $request->all(), [
                'branch' => 'sometimes|required',
                'employee' => 'sometimes|required',
                'rating' => 'sometimes|required|json',
            ]
        );

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $appraisal->update($request->all());

        event(new \App\Events\UpdateAppraisal($request, $appraisal));

        return (new AppraisalResource($appraisal->load(['employees', 'branches'])))->additional(['message' => 'Appraisal successfully updated.']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Appraisal  $appraisal
     * @return \Illuminate\Http\Response
     */
    public function destroy(Appraisal $appraisal)
    {
        if (Gate::denies('delete appraisal')) {
            return response()->json(['error' => 'Permission denied.'], 403);
        }

        if ($appraisal->created_by != Auth::user()->creatorId()) {
            return response()->json(['error' => 'Permission denied.'], 403);
        }

        event(new \App\Events\DeleteAppraisal($appraisal));
        $appraisal->delete();

        return response()->json(['message' => 'Appraisal successfully deleted.']);
    }
}
