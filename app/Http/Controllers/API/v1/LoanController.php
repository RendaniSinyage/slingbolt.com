<?php

namespace App\Http\Controllers\API\v1;

use App\Http\Controllers\Controller;
use App\Models\Loan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoanController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        if (Auth::user()->can('manage loan')) {
            $query = Loan::where('created_by', Auth::user()->creatorId());

            if ($request->has('employee_id')) {
                $query->where('employee_id', $request->employee_id);
            }

            $loans = $query->with(['employee', 'loan_option'])->get();
            return response()->json($loans);
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
        if (Auth::user()->can('create loan')) {
            $validator = \Validator::make($request->all(), [
                'employee_id' => 'required|exists:employees,id',
                'loan_option' => 'required|exists:loan_options,id',
                'title' => 'required|string|max:255',
                'amount' => 'required|numeric|min:0',
                'reason' => 'required|string',
                'type' => 'required|in:fixed,percentage'
            ]);

            if ($validator->fails()) {
                return response()->json(['error' => $validator->errors()->first()], 400);
            }

            $loan = new Loan();
            $loan->employee_id = $request->employee_id;
            $loan->loan_option = $request->loan_option;
            $loan->title = $request->title;
            $loan->amount = $request->amount;
            $loan->reason = $request->reason;
            $loan->type = $request->type;
            $loan->created_by = Auth::user()->creatorId();
            $loan->save();

            return response()->json($loan, 201);
        }

        return response()->json(['error' => __('Permission denied.')], 403);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Loan  $loan
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Loan $loan)
    {
        if (Auth::user()->can('manage loan') && $loan->created_by == Auth::user()->creatorId()) {
            return response()->json($loan->load(['employee', 'loan_option']));
        }

        return response()->json(['error' => __('Permission denied.')], 403);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Loan  $loan
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, Loan $loan)
    {
        if (Auth::user()->can('edit loan') && $loan->created_by == Auth::user()->creatorId()) {
            $validator = \Validator::make($request->all(), [
                'loan_option' => 'required|exists:loan_options,id',
                'title' => 'required|string|max:255',
                'amount' => 'required|numeric|min:0',
                'reason' => 'required|string',
                'type' => 'required|in:fixed,percentage'
            ]);

            if ($validator->fails()) {
                return response()->json(['error' => $validator->errors()->first()], 400);
            }

            $loan->loan_option = $request->loan_option;
            $loan->title = $request->title;
            $loan->amount = $request->amount;
            $loan->reason = $request->reason;
            $loan->type = $request->type;
            $loan->save();

            return response()->json($loan);
        }

        return response()->json(['error' => __('Permission denied.')], 403);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Loan  $loan
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Loan $loan)
    {
        if (Auth::user()->can('delete loan') && $loan->created_by == Auth::user()->creatorId()) {
            $loan->delete();
            return response()->json(null, 204);
        }

        return response()->json(['error' => __('Permission denied.')], 403);
    }
}
