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
    private $idMappings = []; // Track old ID -> new ID mappings
    private $deferredRelationships = []; // Store relationships to fix later

    // ALL master data tables that users can add to OR modify
    private $masterDataTables = [
        // Chart of accounts system
        'chart_of_account_types',
        'chart_of_account_sub_types',
        'chart_of_accounts',

        // Product & inventory system
        'product_service_categories',
        'product_service_units',
        'product_services',
        'taxes',
        'warehouses',

        // CRM system
        'labels',
        'sources',
        'pipelines',
        'stages',
        'lead_stages',

        // HR system
        'branches',
        'departments',
        'designations',
        'job_categories',
        'job_stages',
        'leave_types',
        'allowance_options',
        'deduction_options',
        'loan_options',
        'award_types',
        'training_types',
        'goal_types',
        'performance_types',
        'termination_types',
        'payslip_types',

        // Project system
        'task_stages',

        // Contract system
        'contract_types',

        // Other systems
        'competencies',
        'custom_questions',
        'documents',
        'roles', // Users can create custom roles!

        // Banking
        'bank_accounts',
    ];

    // User-generated transactional data that should be COPIED to new company
    private $userDataTables = [
        // Core user data
        //'users',
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

    // Fields to compare for conflicts in master data
    private $conflictFields = [
        'chart_of_accounts' => ['name', 'description', 'is_enabled', 'parent'],
        'product_services' => ['name', 'description', 'sale_price', 'purchase_price', 'is_enabled'],
        'product_service_categories' => ['name', 'color'],
        'warehouses' => ['name', 'address', 'city', 'state'],
        'branches' => ['name', 'address', 'city', 'state'],
        'departments' => ['name', 'branch_id'],
        'designations' => ['name', 'department_id', 'branch_id'],
        'labels' => ['name', 'color'],
        'sources' => ['name'],
        'pipelines' => ['name'],
        'stages' => ['name', 'pipeline_id', 'order'],
        'taxes' => ['name', 'rate'],
        'bank_accounts' => ['account_number', 'holder_name', 'bank_name', 'contact_number'],
        'roles' => ['name'], // Users can customize role names
        'settings' => ['value'], // ALL settings including integrations
        'company_payment_settings' => ['value'], // Payment gateway configurations
    ];

    public function __construct()
    {
        // No hardcoded template - will be determined by currency matching
    }

    /**
     * Main refresh method - creates new company and COPIES data
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

                // Step 3: Process all master data (unified approach)
                $this->processMasterData();

                // Step 4: Handle special settings
                $this->processSettings();

                // Step 5: Copy all user-generated data (COPY operation for safety)
                $this->copyUserGeneratedData();

                // Step 6: Copy users to new company (COPY operation)
                $this->copyUsersToNewCompany();

                // Step 7: Update company information
                $this->updateCompanyInformation();

                // Step 8: Only delete old company if NOT dry run and everything succeeded
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

        $oldCompanyCurrency = $this->getCompanyCurrency($this->oldCompanyId);

        if (!$oldCompanyCurrency) {
            throw new \Exception("Cannot determine currency for company {$this->oldCompanyId}");
        }

        Log::info("Old company currency: {$oldCompanyCurrency}");

        $templateCompanyId = $this->findTemplateCompanyByCurrency($oldCompanyCurrency);

        if (!$templateCompanyId) {
            $available = TemplateCompanyConfig::getAvailableCurrencies();
            $availableList = implode(', ', array_keys($available));

            throw new \Exception(
                "No template company found for currency: {$oldCompanyCurrency}. " .
                "Available currencies: {$availableList}. " .
                "Please create a template company for {$oldCompanyCurrency} first."
            );
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
     * Step 2: Clone template company to new company using CompanyClonerService
     */
    private function cloneTemplateToNewCompany()
    {
        Log::info("Step 2: Cloning template company {$this->templateCompanyId} to new company");

        $oldCompany = User::find($this->oldCompanyId);

        // Create new company record with proper fields
        $newCompanyId = DB::table('users')->insertGetId([
            'name' => $oldCompany->name . ($this->isDryRun ? ' (DRY RUN)' : ''),
            'email' => $this->generateTempEmail($oldCompany->email),
            'email_verified_at' => $this->isDryRun ? now() : $oldCompany->email_verified_at, // FIX: Set email as verified for dry run
            'password' => $oldCompany->password, // FIX: Copy password from original company
            'type' => 'company',
            'lang' => $oldCompany->lang ?? 'en',
            'avatar' => $oldCompany->avatar ?? 'avatar.png', // FIX: Default avatar
            'plan' => $oldCompany->plan, // FIX: Copy plan
            'plan_expire_date' => $oldCompany->plan_expire_date, // FIX: Copy plan expiry
            'trial_plan' => $oldCompany->trial_plan ?? 0,
            'trial_expire_date' => $oldCompany->trial_expire_date,
            'requested_plan' => $oldCompany->requested_plan ?? 0,
            'storage_limit' => $oldCompany->storage_limit ?? 0,
            'messenger_color' => $oldCompany->messenger_color ?? '#2180f3',
            'default_pipeline' => $oldCompany->default_pipeline,
            'active_status' => $oldCompany->active_status ?? 0,
            'delete_status' => $oldCompany->delete_status ?? 1,
            'mode' => $oldCompany->mode ?? 'light',
            'dark_mode' => $oldCompany->dark_mode ?? 0,
            'is_disable' => $oldCompany->is_disable, // Copy from old company
            'is_enable_login' => $oldCompany->is_enable_login, // Copy from old company
            'is_active' => $oldCompany->is_active ?? 1, // Copy from old company
            'referral_code' => $oldCompany->referral_code,
            'used_referral_code' => $oldCompany->used_referral_code ?? 0,
            'commission_amount' => $oldCompany->commission_amount ?? 0,
            'last_login_at' => $oldCompany->last_login_at,
            'registration_ip' => $oldCompany->registration_ip,
            'last_login_ip' => $oldCompany->last_login_ip,
            'user_agent' => $oldCompany->user_agent,
            'created_by' => $oldCompany->created_by,
            'remember_token' => null, // Reset remember token
            'created_at' => $oldCompany->created_at,
            'updated_at' => now(),
            'is_email_verified' => $this->isDryRun ? 0 : $oldCompany->is_email_verified ?? 0,
            'payfast_subscription_token' => $this->isDryRun ? null : $oldCompany->payfast_subscription_token,
            'payfast_token_created_at' => $this->isDryRun ? null : $oldCompany->payfast_token_created_at,
            'card_last_four' => $this->isDryRun ? null : $oldCompany->card_last_four,
            'card_type' => $this->isDryRun ? null : $oldCompany->card_type,
            'card_exp_month' => $this->isDryRun ? null : $oldCompany->card_exp_month,
            'card_exp_year' => $this->isDryRun ? null : $oldCompany->card_exp_year,
        ]);

        Log::info("Created new company record with ID: {$newCompanyId}");

        // Use CompanyClonerService to clone template data to new company
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
     * Step 3: Process all master data with unified conflict resolution
     */
    private function processMasterData()
    {
        Log::info("Step 3: Processing all master data with unified conflict resolution");

        foreach ($this->masterDataTables as $tableName) {
            if (!Schema::hasTable($tableName) || !Schema::hasColumn($tableName, 'created_by')) {
                continue;
            }

            Log::info("Processing master data table: {$tableName}");
            $this->processMasterDataTable($tableName);
        }

        // Fix relationships after all master data is processed
        $this->fixMasterDataRelationships();
    }

    /**
     * Process a single master data table (handles both additions and modifications)
     */
    private function processMasterDataTable($tableName)
    {
        // Get user's records from old company
        $oldRecords = DB::table($tableName)
            ->where('created_by', $this->oldCompanyId)
            ->get();

        if ($oldRecords->isEmpty()) {
            Log::info("No user data found in {$tableName}");
            return;
        }

        // Get existing template records in new company
        $templateRecords = DB::table($tableName)
            ->where('created_by', $this->newCompanyId)
            ->get()
            ->keyBy($this->getMatchingKey($tableName));

        Log::info("Processing {$oldRecords->count()} user records against {$templateRecords->count()} template records in {$tableName}");

        foreach ($oldRecords as $oldRecord) {
            $matchingKey = $this->getRecordMatchingValue($oldRecord, $tableName);
            $templateRecord = $templateRecords->get($matchingKey);

            if ($templateRecord) {
                // Record exists in template - resolve conflict
                $this->resolveMasterDataConflict($tableName, $oldRecord, $templateRecord);
            } else {
                // User-added record - copy it
                $this->copyUserAddedMasterData($tableName, $oldRecord);
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
     * Resolve conflict between user record and template record
     */
    private function resolveMasterDataConflict($tableName, $oldRecord, $templateRecord)
    {
        if (!isset($this->conflictFields[$tableName])) {
            Log::info("No conflict resolution defined for {$tableName}, keeping template version");
            // Still track the mapping
            $this->idMappings[$tableName][$oldRecord->id] = $templateRecord->id;
            return;
        }

        $updates = [];
        $fieldsToCheck = $this->conflictFields[$tableName];

        foreach ($fieldsToCheck as $field) {
            if (!isset($oldRecord->$field) || !isset($templateRecord->$field)) {
                continue;
            }

            if ($oldRecord->$field !== $templateRecord->$field) {
                // Conflict detected - decide which value to use
                $resolution = $this->decideMasterDataConflictResolution($tableName, $field, $oldRecord, $templateRecord);

                if ($resolution === 'use_user') {
                    $updates[$field] = $oldRecord->$field;
                    $this->logConflictResolution($tableName, $field, 'user_modified', $oldRecord->$field, $templateRecord->$field);
                } else {
                    $this->logConflictResolution($tableName, $field, 'template_updated', $templateRecord->$field, $oldRecord->$field);
                }
            }
        }

        // Apply updates if any
        if (!empty($updates)) {
            $updates['updated_at'] = now();
            DB::table($tableName)->where('id', $templateRecord->id)->update($updates);

            Log::info("Applied conflict resolution to {$tableName} record ID {$templateRecord->id}");
        }

        // Track template record ID for relationship mapping
        if (!isset($this->idMappings[$tableName])) {
            $this->idMappings[$tableName] = [];
        }
        $this->idMappings[$tableName][$oldRecord->id] = $templateRecord->id;
    }

    /**
     * Copy user-added master data record
     */
    private function copyUserAddedMasterData($tableName, $oldRecord)
    {
        $recordArray = (array) $oldRecord;
        $oldId = $recordArray['id'];
        unset($recordArray['id']);
        $recordArray['created_by'] = $this->newCompanyId;
        $recordArray['updated_at'] = now();

        // Handle parent relationships later
        $parentFields = ['parent', 'parent_id', 'branch_id', 'department_id', 'pipeline_id'];
        $deferredRelationships = [];

        foreach ($parentFields as $parentField) {
            if (isset($recordArray[$parentField]) && $recordArray[$parentField] > 0) {
                $deferredRelationships[$parentField] = $recordArray[$parentField];
                $recordArray[$parentField] = null; // Will fix later
            }
        }

        $newId = DB::table($tableName)->insertGetId($recordArray);

        // Track the ID mapping
        if (!isset($this->idMappings[$tableName])) {
            $this->idMappings[$tableName] = [];
        }
        $this->idMappings[$tableName][$oldId] = $newId;

        // Store deferred relationships for later processing
        if (!empty($deferredRelationships)) {
            if (!isset($this->deferredRelationships[$tableName])) {
                $this->deferredRelationships[$tableName] = [];
            }
            $this->deferredRelationships[$tableName][$newId] = $deferredRelationships;
        }

        $this->transferLog[] = [
            'table' => $tableName,
            'action' => 'copied_user_added',
            'old_id' => $oldId,
            'new_id' => $newId
        ];

        Log::info("Copied user-added {$tableName} record (Old ID: {$oldId} -> New ID: {$newId})");
    }

    /**
     * Decide conflict resolution for master data
     */
    private function decideMasterDataConflictResolution($tableName, $field, $oldRecord, $templateRecord)
    {
        // Business rules for different types of master data
        $rules = [
            'chart_of_accounts' => function($field, $old, $template) {
                // User customizations for names/descriptions take precedence
                if (in_array($field, ['name', 'description'])) {
                    return $this->useMostRecent($old, $template);
                }
                // Template structure changes take precedence
                return 'use_template';
            },

            'product_services' => function($field, $old, $template) {
                // User pricing takes precedence
                if (in_array($field, ['sale_price', 'purchase_price'])) {
                    return 'use_user';
                }
                return $this->useMostRecent($old, $template);
            },

            'settings' => function($field, $old, $template) {
                // Integration settings - always keep user's
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
                    return 'use_user';
                }

                return $this->useMostRecent($old, $template);
            },

            'roles' => function($field, $old, $template) {
                // User role customizations take precedence
                return 'use_user';
            },

            // For most master data, user customizations take precedence
            'default' => function($field, $old, $template) {
                return $this->useMostRecent($old, $template);
            }
        ];

        $ruleFunction = $rules[$tableName] ?? $rules['default'];
        return $ruleFunction($field, $oldRecord, $templateRecord);
    }

    /**
     * Use most recently updated record
     */
    private function useMostRecent($oldRecord, $templateRecord)
    {
        $oldTime = isset($oldRecord->updated_at) ? Carbon::parse($oldRecord->updated_at) : null;
        $templateTime = isset($templateRecord->updated_at) ? Carbon::parse($templateRecord->updated_at) : null;

        if (!$oldTime && !$templateTime) return 'use_template';
        if (!$oldTime) return 'use_template';
        if (!$templateTime) return 'use_user';

        return $oldTime->gt($templateTime) ? 'use_user' : 'use_template';
    }

    /**
     * Step 4: Handle special settings
     */
    private function processSettings()
    {
        Log::info("Step 4: Processing settings with integration preservation");

        // Settings and payment settings are already handled in processMasterData
        // but we process them separately for special integration handling
        $this->processMasterDataTable('settings');

        if (Schema::hasTable('company_payment_settings')) {
            $this->processMasterDataTable('company_payment_settings');
        }
    }

    /**
     * Fix all master data relationships after everything is copied
     */
    private function fixMasterDataRelationships()
    {
        Log::info("Fixing master data relationships");

        // Process all deferred relationships
        foreach ($this->deferredRelationships as $tableName => $records) {
            foreach ($records as $newId => $relationships) {
                $this->fixRecordRelationships($tableName, $newId, $relationships);
            }
        }

        // Special handling for roles -> permissions (many-to-many)
        $this->fixRolePermissions();
    }

    /**
     * Fix relationships for a specific record
     */
    private function fixRecordRelationships($tableName, $newId, $relationships)
    {
        $updates = [];

        foreach ($relationships as $field => $oldRefId) {
            $newRefId = $this->findMappedId($field, $oldRefId, $tableName);

            if ($newRefId) {
                $updates[$field] = $newRefId;
                Log::info("Fixed {$tableName}.{$field}: {$oldRefId} -> {$newRefId}");
            } else {
                Log::warning("Could not map {$tableName}.{$field} = {$oldRefId}");
            }
        }

        if (!empty($updates)) {
            DB::table($tableName)->where('id', $newId)->update($updates);
        }
    }

    /**
     * Find mapped ID for a relationship
     */
    private function findMappedId($field, $oldId, $currentTable)
    {
        // Determine which table this field references
        $referenceTables = [
            'parent' => $currentTable, // Self-reference
            'parent_id' => $currentTable,
            'branch_id' => 'branches',
            'department_id' => 'departments',
            'pipeline_id' => 'pipelines',
            'category_id' => 'product_service_categories',
            'type' => 'chart_of_account_types',
            'sub_type' => 'chart_of_account_sub_types',
        ];

        $referenceTable = $referenceTables[$field] ?? null;

        if (!$referenceTable) {
            return null;
        }

        // Check if we have a direct mapping
        if (isset($this->idMappings[$referenceTable][$oldId])) {
            return $this->idMappings[$referenceTable][$oldId];
        }

        // Try to find by name match
        $oldRecord = DB::table($referenceTable)
            ->where('created_by', $this->oldCompanyId)
            ->where('id', $oldId)
            ->first();

        if ($oldRecord && isset($oldRecord->name)) {
            $newRecord = DB::table($referenceTable)
                ->where('created_by', $this->newCompanyId)
                ->where('name', $oldRecord->name)
                ->first();

            if ($newRecord) {
                return $newRecord->id;
            }
        }

        return null;
    }

    /**
     * Fix role permissions (many-to-many relationship)
     */
    private function fixRolePermissions()
    {
        if (!isset($this->idMappings['roles'])) {
            return;
        }

        Log::info("Fixing role permissions");

        foreach ($this->idMappings['roles'] as $oldRoleId => $newRoleId) {
            // Get permissions for old role
            $permissions = DB::table('role_has_permissions')
                ->where('role_id', $oldRoleId)
                ->pluck('permission_id');

            // Assign to new role
            foreach ($permissions as $permissionId) {
                DB::table('role_has_permissions')->updateOrInsert([
                    'role_id' => $newRoleId,
                    'permission_id' => $permissionId
                ]);
            }

            Log::info("Fixed permissions for role {$newRoleId} ({$permissions->count()} permissions)");
        }
    }

    /**
     * Step 5: Copy all user-generated data
     */
    private function copyUserGeneratedData()
    {
        Log::info("Step 5: Copying user-generated data (SAFE COPY operation)");

        foreach ($this->userDataTables as $tableName) {
            $this->copyTableData($tableName);
        }
    }

    /**
     * Copy data from a specific table
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

            // SPECIAL HANDLING FOR USERS TABLE
            if ($tableName === 'users') {
                // Skip company type users (they're handled in cloneTemplateToNewCompany)
                if (isset($recordArray['type']) && $recordArray['type'] === 'company') {
                    Log::info("Skipping company type user in copyTableData");
                    continue;
                }

                // Skip non-company users - they should be handled by copyUsersToNewCompany method
                Log::info("Skipping user in copyTableData - will be handled by copyUsersToNewCompany");
                continue;
            }

            unset($recordArray['id']);
            $recordArray['created_by'] = $this->newCompanyId;
            $recordArray['updated_at'] = now();

            // Handle special fields and relationship mappings
            $recordArray = $this->updateCompanyReferences($recordArray, $tableName);

            try {
                $newId = DB::table($tableName)->insertGetId($recordArray);

                // Store ID mapping for relationship fixing later
                if (isset($record->id)) {
                    $this->idMappings[$tableName][$record->id] = $newId;
                }
            } catch (\Exception $e) {
                Log::error("Error copying {$tableName} record: " . $e->getMessage());
            }
        }
    }

    /**
     * Update company references and relationship mappings
     */
    private function updateCompanyReferences($recordArray, $tableName)
    {
        // Update company ID references
        $companyFields = ['company_id', 'user_id'];

        foreach ($companyFields as $field) {
            if (isset($recordArray[$field]) && $recordArray[$field] == $this->oldCompanyId) {
                $recordArray[$field] = $this->newCompanyId;
            }
        }

        // Update foreign key relationships
        $masterDataMappings = [
            'leads' => ['label_id' => 'labels', 'source_id' => 'sources', 'pipeline_id' => 'pipelines', 'stage_id' => 'stages'],
            'deals' => ['label_id' => 'labels', 'source_id' => 'sources', 'pipeline_id' => 'pipelines', 'stage_id' => 'stages'],
            'product_services' => [
                'category_id' => 'product_service_categories',
                'sale_chartaccount_id' => 'chart_of_accounts',
                'expense_chartaccount_id' => 'chart_of_accounts'
            ],
            'customers' => [
                'billing_address' => 'chart_of_accounts',
                'shipping_address' => 'chart_of_accounts'
            ],
            'venders' => [
                'billing_address' => 'chart_of_accounts'
            ],
            'bank_accounts' => [
                'chart_account_id' => 'chart_of_accounts'
            ],
            'employees' => ['branch_id' => 'branches', 'department_id' => 'departments', 'designation_id' => 'designations'],
            'jobs' => ['branch_id' => 'branches', 'department_id' => 'departments', 'category_id' => 'job_categories'],
            'awards' => ['award_type_id' => 'award_types'],
            'trainings' => ['training_type_id' => 'training_types'],
            'leaves' => ['leave_type_id' => 'leave_types'],
            'contracts' => ['contract_type_id' => 'contract_types'],
            'warehouse_products' => ['warehouse_id' => 'warehouses'],
        ];

        if (isset($masterDataMappings[$tableName])) {
            foreach ($masterDataMappings[$tableName] as $foreignKey => $referencedTable) {
                if (isset($recordArray[$foreignKey]) && $recordArray[$foreignKey] > 0) {
                    $oldForeignId = $recordArray[$foreignKey];

                    // Check if we have a mapping for this master data
                    if (isset($this->idMappings[$referencedTable][$oldForeignId])) {
                        $newForeignId = $this->idMappings[$referencedTable][$oldForeignId];
                        $recordArray[$foreignKey] = $newForeignId;

                        Log::info("Mapped {$tableName}.{$foreignKey}: {$oldForeignId} -> {$newForeignId} ({$referencedTable})");
                    } else {
                        // Try to find by name match
                        $oldRecord = DB::table($referencedTable)
                            ->where('created_by', $this->oldCompanyId)
                            ->where('id', $oldForeignId)
                            ->first();

                        if ($oldRecord && isset($oldRecord->name)) {
                            $newRecord = DB::table($referencedTable)
                                ->where('created_by', $this->newCompanyId)
                                ->where('name', $oldRecord->name)
                                ->first();

                            if ($newRecord) {
                                $recordArray[$foreignKey] = $newRecord->id;
                                Log::info("Found name match for {$tableName}.{$foreignKey}: '{$oldRecord->name}' -> {$newRecord->id}");
                            } else {
                                Log::warning("No mapping found for {$tableName}.{$foreignKey} = {$oldForeignId} ({$referencedTable})");
                                // Set to null to avoid broken references
                                $recordArray[$foreignKey] = null;
                            }
                        }
                    }
                }
            }
        }

        return $recordArray;
    }

    /**
     * Step 6: Copy users to new company
     */
    private function copyUsersToNewCompany()
    {
        Log::info("Step 6: Copying users to new company (SAFE COPY operation)");

        // Get all users from old company (except the main company user)
        $users = User::where('created_by', $this->oldCompanyId)
            ->where('type', '!=', 'company')
            ->get();

        Log::info("Found {$users->count()} users to copy");

        foreach ($users as $user) {
            // Create copy of user in new company using replicate to preserve all fields
            $newUser = $user->replicate();

            // Update necessary fields
            $newUser->created_by = $this->newCompanyId;
            $newUser->updated_at = now();

            // Handle email for dry run
            if ($this->isDryRun) {
                $newUser->email = 'dryrun_' . time() . '_' . $user->email;
                $newUser->is_enable_login = 0; // Disable login for dry run
            }


            // Preserve important fields that might get lost
            $newUser->password = $user->password; // Ensure password is copied
            $newUser->plan = $user->plan; // Preserve plan
            $newUser->plan_expire_date = $user->plan_expire_date; // Preserve plan expiry
            $newUser->trial_plan = $user->trial_plan;
            $newUser->trial_expire_date = $user->trial_expire_date;
            $newUser->storage_limit = $user->storage_limit;
            $newUser->lang = $user->lang;
            $newUser->avatar = $user->avatar;
            $newUser->messenger_color = $user->messenger_color;
            $newUser->active_status = $user->active_status;
            $newUser->delete_status = $user->delete_status;
            $newUser->mode = $user->mode;
            $newUser->dark_mode = $user->dark_mode;
            $newUser->is_active = $user->is_active;
            $newUser->referral_code = $user->referral_code;
            $newUser->used_referral_code = $user->used_referral_code;
            $newUser->commission_amount = $user->commission_amount;

            // Save the new user
            $newUser->save();

            // Store ID mapping for relationship fixing later
            $this->idMappings['users'][$user->id] = $newUser->id;

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
     * Copy user roles and permissions with proper ID mapping
     */
    private function copyUserRolesAndPermissions($oldUser, $newUser)
    {
        // Get old user's role IDs
        $oldRoleIds = DB::table('model_has_roles')
            ->where('model_id', $oldUser->id)
            ->where('model_type', 'App\\Models\\User')
            ->pluck('role_id');

        foreach ($oldRoleIds as $oldRoleId) {
            // Find the equivalent role in new company using our mappings
            if (isset($this->idMappings['roles'][$oldRoleId])) {
                $newRoleId = $this->idMappings['roles'][$oldRoleId];

                DB::table('model_has_roles')->insert([
                    'role_id' => $newRoleId,
                    'model_id' => $newUser->id,
                    'model_type' => 'App\\Models\\User'
                ]);
            } else {
                // Fallback: try to find by role name
                $oldRole = DB::table('roles')->where('id', $oldRoleId)->first();

                if ($oldRole) {
                    $newRole = DB::table('roles')
                        ->where('created_by', $this->newCompanyId)
                        ->where('name', $oldRole->name)
                        ->first();

                    if ($newRole) {
                        DB::table('model_has_roles')->insert([
                            'role_id' => $newRole->id,
                            'model_id' => $newUser->id,
                            'model_type' => 'App\\Models\\User'
                        ]);
                    } else {
                        Log::warning("Role '{$oldRole->name}' not found in new company for user {$newUser->name}");
                    }
                }
            }
        }

        // Handle direct permissions (usually global, so copy as-is)
        $oldPermissionIds = DB::table('model_has_permissions')
            ->where('model_id', $oldUser->id)
            ->where('model_type', 'App\\Models\\User')
            ->pluck('permission_id');

        foreach ($oldPermissionIds as $permissionId) {
            DB::table('model_has_permissions')->insert([
                'permission_id' => $permissionId,
                'model_id' => $newUser->id,
                'model_type' => 'App\\Models\\User'
            ]);
        }
    }

    /**
     * ADDITIONAL METHOD: Update company information after all data is copied
     */
    private function updateCompanyInformation()
    {
        Log::info("Step 7: Updating company information");

        $oldCompany = User::find($this->oldCompanyId);

        // Update company with final settings
        DB::table('users')->where('id', $this->newCompanyId)->update([
            'name' => $oldCompany->name, // Remove "(DRY RUN)" suffix for actual refresh
            'email' => $this->isDryRun ? 'dryrun_' . time() . '_' . $oldCompany->email : $oldCompany->email,
            'email_verified_at' => $oldCompany->email_verified_at, // Restore original verification status
            'is_enable_login' => $oldCompany->is_enable_login, // Copy from old company
            'is_active' => $oldCompany->is_active,
            'is_disable' => $oldCompany->is_disable,
            'updated_at' => now(),
        ]);

        Log::info("Updated new company information");
    }

    /**
     * Step 8: Delete old company only if not dry run
     */
    private function deleteOldCompany()
    {
        Log::info("Step 8: Deleting old company {$this->oldCompanyId}");

        $oldCompany = User::find($this->oldCompanyId);
        if ($oldCompany) {
            // Use existing cascade deletion logic from UserController
            $this->cascadeDeleteCompanyData($this->oldCompanyId);
            $oldCompany->delete();

            Log::info("Successfully deleted old company {$this->oldCompanyId}");
        }
    }

    /**
     * Use existing cascade deletion logic
     */
    private function cascadeDeleteCompanyData($companyId)
    {
        Log::info("Starting cascade deletion for company ID: {$companyId}");

        $allTables = $this->getAllDatabaseTables();

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
     * Clean up role permissions
     */
    private function cleanupRolePermissions($companyId)
    {
        try {
            DB::delete("
                DELETE rp FROM role_has_permissions rp
                JOIN roles r ON rp.role_id = r.id
                WHERE r.created_by = ?
            ", [$companyId]);

            DB::delete("
                DELETE ur FROM model_has_roles ur
                JOIN users u ON ur.model_id = u.id
                WHERE u.created_by = ? AND ur.model_type = 'App\\\\Models\\\\User'
            ", [$companyId]);

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
                'available_currencies' => TemplateCompanyConfig::getAvailableCurrencies()
            ];
        }

        $preview = [
            'success' => true,
            'old_company_id' => $oldCompanyId,
            'old_company_currency' => $oldCompanyCurrency,
            'template_company_id' => $templateCompanyId,
            'master_data_to_process' => [],
            'user_data_to_copy' => [],
            'users_to_copy' => 0,
            'recommendation' => ''
        ];

        // Count master data
        foreach ($this->masterDataTables as $tableName) {
            if (Schema::hasTable($tableName) && Schema::hasColumn($tableName, 'created_by')) {
                $count = DB::table($tableName)->where('created_by', $oldCompanyId)->count();
                if ($count > 0) {
                    $preview['master_data_to_process'][$tableName] = $count;
                }
            }
        }

        // Count user data
        foreach ($this->userDataTables as $tableName) {
            if (Schema::hasTable($tableName) && Schema::hasColumn($tableName, 'created_by')) {
                $count = DB::table($tableName)->where('created_by', $oldCompanyId)->count();
                if ($count > 0) {
                    $preview['user_data_to_copy'][$tableName] = $count;
                }
            }
        }

        // Count users
        $preview['users_to_copy'] = User::where('created_by', $oldCompanyId)
            ->where('type', '!=', 'company')
            ->count();

        // Generate recommendation
        $totalMasterData = array_sum($preview['master_data_to_process']);
        $totalUserData = array_sum($preview['user_data_to_copy']);

        if ($totalMasterData == 0 && $totalUserData == 0) {
            $preview['recommendation'] = "Company appears to be empty - refresh will just apply latest template.";
        } elseif ($totalMasterData > 50) {
            $preview['recommendation'] = "Large amount of master data ({$totalMasterData} records). Review carefully before proceeding.";
        } else {
            $preview['recommendation'] = "Safe to proceed - {$totalMasterData} master data records and {$totalUserData} user records to preserve.";
        }

        return $preview;
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
        $availableTemplates = TemplateCompanyConfig::getAvailableCurrencies();

        return [
            'old_company_id' => $oldCompanyId,
            'old_company_currency' => $oldCurrency,
            'available_templates' => $availableTemplates,
            'compatible_template' => $this->findTemplateCompanyByCurrency($oldCurrency),
            'is_compatible' => $this->findTemplateCompanyByCurrency($oldCurrency) !== null
        ];
    }
}
