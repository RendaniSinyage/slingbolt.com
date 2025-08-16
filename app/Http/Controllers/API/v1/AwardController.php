<?php

namespace App\Http\Controllers\API\v1;

use App\Http\Controllers\Controller;
use App\Models\Award;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AwardController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        if (Auth::user()->can('manage award')) {
            $query = Award::where('created_by', Auth::user()->creatorId());

            if ($request->has('employee_id')) {
                $query->where('employee_id', $request->employee_id);
            }

            $awards = $query->with(['employee', 'awardType'])->get();
            return response()->json($awards);
        }

        return response()->json(['error' => __('Permission denied.')], 403);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        if (Auth::user()->can('create award')) {
            $validator = \Validator::make($request->all(), [
                'employee_id' => 'required|exists:employees,id',
                'award_type' => 'required|exists:award_types,id',
                'date' => 'required|date',
                'gift' => 'required|string',
                'description' => 'nullable|string',
            ]);

            if ($validator->fails()) {
                return response()->json(['error' => $validator->errors()->first()], 400);
            }

            $award = new Award();
            $award->employee_id = $request->employee_id;
            $award->award_type = $request->award_type;
            $award->date = $request->date;
            $award->gift = $request->gift;
            $award->description = $request->description;
            $award->created_by = Auth::user()->creatorId();
            $award->save();

            return response()->json($award, 201);
        }

        return response()->json(['error' => __('Permission denied.')], 403);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Award  $award
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Award $award)
    {
        if (Auth::user()->can('manage award') && $award->created_by == Auth::user()->creatorId()) {
            return response()->json($award->load(['employee', 'awardType']));
        }

        return response()->json(['error' => __('Permission denied.')], 403);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Award  $award
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, Award $award)
    {
        if (Auth::user()->can('edit award') && $award->created_by == Auth::user()->creatorId()) {
            $validator = \Validator::make($request->all(), [
                'employee_id' => 'required|exists:employees,id',
                'award_type' => 'required|exists:award_types,id',
                'date' => 'required|date',
                'gift' => 'required|string',
                'description' => 'nullable|string',
            ]);

            if ($validator->fails()) {
                return response()->json(['error' => $validator->errors()->first()], 400);
            }

            $award->employee_id = $request->employee_id;
            $award->award_type = $request->award_type;
            $award->date = $request->date;
            $award->gift = $request->gift;
            $award->description = $request->description;
            $award->save();

            return response()->json($award);
        }

        return response()->json(['error' => __('Permission denied.')], 403);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Award  $award
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Award $award)
    {
        if (Auth::user()->can('delete award') && $award->created_by == Auth::user()->creatorId()) {
            $award->delete();
            return response()->json(null, 204);
        }

        return response()->json(['error' => __('Permission denied.')], 403);
    }
}
