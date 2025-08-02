<?php
// app/Console/Commands/CheckExpiredPlans.php (UPDATED - No queue, direct processing)

namespace App\Console\Commands;

use App\Models\User;
use App\Services\PlanExpirationService;
use App\Services\TrialEngagementService;
use Carbon\Carbon;
use Illuminate\Console\Command;

class CheckExpiredPlans extends Command
{
    protected $signature = 'plans:check-expired {--dry-run : Show what would be processed without actually doing it}';
    protected $description = 'Check and process expired trials and plans, send engagement emails';

    public function handle()
    {
        $isDryRun = $this->option('dry-run');
        $today = Carbon::today();
        
        if ($isDryRun) {
            $this->info('🔍 DRY RUN MODE - No emails will be sent, no data will be changed');
        }

        $this->info("🕐 Starting plan expiration check for {$today->format('Y-m-d')}");

        // Get all users with active trials or paid plans
        $usersToProcess = User::where(function($query) {
                $query->where('trial_plan', '>', 0)
                      ->whereNotNull('trial_expire_date');
            })
            ->orWhere(function($query) {
                $query->where('plan', '>', 1)
                      ->whereNotNull('plan_expire_date');
            })
            ->get();

        $this->info("👥 Found {$usersToProcess->count()} users to process");

        $expiredTrials = 0;
        $expiredPlans = 0;
        $expiringTrials = 0;
        $expiringPlans = 0;
        $engagementEmails = 0;

        $planService = new PlanExpirationService();
        $engagementService = new TrialEngagementService();

        foreach ($usersToProcess as $user) {
            
            // Check for trial expiration
            if ($user->trial_expire_date) {
                $trialExpiry = Carbon::parse($user->trial_expire_date);
                
                if ($trialExpiry->lt($today) && $user->trial_plan > 0) {
                    // Trial expired
                    $expiredTrials++;
                    $this->line("⏰ Trial EXPIRED: {$user->email} (expired on {$trialExpiry->format('Y-m-d')})");
                    
                    if (!$isDryRun) {
                        $planService->processUser($user);
                    }
                    
                } elseif ($trialExpiry->diffInDays($today) <= 3 && $trialExpiry->gte($today)) {
                    // Trial expiring soon
                    $expiringTrials++;
                    $daysLeft = $trialExpiry->diffInDays($today);
                    $this->line("⚠️  Trial expiring: {$user->email} ({$daysLeft} days left)");
                    
                    if (!$isDryRun) {
                        $planService->processUser($user);
                    }
                }
            }

            // Check for paid plan expiration
            if ($user->plan_expire_date) {
                $planExpiry = Carbon::parse($user->plan_expire_date);
                
                if ($planExpiry->lt($today) && $user->plan > 1) {
                    // Paid plan expired
                    $expiredPlans++;
                    $this->line("💳 Plan EXPIRED: {$user->email} (expired on {$planExpiry->format('Y-m-d')})");
                    
                    if (!$isDryRun) {
                        $planService->processUser($user);
                    }
                    
                } elseif ($planExpiry->diffInDays($today) <= 7 && $planExpiry->gte($today)) {
                    // Plan expiring soon
                    $expiringPlans++;
                    $daysLeft = $planExpiry->diffInDays($today);
                    $this->line("💳 Plan expiring: {$user->email} ({$daysLeft} days left)");
                    
                    if (!$isDryRun) {
                        $planService->processUser($user);
                    }
                }
            }

            // Check for engagement emails (only for trial users)
            if ($user->trial_plan > 0 && $user->trial_expire_date) {
                $engagementEmails++;
                $this->line("📧 Processing engagement: {$user->email}");
                
                if (!$isDryRun) {
                    $engagementService->processUser($user);
                }
            }
        }

        // Summary
        $this->info('');
        $this->info('📊 SUMMARY:');
        $this->info("   • Expired trials: {$expiredTrials}");
        $this->info("   • Expiring trials: {$expiringTrials}");
        $this->info("   • Expired plans: {$expiredPlans}");
        $this->info("   • Expiring plans: {$expiringPlans}");
        $this->info("   • Engagement emails: {$engagementEmails}");
        
        if ($isDryRun) {
            $this->warn('🔍 This was a DRY RUN - no actual changes were made');
            $this->info('💡 Run without --dry-run to actually process these users');
        } else {
            $this->info('✅ All processing completed synchronously');
            $this->info('📧 Emails were sent immediately (no queue needed)');
        }

        return 0;
    }
}