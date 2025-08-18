<?php

namespace App\Http\Controllers\API\v1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Plan;
use App\Models\Coupon;
use App\Models\Utility;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Session;
use Srmklive\PayPal\Services\PayPal as PayPalClient;

class PaypalController extends Controller
{
    protected $invoiceData;

    public function paymentConfig()
    {
        $payment_setting = Utility::getAdminPaymentSetting();
        if ($payment_setting['paypal_mode'] == 'live') {
            config([
                'paypal.live.client_id' => isset($payment_setting['paypal_client_id']) ? $payment_setting['paypal_client_id'] : '',
                'paypal.live.client_secret' => isset($payment_setting['paypal_secret_key']) ? $payment_setting['paypal_secret_key'] : '',
                'paypal.mode' => isset($payment_setting['paypal_mode']) ? $payment_setting['paypal_mode'] : '',
            ]);
        } else {
            config([
                'paypal.sandbox.client_id' => isset($payment_setting['paypal_client_id']) ? $payment_setting['paypal_client_id'] : '',
                'paypal.sandbox.client_secret' => isset($payment_setting['paypal_secret_key']) ? $payment_setting['paypal_secret_key'] : '',
                'paypal.mode' => isset($payment_setting['paypal_mode']) ? $payment_setting['paypal_mode'] : '',
            ]);
        }
    }

    public function planPayWithPaypal(Request $request)
    {
        $planID = Crypt::decrypt($request->plan_id);
        $plan = Plan::find($planID);
        $this->paymentConfig();
        $payment_setting = Utility::getAdminPaymentSetting();

        $provider = new PayPalClient;
        $provider->setApiCredentials(config('paypal'));
        $get_amount = $plan->price;

        if ($plan) {
            try {
                $coupon_id = null;
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
                        $coupon_id = $coupons->id;
                    } else {
                        return response()->json(['error' => __('This coupon code is invalid or has expired.')], 400);
                    }
                }
                $paypalToken = $provider->getAccessToken();
                $response = $provider->createOrder([
                    "intent" => "CAPTURE",
                    "application_context" => [
                        "return_url" => route('api.plan.get.payment.status', [$plan->id, $price]),
                        "cancel_url" =>  route('api.plan.get.payment.status', [$plan->id, $price]),
                    ],
                    "purchase_units" => [
                        0 => [
                            "amount" => [
                                "currency_code" => isset($payment_setting['currency']) ? $payment_setting['currency'] : 'USD',
                                "value" => $price,
                            ]
                        ]
                    ]
                ]);
                if (isset($response['id']) && $response['id'] != null) {
                    foreach ($response['links'] as $links) {
                        if ($links['rel'] == 'approve') {
                            return response()->json(['redirect_url' => $links['href']]);
                        }
                    }
                    return response()->json(['error' => 'Something went wrong.'], 500);
                } else {
                    return response()->json(['error' => $response['message'] ?? 'Something went wrong.'], 500);
                }
            } catch (\Exception $e) {
                return response()->json(['error' => $e->getMessage()], 500);
            }
        } else {
            return response()->json(['error' => 'Plan is deleted.'], 404);
        }
    }

    public function planGetPaymentStatus(Request $request, $plan_id)
    {
        $this->paymentConfig();
        $user = Auth::user();
        $plan = Plan::find($plan_id);
        if ($plan) {
            $provider = new PayPalClient;
            $provider->setApiCredentials(config('paypal'));
            $provider->getAccessToken();
            $response = $provider->capturePaymentOrder($request['token']);
            $payment_id = Session::get('paypal_payment_id');
            $orderID = strtoupper(str_replace('.', '', uniqid('', true)));

            if (isset($response['status']) && $response['status'] == 'COMPLETED') {
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
                $order->price = $plan->price;
                $order->price_currency = config('paypal.currency');
                $order->txn_id = '';
                $order->payment_type = __('PAYPAL');
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
                return response()->json(['error' => $response['message'] ?? 'Something went wrong.'], 500);
            }
        } else {
            return response()->json(['error' => 'Plan is deleted.'], 404);
        }
    }
}
