<?php

namespace App\Http\Controllers\Auth;

use App\Events\VerifyReCaptchaToken;
use App\Http\Controllers\Controller;
use App\Models\ExperienceCertificate;
use App\Models\GenerateOfferLetter;
use App\Models\JoiningLetter;
use App\Models\NOC;
use App\Models\User;
use App\Models\Plan;
use App\Models\Utility;
use Auth;
use App\Providers\RouteServiceProvider;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Spatie\Permission\Models\Role;
use App\Services\CompanyClonerService;
use Illuminate\Support\Facades\DB;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     *
     * @return \Illuminate\View\View
     */

  public function __construct()
    {
        $this->middleware('guest');
    }


    public function create()
    {
        // return view('auth.register');
    }

     /**
          * Handle an incoming registration request.
          *
          * @param  \Illuminate\Http\Request  $request
          * @return \Illuminate\Http\RedirectResponse
          *
          * @throws \Illuminate\Validation\ValidationException
          */
         public function store(Request $request)
         {
             // EMAIL-based rate limiting: Check if this email has been attempted recently
             $userEmail = $request->email;
             $recentEmailAttempts = User::where('email', $userEmail)
                 ->where('created_at', '>', now()->subHour())
                 ->count();

             if ($recentEmailAttempts >= 1) {
                 \Log::warning("Registration blocked - Email already attempted recently", [
                     'email' => $userEmail,
                     'ip' => $request->ip(),
                     'user_agent' => $request->userAgent()
                 ]);

                 return redirect()->back()->with('status', __('A verification email was already sent to this address. Please check your email and spam folder.'));
             }

             // IP-based rate limiting: Check registrations from this IP in last hour
             $userIP = $request->ip();
             $recentRegistrations = User::where('registration_ip', $userIP)
                 ->where('created_at', '>', now()->subHour())
                 ->count();

             if ($recentRegistrations >= 3) {
                 \Log::warning("Public registration blocked - IP rate limit exceeded", [
                     'ip' => $userIP,
                     'recent_count' => $recentRegistrations,
                     'user_agent' => $request->userAgent()
                 ]);

                 return redirect()->back()->with('status', __('Too many registration attempts. Please try again later.'));
             }

             $settings = Utility::settings();
             //ReCpatcha
             $validation = [];

             if(isset($settings['recaptcha_module']) && $settings['recaptcha_module'] == 'on')
             {
                 if($settings['google_recaptcha_version'] == 'v2-checkbox'){
                     $validation['g-recaptcha-response'] = 'required|captcha';
                 }
                 elseif($settings['google_recaptcha_version'] == 'v3-checkbox'){
                     $result = event(new VerifyReCaptchaToken($request));

                     if (!isset($result[0]['status']) || $result[0]['status'] != true) {
                         $key = 'g-recaptcha-response';
                         $request->merge([$key => null]); // Set the key to null

                         $validation['g-recaptcha-response'] = 'required';
                     }
                 }else{
                     $validation = [];
                 }
             }else{
                 $validation = [];
             }
             $this->validate($request, $validation);
             $request->validate([
                 'name' => 'required|string|max:255',
                 'email' => 'required|string|email|max:255|unique:users',
                 'password' => ['required', 'string',
                              'min:8','confirmed', Rules\Password::defaults()],
                 'company_name' => 'required|string|max:255',
                 'terms' => 'required',
             ]);

             do {
                 $code = rand(100000, 999999);
             } while (User::where('referral_code', $code)->exists());

             // Handle plan selection and trial assignment
             $selectedPlan = 1; // Default free plan
             $trialPlan = 0;
             $trialExpireDate = null;
             $planExpireDate = null; // Add plan expiration
             $requestedPlan = 0;

             if (isset($request->plan) && !empty($request->plan)) {
                 try {
                     $planId = Crypt::decrypt($request->plan);
                     $plan = Plan::find($planId);

                     if ($plan && $plan->is_disable == 1) {
                         // Give them the actual plan with trial expiration
                         $selectedPlan = $planId; // Give them the actual plan
                         $trialPlan = $planId; // Track it's a trial
                         $requestedPlan = 0; // Not needed since they have the plan

                         // Set trial expiration - use plan's trial_days or default to 14 days
                         $trialDays = $plan->trial_days && $plan->trial_days > 0 ? $plan->trial_days : 14;
                         $trialExpireDate = now()->addDays($trialDays)->toDateString();
                         $planExpireDate = now()->addDays($trialDays)->toDateString(); // Set plan expiration

                         \Log::info("User selected plan trial", [
                             'plan_id' => $planId,
                             'plan_name' => $plan->name,
                             'trial_days' => $trialDays,
                             'trial_expire_date' => $trialExpireDate,
                             'selected_plan' => $selectedPlan,
                             'trial_plan' => $trialPlan,
                             'plan_expire_date' => $planExpireDate
                         ]);
                     }
                 } catch (\Exception $e) {
                     \Log::warning("Invalid plan parameter provided", [
                         'plan_param' => $request->plan,
                         'error' => $e->getMessage()
                     ]);
                     // Fall back to default plan
                     $selectedPlan = 1;
                 }
             } else {
                 // No plan selected - give them a trial of plan 3 by default
                 $defaultTrialPlan = Plan::find(3);
                 if ($defaultTrialPlan && $defaultTrialPlan->is_disable == 1) {
                     $selectedPlan = 3; // Give them plan 3
                     $trialPlan = 3; // Track it's a trial
                     $requestedPlan = 0; // Not needed
                     $trialDays = $defaultTrialPlan->trial_days && $defaultTrialPlan->trial_days > 0 ? $defaultTrialPlan->trial_days : 14;
                     $trialExpireDate = now()->addDays($trialDays)->toDateString();
                     $planExpireDate = now()->addDays($trialDays)->toDateString(); // Set plan expiration

                     \Log::info("User given default trial plan", [
                         'plan_id' => 3,
                         'plan_name' => $defaultTrialPlan->name,
                         'trial_days' => $trialDays,
                         'selected_plan' => $selectedPlan,
                         'trial_plan' => $trialPlan,
                         'plan_expire_date' => $planExpireDate
                     ]);
                 }
             }

             $user = User::create([
                 'name' => $request->name,
                 'email' => $request->email,
                 'password' => Hash::make($request->password),
                 'type' => 'company',
                 'default_pipeline' => 1,
                 'plan' => $selectedPlan,
                 'plan_expire_date' => $planExpireDate, // Set plan expiration
                 'trial_plan' => $trialPlan,
                 'trial_expire_date' => $trialExpireDate,
                 'requested_plan' => $requestedPlan,
                 'lang' => Utility::getValByName('default_language'),
                 'avatar' => '',
                 'referral_code'=> $code,
                 'used_referral_code'=>$request->ref_code,
                 'created_by' => 1,
                 'registration_ip' => $request->ip(),
                 'user_agent' => $request->userAgent(),
             ]);

             \Log::info("New user registration", [
                 'user_id' => $user->id,
                 'email' => $user->email,
                 'plan' => $selectedPlan,
                 'trial_plan' => $trialPlan,
                 'trial_expire_date' => $trialExpireDate,
                 'ip' => $request->ip(),
                 'user_agent' => $request->userAgent()
             ]);

             \Auth::login($user);

             $settings = Utility::settings();

             if ($settings['email_verification'] == 'on') {
                 try {
                     Utility::smtpDetail(1);

                     // event(new Registered($user));
                     $user->sendEmailVerificationNotification();

                     $role_r = Role::findByName('company');
                     $user->assignRole($role_r);

                     // Clone all defaults from template company
                     \Log::info("Public Registration (Email Verify): About to call cloneCompanyDefaults for user: " . $user->id);
                     $user->cloneCompanyDefaults($user->id);

                     // ADD THIS: Set company name from registration form
                     if ($request->has('company_name') && !empty($request->company_name)) {
                         DB::table('settings')->insert([
                             'name' => 'company_name',
                             'value' => $request->company_name,
                             'created_by' => $user->id,
                             'created_at' => now(),
                             'updated_at' => now()
                         ]);

                         \Log::info("Set company name '{$request->company_name}' for registered user: {$user->id}");
                     }
                     \Log::info("Public Registration (Email Verify): Finished calling cloneCompanyDefaults for user: " . $user->id);

                 } catch (\Exception $e) {
                     \Log::error("Registration email verification failed", [
                         'user_id' => $user->id,
                         'email' => $user->email,
                         'error' => $e->getMessage()
                     ]);

                     $user->delete();
                     return redirect()->back()->with('status', __('Email SMTP settings does not configure so please contact to your site admin.'));
                 }

                 // Check if this was a purchase request (no trial parameter) AND plan costs money
                 $isTrialRequest = isset($request->trial) && $request->trial == 'true';

                 if (!$isTrialRequest && isset($request->plan) && !empty($request->plan)) {
                     try {
                         $planId = Crypt::decrypt($request->plan);
                         $plan = Plan::find($planId);

                         if ($plan && $plan->price > 0) {
                             $encryptedPlanId = Crypt::encrypt($planId);
                             \Log::info("Redirecting user to payment after email verification", [
                                 'user_id' => $user->id,
                                 'plan_id' => $planId,
                                 'plan_price' => $plan->price
                             ]);
                             return redirect()->route('stripe', $encryptedPlanId);
                         }
                     } catch (\Exception $e) {
                         \Log::warning("Plan decryption failed", ['error' => $e->getMessage()]);
                     }
                 }

                 // For email verification flow, go to dashboard (trial users or free plans)
                 return redirect(RouteServiceProvider::HOME);

             } else {
                 $user->email_verified_at = date('Y-m-d H:i:s'); // Fixed timestamp format
                 $user->save();
                 $role_r = Role::findByName('company');
                 $user->assignRole($role_r);

                 // Clone all defaults from template company
                 \Log::info("Public Registration (No Email Verify): About to call cloneCompanyDefaults for user: " . $user->id);
                 $user->cloneCompanyDefaults($user->id);

                 // SET COMPANY NAME - Fixed placement for no email verification path
                 if ($request->has('company_name') && !empty($request->company_name)) {
                     DB::table('settings')->insert([
                         'name' => 'company_name',
                         'value' => $request->company_name,
                         'created_by' => $user->id,
                         'created_at' => now(),
                         'updated_at' => now()
                     ]);

                     \Log::info("Set company name '{$request->company_name}' for registered user: {$user->id}");
                 }

                 \Log::info("Public Registration (No Email Verify): Finished calling cloneCompanyDefaults for user: " . $user->id);

                 // Send welcome email (fix the method name)
                 $userArr = [
                     'email' => $user->email,
                     'password' => $request->password, // Use plain password for email
                 ];

                 try {
                     $resp = Utility::sendEmailTemplate('new_user', [$user->id => $user->email], $userArr);
                 } catch (\Exception $e) {
                     \Log::warning("Failed to send welcome email", [
                         'user_id' => $user->id,
                         'error' => $e->getMessage()
                     ]);
                     // Continue registration even if email fails
                 }

                 // Check if this was a purchase request (no trial parameter) AND plan costs money
                 $isTrialRequest = isset($request->trial) && $request->trial == 'true';

                 if (!$isTrialRequest && isset($request->plan) && !empty($request->plan)) {
                     try {
                         $planId = Crypt::decrypt($request->plan);
                         $plan = Plan::find($planId);

                         if ($plan && $plan->price > 0) {
                             $encryptedPlanId = Crypt::encrypt($planId);
                             \Log::info("Redirecting user to payment (no email verification)", [
                                 'user_id' => $user->id,
                                 'plan_id' => $planId,
                                 'plan_price' => $plan->price
                             ]);
                             return redirect()->route('stripe', $encryptedPlanId);
                         }
                     } catch (\Exception $e) {
                         \Log::warning("Plan decryption failed", ['error' => $e->getMessage()]);
                     }
                 }

                 // For all other cases (trial users or free plans), go to dashboard
                 return redirect(RouteServiceProvider::HOME);
             }
         }





         public function showRegistrationForm(Request $request, $ref = '' , $lang = '')
         {
             $settings = Utility::settings();

             if($settings['enable_signup'] == 'on')
             {
                 $langList = Utility::languages()->toArray();
                 $lang = array_key_exists($lang, $langList) ? $lang : 'en';

                 if($lang == '')
                 {
                     $lang = Utility::getValByName('default_language');
                 }
                 \App::setLocale($lang);
                 if($ref == '')
                 {
                     $ref = 0;
                 }

                 $refCode = User::where('referral_code' , '=', $ref)->first();
                 if(isset($refCode) && $refCode->referral_code != $ref)
                 {
                     return redirect()->route('register');
                 }

                 $plan = null;
                 if($request->plan){
                     $plan = $request->plan;
                 }
                 return view('auth.register', compact('lang' , 'ref', 'plan'));
             }
             else
             {
                 return \Redirect::to('login');
             }
         }


}
