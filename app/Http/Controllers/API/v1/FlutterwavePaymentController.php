<?php

namespace App\Http\Controllers\API\v1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Plan;
use App\Models\Coupon;
use App\Models\Utility;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;

class FlutterwavePaymentController extends Controller
{
    public $secret_key;
    public $public_key;
    public $is_enabled;
    protected $currency;

    public function paymentConfig()
    {
        $payment_setting = Utility::getAdminPaymentSetting();
        $this->secret_key = isset($payment_setting['flutterwave_secret_key']) ? $payment_setting['flutterwave_secret_key'] : '';
        $this->public_key = isset($payment_setting['flutterwave_public_key']) ? $payment_setting['flutterwave_public_key'] : '';
        $this->is_enabled = isset($payment_setting['is_flutterwave_enabled']) ? $payment_setting['is_flutterwave_enabled'] : 'off';
        $this->currency = isset($payment_setting['currency']) ? $payment_setting['currency'] : 'USD';
    }

    public function planPayWithFlutterwave(Request $request)
    {
        $this->paymentConfig();

        $planID = Crypt::decrypt($request->plan_id);
        $plan = Plan::find($planID);
        $authuser = Auth::user();
        $coupon_id = '';
        if ($plan) {
            $price = $plan->price;
            if (isset($request->coupon) && !empty($request->coupon)) {
                $coupons = Coupon::where('code', strtoupper($request->coupon))->where('is_active', '1')->first();
                if (!empty($coupons)) {
                    $usedCoupun = $coupons->used_coupon();
                    $discount_value = ($price / 100) * $coupons->discount;
                    $price = $price - $discount_value;
                    if ($coupons->limit == $usedCoupun) {
                        return response()->json(['error' => __('This coupon code has expired.')], 400);
                    }
                    $coupon_id = $coupons->id;
                } else {
                    return response()->json(['error' => __('This coupon code is invalid or has expired.')], 400);
                }
            }
            if ($price <= 0) {
                return response()->json(['error' => __('Invalid price.')], 400);
            }

            $res_data['email'] = Auth::user()->email;
            $res_data['total_price'] = $price;
            $res_data['currency'] = $this->currency;
            $res_data['coupon'] = $coupon_id;

            return response()->json($res_data);
        } else {
            return response()->json(['error' => __('Plan is deleted.')], 404);
        }
    }

    public function getPaymentStatus(Request $request, $pay_id, $plan)
    {
        $this->paymentConfig();
        $planID = Crypt::decrypt($plan);
        $plan = Plan::find($planID);
        $user = Auth::user();

        if ($plan) {
            try {
                $orderID = time();
                $data = array(
                    'txref' => $pay_id,
                    'SECKEY' => $this->secret_key,
                );
                $headers = array('Content-Type' => 'application/json');
                $body = \Unirest\Request\Body::json($data);
                $url = "https://api.ravepay.co/flwv3-pug/getpaidx/api/v2/verify";
                $response = \Unirest\Request::post($url, $headers, $body);

                if (!empty($response)) {
                    $response = json_decode($response->raw_body, true);
                }
                if (isset($response['status']) && $response['status'] == 'success') {
                    $paydata = $response['data'];
                    if ($request->has('coupon_id') && $request->coupon_id != '') {
                        $coupons = Coupon::find($request->coupon_id);
                        if (!empty($coupons)) {
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
                    }
                    $order = new \App\Models\Order();
                    $order->order_id = $orderID;
                    $order->name = $user->name;
                    $order->card_number = '';
                    $order->card_exp_month = '';
                    $order->card_exp_year = '';
                    $order->plan_name = $plan->name;
                    $order->plan_id = $plan->id;
                    $order->price = isset($paydata['amount']) ? $paydata['amount'] : 0;
                    $order->price_currency = $this->currency;
                    $order->txn_id = isset($paydata['txid']) ? $paydata['txid'] : $pay_id;
                    $order->payment_type = __('Flutterwave');
                    $order->payment_status = 'success';
                    $order->receipt = '';
                    $order->user_id = $user->id;
                    $order->save();
                    $assignPlan = $user->assignPlan($plan->id);
                    if ($assignPlan['is_success']) {
                        return response()->json(['message' => 'Plan activated Successfully!']);
                    } else {
                        return response()->json(['error' => $assignPlan['error']], 500);
                    }
                } else {
                    return response()->json(['error' => 'Transaction has been failed! '], 400);
                }
            } catch (\Exception $e) {
                return response()->json(['error' => 'Plan not found!'], 404);
            }
        }
    }
}
