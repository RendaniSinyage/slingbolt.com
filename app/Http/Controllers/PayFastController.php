<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\Request;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\Plan;
use App\Models\InvoicePayment;
use App\Models\UserCoupon;
use App\Models\Utility;
use App\Models\Invoice;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;


class PayFastController extends Controller
{
    public $payfast_merchant_id;
    public $payfast_merchant_key;
    public $payfast_signature;
    public $payfast_mode;
    public $is_enabled;
    public $currency;
    public $invoiceData;

    public function paymentConfig()
    {

            $payment_setting = Utility::getAdminPaymentSetting();

        $this->payfast_merchant_id = isset($payment_setting['payfast_merchant_id']) ? $payment_setting['payfast_merchant_id'] : '';
        $this->payfast_merchant_key = isset($payment_setting['payfast_merchant_key']) ? $payment_setting['payfast_merchant_key'] : '';
        $this->payfast_signature = isset($payment_setting['payfast_signature']) ? $payment_setting['payfast_signature'] : '';
        $this->payfast_mode = isset($payment_setting['payfast_mode']) ? $payment_setting['payfast_mode'] : 'off';
        $this->is_enabled = isset($payment_setting['is_payfast_enabled']) ? $payment_setting['is_payfast_enabled'] : 'off';
        $this->currency = isset($payment_setting['currency']) ? $payment_setting['currency'] : 'off';

        return $this;
    }

