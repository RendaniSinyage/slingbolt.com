<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use Illuminate\Http\Request;
use  App\Models\Utility;

class EmailVerificationNotificationController extends Controller
{
    /**
     * Send a new email verification notification.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        if ($request->user()->hasVerifiedEmail()) {
            return redirect()->intended(RouteServiceProvider::HOME);
        }

        $user = $request->user();
        $userEmail = $user->email;

        // Check if there's already a queued email for this recipient
        $queuedEmail = $this->checkQueuedEmail($userEmail);

        if ($queuedEmail) {
            // Force retry the existing queued email instead of sending new one
            $retryResult = $this->forceRetryQueuedEmail($queuedEmail['message_id']);

            if ($retryResult['success']) {
                \Log::info("Forced retry of queued email", [
                    'user_id' => $user->id,
                    'email' => $userEmail,
                    'message_id' => $queuedEmail['message_id'],
                    'ip' => $request->ip()
                ]);

                return back()->with('status', __('Verification email retry triggered. Please check your email in a few minutes.'));
            } else {
                \Log::warning("Failed to retry queued email, will send new one", [
                    'user_id' => $user->id,
                    'email' => $userEmail,
                    'message_id' => $queuedEmail['message_id'],
                    'error' => $retryResult['error']
                ]);
                // Fall through to send new email
            }
        }

        // Rate limiting for new emails
        // BYPASS rate limiting if email is not in queue (likely delivered/failed already)
        $lastVerificationRequest = $user->updated_at;
        $allowImmediateResend = !$queuedEmail; // Allow if no email in queue

        if (!$allowImmediateResend && $lastVerificationRequest && $lastVerificationRequest > now()->subMinutes(5)) {
            \Log::info("Email verification rate limited - email still in queue", [
                'user_id' => $user->id,
                'email' => $userEmail,
                'last_request' => $lastVerificationRequest,
                'ip' => $request->ip()
            ]);

            return back()->with('status', __('Please wait 5 minutes before requesting another verification email.'));
        }

        $settings = Utility::settings();

        config([
            'mail.driver'       => $settings['mail_driver'],
            'mail.host'         => $settings['mail_host'],
            'mail.port'         => $settings['mail_port'],
            'mail.encryption'   => $settings['mail_encryption'],
            'mail.username'     => $settings['mail_username'],
            'mail.password'     => $settings['mail_password'],
            'mail.from.address' => $settings['mail_from_address'],
            'mail.from.name'    => $settings['mail_from_name'],
        ]);

        try {
            $request->user()->sendEmailVerificationNotification();

            // Update timestamp to track when verification was sent
            $user->touch(); // Updates updated_at timestamp

            \Log::info("New email verification sent", [
                'user_id' => $user->id,
                'email' => $userEmail,
                'ip' => $request->ip()
            ]);

        } catch (\Throwable $th) {
            \Log::error("Email verification failed", [
                'user_id' => $user->id,
                'email' => $userEmail,
                'error' => $th->getMessage(),
                'ip' => $request->ip()
            ]);

            return redirect()->back()->with('error', __($th->getMessage()));
        }

        return back()->with('status', 'verification-link-sent');
    }

    /**
     * Check if there's a queued email for the recipient
     */
    private function checkQueuedEmail($email)
    {
        try {
            // Get queue list and search for this email
            $output = shell_exec('exim -bp 2>/dev/null');
            if (!$output) {
                return false;
            }

            $lines = explode("\n", $output);
            $messageId = null;
            $foundEmail = false;

            foreach ($lines as $line) {
                // Look for message ID line (starts with space and time, contains message ID)
                if (preg_match('/^\s+\d+[mhd]\s+([a-zA-Z0-9-]+)\s/', $line, $matches)) {
                    $messageId = $matches[1];
                    $foundEmail = false;
                }
                // Look for recipient line containing our email
                elseif ($messageId && strpos($line, $email) !== false) {
                    $foundEmail = true;
                    break;
                }
            }

            if ($foundEmail && $messageId) {
                return [
                    'message_id' => $messageId,
                    'email' => $email
                ];
            }

            return false;

        } catch (\Exception $e) {
            \Log::error("Error checking email queue: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Force retry of a specific queued email
     */
    private function forceRetryQueuedEmail($messageId)
    {
        try {
            // Force immediate delivery attempt
            $command = "exim -M " . escapeshellarg($messageId) . " 2>&1";
            $output = shell_exec($command);

            // Check if command was successful
            if ($output === null || strpos($output, 'Message') === false) {
                return [
                    'success' => false,
                    'error' => 'Failed to execute retry command: ' . $output
                ];
            }

            return [
                'success' => true,
                'output' => $output
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Alternative method: Get queued emails for specific recipient using exiqgrep
     */
    private function getQueuedEmailsForRecipient($email)
    {
        try {
            // Use exiqgrep to find messages for specific recipient
            $command = "exiqgrep -r " . escapeshellarg($email) . " 2>/dev/null";
            $output = shell_exec($command);

            if (!$output) {
                return [];
            }

            $messageIds = array_filter(explode("\n", trim($output)));
            return $messageIds;

        } catch (\Exception $e) {
            \Log::error("Error getting queued emails: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Force retry all queued emails for a recipient
     */
    private function forceRetryAllQueuedEmails($email)
    {
        try {
            $messageIds = $this->getQueuedEmailsForRecipient($email);

            if (empty($messageIds)) {
                return false;
            }

            $results = [];
            foreach ($messageIds as $messageId) {
                $result = $this->forceRetryQueuedEmail($messageId);
                $results[$messageId] = $result;
            }

            return $results;

        } catch (\Exception $e) {
            \Log::error("Error retrying queued emails: " . $e->getMessage());
            return false;
        }
    }
}
