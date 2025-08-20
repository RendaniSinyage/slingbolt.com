<?php

namespace App\Http\Controllers\API\v1\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Plan;
use App\Models\Utility;
use App\Traits\ApiResponser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class LoginController extends Controller
{
    use ApiResponser;

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|string|email',
            'password' => 'required|string'
        ]);

        if ($validator->fails()) {
            return $this->error('Validation failed.', 422, $validator->errors());
        }

        $user = User::where('email', $request->email)->first();

        // Pre-authentication checks from web controller
        if (!$user || !Hash::check($request->password, $user->password)) {
            return $this->error('Credentials do not match.', 401);
        }

        $companyUser = User::where('id', $user->created_by)->first();

        if ($user->is_disable == 0 && $user->type != 'company' && $user->type != 'super admin') {
            return $this->error('Your Account is disabled, please contact your Administrator.', 403);
        }

        if (($user->is_enable_login == 0 || ($companyUser && $companyUser->is_enable_login == 0)) && $user->type != 'super admin') {
            return $this->error('Your Account is disabled by your company.', 403);
        }

        if ($user->delete_status != 1 || ($companyUser && $companyUser->delete_status != 1)) {
            return $this->error('Your Account is deleted, please contact your Administrator.', 403);
        }

        if ($user->is_active == 0) {
            return $this->error('Your Account is deactive, please contact your Administrator.', 403);
        }

        Auth::login($user);
        $authedUser = Auth::user();

        // Email verification check
        $settings = Utility::settings();
        if (isset($settings['email_verification']) && $settings['email_verification'] == 'on') {
            if (!$authedUser->hasVerifiedEmail()) {
                return $this->error('Email not verified.', 403, ['email_not_verified' => true]);
            }
        }

        // Post-login checks
        if ($authedUser->type == 'company') {
            $plan = Plan::find($authedUser->plan);
            if ($plan && $plan->duration != 'lifetime' && $authedUser->plan_expire_date < now()) {
                $authedUser->assignPlan(1); // Assign default plan
                return $this->error('Your plan has expired.', 402, ['plan_expired' => true]);
            }
            if ($authedUser->trial_plan > 0 && $authedUser->trial_expire_date < now()) {
                $authedUser->assignPlan(1); // Assign default plan
                return $this->error('Your trial plan has expired.', 402, ['trial_expired' => true]);
            }
        }

        // Update last login
        $authedUser->last_login_at = now();
        $authedUser->last_login_ip = $request->ip();
        $authedUser->save();

        $token = $authedUser->createToken('API Token')->plainTextToken;

        return $this->success([
            'user' => $authedUser->fresh(),
            'token' => $token,
        ], 'Login successful.');
    }

    public function logout()
    {
        auth()->user()->tokens()->delete();
        return $this->success([], 'Tokens Revoked');
    }
}
