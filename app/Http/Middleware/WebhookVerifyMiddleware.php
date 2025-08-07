<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class WebhookVerifyMiddleware
{
    /**
     * Handle an incoming webhook request.
     * 
     * This middleware verifies webhook signatures to ensure they come from
     * trusted external platforms like Foodyman.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        // Get webhook secret from config
        $webhookSecret = config('services.external_webhooks.secret');
        
        if (empty($webhookSecret)) {
            \Log::warning('Webhook secret not configured');
            return response()->json([
                'error' => 'webhook_not_configured',
                'message' => 'Webhook verification not properly configured'
            ], 500);
        }

        // Get signature from header
        $signature = $request->header('X-Webhook-Signature');
        
        if (!$signature) {
            return response()->json([
                'error' => 'missing_signature',
                'message' => 'Webhook signature is required'
            ], 401);
        }

        // Get timestamp for replay attack protection
        $timestamp = $request->header('X-Webhook-Timestamp');
        $currentTime = time();
        
        if (!$timestamp || abs($currentTime - $timestamp) > 300) { // 5 minutes tolerance
            return response()->json([
                'error' => 'invalid_timestamp',
                'message' => 'Webhook timestamp is invalid or too old'
            ], 401);
        }

        // Get request body
        $payload = $request->getContent();
        
        // Calculate expected signature
        $expectedSignature = hash_hmac('sha256', $timestamp . '.' . $payload, $webhookSecret);
        
        // Verify signature
        if (!hash_equals($expectedSignature, $signature)) {
            \Log::warning('Invalid webhook signature', [
                'expected' => $expectedSignature,
                'received' => $signature,
                'source_ip' => $request->ip()
            ]);
            
            return response()->json([
                'error' => 'invalid_signature',
                'message' => 'Webhook signature verification failed'
            ], 401);
        }

        // Add verified flag to request
        $request->merge(['webhook_verified' => true]);

        return $next($request);
    }
}