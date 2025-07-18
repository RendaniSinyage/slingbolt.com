<?php

// 1. Create NEW OAuth2Controller
// php artisan make:controller OAuth2Controller

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Laravel\Socialite\Facades\Socialite;
use App\Models\User;
use App\Models\Utility;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class OAuth2Controller extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'XSS']);
    }

    /**
     * Redirect to OAuth provider
     */
    public function redirectToProvider($provider)
    {
        $validProviders = ['google', 'slack', 'zoom'];
        
        if (!in_array($provider, $validProviders)) {
            return redirect()->back()->with('error', 'Invalid OAuth provider');
        }

        // Store user ID in session
        session(['oauth_user_id' => Auth::id(), 'oauth_provider' => $provider]);
        
        $scopes = $this->getProviderScopes($provider);
        
        return Socialite::driver($provider)->scopes($scopes)->redirect();
    }

    /**
     * Handle Google Calendar OAuth callback
     * This mimics what SystemController::saveGoogleCalenderSettings() does
     */
    public function handleGoogleCallback(Request $request)
    {
        try {
            $socialUser = Socialite::driver('google')->user();
            $userId = session('oauth_user_id');
            
            if (!$userId) {
                return redirect()->route('system.settings')->with('error', 'OAuth session expired. Please try again.');
            }

            // Use the SAME logic as SystemController for file creation
            $this->createGoogleCalendarFile($socialUser, $userId);
            
            return redirect()->route('system.settings')->with('success', 'Google Calendar connected successfully via OAuth2!');
            
        } catch (\Exception $e) {
            \Log::error('Google OAuth callback error: ' . $e->getMessage());
            return redirect()->route('system.settings')->with('error', 'Google Calendar connection failed: ' . $e->getMessage());
        }
    }

    /**
     * Create Google Calendar file using SAME logic as SystemController
     */
    private function createGoogleCalendarFile($socialUser, $userId)
    {
        // EXACT same directory creation logic as SystemController
        $dir = storage_path() . '/' . md5(time());
        if (!is_dir($dir)) {
            File::makeDirectory($dir, $mode = 0777, true, true);
        }

        // EXACT same file path logic as SystemController
        $file_path = md5(time()) . '/' . md5(time()) . '.json';
        $fullPath = storage_path($file_path);

        // Create OAuth2 credentials file (this replaces the uploaded file)
        $credentials = $this->generateGoogleCredentialsJson($socialUser);
        file_put_contents($fullPath, json_encode($credentials, JSON_PRETTY_PRINT));

        // EXACT same database insertion logic as SystemController
        $post = [
            'google_calendar_enable' => 'on',
            'google_calender_json_file' => $file_path, // Same field name
            'google_clender_id' => $this->extractCalendarId($socialUser) ?: 'primary',
            'google_calendar_oauth_connected' => '1', // Flag to track OAuth2 vs manual
            'google_calendar_user_email' => $socialUser->getEmail(),
        ];

        foreach ($post as $key => $data) {
            DB::insert(
                'insert into settings (`value`, `name`,`created_by`,`created_at`,`updated_at`) values (?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE `value` = VALUES(`value`) ',
                [
                    $data,
                    $key,
                    Auth::user()->creatorId(), // SAME logic as SystemController
                    date('Y-m-d H:i:s'),
                    date('Y-m-d H:i:s'),
                ]
            );
        }
    }

    /**
     * Generate Google credentials JSON (replaces uploaded file)
     */
    private function generateGoogleCredentialsJson($socialUser)
    {
        return [
            'type' => 'authorized_user',
            'client_id' => config('services.google.client_id'),
            'client_secret' => config('services.google.client_secret'),
            'access_token' => $socialUser->token,
            'refresh_token' => $socialUser->refreshToken,
            'scope' => 'https://www.googleapis.com/auth/calendar',
            'token_type' => 'Bearer',
            'expires_in' => $socialUser->expiresIn,
            'expires_at' => time() + ($socialUser->expiresIn ?? 3600),
            
            // Additional metadata
            'oauth2_connected' => true,
            'user_email' => $socialUser->getEmail(),
            'connected_at' => date('Y-m-d H:i:s'),
        ];
    }

    /**
     * Extract calendar ID from user (you might need to make an API call)
     */
    private function extractCalendarId($socialUser)
    {
        // Could make API call to get user's primary calendar ID
        // For now, return 'primary' which works for most users
        return 'primary';
    }

    /**
     * Handle Slack OAuth callback
     */
    public function handleSlackCallback(Request $request)
    {
        try {
            $socialUser = Socialite::driver('slack')->user();
            $userId = session('oauth_user_id');
            
            if (!$userId) {
                return redirect()->route('system.settings')->with('error', 'OAuth session expired.');
            }

            $this->storeSlackTokens($socialUser, $userId);
            
            return redirect()->route('system.settings')->with('success', 'Slack connected successfully!');
            
        } catch (\Exception $e) {
            \Log::error('Slack OAuth error: ' . $e->getMessage());
            return redirect()->route('system.settings')->with('error', 'Slack connection failed: ' . $e->getMessage());
        }
    }

    /**
     * Handle Zoom OAuth callback
     */
    public function handleZoomCallback(Request $request)
    {
        try {
            $socialUser = Socialite::driver('zoom')->user();
            $userId = session('oauth_user_id');
            
            if (!$userId) {
                return redirect()->route('system.settings')->with('error', 'OAuth session expired.');
            }

            $this->storeZoomTokens($socialUser, $userId);
            
            return redirect()->route('system.settings')->with('success', 'Zoom connected successfully!');
            
        } catch (\Exception $e) {
            \Log::error('Zoom OAuth error: ' . $e->getMessage());
            return redirect()->route('system.settings')->with('error', 'Zoom connection failed: ' . $e->getMessage());
        }
    }

    /**
     * Store Slack tokens
     */
    private function storeSlackTokens($socialUser, $userId)
    {
        $settings = [
            'slack_access_token' => $socialUser->token,
            'slack_refresh_token' => $socialUser->refreshToken,
            'slack_team_id' => $socialUser->user['team']['id'] ?? null,
            'slack_user_id' => $socialUser->getId(),
            'slack_connected' => '1',
        ];

        $this->saveSettings($settings, $userId);
    }

    /**
     * Store Zoom tokens  
     */
    private function storeZoomTokens($socialUser, $userId)
    {
        $settings = [
            'zoom_access_token' => $socialUser->token,
            'zoom_refresh_token' => $socialUser->refreshToken,
            'zoom_user_id' => $socialUser->getId(),
            'zoom_connected' => '1',
        ];

        $this->saveSettings($settings, $userId);
    }

    /**
     * Generic settings saver
     */
    private function saveSettings($settings, $userId)
    {
        foreach ($settings as $key => $value) {
            if ($value !== null) {
                DB::insert(
                    'INSERT INTO settings (`value`, `name`, `created_by`, `created_at`, `updated_at`) 
                     VALUES (?, ?, ?, ?, ?) 
                     ON DUPLICATE KEY UPDATE `value` = VALUES(`value`), `updated_at` = VALUES(`updated_at`)',
                    [
                        $value,
                        $key,
                        Auth::user()->creatorId(),
                        date('Y-m-d H:i:s'),
                        date('Y-m-d H:i:s'),
                    ]
                );
            }
        }
    }

    /**
     * Disconnect OAuth provider
     */
    public function disconnect($provider)
    {
        try {
            $userId = Auth::user()->creatorId();
            
            switch ($provider) {
                case 'google':
                    $this->disconnectGoogle($userId);
                    break;
                case 'slack':
                    $this->disconnectSlack($userId);
                    break;
                case 'zoom':
                    $this->disconnectZoom($userId);
                    break;
                default:
                    return redirect()->back()->with('error', 'Invalid provider');
            }
            
            return redirect()->back()->with('success', ucfirst($provider) . ' disconnected successfully!');
            
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Disconnect failed: ' . $e->getMessage());
        }
    }

    /**
     * Disconnect Google Calendar (remove file and settings)
     */
    private function disconnectGoogle($userId)
    {
        $settings = Utility::settings();
        $filePath = $settings['google_calender_json_file'] ?? null;
        
        // Remove file (same logic as manual removal would need)
        if ($filePath && file_exists(storage_path($filePath))) {
            unlink(storage_path($filePath));
            
            // Remove directory if empty
            $dir = dirname(storage_path($filePath));
            if (is_dir($dir) && count(scandir($dir)) == 2) {
                rmdir($dir);
            }
        }
        
        // Remove settings
        DB::table('settings')
            ->where('created_by', $userId)
            ->whereIn('name', [
                'google_calendar_enable',
                'google_calender_json_file', 
                'google_clender_id',
                'google_calendar_oauth_connected',
                'google_calendar_user_email'
            ])
            ->delete();
    }

    /**
     * Disconnect Slack
     */
    private function disconnectSlack($userId)
    {
        DB::table('settings')
            ->where('created_by', $userId)
            ->whereIn('name', [
                'slack_access_token',
                'slack_refresh_token',
                'slack_team_id',
                'slack_user_id',
                'slack_connected'
            ])
            ->delete();
    }

    /**
     * Disconnect Zoom
     */
    private function disconnectZoom($userId)
    {
        DB::table('settings')
            ->where('created_by', $userId)
            ->whereIn('name', [
                'zoom_access_token',
                'zoom_refresh_token', 
                'zoom_user_id',
                'zoom_connected'
            ])
            ->delete();
    }

    /**
     * Get required scopes for each provider
     */
    private function getProviderScopes($provider)
    {
        $scopes = [
            'google' => [
                'https://www.googleapis.com/auth/calendar',
                'https://www.googleapis.com/auth/calendar.events'
            ],
            'slack' => [
                'channels:read',
                'chat:write',
                'users:read',
                'team:read'
            ],
            'zoom' => [
                'meeting:write',
                'meeting:read',
                'user:read'
            ]
        ];

        return $scopes[$provider] ?? [];
    }

    /**
     * Test OAuth connection
     */
    public function testConnection($provider)
    {
        try {
            switch ($provider) {
                case 'google':
                    return $this->testGoogleConnection();
                case 'slack':
                    return $this->testSlackConnection();
                case 'zoom':
                    return $this->testZoomConnection();
                default:
                    return response()->json(['success' => false, 'message' => 'Invalid provider']);
            }
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    private function testGoogleConnection()
    {
        if (!Utility::isGoogleCalendarConnected()) {
            return response()->json(['success' => false, 'message' => 'Google Calendar not connected']);
        }

        // Test by trying to get calendar list
        try {
            Utility::googleCalendarConfig();
            $event = new \Spatie\GoogleCalendar\Event;
            $event->name = 'OAuth2 Test - ' . config('app.name');
            $event->startDateTime = now()->addMinute();
            $event->endDateTime = now()->addMinutes(2);
            $event->description = 'Test event created via OAuth2. Safe to delete.';
            
            $result = $event->save();
            return response()->json(['success' => true, 'message' => 'Google Calendar test successful!']);
            
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Test failed: ' . $e->getMessage()]);
        }
    }

    private function testSlackConnection()
    {
        // Implementation for testing Slack connection
        return response()->json(['success' => true, 'message' => 'Slack test - implement as needed']);
    }

    private function testZoomConnection()
    {
        // Implementation for testing Zoom connection  
        return response()->json(['success' => true, 'message' => 'Zoom test - implement as needed']);
    }
}
