<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\EmailTemplate;
use App\Models\EmailTemplateLang;

class FinalFixedEmailTemplateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $templates = [
            // TRIAL LIFECYCLE EMAILS
            [
                'name' => 'Trial Welcome Day 1',
                'slug' => 'trial_welcome_day1',
                'from' => 'Welcome Team',
                'subject' => 'Welcome to your {current_plan_name} trial!',
                'content' => '<div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px;">
                    <h1 style="color: #333; text-align: center;">Welcome to Your {current_plan_name} Trial!</h1>
                    <p>Hello {user_name},</p>
                    <p>Welcome to your free {current_plan_name} trial! You have <strong>{trial_days} days</strong> to explore all our premium features.</p>
                    <p>Get started by logging into your account and exploring the dashboard.</p>
                    <p style="text-align: center; margin: 30px 0;">
                        <a href="{app_url}login">Start Exploring</a>
                    </p>
                    <p>Thanks,<br><strong>{app_name}</strong></p>
                </div>',
                'is_enabled' => 1
            ],
            [
                'name' => 'Trial Expiring',
                'slug' => 'trial_expiring',
                'from' => 'Billing Team',
                'subject' => 'Your {current_plan_name} trial expires in {days_left} days',
                'content' => '<div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px;">
                    <h1 style="color: #333; text-align: center;">Your Trial Expires Soon!</h1>
                    <p>Hi {user_name},</p>
                    <p>Your <strong>{current_plan_name} trial</strong> expires on <strong>{expiry_date}</strong> ({days_left} days remaining).</p>
                    <p>Don\'t lose access to your premium features! Upgrade now to continue using the {current_plan_name} Plan.</p>
                    <p style="text-align: center; margin: 30px 0;">
                        <a href="{upgrade_link}">Upgrade to {current_plan_name} Plan</a>
                    </p>
                    <p style="text-align: center;">
                        <a href="{app_url}plans">Or view all available plans</a>
                    </p>
                    <p>Best regards,<br><strong>{app_name}</strong></p>
                </div>',
                'is_enabled' => 1
            ],
            [
                'name' => 'Trial Expired',
                'slug' => 'trial_expired',
                'from' => 'Billing Team',
                'subject' => 'Your {current_plan_name} trial has expired',
                'content' => '<div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px;">
                    <h1 style="color: #333; text-align: center;">Your Trial Has Ended</h1>
                    <p>Hi {user_name},</p>
                    <p>Your <strong>{current_plan_name} trial</strong> has ended and your account has been moved to our <strong>{free_plan_name} Plan</strong>.</p>
                    <p>You won\'t be billed automatically, but to restore full access to your data and premium features, please upgrade to continue using the {current_plan_name} Plan.</p>
                    <p style="text-align: center; margin: 30px 0;">
                        <a href="{upgrade_link}">Continue with {current_plan_name} Plan</a>
                    </p>
                    <p style="text-align: center;">
                        <a href="{app_url}plans">Or view all available plans</a>
                    </p>
                    <h3>What happens next?</h3>
                    <ul>
                        <li>Your account remains active on our {free_plan_name} Plan</li>
                        <li>Your data is safe and secure</li>
                        <li>You can upgrade anytime to restore full access</li>
                    </ul>
                    <p>Thanks,<br><strong>{app_name}</strong></p>
                </div>',
                'is_enabled' => 1
            ],

            // PAID PLAN EMAILS
            [
                'name' => 'Subscription Expiring Soon',
                'slug' => 'plan_expiring',
                'from' => 'Billing Team',
                'subject' => 'Your {current_plan_name} Plan expires in {days_left} days',
                'content' => '<div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px;">
                    <h1 style="color: #333; text-align: center;">Subscription Expires Soon</h1>
                    <p>Hi {user_name},</p>
                    <p>Your <strong>{current_plan_name} Plan</strong> subscription expires on <strong>{expiry_date}</strong> (7 days remaining).</p>
                    <p>To continue using our service without interruption, please renew your subscription.</p>
                    <p style="text-align: center; margin: 30px 0;">
                        <a href="{app_url}plans">Renew {current_plan_name} Plan</a>
                    </p>
                    <p style="text-align: center;">
                        <a href="{app_url}billing">Or manage your billing settings</a>
                    </p>
                    <p>Thanks,<br><strong>{app_name}</strong></p>
                </div>',
                'is_enabled' => 1
            ],
            [
                'name' => 'Plan Expired',
                'slug' => 'plan_expired',
                'from' => 'Billing Team',
                'subject' => 'Your {current_plan_name} Plan subscription has expired',
                'content' => '<div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px;">
                    <h1 style="color: #333; text-align: center;">Subscription Expired</h1>
                    <p>Hi {user_name},</p>
                    <p>Your <strong>{current_plan_name} Plan</strong> subscription has expired and your account has been moved to our <strong>{free_plan_name} Plan</strong>.</p>
                    <p>To restore full access to your premium features, please renew your subscription.</p>
                    <p style="text-align: center; margin: 30px 0;">
                        <a href="{app_url}plans">Renew Subscription</a>
                    </p>
                    <p>Thanks,<br><strong>{app_name}</strong></p>
                </div>',
                'is_enabled' => 1
            ],

            // ENGAGEMENT EMAILS  
            [
                'name' => 'Inactive User Reminder',
                'slug' => 'trial_inactive_reminder',
                'from' => 'Support Team',
                'subject' => 'We miss you! Come back to your {current_plan_name} trial',
                'content' => '<div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px;">
                    <h1 style="color: #333; text-align: center;">We Miss You!</h1>
                    <p>Hi {user_name},</p>
                    <p>We noticed you haven\'t logged in for {days_inactive} days. You still have <strong>{trial_days} days</strong> left in your {current_plan_name} trial!</p>
                    <p>Don\'t let your trial go to waste. Log in now and discover what you\'ve been missing.</p>
                    <p style="text-align: center; margin: 30px 0;">
                        <a href="{app_url}login">Continue Your Trial</a>
                    </p>
                    <p>Need help getting started? Just reply to this email.</p>
                    <p>Best regards,<br><strong>{app_name}</strong></p>
                </div>',
                'is_enabled' => 0
            ],
            [
                'name' => 'Never Logged In',
                'slug' => 'trial_never_logged_in',
                'from' => 'Support Team',
                'subject' => 'Complete your {current_plan_name} trial setup',
                'content' => '<div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px;">
                    <h1 style="color: #333; text-align: center;">Complete Your Account Setup</h1>
                    <p>Hi {user_name},</p>
                    <p>You signed up for our {current_plan_name} trial but haven\'t logged in yet. You\'re missing out on <strong>{trial_days} days</strong> of free access to premium features!</p>
                    <p>Complete your setup now:</p>
                    <p style="text-align: center; margin: 30px 0;">
                        <a href="{app_url}login">Log In to Your Account</a>
                    </p>
                    <p>Need help? Our support team is here for you.</p>
                    <p>Thanks,<br><strong>{app_name}</strong></p>
                </div>',
                'is_enabled' => 0
            ],

            // EDUCATIONAL SERIES
            [
                'name' => 'Feature Spotlight',
                'slug' => 'trial_education_features',
                'from' => 'Education Team',
                'subject' => 'Discover powerful {current_plan_name} features',
                'content' => '<div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px;">
                    <h1 style="color: #333; text-align: center;">Feature Spotlight</h1>
                    <p>Hi {user_name},</p>
                    <p>You\'re halfway through your {current_plan_name} trial! Here are some powerful features you might have missed:</p>
                    <ul>
                        <li>Advanced reporting and analytics</li>
                        <li>Team collaboration tools</li>
                        <li>API integrations</li>
                        <li>Custom workflows</li>
                    </ul>
                    <p>You have <strong>{trial_days} days</strong> left to try everything.</p>
                    <p style="text-align: center; margin: 30px 0;">
                        <a href="{app_url}features">Explore Features</a>
                    </p>
                    <p>Happy exploring!<br><strong>{app_name}</strong></p>
                </div>',
                'is_enabled' => 0
            ],
            [
                'name' => 'Pro Tips & Tricks',
                'slug' => 'trial_education_tips',
                'from' => 'Education Team',
                'subject' => 'Pro tips to maximize your {current_plan_name} trial',
                'content' => '<div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px;">
                    <h1 style="color: #333; text-align: center;">Pro Tips & Tricks</h1>
                    <p>Hi {user_name},</p>
                    <p>Here are some pro tips from our power users to help you get maximum value from your {current_plan_name} trial:</p>
                    <ul>
                        <li>Set up automated workflows to save time</li>
                        <li>Use keyboard shortcuts for faster navigation</li>
                        <li>Customize your dashboard for your workflow</li>
                        <li>Connect your favorite tools via integrations</li>
                    </ul>
                    <p>With <strong>{trial_days} days</strong> left, now\'s the perfect time to become a pro!</p>
                    <p style="text-align: center; margin: 30px 0;">
                        <a href="{app_url}dashboard">Try These Tips</a>
                    </p>
                    <p>Best,<br><strong>{app_name}</strong></p>
                </div>',
                'is_enabled' => 0
            ],
            [
                'name' => 'Advanced Training',
                'slug' => 'trial_education_advanced',
                'from' => 'Education Team',
                'subject' => 'Advanced {current_plan_name} techniques',
                'content' => '<div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px;">
                    <h1 style="color: #333; text-align: center;">Advanced Techniques</h1>
                    <p>Hi {user_name},</p>
                    <p>Ready for advanced {current_plan_name} techniques? Here\'s how to become a power user:</p>
                    <ul>
                        <li>Master bulk operations for efficiency</li>
                        <li>Create custom reports and dashboards</li>
                        <li>Set up advanced automations</li>
                        <li>Use advanced filtering and search</li>
                    </ul>
                    <p>Make the most of your remaining <strong>{trial_days} days</strong>!</p>
                    <p style="text-align: center; margin: 30px 0;">
                        <a href="{app_url}help">Learn Advanced Features</a>
                    </p>
                    <p>Cheers,<br><strong>{app_name}</strong></p>
                </div>',
                'is_enabled' => 0
            ]
        ];

        foreach ($templates as $templateData) {
            // Create or update email template
            $template = EmailTemplate::updateOrCreate(
                ['slug' => $templateData['slug']],
                [
                    'name' => $templateData['name'],
                    'slug' => $templateData['slug'],
                    'from' => $templateData['from'],
                    'is_enabled' => $templateData['is_enabled'],
                    'created_by' => 1
                ]
            );

            // Create or update the English language version
            EmailTemplateLang::updateOrCreate(
                [
                    'parent_id' => $template->id,
                    'lang' => 'en'
                ],
                [
                    'subject' => $templateData['subject'],
                    'content' => $templateData['content']
                ]
            );
        }

        $this->command->info('✅ FINAL FIXED email templates seeded successfully!');
        $this->command->info('🔧 FIXED: Simplified button HTML - using plain <a> tags instead of styled buttons');
        $this->command->info('📝 FIXED: Plan naming - "{current_plan_name} Plan" for paid plans, "{current_plan_name} trial" for trials');
        $this->command->info('📝 FIXED: Free plan naming - "{free_plan_name} Plan" (e.g., "David Plan")');
        $this->command->info('✅ ENABLED: Core lifecycle emails');
        $this->command->info('❌ DISABLED: Engagement and educational emails');
    }
}