<?php

namespace App\Http\Controllers\API\v1\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Traits\ApiResponser;
use App\Models\Utility;

class EmailVerificationNotificationController extends Controller
{
    use ApiResponser;

    /**
     * Send a new email verification notification.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        if ($request->user()->hasVerifiedEmail()) {
            return $this->success(null, 'Email already verified.');
        }

        try {
            // Ensure SMTP details are configured before attempting to send.
            $settings = Utility::settings();
            if (
                isset($settings['mail_driver']) &&
                isset($settings['mail_host']) &&
                isset($settings['mail_port']) &&
                isset($settings['mail_username']) &&
                isset($settings['mail_password'])
            ) {
                 config([
                    'mail.driver'       => $settings['mail_driver'],
                    'mail.host'         => $settings['mail_host'],
                    'mail.port'         => $settings['mail_port'],
                    'mail.encryption'   => $settings['mail_encryption'],
                    'mail.username'     => $settings['mail_username'],
                    'mail.password'     => $settings['mail_password'],
                    'mail.from.address' => $settings['mail_from_address'],
                    'mail.from.name'    => $settings['mail_from_name'],
                ]);
            }

            $request->user()->sendEmailVerificationNotification();
        } catch (\Exception $e) {
            \Log::error('API Resend Verification: SMTP email sending failed.', ['error' => $e->getMessage()]);
            return $this->error('Failed to send verification email due to a server error.', 500);
        }

        return $this->success(null, 'A fresh verification link has been sent to your email address.');
    }
}
