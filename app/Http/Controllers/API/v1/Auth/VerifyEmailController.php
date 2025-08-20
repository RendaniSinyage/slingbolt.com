<?php

namespace App\Http\Controllers\API\v1\Auth;

use App\Http\Controllers\Controller;
use App\Traits\ApiResponser;
use Illuminate\Auth\Events\Verified;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\Request;

class VerifyEmailController extends Controller
{
    use ApiResponser;

    /**
     * Mark the authenticated user's email address as verified.
     *
     * @param  \Illuminate\Foundation\Auth\EmailVerificationRequest  $request
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function __invoke(EmailVerificationRequest $request)
    {
        if ($request->user()->hasVerifiedEmail()) {
            // If a redirect URL is provided, use it. Otherwise, return JSON.
            if ($request->has('redirect_url')) {
                return redirect()->to($request->redirect_url . '?verified=true');
            }
            return $this->success(null, 'Email already verified.');
        }

        if ($request->user()->markEmailAsVerified()) {
            event(new Verified($request->user()));
        }

        // If a redirect URL is provided, use it. Otherwise, return JSON.
        if ($request->has('redirect_url')) {
            return redirect()->to($request->redirect_url . '?verified=true');
        }

        return $this->success(null, 'Email successfully verified.');
    }
}
