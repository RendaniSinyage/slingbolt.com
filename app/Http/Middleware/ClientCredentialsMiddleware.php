<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Passport\TokenRepository;
use Laravel\Passport\Passport;

class ClientCredentialsMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        try {
            // Check token existence via guard
            $tokenString = $request->bearerToken();


            if (!$token) {
                return response()->json([
                    'error' => 'missing_token',
                    'message' => 'Access token not found'
                ], 401);
            }

            // Get token ID from parsed token
            $tokenId = $token->getClaim('jti'); // 'jti' is the token ID

            // Retrieve token from DB
            $tokenRepository = app(TokenRepository::class);
            $accessToken = $tokenRepository->find($tokenId);

            if (!$accessToken) {
                return response()->json([
                    'error' => 'invalid_token',
                    'message' => 'Token not found in database'
                ], 401);
            }

            if ($accessToken->revoked) {
                return response()->json([
                    'error' => 'token_revoked',
                    'message' => 'Token revoked'
                ], 401);
            }

            if ($accessToken->expires_at && $accessToken->expires_at->isPast()) {
                return response()->json([
                    'error' => 'token_expired',
                    'message' => 'Token expired'
                ], 401);
            }

            if ($accessToken->user_id) {
                return response()->json([
                    'error' => 'invalid_token_type',
                    'message' => 'User token not allowed for this endpoint'
                ], 401);
            }

            $request->merge([
                'oauth_client_id' => $accessToken->client_id,
                'oauth_scopes' => $accessToken->scopes ?? [],
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
