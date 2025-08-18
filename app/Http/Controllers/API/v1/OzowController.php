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

class OzowController extends Controller
{
    public function planPayWithOzow(Request $request)
    {
        $payment_setting = Utility::getAdminPaymentSetting();
        $ozow_site_code = $payment_setting['ozow_site_code'];
        $ozow_private_key = $payment_setting['ozow_private_key'];
        $currency_code = isset($payment_setting['currency']) ? $payment_setting['currency'] : 'ZAR';
        $planID = Crypt::decrypt($request->plan_id);
        $plan = Plan::find($planID);
        $orderID = strtoupper(str_replace('.', '', uniqid('', true)));
        $authuser = Auth::user();

        if ($plan) {
            $get_amount = $plan->price;

            if (!empty($request->coupon)) {
                $coupons = Coupon::where('code', strtoupper($request->coupon))->where('is_active', '1')->first();
                if (!empty($coupons)) {
                    $usedCoupun = $coupons->used_coupon();
                    $discount_value = ($plan->price / 100) * $coupons->discount;
                    $get_amount = $plan->price - $discount_value;

                    if ($coupons->limit == $usedCoupun) {
                        return response()->json(['error' => __('This coupon code has expired.')], 400);
                    }
                } else {
                    return response()->json(['error' => __('This coupon code is invalid or has expired.')], 400);
                }
            }
            try {
                $call_back = route('api.plan.ozow.status');

                $data = [
                    "countryCode" => "ZA",
                    "amount" => $get_amount,
                    "currencyCode" => $currency_code,
                    "transactionReference" => "tr-" . $orderID,
                    "bankReference" => "br-" . $orderID,
                    "siteCode" => $ozow_site_code,
                    "privateKey" => $ozow_private_key,
                    "hashCheck" => $this->ozowHash($get_amount, $orderID, $ozow_site_code, $ozow_private_key),
                    "isTest" => ($payment_setting['ozow_mode'] == "sandbox") ? true : false,
                    "cancelUrl" => $call_back,
                    "errorUrl" => $call_back,
                    "successUrl" => $call_back,
                    "notifyUrl" => $call_back,
                    "optional1" => $request->plan_id,
                    "optional2" => $authuser->id,
                    "optional3" => $request->coupon,
                ];

                return response()->json($data);
            } catch (\Exception $e) {
                return response()->json(['error' => $e->getMessage()], 500);
            }
        }
    }

    public function ozowHash($amount, $order_id, $site_code, $private_key)
    {
        $string = $site_code . "ZAR" . number_format(sprintf('%.2f', $amount), 2, '.', '') . "tr-" . $order_id . "br-" . $order_id . false;
        $hash = hash('sha512', strtolower($string . $private_key));
        return $hash;
    }

    public function planGetOzowStatus(Request $request)
    {
        $payment_setting = Utility::getAdminPaymentSetting();
        $currency_code = isset($payment_setting['currency']) ? $payment_setting['currency'] : '';

        if ($request->Status == 'Complete') {
            $plan = Plan::find($request->optional1);
            $user = User::find($request->optional2);
            $orderID = strtoupper(str_replace('.', '', uniqid('', true)));

            $order = new \App\Models\Order();
            $order->order_id = $orderID;
            $order->name = $user->name;
            $order->card_number = '';
            $order->card_exp_month = '';
            $order->card_exp_year = '';
            $order->plan_name = $plan->name;
            $order->plan_id = $plan->id;
            $order->price = $request->amount;
            $order->price_currency = $currency_code;
            $order->txn_id = $request->TransactionId;
            $order->payment_type = __('Ozow');
            $order->payment_status = 'success';
            $order->receipt = '';
            $order->user_id = $user->id;
            $order->save();

            $assignPlan = $user->assignPlan($plan->id);
            if (!empty($request->optional3)) {
                $coupons = Coupon::where('code', strtoupper($request->optional3))->where('is_active', '1')->first();
                if (!empty($coupons)) {
                    $userCoupon = new UserCoupon();
                    $userCoupon->user = $user->id;
                    $userCoupon->coupon = $coupons->id;
                    $userCoupon->order = $orderID;
                    $userCoupon->save();
                    $usedCoupun = $coupons->used_coupon();
                    if ($coupons->limit <= $usedCoupun) {
                        $coupons->is_active = 0;
                        $coupons->save();
                    }
                }
            }

            if ($assignPlan['is_success']) {
                return response()->json(['message' => 'Plan activated Successfully!']);
            } else {
                return response()->json(['error' => $assignPlan['error']], 500);
            }
        } else {
            return response()->json(['error' => 'Your Payment Has Been Failled!'], 400);
        }
    }
}
