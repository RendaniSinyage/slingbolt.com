<?php

namespace App\Http\Controllers\API\v1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Plan;
use App\Models\Coupon;
use App\Models\Utility;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;

class AamarpayController extends Controller
{
    public function pay(Request $request)
    {
        $payment_setting = Utility::getAdminPaymentSetting();
        $aamarpay_store_id = $payment_setting['aamarpay_store_id'];
        $aamarpay_signature_key = $payment_setting['aamarpay_signature_key'];
        $aamarpay_description = $payment_setting['aamarpay_description'];
        $aamarpay_mode = $payment_setting['aamarpay_mode'];
        if ($aamarpay_mode == "sandbox") {
            $url = "https://sandbox.aamarpay.com/request.php";
        } else {
            $url =  "https://secure.aamarpay.com/request.php";
        }
        $currency = $payment_setting['currency'];
        $planID = Crypt::decrypt($request->plan_id);
        $authuser = Auth::user();
        $plan = Plan::find($planID);

        if ($plan) {
            $get_amount = $plan->price;

            try {
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
                $coupon = (empty($request->coupon)) ? "0" : $request->coupon;
                $orderID = strtoupper(str_replace('.', '', uniqid('', true)));
                $fields = array(
                    'store_id' => $aamarpay_store_id,
                    'amount' => $get_amount,
                    'payment_type' => '',
                    'currency' => $currency,
                    'tran_id' => $orderID,
                    'cus_name' => $authuser->name,
                    'cus_email' => $authuser->email,
                    'cus_add1' => '',
                    'cus_add2' => '',
                    'cus_city' => '',
                    'cus_state' => '',
                    'cus_postcode' => '',
                    'cus_country' => '',
                    'cus_phone' => '1234567890',
                    'success_url' => route('api.pay.aamarpay.success', Crypt::encrypt(['response'=>'success','coupon' => $coupon, 'plan_id' => $plan->id, 'price' => $get_amount, 'order_id' => $orderID])),
                    'fail_url' => route('api.pay.aamarpay.success', Crypt::encrypt(['response'=>'failure','coupon' => $coupon, 'plan_id' => $plan->id, 'price' => $get_amount, 'order_id' => $orderID])),
                    'cancel_url' => route('api.pay.aamarpay.success', Crypt::encrypt(['response'=>'cancel'])),
                    'signature_key' => $aamarpay_signature_key,
                    'desc' => $aamarpay_description,
                );

                $fields_string = http_build_query($fields);

                $ch = curl_init();
                curl_setopt($ch, CURLOPT_VERBOSE, true);
                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $fields_string);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                $url_forward = str_replace('"', '', stripslashes(curl_exec($ch)));
                curl_close($ch);

                if($aamarpay_mode == 'sandbox'){
                    $redirect_url = 'https://sandbox.aamarpay.com/' . $url_forward;
                } else {
                    $redirect_url = 'https://secure.aamarpay.com/' . $url_forward;
                }

                return response()->json(['redirect_url' => $redirect_url]);

            } catch (\Exception $e) {
                return response()->json(['error' => $e->getMessage()], 500);
            }
        } else {
            return response()->json(['error' => 'Plan is deleted.'], 404);
        }
    }

    public function aamarpaySuccess($data, Request $request)
    {
        $data = Crypt::decrypt($data);
        $user = Auth::user();

        if ($data['response'] == "success")
        {
            $plan = Plan::find($data['plan_id']);
            $couponCode = $data['coupon'];
            $getAmount = $data['price'];
            $orderID = $data['order_id'];
            if ($couponCode != 0) {
                $coupons = Coupon::where('code', strtoupper($couponCode))->where('is_active', '1')->first();
                $request['coupon_id'] = $coupons->id;
            } else {
                $coupons = null;
            }

            $order = new \App\Models\Order();
            $order->order_id = $orderID;
            $order->name = $user->name;
            $order->card_number = '';
            $order->card_exp_month = '';
            $order->card_exp_year = '';
            $order->plan_name = $plan->name;
            $order->plan_id = $plan->id;
            $order->price = $getAmount;
            $order->price_currency = !empty($payment_setting['currency']) ? $payment_setting['currency'] : 'BDT';
            $order->payment_type = __('Aamarpay');
            $order->payment_status = 'success';
            $order->txn_id = '';
            $order->receipt = '';
            $order->user_id = $user->id;
            $order->save();
            $assignPlan = $user->assignPlan($plan->id);

            if (!empty($request->coupon_id) && !empty($coupons)) {
                $userCoupon = new \App\Models\UserCoupon();
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

            if ($assignPlan['is_success']) {
                return response()->json(['message' => 'Plan activated Successfully.']);
            } else {
                return response()->json(['error' => $assignPlan['error']], 500);
            }
        }
        elseif ($data['response'] == "cancel")
        {
            return response()->json(['error' => 'Your payment is cancel'], 400);
        }
        else {
            return response()->json(['error' => 'Your Transaction is fail please try again'], 400);
        }
    }
}
