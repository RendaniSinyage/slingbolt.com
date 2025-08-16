<?php

namespace App\Http\Controllers\API\v1;

use App\Http\Controllers\Controller;
use App\Models\DeductionOption;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DeductionOptionController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        if (Auth::user()->can('manage deduction option')) {
            $deductionoptions = DeductionOption::where('created_by', Auth::user()->creatorId())->get();
            return response()->json($deductionoptions);
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
        if (Auth::user()->can('create deduction option')) {
            $validator = \Validator::make($request->all(), ['name' => 'required|string|max:255']);

            if ($validator->fails()) {
                return response()->json(['error' => $validator->errors()->first()], 400);
            }

            $deductionoption = new DeductionOption();
            $deductionoption->name = $request->name;
            $deductionoption->created_by = Auth::user()->creatorId();
            $deductionoption->save();

            return response()->json($deductionoption, 201);
        }

        return response()->json(['error' => __('Permission denied.')], 403);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\DeductionOption  $deductionoption
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(DeductionOption $deductionoption)
    {
        if (Auth::user()->can('manage deduction option') && $deductionoption->created_by == Auth::user()->creatorId()) {
            return response()->json($deductionoption);
        }

        return response()->json(['error' => __('Permission denied.')], 403);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\DeductionOption  $deductionoption
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, DeductionOption $deductionoption)
    {
        if (Auth::user()->can('edit deduction option') && $deductionoption->created_by == Auth::user()->creatorId()) {
            $validator = \Validator::make($request->all(), ['name' => 'required|string|max:255']);

            if ($validator->fails()) {
                return response()->json(['error' => $validator->errors()->first()], 400);
            }

            $deductionoption->name = $request->name;
            $deductionoption->save();

            return response()->json($deductionoption);
        }

        return response()->json(['error' => __('Permission denied.')], 403);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\DeductionOption  $deductionoption
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(DeductionOption $deductionoption)
    {
        if (Auth::user()->can('delete deduction option') && $deductionoption->created_by == Auth::user()->creatorId()) {
            $deductionoption->delete();
            return response()->json(null, 204);
        }

        return response()->json(['error' => __('Permission denied.')], 403);
    }
}
