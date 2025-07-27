<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use App\Models\User;
use App\Services\TemplateCompanyConfig;

class CompanyRefreshService
{
    private $templateCompanyId;
    private $oldCompanyId;
    private $newCompanyId;
    private $transferLog = [];
    private $refreshSummary = [];
    private $isDryRun = false;
    
    // User-generated data that should be COPIED to new company
    private $userDataTables = [
        // Core user data
        'users',
        'employees',
        'customers', 
        'venders',
        
        // Financial transactions
        'invoices',
        'invoice_products', 
        'bills',
        'bill_products',
        'payments',
        'purchase_payments',
        'transactions',
        'journal_entries',
        'journal_items',
        'revenues',
        'bank_transfers',
        'customer_credits',
        'vender_credits',
        'orders',
        'order_products',
        'pos',
        'pos_products',
        'quotations',
        'quotation_products',
        'purchases',
        'purchase_products',
        
        // CRM data
        'leads',
        'deals',
        'lead_calls',
        'lead_emails', 
        'lead_files',
        'lead_discussions',
        'deal_calls',
        'deal_emails',
        'deal_files',
        'deal_tasks',
        'user_leads',
        'user_deals',
        
        // Project data
        'projects',
        'project_tasks',
        'project_files',
        'project_comments',
        'project_notes',
        'project_users',
        'project_expenses',
        'project_invoices',
        'tasks',
        'task_comments',
        'task_files', 
        'task_checklists',
        'milestones',
        'timesheet',
        'trackers',
        'time_trackers',
        
        // HR data
        'employee_documents',
        'allowances',
        'commissions',
        'other_payments',
        'overtimes',
        'saturation_deductions',
        'loans',
        'leaves',
        'attendance_employees',
        'payslips',
        'set_salaries',
        'pay_slips',
        'appraisals',
        'goal_trackings',
        'trainings',
        'awards',
        'job_applications',
        'job_on_boards',
        'interview_schedules',
        'announcements',
        'holidays',
        'meetings',
        'events',
        'transfers',
        'resignations',
        'travels',
        'promotions',
        'complaints',
        'warnings',
        'terminations',
        'zoom_meetings',
        
        // Inventory data
        'warehouse_products',
        'warehouse_transfers',
        
        // Activity logs
        'activity_logs',
        'login_details',
        'user_todos',
        'notifications',
        
        // Contract activities
        'contracts',
        'contract_attachment',
        'contract_comment',
        'contract_notes',
        
        // Form data
        'form_fields',
        'form_field_responses',
        'form_responses',
        'forms',
        'form_builders',
        
        // Custom data
        'custom_field_values',
        'ducument_uploads',
        
        // Integration data (COPIED - includes all settings/tokens)
        'webhook_settings',
    ];

    // Tables that may need conflict resolution (user may have modified template data)
    private $conflictTables = [
        'chart_of_accounts',
        'product_services', 
        'settings',  // This includes ALL integration settings (Slack, Zoom, Google Calendar, Payment gateways, etc.)
        'bank_accounts',
        'company_payment_settings', // Company-specific payment gateway settings
    ];

    // Fields to compare for conflicts
    private $conflictFields = [
        'chart_of_accounts' => ['name', 'description', 'is_enabled', 'parent'],
        'product_services' => ['name', 'description', 'sale_price', 'purchase_price', 'is_enabled'],
        'settings' => ['value'], // ALL settings including integrations
        'bank_accounts' => ['account_number', 'holder_name', 'bank_name', 'contact_number'],
        'company_payment_settings' => ['value'], // Payment gateway configurations
    ];

    public function __construct()
    {
        // No hardcoded template - will be determined by currency matching
    }

