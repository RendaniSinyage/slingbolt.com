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

class PayFastController extends Controller
{
    public $payfast_merchant_id;
    public $payfast_merchant_key;
    public $payfast_signature;
    public $payfast_mode;
    public $is_enabled;
    public $currency;

    public function paymentConfig()
    {
        $payment_setting = Utility::getAdminPaymentSetting();
        $this->payfast_merchant_id = isset($payment_setting['payfast_merchant_id']) ? $payment_setting['payfast_merchant_id'] : '';
        $this->payfast_merchant_key = isset($payment_setting['payfast_merchant_key']) ? $payment_setting['payfast_merchant_key'] : '';
        $this->payfast_signature = isset($payment_setting['payfast_signature']) ? $payment_setting['payfast_signature'] : '';
        $this->payfast_mode = isset($payment_setting['payfast_mode']) ? $payment_setting['payfast_mode'] : 'off';
        $this->is_enabled = isset($payment_setting['is_payfast_enabled']) ? $payment_setting['is_payfast_enabled'] : 'off';
        $this->currency = isset($payment_setting['currency']) ? $payment_setting['currency'] : 'USD';
        return $this;
    }

    public function planPayWithPayfast(Request $request)
    {
        $payment_setting = $this->paymentConfig();
        $planID = Crypt::decrypt($request->plan_id);
        $plan = Plan::find($planID);
        if ($plan) {
            $plan_amount = $plan->price;
            $order_id = strtoupper(str_replace('.', '', uniqid('', true)));
            $user = Auth::user();
            if ($request->coupon_code != null) {
                $coupons = Coupon::where('code', $request->coupon_code)->first();
                if (!empty($coupons)) {
                    $userCoupon = new UserCoupon();
                    $userCoupon->user = $user->id;
                    $userCoupon->coupon = $coupons->id;
                    $userCoupon->order = $order_id;
                    $userCoupon->save();
                    $usedCoupun = $coupons->used_coupon();
                    if ($coupons->limit <= $usedCoupun) {
                        $coupons->is_active = 0;
                        $coupons->save();
                    }
                    $plan_amount = $request->coupon_amount;
                }
            }
            if ($plan_amount < 1) {
                return response()->json(['error' => 'Invalid amount.'], 400);
            }

            $success = Crypt::encrypt([
                'plan' => $plan->toArray(),
                'order_id' => $order_id,
                'plan_amount' => $plan_amount
            ]);

            $data = array(
                'merchant_id' => !empty($payment_setting->payfast_merchant_id) ? $payment_setting->payfast_merchant_id : '',
                'merchant_key' => !empty($payment_setting->payfast_merchant_key) ? $payment_setting->payfast_merchant_key : '',
                'return_url' => route('api.payfast.payment.success', $success),
                'cancel_url' => route('plans.index'),
                'notify_url' => route('plans.index'),
                'name_first' => $user->name,
                'name_last' => '',
                'email_address' => $user->email,
                'm_payment_id' => $order_id,
                'amount' => number_format(sprintf('%.2f', $plan_amount), 2, '.', ''),
                'item_name' => $plan->name,
            );

            $passphrase = !empty($payment_setting->payfast_signature) ? $payment_setting->payfast_signature : '';
            $signature = $this->generateSignature($data, $passphrase);
            $data['signature'] = $signature;

            return response()->json($data);
        }
    }

    public function generateSignature($data, $passPhrase = null)
    {
        $pfOutput = '';
        foreach ($data as $key => $val) {
            if ($val !== '') {
                $pfOutput .= $key . '=' . urlencode(trim($val)) . '&';
            }
        }

        $getString = substr($pfOutput, 0, -1);
        if ($passPhrase !== null) {
            $getString .= '&passphrase=' . urlencode(trim($passPhrase));
        }
        return md5($getString);
    }

    public function getPaymentStatus($success)
    {
        $payment_setting = $this->paymentConfig();

        try {
            $user = Auth::user();
            $data = Crypt::decrypt($success);

            $plan = Plan::find($data['plan']['id']);
            $order = new \App\Models\Order();
            $order->order_id = $data['order_id'];
            $order->name = $user->name;
            $order->card_number = '';
            $order->card_exp_month = '';
            $order->card_exp_year = '';
            $order->plan_name = $data['plan']['name'];
            $order->plan_id = $data['plan']['id'];
            $order->price = $data['plan_amount'];
            $order->price_currency = $this->currency;
            $order->txn_id = $data['order_id'];
            $order->payment_type = __('PayFast');
            $order->payment_status = 'success';
            $order->txn_id = '';
            $order->receipt = '';
            $order->user_id = $user->id;
            $order->save();
            $assignPlan = $user->assignPlan($data['plan']['id']);

            if ($assignPlan['is_success']) {
                return response()->json(['message' => 'Plan activated Successfully.']);
            } else {
                return response()->json(['error' => $assignPlan['error']], 500);
            }
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
