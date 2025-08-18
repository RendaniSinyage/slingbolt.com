<?php

namespace App\Http\Controllers\API\v1;

use App\Http\Controllers\Controller;
use App\Http\Resources\CouponResource;
use App\Models\Coupon;
use App\Models\Plan;
use App\Models\UserCoupon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class CouponController extends Controller
{
    public function index()
    {
        if (Auth::user()->can('manage coupon')) {
            $coupons = Coupon::get();
            return CouponResource::collection($coupons);
        } else {
            return response()->json(['error' => __('Permission denied.')], 403);
        }
    }

    public function store(Request $request)
    {
        if (Auth::user()->can('create coupon')) {
            $validator = Validator::make(
                $request->all(),
                [
                    'name' => 'required|unique:coupons',
                    'discount' => 'required|numeric',
                    'limit' => 'required|numeric',
                    'code' => 'required|unique:coupons',
                ]
            );
            if ($validator->fails()) {
                return response()->json(['error' => $validator->errors()->first()], 422);
            }

            $coupon = new Coupon();
            $coupon->name = $request->name;
            $coupon->discount = $request->discount;
            $coupon->limit = $request->limit;
            $coupon->code = strtoupper($request->code);
            $coupon->save();

            return new CouponResource($coupon);
        } else {
            return response()->json(['error' => __('Permission denied.')], 403);
        }
    }

    public function show(Coupon $coupon)
    {
        if (Auth::user()->can('manage coupon')) {
            return new CouponResource($coupon);
        } else {
            return response()->json(['error' => __('Permission denied.')], 403);
        }
    }

    public function update(Request $request, Coupon $coupon)
    {
        if (Auth::user()->can('edit coupon')) {
            $validator = Validator::make(
                $request->all(),
                [
                    'name' => 'required',
                    'discount' => 'required|numeric',
                    'limit' => 'required|numeric',
                    'code' => 'required|unique:coupons,code,' . $coupon->id,
                ]
            );
            if ($validator->fails()) {
                return response()->json(['error' => $validator->errors()->first()], 422);
            }

            $coupon->name = $request->name;
            $coupon->discount = $request->discount;
            $coupon->limit = $request->limit;
            $coupon->code = $request->code;
            $coupon->save();

            return new CouponResource($coupon);
        } else {
            return response()->json(['error' => __('Permission denied.')], 403);
        }
    }

    public function destroy(Coupon $coupon)
    {
        if (Auth::user()->can('delete coupon')) {
            $coupon->delete();
            return response()->json(null, 204);
        } else {
            return response()->json(['error' => __('Permission denied.')], 403);
        }
    }

    public function apply(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'plan_id' => 'required',
            'coupon' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()], 422);
        }

        try {
            $plan = Plan::find($request->plan_id);
        } catch (\Exception $e) {
            return response()->json(['error' => __('Something went wrong.')], 500);
        }

        if ($plan && !empty($request->coupon)) {
            $coupon = Coupon::where('code', strtoupper($request->coupon))->where('is_active', '1')->first();
            if (!empty($coupon)) {
                $usedCoupun = $coupon->used_coupon();
                if ($coupon->limit == $usedCoupun) {
                    return response()->json(['error' => __('This coupon code has expired.')], 400);
                } else {
                    $discount_value = ($plan->price / 100) * $coupon->discount;
                    $plan_price = $plan->price - $discount_value;
                    return response()->json([
                        'is_success' => true,
                        'original_price' => $plan->price,
                        'discount_price' => $discount_value,
                        'final_price' => $plan_price,
                        'message' => __('Coupon code has applied successfully.'),
                    ]);
                }
            } else {
                return response()->json(['error' => __('This coupon code is invalid or has expired.')], 400);
            }
        } else {
            return response()->json(['error' => __('Plan not found or coupon code is empty.')], 404);
        }
    }
}
