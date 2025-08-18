<?php

namespace App\Http\Controllers\API\v1;

use App\Http\Controllers\Controller;
use App\Http\Resources\PlanRequestResource;
use App\Models\Order;
use App\Models\Plan;
use App\Models\PlanRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PlanRequestController extends Controller
{
    public function index()
    {
        if (Auth::user()->type == 'super admin') {
            $plan_requests = PlanRequest::with(['user', 'plan'])->get();
            return PlanRequestResource::collection($plan_requests);
        }
        return response()->json(['error' => __('Permission Denied.')], 403);
    }

    public function store(Request $request)
    {
        $user = Auth::user();
        $plan = Plan::find($request->plan_id);

        if (!$plan) {
            return response()->json(['error' => 'Plan not found.'], 404);
        }
        if ($user->requested_plan != 0) {
            return response()->json(['error' => __('You already sent a request for another plan.')], 400);
        }

        $planRequest = PlanRequest::create([
            'user_id' => $user->id,
            'plan_id' => $plan->id,
            'duration' => $plan->duration,
        ]);

        $user->update(['requested_plan' => $plan->id]);

        return new PlanRequestResource($planRequest);
    }

    public function update(Request $request, $id)
    {
        if (Auth::user()->type == 'super admin') {
            $plan_request = PlanRequest::find($id);
            if (!$plan_request) {
                return response()->json(['error' => 'Plan request not found.'], 404);
            }

            $user = User::find($plan_request->user_id);
            if ($request->response == 1) { // Accept
                $user->requested_plan = 0;
                $user->plan = $plan_request->plan_id;
                $user->save();

                $plan = Plan::find($plan_request->plan_id);
                $assignPlan = $user->assignPlan($plan_request->plan_id);

                if ($assignPlan['is_success']) {
                    Order::create([
                        'order_id' => strtoupper(str_replace('.', '', uniqid('', true))),
                        'name' => null,
                        'email' => null,
                        'card_number' => null,
                        'card_exp_month' => null,
                        'card_exp_year' => null,
                        'plan_name' => $plan->name,
                        'plan_id' => $plan->id,
                        'price' => $plan->price,
                        'price_currency' => env('CURRENCY_CODE', 'USD'),
                        'txn_id' => '',
                        'payment_type' => __('Manually Upgrade By Super Admin'),
                        'payment_status' => 'success',
                        'receipt' => null,
                        'user_id' => $user->id,
                    ]);
                    $plan_request->delete();
                    return response()->json(['message' => __('Plan successfully upgraded.')]);
                } else {
                    return response()->json(['error' => __('Plan fail to upgrade.')], 500);
                }
            } else { // Reject
                $user->update(['requested_plan' => '0']);
                $plan_request->delete();
                return response()->json(['message' => __('Request Rejected Successfully.')]);
            }
        }
        return response()->json(['error' => __('Permission Denied.')], 403);
    }

    public function destroy(Request $request)
    {
        $user = Auth::user();
        $user->update(['requested_plan' => '0']);
        PlanRequest::where('user_id', $user->id)->delete();

        return response()->json(null, 204);
    }
}