    /**
     * Main refresh method - creates new company and COPIES data
     * 
     * SAFETY: This is a COPY operation for safety!
     * - Template data is cloned to new company
     * - User data is COPIED from old company to new company
     * - Old company is only deleted at the very end if everything succeeds
     * - Multi-currency support: automatically selects template based on currency
     */
    public function refreshCompany($oldCompanyId, $options = [])
    {
        $this->oldCompanyId = $oldCompanyId;
        $this->isDryRun = $options['dry_run'] ?? false;
        
        Log::info("Starting company refresh for company {$this->oldCompanyId}" . 
            ($this->isDryRun ? " (DRY RUN)" : ""));
        
        try {
            return DB::transaction(function () use ($options) {
                // Step 1: Determine template company based on currency
                $this->templateCompanyId = $this->determineTemplateCompany();
                
                // Step 2: Clone template to new company using CompanyClonerService
                $this->newCompanyId = $this->cloneTemplateToNewCompany();
                
                // Step 3: Resolve conflicts for modified template data
                $this->resolveConflictsAndCopy();
                
                // Step 4: Copy all user-generated data (COPY operation for safety)
                $this->copyUserGeneratedData();
                
                // Step 5: Copy users to new company (COPY operation)
                $this->copyUsersToNewCompany();
                
                // Step 6: Update company information
                $this->updateCompanyInformation();
                
                // Step 7: Only delete old company if NOT dry run and everything succeeded
                if (!$this->isDryRun) {
                    $this->deleteOldCompany();
                } else {
                    Log::info("DRY RUN: Old company {$this->oldCompanyId} preserved for testing");
                }
                
                return $this->generateSuccessResponse();
            });
            
        } catch (\Exception $e) {
            Log::error("Company refresh failed: " . $e->getMessage());
            Log::error("Stack trace: " . $e->getTraceAsString());
            
            // Cleanup new company if something went wrong
            if ($this->newCompanyId) {
                $this->cleanupFailedRefresh();
            }
            
            throw $e;
        }
    }

    /**
     * Step 1: Determine template company based on old company's currency
     */
    private function determineTemplateCompany()
    {
        Log::info("Step 1: Determining template company based on currency");
        
        // Get old company's currency
        $oldCompanyCurrency = $this->getCompanyCurrency($this->oldCompanyId);
        
        if (!$oldCompanyCurrency) {
            throw new \Exception("Cannot determine currency for company {$this->oldCompanyId}");
        }
        
        Log::info("Old company currency: {$oldCompanyCurrency}");
        
        // Find template company with matching currency
        $templateCompanyId = $this->findTemplateCompanyByCurrency($oldCompanyCurrency);
        
        if (!$templateCompanyId) {
            throw new \Exception("No template company found for currency: {$oldCompanyCurrency}");
        }
        
        Log::info("Selected template company {$templateCompanyId} for currency {$oldCompanyCurrency}");
        
        return $templateCompanyId;
    }

    /**
     * Get company's currency from settings
     */
    private function getCompanyCurrency($companyId)
    {
        $currency = DB::table('settings')
            ->where('created_by', $companyId)
            ->where('name', 'site_currency')
            ->value('value');
            
        // Use ZAR as default for South African context, or keep USD for global
        return $currency ?: TemplateCompanyConfig::getDefaultCurrency();
    }

    /**
     * Find template company by currency
     */
    private function findTemplateCompanyByCurrency($currency)
    {
        return TemplateCompanyConfig::findTemplateByCurrency($currency);
    }

    /**
     * Get list of template companies (now delegated to config)
     */
    private function getTemplateCompanies()
    {
        return array_keys(TemplateCompanyConfig::getTemplateCompanies());
    }

