<?php

namespace App\Http\Controllers\API\v1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Plan;
use App\Models\Coupon;
use App\Models\Utility;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use App\Coingate\Coingate;

class CoingatePaymentController extends Controller
{
    public $mode;
    public $coingate_auth_token;
    public $is_enabled;
    protected $currency;

    public function paymentConfig()
    {
        $payment_setting = Utility::getAdminPaymentSetting();
        $this->coingate_auth_token = isset($payment_setting['coingate_auth_token']) ? $payment_setting['coingate_auth_token'] : '';
        $this->mode = isset($payment_setting['coingate_mode']) ? $payment_setting['coingate_mode'] : 'off';
        $this->is_enabled = isset($payment_setting['is_coingate_enabled']) ? $payment_setting['is_coingate_enabled'] : 'off';
        $this->currency = isset($payment_setting['currency']) ? $payment_setting['currency'] : 'USD';
        return $this;
    }

    public function planPayWithCoingate(Request $request)
    {
        $payment = $this->paymentConfig();
        $planID = Crypt::decrypt($request->plan_id);
        $plan = Plan::find($planID);
        $authuser = Auth::user();
        $coupons_id = '';

        if ($plan) {
            $price = $plan->price;
            if (isset($request->coupon) && !empty($request->coupon)) {
                $request->coupon = trim($request->coupon);
                $coupons = Coupon::where('code', strtoupper($request->coupon))->where('is_active', '1')->first();
                if (!empty($coupons)) {
                    $usedCoupun = $coupons->used_coupon();
                    $discount_value = ($price / 100) * $coupons->discount;
                    $plan->discounted_price = $price - $discount_value;
                    $coupons_id = $coupons->id;
                    if ($usedCoupun >= $coupons->limit) {
                        return response()->json(['error' => __('This coupon code has expired.')], 400);
                    }
                    $price = $price - $discount_value;
                } else {
                    return response()->json(['error' => __('This coupon code is invalid or has expired.')], 400);
                }
            }

            if ($price <= 0) {
                return response()->json(['error' => __('Invalid price.')], 400);
            }

            Coingate::config(
                array(
                    'environment' => $this->mode,
                    'auth_token' => $this->coingate_auth_token,
                    'curlopt_ssl_verifypeer' => FALSE,
                )
            );
            $post_params = array(
                'order_id' => time(),
                'price_amount' => $price,
                'price_currency' => $this->currency,
                'receive_currency' => $this->currency,
                'callback_url' => route('api.plan.coingate', [$request->plan_id, 'coupon_id=' . $coupons_id]),
                'cancel_url' => route('api.stripe', [$request->plan_id]),
                'success_url' => route('api.plan.coingate', [$request->plan_id, 'coupon_id=' . $coupons_id]),
                'title' => 'Plan #' . time(),
            );

            $order = Coingate::coingatePayment($post_params, 'POST');

            if ($order['status_code'] === 200) {
                return response()->json(['redirect_url' => $order['response']['payment_url']]);
            } else {
                return response()->json(['error' => __('Opps something went wrong.')], 500);
            }
        } else {
            return response()->json(['error' => 'Plan is deleted.'], 404);
        }
    }

    public function getPaymentStatus(Request $request, $plan)
    {
        $user = Auth::user();
        $planID = Crypt::decrypt($plan);
        $plan_id = $planID;
        $admin_payment_setting = Utility::getAdminPaymentSetting();
        $plan = Plan::find($plan_id);
        $price = $plan->price;
        if ($plan) {
            $orderID = time();
            if ($request->has('coupon_id') && $request->coupon_id != '') {
                $coupons = Coupon::find($request->coupon_id);
                if (!empty($coupons)) {
                    $usedCoupun = $coupons->used_coupon();
                    $discount_value = ($price / 100) * $coupons->discount;
                    $plan->discounted_price = $price - $discount_value;
                    $coupons_id = $coupons->id;
                    if ($usedCoupun >= $coupons->limit) {
                        return response()->json(['error' => __('This coupon code has expired.')], 400);
                    }
                    $price = $price - $discount_value;
                }
            }
            $order = new \App\Models\Order();
            $order->order_id = $orderID;
            $order->name = $user->name;
            $order->card_number = '';
            $order->card_exp_month = '';
            $order->card_exp_year = '';
            $order->plan_name = $plan->name;
            $order->plan_id = $plan->id;
            $order->price = $price;
            $order->price_currency = !empty($admin_payment_setting['currency']) ? $admin_payment_setting['currency'] : 'USD';
            $order->txn_id = isset($request->transaction_id) ? $request->transaction_id : '';
            $order->payment_type = __('Coingate');
            $order->payment_status = 'success';
            $order->receipt = '';
            $order->user_id = $user->id;
            $order->save();
            $assignPlan = $user->assignPlan($plan->id);
            if ($assignPlan['is_success']) {
                return response()->json(['message' => 'Plan activated Successfully.']);
            } else {
                return response()->json(['error' => $assignPlan['error']], 500);
            }
        } else {
            return response()->json(['error' => 'Plan is deleted.'], 404);
        }
    }
}
