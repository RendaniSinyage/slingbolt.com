<?php

namespace App\Services;

use App\Models\User;
use App\Models\Utility;
use App\Models\EmailTemplate;
use App\Models\EmailSendLog;
use App\Services\ConsoleEmailService; // Add this import
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class TrialEngagementService
{
    public function processUser(User $user)
    {
        // Only process users who are currently on trial
        if (!$user->trial_plan || !$user->trial_expire_date) {
            return;
        }

        $emailVerified = $user->email_verified_at ? Carbon::parse($user->email_verified_at) : null;
        $lastLogin = $user->last_login_at ? Carbon::parse($user->last_login_at) : null;
        $trialExpiry = Carbon::parse($user->trial_expire_date);
        $today = Carbon::today();

        if (!$emailVerified) {
            return; // Skip if no verified date
        }

        $daysSinceVerification = $emailVerified->diffInDays($today);
        $daysSinceLastLogin = $lastLogin ? $lastLogin->diffInDays($today) : null;

        // Different engagement scenarios
        $this->checkWelcomeSequence($user, $daysSinceVerification);
        $this->checkInactiveUser($user, $daysSinceLastLogin, $daysSinceVerification);
        $this->checkEducationalEmails($user, $daysSinceVerification);
    }

    private function isTemplateEnabled($templateSlug)
    {
        $template = EmailTemplate::where('slug', $templateSlug)->first();
        return $template && $template->is_enabled;
    }

    private function checkWelcomeSequence($user, $daysSinceVerification)
    {
        // Day 1: Welcome email (usually sent during registration)
        if ($daysSinceVerification == 1) {
            $this->sendEmail($user, 'trial_welcome_day1', [
                'user_name' => $user->name,
                'trial_days' => $this->getTrialDaysRemaining($user)
            ]);
        }

        // Day 3: Getting started tips
        if ($daysSinceVerification == 3) {
            $this->sendEmail($user, 'trial_getting_started', [
                'user_name' => $user->name,
                'trial_days' => $this->getTrialDaysRemaining($user)
            ]);
        }
    }

    private function checkInactiveUser($user, $daysSinceLastLogin, $daysSinceVerification)
    {
        // User hasn't logged in for 5+ days during trial
        if ($daysSinceLastLogin >= 5 && $daysSinceVerification >= 5) {
            $this->sendEmail($user, 'trial_inactive_reminder', [
                'user_name' => $user->name,
                'days_inactive' => $daysSinceLastLogin,
                'trial_days' => $this->getTrialDaysRemaining($user)
            ]);
        }

        // User registered but never logged in (after 2 days)
        if (!$user->last_login_at && $daysSinceVerification >= 2) {
            $this->sendEmail($user, 'trial_never_logged_in', [
                'user_name' => $user->name,
                'trial_days' => $this->getTrialDaysRemaining($user)
            ]);
        }
    }

    private function checkEducationalEmails($user, $daysSinceVerification)
    {
        // Educational email sequence during trial
        switch ($daysSinceVerification) {
            case 5:
                $this->sendEmail($user, 'trial_education_features', [
                    'user_name' => $user->name,
                    'trial_days' => $this->getTrialDaysRemaining($user)
                ]);
                break;

            case 7:
                $this->sendEmail($user, 'trial_education_tips', [
                    'user_name' => $user->name,
                    'trial_days' => $this->getTrialDaysRemaining($user)
                ]);
                break;

            case 10:
                $this->sendEmail($user, 'trial_education_advanced', [
                    'user_name' => $user->name,
                    'trial_days' => $this->getTrialDaysRemaining($user)
                ]);
                break;
        }
    }

    private function getTrialDaysRemaining($user)
    {
        if (!$user->trial_expire_date) {
            return 0;
        }

        $expiry = Carbon::parse($user->trial_expire_date);
        $today = Carbon::today();

        return max(0, $expiry->diffInDays($today, false));
    }

    private function sendEmail($user, $templateSlug, $variables)
    {
        try {
            // Check if this email was already sent to avoid duplicates
            if (EmailSendLog::wasEmailSent($user->id, $templateSlug)) {
                Log::info("Email already sent, skipping", [
                    'template' => $templateSlug,
                    'user_email' => $user->email
                ]);
                return;
            }

            // Check if template is enabled
            if (!$this->isTemplateEnabled($templateSlug)) {
                Log::info("Template disabled, skipping email", [
                    'template' => $templateSlug,
                    'user_email' => $user->email
                ]);
                return;
            }

            Log::info("About to send engagement email", [
                'template' => $templateSlug,
                'user_id' => $user->id,
                'user_email' => $user->email,
                'context' => app()->runningInConsole() ? 'console' : 'web'
            ]);

            // CHANGED: Use ConsoleEmailService instead of Utility::sendEmailTemplate
            $resp = ConsoleEmailService::sendEmailTemplate($templateSlug, [$user->id => $user->email], $variables);

            // Check if email sending was successful
            if (!empty($resp) && $resp['is_success'] == false && !empty($resp['error'])) {
                EmailSendLog::logFailure($user->id, $templateSlug, $user->email, $resp['error'], $variables);
                Log::error("Engagement email failed", [
                    'template' => $templateSlug,
                    'user_email' => $user->email,
                    'error' => $resp['error']
                ]);
                return;
            }

            // Log successful send
            EmailSendLog::logSuccess($user->id, $templateSlug, $user->email, $variables);

            Log::info("Sent engagement email", [
                'template' => $templateSlug,
                'user_id' => $user->id,
                'user_email' => $user->email
            ]);

        } catch (\Exception $e) {
            EmailSendLog::logFailure($user->id, $templateSlug, $user->email, $e->getMessage(), $variables);
            
            Log::error("Failed to send engagement email", [
                'template' => $templateSlug,
                'user_email' => $user->email,
                'error' => $e->getMessage()
            ]);
        }
    }
}