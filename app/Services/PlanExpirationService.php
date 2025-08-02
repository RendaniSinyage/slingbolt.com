<?php
// app/Services/PlanExpirationService.php (NEW - Synchronous service, no queue)

namespace App\Services;

use App\Models\User;
use App\Models\Utility;
use App\Models\EmailTemplate;
use App\Models\EmailSendLog;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class PlanExpirationService
{
    public function processUser(User $user)
    {
        $today = Carbon::today();

        // Check trial expiration
        if ($user->trial_expire_date) {
            $trialExpiry = Carbon::parse($user->trial_expire_date);

            if ($trialExpiry->lt($today) && $user->trial_plan > 0) {
                // Trial expired - downgrade and send email
                $this->expireTrial($user);

            } elseif ($trialExpiry->diffInDays($today) <= 3 && $trialExpiry->gte($today)) {
                // Trial expiring in 3 days or less
                $this->sendTrialExpiringEmail($user, $trialExpiry->diffInDays($today));
            }
        }

        // Check paid plan expiration
        if ($user->plan_expire_date) {
            $planExpiry = Carbon::parse($user->plan_expire_date);

            if ($planExpiry->lt($today) && $user->plan > 1) {
                // Paid plan expired - downgrade and send email
                $this->expirePlan($user);

            } elseif ($planExpiry->diffInDays($today) <= 7 && $planExpiry->gte($today)) {
                // Plan expiring in 7 days or less
                $this->sendPlanExpiringEmail($user, $planExpiry->diffInDays($today));
            }
        }
    }

    private function isTemplateEnabled($templateSlug)
    {
        $template = EmailTemplate::where('slug', $templateSlug)->first();
        return $template && $template->is_enabled;
    }

    private function expireTrial($user)
    {
        Log::info("Expiring trial for user: {$user->email}");

        // Downgrade to free plan
        $user->plan = 1;
        $user->trial_plan = 0;
        $user->trial_expire_date = null;
        $user->save();

        // Send trial expired email (only once)
        if (!EmailSendLog::wasEmailSent($user->id, 'trial_expired')) {
            $this->sendEmail($user, 'trial_expired', [
                'user_name' => $user->name,
                'user_email' => $user->email,
                'trial_days' => 'trial period'
            ]);
        } else {
            Log::info("Trial expired email already sent to user: {$user->email}");
        }
    }

    private function expirePlan($user)
    {
        Log::info("Expiring plan for user: {$user->email}");

        // Downgrade to free plan
        $user->plan = 1;
        $user->plan_expire_date = null;
        $user->save();

        // Send plan expired email (only once)
        if (!EmailSendLog::wasEmailSent($user->id, 'plan_expired')) {
            $this->sendEmail($user, 'plan_expired', [
                'user_name' => $user->name,
                'user_email' => $user->email,
                'plan_name' => 'subscription'
            ]);
        } else {
            Log::info("Plan expired email already sent to user: {$user->email}");
        }
    }

    private function sendTrialExpiringEmail($user, $daysLeft)
    {
        // Create unique template slug for each day to allow multiple reminders
        $templateSlug = "trial_expiring_day_{$daysLeft}";
        
        // Check if we already sent this specific reminder
        if (!EmailSendLog::wasEmailSent($user->id, $templateSlug)) {
            $this->sendEmail($user, 'trial_expiring', [
                'user_name' => $user->name,
                'user_email' => $user->email,
                'days_left' => $daysLeft,
                'expiry_date' => Carbon::parse($user->trial_expire_date)->format('M d, Y')
            ], $templateSlug); // Use unique slug for tracking
        } else {
            Log::info("Trial expiring ({$daysLeft} days) email already sent to user: {$user->email}");
        }
    }

    private function sendPlanExpiringEmail($user, $daysLeft)
    {
        // Create unique template slug for each day to allow multiple reminders
        $templateSlug = "plan_expiring_day_{$daysLeft}";
        
        // Check if we already sent this specific reminder
        if (!EmailSendLog::wasEmailSent($user->id, $templateSlug)) {
            $this->sendEmail($user, 'plan_expiring', [
                'user_name' => $user->name,
                'user_email' => $user->email,
                'days_left' => $daysLeft,
                'expiry_date' => Carbon::parse($user->plan_expire_date)->format('M d, Y')
            ], $templateSlug); // Use unique slug for tracking
        } else {
            Log::info("Plan expiring ({$daysLeft} days) email already sent to user: {$user->email}");
        }
    }

    private function sendEmail($user, $templateSlug, $variables, $trackingSlug = null)
    {
        $trackingSlug = $trackingSlug ?: $templateSlug;
        
        try {
            // Check if template is enabled
            if (!$this->isTemplateEnabled($templateSlug)) {
                Log::info("Template disabled, skipping email", [
                    'template' => $templateSlug,
                    'user_email' => $user->email
                ]);
                return;
            }

            // Send the email using existing Utility method
            $resp = Utility::sendEmailTemplate($templateSlug, [$user->id => $user->email], $variables);

            // Check if email sending was successful
            if (!empty($resp) && $resp['is_success'] == false && !empty($resp['error'])) {
                EmailSendLog::logFailure($user->id, $trackingSlug, $user->email, $resp['error'], $variables);
                Log::error("Email failed", [
                    'template' => $templateSlug,
                    'user_email' => $user->email,
                    'error' => $resp['error']
                ]);
                return;
            }

            // Log successful send
            EmailSendLog::logSuccess($user->id, $trackingSlug, $user->email, $variables);
            
            Log::info("Email sent successfully", [
                'template' => $templateSlug,
                'tracking_slug' => $trackingSlug,
                'user_id' => $user->id,
                'user_email' => $user->email
            ]);

        } catch (\Exception $e) {
            EmailSendLog::logFailure($user->id, $trackingSlug, $user->email, $e->getMessage(), $variables);
            
            Log::error("Failed to send email", [
                'template' => $templateSlug,
                'user_email' => $user->email,
                'error' => $e->getMessage()
            ]);
        }
    }
}