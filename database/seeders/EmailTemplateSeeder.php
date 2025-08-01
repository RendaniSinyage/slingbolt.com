<?php
// database/seeders/EmailTemplateSeeder.php
// Seeds both email_templates and email_template_langs tables with is_enabled control

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\EmailTemplate;
use App\Models\EmailTemplateLang;

class EmailTemplateSeeder extends Seeder
{
    public function run()
    {
        $templates = [
            // TRIAL LIFECYCLE - ONLY FIRST ONE ENABLED
            [
                'name' => 'Trial Welcome Day 1',
                'slug' => 'trial_welcome_day1',
                'from' => 'Welcome Team',
                'subject' => 'Welcome to your trial!',
                'content' => '<p>Hello {user_name},</p><p>Welcome to your free trial! You have <strong>{trial_days} days</strong> to explore all our features.</p><p>Get started by logging into your account and exploring the dashboard.</p><p>Thanks,<br>{app_name}</p>',
                'is_enabled' => 1  // ENABLED
            ],
            [
                'name' => 'Trial Getting Started',
                'slug' => 'trial_getting_started',
                'from' => 'Support Team',
                'subject' => 'Get the most out of your trial',
                'content' => '<p>Hi {user_name},</p><p>You\'re 3 days into your trial! Here are some tips to get the most out of your remaining <strong>{trial_days} days</strong>:</p><ul><li>Complete your profile setup</li><li>Import your data</li><li>Try our key features</li></ul><p>Need help? Just reply to this email.</p><p>Best regards,<br>{app_name}</p>',
                'is_enabled' => 0  // DISABLED
            ],
            [
                'name' => 'Trial Expiring Soon',
                'slug' => 'trial_expiring',
                'from' => 'Billing Team',
                'subject' => 'Your trial expires in {days_left} days',
                'content' => '<p>Hi {user_name},</p><p>Your trial expires on <strong>{expiry_date}</strong> ({days_left} days remaining).</p><p>Don\'t lose access to your data! Upgrade now to continue using all features.</p><p><a href="{app_url}/plans">Choose Your Plan</a></p><p>Questions? Contact our support team.</p><p>Thanks,<br>{app_name}</p>',
                'is_enabled' => 0  // DISABLED
            ],
            [
                'name' => 'Trial Expired',
                'slug' => 'trial_expired',
                'from' => 'Billing Team',
                'subject' => 'Your trial has expired',
                'content' => '<p>Hi {user_name},</p><p>Your {trial_days} has ended and your account has been moved to our free plan.</p><p>To restore full access to your data and features, please choose a paid plan.</p><p><a href="{app_url}/plans">Upgrade Your Account</a></p><p>Thanks,<br>{app_name}</p>',
                'is_enabled' => 0  // DISABLED
            ],

            // PLAN LIFECYCLE
            [
                'name' => 'Subscription Expiring Soon',
                'slug' => 'plan_expiring',
                'from' => 'Billing Team',
                'subject' => 'Your subscription expires in {days_left} days',
                'content' => '<p>Hi {user_name},</p><p>Your subscription expires on <strong>{expiry_date}</strong> ({days_left} days remaining).</p><p>To continue using our service without interruption, please update your billing information or renew your subscription.</p><p><a href="{app_url}/billing">Manage Subscription</a></p><p>Thanks,<br>{app_name}</p>',
                'is_enabled' => 0  // DISABLED
            ],
            [
                'name' => 'Subscription Expired',
                'slug' => 'plan_expired',
                'from' => 'Billing Team',
                'subject' => 'Your subscription has expired',
                'content' => '<p>Hi {user_name},</p><p>Your {plan_name} has expired and your account has been downgraded to our free plan.</p><p>To restore full access, please renew your subscription.</p><p><a href="{app_url}/plans">Renew Subscription</a></p><p>Thanks,<br>{app_name}</p>',
                'is_enabled' => 0  // DISABLED
            ],

            // USER ENGAGEMENT
            [
                'name' => 'Inactive User Reminder',
                'slug' => 'trial_inactive_reminder',
                'from' => 'Support Team',
                'subject' => 'We miss you! Come back to your trial',
                'content' => '<p>Hi {user_name},</p><p>We noticed you haven\'t logged in for {days_inactive} days. You still have <strong>{trial_days} days</strong> left in your trial!</p><p>Don\'t let your trial go to waste. Log in now and discover what you\'ve been missing.</p><p><a href="{app_url}/login">Continue Your Trial</a></p><p>Need help getting started? Just reply to this email.</p><p>Best regards,<br>{app_name}</p>',
                'is_enabled' => 0  // DISABLED
            ],
            [
                'name' => 'Never Logged In',
                'slug' => 'trial_never_logged_in',
                'from' => 'Support Team',
                'subject' => 'Complete your account setup',
                'content' => '<p>Hi {user_name},</p><p>You signed up for our service but haven\'t logged in yet. You\'re missing out on <strong>{trial_days} days</strong> of free access!</p><p>Complete your setup now:</p><p><a href="{app_url}/login">Log In to Your Account</a></p><p>Need help? Our support team is here for you.</p><p>Thanks,<br>{app_name}</p>',
                'is_enabled' => 0  // DISABLED
            ],

            // EDUCATIONAL SERIES
            [
                'name' => 'Feature Spotlight',
                'slug' => 'trial_education_features',
                'from' => 'Education Team',
                'subject' => 'Discover powerful features in your trial',
                'content' => '<p>Hi {user_name},</p><p>You\'re halfway through your trial! Here are some powerful features you might have missed:</p><ul><li>Advanced reporting</li><li>Team collaboration tools</li><li>API integrations</li></ul><p>You have <strong>{trial_days} days</strong> left to try everything.</p><p><a href="{app_url}/features">Explore Features</a></p><p>Happy exploring!<br>{app_name}</p>',
                'is_enabled' => 0  // DISABLED
            ],
            [
                'name' => 'Pro Tips & Tricks',
                'slug' => 'trial_education_tips',
                'from' => 'Education Team',
                'subject' => 'Pro tips to maximize your results',
                'content' => '<p>Hi {user_name},</p><p>Here are some pro tips from our power users to help you get maximum value:</p><ul><li>Set up automated workflows</li><li>Use keyboard shortcuts</li><li>Customize your dashboard</li></ul><p>With <strong>{trial_days} days</strong> left, now\'s the perfect time to become a pro!</p><p>Best,<br>{app_name}</p>',
                'is_enabled' => 0  // DISABLED
            ],
            [
                'name' => 'Advanced Training',
                'slug' => 'trial_education_advanced',
                'from' => 'Education Team',
                'subject' => 'Advanced techniques for power users',
                'content' => '<p>Hi {user_name},</p><p>Ready for advanced techniques? Here\'s how to become a power user:</p><ul><li>Master bulk operations</li><li>Create custom reports</li><li>Set up advanced automations</li></ul><p>Make the most of your remaining <strong>{trial_days} days</strong>!</p><p>Cheers,<br>{app_name}</p>',
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
                    'is_enabled' => $templateData['is_enabled'], // ADD THIS FIELD
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
    }
}

