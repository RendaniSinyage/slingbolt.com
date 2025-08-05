<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Plan;
use App\Models\Utility;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

class ExternalUserController extends Controller
{
    /**
     * Check if a user exists by email and return user details
     */
    public function checkUser(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => 'validation_failed',
                'message' => $validator->errors()->first()
            ], 400);
        }

        $user = User::where('email', $request->email)->first();
        
        if (!$user) {
            return response()->json([
                'exists' => false,
                'message' => 'No account found with this email'
            ]);
        }

        // Check if it's a company user (only company users can be linked to external platforms)
        if ($user->type !== 'company') {
            return response()->json([
                'exists' => true,
                'error' => 'email_exists_different_type',
                'user_type' => $user->type,
                'message' => 'This email is already registered as a ' . $user->type . ' user in ERPGo. Please use a different email or contact support.',
                'can_link' => false
            ], 409);
        }

        // Check if already linked to external platform
        $alreadyLinked = !empty($user->external_platform) && !empty($user->external_id);

        return response()->json([
            'exists' => true,
            'user_id' => $user->id,
            'user_type' => $user->type,
            'user_name' => $user->name,
            'already_linked' => $alreadyLinked,
            'external_platform' => $user->external_platform,
            'can_link' => true,
            'message' => $alreadyLinked ? 'Account already linked to external platform' : 'Account found and can be linked'
        ]);
    }

    /**
     * Link an existing ERPGo user to an external platform (Foodyman)
     */
    public function linkExistingSeller(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'external_id' => 'required',
            'external_platform' => 'string|in:foodyman'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => 'validation_failed',
                'message' => $validator->errors()->first()
            ], 400);
        }

        $user = User::where('email', $request->email)->first();
        
        if (!$user) {
            return response()->json([
                'error' => 'user_not_found',
                'message' => 'No user found with this email'
            ], 404);
        }

        if ($user->type !== 'company') {
            return response()->json([
                'error' => 'invalid_user_type',
                'message' => 'Only company users can be linked to external platforms'
            ], 403);
        }

        // Check if already linked to a different external platform
        if (!empty($user->external_platform) && $user->external_platform !== $request->external_platform) {
            return response()->json([
                'error' => 'already_linked_different_platform',
                'message' => 'This account is already linked to ' . $user->external_platform
            ], 409);
        }

        // Link the existing user to external platform
        $user->update([
            'external_platform' => $request->external_platform ?? 'foodyman',
            'external_id' => $request->external_id,
            'updated_at' => now()
        ]);

        Log::info("Linked existing ERPGo user {$user->id} to external platform", [
            'user_id' => $user->id,
            'email' => $user->email,
            'external_platform' => $request->external_platform,
            'external_id' => $request->external_id
        ]);

        return response()->json([
            'success' => true,
            'user_id' => $user->id,
            'message' => 'Successfully linked existing account to external platform'
        ]);
    }

    /**
     * Create a new company user account for external platform integration
     */
    public function createSellerCompany(Request $request)
    {
        // First check if email exists with different type
        $existingUser = User::where('email', $request->email)->first();
        
        if ($existingUser && $existingUser->type !== 'company') {
            return response()->json([
                'success' => false,
                'error' => 'email_exists_different_type',
                'existing_type' => $existingUser->type,
                'message' => 'This email is already registered as a ' . $existingUser->type . ' user in ERPGo. Please use a different email to connect.'
            ], 409);
        }

        $validator = Validator::make($request->all(), [
            'email' => 'required|email|unique:users,email,' . ($existingUser ? $existingUser->id : 'NULL'),
            'first_name' => 'nullable|string|max:120',
            'last_name' => 'nullable|string|max:120',
            'restaurant_name' => 'required|string|max:120',
            'external_id' => 'required',
            'password' => 'nullable|string|min:6',
            'external_platform' => 'string|in:foodyman'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => 'validation_failed',
                'message' => $validator->errors()->first()
            ], 400);
        }

        try {
            // Build user name from first_name and last_name
            $firstName = trim($request->first_name ?? '');
            $lastName = trim($request->last_name ?? '');
            
            if (!empty($firstName) && !empty($lastName)) {
                $name = $firstName . ' ' . $lastName;
            } elseif (!empty($firstName)) {
                $name = $firstName;
            } elseif (!empty($lastName)) {
                $name = $lastName;
            } else {
                $name = $request->restaurant_name; // Fallback to restaurant name
            }

            // Generate password if not provided
            $password = $request->password ?? $this->generateSecurePassword();
            $settings = Utility::settings();
            $default_language = \DB::table('settings')
                ->select('value')
                ->where('name', 'default_language')
                ->where('created_by', '=', 1) // Super admin settings
                ->first();

            // Generate unique referral code
            do {
                $code = rand(100000, 999999);
            } while (User::where('referral_code', $code)->exists());

            // Create the company user
            $user = new User();
            $user->name = $name;
            $user->email = $request->email;
            $user->password = Hash::make($password);
            $user->type = 'company';
            $user->default_pipeline = 1;
            $user->plan = Plan::first()->id;
            $user->lang = !empty($default_language) ? $default_language->value : 'en';
            $user->referral_code = $code;
            $user->created_by = 1; // Super admin
            $user->email_verified_at = now(); // Auto-verify external users
            $user->is_enable_login = 1;
            $user->registration_ip = $request->ip();
            $user->user_agent = $request->userAgent();
            
            // Store external platform reference
            $user->external_platform = $request->external_platform ?? 'foodyman';
            $user->external_id = $request->external_id;

            $user->save();

            // Assign company role
            $role_r = Role::findByName('company');
            $user->assignRole($role_r);

            Log::info("Created ERPGo user for external platform", [
                'user_id' => $user->id,
                'email' => $user->email,
                'external_platform' => $user->external_platform,
                'external_id' => $user->external_id
            ]);

            // Clone company defaults (your existing method)
            $user->cloneCompanyDefaults($user->id);

            Log::info("Finished cloning company defaults for external user: " . $user->id);

            return response()->json([
                'success' => true,
                'user_id' => $user->id,
                'email' => $user->email,
                'name' => $user->name,
                'generated_password' => $request->password ? null : $password, // Only return if auto-generated
                'message' => 'Company account created successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to create ERPGo company account: ' . $e->getMessage(), [
                'email' => $request->email,
                'external_id' => $request->external_id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'creation_failed',
                'message' => 'Failed to create company account. Please try again.'
            ], 500);
        }
    }

    /**
     * Get user info by external platform reference
     */
    public function getUserByExternalId(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'external_id' => 'required',
            'external_platform' => 'required|string|in:foodyman'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => 'validation_failed',
                'message' => $validator->errors()->first()
            ], 400);
        }

        $user = User::where('external_platform', $request->external_platform)
                   ->where('external_id', $request->external_id)
                   ->first();

        if (!$user) {
            return response()->json([
                'exists' => false,
                'message' => 'No linked account found'
            ], 404);
        }

        return response()->json([
            'exists' => true,
            'user_id' => $user->id,
            'email' => $user->email,
            'name' => $user->name,
            'type' => $user->type,
            'is_active' => $user->is_enable_login,
            'linked_at' => $user->updated_at
        ]);
    }

    /**
     * Update external user information
     */
    public function updateExternalUser(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'external_id' => 'required',
            'external_platform' => 'required|string|in:foodyman',
            'email' => 'sometimes|email',
            'first_name' => 'sometimes|string|max:120',
            'last_name' => 'sometimes|string|max:120',
            'restaurant_name' => 'sometimes|string|max:120'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => 'validation_failed',
                'message' => $validator->errors()->first()
            ], 400);
        }

        $user = User::where('external_platform', $request->external_platform)
                   ->where('external_id', $request->external_id)
                   ->first();

        if (!$user) {
            return response()->json([
                'error' => 'user_not_found',
                'message' => 'No linked account found'
            ], 404);
        }

        // Update user information if provided
        $updates = [];
        
        if ($request->has('email') && $request->email !== $user->email) {
            // Check if new email already exists
            if (User::where('email', $request->email)->where('id', '!=', $user->id)->exists()) {
                return response()->json([
                    'error' => 'email_exists',
                    'message' => 'This email is already in use'
                ], 409);
            }
            $updates['email'] = $request->email;
        }

        if ($request->has('first_name') || $request->has('last_name')) {
            $firstName = trim($request->first_name ?? '');
            $lastName = trim($request->last_name ?? '');
            
            if (!empty($firstName) && !empty($lastName)) {
                $updates['name'] = $firstName . ' ' . $lastName;
            } elseif (!empty($firstName)) {
                $updates['name'] = $firstName;
            } elseif (!empty($lastName)) {
                $updates['name'] = $lastName;
            }
        }

        if (!empty($updates)) {
            $user->update($updates);
            
            Log::info("Updated external user information", [
                'user_id' => $user->id,
                'external_id' => $request->external_id,
                'updates' => $updates
            ]);
        }

        return response()->json([
            'success' => true,
            'user_id' => $user->id,
            'message' => 'User information updated successfully'
        ]);
    }

    /**
     * Disconnect external platform integration
     */
    public function disconnectExternalUser(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'external_id' => 'required',
            'external_platform' => 'required|string|in:foodyman'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => 'validation_failed',
                'message' => $validator->errors()->first()
            ], 400);
        }

        $user = User::where('external_platform', $request->external_platform)
                   ->where('external_id', $request->external_id)
                   ->first();

        if (!$user) {
            return response()->json([
                'error' => 'user_not_found',
                'message' => 'No linked account found'
            ], 404);
        }

        // Clear external platform links but keep the ERPGo account
        $user->update([
            'external_platform' => null,
            'external_id' => null,
            'updated_at' => now()
        ]);

        Log::info("Disconnected external user", [
            'user_id' => $user->id,
            'email' => $user->email,
            'external_platform' => $request->external_platform,
            'external_id' => $request->external_id
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Successfully disconnected from external platform. ERPGo account remains active.'
        ]);
    }

    /**
     * Generate a secure password for new users
     */
    private function generateSecurePassword()
    {
        // Generate a 14-character password with mixed case, numbers and symbols
        $password = Str::random(8) . rand(100, 999) . '!@#';
        return str_shuffle($password);
    }

    /**
     * Validate external platform request
     */
    private function validateExternalRequest(Request $request)
    {
        // Additional validation for external requests
        $clientId = $request->header('X-Client-ID');
        $clientSecret = $request->header('X-Client-Secret');
        
        // Verify client credentials if needed
        // This adds an extra layer of security for external API calls
        
        return true; // Implement your validation logic
    }

    /**
     * Get external user statistics
     */
    public function getExternalUserStats(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'external_platform' => 'required|string|in:foodyman'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => 'validation_failed',
                'message' => $validator->errors()->first()
            ], 400);
        }

        $stats = User::where('external_platform', $request->external_platform)
                    ->selectRaw('
                        COUNT(*) as total_linked_users,
                        COUNT(CASE WHEN is_enable_login = 1 THEN 1 END) as active_users,
                        COUNT(CASE WHEN created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY) THEN 1 END) as recent_connections
                    ')
                    ->first();

        return response()->json([
            'success' => true,
            'platform' => $request->external_platform,
            'stats' => $stats
        ]);
    }

    /**
     * Bulk sync external users (for data synchronization)
     */
    public function bulkSyncExternalUsers(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'external_platform' => 'required|string|in:foodyman',
            'users' => 'required|array',
            'users.*.external_id' => 'required',
            'users.*.email' => 'required|email',
            'users.*.first_name' => 'nullable|string|max:120',
            'users.*.last_name' => 'nullable|string|max:120',
            'users.*.restaurant_name' => 'required|string|max:120'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => 'validation_failed',
                'message' => $validator->errors()->first()
            ], 400);
        }

        $results = [];
        $successCount = 0;
        $errorCount = 0;

        foreach ($request->users as $userData) {
            try {
                // Check if user already exists
                $existingUser = User::where('email', $userData['email'])->first();
                
                if ($existingUser) {
                    if ($existingUser->type !== 'company') {
                        $results[] = [
                            'external_id' => $userData['external_id'],
                            'email' => $userData['email'],
                            'status' => 'error',
                            'message' => 'Email exists as ' . $existingUser->type . ' user'
                        ];
                        $errorCount++;
                        continue;
                    }

                    // Link existing company user
                    $existingUser->update([
                        'external_platform' => $request->external_platform,
                        'external_id' => $userData['external_id']
                    ]);

                    $results[] = [
                        'external_id' => $userData['external_id'],
                        'email' => $userData['email'],
                        'status' => 'linked',
                        'user_id' => $existingUser->id
                    ];
                    $successCount++;
                } else {
                    // Create new user using the same logic as createSellerCompany
                    $createRequest = new Request($userData + ['external_platform' => $request->external_platform]);
                    $createResponse = $this->createSellerCompany($createRequest);
                    
                    if ($createResponse->getStatusCode() === 200) {
                        $data = json_decode($createResponse->getContent(), true);
                        $results[] = [
                            'external_id' => $userData['external_id'],
                            'email' => $userData['email'],
                            'status' => 'created',
                            'user_id' => $data['user_id']
                        ];
                        $successCount++;
                    } else {
                        $results[] = [
                            'external_id' => $userData['external_id'],
                            'email' => $userData['email'],
                            'status' => 'error',
                            'message' => 'Failed to create account'
                        ];
                        $errorCount++;
                    }
                }
            } catch (\Exception $e) {
                $results[] = [
                    'external_id' => $userData['external_id'],
                    'email' => $userData['email'],
                    'status' => 'error',
                    'message' => $e->getMessage()
                ];
                $errorCount++;
            }
        }

        return response()->json([
            'success' => true,
            'total_processed' => count($request->users),
            'success_count' => $successCount,
            'error_count' => $errorCount,
            'results' => $results
        ]);
    }
}