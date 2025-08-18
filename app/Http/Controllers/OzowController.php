<?php

namespace App\Http\Controllers;

use App\Models\Coupon;
use App\Models\Invoice;
use App\Models\InvoicePayment;
use App\Models\Order;
use App\Models\Plan;
use App\Models\User;
use App\Models\UserCoupon;
use App\Models\Utility;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;

class OzowController extends Controller
{
    public function planPayWithOzow(Request $request)
    {
        $payment_setting = Utility::getAdminPaymentSetting();
        $ozow_site_code = $payment_setting['ozow_site_code'];
        $ozow_private_key = $payment_setting['ozow_private_key'];
        $ozow_secret_key = $payment_setting['ozow_secret_key'];
        $currency_code = isset($payment_setting['currency']) ? $payment_setting['currency'] : 'ZAR';
        $planID = \Illuminate\Support\Facades\Crypt::decrypt($request->plan_id);
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
                        return redirect()->back()->with('error', __('This coupon code has expired.'));
                    }
                    if ($get_amount <= 0) {
                        $authuser = Auth::user();
                        $authuser->plan = $plan->id;
                        $authuser->save();
                        $assignPlan = $authuser->assignPlan($plan->id);
                        if ($assignPlan['is_success'] == true && !empty($plan)) {

                            $orderID = strtoupper(str_replace('.', '', uniqid('', true)));
                            $userCoupon = new UserCoupon();

                            $userCoupon->user = $authuser->id;
                            $userCoupon->coupon = $coupons->id;
                            $userCoupon->order = $orderID;
                            $userCoupon->save();
                            Order::create(
                                [
                                    'order_id' => $orderID,
                                    'name' => null,
                                    'email' => null,
                                    'card_number' => null,
                                    'card_exp_month' => null,
                                    'card_exp_year' => null,
                                    'plan_name' => $plan->name,
                                    'plan_id' => $plan->id,
                                    'price' => $get_amount == null ? 0 : $get_amount,
                                    'price_currency' => $currency_code,
                                    'txn_id' => '',
                                    'payment_type' => 'Ozow',
                                    'payment_status' => 'success',
                                    'receipt' => null,
                                    'user_id' => $authuser->id,
                                ]
                            );
                            $assignPlan = $authuser->assignPlan($plan->id);
                            return redirect()->route('plans.index')->with('success', __('Plan Successfully Activated'));
                        }
                    }
                } else {
                    return redirect()->back()->with('error', __('This coupon code is invalid or has expired.'));
                }
            }
            try {
                $call_back = route('plan.ozow.status');

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
                    "optional2" => $user->id,
                    "optional3" => $request->coupon,
                ];

                $ozowSession = [
                    'order_id' => $orderID,
                    'amount' => $get_amount,
                    'plan_id' => $plan->id,
                    'coupon_id' => !empty($coupons->id) ? $coupons->id : '',
                    'coupon_code' => !empty($request->coupon) ? $request->coupon : '',
                ];

                $request->session()->put('ozowSession', $ozowSession);

                return view('plan.ozow_redirect', compact('data'));
            } catch (\Exception $e) {
                \Log::debug($e->getMessage());
                return redirect()->route('plans.index')->with('error', $e->getMessage());
            }
        }
    }

    public function ozowHash($amount, $order_id, $site_code, $private_key)
    {
        $string = $site_code . "ZAR" . number_format(sprintf('%.2f', $amount), 2, '.', '') . "tr-" . $order_id . "br-" . $orderID . false;
        $hash = hash('sha512', strtolower($string . $private_key));
        return $hash;
    }

    public function planGetOzowStatus(Request $request)
    {
        $ozowSession = $request->session()->get('ozowSession');
        $request->session()->forget('ozowSession');

        $payment_setting = Utility::getAdminPaymentSetting();
        $currency_code = isset($payment_setting['currency']) ? $payment_setting['currency'] : '';

        if ($request->Status == 'Complete') {

            $plan = Plan::find($ozowSession['plan_id']);
            $user = User::find(Auth::user()->id);
            $orderID = $ozowSession['order_id'];

            Utility::referralTransaction($plan);

            $order = new Order();
            $order->order_id = $orderID;
            $order->name = $user->name;
            $order->card_number = '';
            $order->card_exp_month = '';
            $order->card_exp_year = '';
            $order->plan_name = $plan->name;
            $order->plan_id = $plan->id;
            $order->price = $ozowSession['amount'];
            $order->price_currency = $currency_code;
            $order->txn_id = $request->TransactionId;
            $order->payment_type = __('Ozow');
            $order->payment_status = 'success';
            $order->receipt = '';
            $order->user_id = $user->id;
            $order->save();

            $assignPlan = $user->assignPlan($plan->id);
            if (!empty($ozowSession['coupon_id'])) {
                $coupons = Coupon::find($ozowSession['coupon_id']);
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
                return redirect()->route('plans.index')->with('success', __('Plan activated Successfully!'));
            } else {
                return redirect()->route('plans.index')->with('error', __($assignPlan['error']));
            }
        } else {
            return redirect()->route('plans.index')->with('error', __('Your Payment Has Been Failled!'));
        }
    }
}
