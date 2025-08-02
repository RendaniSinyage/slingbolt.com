<?php
// app/Jobs/ProcessTrialEngagement.php (UPDATED with is_enabled check)

namespace App\Jobs;

use App\Models\User;
use App\Models\Utility;
use App\Models\EmailTemplate;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class ProcessTrialEngagement implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $user;

    public function __construct(User $user)
    {
        $this->user = $user;
    }

    public function handle()
    {
        $user = $this->user;

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

    private function sendEmail($user, $template, $variables)
    {
        try {
            // Check if template is enabled
            if (!$this->isTemplateEnabled($template)) {
                \Log::info("Template disabled, skipping email", [
                    'template' => $template,
                    'user_email' => $user->email
                ]);
                return;
            }

            // Check if this email was already sent to avoid duplicates
            $sentKey = "email_sent_{$template}_{$user->id}";
            if (cache()->has($sentKey)) {
                return;
            }

            // Send the email using existing Utility method
            $resp = Utility::sendEmailTemplate($template, [$user->id => $user->email], $variables);

            // Check if email sending was successful
            if (!empty($resp) && $resp['is_success'] == false && !empty($resp['error'])) {
                \Log::error("Engagement email failed", [
                    'template' => $template,
                    'user_email' => $user->email,
                    'error' => $resp['error']
                ]);
                return;
            }

            // Mark as sent (cache for 30 days)
            cache()->put($sentKey, true, now()->addDays(30));

            \Log::info("Sent engagement email", [
                'template' => $template,
                'user_id' => $user->id,
                'user_email' => $user->email
            ]);

        } catch (\Exception $e) {
            \Log::error("Failed to send engagement email", [
                'template' => $template,
                'user_email' => $user->email,
                'error' => $e->getMessage()
            ]);
        }
    }
}
