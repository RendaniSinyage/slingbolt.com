<?php

namespace App\Http\Controllers\API\v1;

use App\Http\Controllers\Controller;
use App\Models\Promotion;
use App\Models\Employee;
use App\Models\Designation;
use Illuminate\Http\Request;
use App\Http\Resources\PromotionResource;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Auth;
use App\Models\Utility;

class PromotionController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if (Gate::denies('manage promotion')) {
            return response()->json(['error' => 'Permission denied.'], 403);
        }

        if(Auth::user()->type == 'employee')
        {
            $emp = Employee::where('user_id', '=', Auth::user()->id)->first();
            $promotions = Promotion::where('created_by', '=', Auth::user()->creatorId())->where('employee_id', '=', $emp->id)->with(['designation','employee'])->get();
        }
        else
        {
            $promotions = Promotion::where('created_by', '=', Auth::user()->creatorId())->with(['designation','employee'])->get();
        }

        return PromotionResource::collection($promotions);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        if (Gate::denies('create promotion')) {
            return response()->json(['error' => 'Permission denied.'], 403);
        }

        $validator = \Validator::make(
            $request->all(), [
                'employee_id' => 'required',
                'designation_id' => 'required',
                'promotion_title' => 'required',
                'promotion_date' => 'required',
            ]
        );

        if($validator->fails())
        {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $promotion = new Promotion();
        $promotion->employee_id = $request->employee_id;
        $promotion->designation_id = $request->designation_id;
        $promotion->promotion_title = $request->promotion_title;
        $promotion->promotion_date = $request->promotion_date;
        $promotion->description = $request->description;
        $promotion->created_by = Auth::user()->creatorId();
        $promotion->save();

        $setings = Utility::settings();
        if($setings['promotion_sent'] == 1)
        {
            $employee = Employee::find($promotion->employee_id);
            $designation = Designation::find($promotion->designation_id);
            $promotion->designation = $designation->name;
            $promotionArr = [
                'employee_name' => $employee->name,
                'promotion_designation' => $promotion->designation,
                'promotion_title' => $promotion->promotion_title,
                'promotion_date' => $promotion->promotion_date,
            ];

            Utility::sendEmailTemplate('promotion_sent', [$employee->email], $promotionArr);
        }


        return (new PromotionResource($promotion->load(['designation', 'employee'])))->additional(['message' => __('Promotion successfully created.')]);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Promotion  $promotion
     * @return \Illuminate\Http\Response
     */
    public function show(Promotion $promotion)
    {
        if (Gate::denies('manage promotion')) {
            return response()->json(['error' => 'Permission denied.'], 403);
        }

        if($promotion->created_by != Auth::user()->creatorId())
        {
            return response()->json(['error' => __('Permission denied.')], 403);
        }

        return new PromotionResource($promotion->load(['designation', 'employee']));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Promotion  $promotion
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Promotion $promotion)
    {
        if (Gate::denies('edit promotion')) {
            return response()->json(['error' => 'Permission denied.'], 403);
        }

        if($promotion->created_by != Auth::user()->creatorId())
        {
            return response()->json(['error' => __('Permission denied.')], 403);
        }

        $validator = \Validator::make(
            $request->all(), [
                'employee_id' => 'required',
                'designation_id' => 'required',
                'promotion_title' => 'required',
                'promotion_date' => 'required',
            ]
        );

        if($validator->fails())
        {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $promotion->employee_id = $request->employee_id;
        $promotion->designation_id = $request->designation_id;
        $promotion->promotion_title = $request->promotion_title;
        $promotion->promotion_date = $request->promotion_date;
        $promotion->description = $request->description;
        $promotion->save();

        return (new PromotionResource($promotion->load(['designation', 'employee'])))->additional(['message' => __('Promotion successfully updated.')]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Promotion  $promotion
     * @return \Illuminate\Http\Response
     */
    public function destroy(Promotion $promotion)
    {
        if (Gate::denies('delete promotion')) {
            return response()->json(['error' => 'Permission denied.'], 403);
        }

        if($promotion->created_by != Auth::user()->creatorId())
        {
            return response()->json(['error' => __('Permission denied.')], 403);
        }

        $promotion->delete();

        return response()->json(['message' => __('Promotion successfully deleted.')]);
    }
}
