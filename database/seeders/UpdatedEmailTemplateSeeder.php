<?php


namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\EmailTemplate;
use App\Models\EmailTemplateLang;

class UpdatedEmailTemplateSeeder extends Seeder
{
    public function run()
    {
        $templates = [
            // TRIAL LIFECYCLE
            [
                'name' => 'Trial Welcome Day 1',
                'slug' => 'trial_welcome_day1',
                'from' => 'Welcome Team',
                'subject' => 'Welcome to your {current_plan_name} trial!',
                'content' => '<div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px;">
                    <h2 style="color: #333; margin-bottom: 20px;">Welcome to Your {current_plan_name} Trial!</h2>
                    <p>Hello {user_name},</p>
                    <p>Welcome to your free {current_plan_name} trial! You have <strong>{trial_days} days</strong> to explore all our premium features.</p>
                    <p>Get started by logging into your account and exploring the dashboard.</p>
                    <div style="text-align: center; margin: 30px 0;">
                        <a href="{app_url}/login" style="background: #007cba; color: white; padding: 15px 30px; text-decoration: none; border-radius: 5px; font-weight: bold; display: inline-block;">
                            Start Exploring →
                        </a>
                    </div>
                    <p>Thanks,<br><strong>{app_name}</strong></p>
                </div>',
                'is_enabled' => 1  // ENABLED
            ],
            [
                'name' => 'Trial Getting Started',
                'slug' => 'trial_getting_started', 
                'from' => 'Support Team',
                'subject' => 'Get the most out of your {current_plan_name} trial',
                'content' => '<div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px;">
                    <h2 style="color: #333; margin-bottom: 20px;">Getting Started Tips</h2>
                    <p>Hi {user_name},</p>
                    <p>You\'re 3 days into your {current_plan_name} trial! Here are some tips to get the most out of your remaining <strong>{trial_days} days</strong>:</p>
                    <ul style="color: #666; line-height: 1.8;">
                        <li>Complete your profile setup</li>
                        <li>Import your data</li>
                        <li>Try our key features</li>
                        <li>Set up your first project</li>
                    </ul>
                    <div style="text-align: center; margin: 30px 0;">
                        <a href="{app_url}/dashboard" style="background: #007cba; color: white; padding: 12px 24px; text-decoration: none; border-radius: 5px; font-weight: bold; display: inline-block;">
                            Continue Setup →
                        </a>
                    </div>
                    <p>Need help? Just reply to this email.</p>
                    <p>Best regards,<br><strong>{app_name}</strong></p>
                </div>',
                'is_enabled' => 0  // DISABLED
            ],
            [
                'name' => 'Trial Expiring Soon',
                'slug' => 'trial_expiring',
                'from' => 'Billing Team',
                'subject' => 'Your {current_plan_name} trial expires in {days_left} days',
                'content' => '<div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px;">
                    <h2 style="color: #333; margin-bottom: 20px;">Your Trial Expires Soon</h2>
                    <p>Hi {user_name},</p>
                    <p>Your <strong>{current_plan_name}</strong> trial expires on <strong>{expiry_date}</strong> ({days_left} days remaining).</p>
                    <p>Don\'t lose access to your data! Upgrade now to continue using all {current_plan_name} features.</p>
                    <div style="text-align: center; margin: 30px 0;">
                        <a href="{trial_plan_upgrade_link}" style="background: #28a745; color: white; padding: 15px 30px; text-decoration: none; border-radius: 5px; font-weight: bold; display: inline-block; font-size: 16px;">
                            Upgrade to {current_plan_name} →
                        </a>
                    </div>
                    <p style="text-align: center; margin: 20px 0;">
                        <a href="{app_url}/plans" style="color: #007cba; text-decoration: none;">
                            Or view all available plans →
                        </a>
                    </p>
                    <p>Questions? Contact our support team.</p>
                    <p>Thanks,<br><strong>{app_name}</strong></p>
                </div>',
                'is_enabled' => 0  // DISABLED
            ],
            [
                'name' => 'Trial Expired',
                'slug' => 'trial_expired',
                'from' => 'Billing Team',
                'subject' => 'Your {current_plan_name} trial has expired',
                'content' => '<div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px;">
                    <h2 style="color: #333; margin-bottom: 20px;">Your {current_plan_name} Trial Has Ended</h2>
                    <p>Hi {user_name},</p>
                    <p>Your <strong>{current_plan_name}</strong> trial has ended and your account has been moved to our {free_plan_name}.</p>
                    <p>You won\'t be billed automatically, but to restore full access to your data and premium features, please upgrade to continue using {current_plan_name}.</p>
                    <div style="text-align: center; margin: 30px 0;">
                        <a href="{trial_plan_upgrade_link}" style="background: #28a745; color: white; padding: 15px 30px; text-decoration: none; border-radius: 5px; font-weight: bold; display: inline-block; font-size: 16px;">
                            Continue with {current_plan_name} →
                        </a>
                    </div>
                    <p style="text-align: center; margin: 20px 0;">
                        <a href="{app_url}/plans" style="color: #007cba; text-decoration: none;">
                            Or view all available plans →
                        </a>
                    </p>
                    <hr style="border: none; border-top: 1px solid #eee; margin: 30px 0;">
                    <h3 style="color: #333; font-size: 18px;">What happens next?</h3>
                    <ul style="color: #666; line-height: 1.6;">
                        <li>Your account remains active on our {free_plan_name}</li>
                        <li>Your data is safely stored and ready when you upgrade</li>
                        <li>Upgrade anytime to restore full access</li>
                    </ul>
                    <p style="color: #666; margin-top: 30px;">
                        Questions? Just reply to this email – we\'re here to help!
                    </p>
                    <p style="margin-top: 30px;">
                        Thanks,<br><strong>{app_name}</strong>
                    </p>
                </div>',
                'is_enabled' => 0  // DISABLED
            ],

            // PLAN LIFECYCLE
            [
                'name' => 'Subscription Expiring Soon',
                'slug' => 'plan_expiring',
                'from' => 'Billing Team',
                'subject' => 'Your {current_plan_name} expires in {days_left} days',
                'content' => '<div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px;">
                    <h2 style="color: #333; margin-bottom: 20px;">Your Subscription Expires Soon</h2>
                    <p>Hi {user_name},</p>
                    <p>Your <strong>{current_plan_name}</strong> subscription expires on <strong>{expiry_date}</strong> ({days_left} days remaining).</p>
                    <p>To continue using our service without interruption, please renew your subscription.</p>
                    <div style="text-align: center; margin: 30px 0;">
                        <a href="{app_url}/plans" style="background: #007cba; color: white; padding: 15px 30px; text-decoration: none; border-radius: 5px; font-weight: bold; display: inline-block; font-size: 16px;">
                            Renew {current_plan_name} →
                        </a>
                    </div>
                    <p style="text-align: center; margin: 20px 0;">
                        <a href="{app_url}/billing" style="color: #007cba; text-decoration: none;">
                            Or manage your billing settings →
                        </a>
                    </p>
                    <p>Thanks,<br><strong>{app_name}</strong></p>
                </div>',
                'is_enabled' => 0  // DISABLED
            ],
            [
                'name' => 'Subscription Expired',
                'slug' => 'plan_expired',
                'from' => 'Billing Team',
                'subject' => 'Your {current_plan_name} has expired',
                'content' => '<div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px;">
                    <h2 style="color: #333; margin-bottom: 20px;">Your Subscription Has Expired</h2>
                    <p>Hi {user_name},</p>
                    <p>Your <strong>{current_plan_name}</strong> subscription has expired and your account has been downgraded to our {free_plan_name}.</p>
                    <p>To restore full access to {current_plan_name} features, please renew your subscription.</p>
                    <div style="text-align: center; margin: 30px 0;">
                        <a href="{app_url}/plans" style="background: #28a745; color: white; padding: 15px 30px; text-decoration: none; border-radius: 5px; font-weight: bold; display: inline-block; font-size: 16px;">
                            Renew {current_plan_name} →
                        </a>
                    </div>
                    <p style="text-align: center; margin: 20px 0;">
                        <a href="{app_url}/billing" style="color: #007cba; text-decoration: none;">
                            Or browse other plans →
                        </a>
                    </p>
                    <p>Thanks,<br><strong>{app_name}</strong></p>
                </div>',
                'is_enabled' => 0  // DISABLED
            ],

            // USER ENGAGEMENT
            [
                'name' => 'Inactive User Reminder',
                'slug' => 'trial_inactive_reminder',
                'from' => 'Support Team',
                'subject' => 'We miss you! Come back to your {current_plan_name} trial',
                'content' => '<div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px;">
                    <h2 style="color: #333; margin-bottom: 20px;">We Miss You!</h2>
                    <p>Hi {user_name},</p>
                    <p>We noticed you haven\'t logged in for <strong>{days_inactive} days</strong>. You still have <strong>{trial_days} days</strong> left in your {current_plan_name} trial!</p>
                    <p>Don\'t let your trial go to waste. Log in now and discover what you\'ve been missing.</p>
                    <div style="text-align: center; margin: 30px 0;">
                        <a href="{app_url}/login" style="background: #007cba; color: white; padding: 15px 30px; text-decoration: none; border-radius: 5px; font-weight: bold; display: inline-block;">
                            Continue Your Trial →
                        </a>
                    </div>
                    <p>Need help getting started? Just reply to this email.</p>
                    <p>Best regards,<br><strong>{app_name}</strong></p>
                </div>',
                'is_enabled' => 0  // DISABLED
            ],
            [
                'name' => 'Never Logged In',
                'slug' => 'trial_never_logged_in',
                'from' => 'Support Team',
                'subject' => 'Complete your {current_plan_name} trial setup',
                'content' => '<div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px;">
                    <h2 style="color: #333; margin-bottom: 20px;">Complete Your Account Setup</h2>
                    <p>Hi {user_name},</p>
                    <p>You signed up for our {current_plan_name} trial but haven\'t logged in yet. You\'re missing out on <strong>{trial_days} days</strong> of free access to premium features!</p>
                    <p>Complete your setup now:</p>
                    <div style="text-align: center; margin: 30px 0;">
                        <a href="{app_url}/login" style="background: #28a745; color: white; padding: 15px 30px; text-decoration: none; border-radius: 5px; font-weight: bold; display: inline-block;">
                            Log In to Your Account →
                        </a>
                    </div>
                    <p>Need help? Our support team is here for you.</p>
                    <p>Thanks,<br><strong>{app_name}</strong></p>
                </div>',
                'is_enabled' => 0  // DISABLED
            ],

            // EDUCATIONAL SERIES
            [
                'name' => 'Feature Spotlight',
                'slug' => 'trial_education_features',
                'from' => 'Education Team',
                'subject' => 'Discover powerful {current_plan_name} features',
                'content' => '<div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px;">
                    <h2 style="color: #333; margin-bottom: 20px;">Feature Spotlight</h2>
                    <p>Hi {user_name},</p>
                    <p>You\'re halfway through your {current_plan_name} trial! Here are some powerful features you might have missed:</p>
                    <ul style="color: #666; line-height: 1.8;">
                        <li>Advanced reporting and analytics</li>
                        <li>Team collaboration tools</li>
                        <li>API integrations</li>
                        <li>Custom workflows</li>
                    </ul>
                    <p>You have <strong>{trial_days} days</strong> left to try everything.</p>
                    <div style="text-align: center; margin: 30px 0;">
                        <a href="{app_url}/features" style="background: #007cba; color: white; padding: 12px 24px; text-decoration: none; border-radius: 5px; font-weight: bold; display: inline-block;">
                            Explore Features →
                        </a>
                    </div>
                    <p>Happy exploring!<br><strong>{app_name}</strong></p>
                </div>',
                'is_enabled' => 0  // DISABLED
            ],
            [
                'name' => 'Pro Tips & Tricks',
                'slug' => 'trial_education_tips',
                'from' => 'Education Team',
                'subject' => 'Pro tips to maximize your {current_plan_name} trial',
                'content' => '<div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px;">
                    <h2 style="color: #333; margin-bottom: 20px;">Pro Tips & Tricks</h2>
                    <p>Hi {user_name},</p>
                    <p>Here are some pro tips from our power users to help you get maximum value from {current_plan_name}:</p>
                    <ul style="color: #666; line-height: 1.8;">
                        <li>Set up automated workflows to save time</li>
                        <li>Use keyboard shortcuts for faster navigation</li>
                        <li>Customize your dashboard for your workflow</li>
                        <li>Connect your favorite tools via integrations</li>
                    </ul>
                    <p>With <strong>{trial_days} days</strong> left, now\'s the perfect time to become a pro!</p>
                    <div style="text-align: center; margin: 30px 0;">
                        <a href="{app_url}/dashboard" style="background: #007cba; color: white; padding: 12px 24px; text-decoration: none; border-radius: 5px; font-weight: bold; display: inline-block;">
                            Try These Tips →
                        </a>
                    </div>
                    <p>Best,<br><strong>{app_name}</strong></p>
                </div>',
                'is_enabled' => 0  // DISABLED
            ],
            [
                'name' => 'Advanced Training',
                'slug' => 'trial_education_advanced',
                'from' => 'Education Team',
                'subject' => 'Advanced {current_plan_name} techniques',
                'content' => '<div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px;">
                    <h2 style="color: #333; margin-bottom: 20px;">Advanced Techniques</h2>
                    <p>Hi {user_name},</p>
                    <p>Ready for advanced {current_plan_name} techniques? Here\'s how to become a power user:</p>
                    <ul style="color: #666; line-height: 1.8;">
                        <li>Master bulk operations for efficiency</li>
                        <li>Create custom reports and dashboards</li>
                        <li>Set up advanced automations</li>
                        <li>Use advanced filtering and search</li>
                    </ul>
                    <p>Make the most of your remaining <strong>{trial_days} days</strong>!</p>
                    <div style="text-align: center; margin: 30px 0;">
                        <a href="{app_url}/help" style="background: #007cba; color: white; padding: 12px 24px; text-decoration: none; border-radius: 5px; font-weight: bold; display: inline-block;">
                            Learn Advanced Features →
                        </a>
                    </div>
                    <p>Cheers,<br><strong>{app_name}</strong></p>
                </div>',
                'is_enabled' => 0  // DISABLED
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

        $this->command->info('Email templates seeded successfully!');
        $this->command->info('✅ ENABLED: trial_welcome_day1');
        $this->command->info('❌ DISABLED: All other templates (enable them as you confirm content)');
        $this->command->info('📧 All templates updated with new variables: {current_plan_name}, {free_plan_name}, {upgrade_link}, {trial_plan_upgrade_link}');
        $this->command->info('🔗 Fixed double slash issue in {app_url} links');
        $this->command->info('🎨 Added prominent call-to-action buttons with proper HTML styling');
    }
}