    public function companyPaymentConfig()
    {

            $payment_setting = Utility::getCompanyPaymentSetting($this->invoiceData->created_by);

            $setting = Utility::settingsById($this->invoiceData->created_by);

        $this->payfast_merchant_id = isset($payment_setting['payfast_merchant_id']) ? $payment_setting['payfast_merchant_id'] : '';
        $this->payfast_merchant_key = isset($payment_setting['payfast_merchant_key']) ? $payment_setting['payfast_merchant_key'] : '';
        $this->payfast_signature = isset($payment_setting['payfast_signature']) ? $payment_setting['payfast_signature'] : '';
        $this->payfast_mode = isset($payment_setting['payfast_mode']) ? $payment_setting['payfast_mode'] : 'off';
        $this->is_enabled = isset($payment_setting['is_payfast_enabled']) ? $payment_setting['is_payfast_enabled'] : 'off';
        $this->currency = isset($setting['site_currency']) ? $setting['site_currency'] : 'off';

        return $this;
    }

 


public function planPayWithPayfast(Request $request)
{
    $payment_setting = $this->paymentConfig();
    $planID = Crypt::decrypt($request->plan_id);
    $plan = Plan::find($planID);
    $user = Auth::user();
    
    if ($plan) {
        $plan_amount = $plan->price;
        $order_id = strtoupper(str_replace('.', '', uniqid('', true)));
        
        // Handle coupon logic
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
        
        // Handle free plans
        if ($plan_amount < 1) {
            $orderID = strtoupper(str_replace('.', '', uniqid('', true)));
            $order = new Order();
            $order->order_id = $orderID;
            $order->name = $user->name;
            $order->card_number = '';
            $order->card_exp_month = '';
            $order->card_exp_year = '';
            $order->plan_name = $plan->name;
            $order->plan_id = $plan->id;
            $order->price = $plan_amount;
            $order->price_currency = $this->currency;
            $order->txn_id = '';
            $order->payment_type = __('PayFast');
            $order->payment_status = 'success';
            $order->receipt = '';
            $order->user_id = $user->id;
            $order->save();
            
            $assignPlan = $user->assignPlan($plan->id);
            if ($assignPlan['is_success']) {
                return response()->json(['success' => __('Plan activated Successfully.')]);
            } else {
                return redirect()->route('plans.index')->with('error', __($assignPlan['error']));
            }
        }

        // Check for existing valid token
        $existingToken = $this->checkUserToken($user->id);
        
        if ($existingToken && $existingToken['is_valid']) {
            // User has valid token - charge directly via PayFast API
            $chargeResult = $this->chargeExistingToken($existingToken['token'], $plan_amount, $order_id, $plan);
            
            if ($chargeResult['success']) {
                // Create order record with existing card details
                $order = new Order();
                $order->order_id = $order_id;
                $order->name = $user->name;
                $order->plan_name = $plan->name;
                $order->plan_id = $plan->id;
                $order->price = $plan_amount;
                $order->price_currency = $this->currency;
                $order->txn_id = $chargeResult['transaction_id'];
                $order->payment_type = __('PayFast');
                $order->payment_status = 'success';
                $order->user_id = $user->id;
                
                // Store token and existing card details in ORDER
                $order->subscription_token = $existingToken['token'];
                $order->subscription_status = 'active';
                $order->card_last_four = $user->card_last_four;
                $order->card_type = $user->card_type;
                
                $order->save();
                
                $assignPlan = $user->assignPlan($plan->id);
                if ($assignPlan['is_success']) {
                    return response()->json(['success' => __('Plan activated Successfully using saved payment method.')]);
                }
            } else {
                // Token failed - remove it and card details from user
                $user->payfast_subscription_token = null;
                $user->payfast_token_created_at = null;
                $user->card_last_four = null;
                $user->card_type = null;
                $user->card_exp_month = null;
                $user->card_exp_year = null;
                $user->save();
            }
        }

        // NO VALID TOKEN - Proceed with regular PayFast subscription checkout
        $success = Crypt::encrypt([
            'plan' => $plan->toArray(),
            'order_id' => $order_id,
            'plan_amount' => $plan_amount
        ]);

        // PayFast subscription form data - MUST BE IN EXACT ORDER
        $data = array(
            'merchant_id' => !empty($payment_setting->payfast_merchant_id) ? $payment_setting->payfast_merchant_id : '',
            'merchant_key' => !empty($payment_setting->payfast_merchant_key) ? $payment_setting->payfast_merchant_key : '',
            'return_url' => route('payfast.payment.success', $success),
            'cancel_url' => route('plans.index'),
            'notify_url' => route('plans.index'), // Use existing route for now
            'name_first' => $user->name,
            'name_last' => '', // Keep empty but include field
            'email_address' => $user->email,
            // 'cell_number' => '', // Skip empty fields
            'm_payment_id' => $order_id,
            'amount' => number_format(sprintf('%.2f', $plan_amount), 2, '.', ''),
            'item_name' => $plan->name,
            // 'item_description' => '', // Skip empty fields
            'subscription_type' => '1',
            'billing_date' => date('Y-m-d'),
            'recurring_amount' => number_format(sprintf('%.2f', $plan_amount), 2, '.', ''),
            'frequency' => '3', // Monthly
            'cycles' => '0', // Unlimited
        );

        $passphrase = !empty($payment_setting->payfast_signature) ? $payment_setting->payfast_signature : '';
        
        // Use the REGULAR signature method for testing (same as invoices)
        $signature = $this->generateSignature($data, $passphrase);
        $data['signature'] = $signature;

        // Generate HTML form in the EXACT order for PayFast subscriptions
        // Form fields must be in this exact order (including merchant_key in form but not signature)
        $formFieldOrder = [
            'merchant_id', 'merchant_key', 'return_url', 'cancel_url', 'notify_url',
            'name_first', 'name_last', 'email_address', 'm_payment_id', 'amount',
            'item_name', 'subscription_type', 'billing_date', 'recurring_amount',
            'frequency', 'cycles', 'signature'
        ];
        
        $htmlForm = '';
        foreach ($formFieldOrder as $field) {
            if (isset($data[$field]) && $data[$field] !== '') {
                $htmlForm .= '<input name="' . $field . '" type="hidden" value=\'' . htmlspecialchars($data[$field], ENT_QUOTES) . '\' />';
            }
        }

        return response()->json([
            'success' => true,
            'inputs' => $htmlForm,
        ]);
    }
}
// Helper method to check if user has valid token
private function checkUserToken($userId)
{
    $user = User::find($userId);
    
    if (!$user->payfast_subscription_token) {
        return null;
    }
    
    // Verify token is still valid with PayFast API
    $isValid = $this->verifySubscriptionToken($user->payfast_subscription_token);
    
    if (!$isValid) {
        // Token is invalid, remove it
        $user->payfast_subscription_token = null;
        $user->save();
        return null;
    }
    
    return [
        'token' => $user->payfast_subscription_token,
        'is_valid' => true
    ];
}

// Verify if subscription token is still active
private function verifySubscriptionToken($token)
{
    try {
        $payment_setting = $this->paymentConfig();
        
        // PayFast API endpoint to fetch subscription details
        $apiUrl = $payment_setting->payfast_mode === 'sandbox' 
            ? 'https://api.payfast.co.za/subscriptions/' . $token . '/fetch?testing=true'
            : 'https://api.payfast.co.za/subscriptions/' . $token . '/fetch';
        
        $timestamp = date('c');
        $apiData = [
            'merchant-id' => $payment_setting->payfast_merchant_id,
            'version' => 'v1',
            'timestamp' => $timestamp,
        ];
        
        // Generate API signature
        $apiSignature = $this->generateApiSignature($apiData, $payment_setting->payfast_merchant_key);
        
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $apiUrl,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPGET => true,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'merchant-id: ' . $payment_setting->payfast_merchant_id,
                'version: v1',
                'timestamp: ' . $timestamp,
                'signature: ' . $apiSignature
            ],
            CURLOPT_TIMEOUT => 30,
            CURLOPT_SSL_VERIFYPEER => true,
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode === 200) {
            $responseData = json_decode($response, true);
            
            // Check if subscription is active
            if (isset($responseData['data']['status'])) {
                return in_array($responseData['data']['status'], ['1', 'active', 'ACTIVE']);
            }
        }
        
