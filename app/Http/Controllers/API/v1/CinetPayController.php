<?php

namespace App\Http\Controllers\API\v1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Plan;
use App\Models\Coupon;
use App\Models\Utility;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;

class CinetPayController extends Controller
{
    public function planPayWithCinetPay(Request $request)
    {
        $payment_setting = Utility::getAdminPaymentSetting();
        $cinetpay_api_key = !empty($payment_setting['cinetpay_api_key']) ? $payment_setting['cinetpay_api_key'] : '';
        $cinetpay_site_id = !empty($payment_setting['cinetpay_site_id']) ? $payment_setting['cinetpay_site_id'] : '';
        $currency_code = isset($payment_setting['currency']) ? $payment_setting['currency'] : 'XOF';
        $user = Auth::user();
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
                if (
                    $currency_code != 'XOF' &&
                    $currency_code != 'CDF' &&
                    $currency_code != 'USD' &&
                    $currency_code != 'KMF' &&
                    $currency_code != 'GNF'
                ) {
                    return response()->json(['error' => __('Availabe currencies: XOF, CDF, USD, KMF, GNF')], 400);
                }
                $call_back = route('api.plan.cinetpay.return') . '?_token=' . csrf_token();
                $returnURL = route('api.plan.cinetpay.notify') . '?_token=' . csrf_token();

                $cinetpay_data =  [
                    "amount" => 100,
                    "currency" => $currency_code,
                    "apikey" => $cinetpay_api_key,
                    "site_id" => $cinetpay_site_id,
                    "transaction_id" => $orderID,
                    "description" => "Plan purchase",
                    "return_url" => $call_back,
                    "notify_url" => $returnURL,
                    "metadata" => "user001",
                    "channels" => "ALL",
                    'customer_name' => isset($authuser->name) ? $authuser->name : 'Test',
                    'customer_surname' => isset($authuser->name) ? $authuser->name : 'Test',
                    'customer_email' => isset($authuser->email) ? $authuser->email : 'test@gmail.com',
                    'customer_phone_number' => isset($authuser->mobile_number) ? $authuser->mobile_number : '1234567890',
                    'customer_address' => isset($authuser->address) ? $authuser->address  : 'A-101, alok area, USA',
                    'customer_city' => 'texas',
                    'customer_country' => 'BF',
                    'customer_state' => 'USA',
                    'customer_zip_code' => isset($authuser->zipcode) ? $authuser->zipcode : '432876',
                ];
                $curl = curl_init();

                curl_setopt_array($curl, array(
                    CURLOPT_URL => 'https://api-checkout.cinetpay.com/v2/payment',
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_ENCODING => "",
                    CURLOPT_MAXREDIRS => 10,
                    CURLOPT_TIMEOUT => 45,
                    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                    CURLOPT_CUSTOMREQUEST => 'POST',
                    CURLOPT_POSTFIELDS => json_encode($cinetpay_data),
                    CURLOPT_SSL_VERIFYPEER => 0,
                    CURLOPT_HTTPHEADER => array(
                        "content-type:application/json"
                    ),
                ));
                $response = curl_exec($curl);
                $err = curl_error($curl);
                curl_close($curl);

                $response_body = json_decode($response, true);
                if ($response_body['code'] == '201') {
                    $payment_link = $response_body["data"]["payment_url"];
                    return response()->json(['redirect_url' => $payment_link]);
                } else {
                    return response()->json(['error' => $response_body["description"]], 400);
                }
            } catch (\Exception $e) {
                return response()->json(['error' => $e->getMessage()], 500);
            }
        }
    }

    public function planCinetPayReturn(Request $request)
    {
        $cinetpaySession = $request->session()->get('cinetpaySession');
        $request->session()->forget('cinetpaySession');

        if (isset($request->transaction_id) || isset($request->token)) {
            $payment_setting = Utility::getAdminPaymentSetting();

            $cinetpay_check = [
                "apikey" => $payment_setting['cinetpay_api_key'],
                "site_id" => $payment_setting['cinetpay_site_id'],
                "transaction_id" => $request->transaction_id
            ];

            $response = $this->getPayStatus($cinetpay_check);

            $response_body = json_decode($response, true);
            $authuser = Auth::user();
            $plan = Plan::find($cinetpaySession['plan_id']);
            $getAmount = $cinetpaySession['amount'];
            $currency = isset($payment_setting['currency']) ? $payment_setting['currency'] : '';
            $orderID = strtoupper(str_replace('.', '', uniqid('', true)));

            if ($response_body['code'] == '00') {
                $order = \App\Models\Order::where('order_id', $request['order_id'])->first();
                $order->payment_status = 'success';
                $order->save();

                $assignPlan = $authuser->assignPlan($plan->id);
                if ($request->coupon_code) {
                    $coupons = Coupon::find($request->coupon_id);

                    if (!empty($request->coupon_id) && !empty($coupons)) {
                        $userCoupon = new \App\Models\UserCoupon();
                        $userCoupon->user = $authuser->id;
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
                return response()->json(['error' => 'Your Payment has failed!'], 400);
            }
        } else {
            return response()->json(['error' => 'Your Payment has failed!'], 400);
        }
    }
}
