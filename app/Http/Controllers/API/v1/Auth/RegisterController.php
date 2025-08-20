<?php

namespace App\Http\Controllers\API\v1\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Plan;
use App\Models\Utility;
use App\Traits\ApiResponser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use Illuminate\Validation\Rules;

class RegisterController extends Controller
{
    use ApiResponser;

    public function store(Request $request)
    {
        // Rate limiting checks (simplified for API)
        $userEmail = $request->email;
        $recentEmailAttempts = User::where('email', $userEmail)->where('created_at', '>', now()->subHour())->count();
        if ($recentEmailAttempts >= 1) {
            return $this->error('A verification email was already sent to this address. Please check your email.', 429);
        }

        $userIP = $request->ip();
        $recentRegistrations = User::where('registration_ip', $userIP)->where('created_at', '>', now()->subHour())->count();
        if ($recentRegistrations >= 3) {
            return $this->error('Too many registration attempts. Please try again later.', 429);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => ['required', 'string', 'min:8', 'confirmed', Rules\Password::defaults()],
            'company_name' => 'required|string|max:255',
            'plan_id' => 'nullable|integer|exists:plans,id',
        ]);

        if ($validator->fails()) {
            return $this->error('Validation failed.', 422, $validator->errors());
        }

        // Plan selection and trial assignment
        $selectedPlanId = 1;
        $trialPlanId = 0;
        $trialExpireDate = null;
        $planExpireDate = null;
        $plan = null;

        if ($request->has('plan_id') && !empty($request->plan_id)) {
            $plan = Plan::find($request->plan_id);
            if ($plan && $plan->is_disable == 1) { // Assuming is_disable=1 means enabled
                $selectedPlanId = $plan->id;
                if ($plan->price > 0) {
                    $trialPlanId = $plan->id;
                    $trialDays = $plan->trial_days > 0 ? $plan->trial_days : 14;
                    $trialExpireDate = now()->addDays($trialDays)->toDateString();
                    $planExpireDate = $trialExpireDate;
                }
            }
        } else {
            $defaultTrialPlan = Plan::find(3);
            if ($defaultTrialPlan && $defaultTrialPlan->is_disable == 1) {
                $plan = $defaultTrialPlan;
                $selectedPlanId = $plan->id;
                $trialPlanId = $plan->id;
                $trialDays = $plan->trial_days > 0 ? $plan->trial_days : 14;
                $trialExpireDate = now()->addDays($trialDays)->toDateString();
                $planExpireDate = $trialExpireDate;
            }
        }

        do {
            $code = rand(100000, 999999);
        } while (User::where('referral_code', $code)->exists());

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'type' => 'company',
            'plan' => $selectedPlanId,
            'plan_expire_date' => $planExpireDate,
            'trial_plan' => $trialPlanId,
            'trial_expire_date' => $trialExpireDate,
            'lang' => Utility::getValByName('default_language') ?? 'en',
            'created_by' => 1,
            'registration_ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'referral_code'=> $code,
        ]);

        $role_r = Role::findByName('company');
        $user->assignRole($role_r);
        $user->cloneCompanyDefaults($user->id);

        DB::table('settings')->updateOrInsert(
            ['name' => 'company_name', 'created_by' => $user->id],
            ['value' => $request->company_name, 'created_at' => now(), 'updated_at' => now()]
        );

        $settings = Utility::settings();
        if (isset($settings['email_verification']) && $settings['email_verification'] == 'on') {
            try {
                $user->sendEmailVerificationNotification();
            } catch (\Exception $e) {
                // Do not block registration, but log the error
                \Log::error('API Registration: SMTP email sending failed.', ['error' => $e->getMessage()]);
            }
        } else {
            $user->email_verified_at = now();
            $user->save();
        }

        $token = $user->createToken('API Token')->plainTextToken;
        $paymentRequired = ($plan && $plan->price > 0);

        return $this->success([
            'user' => $user->fresh(),
            'token' => $token,
            'payment_required' => $paymentRequired,
            'plan_details' => $plan,
        ], 'Registration successful.');
    }
}
