<?php

namespace App\Http\Controllers\API\v1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Plan;
use App\Models\Coupon;
use App\Models\Utility;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;

class AuthorizeNetController extends Controller
{
    public function planPayWithAuthorizeNet(Request $request)
    {
        $payment_setting = Utility::getAdminPaymentSetting();
        $currency = isset($payment_setting['currency']) ? $payment_setting['currency'] : 'USD';

        $planID = Crypt::decrypt($request->plan_id);
        $plan = Plan::find($planID);
        $authuser = Auth::user();

        if ($plan) {
            $price = $plan->price;
            if (isset($request->coupon) && !empty($request->coupon)) {
                $request->coupon = trim($request->coupon);
                $coupons = Coupon::where('code', strtoupper($request->coupon))->where('is_active', '1')->first();
                if (!empty($coupons)) {
                    $usedCoupun = $coupons->used_coupon();
                    $discount_value = ($price / 100) * $coupons->discount;
                    $plan->discounted_price = $price - $discount_value;

                    if ($usedCoupun >= $coupons->limit) {
                        return response()->json(['error' => 'This coupon code has expired.'], 400);
                    }
                    $price = $price - $discount_value;
                } else {
                    return response()->json(['error' => 'This coupon code is invalid or has expired.'], 400);
                }
            }

            $data = [
                'id' =>  $plan->id,
                'amount' =>  $price,
                'coupon_code' =>  $request->coupon,
            ];

            return response()->json(['data' => $data]);
        } else {
            return response()->json(['error' => 'Plan is deleted.'], 404);
        }
    }

    public function planPayWithAuthorizeNetData(Request $request)
    {
        $input = $request->all();
        $data = $input['data'];
        $adminPaymentSettings = Utility::getAdminPaymentSetting();
        $currency = $adminPaymentSettings['currency'];

        $plan = Plan::find($data['id']);
        $amount =  $data['amount'];

        $user = Auth::user();

        $orderID = strtoupper(str_replace('.', '', uniqid('', true)));

        $order = new \App\Models\Order();
        $order->order_id = $orderID;
        $order->name = $user->name;
        $order->card_number = '';
        $order->card_exp_month = '';
        $order->card_exp_year = '';
        $order->plan_name = $plan->name;
        $order->plan_id = $plan->id;
        $order->price = $amount;
        $order->price_currency = $currency;
        $order->txn_id = time();
        $order->payment_type = __('AuthorizetNet');
        $order->payment_status = 'success';
        $order->txn_id = '';
        $order->receipt = '';
        $order->user_id = $user->id;
        $order->save();
        $user = User::find($user->id);

        $assignPlan = $user->assignPlan($plan->id);

        if ($assignPlan['is_success']) {
            return response()->json(['message' => 'Plan activated Successfully.']);
        } else {
            return response()->json(['error' => $assignPlan['error']], 500);
        }
    }
}
