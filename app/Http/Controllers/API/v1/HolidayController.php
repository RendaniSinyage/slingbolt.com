<?php

namespace App\Http\Controllers\API\v1;

use App\Http\Controllers\Controller;
use App\Http\Resources\HolidayResource;
use App\Models\Holiday;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HolidayController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        if (Auth::user()->can('manage holiday')) {
            $query = Holiday::where('created_by', Auth::user()->creatorId());

            if ($request->has('start_date')) {
                $query->where('date', '>=', $request->start_date);
            }
            if ($request->has('end_date')) {
                $query->where('end_date', '<=', $request->end_date);
            }

            $holidays = $query->get();
            return HolidayResource::collection($holidays);
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
        if (Auth::user()->can('create holiday')) {
            $validator = \Validator::make($request->all(), [
                'date' => 'required|date',
                'end_date' => 'required|date|after_or_equal:date',
                'occasion' => 'required|string|max:255',
            ]);

            if ($validator->fails()) {
                return response()->json(['error' => $validator->errors()->first()], 400);
            }

            $holiday = new Holiday();
            $holiday->date = $request->date;
            $holiday->end_date = $request->end_date;
            $holiday->occasion = $request->occasion;
            $holiday->created_by = Auth::user()->creatorId();
            $holiday->save();

            return new HolidayResource($holiday);
        }

        return response()->json(['error' => __('Permission denied.')], 403);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Holiday  $holiday
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Holiday $holiday)
    {
        if (Auth::user()->can('manage holiday') && $holiday->created_by == Auth::user()->creatorId()) {
            return new HolidayResource($holiday);
        }

        return response()->json(['error' => __('Permission denied.')], 403);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Holiday  $holiday
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, Holiday $holiday)
    {
        if (Auth::user()->can('edit holiday') && $holiday->created_by == Auth::user()->creatorId()) {
            $validator = \Validator::make($request->all(), [
                'date' => 'required|date',
                'end_date' => 'required|date|after_or_equal:date',
                'occasion' => 'required|string|max:255',
            ]);

            if ($validator->fails()) {
                return response()->json(['error' => $validator->errors()->first()], 400);
            }

            $holiday->date = $request->date;
            $holiday->end_date = $request->end_date;
            $holiday->occasion = $request->occasion;
            $holiday->save();

            return new HolidayResource($holiday);
        }

        return response()->json(['error' => __('Permission denied.')], 403);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Holiday  $holiday
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Holiday $holiday)
    {
        if (Auth::user()->can('delete holiday') && $holiday->created_by == Auth::user()->creatorId()) {
            $holiday->delete();
            return response()->json(null, 204);
        }

        return response()->json(['error' => __('Permission denied.')], 403);
    }
}
