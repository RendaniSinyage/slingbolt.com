<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Laravel\Passport\Token;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class ClientCredentialsMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        try {
            // Check for Authorization header
            $authorization = $request->header('Authorization');
            
            if (!$authorization) {
                return response()->json([
                    'error' => 'missing_authorization',
                    'message' => 'Authorization header is required'
                ], 401);
            }

            // Extract bearer token
            if (!preg_match('/Bearer\s+(.*)$/i', $authorization, $matches)) {
                return response()->json([
                    'error' => 'invalid_authorization_format',
                    'message' => 'Authorization header must be in Bearer format'
                ], 401);
            }

            $jwtToken = $matches[1];

            // Decode JWT to get the jti (token ID)
            try {
                // Get JWT payload without verification for now (just to extract jti)
                $parts = explode('.', $jwtToken);
                if (count($parts) !== 3) {
                    throw new \Exception('Invalid JWT format');
                }
                
                $payload = json_decode(base64_decode($parts[1]), true);
                $tokenId = $payload['jti'] ?? null;
                
                if (!$tokenId) {
                    throw new \Exception('Token ID not found in JWT');
                }
            } catch (\Exception $e) {
                return response()->json([
                    'error' => 'invalid_jwt',
                    'message' => 'Invalid JWT token format'
                ], 401);
            }

            // Find the token in database using the jti
            $accessToken = Token::find($tokenId);
            
            if (!$accessToken) {
                return response()->json([
                    'error' => 'invalid_token',
                    'message' => 'Invalid or expired access token'
                ], 401);
            }

            // Check if token is expired
            if ($accessToken->expires_at && $accessToken->expires_at->isPast()) {
                return response()->json([
                    'error' => 'token_expired',
                    'message' => 'Access token has expired'
                ], 401);
            }

            // Check if token is revoked
            if ($accessToken->revoked) {
                return response()->json([
                    'error' => 'token_revoked',
                    'message' => 'Access token has been revoked'
                ], 401);
            }

            // Verify it's a client credentials token (no user_id)
            if ($accessToken->user_id) {
                return response()->json([
                    'error' => 'invalid_token_type',
                    'message' => 'This endpoint requires client credentials token'
                ], 401);
            }

            // Add client info to request for controllers
            $request->merge([
                'oauth_client_id' => $accessToken->client_id,
                'oauth_token_id' => $accessToken->id,
                'oauth_scopes' => $accessToken->scopes ?? []
            ]);

            return $next($request);

        } catch (\Exception $e) {
            \Log::error('Client credentials middleware error: ' . $e->getMessage());
            
            return response()->json([
                'error' => 'authentication_error',
                'message' => 'Authentication failed'
            ], 500);
        }
    }
}