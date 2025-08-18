<?php

namespace App\Http\Controllers\API\v1;

use App\Http\Controllers\Controller;
use App\Http\Resources\AwardTypeResource;
use App\Models\AwardType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AwardTypeController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        if (Auth::user()->can('manage award type')) {
            $awardtypes = AwardType::where('created_by', Auth::user()->creatorId())->get();
            return AwardTypeResource::collection($awardtypes);
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
        if (Auth::user()->can('create award type')) {
            $validator = \Validator::make($request->all(), ['name' => 'required|string|max:255']);

            if ($validator->fails()) {
                return response()->json(['error' => $validator->errors()->first()], 400);
            }

            $awardtype = new AwardType();
            $awardtype->name = $request->name;
            $awardtype->created_by = Auth::user()->creatorId();
            $awardtype->save();

            return new AwardTypeResource($awardtype);
        }

        return response()->json(['error' => __('Permission denied.')], 403);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\AwardType  $awardtype
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(AwardType $awardtype)
    {
        if (Auth::user()->can('manage award type') && $awardtype->created_by == Auth::user()->creatorId()) {
            return new AwardTypeResource($awardtype);
        }

        return response()->json(['error' => __('Permission denied.')], 403);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\AwardType  $awardtype
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, AwardType $awardtype)
    {
        if (Auth::user()->can('edit award type') && $awardtype->created_by == Auth::user()->creatorId()) {
            $validator = \Validator::make($request->all(), ['name' => 'required|string|max:255']);

            if ($validator->fails()) {
                return response()->json(['error' => $validator->errors()->first()], 400);
            }

            $awardtype->name = $request->name;
            $awardtype->save();

            return new AwardTypeResource($awardtype);
        }

        return response()->json(['error' => __('Permission denied.')], 403);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\AwardType  $awardtype
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(AwardType $awardtype)
    {
        if (Auth::user()->can('delete award type') && $awardtype->created_by == Auth::user()->creatorId()) {
            $awardtype->delete();
            return response()->json(null, 204);
        }

        return response()->json(['error' => __('Permission denied.')], 403);
    }
}
