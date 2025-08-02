<?php
// app/Services/PlanExpirationService.php (COMPLETE - Fixed template names)

namespace App\Services;

use App\Models\User;
use App\Models\Utility;
use App\Models\EmailTemplate;
use App\Models\EmailTemplateLang;
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

    private function isTemplateEnabled($templateName)
    {
        $template = EmailTemplate::where('name', $templateName)->first();
        return $template && $template->is_enabled;
    }

    private function templateHasContent($templateName, $lang = 'en')
    {
        $template = EmailTemplate::where('name', $templateName)->first();
        if (!$template) {
            Log::warning("Email template not found", ['template' => $templateName]);
            return false;
        }

        $templateLang = EmailTemplateLang::where('parent_id', $template->id)
                                        ->where('lang', $lang)
                                        ->first();
        
        if (!$templateLang) {
            Log::warning("Email template language content not found", [
                'template' => $templateName,
                'lang' => $lang,
                'template_id' => $template->id
            ]);
            return false;
        }

        return true;
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
            $this->sendEmail($user, 'Trial Expired', [
                'user_name' => $user->name,
                'user_email' => $user->email,
                'trial_days' => 'trial period'
            ], 'trial_expired');
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
            $this->sendEmail($user, 'Plan Expired', [
                'user_name' => $user->name,
                'user_email' => $user->email,
                'plan_name' => 'subscription'
            ], 'plan_expired');
        } else {
            Log::info("Plan expired email already sent to user: {$user->email}");
        }
    }

    private function sendTrialExpiringEmail($user, $daysLeft)
    {
        // Create unique template slug for each day to allow multiple reminders
        $trackingSlug = "trial_expiring_day_{$daysLeft}";
        
        // Check if we already sent this specific reminder
        if (!EmailSendLog::wasEmailSent($user->id, $trackingSlug)) {
            $this->sendEmail($user, 'Trial Expiring', [
                'user_name' => $user->name,
                'user_email' => $user->email,
                'days_left' => $daysLeft,
                'expiry_date' => Carbon::parse($user->trial_expire_date)->format('M d, Y')
            ], $trackingSlug);
        } else {
            Log::info("Trial expiring ({$daysLeft} days) email already sent to user: {$user->email}");
        }
    }

    private function sendPlanExpiringEmail($user, $daysLeft)
    {
        // Create unique template slug for each day to allow multiple reminders
        $trackingSlug = "plan_expiring_day_{$daysLeft}";
        
        // Check if we already sent this specific reminder
        if (!EmailSendLog::wasEmailSent($user->id, $trackingSlug)) {
            $this->sendEmail($user, 'Plan Expiring', [
                'user_name' => $user->name,
                'user_email' => $user->email,
                'days_left' => $daysLeft,
                'expiry_date' => Carbon::parse($user->plan_expire_date)->format('M d, Y')
            ], $trackingSlug);
        } else {
            Log::info("Plan expiring ({$daysLeft} days) email already sent to user: {$user->email}");
        }
    }

    private function sendEmail($user, $templateName, $variables, $trackingSlug = null)
    {
        $trackingSlug = $trackingSlug ?: strtolower(str_replace(' ', '_', $templateName));
        
        try {
            // Check if template is enabled
            if (!$this->isTemplateEnabled($templateName)) {
                Log::info("Template disabled, skipping email", [
                    'template' => $templateName,
                    'user_email' => $user->email
                ]);
                return;
            }

            // Check if template has content
            if (!$this->templateHasContent($templateName)) {
                Log::error("Template missing content, skipping email", [
                    'template' => $templateName,
                    'user_email' => $user->email
                ]);
                EmailSendLog::logFailure($user->id, $trackingSlug, $user->email, 'Template missing content', $variables);
                return;
            }

            // Additional debugging
            Log::info("About to send email", [
                'template' => $templateName,
                'user_id' => $user->id,
                'user_email' => $user->email,
                'user_type' => $user->type ?? 'null',
                'tracking_slug' => $trackingSlug,
                'variables' => $variables
            ]);

            // Send the email using existing Utility method (using template NAME not slug)
            $resp = Utility::sendEmailTemplate($templateName, [$user->id => $user->email], $variables);

            // Check if email sending was successful
            if (!empty($resp) && $resp['is_success'] == false && !empty($resp['error'])) {
                EmailSendLog::logFailure($user->id, $trackingSlug, $user->email, $resp['error'], $variables);
                Log::error("Email failed", [
                    'template' => $templateName,
                    'user_email' => $user->email,
                    'error' => $resp['error']
                ]);
                return;
            }

            // Log successful send
            EmailSendLog::logSuccess($user->id, $trackingSlug, $user->email, $variables);
            
            Log::info("Email sent successfully", [
                'template' => $templateName,
                'tracking_slug' => $trackingSlug,
                'user_id' => $user->id,
                'user_email' => $user->email
            ]);

        } catch (\Exception $e) {
            EmailSendLog::logFailure($user->id, $trackingSlug, $user->email, $e->getMessage(), $variables);
            
            Log::error("Failed to send email", [
                'template' => $templateName,
                'user_email' => $user->email,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }
}