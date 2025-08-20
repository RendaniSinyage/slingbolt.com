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

class RegisterController extends Controller
{
    use ApiResponser;

    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'company_name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'plan_id' => 'nullable|integer|exists:plans,id',
        ]);

        if ($validator->fails()) {
            return $this->error('Validation failed.', 422, $validator->errors());
        }

        // --- Plan Selection & Trial Logic ---
        $selectedPlanId = 1; // Default free plan
        $trialPlanId = 0;
        $trialExpireDate = null;
        $planExpireDate = null;
        $paymentRequired = false;

        $plan = Plan::find($request->plan_id);

        if ($plan && $plan->is_disable == 1) {
            $selectedPlanId = $plan->id;
            // If plan has a price, it's a trial. If not, it's a free plan.
            if($plan->price > 0) {
                $trialPlanId = $plan->id;
                $trialDays = $plan->trial_days > 0 ? $plan->trial_days : 14;
                $trialExpireDate = now()->addDays($trialDays)->toDateString();
                $planExpireDate = $trialExpireDate;
            }
        } else {
            // Default to trial of plan 3 if no valid plan is provided
            $defaultTrialPlan = Plan::find(3);
            if ($defaultTrialPlan && $defaultTrialPlan->is_disable == 1) {
                $selectedPlanId = $defaultTrialPlan->id;
                $trialPlanId = $defaultTrialPlan->id;
                $trialDays = $defaultTrialPlan->trial_days > 0 ? $defaultTrialPlan->trial_days : 14;
                $trialExpireDate = now()->addDays($trialDays)->toDateString();
                $planExpireDate = $trialExpireDate;
            }
        }

        // --- User Creation ---
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
            'created_by' => 1, // Super admin
            'registration_ip' => $request->ip(),
        ]);

        // --- Post-Registration Actions ---
        $role_r = Role::findByName('company');
        $user->assignRole($role_r);

        $user->cloneCompanyDefaults($user->id);

        DB::table('settings')->insert([
            'name' => 'company_name',
            'value' => $request->company_name,
            'created_by' => $user->id,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        $settings = Utility::settings();
        if ($settings['email_verification'] == 'on') {
            try {
                $user->sendEmailVerificationNotification();
            } catch (\Exception $e) {
                // Log error, but don't block registration
            }
        }

        // --- Token and Response ---
        $token = $user->createToken('API Token')->plainTextToken;

        // Check if payment is needed (plan exists and has a price)
        if ($plan && $plan->price > 0) {
            $paymentRequired = true;
        }

        return $this->success([
            'user' => $user->fresh(),
            'token' => $token,
            'payment_required' => $paymentRequired,
            'plan' => $plan,
        ], 'Registration successful.');
    }
}
