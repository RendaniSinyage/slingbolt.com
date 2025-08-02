<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\PlanExpirationService;
use App\Services\TrialEngagementService;
use App\Services\ConsoleEmailService;
use Carbon\Carbon;
use Illuminate\Console\Command;

class TestEmailTemplates extends Command
{
    protected $signature = 'emails:test {email} {user_id} {--template=all : Specific template to test or "all"}';
    protected $description = 'Test email templates by sending to specific email without affecting user data';

    public function handle()
    {
        $testEmail = $this->argument('email');
        $userId = $this->argument('user_id');
        $templateToTest = $this->option('template');

        // Get the user to use as template data
        $user = User::find($userId);
        if (!$user) {
            $this->error("User with ID {$userId} not found!");
            return 1;
        }

        $this->info("🧪 TESTING EMAIL TEMPLATES");
        $this->info("📧 Test Email: {$testEmail}");
        $this->info("👤 Using data from user: {$user->name} (ID: {$userId})");
        $this->info("🎯 Template: {$templateToTest}");
        $this->line("");

        // Create test scenarios - UPDATED with correct template names
        $testScenarios = [
            'trial_expired' => [
                'description' => 'Trial Expired Email',
                'method' => 'sendTrialExpiredTest'
            ],
            'trial_expiring' => [
                'description' => 'Trial Expiring Email (3 days left)',
                'method' => 'sendTrialExpiringTest'
            ],
            'plan_expired' => [
                'description' => 'Plan Expired Email',
                'method' => 'sendPlanExpiredTest'
            ],
            'plan_expiring' => [
                'description' => 'Plan Expiring Email (7 days left)',
                'method' => 'sendPlanExpiringTest'
            ],
            'trial_inactive_reminder' => [
                'description' => 'Inactive User Reminder',
                'method' => 'sendInactiveReminderTest'
            ],
            'trial_never_logged_in' => [
                'description' => 'Never Logged In Email',
                'method' => 'sendNeverLoggedInTest'
            ],
            'trial_welcome_day1' => [
                'description' => 'Trial Welcome Day 1',
                'method' => 'sendWelcomeTest'
            ],
            'trial_education_features' => [
                'description' => 'Educational Features Email',
                'method' => 'sendEducationFeaturesTest'
            ],
            'trial_education_tips' => [
                'description' => 'Pro Tips & Tricks Email',
                'method' => 'sendEducationTipsTest'
            ],
            'trial_education_advanced' => [
                'description' => 'Advanced Training Email',
                'method' => 'sendEducationAdvancedTest'
            ]
        ];

        if ($templateToTest === 'all') {
            // Test all templates
            foreach ($testScenarios as $template => $scenario) {
                $this->testTemplate($template, $scenario, $user, $testEmail);
                $this->line(""); // Empty line between tests
            }
        } else {
            // Test specific template
            if (!isset($testScenarios[$templateToTest])) {
                $this->error("Template '{$templateToTest}' not found!");
                $this->info("Available templates: " . implode(', ', array_keys($testScenarios)));
                return 1;
            }
            
            $this->testTemplate($templateToTest, $testScenarios[$templateToTest], $user, $testEmail);
        }

        $this->info("✅ Email testing completed!");
        $this->info("📧 Check {$testEmail} for the test emails");
        $this->line("");
        $this->info("💡 Note: If some emails didn't send, check that templates are enabled in your database");
        $this->info("💡 Run: php artisan tinker then \\App\\Models\\EmailTemplate::select('name', 'slug', 'is_enabled')->get()");
        
        return 0;
    }

    private function testTemplate($templateSlug, $scenario, $user, $testEmail)
    {
        $this->info("🔄 Testing: {$scenario['description']}");
        
        try {
            $method = $scenario['method'];
            $this->$method($user, $testEmail);
            $this->info("✅ Sent: {$scenario['description']}");
        } catch (\Exception $e) {
            $this->error("❌ Failed: {$scenario['description']} - " . $e->getMessage());
        }
    }

    private function sendTrialExpiredTest($user, $testEmail)
    {
        // Get plan names
        $currentPlan = \App\Models\Plan::find($user->trial_plan ?: 3);
        $freePlan = \App\Models\Plan::find(1);
        
        // Generate upgrade link
        $encryptedPlanId = \Illuminate\Support\Facades\Crypt::encrypt($user->trial_plan ?: 3);
        $upgradeLink = config('app.url') . '/stripe/' . $encryptedPlanId;
        
        $variables = [
            'user_name' => $user->name,
            'user_email' => $testEmail,
            'trial_days' => 'trial period',
            'current_plan_name' => $currentPlan ? $currentPlan->name : 'Pro Plan',
            'free_plan_name' => $freePlan ? $freePlan->name : 'Free Plan',
            'upgrade_link' => $upgradeLink,
            'trial_plan_upgrade_link' => $upgradeLink
        ];

        ConsoleEmailService::sendEmailTemplate('Trial Expired', [$user->id => $testEmail], $variables);
    }

