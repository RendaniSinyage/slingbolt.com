<?php

namespace App\Http\Controllers\API\v1;

use App\Http\Controllers\Controller;
use App\Http\Resources\AllowanceResource;
use App\Models\Allowance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AllowanceController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        if (Auth::user()->can('manage allowance')) {
            $query = Allowance::where('created_by', Auth::user()->creatorId());

            if ($request->has('employee_id')) {
                $query->where('employee_id', $request->employee_id);
            }

            $allowances = $query->with(['employee', 'allowance_option'])->get();
            return AllowanceResource::collection($allowances);
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
        if (Auth::user()->can('create allowance')) {
            $validator = \Validator::make($request->all(), [
                'employee_id' => 'required|exists:employees,id',
                'allowance_option' => 'required|exists:allowance_options,id',
                'title' => 'required|string|max:255',
                'amount' => 'required|numeric|min:0',
                'type' => 'required|in:fixed,percentage'
            ]);

            if ($validator->fails()) {
                return response()->json(['error' => $validator->errors()->first()], 400);
            }

            $allowance = new Allowance();
            $allowance->employee_id = $request->employee_id;
            $allowance->allowance_option = $request->allowance_option;
            $allowance->title = $request->title;
            $allowance->amount = $request->amount;
            $allowance->type = $request->type;
            $allowance->created_by = Auth::user()->creatorId();
            $allowance->save();

            event(new \App\Events\CreateAllowance($request, $allowance));

            return new AllowanceResource($allowance);
        }

        return response()->json(['error' => __('Permission denied.')], 403);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Allowance  $allowance
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Allowance $allowance)
    {
        if (Auth::user()->can('manage allowance') && $allowance->created_by == Auth::user()->creatorId()) {
            return new AllowanceResource($allowance->load(['employee', 'allowance_option']));
        }

        return response()->json(['error' => __('Permission denied.')], 403);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Allowance  $allowance
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, Allowance $allowance)
    {
        if (Auth::user()->can('edit allowance') && $allowance->created_by == Auth::user()->creatorId()) {
            $validator = \Validator::make($request->all(), [
                'allowance_option' => 'required|exists:allowance_options,id',
                'title' => 'required|string|max:255',
                'amount' => 'required|numeric|min:0',
                'type' => 'required|in:fixed,percentage'
            ]);

            if ($validator->fails()) {
                return response()->json(['error' => $validator->errors()->first()], 400);
            }

            $allowance->allowance_option = $request->allowance_option;
            $allowance->title = $request->title;
            $allowance->amount = $request->amount;
            $allowance->type = $request->type;
            $allowance->save();

            event(new \App\Events\UpdateAllowance($request, $allowance));

            return new AllowanceResource($allowance);
        }

        return response()->json(['error' => __('Permission denied.')], 403);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Allowance  $allowance
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Allowance $allowance)
    {
        if (Auth::user()->can('delete allowance') && $allowance->created_by == Auth::user()->creatorId()) {
            event(new \App\Events\DeleteAllowance($allowance));
            $allowance->delete();
            return response()->json(null, 204);
        }

        return response()->json(['error' => __('Permission denied.')], 403);
    }
}
