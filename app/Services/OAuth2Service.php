<?php

namespace App\Services;

use App\Models\Utility;
use Illuminate\Support\Facades\Http;

class OAuth2Service
{
    /**
     * Check if Google Calendar is connected
     */
    public static function isGoogleCalendarConnected()
    {
        $settings = Utility::settings();
        $isEnabled = isset($settings['google_calendar_enable']) && $settings['google_calendar_enable'] === 'on';
        $hasFile = isset($settings['google_calender_json_file']) && !empty($settings['google_calender_json_file']);
        $fileExists = $hasFile ? file_exists(storage_path($settings['google_calender_json_file'])) : false;
        
        return $isEnabled && $hasFile && $fileExists;
    }

    /**
     * Check if provider is connected
     */
    public static function isConnected($provider)
    {
        if ($provider === 'google') {
            return self::isGoogleCalendarConnected();
        }
        
        $settings = Utility::settings();
        return isset($settings[$provider . '_connected']) && $settings[$provider . '_connected'] === '1';
    }

    /**
     * Check if Google Calendar was connected via OAuth2
     */
    public static function isGoogleCalendarOAuth()
    {
        $settings = Utility::settings();
        return isset($settings['google_calendar_oauth_connected']) && $settings['google_calendar_oauth_connected'] === '1';
    }

    /**
     * Get access token for provider (non-Google)
     */
    public static function getAccessToken($provider)
    {
        if ($provider === 'google') {
            // For Google, spatie package handles tokens via files
            return null;
        }
        
        $settings = Utility::settings();
        return $settings[$provider . '_access_token'] ?? null;
    }

    /**
     * Make authenticated API request
     */
    public static function makeAuthenticatedRequest($provider, $method, $url, $data = [])
    {
        if ($provider === 'google') {
            throw new \Exception('Use spatie/laravel-google-calendar package for Google Calendar API calls');
        }
        
        $token = self::getAccessToken($provider);
        
        if (!$token) {
            throw new \Exception("No access token found for {$provider}");
        }
        
        $headers = [
            'Authorization' => 'Bearer ' . $token,
            'Content-Type' => 'application/json',
        ];
        
        return Http::withHeaders($headers)->$method($url, $data);
    }

    /**
     * Get connection status for all providers
     */
    public static function getConnectionStatus()
    {
        return [
            'google' => self::isConnected('google'),
            'slack' => self::isConnected('slack'),
            'zoom' => self::isConnected('zoom'),
        ];
    }

    /**
     * Get user info for connected provider
     */
    public static function getUserInfo($provider)
    {
        $settings = Utility::settings();
        
        switch ($provider) {
            case 'google':
                return [
                    'email' => $settings['google_calendar_user_email'] ?? null,
                    'name' => $settings['google_calendar_user_name'] ?? null,
                    'oauth' => self::isGoogleCalendarOAuth(),
                ];
            case 'slack':
                return [
                    'user_id' => $settings['slack_user_id'] ?? null,
                    'team_id' => $settings['slack_team_id'] ?? null,
                ];
            case 'zoom':
                return [
                    'user_id' => $settings['zoom_user_id'] ?? null,
                ];
            default:
                return null;
        }
    }
}

?>