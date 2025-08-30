<?php

namespace App\Http\Controllers\API\v1;

use App\Http\Controllers\Controller;
use App\Http\Resources\CommissionResource;
use Illuminate\Http\Request;
use App\Models\Commission;
use App\Models\Employee;
use Illuminate\Support\Facades\Auth;

class CommissionController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $commissions = Commission::where('created_by', $user->creatorId());

        if ($request->has('employee_id')) {
            $commissions->where('employee_id', $request->employee_id);
        }

        return CommissionResource::collection($commissions->get());
    }

    public function store(Request $request)
    {
        $validator = \Validator::make($request->all(), [
            'employee_id' => 'required|exists:employees,id',
            'title' => 'required',
            'amount' => 'required|numeric|min:0',
            'type' => 'required|in:fixed,percentage',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()], 400);
        }

        $commission = new Commission();
        $commission->employee_id = $request->employee_id;
        $commission->title = $request->title;
        $commission->type = $request->type;
        $commission->amount = $request->amount;
        $commission->created_by = Auth::user()->creatorId();
        $commission->save();

        event(new \App\Events\CreateCommission($request, $commission));

        return new CommissionResource($commission);
    }

    public function show($id)
    {
        $commission = Commission::find($id);
        if (!$commission || $commission->created_by != Auth::user()->creatorId()) {
            return response()->json(['error' => 'Commission not found.'], 404);
        }
        return new CommissionResource($commission);
    }

    public function update(Request $request, $id)
    {
        $commission = Commission::find($id);
        if (!$commission || $commission->created_by != Auth::user()->creatorId()) {
            return response()->json(['error' => 'Commission not found.'], 404);
        }

        $validator = \Validator::make($request->all(), [
            'title' => 'required',
            'amount' => 'required|numeric|min:0',
            'type' => 'required|in:fixed,percentage',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()], 400);
        }

        $commission->title = $request->title;
        $commission->type = $request->type;
        $commission->amount = $request->amount;
        $commission->save();

        event(new \App\Events\UpdateCommission($request, $commission));

        return new CommissionResource($commission);
    }

    public function destroy($id)
    {
        $commission = Commission::find($id);
        if (!$commission || $commission->created_by != Auth::user()->creatorId()) {
            return response()->json(['error' => 'Commission not found.'], 404);
        }

        event(new \App\Events\DeleteCommission($commission));
        $commission->delete();
        return response()->json(['message' => 'Commission successfully deleted.']);
    }
}