        return false;
        
    } catch (\Exception $e) {
        \Log::error('PayFast token verification failed', [
            'message' => $e->getMessage(),
            'token' => $token
        ]);
        return false;
    }
}

// Helper method to charge existing token
private function chargeExistingToken($token, $amount, $orderId, $plan)
{
    try {
        $payment_setting = $this->paymentConfig();
        
        // PayFast API endpoint for ad-hoc payments
        $apiUrl = $payment_setting->payfast_mode === 'sandbox' 
            ? 'https://api.payfast.co.za/subscriptions/' . $token . '/adhoc?testing=true'
            : 'https://api.payfast.co.za/subscriptions/' . $token . '/adhoc';
        
        // Prepare API data
        $apiData = [
            'merchant-id' => $payment_setting->payfast_merchant_id,
            'version' => 'v1',
            'timestamp' => date('c'),
            'amount' => (int)($amount * 100), // PayFast expects cents
            'item_name' => $plan->name,
            'item_description' => 'Plan: ' . $plan->name,
            'm_payment_id' => $orderId,
        ];
        
        // Generate API signature
        $apiSignature = $this->generateApiSignature($apiData, $payment_setting->payfast_merchant_key);
        $apiData['signature'] = $apiSignature;
        
        // Make API call
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $apiUrl,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($apiData),
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'merchant-id: ' . $payment_setting->payfast_merchant_id,
                'version: v1',
                'timestamp: ' . $apiData['timestamp'],
                'signature: ' . $apiSignature
            ],
            CURLOPT_TIMEOUT => 30,
            CURLOPT_SSL_VERIFYPEER => true,
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode === 200) {
            $responseData = json_decode($response, true);
            
            if (isset($responseData['status']) && $responseData['status'] === 'COMPLETE') {
                return [
                    'success' => true,
                    'transaction_id' => $responseData['pf_payment_id'] ?? $orderId,
                    'response' => $responseData
                ];
            }
        }
        
        // Log the failure for debugging
        \Log::error('PayFast ad-hoc payment failed', [
            'http_code' => $httpCode,
            'response' => $response,
            'token' => $token,
            'amount' => $amount
        ]);
        
        return [
            'success' => false,
            'error' => 'Payment failed',
            'http_code' => $httpCode,
            'response' => $response
        ];
        
    } catch (\Exception $e) {
        \Log::error('PayFast ad-hoc payment exception', [
            'message' => $e->getMessage(),
            'token' => $token,
            'amount' => $amount
        ]);
        
        return [
            'success' => false,
            'error' => $e->getMessage()
        ];
    }
}

// Generate signature for PayFast API calls
private function generateApiSignature($data, $merchantKey)
{
    // Sort data by key
    ksort($data);
    
    // Create parameter string
    $paramString = '';
    foreach ($data as $key => $value) {
        if ($key !== 'signature') {
            $paramString .= $key . '=' . urlencode($value) . '&';
        }
    }
    
    // Remove trailing &
    $paramString = rtrim($paramString, '&');
    
    // Create signature
    return md5($paramString . '&passphrase=' . urlencode($merchantKey));
}

