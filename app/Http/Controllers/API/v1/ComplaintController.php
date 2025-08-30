<?php

namespace App\Http\Controllers\API\v1;

use App\Http\Controllers\Controller;
use App\Models\Complaint;
use App\Models\Employee;
use Illuminate\Http\Request;
use App\Http\Resources\ComplaintResource;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Auth;

class ComplaintController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if (Gate::denies('manage complaint')) {
            return response()->json(['error' => 'Permission denied.'], 403);
        }

        if (Auth::user()->type == 'employee') {
            $employee = Employee::where('user_id', '=', Auth::user()->id)->first();
            $complaints = Complaint::where('complaint_from', '=', $employee->id)->with(['complaintFrom', 'complaintAgainst'])->get();
        } else {
            $complaints = Complaint::where('created_by', '=', Auth::user()->creatorId())->with(['complaintFrom', 'complaintAgainst'])->get();
        }

        return ComplaintResource::collection($complaints);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        if (Gate::denies('create complaint')) {
            return response()->json(['error' => 'Permission denied.'], 403);
        }

        $validator = \Validator::make(
            $request->all(), [
                'complaint_against' => 'required|exists:employees,id',
                'title' => 'required',
                'complaint_date' => 'required|date',
            ]
        );

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $complaint_from_id = null;
        if (Auth::user()->type == 'employee') {
            $employee = Employee::where('user_id', '=', Auth::user()->id)->first();
            $complaint_from_id = $employee->id;
        } else {
            $validator = \Validator::make(
                $request->all(), [
                    'complaint_from' => 'required|exists:employees,id',
                ]
            );
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            $complaint_from_id = $request->complaint_from;
        }

        if ($complaint_from_id == $request->complaint_against) {
            return response()->json(['error' => 'Complaint from and against cannot be the same employee.'], 422);
        }

        $complaint = new Complaint();
        $complaint->complaint_from = $complaint_from_id;
        $complaint->complaint_against = $request->complaint_against;
        $complaint->title = $request->title;
        $complaint->complaint_date = $request->complaint_date;
        $complaint->description = $request->description;
        $complaint->created_by = Auth::user()->creatorId();
        $complaint->save();

        event(new \App\Events\CreateComplaint($request, $complaint));

        return (new ComplaintResource($complaint->load(['complaintFrom', 'complaintAgainst'])))->additional(['message' => 'Complaint successfully created.']);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Complaint  $complaint
     * @return \Illuminate\Http\Response
     */
    public function show(Complaint $complaint)
    {
        if (Gate::denies('manage complaint')) {
            return response()->json(['error' => 'Permission denied.'], 403);
        }

        if ($complaint->created_by != Auth::user()->creatorId()) {
            return response()->json(['error' => 'Permission denied.'], 403);
        }

        return new ComplaintResource($complaint->load(['complaintFrom', 'complaintAgainst']));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Complaint  $complaint
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Complaint $complaint)
    {
        if (Gate::denies('edit complaint')) {
            return response()->json(['error' => 'Permission denied.'], 403);
        }

        if ($complaint->created_by != Auth::user()->creatorId()) {
            return response()->json(['error' => 'Permission denied.'], 403);
        }

        $validator = \Validator::make(
            $request->all(), [
                'complaint_against' => 'sometimes|required|exists:employees,id',
                'title' => 'sometimes|required',
                'complaint_date' => 'sometimes|required|date',
            ]
        );

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $complaint->update($request->all());

        event(new \App\Events\UpdateComplaint($request, $complaint));

        return (new ComplaintResource($complaint->load(['complaintFrom', 'complaintAgainst'])))->additional(['message' => 'Complaint successfully updated.']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Complaint  $complaint
     * @return \Illuminate\Http\Response
     */
    public function destroy(Complaint $complaint)
    {
        if (Gate::denies('delete complaint')) {
            return response()->json(['error' => 'Permission denied.'], 403);
        }

        if ($complaint->created_by != Auth::user()->creatorId()) {
            return response()->json(['error' => 'Permission denied.'], 403);
        }

        event(new \App\Events\DeleteComplaint($complaint));
        $complaint->delete();

        return response()->json(['message' => 'Complaint successfully deleted.']);
    }
}
