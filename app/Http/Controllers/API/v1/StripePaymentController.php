<?php

namespace App\Http\Controllers\API\v1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Plan;
use App\Models\Coupon;
use App\Models\UserCoupon;
use App\Models\Utility;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Stripe;

class StripePaymentController extends Controller
{
    public function stripePost(Request $request)
    {
        $objUser = Auth::user();
        $planID = Crypt::decrypt($request->plan_id);
        $plan = Plan::find($planID);

        $admin_payment_setting = Utility::getAdminPaymentSetting();

        if ($plan) {
            try {
                $price = $plan->price;
                if (!empty($request->coupon)) {
                    $coupons = Coupon::where('code', strtoupper($request->coupon))->where('is_active', '1')->first();
                    if (!empty($coupons)) {
                        $usedCoupun = $coupons->used_coupon();
                        $discount_value = ($plan->price / 100) * $coupons->discount;
                        $price = $plan->price - $discount_value;
                        if ($coupons->limit == $usedCoupun) {
                            return response()->json(['error' => __('This coupon code has expired.')], 400);
                        }
                    } else {
                        return response()->json(['error' => __('This coupon code is invalid or has expired.')], 400);
                    }
                }

                $orderID = strtoupper(str_replace('.', '', uniqid('', true)));

                if ($price > 0.0) {
                    Stripe\Stripe::setApiKey($admin_payment_setting['stripe_secret']);
                    $data = Stripe\Charge::create([
                        "amount" => 100 * $price,
                        "currency" => !empty($admin_payment_setting['currency']) ? $admin_payment_setting['currency'] : 'USD',
                        "source" => $request->stripeToken,
                        "description" => " Plan - " . $plan->name,
                        "metadata" => ["order_id" => $orderID],
                    ]);
                } else {
                    $data['amount_refunded'] = 0;
                    $data['failure_code'] = '';
                    $data['paid'] = 1;
                    $data['captured'] = 1;
                    $data['status'] = 'succeeded';
                }

                if ($data['amount_refunded'] == 0 && empty($data['failure_code']) && $data['paid'] == 1 && $data['captured'] == 1) {
                    \App\Models\Order::create([
                        'order_id' => $orderID,
                        'name' => $request->name,
                        'card_number' => isset($data['payment_method_details']['card']['last4']) ? $data['payment_method_details']['card']['last4'] : '',
                        'card_exp_month' => isset($data['payment_method_details']['card']['exp_month']) ? $data['payment_method_details']['card']['exp_month'] : '',
                        'card_exp_year' => isset($data['payment_method_details']['card']['exp_year']) ? $data['payment_method_details']['card']['exp_year'] : '',
                        'plan_name' => $plan->name,
                        'plan_id' => $plan->id,
                        'price' => $price,
                        'price_currency' => !empty($admin_payment_setting['currency']) ? $admin_payment_setting['currency'] : 'USD',
                        'txn_id' => isset($data['balance_transaction']) ? $data['balance_transaction'] : '',
                        'payment_type' => __('STRIPE'),
                        'payment_status' => isset($data['status']) ? $data['status'] : 'success',
                        'receipt' => isset($data['receipt_url']) ? $data['receipt_url'] : 'free coupon',
                        'user_id' => $objUser->id,
                    ]);

                    if (!empty($request->coupon)) {
                        $userCoupon = new UserCoupon();
                        $userCoupon->user = $objUser->id;
                        $userCoupon->coupon = $coupons->id;
                        $userCoupon->order = $orderID;
                        $userCoupon->save();
                        $usedCoupun = $coupons->used_coupon();
                        if ($coupons->limit <= $usedCoupun) {
                            $coupons->is_active = 0;
                            $coupons->save();
                        }
                    }

                    if ($data['status'] == 'succeeded') {
                        $assignPlan = $objUser->assignPlan($plan->id);
                        if ($assignPlan['is_success']) {
                            return response()->json(['message' => 'Plan successfully activated.']);
                        } else {
                            return response()->json(['error' => $assignPlan['error']], 500);
                        }
                    } else {
                        return response()->json(['error' => 'Your payment has failed.'], 400);
                    }
                } else {
                    return response()->json(['error' => 'Transaction has been failed.'], 400);
                }
            } catch (\Exception $e) {
                return response()->json(['error' => $e->getMessage()], 500);
            }
        } else {
            return response()->json(['error' => 'Plan is deleted.'], 404);
        }
    }
}
