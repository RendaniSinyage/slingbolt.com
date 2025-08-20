<?php

namespace App\Http\Controllers\API\v1\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Validator;
use App\Traits\ApiResponser;
use App\Models\Utility;

class ForgotPasswordController extends Controller
{
    use ApiResponser;

    public function sendResetLinkEmail(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:users',
        ]);

        if ($validator->fails()) {
            return $this->error('Validation failed.', 422, $validator->errors());
        }

        try {
            Utility::smtpDetail(1);
            $status = Password::sendResetLink($request->only('email'));

            if ($status == Password::RESET_LINK_SENT) {
                return $this->success(null, __($status));
            } else {
                return $this->error(__($status), 400);
            }
        } catch (\Exception $e) {
            \Log::error('API Forgot Password: SMTP email sending failed.', ['error' => $e->getMessage()]);
            return $this->error('E-Mail could not be sent due to a server error.', 500);
        }
    }
}
