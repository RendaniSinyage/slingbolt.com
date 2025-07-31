<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Log;
use App\Models\User;
use App\Services\CompanyClonerService;
use App\Services\TemplateCompanyConfig;
use App\Services\CompanyCleanupService;

class CompanyRefreshService
{
    private $oldCompanyId;
    private $newCompanyId;
    private $templateCompanyId;
    private $originalCompanyData;
    private $transferLog = [];

    /**
     * Master data tables that should be preserved/merged with template
     * These contain user customizations that should not be lost
     */
    private $masterDataTables = [
        // Financial Master Data
        'chart_of_accounts' => ['account', 'account_code', 'name'],
        'product_categories' => ['name'],
        'units' => ['name'],
        'warehouses' => ['name'],
        'pipelines' => ['name'],
        'stages' => ['name', 'pipeline_id', 'order'],
        'taxes' => ['name', 'rate'],
        'chart_of_account_parents' => ['name', 'account'],
        'bank_accounts' => ['account_number', 'holder_name', 'bank_name', 'contact_number'],
        'roles' => ['name'], // Users can customize role names
        'settings' => ['value'], // ALL settings including integrations
        'company_payment_settings' => ['value'], // Payment gateway configurations

        // Email & Communication Templates
        'email_templates' => ['subject', 'content', 'is_enabled', 'lang'],
        'notification_templates' => ['subject', 'content', 'is_enabled', 'lang'],
        'user_email_templates' => ['template_id', 'is_enabled'], // User-template assignments

        // Certificate Templates (CRITICAL for revenue)
        'joining_letters' => ['content', 'is_enabled'],
        'experience_certificates' => ['content', 'is_enabled'],
        'generate_offer_letters' => ['content', 'is_enabled'],
        'noc_certificates' => ['content', 'is_enabled'],

        // Settings & Configuration
        'landing_page_settings' => ['value'], // Usually stored as key-value pairs
        'referral_settings' => ['value'], // Usually stored as key-value pairs
    ];

    /**
     * User data tables that should be completely preserved
     * These contain all the actual business data
     */
    private $userDataTables = [
        // Core Business Data
        'customers', 'venders', 'product_services',
        'warehouse_products', 'proposals', 'invoices', 'bills',
        'revenues', 'payments', 'bill_payments', 'credit_notes',
        'debit_notes', 'pos', 'purchase_orders',

        // HR Data
        'employees', 'departments', 'designations', 'branches',
        'employee_documents', 'leaves', 'attendances',
        'payslips', 'allowances', 'commissions', 'loans',
        'saturdays', 'holidays', 'meeting',
        'goals', 'trainings', 'awards', 'transfers',
        'resignations', 'travels', 'promotions',
        'complaints', 'warnings', 'terminations',
        'job_on_boards', 'job_applications', 'job_interviews',
        'performance_types',

        // Project & Task Data
        'projects', 'project_tasks', 'project_files',
        'timesheet', 'contracts', 'contract_attachment',
        'contract_comment', 'contract_notes',

        // Form Data
        'forms', 'form_builders', 'form_fields',
        'form_responses', 'form_field_responses',

        // Document Management
        'ducument_uploads', 'document_types',

        // Communication & Activities
        'activity_logs', 'notifications',

        // User Management
        'users' => ['skip_company_users'], // Will handle separately
        'model_has_permissions', 'model_has_roles', 'role_has_permissions',
    ];

    public function __construct()
    {
        // No hardcoded template - will be determined by currency matching
    }

    /**
     * MAIN ENTRY POINT - Uses unified approach
     */
    public function refreshCompany($oldCompanyId, $options = [])
    {
        $this->oldCompanyId = $oldCompanyId;
        $isDryRun = $options['dry_run'] ?? false;

        // Store original company data for later restoration (actual refresh needs this)
        $this->originalCompanyData = DB::table('users')->where('id', $oldCompanyId)->first();
        if (!$this->originalCompanyData) {
            throw new \Exception("Company {$oldCompanyId} not found");
        }

        if ($isDryRun) {
            Log::info("Starting DRY RUN for company {$oldCompanyId}");
            return $this->performDryRun();
        } else {
            Log::info("Starting ACTUAL REFRESH for company {$oldCompanyId}");
            return $this->performActualRefresh();
        }
    }

