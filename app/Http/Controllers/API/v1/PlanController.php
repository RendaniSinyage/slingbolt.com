<?php

namespace App\Http\Controllers\API\v1;

use App\Http\Controllers\Controller;
use App\Http\Resources\PlanResource;
use App\Models\Plan;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class PlanController extends Controller
{
    public function index()
    {
        if (Auth::user()->can('manage plan')) {
            if (Auth::user()->type == 'super admin') {
                $plans = Plan::get();
            } else {
                $plans = Plan::where('is_disable', 1)->get();
            }
            return PlanResource::collection($plans);
        } else {
            return response()->json(['error' => __('Permission denied.')], 403);
        }
    }

    public function store(Request $request)
    {
        if (Auth::user()->can('create plan')) {
            $validator = Validator::make($request->all(), [
                'name' => 'required|unique:plans',
                'price' => 'required|numeric|min:0',
                'duration' => 'required',
                'max_users' => 'required|numeric',
                'max_customers' => 'required|numeric',
                'max_venders' => 'required|numeric',
                'storage_limit' => 'required|numeric',
                'image' => 'image|mimes:jpeg,png,jpg,gif,svg|max:20480',
            ]);

            if ($validator->fails()) {
                return response()->json(['error' => $validator->errors()->first()], 422);
            }

            $post = $request->all();
            if ($request->hasFile('image')) {
                $filenameWithExt = $request->file('image')->getClientOriginalName();
                $filename = pathinfo($filenameWithExt, PATHINFO_FILENAME);
                $extension = $request->file('image')->getClientOriginalExtension();
                $fileNameToStore = 'plan_' . time() . '.' . $extension;
                $request->file('image')->storeAs('uploads/plan/', $fileNameToStore);
                $post['image'] = $fileNameToStore;
            }

            $plan = Plan::create($post);
            return new PlanResource($plan);
        } else {
            return response()->json(['error' => __('Permission denied.')], 403);
        }
    }

    public function show(Plan $plan)
    {
        if (Auth::user()->can('manage plan')) {
            return new PlanResource($plan);
        } else {
            return response()->json(['error' => __('Permission denied.')], 403);
        }
    }

    public function update(Request $request, Plan $plan)
    {
        if (Auth::user()->can('edit plan')) {
            $validator = Validator::make($request->all(), [
                'name' => 'required|unique:plans,name,' . $plan->id,
                'max_users' => 'required|numeric',
                'max_customers' => 'required|numeric',
                'max_venders' => 'required|numeric',
                'storage_limit' => 'required|numeric',
            ]);

            if ($validator->fails()) {
                return response()->json(['error' => $validator->errors()->first()], 422);
            }

            $post = $request->all();
            if ($request->hasFile('image')) {
                // Handle image update
            }

            $plan->update($post);
            return new PlanResource($plan);
        } else {
            return response()->json(['error' => __('Permission denied.')], 403);
        }
    }

    public function destroy(Plan $plan)
    {
        if (Auth::user()->can('delete plan')) {
            if (User::where('plan', $plan->id)->exists()) {
                return response()->json(['error' => __('The company has subscribed to this plan, so it cannot be deleted.')], 400);
            }
            $plan->delete();
            return response()->json(null, 204);
        } else {
            return response()->json(['error' => __('Permission denied.')], 403);
        }
    }

    public function assign(Request $request, Plan $plan)
    {
        $user = Auth::user();
        if ($plan->price <= 0) {
            $user->assignPlan($plan->id);
            return response()->json(['message' => __('Plan successfully activated.')]);
        }
        return response()->json(['error' => __('This is a paid plan.')], 400);
    }

    public function trial(Request $request, Plan $plan)
    {
        $user = Auth::user();
        if ($plan->price > 0 && $plan->trial == 1) {
            if ($user->has_used_trial) {
                return response()->json(['error' => __('You have already used your trial period.')], 400);
            }
            $user->trial_plan = $plan->id;
            $user->has_used_trial = true;
            $user->trial_expire_date = date('Y-m-d', strtotime(now() . ' + ' . $plan->trial_days . ' days'));
            $user->save();
            $user->assignPlan($plan->id);
            return response()->json(['message' => __('Trial plan successfully activated.')]);
        }
        return response()->json(['error' => __('This plan does not have a trial.')], 400);
    }

    public function disable(Request $request, Plan $plan)
    {
        if (Auth::user()->type == 'super admin') {
            if (User::where('plan', $plan->id)->exists()) {
                return response()->json(['error' => __('The company has subscribed to this plan, so it cannot be disabled.')], 400);
            }
            $plan->update(['is_disable' => $request->is_disable]);
            $message = $request->is_disable == 1 ? 'Plan successfully enabled.' : 'Plan successfully disabled.';
            return response()->json(['message' => __($message)]);
        }
        return response()->json(['error' => __('Permission denied.')], 403);
    }
}
