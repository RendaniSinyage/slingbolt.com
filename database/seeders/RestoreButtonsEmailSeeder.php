<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\EmailTemplate;
use App\Models\EmailTemplateLang;

class RestoreButtonsEmailSeeder extends Seeder
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
                'content' => '<div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; background-color: #ffffff;">
                    <h2 style="color: #333; margin-bottom: 20px; text-align: center;">Welcome to Your {current_plan_name} Trial!</h2>
                    <p style="font-size: 16px; line-height: 1.6; color: #555;">Hello {user_name},</p>
                    <p style="font-size: 16px; line-height: 1.6; color: #555;">Welcome to your free {current_plan_name} trial! You have <strong>{trial_days} days</strong> to explore all our premium features.</p>
                    <p style="font-size: 16px; line-height: 1.6; color: #555;">Get started by logging into your account and exploring the dashboard.</p>
                    <div style="text-align: center; margin: 30px 0;">
                        <a href="{app_url}login" style="background-color: #007cba; color: white; padding: 15px 30px; text-decoration: none; border-radius: 5px; font-weight: bold; display: inline-block; font-size: 16px;">Start Exploring</a>
                    </div>
                    <p style="font-size: 16px; line-height: 1.6; color: #555;">Thanks,<br><strong>{app_name}</strong></p>
                </div>',
                'is_enabled' => 1
            ],
            [
                'name' => 'Trial Expiring',
                'slug' => 'trial_expiring',
                'from' => 'Billing Team',
                'subject' => 'Your {current_plan_name} trial expires in {days_left} days',
                'content' => '<div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; background-color: #ffffff;">
                    <h2 style="color: #333; margin-bottom: 20px; text-align: center;">Your Trial Expires Soon!</h2>
                    <p style="font-size: 16px; line-height: 1.6; color: #555;">Hi {user_name},</p>
                    <p style="font-size: 16px; line-height: 1.6; color: #555;">Your <strong>{current_plan_name} trial</strong> expires on <strong>{expiry_date}</strong> ({days_left} days remaining).</p>
                    <p style="font-size: 16px; line-height: 1.6; color: #555;">Don\'t lose access to your premium features! Upgrade now to continue using the {current_plan_name} Plan.</p>
                    <div style="text-align: center; margin: 30px 0;">
                        <a href="{upgrade_link}" style="background-color: #28a745; color: white; padding: 15px 30px; text-decoration: none; border-radius: 5px; font-weight: bold; display: inline-block; font-size: 16px;">Upgrade to {current_plan_name} Plan</a>
                    </div>
                    <p style="font-size: 14px; color: #777; text-align: center;">
                        <a href="{app_url}plans" style="color: #007cba; text-decoration: none;">Or view all available plans</a>
                    </p>
                    <p style="font-size: 16px; line-height: 1.6; color: #555;">Best regards,<br><strong>{app_name}</strong></p>
                </div>',
                'is_enabled' => 1
            ],
            [
                'name' => 'Trial Expired',
                'slug' => 'trial_expired',
                'from' => 'Billing Team',
                'subject' => 'Your {current_plan_name} trial has expired',
                'content' => '<div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; background-color: #ffffff;">
                    <h2 style="color: #333; margin-bottom: 20px; text-align: center;">Your Trial Has Ended</h2>
                    <p style="font-size: 16px; line-height: 1.6; color: #555;">Hi {user_name},</p>
                    <p style="font-size: 16px; line-height: 1.6; color: #555;">Your <strong>{current_plan_name} trial</strong> has ended and your account has been moved to our <strong>{free_plan_name} Plan</strong>.</p>
                    <p style="font-size: 16px; line-height: 1.6; color: #555;">You won\'t be billed automatically, but to restore full access to your data and premium features, please upgrade to continue using the {current_plan_name} Plan.</p>
                    <div style="text-align: center; margin: 30px 0;">
                        <a href="{upgrade_link}" style="background-color: #28a745; color: white; padding: 15px 30px; text-decoration: none; border-radius: 5px; font-weight: bold; display: inline-block; font-size: 16px;">Continue with {current_plan_name} Plan</a>
                    </div>
                    <p style="font-size: 14px; color: #777; text-align: center;">
                        <a href="{app_url}plans" style="color: #007cba; text-decoration: none;">Or view all available plans</a>
                    </p>
                    <div style="margin: 30px 0; padding: 20px; background-color: #f8f9fa; border-radius: 5px;">
                        <h3 style="color: #333; margin-bottom: 15px; font-size: 18px;">What happens next?</h3>
                        <ul style="color: #666; line-height: 1.8; margin: 0; padding-left: 20px;">
                            <li>Your account remains active on our {free_plan_name} Plan</li>
                            <li>Your data is safe and secure</li>
                            <li>You can upgrade anytime to restore full access</li>
                        </ul>
                    </div>
                    <p style="font-size: 16px; line-height: 1.6; color: #555;">Thanks,<br><strong>{app_name}</strong></p>
                </div>',
                'is_enabled' => 1
            ],

            // PAID PLAN EMAILS
            [
                'name' => 'Subscription Expiring Soon',
                'slug' => 'plan_expiring',
                'from' => 'Billing Team',
                'subject' => 'Your {current_plan_name} Plan expires in {days_left} days',
                'content' => '<div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; background-color: #ffffff;">
                    <h2 style="color: #333; margin-bottom: 20px; text-align: center;">Subscription Expires Soon</h2>
                    <p style="font-size: 16px; line-height: 1.6; color: #555;">Hi {user_name},</p>
                    <p style="font-size: 16px; line-height: 1.6; color: #555;">Your <strong>{current_plan_name} Plan</strong> subscription expires on <strong>{expiry_date}</strong> (7 days remaining).</p>
                    <p style="font-size: 16px; line-height: 1.6; color: #555;">To continue using our service without interruption, please renew your subscription.</p>
                    <div style="text-align: center; margin: 30px 0;">
                        <a href="{app_url}plans" style="background-color: #007cba; color: white; padding: 15px 30px; text-decoration: none; border-radius: 5px; font-weight: bold; display: inline-block; font-size: 16px;">Renew {current_plan_name} Plan</a>
                    </div>
                    <p style="font-size: 14px; color: #777; text-align: center;">
                        <a href="{app_url}billing" style="color: #007cba; text-decoration: none;">Or manage your billing settings</a>
                    </p>
                    <p style="font-size: 16px; line-height: 1.6; color: #555;">Thanks,<br><strong>{app_name}</strong></p>
                </div>',
                'is_enabled' => 1
            ],
            [
                'name' => 'Plan Expired',
                'slug' => 'plan_expired',
                'from' => 'Billing Team',
                'subject' => 'Your {current_plan_name} Plan subscription has expired',
                'content' => '<div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; background-color: #ffffff;">
                    <h2 style="color: #333; margin-bottom: 20px; text-align: center;">Subscription Expired</h2>
                    <p style="font-size: 16px; line-height: 1.6; color: #555;">Hi {user_name},</p>
                    <p style="font-size: 16px; line-height: 1.6; color: #555;">Your <strong>{current_plan_name} Plan</strong> subscription has expired and your account has been moved to our <strong>{free_plan_name} Plan</strong>.</p>
                    <p style="font-size: 16px; line-height: 1.6; color: #555;">To restore full access to your premium features, please renew your subscription.</p>
                    <div style="text-align: center; margin: 30px 0;">
                        <a href="{app_url}plans" style="background-color: #28a745; color: white; padding: 15px 30px; text-decoration: none; border-radius: 5px; font-weight: bold; display: inline-block; font-size: 16px;">Renew Subscription</a>
                    </div>
                    <p style="font-size: 16px; line-height: 1.6; color: #555;">Thanks,<br><strong>{app_name}</strong></p>
                </div>',
                'is_enabled' => 1
            ],

            // ENGAGEMENT EMAILS  
            [
                'name' => 'Inactive User Reminder',
                'slug' => 'trial_inactive_reminder',
                'from' => 'Support Team',
                'subject' => 'We miss you! Come back to your {current_plan_name} trial',
                'content' => '<div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; background-color: #ffffff;">
                    <h2 style="color: #333; margin-bottom: 20px; text-align: center;">We Miss You!</h2>
                    <p style="font-size: 16px; line-height: 1.6; color: #555;">Hi {user_name},</p>
                    <p style="font-size: 16px; line-height: 1.6; color: #555;">We noticed you haven\'t logged in for {days_inactive} days. You still have <strong>{trial_days} days</strong> left in your {current_plan_name} trial!</p>
                    <p style="font-size: 16px; line-height: 1.6; color: #555;">Don\'t let your trial go to waste. Log in now and discover what you\'ve been missing.</p>
                    <div style="text-align: center; margin: 30px 0;">
                        <a href="{app_url}login" style="background-color: #007cba; color: white; padding: 15px 30px; text-decoration: none; border-radius: 5px; font-weight: bold; display: inline-block; font-size: 16px;">Continue Your Trial</a>
                    </div>
                    <p style="font-size: 16px; line-height: 1.6; color: #555;">Need help getting started? Just reply to this email.</p>
                    <p style="font-size: 16px; line-height: 1.6; color: #555;">Best regards,<br><strong>{app_name}</strong></p>
                </div>',
                'is_enabled' => 0
            ],
            [
                'name' => 'Never Logged In',
                'slug' => 'trial_never_logged_in',
                'from' => 'Support Team',
                'subject' => 'Complete your {current_plan_name} trial setup',
                'content' => '<div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; background-color: #ffffff;">
                    <h2 style="color: #333; margin-bottom: 20px; text-align: center;">Complete Your Account Setup</h2>
                    <p style="font-size: 16px; line-height: 1.6; color: #555;">Hi {user_name},</p>
                    <p style="font-size: 16px; line-height: 1.6; color: #555;">You signed up for our {current_plan_name} trial but haven\'t logged in yet. You\'re missing out on <strong>{trial_days} days</strong> of free access to premium features!</p>
                    <p style="font-size: 16px; line-height: 1.6; color: #555;">Complete your setup now:</p>
                    <div style="text-align: center; margin: 30px 0;">
                        <a href="{app_url}login" style="background-color: #28a745; color: white; padding: 15px 30px; text-decoration: none; border-radius: 5px; font-weight: bold; display: inline-block; font-size: 16px;">Log In to Your Account</a>
                    </div>
                    <p style="font-size: 16px; line-height: 1.6; color: #555;">Need help? Our support team is here for you.</p>
                    <p style="font-size: 16px; line-height: 1.6; color: #555;">Thanks,<br><strong>{app_name}</strong></p>
                </div>',
                'is_enabled' => 0
            ],

            // EDUCATIONAL SERIES
            [
                'name' => 'Feature Spotlight',
                'slug' => 'trial_education_features',
                'from' => 'Education Team',
                'subject' => 'Discover powerful {current_plan_name} features',
                'content' => '<div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; background-color: #ffffff;">
                    <h2 style="color: #333; margin-bottom: 20px; text-align: center;">Feature Spotlight</h2>
                    <p style="font-size: 16px; line-height: 1.6; color: #555;">Hi {user_name},</p>
                    <p style="font-size: 16px; line-height: 1.6; color: #555;">You\'re halfway through your {current_plan_name} trial! Here are some powerful features you might have missed:</p>
                    <ul style="color: #666; line-height: 1.8; font-size: 16px; margin: 20px 0; padding-left: 20px;">
                        <li>Advanced reporting and analytics</li>
                        <li>Team collaboration tools</li>
                        <li>API integrations</li>
                        <li>Custom workflows</li>
                    </ul>
                    <p style="font-size: 16px; line-height: 1.6; color: #555;">You have <strong>{trial_days} days</strong> left to try everything.</p>
                    <div style="text-align: center; margin: 30px 0;">
                        <a href="{app_url}features" style="background-color: #007cba; color: white; padding: 12px 24px; text-decoration: none; border-radius: 5px; font-weight: bold; display: inline-block; font-size: 16px;">Explore Features</a>
                    </div>
                    <p style="font-size: 16px; line-height: 1.6; color: #555;">Happy exploring!<br><strong>{app_name}</strong></p>
                </div>',
                'is_enabled' => 0
            ],
            [
                'name' => 'Pro Tips & Tricks',
                'slug' => 'trial_education_tips',
                'from' => 'Education Team',
                'subject' => 'Pro tips to maximize your {current_plan_name} trial',
                'content' => '<div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; background-color: #ffffff;">
                    <h2 style="color: #333; margin-bottom: 20px; text-align: center;">Pro Tips & Tricks</h2>
                    <p style="font-size: 16px; line-height: 1.6; color: #555;">Hi {user_name},</p>
                    <p style="font-size: 16px; line-height: 1.6; color: #555;">Here are some pro tips from our power users to help you get maximum value from your {current_plan_name} trial:</p>
                    <ul style="color: #666; line-height: 1.8; font-size: 16px; margin: 20px 0; padding-left: 20px;">
                        <li>Set up automated workflows to save time</li>
                        <li>Use keyboard shortcuts for faster navigation</li>
                        <li>Customize your dashboard for your workflow</li>
                        <li>Connect your favorite tools via integrations</li>
                    </ul>
                    <p style="font-size: 16px; line-height: 1.6; color: #555;">With <strong>{trial_days} days</strong> left, now\'s the perfect time to become a pro!</p>
                    <div style="text-align: center; margin: 30px 0;">
                        <a href="{app_url}dashboard" style="background-color: #007cba; color: white; padding: 12px 24px; text-decoration: none; border-radius: 5px; font-weight: bold; display: inline-block; font-size: 16px;">Try These Tips</a>
                    </div>
                    <p style="font-size: 16px; line-height: 1.6; color: #555;">Best,<br><strong>{app_name}</strong></p>
                </div>',
                'is_enabled' => 0
            ],
            [
                'name' => 'Advanced Training',
                'slug' => 'trial_education_advanced',
                'from' => 'Education Team',
                'subject' => 'Advanced {current_plan_name} techniques',
                'content' => '<div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; background-color: #ffffff;">
                    <h2 style="color: #333; margin-bottom: 20px; text-align: center;">Advanced Techniques</h2>
                    <p style="font-size: 16px; line-height: 1.6; color: #555;">Hi {user_name},</p>
                    <p style="font-size: 16px; line-height: 1.6; color: #555;">Ready for advanced {current_plan_name} techniques? Here\'s how to become a power user:</p>
                    <ul style="color: #666; line-height: 1.8; font-size: 16px; margin: 20px 0; padding-left: 20px;">
                        <li>Master bulk operations for efficiency</li>
                        <li>Create custom reports and dashboards</li>
                        <li>Set up advanced automations</li>
                        <li>Use advanced filtering and search</li>
                    </ul>
                    <p style="font-size: 16px; line-height: 1.6; color: #555;">Make the most of your remaining <strong>{trial_days} days</strong>!</p>
                    <div style="text-align: center; margin: 30px 0;">
                        <a href="{app_url}help" style="background-color: #007cba; color: white; padding: 12px 24px; text-decoration: none; border-radius: 5px; font-weight: bold; display: inline-block; font-size: 16px;">Learn Advanced Features</a>
                    </div>
                    <p style="font-size: 16px; line-height: 1.6; color: #555;">Cheers,<br><strong>{app_name}</strong></p>
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

        $this->command->info('✅ FIXED broken HTML links and buttons!');
        $this->command->info('🔧 FIXED: Removed broken → arrows that were causing HTML malformation');
        $this->command->info('🎨 FIXED: All buttons now have proper HTML structure');
        $this->command->info('📝 FIXED: Plan naming with "Plan" suffix where appropriate');
        $this->command->info('✅ ENABLED: Core lifecycle emails');
    }
}