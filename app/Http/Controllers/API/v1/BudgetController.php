<?php

namespace App\Http\Controllers\API\v1;

use App\Http\Controllers\Controller;
use App\Http\Resources\BudgetResource;
use App\Models\Budget;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BudgetController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        if (Auth::user()->can('manage budget plan')) {
            $budgets = Budget::where('created_by', Auth::user()->creatorId())->get();
            return BudgetResource::collection($budgets);
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
        if (Auth::user()->can('create budget plan')) {
            $validator = \Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'period' => 'required|in:monthly,quarterly,half-yearly,yearly',
                'year' => 'required|date_format:Y',
                'income_data' => 'nullable|array',
                'expense_data' => 'nullable|array',
            ]);

            if ($validator->fails()) {
                return response()->json(['error' => $validator->errors()->first()], 400);
            }

            $budget = new Budget();
            $budget->name = $request->name;
            $budget->from = $request->year;
            $budget->period = $request->period;
            $budget->income_data = json_encode($request->income_data ?? []);
            $budget->expense_data = json_encode($request->expense_data ?? []);
            $budget->created_by = Auth::user()->creatorId();
            $budget->save();

            event(new \App\Events\CreateBudget($request, $budget));

            return new BudgetResource($budget);
        }

        return response()->json(['error' => __('Permission denied.')], 403);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Budget  $budget
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Budget $budget)
    {
        if (Auth::user()->can('view budget plan') && $budget->created_by == Auth::user()->creatorId()) {
            return new BudgetResource($budget);
        }

        return response()->json(['error' => __('Permission denied.')], 403);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Budget  $budget
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, Budget $budget)
    {
        if (Auth::user()->can('edit budget plan') && $budget->created_by == Auth::user()->creatorId()) {
            $validator = \Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'period' => 'required|in:monthly,quarterly,half-yearly,yearly',
                'year' => 'required|date_format:Y',
                'income_data' => 'nullable|array',
                'expense_data' => 'nullable|array',
            ]);

            if ($validator->fails()) {
                return response()->json(['error' => $validator->errors()->first()], 400);
            }

            $budget->name = $request->name;
            $budget->from = $request->year;
            $budget->period = $request->period;
            $budget->income_data = json_encode($request->income_data ?? []);
            $budget->expense_data = json_encode($request->expense_data ?? []);
            $budget->save();

            event(new \App\Events\UpdateBudget($request, $budget));

            return new BudgetResource($budget);
        }

        return response()->json(['error' => __('Permission denied.')], 403);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Budget  $budget
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Budget $budget)
    {
        if (Auth::user()->can('delete budget plan') && $budget->created_by == Auth::user()->creatorId()) {
            event(new \App\Events\DeleteBudget($budget));
            $budget->delete();
            return response()->json(null, 204);
        }

        return response()->json(['error' => __('Permission denied.')], 403);
    }
}
