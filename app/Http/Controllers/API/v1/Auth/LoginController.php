<?php

namespace App\Http\Controllers\API\v1\Auth;

use App\Http\Controllers\Controller;
use App\Traits\ApiResponser;
use App\Models\Utility;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    use ApiResponser;

    public function login(Request $request)
    {
        $attr = $request->validate([
            'email' => 'required|string|email|',
            'password' => 'required|string'
        ]);

        if (!Auth::attempt($attr)) {
            return $this->error('Credentials not match', 401);
        }

        $user = auth()->user();
        $settings = Utility::settings($user->id);

        $user_settings = [
            'shot_time' => isset($settings['interval_time']) ? $settings['interval_time'] : 0.5,
        ];

        return $this->success([
            'token' => $user->createToken('API Token')->plainTextToken,
            'user_id' => $user->id,
            'user_name' => $user->name,
            'settings' => $user_settings,
        ], 'Login successfully.');
    }

    public function logout()
    {
        auth()->user()->tokens()->delete();
        return $this->success([], 'Tokens Revoked');
    }
}
