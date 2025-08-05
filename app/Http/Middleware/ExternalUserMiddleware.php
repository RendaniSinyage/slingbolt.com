<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class ExternalUserMiddleware
{
    /**
     * Handle an incoming request.
     * 
     * This middleware ensures that only users linked to external platforms
     * can access external API endpoints.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        // Check if user exists and is authenticated
        if (!$user) {
            return response()->json([
                'error' => 'unauthenticated',
                'message' => 'User not authenticated'
            ], 401);
        }

        // Check if user is linked to an external platform
        if (empty($user->external_platform) || empty($user->external_id)) {
            return response()->json([
                'error' => 'not_external_user',
                'message' => 'This endpoint is only accessible to users linked from external platforms'
            ], 403);
        }

        // Check if user account is active
        if (!$user->is_enable_login) {
            return response()->json([
                'error' => 'account_disabled',
                'message' => 'Your ERPGo account has been disabled. Please contact support.'
            ], 403);
        }

        // Check if user is a company type (external users should always be company type)
        if ($user->type !== 'company') {
            return response()->json([
                'error' => 'invalid_user_type',
                'message' => 'External platform access is only available for company accounts'
            ], 403);
        }

        // Add external platform info to request for controllers
        $request->merge([
            'external_platform' => $user->external_platform,
            'external_id' => $user->external_id
        ]);

        return $next($request);
    }
}