    private function sendTrialExpiringTest($user, $testEmail)
    {
        $encryptedPlanId = \Illuminate\Support\Facades\Crypt::encrypt($user->trial_plan ?: 3);
        $upgradeLink = config('app.url') . '/stripe/' . $encryptedPlanId;
        $currentPlan = \App\Models\Plan::find($user->trial_plan ?: 3);
        
        $variables = [
            'user_name' => $user->name,
            'user_email' => $testEmail,
            'days_left' => 3,
            'expiry_date' => Carbon::now()->addDays(3)->format('M d, Y'),
            'current_plan_name' => $currentPlan ? $currentPlan->name : 'Pro Plan',
            'upgrade_link' => $upgradeLink,
            'trial_plan_upgrade_link' => $upgradeLink
        ];

        ConsoleEmailService::sendEmailTemplate('Trial Expiring', [$user->id => $testEmail], $variables);
    }

    private function sendPlanExpiredTest($user, $testEmail)
    {
        $currentPlan = \App\Models\Plan::find($user->plan ?: 3);
        $freePlan = \App\Models\Plan::find(1);
        
        $variables = [
            'user_name' => $user->name,
            'user_email' => $testEmail,
            'plan_name' => 'subscription',
            'current_plan_name' => $currentPlan ? $currentPlan->name : 'Pro Plan',
            'free_plan_name' => $freePlan ? $freePlan->name : 'Free Plan'
        ];

        ConsoleEmailService::sendEmailTemplate('Plan Expired', [$user->id => $testEmail], $variables);
    }

    private function sendPlanExpiringTest($user, $testEmail)
    {
        $currentPlan = \App\Models\Plan::find($user->plan ?: 3);
        
        $variables = [
            'user_name' => $user->name,
            'user_email' => $testEmail,
            'days_left' => 7,
            'expiry_date' => Carbon::now()->addDays(7)->format('M d, Y'),
            'current_plan_name' => $currentPlan ? $currentPlan->name : 'Pro Plan'
        ];

        ConsoleEmailService::sendEmailTemplate('Subscription Expiring Soon', [$user->id => $testEmail], $variables);
    }

    private function sendInactiveReminderTest($user, $testEmail)
    {
        $currentPlan = \App\Models\Plan::find($user->trial_plan ?: 3);
        
        $variables = [
            'user_name' => $user->name,
            'days_inactive' => 5,
            'trial_days' => 10,
            'current_plan_name' => $currentPlan ? $currentPlan->name : 'Pro Plan'
        ];

        // FIXED: Use the actual template name from seeder, not slug
        ConsoleEmailService::sendEmailTemplate('Inactive User Reminder', [$user->id => $testEmail], $variables);
    }

    private function sendNeverLoggedInTest($user, $testEmail)
    {
        $currentPlan = \App\Models\Plan::find($user->trial_plan ?: 3);
        
        $variables = [
            'user_name' => $user->name,
            'trial_days' => 14,
            'current_plan_name' => $currentPlan ? $currentPlan->name : 'Pro Plan'
        ];

        // FIXED: Use the actual template name from seeder, not slug
        ConsoleEmailService::sendEmailTemplate('Never Logged In', [$user->id => $testEmail], $variables);
    }

    private function sendWelcomeTest($user, $testEmail)
    {
        $currentPlan = \App\Models\Plan::find($user->trial_plan ?: 3);
        
        $variables = [
            'user_name' => $user->name,
            'trial_days' => 14,
            'current_plan_name' => $currentPlan ? $currentPlan->name : 'Pro Plan'
        ];

        ConsoleEmailService::sendEmailTemplate('Trial Welcome Day 1', [$user->id => $testEmail], $variables);
    }

    private function sendEducationFeaturesTest($user, $testEmail)
    {
        $currentPlan = \App\Models\Plan::find($user->trial_plan ?: 3);
        
        $variables = [
            'user_name' => $user->name,
            'trial_days' => 7,
            'current_plan_name' => $currentPlan ? $currentPlan->name : 'Pro Plan'
        ];

        // FIXED: Use the actual template name from seeder, not slug
        ConsoleEmailService::sendEmailTemplate('Feature Spotlight', [$user->id => $testEmail], $variables);
    }

    // Additional educational templates from your seeder
    private function sendEducationTipsTest($user, $testEmail)
    {
        $currentPlan = \App\Models\Plan::find($user->trial_plan ?: 3);
        
        $variables = [
            'user_name' => $user->name,
            'trial_days' => 7,
            'current_plan_name' => $currentPlan ? $currentPlan->name : 'Pro Plan'
        ];

        ConsoleEmailService::sendEmailTemplate('Pro Tips & Tricks', [$user->id => $testEmail], $variables);
    }

    private function sendEducationAdvancedTest($user, $testEmail)
    {
        $currentPlan = \App\Models\Plan::find($user->trial_plan ?: 3);
        
        $variables = [
            'user_name' => $user->name,
            'trial_days' => 5,
            'current_plan_name' => $currentPlan ? $currentPlan->name : 'Pro Plan'
        ];

        ConsoleEmailService::sendEmailTemplate('Advanced Training', [$user->id => $testEmail], $variables);
    }
}