    /**
     * Step 2: Clone template company to new company using CompanyClonerService
     */
    private function cloneTemplateToNewCompany()
    {
        Log::info("Step 2: Cloning template company {$this->templateCompanyId} to new company");
        
        // Get old company details for the new company
        $oldCompany = User::find($this->oldCompanyId);
        if (!$oldCompany) {
            throw new \Exception("Old company not found: {$this->oldCompanyId}");
        }
        
        // Create new company user record first
        $newCompanyId = DB::table('users')->insertGetId([
            'name' => $oldCompany->name . ($this->isDryRun ? ' (DRY RUN)' : ''),
            'email' => $this->generateTempEmail($oldCompany->email), // Temporary email to avoid conflicts
            'type' => 'company',
            'lang' => $oldCompany->lang ?? 'en',
            'avatar' => $oldCompany->avatar ?? '',
            'plan' => $oldCompany->plan,
            'plan_expire_date' => $oldCompany->plan_expire_date,
            'created_by' => $oldCompany->created_by,
            'is_active' => $oldCompany->is_active,
            'is_disable' => 0, // Temporarily enable during transfer
            'is_enable_login' => 0, // Temporarily disable login
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        
        Log::info("Created new company record with ID: {$newCompanyId}");
        
        // Use CompanyClonerService to clone template data to new company
        // Note: CompanyClonerService constructor is ($targetCompanyId, $sourceCompanyId)
        $cloner = new CompanyClonerService($newCompanyId, $this->templateCompanyId);
        $cloner->cloneAllCompanyData();
        
        Log::info("Successfully cloned template data to new company {$newCompanyId}");
        
        return $newCompanyId;
    }

    /**
     * Generate temporary email to avoid conflicts during testing
     */
    private function generateTempEmail($originalEmail)
    {
        if ($this->isDryRun) {
            return 'dryrun_' . time() . '_' . $originalEmail;
        }
        return 'temp_' . time() . '_' . $originalEmail;
    }

    /**
     * Step 3: Resolve conflicts for template data that user may have modified
     */
    private function resolveConflictsAndCopy()
    {
        Log::info("Step 3: Resolving conflicts and copying modified template data");
        
        foreach ($this->conflictTables as $tableName) {
            if (!Schema::hasTable($tableName) || !Schema::hasColumn($tableName, 'created_by')) {
                continue;
            }
            
            Log::info("Processing conflicts for table: {$tableName}");
            $this->processTableConflicts($tableName);
        }
    }

    /**
     * Process conflicts for a specific table
     */
    private function processTableConflicts($tableName)
    {
        // Get user's modified records from old company
        $oldRecords = DB::table($tableName)
            ->where('created_by', $this->oldCompanyId)
            ->get();
            
        // Get new template records 
        $newRecords = DB::table($tableName)
            ->where('created_by', $this->newCompanyId)
            ->get()
            ->keyBy($this->getMatchingKey($tableName));
        
        foreach ($oldRecords as $oldRecord) {
            $matchingKey = $this->getRecordMatchingValue($oldRecord, $tableName);
            $newRecord = $newRecords->get($matchingKey);
            
            if ($newRecord) {
                // Record exists in both - resolve conflict
                $this->resolveRecordConflict($tableName, $oldRecord, $newRecord);
            } else {
                // User added this record - copy it to new company
                $this->copyUserAddedRecord($tableName, $oldRecord);
            }
        }
    }

    /**
     * Get matching key for record comparison
     */
    private function getMatchingKey($tableName)
    {
        $matchingKeys = [
            'chart_of_accounts' => 'code',
            'product_services' => 'sku', 
            'settings' => 'name',
            'bank_accounts' => 'account_number',
            'company_payment_settings' => 'name',
        ];
        
        return $matchingKeys[$tableName] ?? 'name';
    }

    /**
     * Get matching value from record
     */
    private function getRecordMatchingValue($record, $tableName)
    {
        $key = $this->getMatchingKey($tableName);
        return $record->$key ?? null;
    }

    /**
     * Resolve conflict between old user record and new template record
     */
    private function resolveRecordConflict($tableName, $oldRecord, $newRecord)
    {
        if (!isset($this->conflictFields[$tableName])) {
            return; // No conflict resolution defined
        }
        
        $updates = [];
        $fieldsToCheck = $this->conflictFields[$tableName];
        
        foreach ($fieldsToCheck as $field) {
            if (!isset($oldRecord->$field) || !isset($newRecord->$field)) {
                continue;
            }
            
            if ($oldRecord->$field !== $newRecord->$field) {
                // Conflict detected - decide which value to use
                $resolution = $this->decideConflictResolution($tableName, $field, $oldRecord, $newRecord);
                
                if ($resolution === 'use_old') {
                    $updates[$field] = $oldRecord->$field;
                    $this->logConflictResolution($tableName, $field, 'user_modified', $oldRecord->$field, $newRecord->$field);
                } else {
                    $this->logConflictResolution($tableName, $field, 'template_updated', $newRecord->$field, $oldRecord->$field);
                }
            }
        }
        
        // Apply updates if any
        if (!empty($updates)) {
            $updates['updated_at'] = now();
            DB::table($tableName)->where('id', $newRecord->id)->update($updates);
            
            Log::info("Applied conflict resolution to {$tableName} record ID {$newRecord->id}");
        }
    }

    /**
     * Decide conflict resolution strategy
     */
    private function decideConflictResolution($tableName, $field, $oldRecord, $newRecord)
    {
        // Business rules for conflict resolution
        $rules = [
            'chart_of_accounts' => function($field, $old, $new) {
                // User customizations for names/descriptions take precedence
                if (in_array($field, ['name', 'description'])) {
                    return $this->useMostRecent($old, $new);
                }
                // Template changes for structure (parent, enabled status)
                return 'use_template';
            },
            
            'product_services' => function($field, $old, $new) {
                // User pricing takes precedence
                if (in_array($field, ['sale_price', 'purchase_price'])) {
                    return 'use_old';
                }
                return $this->useMostRecent($old, $new);
            },
            
            'settings' => function($field, $old, $new) {
                // Integration settings (Slack, Zoom, Google Calendar, etc.) - always keep user's
                $integrationSettings = [
                    'slack_webhook', 'slack_token', 'slack_channel', 'slack_connected',
                    'zoom_account_id', 'zoom_client_id', 'zoom_client_secret', 'zoom_connected',
                    'google_calender_json_file', 'google_calendar_token_file', 'google_clender_id',
                    'google_calendar_enable', 'google_calendar_oauth_connected', 'google_calendar_user_email',
                    'telegram_accestoken', 'telegram_chatid',
                    'pusher_app_id', 'pusher_app_key', 'pusher_app_secret', 'pusher_app_cluster',
                    'chatgpt_key', 'chatgpt_model'
                ];
                
                if (in_array($field, $integrationSettings)) {
                    return 'use_old'; // Always keep user's integration settings
                }
                
                // For other settings, use most recently updated
                return $this->useMostRecent($old, $new);
            },
            
            'company_payment_settings' => function($field, $old, $new) {
                // Always keep user's payment gateway settings
                return 'use_old';
            },
            
            'bank_accounts' => function($field, $old, $new) {
                // User bank details take precedence
                return 'use_old';
            },
        ];
        
        if (isset($rules[$tableName])) {
            return $rules[$tableName]($field, $oldRecord, $newRecord);
        }
        
        return $this->useMostRecent($oldRecord, $newRecord);
    }

    /**
     * Use most recently updated record
     */
    private function useMostRecent($oldRecord, $newRecord)
    {
        $oldTime = isset($oldRecord->updated_at) ? Carbon::parse($oldRecord->updated_at) : null;
        $newTime = isset($newRecord->updated_at) ? Carbon::parse($newRecord->updated_at) : null;
        
        if (!$oldTime && !$newTime) return 'use_template';
        if (!$oldTime) return 'use_template';
        if (!$newTime) return 'use_old';
        
        return $oldTime->gt($newTime) ? 'use_old' : 'use_template';
    }

    /**
     * Copy user-added record that doesn't exist in template
     */
    private function copyUserAddedRecord($tableName, $oldRecord)
    {
        $recordArray = (array) $oldRecord;
        unset($recordArray['id']);
        $recordArray['created_by'] = $this->newCompanyId;
        $recordArray['updated_at'] = now();
        
        DB::table($tableName)->insert($recordArray);
        
        $this->transferLog[] = [
            'table' => $tableName,
            'action' => 'copied_user_added',
            'old_id' => $oldRecord->id
        ];
        
        Log::info("Copied user-added record from {$tableName}");
    }

    /**
     * Step 4: Copy all user-generated data (COPY for safety)
     */
    private function copyUserGeneratedData()
    {
        Log::info("Step 4: Copying user-generated data (SAFE COPY operation)");
        
        foreach ($this->userDataTables as $tableName) {
            $this->copyTableData($tableName);
        }
    }

    /**
     * Copy data from a specific table (COPY operation for safety)
     */
    private function copyTableData($tableName)
    {
        if (!Schema::hasTable($tableName) || !Schema::hasColumn($tableName, 'created_by')) {
            return;
        }
        
        $records = DB::table($tableName)
            ->where('created_by', $this->oldCompanyId)
            ->get();
            
        if ($records->isEmpty()) {
            return;
        }
        
        Log::info("Copying {$records->count()} records from {$tableName}");
        
        foreach ($records as $record) {
            $recordArray = (array) $record;
            unset($recordArray['id']);
            $recordArray['created_by'] = $this->newCompanyId;
            $recordArray['updated_at'] = now();
            
            // Handle special fields that might reference old company
            $recordArray = $this->updateCompanyReferences($recordArray, $tableName);
            
            DB::table($tableName)->insert($recordArray);
        }
        
        $this->transferLog[] = [
            'table' => $tableName,
            'action' => 'copied',
            'count' => $records->count()
        ];
    }

    /**
     * Update any company references in the record
     */
    private function updateCompanyReferences($recordArray, $tableName)
    {
        // Update any fields that might reference the company ID
        $companyFields = ['company_id', 'user_id'];
        
        foreach ($companyFields as $field) {
            if (isset($recordArray[$field]) && $recordArray[$field] == $this->oldCompanyId) {
                $recordArray[$field] = $this->newCompanyId;
            }
        }
        
        return $recordArray;
    }

    /**
     * Step 5: Copy users to new company (COPY operation for safety)
     */
    private function copyUsersToNewCompany()
    {
        Log::info("Step 5: Copying users to new company (SAFE COPY operation)");
        
        // Get all users from old company (except the main company user)
        $users = User::where('created_by', $this->oldCompanyId)
            ->where('type', '!=', 'company')
            ->get();
            
        Log::info("Found {$users->count()} users to copy");
        
        foreach ($users as $user) {
            // Create copy of user in new company
            $newUser = $user->replicate();
            $newUser->created_by = $this->newCompanyId;
            $newUser->email = $this->isDryRun ? 'dryrun_' . time() . '_' . $user->email : $user->email;
            $newUser->updated_at = now();
            $newUser->save();
            
            // Copy user roles and permissions
            $this->copyUserRolesAndPermissions($user, $newUser);
            
            Log::info("Copied user {$user->name} (Old ID: {$user->id}, New ID: {$newUser->id})");
        }
        
        $this->transferLog[] = [
            'table' => 'users',
            'action' => 'copied',
            'count' => $users->count()
        ];
    }

    /**
     * Copy user roles and permissions
     */
    private function copyUserRolesAndPermissions($oldUser, $newUser)
    {
        // Copy user roles
        $roles = $oldUser->roles;
        if ($roles) {
            $newUser->roles()->sync($roles->pluck('id'));
        }
        
        // Copy direct permissions
        $permissions = $oldUser->permissions;
        if ($permissions) {
            $newUser->permissions()->sync($permissions->pluck('id'));
        }
    }

    /**
     * Step 6: Update company information 
     */
    private function updateCompanyInformation()
    {
        Log::info("Step 6: Updating company information");
        
        $oldCompany = User::find($this->oldCompanyId);
        $newCompany = User::find($this->newCompanyId);
        
        // Update new company with old company's details
        if (!$this->isDryRun) {
            $newCompany->email = $oldCompany->email;
        }
        $newCompany->is_disable = $oldCompany->is_disable;
        $newCompany->is_enable_login = $oldCompany->is_enable_login;
        $newCompany->avatar = $oldCompany->avatar;
        $newCompany->updated_at = now();
        $newCompany->save();
        
        Log::info("Updated new company information");
    }

    /**
     * Step 7: Delete old company only if not dry run
     */
    private function deleteOldCompany()
    {
        Log::info("Step 7: Deleting old company {$this->oldCompanyId}");
        
        $oldCompany = User::find($this->oldCompanyId);
        if ($oldCompany) {
            // Use existing cascade deletion logic from UserController
            $this->cascadeDeleteCompanyData($this->oldCompanyId);
            $oldCompany->delete();
            
            Log::info("Successfully deleted old company {$this->oldCompanyId}");
        }
    }

    /**
     * Use existing cascade deletion logic (copied from UserController)
     */
    private function cascadeDeleteCompanyData($companyId)
    {
        Log::info("Starting cascade deletion for company ID: {$companyId}");

        // Get all tables
        $allTables = $this->getAllDatabaseTables();

        // Tables to exclude (system/shared tables)
        $excludedTables = [
            'migrations', 'password_resets', 'failed_jobs', 'personal_access_tokens',
            'sessions', 'permissions', 'plans', 'coupons', 'admin_payment_settings',
            'orders', 'order_products', 'plan_requests', 'subscriptions', 'user_plans',
            'order_coupons', 'user_coupons', 'transaction_orders'
        ];

        foreach ($allTables as $table) {
            if (in_array($table, $excludedTables)) {
                continue;
            }

            try {
                if (!Schema::hasTable($table)) {
                    continue;
                }

                $columns = Schema::getColumnListing($table);
                $companyIdColumns = ['created_by', 'company_id', 'user_id'];

                foreach ($companyIdColumns as $column) {
                    if (in_array($column, $columns)) {
                        $deleted = DB::table($table)->where($column, $companyId)->delete();
                        if ($deleted > 0) {
                            Log::info("Deleted {$deleted} records from {$table}");
                        }
                        break;
                    }
                }
            } catch (\Exception $e) {
                Log::error("Error deleting from table {$table}: " . $e->getMessage());
            }
        }

        // Clean up role permissions
        $this->cleanupRolePermissions($companyId);
    }

    /**
     * Get all database tables
     */
    private function getAllDatabaseTables()
    {
        $tables = [];
        $results = DB::select('SHOW TABLES');
        
        foreach ($results as $result) {
            $tables[] = array_values((array) $result)[0];
        }
        
        return $tables;
    }

    /**
     * Clean up role permissions (from UserController)
     */
    private function cleanupRolePermissions($companyId)
    {
        try {
            // Delete role permissions for this company's roles
            DB::delete("
                DELETE rp FROM role_has_permissions rp
                JOIN roles r ON rp.role_id = r.id
                WHERE r.created_by = ?
            ", [$companyId]);

            // Delete user role assignments
            DB::delete("
                DELETE ur FROM model_has_roles ur
                JOIN users u ON ur.model_id = u.id
                WHERE u.created_by = ? AND ur.model_type = 'App\\\\Models\\\\User'
            ", [$companyId]);

            // Delete user permissions
            DB::delete("
                DELETE up FROM model_has_permissions up
                JOIN users u ON up.model_id = u.id
                WHERE u.created_by = ? AND up.model_type = 'App\\\\Models\\\\User'
            ", [$companyId]);

        } catch (\Exception $e) {
            Log::error("Error cleaning up role permissions: " . $e->getMessage());
        }
    }

    /**
     * Log conflict resolution
     */
    private function logConflictResolution($table, $field, $resolution, $usedValue, $rejectedValue)
    {
        $this->transferLog[] = [
            'table' => $table,
            'field' => $field,
            'action' => 'conflict_resolved',
            'resolution' => $resolution,
            'used_value' => $usedValue,
            'rejected_value' => $rejectedValue,
            'timestamp' => now()
        ];
    }

    /**
     * Generate success response
     */
    private function generateSuccessResponse()
    {
        $message = $this->isDryRun ? 
            'Company refresh dry run completed successfully' : 
            'Company refreshed successfully';
            
        return [
            'success' => true,
            'message' => $message,
            'is_dry_run' => $this->isDryRun,
            'template_company_id' => $this->templateCompanyId,
            'old_company_id' => $this->oldCompanyId,
            'new_company_id' => $this->newCompanyId,
            'currency_matched' => $this->getCompanyCurrency($this->oldCompanyId),
            'transfer_log' => $this->transferLog,
            'summary' => $this->generateTransferSummary(),
            'completed_at' => now()
        ];
    }

    /**
     * Generate transfer summary
     */
    private function generateTransferSummary()
    {
        $summary = [
            'tables_processed' => 0,
            'records_copied' => 0,
            'conflicts_resolved' => 0,
            'users_copied' => 0
        ];

        foreach ($this->transferLog as $log) {
            if ($log['action'] === 'copied') {
                $summary['tables_processed']++;
                $summary['records_copied'] += $log['count'];
                
                if ($log['table'] === 'users') {
                    $summary['users_copied'] = $log['count'];
                }
            } elseif ($log['action'] === 'conflict_resolved') {
                $summary['conflicts_resolved']++;
            }
        }

        return $summary;
    }

    /**
     * Cleanup if refresh fails
     */
    private function cleanupFailedRefresh()
    {
        if ($this->newCompanyId) {
            Log::info("Cleaning up failed refresh - deleting new company {$this->newCompanyId}");
            
            try {
                $this->cascadeDeleteCompanyData($this->newCompanyId);
                User::where('id', $this->newCompanyId)->delete();
                
                Log::info("Cleanup completed");
            } catch (\Exception $e) {
                Log::error("Error during cleanup: " . $e->getMessage());
            }
        }
    }

    /**
     * Preview what would be refreshed (dry run)
     */
    public function previewRefresh($oldCompanyId)
    {
        Log::info("Generating refresh preview for company {$oldCompanyId}");
        
        // Check currency compatibility first
        $oldCompanyCurrency = $this->getCompanyCurrency($oldCompanyId);
        $templateCompanyId = $this->findTemplateCompanyByCurrency($oldCompanyCurrency);
        
        if (!$templateCompanyId) {
            return [
                'success' => false,
                'error' => "No template company found for currency: {$oldCompanyCurrency}",
                'available_currencies' => $this->getAvailableTemplateCurrencies()
            ];
        }
        
        $preview = [
            'success' => true,
            'old_company_id' => $oldCompanyId,
            'old_company_currency' => $oldCompanyCurrency,
            'template_company_id' => $templateCompanyId,
            'user_data_to_copy' => [],
            'potential_conflicts' => [],
            'users_to_copy' => 0,
            'recommendation' => ''
        ];

        // Count user data
        foreach ($this->userDataTables as $tableName) {
            if (Schema::hasTable($tableName) && Schema::hasColumn($tableName, 'created_by')) {
                $count = DB::table($tableName)->where('created_by', $oldCompanyId)->count();
                if ($count > 0) {
                    $preview['user_data_to_copy'][$tableName] = $count;
                }
            }
        }

        // Check for potential conflicts
        foreach ($this->conflictTables as $tableName) {
            if (Schema::hasTable($tableName) && Schema::hasColumn($tableName, 'created_by')) {
                $oldCount = DB::table($tableName)->where('created_by', $oldCompanyId)->count();
                $templateCount = DB::table($tableName)->where('created_by', $templateCompanyId)->count();
                
                if ($oldCount > 0 || $templateCount > 0) {
                    $preview['potential_conflicts'][$tableName] = [
                        'current_records' => $oldCount,
                        'template_records' => $templateCount
                    ];
                }
            }
        }

        // Count users
        $preview['users_to_copy'] = User::where('created_by', $oldCompanyId)
            ->where('type', '!=', 'company')
            ->count();

        // Generate recommendation
        $totalConflicts = array_sum(array_column($preview['potential_conflicts'], 'current_records'));
        $totalUserData = array_sum($preview['user_data_to_copy']);
        
        if ($totalConflicts == 0 && $totalUserData == 0) {
            $preview['recommendation'] = "Company appears to be empty - refresh will just apply latest template.";
        } elseif ($totalConflicts > 20) {
            $preview['recommendation'] = "High number of potential conflicts ({$totalConflicts}). Review carefully before proceeding.";
        } else {
            $preview['recommendation'] = "Safe to proceed - {$totalConflicts} potential conflicts and {$totalUserData} user records to preserve.";
        }

        return $preview;
    }

    /**
     * Get available template currencies
     */
    private function getAvailableTemplateCurrencies()
    {
        return TemplateCompanyConfig::getAvailableCurrencies();
    }

    /**
     * Perform actual dry run (creates everything but doesn't delete old company)
     */
    public function dryRun($oldCompanyId)
    {
        return $this->refreshCompany($oldCompanyId, ['dry_run' => true]);
    }

    /**
     * Clean up dry run data
     */
    public function cleanupDryRun($dryRunResponse)
    {
        if (isset($dryRunResponse['new_company_id'])) {
            $newCompanyId = $dryRunResponse['new_company_id'];
            
            Log::info("Cleaning up dry run company {$newCompanyId}");
            
            try {
                $this->cascadeDeleteCompanyData($newCompanyId);
                User::where('id', $newCompanyId)->delete();
                
                Log::info("Dry run cleanup completed");
                return ['success' => true, 'message' => 'Dry run data cleaned up successfully'];
            } catch (\Exception $e) {
                Log::error("Error cleaning up dry run: " . $e->getMessage());
                return ['success' => false, 'error' => $e->getMessage()];
            }
        }
        
        return ['success' => false, 'error' => 'No dry run company ID provided'];
    }

    /**
     * Get detailed currency analysis
     */
    public function analyzeCurrencyCompatibility($oldCompanyId)
    {
        $oldCurrency = $this->getCompanyCurrency($oldCompanyId);
        $availableTemplates = $this->getAvailableTemplateCurrencies();
        
        return [
            'old_company_id' => $oldCompanyId,
            'old_company_currency' => $oldCurrency,
            'available_templates' => $availableTemplates,
            'compatible_template' => $this->findTemplateCompanyByCurrency($oldCurrency),
            'is_compatible' => $this->findTemplateCompanyByCurrency($oldCurrency) !== null
        ];
    }
}