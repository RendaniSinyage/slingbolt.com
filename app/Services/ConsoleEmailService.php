<?php

namespace App\Services;

use App\Models\User;
use App\Models\Utility;
use App\Models\EmailTemplate;
use App\Models\EmailTemplateLang;
use App\Models\UserEmailTemplate;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Mail\CommonEmailTemplate;

class ConsoleEmailService
{
    /**
     * Send email template that works in both web and console contexts
     * 
     * @param string $emailTemplate Template name
     * @param array $mailTo Array of [user_id => email]
     * @param array $variables Template variables
     * @param int|null $systemUserId Super admin user ID for console context
     * @return array
     */
    public static function sendEmailTemplate($emailTemplate, $mailTo, $variables, $systemUserId = 1)
    {
        // Determine context and get appropriate user
        $usr = self::getContextualUser($systemUserId);
        
        if (!$usr) {
            return [
                'is_success' => false,
                'error' => 'No user context available for email sending'
            ];
        }

        // Clean up mailTo array
        $mailTo = array_values($mailTo);

        // Only process if not Super Admin (same logic as original)
        if ($usr->type != 'Super Admin') {
            // Find template
            $template = EmailTemplate::where('name', 'LIKE', $emailTemplate)->first();
            
            if (!isset($template) || empty($template)) {
                return [
                    'is_success' => false,
                    'error' => __('Mail not send, email template not found'),
                ];
            }

            // Check if template is active for this company
            $is_active = self::checkTemplateActive($template, $usr);
            
            if (!$is_active || $is_active->is_active != 1) {
                return [
                    'is_success' => true, // Return success but don't send (same as original logic)
                    'error' => false,
                ];
            }

            // Get settings for this user
            $settings = Utility::settingsById($usr->id);
            $defaultSettings = self::getDefaultMailSettings();

            // Get email content in user's language
            $content = EmailTemplateLang::where('parent_id', '=', $template->id)
                ->where('lang', 'LIKE', $usr->lang)
                ->first();

            if (!$content) {
                return [
                    'is_success' => false,
                    'error' => 'Email template content not found for language: ' . $usr->lang,
                ];
            }

            $content->from = $template->from;
            
            if (!empty($content->content)) {
                // FIXED: Replace variables in BOTH content AND subject
                $content->content = Utility::replaceVariable($content->content, $variables);
                $content->subject = Utility::replaceVariable($content->subject, $variables);
                
                // Send email
                try {
                    // Configure mail settings
                    config([
                        'mail.driver' => $settings['mail_driver'] ?: $defaultSettings['mail_driver'],
                        'mail.host' => $settings['mail_host'] ?: $defaultSettings['mail_host'],
                        'mail.port' => $settings['mail_port'] ?: $defaultSettings['mail_port'],
                        'mail.encryption' => $settings['mail_encryption'] ?: $defaultSettings['mail_encryption'],
                        'mail.username' => $settings['mail_username'] ?: $defaultSettings['mail_username'],
                        'mail.password' => $settings['mail_password'] ?: $defaultSettings['mail_password'],
                        'mail.from.address' => $settings['mail_from_address'] ?: $defaultSettings['mail_from_address'],
                        'mail.from.name' => $settings['mail_from_name'] ?: $defaultSettings['mail_from_name'],
                    ]);

                    Mail::to($mailTo)->send(new CommonEmailTemplate($content, $settings));
                    
                    Log::info("Console email sent successfully", [
                        'template' => $emailTemplate,
                        'recipients' => $mailTo,
                        'context' => app()->runningInConsole() ? 'console' : 'web'
                    ]);
                    
                } catch (\Exception $e) {
                    $error = $e->getMessage();
                    
                    Log::error("Console email failed", [
                        'template' => $emailTemplate,
                        'recipients' => $mailTo,
                        'error' => $error,
                        'context' => app()->runningInConsole() ? 'console' : 'web'
                    ]);
                }

                if (isset($error)) {
                    return [
                        'is_success' => false,
                        'error' => $error,
                    ];
                } else {
                    return [
                        'is_success' => true,
                        'error' => false,
                    ];
                }
            } else {
                return [
                    'is_success' => false,
                    'error' => __('Mail not send, email content is empty'),
                ];
            }
        }

        // If user is Super Admin, handle differently if needed
        return [
            'is_success' => true,
            'error' => false,
        ];
    }

    /**
     * Get user context based on whether we're in console or web
     */
    private static function getContextualUser($systemUserId = 1)
    {
        if (app()->runningInConsole()) {
            // Console context - use system user (super admin)
            $systemUser = User::find($systemUserId);
            
            if (!$systemUser) {
                Log::error("System user not found", ['user_id' => $systemUserId]);
                return null;
            }
            
            Log::info("Using system user for console email", [
                'user_id' => $systemUser->id,
                'user_type' => $systemUser->type
            ]);
            
            return $systemUser;
        } else {
            // Web context - use authenticated user
            $user = Auth::user();
            
            if (!$user) {
                Log::error("No authenticated user in web context");
                return null;
            }
            
            return $user;
        }
    }

    /**
     * Check if template is active for the given user
     */
    private static function checkTemplateActive($template, $usr)
    {
        if ($usr->type != 'super admin') {
            return UserEmailTemplate::where('template_id', '=', $template->id)
                ->where('user_id', '=', $usr->creatorId())
                ->first();
        } else {
            // Super admin can always send emails
            return (object) ['is_active' => 1];
        }
    }

    /**
     * Get default mail settings
     */
    private static function getDefaultMailSettings()
    {
        $data = Utility::getSetting();
        
        $settings = [
            'mail_driver' => '',
            'mail_host' => '',
            'mail_port' => '',
            'mail_encryption' => '',
            'mail_username' => '',
            'mail_password' => '',
            'mail_from_address' => '',
            'mail_from_name' => '',
        ];
        
        foreach ($data as $row) {
            $settings[$row->name] = $row->value;
        }
        
        return $settings;
    }

    /**
     * Quick method to send system emails (console-safe)
     */
    public static function sendSystemEmail($template, $userEmail, $variables, $userId = null)
    {
        // If we have a user ID, use it, otherwise extract from email array
        if ($userId) {
            $mailTo = [$userId => $userEmail];
        } else {
            // Generate a temporary ID based on email for logging
            $mailTo = [0 => $userEmail];
        }
        
        return self::sendEmailTemplate($template, $mailTo, $variables);
    }
}