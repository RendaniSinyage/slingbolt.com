<?php

namespace App\Http\Controllers\API\v1;

use App\Http\Controllers\Controller;
use App\Models\LoanOption;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoanOptionController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        if (Auth::user()->can('manage loan option')) {
            $loanoptions = LoanOption::where('created_by', Auth::user()->creatorId())->get();
            return response()->json($loanoptions);
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
        if (Auth::user()->can('create loan option')) {
            $validator = \Validator::make($request->all(), ['name' => 'required|string|max:255']);

            if ($validator->fails()) {
                return response()->json(['error' => $validator->errors()->first()], 400);
            }

            $loanoption = new LoanOption();
            $loanoption->name = $request->name;
            $loanoption->created_by = Auth::user()->creatorId();
            $loanoption->save();

            return response()->json($loanoption, 201);
        }

        return response()->json(['error' => __('Permission denied.')], 403);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\LoanOption  $loanoption
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(LoanOption $loanoption)
    {
        if (Auth::user()->can('manage loan option') && $loanoption->created_by == Auth::user()->creatorId()) {
            return response()->json($loanoption);
        }

        return response()->json(['error' => __('Permission denied.')], 403);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\LoanOption  $loanoption
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, LoanOption $loanoption)
    {
        if (Auth::user()->can('edit loan option') && $loanoption->created_by == Auth::user()->creatorId()) {
            $validator = \Validator::make($request->all(), ['name' => 'required|string|max:255']);

            if ($validator->fails()) {
                return response()->json(['error' => $validator->errors()->first()], 400);
            }

            $loanoption->name = $request->name;
            $loanoption->save();

            return response()->json($loanoption);
        }

        return response()->json(['error' => __('Permission denied.')], 403);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\LoanOption  $loanoption
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(LoanOption $loanoption)
    {
        if (Auth::user()->can('delete loan option') && $loanoption->created_by == Auth::user()->creatorId()) {
            $loanoption->delete();
            return response()->json(null, 204);
        }

        return response()->json(['error' => __('Permission denied.')], 403);
    }
}
