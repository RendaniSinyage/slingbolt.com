<?php
// app/Jobs/ProcessPlanExpiration.php (UPDATED with is_enabled check)

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

class ProcessPlanExpiration implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $user;

    public function __construct(User $user)
    {
        $this->user = $user;
    }

    public function handle()
    {
        $today = Carbon::today();
        $user = $this->user;

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
        \Log::info("Expiring trial for user: {$user->email}");

        // Downgrade to free plan
        $user->plan = 1;
        $user->trial_plan = 0;
        $user->trial_expire_date = null;
        $user->save();

        // Send trial expired email using existing system
        if ($this->isTemplateEnabled('trial_expired')) {
            try {
                $userArr = [
                    'user_name' => $user->name,
                    'user_email' => $user->email,
                    'trial_days' => 'trial period'
                ];
                $resp = Utility::sendEmailTemplate('trial_expired', [$user->id => $user->email], $userArr);

                if (!empty($resp) && $resp['is_success'] == false && !empty($resp['error'])) {
                    \Log::error("Trial expired email failed: " . $resp['error']);
                }
            } catch (\Exception $e) {
                \Log::error("Failed to send trial expired email to {$user->email}: " . $e->getMessage());
            }
        } else {
            \Log::info("Trial expired email template disabled, skipping for user: {$user->email}");
        }
    }

    private function expirePlan($user)
    {
        \Log::info("Expiring plan for user: {$user->email}");

        // Downgrade to free plan
        $user->plan = 1;
        $user->plan_expire_date = null;
        $user->save();

        // Send plan expired email using existing system
        if ($this->isTemplateEnabled('plan_expired')) {
            try {
                $userArr = [
                    'user_name' => $user->name,
                    'user_email' => $user->email,
                    'plan_name' => 'subscription'
                ];
                $resp = Utility::sendEmailTemplate('plan_expired', [$user->id => $user->email], $userArr);

                if (!empty($resp) && $resp['is_success'] == false && !empty($resp['error'])) {
                    \Log::error("Plan expired email failed: " . $resp['error']);
                }
            } catch (\Exception $e) {
                \Log::error("Failed to send plan expired email to {$user->email}: " . $e->getMessage());
            }
        } else {
            \Log::info("Plan expired email template disabled, skipping for user: {$user->email}");
        }
    }

    private function sendTrialExpiringEmail($user, $daysLeft)
    {
        if ($this->isTemplateEnabled('trial_expiring')) {
            try {
                $userArr = [
                    'user_name' => $user->name,
                    'user_email' => $user->email,
                    'days_left' => $daysLeft,
                    'expiry_date' => Carbon::parse($user->trial_expire_date)->format('M d, Y')
                ];
                $resp = Utility::sendEmailTemplate('trial_expiring', [$user->id => $user->email], $userArr);

                if (!empty($resp) && $resp['is_success'] == false && !empty($resp['error'])) {
                    \Log::error("Trial expiring email failed: " . $resp['error']);
                }
            } catch (\Exception $e) {
                \Log::error("Failed to send trial expiring email to {$user->email}: " . $e->getMessage());
            }
        } else {
            \Log::info("Trial expiring email template disabled, skipping for user: {$user->email}");
        }
    }

    private function sendPlanExpiringEmail($user, $daysLeft)
    {
        if ($this->isTemplateEnabled('plan_expiring')) {
            try {
                $userArr = [
                    'user_name' => $user->name,
                    'user_email' => $user->email,
                    'days_left' => $daysLeft,
                    'expiry_date' => Carbon::parse($user->plan_expire_date)->format('M d, Y')
                ];
                $resp = Utility::sendEmailTemplate('plan_expiring', [$user->id => $user->email], $userArr);

                if (!empty($resp) && $resp['is_success'] == false && !empty($resp['error'])) {
                    \Log::error("Plan expiring email failed: " . $resp['error']);
                }
            } catch (\Exception $e) {
                \Log::error("Failed to send plan expiring email to {$user->email}: " . $e->getMessage());
            }
        } else {
            \Log::info("Plan expiring email template disabled, skipping for user: {$user->email}");
        }
    }
}