// Helper method to remove invalid token
private function removeInvalidToken($userId)
{
    $user = User::find($userId);
    $user->payfast_subscription_token = null;
    $user->save();
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
        Utility::referralTransaction($plan);

        // Capture PayFast response data
        $subscription_token = request('token'); // PayFast subscription token
        $payment_id = request('pf_payment_id'); // PayFast payment ID
        $signature = request('signature'); // For verification
        
        // Card details from PayFast (if available)
        $card_last_four = request('card_last_four'); // Last 4 digits
        $card_type = request('card_type'); // Visa, MasterCard, etc.
        $card_exp_month = request('card_exp_month'); // MM
        $card_exp_year = request('card_exp_year'); // YYYY

        // 1. CREATE ORDER RECORD (store token and card details)
        $order = new Order();
        $order->order_id = $data['order_id'];
        $order->name = $user->name;
        $order->card_number = '';
        $order->card_exp_month = '';
        $order->card_exp_year = '';
        $order->plan_name = $data['plan']['name'];
        $order->plan_id = $data['plan']['id'];
        $order->price = $data['plan_amount'];
        $order->price_currency = $this->currency;
        $order->txn_id = $payment_id ?: $data['order_id'];
        $order->payment_type = __('PayFast');
        $order->payment_status = 'success';
        $order->receipt = '';
        $order->user_id = $user->id;
        
        // Store subscription and card details in ORDER
        if ($subscription_token) {
            $order->subscription_token = $subscription_token;
            $order->subscription_status = 'active';
        }
        if ($card_last_four) {
            $order->card_last_four = $card_last_four;
            $order->card_type = $card_type;
        }
        
        $order->save();

        // 2. UPDATE USER RECORD (store token and card details)
        if ($subscription_token) {
            $user->payfast_subscription_token = $subscription_token;
            $user->payfast_token_created_at = now();
            
            // Store card details in USER for future reference
            if ($card_last_four) {
                $user->card_last_four = $card_last_four;
                $user->card_type = $card_type;
                $user->card_exp_month = $card_exp_month;
                $user->card_exp_year = $card_exp_year;
            }
            
            $user->save();
        }

        $assignPlan = $user->assignPlan($data['plan']['id']);

        if ($assignPlan['is_success']) {
            return redirect()->route('plans.index')->with('success', __('Plan activated Successfully.'));
        } else {
            return redirect()->route('plans.index')->with('error', __($assignPlan['error']));
        }
    } catch (Exception $e) {
        return redirect()->route('plans.index')->with('error', __($e));
    }
}

    public function invoicePayWithPayFast(Request $request)
    {

        $invoiceID = Crypt::decrypt($request->invoice_id);
        $invoice                 = Invoice::find($invoiceID);
        $user      = User::find($invoice->created_by);
        $settings=Utility::settingsById($invoice->created_by);
        $this->invoiceData =$invoice;
        $payment_setting   = $this->companyPaymentConfig();
        $order_id = strtoupper(str_replace('.', '', uniqid('', true)));
        $get_amount = $request->amount;
        $success = Crypt::encrypt([
            'invoice' => $invoice->id,
            'order_id' => $order_id,
            'invoice_amount' => $get_amount
        ]);
        $data = array(
            'merchant_id' => !empty($payment_setting->payfast_merchant_id) ? $payment_setting->payfast_merchant_id : '',
            'merchant_key' => !empty($payment_setting->payfast_merchant_key) ? $payment_setting->payfast_merchant_key : '',
            'return_url' => route('invoice.payfast.status', $success),
            'name_first' => $user->name,
            'name_last' => '',
            'email_address' => $user->email,
            'm_payment_id' => $order_id, // Unique payment ID to pass through to notify_url
            'amount' => number_format(sprintf('%.2f', $get_amount), 2, '.', ''),
            'item_name' => $user->name,
            'payment_method' => 'cc',
        );
        $passphrase = !empty($payment_setting->payfast_signature) ? $payment_setting->payfast_signature : '';
        $signature = $this->generateSignature($data, $passphrase);
        $data['signature'] = $signature;
        $htmlForm = '';
        foreach ($data as $name => $value) {
            $htmlForm .= '<input name="' . $name . '" type="hidden" value=\'' . $value . '\' />';
        }
        return response()->json([
            'success' => true,
            'inputs' => $htmlForm,
        ]);

    }

    public function invoicepayfaststatus(Request $request, $success)
    {

        $data = Crypt::decrypt($success);
        $invoice                 = Invoice::find($data['invoice']);
//        $settings  = DB::table('settings')->where('created_by', '=', $invoice->created_by)->get()->pluck('value', 'name');
        $settings  = Utility::settingsById($invoice->created_by);
        if (empty($request->PayerID || empty($request->token)))
        {
            return redirect()->back()->with('error', __('Payment failed'));
        }
        try {
            $payments = InvoicePayment::create(
                    [
                        'invoice_id' => $invoice->id,
                        'date' => date('Y-m-d'),
                        'amount' => $data['invoice_amount'],
                        'payment_method' => 1,
                        'order_id' =>  $data['order_id'],
                        'currency' => Utility::getValByName('site_currency'),
                        'txn_id' =>  $data['order_id'],
                        'payment_type' => __('Payfast'),
                        'receipt' => '',
                        'reference' => '',
                        'description' => 'Invoice ' . Utility::invoiceNumberFormat($settings, $invoice->invoice_id),
                    ]
                );
                if ($invoice->getDue() <= 0) {
                    $invoice->status = 4;
                    $invoice->save();
                } elseif (($invoice->getDue() - $payments->amount) == 0) {
                    $invoice->status = 4;
                    $invoice->save();
                } elseif ($invoice->getDue() > 0) {
                    $invoice->status = 3;
                    $invoice->save();
                }
                else {
                    $invoice->status = 2;
                    $invoice->save();
                }

            //for customer balance update
            Utility::updateUserBalance('customer', $invoice->customer_id, $request->amount, 'debit');

            //For Notification
            $setting  = Utility::settingsById($invoice->created_by);
            $customer = Customer::find($invoice->customer_id);
            $notificationArr = [
                'payment_price' => $request->amount,
                'invoice_payment_type' => 'Payfast',
                'customer_name' => $customer->name,
            ];
            //Slack Notification
            if(isset($setting['payment_notification']) && $setting['payment_notification'] ==1)
            {
                Utility::send_slack_msg('new_invoice_payment', $notificationArr,$invoice->created_by);
            }
            //Telegram Notification
            if(isset($setting['telegram_payment_notification']) && $setting['telegram_payment_notification'] == 1)
            {
                Utility::send_telegram_msg('new_invoice_payment', $notificationArr,$invoice->created_by);
            }
            //Twilio Notification
            if(isset($setting['twilio_payment_notification']) && $setting['twilio_payment_notification'] ==1)
            {
                Utility::send_twilio_msg($customer->contact,'new_invoice_payment', $notificationArr,$invoice->created_by);
            }
            //webhook
            $module ='New Invoice Payment';
            $webhook=  Utility::webhookSetting($module,$invoice->created_by);
            if($webhook)
            {
                $parameter = json_encode($invoice);
                $status = Utility::WebhookCall($webhook['url'],$parameter,$webhook['method']);
                if($status == true)
                {
                    return redirect()->route('invoice.link.copy', Crypt::encrypt($invoice->id))->with('success', __(' Payment successfully added.'));
                }
                else
                {
                    return redirect()->back()->with('error', __('Payment successfully, Webhook call failed.'));
                }
            }

            if (Auth::check())
            {
                return redirect()->route('invoice.link.copy', Crypt::encrypt($invoice->id))->with('success', __(' Payment successfully added.'));
            }
            else
            {
                return redirect()->back()->with('success', __(' Payment successfully added.'));
            }
        }
        catch (\Exception $e)
            {
                if (Auth::check())
                {
                    return redirect()->route('invoice.link.copy', Crypt::encrypt($invoice->id))->with('error', __('Transaction has been failed! '));
                } else
                {
                    return redirect()->back()->with('success', __('Transaction has been complted.'));
                }
            }
    }

}
