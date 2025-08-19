<?php

namespace App\Http\Controllers\API\v1;

use App\Http\Controllers\Controller;
use App\Http\Resources\SaturationDeductionResource;
use App\Models\SaturationDeduction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SaturationDeductionController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        if (Auth::user()->can('manage saturation deduction')) {
            $query = SaturationDeduction::where('created_by', Auth::user()->creatorId());

            if ($request->has('employee_id')) {
                $query->where('employee_id', $request->employee_id);
            }

            $deductions = $query->with(['employee', 'deduction_option'])->get();
            return SaturationDeductionResource::collection($deductions);
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
        if (Auth::user()->can('create saturation deduction')) {
            $validator = \Validator::make($request->all(), [
                'employee_id' => 'required|exists:employees,id',
                'deduction_option' => 'required|exists:deduction_options,id',
                'title' => 'required|string|max:255',
                'amount' => 'required|numeric|min:0',
                'type' => 'required|in:fixed,percentage'
            ]);

            if ($validator->fails()) {
                return response()->json(['error' => $validator->errors()->first()], 400);
            }

            $deduction = new SaturationDeduction();
            $deduction->employee_id = $request->employee_id;
            $deduction->deduction_option = $request->deduction_option;
            $deduction->title = $request->title;
            $deduction->amount = $request->amount;
            $deduction->type = $request->type;
            $deduction->created_by = Auth::user()->creatorId();
            $deduction->save();

            return new SaturationDeductionResource($deduction);
        }

        return response()->json(['error' => __('Permission denied.')], 403);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\SaturationDeduction  $saturationdeduction
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(SaturationDeduction $saturationdeduction)
    {
        if (Auth::user()->can('manage saturation deduction') && $saturationdeduction->created_by == Auth::user()->creatorId()) {
            return new SaturationDeductionResource($saturationdeduction->load(['employee', 'deduction_option']));
        }

        return response()->json(['error' => __('Permission denied.')], 403);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\SaturationDeduction  $saturationdeduction
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, SaturationDeduction $saturationdeduction)
    {
        if (Auth::user()->can('edit saturation deduction') && $saturationdeduction->created_by == Auth::user()->creatorId()) {
            $validator = \Validator::make($request->all(), [
                'deduction_option' => 'required|exists:deduction_options,id',
                'title' => 'required|string|max:255',
                'amount' => 'required|numeric|min:0',
                'type' => 'required|in:fixed,percentage'
            ]);

            if ($validator->fails()) {
                return response()->json(['error' => $validator->errors()->first()], 400);
            }

            $saturationdeduction->deduction_option = $request->deduction_option;
            $saturationdeduction->title = $request->title;
            $saturationdeduction->amount = $request->amount;
            $saturationdeduction->type = $request->type;
            $saturationdeduction->save();

            return new SaturationDeductionResource($saturationdeduction);
        }

        return response()->json(['error' => __('Permission denied.')], 403);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\SaturationDeduction  $saturationdeduction
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(SaturationDeduction $saturationdeduction)
    {
        if (Auth::user()->can('delete saturation deduction') && $saturationdeduction->created_by == Auth::user()->creatorId()) {
            $saturationdeduction->delete();
            return response()->json(null, 204);
        }

        return response()->json(['error' => __('Permission denied.')], 403);
    }
}