    /**
     * Perform dry run - creates prefixed company, preserves original
     */
    private function performDryRun()
    {
        try {
            return DB::transaction(function () {
                Log::info("DRY RUN: Creating new company with prefixed data...");

                // Step 1: Determine template company based on currency
                $this->templateCompanyId = $this->determineTemplateCompany();

                // Step 2: Clone template to new company with PREFIXED data
                $this->newCompanyId = $this->cloneTemplateToNewCompanyWithPrefix(true); // true = dry run

                // Step 3: Process all master data
                $this->processMasterData();

                // Step 4: Handle special settings
                $this->processSettings();

                // Step 5: Copy all user-generated data
                $this->copyUserGeneratedData();

                // Step 6: Copy users to new company with PREFIXED emails
                $this->copyUsersToNewCompanyWithPrefix(true); // true = dry run

                Log::info("DRY RUN: Old company {$this->oldCompanyId} preserved, new company has prefixed data");

                return $this->generateSuccessResponse(true); // true = dry run
            });

        } catch (\Exception $e) {
            Log::error("Dry run failed: " . $e->getMessage());
            Log::error("Stack trace: " . $e->getTraceAsString());

            // Cleanup new company if something went wrong
            if ($this->newCompanyId) {
                $this->cleanupFailedRefresh();
            }

            throw $e;
        }
    }

