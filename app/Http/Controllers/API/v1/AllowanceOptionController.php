<?php

namespace App\Http\Controllers\API\v1;

use App\Http\Controllers\Controller;
use App\Models\AllowanceOption;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AllowanceOptionController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        if (Auth::user()->can('manage allowance option')) {
            $allowanceoptions = AllowanceOption::where('created_by', Auth::user()->creatorId())->get();
            return response()->json($allowanceoptions);
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
        if (Auth::user()->can('create allowance option')) {
            $validator = \Validator::make($request->all(), ['name' => 'required|string|max:255']);

            if ($validator->fails()) {
                return response()->json(['error' => $validator->errors()->first()], 400);
            }

            $allowanceoption = new AllowanceOption();
            $allowanceoption->name = $request->name;
            $allowanceoption->created_by = Auth::user()->creatorId();
            $allowanceoption->save();

            return response()->json($allowanceoption, 201);
        }

        return response()->json(['error' => __('Permission denied.')], 403);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\AllowanceOption  $allowanceoption
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(AllowanceOption $allowanceoption)
    {
        if (Auth::user()->can('manage allowance option') && $allowanceoption->created_by == Auth::user()->creatorId()) {
            return response()->json($allowanceoption);
        }

        return response()->json(['error' => __('Permission denied.')], 403);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\AllowanceOption  $allowanceoption
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, AllowanceOption $allowanceoption)
    {
        if (Auth::user()->can('edit allowance option') && $allowanceoption->created_by == Auth::user()->creatorId()) {
            $validator = \Validator::make($request->all(), ['name' => 'required|string|max:255']);

            if ($validator->fails()) {
                return response()->json(['error' => $validator->errors()->first()], 400);
            }

            $allowanceoption->name = $request->name;
            $allowanceoption->save();

            return response()->json($allowanceoption);
        }

        return response()->json(['error' => __('Permission denied.')], 403);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\AllowanceOption  $allowanceoption
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(AllowanceOption $allowanceoption)
    {
        if (Auth::user()->can('delete allowance option') && $allowanceoption->created_by == Auth::user()->creatorId()) {
            $allowanceoption->delete();
            return response()->json(null, 204);
        }

        return response()->json(['error' => __('Permission denied.')], 403);
    }
}
