<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class OAuth2DebugController extends Controller
{
    /**
     * Debug what Socialite is actually sending to Google
     */
    public function debugGoogleOAuth()
    {
        // Clear caches
        \Artisan::call('config:clear');
        \Artisan::call('cache:clear');

        try {
            // Test 1: Basic Socialite driver
            Log::info('=== SOCIALITE DEBUG TEST ===');

            $driver = Socialite::driver('google');
            Log::info('Driver created successfully');

            // Test 2: Add scopes
            $driver = $driver->scopes([
                'https://www.googleapis.com/auth/calendar',
                'https://www.googleapis.com/auth/calendar.events'
            ]);
            Log::info('Scopes added successfully');

            // Test 3: Try different parameter combinations

            // Option A: Only prompt
            $driverA = clone $driver;
            $driverA = $driverA->with(['prompt' => 'consent']);

            // Option B: Only access_type
            $driverB = clone $driver;
            $driverB = $driverB->with(['access_type' => 'offline']);

            // Option C: Both parameters
            $driverC = clone $driver;
            $driverC = $driverC->with([
                'access_type' => 'offline',
                'prompt' => 'consent'
            ]);

            // Option D: Force select_account
            $driverD = clone $driver;
            $driverD = $driverD->with([
                'access_type' => 'offline',
                'prompt' => 'select_account'
            ]);

            // Get URLs for each option
            $tests = [
                'A_prompt_only' => $driverA,
                'B_access_type_only' => $driverB,
                'C_both_params' => $driverC,
                'D_select_account' => $driverD
            ];

            foreach ($tests as $testName => $testDriver) {
                try {
                    // Use reflection to get the redirect URL without actually redirecting
                    $reflection = new \ReflectionClass($testDriver);
                    $method = $reflection->getMethod('getCodeFields');
                    $method->setAccessible(true);
                    $fields = $method->invoke($testDriver, 'dummy_state');

                    Log::info("Test {$testName} - Parameters:", $fields);

                    // Check for conflicts
                    if (isset($fields['approval_prompt']) && isset($fields['prompt'])) {
                        Log::error("CONFLICT in {$testName}: Both approval_prompt and prompt present!");
                        Log::error("approval_prompt: " . $fields['approval_prompt']);
                        Log::error("prompt: " . $fields['prompt']);
                    }

                } catch (\Exception $e) {
                    Log::error("Test {$testName} failed: " . $e->getMessage());
                }
            }

            // Test 4: Manual URL construction
            $manualParams = [
                'client_id' => config('services.google.client_id'),
                'redirect_uri' => config('services.google.redirect'),
                'scope' => 'https://www.googleapis.com/auth/calendar https://www.googleapis.com/auth/calendar.events',
                'response_type' => 'code',
                'state' => 'test_state',
                'access_type' => 'offline',
                'prompt' => 'consent'
            ];

            $manualUrl = 'https://accounts.google.com/o/oauth2/auth?' . http_build_query($manualParams);
            Log::info('Manual URL: ' . $manualUrl);

            return response()->json([
                'message' => 'Debug completed. Check logs for details.',
                'manual_url' => $manualUrl,
                'config' => [
                    'client_id' => config('services.google.client_id'),
                    'redirect' => config('services.google.redirect'),
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Debug failed: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());

            return response()->json([
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ], 500);
        }
    }

    /**
     * Test minimal Google OAuth without any extra parameters
     */
    public function testMinimalOAuth()
    {
        session(['oauth_user_id' => Auth::id()]);

        // Absolute minimal approach
        return Socialite::driver('google')
            ->scopes(['https://www.googleapis.com/auth/calendar'])
            ->redirect();
    }

    /**
     * Test manual OAuth URL
     */
    public function testManualOAuth()
    {
        $state = \Str::random(40);
        session(['oauth_state' => $state, 'oauth_user_id' => Auth::id()]);

        $params = [
            'client_id' => config('services.google.client_id'),
            'redirect_uri' => config('services.google.redirect'),
            'scope' => 'https://www.googleapis.com/auth/calendar',
            'response_type' => 'code',
            'state' => $state,
            'access_type' => 'offline',
            'prompt' => 'consent'
        ];

        $url = 'https://accounts.google.com/o/oauth2/auth?' . http_build_query($params);

        Log::info('Manual OAuth URL: ' . $url);

        return redirect($url);
    }
}