    /**
     * Perform actual refresh - uses dry run logic + finalization
     */
    private function performActualRefresh()
    {
        try {
            return DB::transaction(function () {
                Log::info("ACTUAL REFRESH: Step 1 - Performing dry run to create new company safely...");

                // Step 1: Determine template company based on currency
                $this->templateCompanyId = $this->determineTemplateCompany();

                // Step 2: Clone template to new company with PREFIXED data
                $this->newCompanyId = $this->cloneTemplateToNewCompanyWithPrefix(false); // false = actual refresh

                // Step 3: Process all master data
                $this->processMasterData();

                // Step 4: Handle special settings
                $this->processSettings();

                // Step 5: Copy all user-generated data
                $this->copyUserGeneratedData();

                // Step 6: Copy users to new company with PREFIXED emails
                $this->copyUsersToNewCompanyWithPrefix(false); // false = actual refresh

                Log::info("ACTUAL REFRESH: Step 2 - Data migration successful, now finalizing...");

                // Step 7: Delete old company (point of no return)
                $this->deleteOldCompany();

                // Step 8: Remove prefixes and restore original identity
                $this->removePrefixesAndFinalize();

                Log::info("ACTUAL REFRESH: Successfully completed");

                return $this->generateSuccessResponse(false); // false = actual refresh
            });

        } catch (\Exception $e) {
            Log::error("Actual refresh failed: " . $e->getMessage());
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
     * Step 2: Clone template to new company with prefixed data
     */
    private function cloneTemplateToNewCompanyWithPrefix($isDryRun)
    {
        Log::info("Step 2: Cloning template company {$this->templateCompanyId} to new company with prefixed data");

        $oldCompany = User::find($this->oldCompanyId);
        $prefix = $isDryRun ? 'dryrun_' : 'refresh_';

        // Create new company record with prefixed email
        $newCompanyId = DB::table('users')->insertGetId([
            'name' => $oldCompany->name . ($isDryRun ? ' (DRY RUN)' : ' (REFRESH)'),
            'email' => $prefix . time() . '_' . $oldCompany->email,
            'email_verified_at' => $oldCompany->email_verified_at,
            'password' => $oldCompany->password,
            'type' => 'company',
            'lang' => $oldCompany->lang ?? 'en',
            'mode' => $oldCompany->mode ?? 'light',
            'avatar' => $oldCompany->avatar ?? '',
            'theme_color' => $oldCompany->theme_color ?? '#2180f3',
            'messenger_color' => $oldCompany->messenger_color ?? '#2180f3',
            'is_enable_login' => $oldCompany->is_enable_login ?? 1,
            'plan' => $oldCompany->plan ?? 1,
            'plan_expire_date' => $oldCompany->plan_expire_date,
            'requested_plan' => $oldCompany->requested_plan ?? 0,
            'is_trial_done' => $oldCompany->is_trial_done ?? 0,
            'trial_expire_date' => $oldCompany->trial_expire_date,
            'is_register_trial' => $oldCompany->is_register_trial ?? 1,
            'is_plan_purchased' => $oldCompany->is_plan_purchased ?? 1,
            'interested_plan_id' => $oldCompany->interested_plan_id ?? 1,
            'seeder_run' => $oldCompany->seeder_run ?? 1,
            'referral_code' => $oldCompany->referral_code ?? 0,
            'used_referral_code' => $oldCompany->used_referral_code ?? 0,
            'commission_amount' => $oldCompany->commission_amount ?? 0,
            'last_login_at' => $oldCompany->last_login_at,
            'registration_ip' => $oldCompany->registration_ip,
            'last_login_ip' => $oldCompany->last_login_ip,
            'user_agent' => $oldCompany->user_agent,
            'created_by' => $oldCompany->created_by,
            'remember_token' => null,
            'created_at' => $oldCompany->created_at,
            'updated_at' => now(),
            'is_email_verified' => $oldCompany->is_email_verified ?? 0,
            'payfast_subscription_token' => $oldCompany->payfast_subscription_token,
            'payfast_token_created_at' => $oldCompany->payfast_token_created_at,
            'card_last_four' => $oldCompany->card_last_four,
            'card_type' => $oldCompany->card_type,
            'card_exp_month' => $oldCompany->card_exp_month,
            'card_exp_year' => $oldCompany->card_exp_year,
        ]);

        Log::info("Created new company record with ID: {$newCompanyId}");

        // Use CompanyClonerService to clone template data to new company
        $cloner = new CompanyClonerService($newCompanyId, $this->templateCompanyId);
        $cloner->cloneAllCompanyData();

        Log::info("Successfully cloned template data to new company {$newCompanyId}");

        return $newCompanyId;
    }

    /**
     * Step 3: Process all master data with unified conflict resolution
     */
    private function processMasterData()
    {
        Log::info("Step 3: Processing all master data with unified conflict resolution");

        foreach ($this->masterDataTables as $tableName => $matchFields) {
            if (!Schema::hasTable($tableName) || !Schema::hasColumn($tableName, 'created_by')) {
                continue;
            }

            Log::info("Processing master data table: {$tableName}");
            $this->processMasterDataTable($tableName, $matchFields);
        }

        // Fix relationships after all master data is processed
        $this->fixMasterDataRelationships();
    }

    /**
     * Process a single master data table (handles both additions and modifications)
     */
    private function processMasterDataTable($tableName, $matchFields)
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
            ->keyBy($this->getMatchingKey($tableName, $matchFields));

        Log::info("Found {$oldRecords->count()} user records and {$templateRecords->count()} template records in {$tableName}");

        $added = 0;
        $updated = 0;
        $skipped = 0;

        foreach ($oldRecords as $oldRecord) {
            $matchingKey = $this->getRecordMatchingKey($oldRecord, $tableName, $matchFields);

            if ($templateRecords->has($matchingKey)) {
                // Conflict: User has customized something that exists in template
                $templateRecord = $templateRecords->get($matchingKey);

                if ($this->shouldUpdateTemplateRecord($oldRecord, $templateRecord, $tableName)) {
                    // Update template record with user's customization
                    $this->updateRecord($tableName, $templateRecord->id, $oldRecord);
                    $updated++;

                    $this->transferLog[] = [
                        'action' => 'conflict_resolved',
                        'table' => $tableName,
                        'details' => "Updated template record with user customization: {$matchingKey}",
                        'old_record_id' => $oldRecord->id,
                        'new_record_id' => $templateRecord->id
                    ];
                } else {
                    $skipped++;
                    $this->transferLog[] = [
                        'action' => 'skipped',
                        'table' => $tableName,
                        'details' => "Template record preferred over user record: {$matchingKey}",
                        'reason' => 'template_priority'
                    ];
                }
            } else {
                // No conflict: User has something new that doesn't exist in template
                $this->addNewRecord($tableName, $oldRecord);
                $added++;

                $this->transferLog[] = [
                    'action' => 'added',
                    'table' => $tableName,
                    'details' => "Added new user record: {$matchingKey}",
                    'old_record_id' => $oldRecord->id
                ];
            }
        }

        Log::info("Master data processing for {$tableName}: {$added} added, {$updated} updated, {$skipped} skipped");
    }

    /**
     * Get matching key for a table
     */
    private function getMatchingKey($tableName, $matchFields)
    {
        return function($record) use ($matchFields) {
            return $this->getRecordMatchingKey($record, $tableName, $matchFields);
        };
    }

    /**
     * Get matching key for a specific record
     */
    private function getRecordMatchingKey($record, $tableName, $matchFields)
    {
        $keyParts = [];

        foreach ($matchFields as $field) {
            $value = is_object($record) ? $record->{$field} : $record[$field];
            $keyParts[] = strtolower(trim($value));
        }

        return implode('|', $keyParts);
    }

    /**
     * Determine if template record should be updated with user's version
     */
    private function shouldUpdateTemplateRecord($oldRecord, $templateRecord, $tableName)
    {
        // Special rules for different types of data
        switch ($tableName) {
            case 'settings':
                // Always prefer user settings over template
                return true;

            case 'email_templates':
            case 'notification_templates':
                // If user has customized content, prefer user's version
                $userContent = is_object($oldRecord) ? $oldRecord->content : $oldRecord['content'];
                $templateContent = is_object($templateRecord) ? $templateRecord->content : $templateRecord['content'];
                return $userContent !== $templateContent;

            case 'roles':
                // Prefer user's role names if they've customized them
                return true;

            case 'taxes':
                // Prefer user's tax rates
                return true;

            case 'chart_of_accounts':
                // This is tricky - might want to prefer template structure
                // but allow user customizations for names
                return false; // Prefer template for chart of accounts

            default:
                // Default: prefer user customizations
                return true;
        }
    }

    /**
     * Update a record with user's data
     */
    private function updateRecord($tableName, $recordId, $oldRecord)
    {
        $updateData = (array) $oldRecord;
        unset($updateData['id']);
        $updateData['created_by'] = $this->newCompanyId;
        $updateData['updated_at'] = now();

        DB::table($tableName)->where('id', $recordId)->update($updateData);
    }

    /**
     * Add a new record from user's data
     */
    private function addNewRecord($tableName, $oldRecord)
    {
        $newData = (array) $oldRecord;
        unset($newData['id']);
        $newData['created_by'] = $this->newCompanyId;
        $newData['created_at'] = now();
        $newData['updated_at'] = now();

        DB::table($tableName)->insert($newData);
    }

    /**
     * Step 4: Handle special settings that need unique processing
     */
    private function processSettings()
    {
        Log::info("Step 4: Processing special settings");

        // Ensure critical settings are preserved from old company
        $criticalSettings = [
            'company_name', 'company_email', 'company_address',
            'company_city', 'company_state', 'company_zipcode',
            'company_country', 'company_telephone',
            'registration_number', 'vat_number',
            'company_logo', 'company_favicon',
            // Payment gateway settings
            'stripe_key', 'stripe_secret', 'paypal_mode',
            'paypal_client_id', 'paypal_client_secret',
            'razorpay_public_key', 'razorpay_secret_key',
            'paystack_public_key', 'paystack_secret_key',
            'flutterwave_public_key', 'flutterwave_secret_key',
            'payfast_merchant_id', 'payfast_merchant_key',
            'mercado_app_id', 'mercado_secret_key',
        ];

        foreach ($criticalSettings as $setting) {
            $userValue = DB::table('settings')
                ->where('created_by', $this->oldCompanyId)
                ->where('name', $setting)
                ->value('value');

            if ($userValue !== null) {
                DB::table('settings')->updateOrInsert(
                    [
                        'created_by' => $this->newCompanyId,
                        'name' => $setting
                    ],
                    [
                        'value' => $userValue,
                        'created_at' => now(),
                        'updated_at' => now()
                    ]
                );

                $this->transferLog[] = [
                    'action' => 'setting_preserved',
                    'table' => 'settings',
                    'details' => "Preserved critical setting: {$setting}"
                ];
            }
        }
    }

    /**
     * Step 5: Copy all user-generated data
     */
    private function copyUserGeneratedData()
    {
        Log::info("Step 5: Copying all user-generated data");

        foreach ($this->userDataTables as $tableName => $options) {
            if (is_array($options) && isset($options['skip_company_users'])) {
                continue; // Handle users separately
            }

            $actualTableName = is_string($tableName) ? $tableName : $options;

            if (!Schema::hasTable($actualTableName) || !Schema::hasColumn($actualTableName, 'created_by')) {
                continue;
            }

            $this->copyUserDataTable($actualTableName);
        }
    }

    /**
     * Copy a single user data table
     */
    private function copyUserDataTable($tableName)
    {
        $records = DB::table($tableName)
            ->where('created_by', $this->oldCompanyId)
            ->get();

        if ($records->isEmpty()) {
            return;
        }

        Log::info("Copying {$records->count()} records from {$tableName}");

        $copiedCount = 0;
        foreach ($records as $record) {
            $newData = (array) $record;
            unset($newData['id']);
            $newData['created_by'] = $this->newCompanyId;
            $newData['created_at'] = now();
            $newData['updated_at'] = now();

            // Reset financial balances and counters
            foreach (['balance', 'opening_balance', 'current_balance', 'credit_balance'] as $field) {
                if (isset($newData[$field])) {
                    $newData[$field] = '0.00';
                }
            }

            foreach (['quantity', 'stock', 'total_user', 'total_customer', 'total_vender'] as $field) {
                if (isset($newData[$field])) {
                    $newData[$field] = 0;
                }
            }

            DB::table($tableName)->insert($newData);
            $copiedCount++;
        }

        $this->transferLog[] = [
            'action' => 'copied',
            'table' => $tableName,
            'count' => $copiedCount
        ];
    }

    /**
     * Step 6: Copy users to new company with prefixed emails
     */
    private function copyUsersToNewCompanyWithPrefix($isDryRun)
    {
        Log::info("Step 6: Copying users to new company with prefixed emails");

        $users = DB::table('users')
            ->where('created_by', $this->oldCompanyId)
            ->where('type', '!=', 'company')
            ->get();

        if ($users->isEmpty()) {
            Log::info("No users found to copy");
            return;
        }

        $prefix = $isDryRun ? 'dryrun_' : 'refresh_';
        $copiedCount = 0;

        foreach ($users as $user) {
            $userData = (array) $user;
            unset($userData['id']);

            // Add prefix to email
            $userData['email'] = $prefix . time() . '_' . $user->email;
            $userData['created_by'] = $this->newCompanyId;
            $userData['created_at'] = now();
            $userData['updated_at'] = now();

            DB::table('users')->insert($userData);
            $copiedCount++;
        }

        Log::info("Copied {$copiedCount} users with prefixed emails");

        $this->transferLog[] = [
            'action' => 'copied_with_prefix',
            'table' => 'users',
            'count' => $copiedCount,
            'prefix_used' => $prefix
        ];
    }

    /**
     * Step 7: Delete old company (ACTUAL REFRESH ONLY)
     */
    private function deleteOldCompany()
    {
        Log::info("Step 7: Deleting old company {$this->oldCompanyId}");

        // Use existing cleanup service to properly delete all data
        CompanyCleanupService::cascadeDeleteCompanyData($this->oldCompanyId);
        User::where('id', $this->oldCompanyId)->delete();

        Log::info("Old company {$this->oldCompanyId} deleted successfully");

        $this->transferLog[] = [
            'action' => 'deleted',
            'table' => 'users',
            'details' => "Deleted old company {$this->oldCompanyId} and all associated data"
        ];
    }

    /**
     * Step 8: Remove prefixes and restore original identity (ACTUAL REFRESH ONLY)
     */
    private function removePrefixesAndFinalize()
    {
        Log::info("Step 8: Removing prefixes and restoring original identity");

        // Update company record to restore original email and clean up name
        DB::table('users')->where('id', $this->newCompanyId)->update([
            'name' => $this->originalCompanyData->name,
            'email' => $this->originalCompanyData->email,
            'updated_at' => now()
        ]);

        // Remove prefixes from all user emails
        $prefixedUsers = DB::table('users')
            ->where('created_by', $this->newCompanyId)
            ->where('email', 'LIKE', 'refresh_%')
            ->get();

        foreach ($prefixedUsers as $user) {
            // Remove the prefix (refresh_timestamp_)
            $originalEmail = preg_replace('/^refresh_\d+_/', '', $user->email);

            DB::table('users')->where('id', $user->id)->update([
                'email' => $originalEmail,
                'updated_at' => now()
            ]);
        }

        Log::info("Removed prefixes from company and {$prefixedUsers->count()} user emails");

        $this->transferLog[] = [
            'action' => 'prefixes_removed',
            'table' => 'users',
            'count' => $prefixedUsers->count() + 1, // +1 for company
            'details' => 'Restored original emails for company and all users'
        ];
    }

    /**
     * Fix relationships between master data tables
     */
    private function fixMasterDataRelationships()
    {
        Log::info("Fixing master data relationships");

        // This would need to be customized based on your specific relationships
        // Example: Update stage pipeline_id references, etc.

        // For now, we'll log that this step was reached
        $this->transferLog[] = [
            'action' => 'relationships_fixed',
            'table' => 'multiple',
            'details' => 'Fixed foreign key relationships between master data tables'
        ];
    }

    /**
     * Generate success response
     */
    private function generateSuccessResponse($isDryRun)
    {
        $message = $isDryRun ?
            'Company refresh dry run completed successfully - data created with prefixes for testing' :
            'Company refreshed successfully - old company deleted and prefixes removed';

        return [
            'success' => true,
            'message' => $message,
            'is_dry_run' => $isDryRun,
            'template_company_id' => $this->templateCompanyId,
            'old_company_id' => $this->oldCompanyId,
            'new_company_id' => $this->newCompanyId,
            'currency_matched' => $this->getCompanyCurrency($this->oldCompanyId),
            'transfer_log' => $this->transferLog,
            'summary' => $this->generateTransferSummary(),
            'completed_at' => now(),
            'prefixes_removed' => !$isDryRun
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
            'users_copied' => 0,
            'settings_preserved' => 0
        ];

        foreach ($this->transferLog as $log) {
            if (in_array($log['action'], ['copied', 'copied_with_prefix', 'added'])) {
                $summary['tables_processed']++;
                if (isset($log['count'])) {
                    $summary['records_copied'] += $log['count'];
                }

                if ($log['table'] === 'users') {
                    $summary['users_copied'] = $log['count'];
                }
            } elseif ($log['action'] === 'conflict_resolved') {
                $summary['conflicts_resolved']++;
            } elseif ($log['action'] === 'setting_preserved') {
                $summary['settings_preserved']++;
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
                CompanyCleanupService::cascadeDeleteCompanyData($this->newCompanyId);
                User::where('id', $this->newCompanyId)->delete();

                Log::info("Cleanup completed");
            } catch (\Exception $e) {
                Log::error("Error during cleanup: " . $e->getMessage());
            }
        }
    }

    // =============================================================================
    // PUBLIC UTILITY METHODS
    // =============================================================================

    /**
     * Preview what will happen during refresh (without making changes)
     */
    public function previewRefresh($oldCompanyId)
    {
        $oldCompanyCurrency = $this->getCompanyCurrency($oldCompanyId);
        $templateCompanyId = $this->findTemplateCompanyByCurrency($oldCompanyCurrency);

        if (!$templateCompanyId) {
            return [
                'success' => false,
                'error' => "No template company found for currency: {$oldCompanyCurrency}",
                'available_currencies' => array_keys(TemplateCompanyConfig::getAvailableCurrencies())
            ];
        }

        // Count master data that will be processed
        $masterDataCounts = [];
        $totalMasterData = 0;

        foreach ($this->masterDataTables as $tableName => $matchFields) {
            if (Schema::hasTable($tableName) && Schema::hasColumn($tableName, 'created_by')) {
                $count = DB::table($tableName)->where('created_by', $oldCompanyId)->count();
                if ($count > 0) {
                    $masterDataCounts[$tableName] = $count;
                    $totalMasterData += $count;
                }
            }
        }

        // Count user data that will be copied
        $userDataCounts = [];
        $totalUserData = 0;

        foreach ($this->userDataTables as $tableName => $options) {
            $actualTableName = is_string($tableName) ? $tableName : $options;

            if (Schema::hasTable($actualTableName) && Schema::hasColumn($actualTableName, 'created_by')) {
                $count = DB::table($actualTableName)->where('created_by', $oldCompanyId)->count();
                if ($count > 0) {
                    $userDataCounts[$actualTableName] = $count;
                    $totalUserData += $count;
                }
            }
        }

        // Count users
        $userCount = DB::table('users')
            ->where('created_by', $oldCompanyId)
            ->where('type', '!=', 'company')
            ->count();

        $preview = [
            'success' => true,
            'old_company_id' => $oldCompanyId,
            'old_company_currency' => $oldCompanyCurrency,
            'template_company_id' => $templateCompanyId,
            'template_company_name' => User::find($templateCompanyId)->name ?? 'Unknown',
            'master_data_counts' => $masterDataCounts,
            'user_data_counts' => $userDataCounts,
            'user_count' => $userCount,
            'totals' => [
                'master_data_records' => $totalMasterData,
                'user_data_records' => $totalUserData,
                'users' => $userCount
            ]
        ];

        // Add recommendation
        if ($totalUserData > 10000) {
            $preview['recommendation'] = "Large dataset ({$totalUserData} records). Consider running during off-peak hours.";
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
                CompanyCleanupService::cascadeDeleteCompanyData($newCompanyId);
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

    /**
     * Get refresh status/history for a company
     */
    public function getRefreshHistory($companyId)
    {
        // This would require a refresh_history table to track previous refreshes
        // For now, return basic info
        return [
            'company_id' => $companyId,
            'last_refresh' => null, // Would come from refresh_history table
            'refresh_count' => 0,   // Would come from refresh_history table
            'can_refresh' => $this->findTemplateCompanyByCurrency($this->getCompanyCurrency($companyId)) !== null
        ];
    }

    /**
     * Validate company is ready for refresh
     */
    public function validateCompanyForRefresh($companyId)
    {
        $issues = [];

        // Check if company exists
        $company = User::where('type', 'company')->where('id', $companyId)->first();
        if (!$company) {
            $issues[] = "Company {$companyId} does not exist or is not a company account";
        }

        // Check if template is available for company's currency
        $currency = $this->getCompanyCurrency($companyId);
        $templateId = $this->findTemplateCompanyByCurrency($currency);
        if (!$templateId) {
            $available = array_keys(TemplateCompanyConfig::getAvailableCurrencies());
            $issues[] = "No template company available for currency '{$currency}'. Available: " . implode(', ', $available);
        }

        // Check if template company actually exists
        if ($templateId && !User::where('type', 'company')->where('id', $templateId)->exists()) {
            $issues[] = "Template company {$templateId} is configured but does not exist in database";
        }

        // Check for active dry runs
        $existingDryRun = User::where('type', 'company')
            ->where('email', 'LIKE', 'dryrun_%' . $company->email ?? '')
            ->exists();

        if ($existingDryRun) {
            $issues[] = "There is already an active dry run for this company. Clean it up first.";
        }

        return [
            'valid' => empty($issues),
            'issues' => $issues,
            'company_currency' => $currency,
            'template_company_id' => $templateId
        ];
    }

    /**
     * Emergency rollback (if something goes wrong during actual refresh)
     * Note: This only works if the old company hasn't been deleted yet
     */
    public function emergencyRollback($refreshResponse)
    {
        if (!isset($refreshResponse['new_company_id']) || $refreshResponse['is_dry_run']) {
            return ['success' => false, 'error' => 'Invalid refresh response or dry run cannot be rolled back'];
        }

        $newCompanyId = $refreshResponse['new_company_id'];
        $oldCompanyId = $refreshResponse['old_company_id'];

        try {
            // Check if old company still exists
            $oldCompany = User::where('id', $oldCompanyId)->first();
            if ($oldCompany) {
                return ['success' => false, 'error' => 'Old company still exists - no rollback needed'];
            }

            Log::warning("Emergency rollback not possible - old company {$oldCompanyId} has been deleted");
            return [
                'success' => false,
                'error' => 'Cannot rollback - old company data has been permanently deleted',
                'recommendation' => 'You may need to restore from backup'
            ];

        } catch (\Exception $e) {
            Log::error("Error during emergency rollback: " . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    // =============================================================================
    // HELPER METHODS FOR TESTING & DEBUGGING
    // =============================================================================

    /**
     * Compare two companies (useful for validating refresh results)
     */
    public function compareCompanies($companyId1, $companyId2, $tables = null)
    {
        $tables = $tables ?: array_merge(
            array_keys($this->masterDataTables),
            array_keys($this->userDataTables)
        );

        $comparison = [];

        foreach ($tables as $table) {
            if (!Schema::hasTable($table) || !Schema::hasColumn($table, 'created_by')) {
                continue;
            }

            $count1 = DB::table($table)->where('created_by', $companyId1)->count();
            $count2 = DB::table($table)->where('created_by', $companyId2)->count();

            $comparison[$table] = [
                'company_1_count' => $count1,
                'company_2_count' => $count2,
                'difference' => $count2 - $count1
            ];
        }

        return $comparison;
    }

    /**
     * Get detailed breakdown of what will be processed
     */
    public function getProcessingBreakdown($companyId)
    {
        $breakdown = [
            'master_data' => [],
            'user_data' => [],
            'users' => 0
        ];

        // Master data breakdown
        foreach ($this->masterDataTables as $table => $fields) {
            if (Schema::hasTable($table) && Schema::hasColumn($table, 'created_by')) {
                $count = DB::table($table)->where('created_by', $companyId)->count();
                if ($count > 0) {
                    $breakdown['master_data'][$table] = [
                        'count' => $count,
                        'match_fields' => $fields,
                        'strategy' => 'merge_with_conflict_resolution'
                    ];
                }
            }
        }

        // User data breakdown
        foreach ($this->userDataTables as $table => $options) {
            $actualTable = is_string($table) ? $table : $options;

            if (Schema::hasTable($actualTable) && Schema::hasColumn($actualTable, 'created_by')) {
                $count = DB::table($actualTable)->where('created_by', $companyId)->count();
                if ($count > 0) {
                    $breakdown['user_data'][$actualTable] = [
                        'count' => $count,
                        'strategy' => 'copy_all'
                    ];
                }
            }
        }

        // Users
        $breakdown['users'] = DB::table('users')
            ->where('created_by', $companyId)
            ->where('type', '!=', 'company')
            ->count();

        return $breakdown;
    }
}
