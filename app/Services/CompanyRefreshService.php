<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Log;
use App\Models\User;
use App\Services\CompanyClonerService;
use App\Services\TemplateCompanyConfig;

class CompanyRefreshService
{
    private $companyId;
    private $templateCompanyId;
    private $originalBackup = [];
    private $transferLog = [];
    private $isDryRun = false;
    private $roleIdMappings = [];

    /**
     * Master data tables that should be merged with template
     * Based on complete table list and your specific requirements
     */
    private $masterDataTables = [
        // Financial Master Data - TEMPLATE WINS (compliance, match by code)
        'taxes' => ['name', 'rate'], // Template wins for compliance

        // Categories - USER WINS in conflicts, but merge
        'product_service_categories' => ['name'],
        'product_service_units' => ['name'],
        'warehouses' => ['name'],
        'bank_accounts' => ['account_number', 'holder_name', 'bank_name'],
        'roles' => ['name'], // Users can customize role names

        // Sales & CRM - USER WINS in conflicts, but merge
        'pipelines' => ['name'],
        'stages' => ['name', 'pipeline_id'],
        'sources' => ['name'],
        'labels' => ['name'],
        'lead_stages' => ['name'],

        // HR Options - USER WINS in conflicts, but merge
        'leave_types' => ['title'],
        'allowance_options' => ['name'],
        'deduction_options' => ['name'],
        'loan_options' => ['name'],
        'goal_types' => ['name'],
        'award_types' => ['name'],
        'performance_types' => ['name'],
        'termination_types' => ['name'],
        'payslip_types' => ['name'],
        'training_types' => ['name'],

        // HR Structure - USER WINS completely
        'job_categories' => ['title'],
        'job_stages' => ['title'],
        'departments' => ['name'],
        'designations' => ['name'],
        'branches' => ['name'],

        // Document & Certificate Templates - USER WINS if content modified
        'joining_letters' => ['content', 'lang'],
        'experience_certificates' => ['content', 'lang'],
        'generate_offer_letters' => ['content', 'lang'],
        'noc_certificates' => ['content', 'lang'],

        // Email & Communication Templates - USER WINS if modified
        'email_template_langs' => ['parent_id', 'lang', 'subject' , 'content'],
        'notification_templates' => ['subject', 'content', 'lang'],
        'notification_template_langs' => ['parent_id', 'lang', 'content' , 'variables'],

        // System Templates - TEMPLATE WINS
        //'templates' => ['name', 'type'], // Template wins

        // Configuration that should be merged - USER WINS
        'company_payment_settings' => ['value'], // Payment gateway configurations
        'referral_settings' => ['value'], // Referral configurations
        'landing_page_settings' => ['value'], // Brand settings - USER WINS

        // Other configuration tables
        'custom_questions' => ['question'],
        'competencies' => ['name'],
        'languages' => ['code'],
        'contract_types' => ['name'],
        'bug_statuses' => ['title'],
        'task_stages' => ['name', 'type'],
    ];

    /**
     * Settings that should be preserved from user (never overwritten by template)
     */
    private $preservedSettings = [
        // Company Identity
        'company_name', 'company_email', 'company_address',
        'company_city', 'company_state', 'company_zipcode',
        'company_telephone', 'registration_number', 'vat_number',
        'company_logo', // company_favicon handled separately (template wins)

        // Payment Gateway Settings
        'stripe_key', 'stripe_secret', 'paypal_mode',
        'paypal_client_id', 'paypal_client_secret',
        'razorpay_public_key', 'razorpay_secret_key',
        'paystack_public_key', 'paystack_secret_key',
        'flutterwave_public_key', 'flutterwave_secret_key',
        'payfast_merchant_id', 'payfast_merchant_key',
        'mercado_app_id', 'mercado_secret_key',

        // User-specific settings from images
        'mail_driver', 'mail_host', 'mail_port', 'mail_username',
        'mail_password', 'mail_encryption', 'mail_from_address', 'mail_from_name',

        // System prefixes from images (user customized)
        'customer_prefix', 'supplier_prefix', 'proposal_prefix', 'invoice_prefix',
        'bill_prefix', 'quotation_prefix', 'purchase_prefix', 'pos_prefix',
        'journal_prefix', 'expense_prefix', 'employee_prefix', 'contract_prefix',
        'proposal_invoice_bill_purchase_pos_footer_title',
        'proposal_invoice_bill_purchase_pos_footer_note',
        'display_shipping_proposal_invoice_bill',
    ];

    /**
     * Settings that come from superadmin (never from user or template)
     */
    private $superadminSettings = [
        'chat_gpt_key', 'chat_gpt_model', // Superadmin controls AI
        'email_templates', // Superadmin per your instruction
    ];

    /**
     * Settings that should come from template (always overwritten)
     */
    private $templateSettings = [
        // System Configuration - TEMPLATE WINS
        'site_currency', 'site_currency_symbol', 'site_currency_symbol_position',
        'site_currency_symbol_space', 'site_currency_symbol_name',
        'decimal_number_format', 'site_decimal_separator', 'site_thousands_separator',
        'site_date_format', 'site_time_format',

        // Regional Settings - TEMPLATE WINS per your instruction
        'timezone', 'country', 'default_language',

        // Company branding - TEMPLATE WINS
        'company_favicon', // Template wins per your instruction

        // System Features - TEMPLATE WINS
        'tracking_interval', 'application_url',
        'storage_setting',

        // Theme (template provides good defaults)
        'color', 'color_flag', 'layout_settings',
        'cust_theme_bg', 'cust_darklayout', 'SITE_RTL',
    ];

    public function __construct()
    {
        // Constructor intentionally minimal
    }

    // =============================================================================
    // MAIN ENTRY POINTS
    // =============================================================================

    /**
     * Main entry point - refreshes company in place
     */
    public function refreshCompany($companyId, $options = [])
    {
        $this->companyId = $companyId;
        $this->isDryRun = $options['dry_run'] ?? false;

        // Validate company exists
        $company = User::where('type', 'company')->where('id', $companyId)->first();
        if (!$company) {
            throw new \Exception("Company {$companyId} not found");
        }

        Log::info($this->isDryRun ? "Starting DRY RUN for company {$companyId}" : "Starting ACTUAL REFRESH for company {$companyId}");

        try {
            return DB::transaction(function () {
                // Step 1: Determine template company
                $this->templateCompanyId = $this->determineTemplateCompany();

                // Step 2: Backup current data (for dry run comparison and rollback)
                $this->backupCurrentData();

                // Step 3: Merge template master data with existing
                $this->mergeTemplateMasterData();

                // Step 3b: Refresh chart of accounts with specialized logic
                $this->_refreshChartOfAccounts();

                // Step 4: Update settings strategically
                $this->mergeTemplateSettings();

                // Step 5: Add missing template configurations
                $this->addMissingTemplateConfigurations();

                // Step 6: Merge role permissions (NEW!)
                            $this->mergeRolePermissions();

                            $this->fixChartOfAccountsRelationships();

                Log::info($this->isDryRun ? "DRY RUN completed successfully" : "ACTUAL REFRESH completed successfully");

                return $this->generateSuccessResponse();
            });

        } catch (\Exception $e) {
            Log::error("Company refresh failed: " . $e->getMessage());
            Log::error("Stack trace: " . $e->getTraceAsString());

            if (!$this->isDryRun) {
                $this->rollbackChanges();
            }

            throw $e;
        }
    }

    /**
     * Dry run entry point
     */
    public function dryRun($companyId)
    {
        return $this->refreshCompany($companyId, ['dry_run' => true]);
    }

    // =============================================================================
    // CORE PROCESSING STEPS
    // =============================================================================

    /**
     * Step 1: Determine template company based on currency
     */
    private function determineTemplateCompany()
    {
        Log::info("Step 1: Determining template company based on currency");

        $companyCurrency = $this->getCompanyCurrency($this->companyId);

        if (!$companyCurrency) {
            throw new \Exception("Cannot determine currency for company {$this->companyId}");
        }

        Log::info("Company currency: {$companyCurrency}");

        $templateCompanyId = TemplateCompanyConfig::findTemplateByCurrency($companyCurrency);

        if (!$templateCompanyId) {
            $available = array_keys(TemplateCompanyConfig::getAvailableCurrencies());
            $availableList = implode(', ', $available);

            throw new \Exception(
                "No template company found for currency: {$companyCurrency}. " .
                "Available currencies: {$availableList}. " .
                "Please create a template company for {$companyCurrency} first."
            );
        }

        Log::info("Selected template company {$templateCompanyId} for currency {$companyCurrency}");

        return $templateCompanyId;
    }

    /**
     * Step 2: Backup current data for comparison/rollback
     */
    private function backupCurrentData()
    {
        Log::info("Step 2: Backing up current data");

        foreach ($this->masterDataTables as $tableName => $matchFields) {
            if (!Schema::hasTable($tableName) || !Schema::hasColumn($tableName, 'created_by')) {
                continue;
            }

            $records = DB::table($tableName)
                ->where('created_by', $this->companyId)
                ->get();

            if (!$records->isEmpty()) {
                $this->originalBackup[$tableName] = $records->toArray();
                Log::info("Backed up {$records->count()} records from {$tableName}");
            }
        }

        // Backup settings
        $settings = DB::table('settings')
            ->where('created_by', $this->companyId)
            ->get();

        $this->originalBackup['settings'] = $settings->toArray();
        Log::info("Backed up {$settings->count()} settings");

        // Backup role permissions
            $userRoleIds = DB::table('roles')
                ->where('created_by', $this->companyId)
                ->pluck('id')
                ->toArray();

            if (!empty($userRoleIds)) {
                $rolePermissions = DB::table('role_has_permissions')
                    ->whereIn('role_id', $userRoleIds)
                    ->get();

                $this->originalBackup['role_has_permissions'] = $rolePermissions->toArray();
                Log::info("Backed up {$rolePermissions->count()} role permissions");
            }
    }

    /**
     * Step 3: Merge template master data with existing
     */
    private function mergeTemplateMasterData()
    {
        Log::info("Step 3: Merging template master data with existing data");

        foreach ($this->masterDataTables as $tableName => $matchFields) {
            if (!Schema::hasTable($tableName) || !Schema::hasColumn($tableName, 'created_by')) {
                continue;
            }

            Log::info("Processing table: {$tableName}");
            $this->mergeTable($tableName, $matchFields);
        }
    }

    /**
     * Step 3b: Refresh chart of accounts with a specialized method
     */
    private function _refreshChartOfAccounts()
    {
        Log::info("Step 3b: Refreshing chart of accounts with specialized logic");

        // Mappings to track template IDs/names to user's new IDs
        $typeMap = []; // template type name -> user type id
        $subTypeMap = []; // template sub-type composite key -> user sub-type id
        $accountMap = []; // template account code -> user account id

        // === 1. Process Chart of Account Types ===
        Log::info("Processing chart_of_account_types");
        $userTypes = DB::table('chart_of_account_types')->where('created_by', $this->companyId)->get()->keyBy('name');
        $templateTypes = DB::table('chart_of_account_types')->where('created_by', $this->templateCompanyId)->get();

        foreach ($templateTypes as $templateType) {
            $userType = $userTypes->get($templateType->name);
            if ($userType) {
                // Type exists, just map it
                $typeMap[$templateType->name] = $userType->id;
            } else {
                // Type does not exist, create it
                if (!$this->isDryRun) {
                    $newTypeId = DB::table('chart_of_account_types')->insertGetId([
                        'name' => $templateType->name,
                        'created_by' => $this->companyId,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                    $typeMap[$templateType->name] = $newTypeId;
                }
                $this->transferLog[] = ['action' => 'added', 'table' => 'chart_of_account_types', 'details' => "Added type: {$templateType->name}", 'dry_run' => $this->isDryRun];
            }
        }

        // === 2. Process Chart of Account Sub-Types ===
        Log::info("Processing chart_of_account_sub_types");
        $userSubTypes = DB::table('chart_of_account_sub_types as st')
            ->join('chart_of_account_types as t', 'st.type', '=', 't.id')
            ->where('st.created_by', $this->companyId)
            ->select('st.*', 't.name as type_name')
            ->get()->keyBy(fn($item) => $item->name . '|' . $item->type_name);

        $templateSubTypes = DB::table('chart_of_account_sub_types as st')
            ->join('chart_of_account_types as t', 'st.type', '=', 't.id')
            ->where('st.created_by', $this->templateCompanyId)
            ->select('st.*', 't.name as type_name')
            ->get();

        foreach ($templateSubTypes as $templateSubType) {
            $compositeKey = $templateSubType->name . '|' . $templateSubType->type_name;
            $userSubType = $userSubTypes->get($compositeKey);

            if ($userSubType) {
                $subTypeMap[$compositeKey] = $userSubType->id;
            } else {
                $userTypeId = $typeMap[$templateSubType->type_name] ?? null;
                if ($userTypeId) {
                    if (!$this->isDryRun) {
                        $newSubTypeId = DB::table('chart_of_account_sub_types')->insertGetId([
                            'name' => $templateSubType->name,
                            'type' => $userTypeId,
                            'created_by' => $this->companyId,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                        $subTypeMap[$compositeKey] = $newSubTypeId;
                    }
                    $this->transferLog[] = ['action' => 'added', 'table' => 'chart_of_account_sub_types', 'details' => "Added sub-type: {$templateSubType->name}", 'dry_run' => $this->isDryRun];
                }
            }
        }

        // === 3. Process Chart of Accounts (Create/Update) ===
        Log::info("Processing chart_of_accounts (create/update)");
        $userAccounts = DB::table('chart_of_accounts')->where('created_by', $this->companyId)->get()->keyBy('code');
        $templateAccounts = DB::table('chart_of_accounts as ca')
            ->join('chart_of_account_types as t', 'ca.type', '=', 't.id')
            ->join('chart_of_account_sub_types as st', 'ca.sub_type', '=', 'st.id')
            ->where('ca.created_by', $this->templateCompanyId)
            ->select('ca.*', 't.name as type_name', 'st.name as sub_type_name')
            ->get();

        foreach ($templateAccounts as $templateAccount) {
            $userAccount = $userAccounts->get($templateAccount->code);
            $userTypeId = $typeMap[$templateAccount->type_name] ?? null;
            $subTypeCompositeKey = $templateAccount->sub_type_name . '|' . $templateAccount->type_name;
            $userSubTypeId = $subTypeMap[$subTypeCompositeKey] ?? null;

            if (!$userTypeId || !$userSubTypeId) {
                Log::warning("Skipping account code {$templateAccount->code} due to missing type/subtype mapping.");
                continue;
            }

            if ($userAccount) {
                // Account exists, update it
                $updateData = [
                    'name' => $templateAccount->name,
                    'is_enabled' => $templateAccount->is_enabled,
                    'type' => $userTypeId,
                    'sub_type' => $userSubTypeId,
                    'updated_at' => now(),
                ];
                if (!$this->isDryRun) {
                    DB::table('chart_of_accounts')->where('id', $userAccount->id)->update($updateData);
                }
                $accountMap[$templateAccount->code] = $userAccount->id;
                $this->transferLog[] = ['action' => 'updated', 'table' => 'chart_of_accounts', 'details' => "Updated account: {$templateAccount->code}", 'dry_run' => $this->isDryRun];
            } else {
                // Account does not exist, create it
                $insertData = [
                    'name' => $templateAccount->name,
                    'code' => $templateAccount->code,
                    'type' => $userTypeId,
                    'sub_type' => $userSubTypeId,
                    'parent' => 0, // Set parent to 0 initially
                    'is_enabled' => $templateAccount->is_enabled,
                    'description' => $templateAccount->description,
                    'created_by' => $this->companyId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
                if (!$this->isDryRun) {
                    $newAccountId = DB::table('chart_of_accounts')->insertGetId($insertData);
                    $accountMap[$templateAccount->code] = $newAccountId;
                }
                $this->transferLog[] = ['action' => 'added', 'table' => 'chart_of_accounts', 'details' => "Added account: {$templateAccount->code}", 'dry_run' => $this->isDryRun];
            }
        }

        // At this point, all user accounts that are also in the template have an entry in the accountMap
        // Fill the map for any user accounts that were not in the template, we need them for parent mapping
        $allUserAccounts = DB::table('chart_of_accounts')->where('created_by', $this->companyId)->get();
        foreach($allUserAccounts as $ua) {
            if (!isset($accountMap[$ua->code])) {
                $accountMap[$ua->code] = $ua->id;
            }
        }


        // === 4. Fix Parent Relationships ===
        Log::info("Fixing chart_of_accounts parent relationships");
        $templateIdToCode = DB::table('chart_of_accounts')->where('created_by', $this->templateCompanyId)->pluck('code', 'id');
        $templateAccountsWithParents = DB::table('chart_of_accounts')
            ->where('created_by', $this->templateCompanyId)
            ->whereNotNull('parent')
            ->where('parent', '!=', 0)
            ->get(['code', 'parent']);

        foreach ($templateAccountsWithParents as $templateAccount) {
            $parentCode = $templateIdToCode->get($templateAccount->parent);
            if ($parentCode) {
                $userChildId = $accountMap[$templateAccount->code] ?? null;
                $userParentId = $accountMap[$parentCode] ?? null;

                if ($userChildId && $userParentId) {
                    if (!$this->isDryRun) {
                        DB::table('chart_of_accounts')->where('id', $userChildId)->update(['parent' => $userParentId]);
                    }
                    $this->transferLog[] = ['action' => 'fixed_parent_relationship', 'table' => 'chart_of_accounts', 'details' => "Set parent for {$templateAccount->code} to {$parentCode}", 'dry_run' => $this->isDryRun];
                }
            }
        }
    }

    /**
     * Merge a single table with template data
     */
    private function mergeTable($tableName, $matchFields)
    {
        // Validate that the table has the expected columns
        $tableColumns = Schema::getColumnListing($tableName);
        $missingFields = array_diff($matchFields, $tableColumns);

        if (!empty($missingFields)) {
            Log::warning("Table {$tableName} is missing expected columns: " . implode(', ', $missingFields));
            Log::info("Available columns in {$tableName}: " . implode(', ', $tableColumns));

            // Filter out missing fields
            $matchFields = array_intersect($matchFields, $tableColumns);

            if (empty($matchFields)) {
                Log::warning("No valid matching fields found for {$tableName}, skipping table");
                return;
            }

            Log::info("Using available matching fields for {$tableName}: " . implode(', ', $matchFields));
        }

        // Get template records
        $templateRecords = DB::table($tableName)
            ->where('created_by', $this->templateCompanyId)
            ->get();

        if ($templateRecords->isEmpty()) {
            Log::info("No template data found in {$tableName}");
            return;
        }

        // Get existing user records
        $existingRecords = DB::table($tableName)
            ->where('created_by', $this->companyId)
            ->get()
            ->keyBy(function($record) use ($matchFields) {
                return $this->getRecordMatchingKey($record, $matchFields);
            });

        $added = 0;
        $updated = 0;
        $skipped = 0;

        foreach ($templateRecords as $templateRecord) {
            $matchingKey = $this->getRecordMatchingKey($templateRecord, $matchFields);

            if ($existingRecords->has($matchingKey)) {
                // Conflict: User has this record, template also has it
                $existingRecord = $existingRecords->get($matchingKey);

                if ($this->shouldUpdateWithTemplate($existingRecord, $templateRecord, $tableName)) {
                    if (!$this->isDryRun) {
                        $this->updateRecordWithTemplate($tableName, $existingRecord->id, $templateRecord);
                    }
                    $updated++;

                    // Track role ID mapping for permissions
                                    if ($tableName === 'roles') {
                                        $this->roleIdMappings[$templateRecord->id] = $existingRecord->id;
                                    }

                    $this->transferLog[] = [
                        'action' => 'updated_with_template',
                        'table' => $tableName,
                        'details' => "Updated existing record with template data: {$matchingKey}",
                        'dry_run' => $this->isDryRun
                    ];
                } else {
                    $skipped++;

                    // Still track role ID mapping even if we keep user version
                                    if ($tableName === 'roles') {
                                        $this->roleIdMappings[$templateRecord->id] = $existingRecord->id;
                                    }

                    $this->transferLog[] = [
                        'action' => 'kept_user_version',
                        'table' => $tableName,
                        'details' => "Kept user customization over template: {$matchingKey}",
                        'dry_run' => $this->isDryRun
                    ];
                }
            } else {
                // No conflict: Template has something user doesn't have
                if (!$this->isDryRun) {
                    $this->addTemplateRecord($tableName, $templateRecord);

                    // Track role ID mapping for new roles
                                    if ($tableName === 'roles') {
                                        $this->roleIdMappings[$templateRecord->id] = $newId;
                                    }
                }
                $added++;

                $this->transferLog[] = [
                    'action' => 'added_from_template',
                    'table' => $tableName,
                    'details' => "Added new template record: {$matchingKey}",
                    'dry_run' => $this->isDryRun
                ];
            }
        }

        Log::info("Table {$tableName}: {$added} added, {$updated} updated, {$skipped} kept user version");
    }

    /**
     * Step 4: Merge template settings strategically
     */
    private function mergeTemplateSettings()
    {
        Log::info("Step 4: Merging template settings strategically");

        // Get template settings
        $templateSettings = DB::table('settings')
            ->where('created_by', $this->templateCompanyId)
            ->get()
            ->keyBy('name');

        // Get existing settings
        $existingSettings = DB::table('settings')
            ->where('created_by', $this->companyId)
            ->get()
            ->keyBy('name');

        $preserved = 0;
        $updated = 0;
        $added = 0;
        $skipped = 0;

        foreach ($templateSettings as $settingName => $templateSetting) {
            if (in_array($settingName, $this->preservedSettings)) {
                // Always preserve user's version of these settings
                if ($existingSettings->has($settingName)) {
                    $preserved++;
                    $this->transferLog[] = [
                        'action' => 'setting_preserved',
                        'table' => 'settings',
                        'details' => "Preserved user setting: {$settingName}",
                        'dry_run' => $this->isDryRun
                    ];
                }
                continue;
            }

            if (in_array($settingName, $this->superadminSettings)) {
                // Skip superadmin settings - don't touch them
                $skipped++;
                $this->transferLog[] = [
                    'action' => 'setting_skipped_superadmin',
                    'table' => 'settings',
                    'details' => "Skipped superadmin setting: {$settingName}",
                    'dry_run' => $this->isDryRun
                ];
                continue;
            }

            if (in_array($settingName, $this->templateSettings)) {
                // Always use template version of these settings
                if (!$this->isDryRun) {
                    DB::table('settings')->updateOrInsert(
                        [
                            'created_by' => $this->companyId,
                            'name' => $settingName
                        ],
                        [
                            'value' => $templateSetting->value,
                            'created_at' => $templateSetting->created_at,
                            'updated_at' => now()
                        ]
                    );
                }

                if ($existingSettings->has($settingName)) {
                    $updated++;
                    $this->transferLog[] = [
                        'action' => 'setting_updated_from_template',
                        'table' => 'settings',
                        'details' => "Updated setting from template: {$settingName}",
                        'dry_run' => $this->isDryRun
                    ];
                } else {
                    $added++;
                    $this->transferLog[] = [
                        'action' => 'setting_added_from_template',
                        'table' => 'settings',
                        'details' => "Added new setting from template: {$settingName}",
                        'dry_run' => $this->isDryRun
                    ];
                }
            } else {
                // For other settings, only add if user doesn't have them
                if (!$existingSettings->has($settingName)) {
                    if (!$this->isDryRun) {
                        DB::table('settings')->insert([
                            'created_by' => $this->companyId,
                            'name' => $settingName,
                            'value' => $templateSetting->value,
                            'created_at' => $templateSetting->created_at,
                            'updated_at' => now()
                        ]);
                    }
                    $added++;
                    $this->transferLog[] = [
                        'action' => 'setting_added_if_missing',
                        'table' => 'settings',
                        'details' => "Added missing setting from template: {$settingName}",
                        'dry_run' => $this->isDryRun
                    ];
                }
            }
        }

        Log::info("Settings processing: {$preserved} preserved, {$updated} updated, {$added} added, {$skipped} skipped");
    }

    /**
     * Step 5: Add missing template configurations that user doesn't have
     */
    private function addMissingTemplateConfigurations()
    {
        Log::info("Step 5: Adding missing template configurations");

        // Tables that should be copied entirely if user has none
        $configurationTables = [
            'documents', // Document templates
            'indicators', // Performance indicators
            'milestones', // Project milestones
            // Note: Most other tables are handled in master data merging
        ];

        foreach ($configurationTables as $tableName) {
            if (!Schema::hasTable($tableName) || !Schema::hasColumn($tableName, 'created_by')) {
                continue;
            }

            $userCount = DB::table($tableName)->where('created_by', $this->companyId)->count();

            if ($userCount === 0) {
                // User has no data in this table, copy from template
                $templateRecords = DB::table($tableName)
                    ->where('created_by', $this->templateCompanyId)
                    ->get();

                if (!$templateRecords->isEmpty()) {
                    if (!$this->isDryRun) {
                        foreach ($templateRecords as $record) {
                            $newData = (array) $record;
                            unset($newData['id']);
                            $newData['created_by'] = $this->companyId;
                            $newData['created_at'] = now();
                            $newData['updated_at'] = now();

                            DB::table($tableName)->insert($newData);
                        }
                    }

                    $this->transferLog[] = [
                        'action' => 'copied_missing_config',
                        'table' => $tableName,
                        'count' => $templateRecords->count(),
                        'details' => "Copied {$templateRecords->count()} template records to empty table",
                        'dry_run' => $this->isDryRun
                    ];

                    Log::info("Copied {$templateRecords->count()} template records to {$tableName}");
                }
            }
        }
    }

    // =============================================================================
    // HELPER METHODS
    // =============================================================================

    /**
     * Get matching key for record comparison
     */
    private function getRecordMatchingKey($record, $matchFields)
    {
        $keyParts = [];

        foreach ($matchFields as $field) {
            $value = null;

            // Handle both object and array record types
            if (is_object($record)) {
                $value = property_exists($record, $field) ? $record->{$field} : null;
            } else {
                $value = isset($record[$field]) ? $record[$field] : null;
            }

            // If field doesn't exist, log warning and skip
            if ($value === null) {
                Log::warning("Field '{$field}' not found in record. Available fields: " .
                    implode(', ', is_object($record) ? array_keys(get_object_vars($record)) : array_keys($record)));
                continue;
            }

            $keyParts[] = strtolower(trim($value ?? ''));
        }

        // If no valid fields found, use ID as fallback
        if (empty($keyParts)) {
            $id = is_object($record) ? ($record->id ?? 'unknown') : ($record['id'] ?? 'unknown');
            $keyParts[] = 'id_' . $id;
        }

        return implode('|', $keyParts);
    }

    /**
     * Decide if existing record should be updated with template version
     * Based on your specific requirements
     */
    private function shouldUpdateWithTemplate($existingRecord, $templateRecord, $tableName)
    {
        switch ($tableName) {
            // TEMPLATE WINS - Always update with template
            case 'chart_of_accounts':
            case 'chart_of_account_parents':
            case 'chart_of_account_types':
            case 'chart_of_account_sub_types':
            case 'taxes':
                return true; // Template wins for compliance and is_enabled

            case 'templates':
                return true; // Template wins per your instruction

            // USER WINS IF CONTENT MODIFIED - Check if content is different
            case 'joining_letters':
            case 'experience_certificates':
            case 'generate_offer_letters':
            case 'noc_certificates':
                // Check if user has modified the content
                $userContent = is_object($existingRecord) ? $existingRecord->content : $existingRecord['content'];
                $templateContent = is_object($templateRecord) ? $templateRecord->content : $templateRecord['content'];

                // If content is the same, no modification - template can win
                // If content is different, user has modified - user wins
                return $userContent === $templateContent;

            case 'email_template_langs':
                        // Check if user has modified the subject
                        $userSubject = is_object($existingRecord) ? $existingRecord->subject : $existingRecord['subject'];
                        $templateSubject = is_object($templateRecord) ? $templateRecord->subject : $templateRecord['subject'];

                        // If same, template can win. If different, user has modified - user wins
                        return $userSubject === $templateSubject;
            case 'notification_templates':
            case 'notification_template_langs':
                // Check if user has modified the content/subject
                $userSubject = is_object($existingRecord) ? $existingRecord->subject : $existingRecord['subject'];
                $templateSubject = is_object($templateRecord) ? $templateRecord->subject : $templateRecord['subject'];

                // If same, template can win. If different, user has modified - user wins
                return $userSubject === $templateSubject;

            // USER WINS - Never update, keep user's version
            case 'job_categories':
            case 'job_stages':
            case 'departments':
            case 'designations':
            case 'branches':
            case 'roles':
            case 'warehouses':
            case 'bank_accounts':
            case 'company_payment_settings':
            case 'referral_settings':
            case 'landing_page_settings':
                return false; // User wins completely

            // BOTH BUT USER WINS - Only update if user record seems incomplete/default
            case 'product_service_categories':
            case 'product_service_units':
            case 'pipelines':
            case 'stages':
            case 'sources':
            case 'labels':
            case 'lead_stages':
            case 'allowance_options':
            case 'deduction_options':
            case 'loan_options':
            case 'goal_types':
            case 'award_types':
            case 'performance_types':
            case 'terminat ion_types':
            case 'payslip_types':
            case 'training_types':
            case 'custom_questions':
            case 'competencies':
            case 'contract_types':
            case 'task_stages':
                        // Check if user record looks like a default/minimal entry using 'name' field
                        $userContent = is_object($existingRecord) ? $existingRecord->name : $existingRecord['name'];
                        $isUserDefault = in_array(strtolower(trim($userContent)), [
                            'default', 'basic', 'standard', 'general', 'other', 'misc', 'temp', 'test'
                        ]);
                        return $isUserDefault; // Only update if user has default/temp values

                    case 'leave_types':
                        // Use 'title' field for leave_types since your schema has 'title', not 'name'
                        $userContent = is_object($existingRecord) ? $existingRecord->title : $existingRecord['title'];
                        $isUserDefault = in_array(strtolower(trim($userContent)), [
                            'default', 'basic', 'standard', 'general', 'other', 'misc', 'temp', 'test'
                        ]);
                        return $isUserDefault; // Only update if user has default/temp values

                    case 'bug_statuses':
                        // Use 'title' field for bug_statuses since your schema has 'title', not 'name'
                        $userContent = is_object($existingRecord) ? $existingRecord->title : $existingRecord['title'];
                        $isUserDefault = in_array(strtolower(trim($userContent)), [
                            'default', 'basic', 'standard', 'general', 'other', 'misc', 'temp', 'test'
                        ]);
                        return $isUserDefault; // Only update if user has default/temp values

                    default:
                        return false; // Default: prefer user customizations
        }
    }

    /**
     * Update existing record with template data
     */
    private function updateRecordWithTemplate($tableName, $recordId, $templateRecord)
    {
        $updateData = (array) $templateRecord;
        unset($updateData['id']);
        $updateData['created_by'] = $this->companyId;
        $updateData['updated_at'] = now();

        DB::table($tableName)->where('id', $recordId)->update($updateData);
    }

    /**
     * Add new record from template
     */
    private function addTemplateRecord($tableName, $templateRecord)
    {
        $newData = (array) $templateRecord;
        unset($newData['id']);
        $newData['created_by'] = $this->companyId;
        $newData['created_at'] = now();
        $newData['updated_at'] = now();

        DB::table($tableName)->insert($newData);
    }


    // Add this method to the CompanyRefreshService class
    private function mergeRolePermissions()
    {
        Log::info("Step 6: Merging role permissions");

        if (empty($this->roleIdMappings)) {
            Log::info("No role mappings found, skipping role permissions");
            return;
        }

        $permissionsAdded = 0;
        $permissionsSkipped = 0;

        foreach ($this->roleIdMappings as $templateRoleId => $userRoleId) {
            // Get template role permissions
            $templatePermissions = DB::table('role_has_permissions')
                ->where('role_id', $templateRoleId)
                ->get();

            // Get existing user role permissions
            $existingPermissions = DB::table('role_has_permissions')
                ->where('role_id', $userRoleId)
                ->pluck('permission_id')
                ->toArray();

            foreach ($templatePermissions as $permission) {
                if (!in_array($permission->permission_id, $existingPermissions)) {
                    // Permission doesn't exist for user role, add it
                    if (!$this->isDryRun) {
                        DB::table('role_has_permissions')->insert([
                            'role_id' => $userRoleId,
                            'permission_id' => $permission->permission_id,
                        ]);
                    }
                    $permissionsAdded++;

                    $this->transferLog[] = [
                        'action' => 'added_role_permission',
                        'table' => 'role_has_permissions',
                        'details' => "Added permission {$permission->permission_id} to role {$userRoleId}",
                        'dry_run' => $this->isDryRun
                    ];
                } else {
                    $permissionsSkipped++;
                }
            }
        }

        Log::info("Role permissions: {$permissionsAdded} added, {$permissionsSkipped} already existed");
    }


    /**
     * Get company's currency
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
     * Rollback changes (for actual refresh if something goes wrong)
     */
    private function rollbackChanges()
    {
        if (empty($this->originalBackup)) {
            Log::warning("No backup data available for rollback");
            return;
        }

        Log::info("Rolling back changes...");

        try {
            foreach ($this->originalBackup as $tableName => $records) {
                if ($tableName === 'settings') {
                    // Restore settings
                    DB::table('settings')->where('created_by', $this->companyId)->delete();
                    foreach ($records as $record) {
                        DB::table('settings')->insert((array) $record);
                    }
                } elseif ($tableName === 'role_has_permissions') {
                                // Restore role permissions
                                $userRoleIds = DB::table('roles')
                                    ->where('created_by', $this->companyId)
                                    ->pluck('id')
                                    ->toArray();

                                if (!empty($userRoleIds)) {
                                    DB::table('role_has_permissions')->whereIn('role_id', $userRoleIds)->delete();
                                    foreach ($records as $record) {
                                        DB::table('role_has_permissions')->insert((array) $record);
                                    }
                                }
                            } else {
                                // Restore other tables
                                DB::table($tableName)->where('created_by', $this->companyId)->delete();
                                foreach ($records as $record) {
                                    DB::table($tableName)->insert((array) $record);
                                }
                            }
                        }


            Log::info("Rollback completed successfully");
        } catch (\Exception $e) {
            Log::error("Rollback failed: " . $e->getMessage());
        }
    }




    /**
     * Generate success response
     */
    private function generateSuccessResponse()
    {
        $message = $this->isDryRun ?
            'Company refresh dry run completed - analyzed changes without making permanent modifications' :
            'Company refreshed successfully - merged template data with existing company';

        return [
            'success' => true,
            'message' => $message,
            'is_dry_run' => $this->isDryRun,
            'company_id' => $this->companyId,
            'template_company_id' => $this->templateCompanyId,
            'currency' => $this->getCompanyCurrency($this->companyId),
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
            'records_added' => 0,
            'records_updated' => 0,
            'records_preserved' => 0,
            'settings_preserved' => 0,
            'settings_updated' => 0,
            'settings_added' => 0,
            'settings_skipped' => 0
        ];

        $tablesProcessed = [];

        foreach ($this->transferLog as $log) {
            // Track unique tables
            if (!in_array($log['table'], $tablesProcessed)) {
                $tablesProcessed[] = $log['table'];
            }

            // Count actions
            if (strpos($log['action'], 'added') !== false) {
                $summary['records_added'] += $log['count'] ?? 1;
            } elseif (strpos($log['action'], 'updated') !== false) {
                $summary['records_updated'] += $log['count'] ?? 1;
            } elseif (strpos($log['action'], 'kept') !== false || strpos($log['action'], 'preserved') !== false) {
                $summary['records_preserved'] += $log['count'] ?? 1;
            }

            // Count settings specifically
            if ($log['table'] === 'settings') {
                if (strpos($log['action'], 'preserved') !== false) {
                    $summary['settings_preserved']++;
                } elseif (strpos($log['action'], 'updated') !== false) {
                    $summary['settings_updated']++;
                } elseif (strpos($log['action'], 'added') !== false) {
                    $summary['settings_added']++;
                } elseif (strpos($log['action'], 'skipped') !== false) {
                    $summary['settings_skipped']++;
                }
            }
        }

        $summary['tables_processed'] = count($tablesProcessed);
        $summary['tables_list'] = $tablesProcessed;

        return $summary;
    }

    // =============================================================================
    // PUBLIC UTILITY METHODS
    // =============================================================================

    /**
     * Preview what will happen during refresh
     */
    public function previewRefresh($companyId)
    {
        $companyCurrency = $this->getCompanyCurrency($companyId);
        $templateCompanyId = TemplateCompanyConfig::findTemplateByCurrency($companyCurrency);

        if (!$templateCompanyId) {
            return [
                'success' => false,
                'error' => "No template company found for currency: {$companyCurrency}",
                'available_currencies' => array_keys(TemplateCompanyConfig::getAvailableCurrencies())
            ];
        }

        $analysis = [];

        // Analyze each master data table
        foreach ($this->masterDataTables as $tableName => $matchFields) {
            if (!Schema::hasTable($tableName) || !Schema::hasColumn($tableName, 'created_by')) {
                continue;
            }

            $userCount = DB::table($tableName)->where('created_by', $companyId)->count();
            $templateCount = DB::table($tableName)->where('created_by', $templateCompanyId)->count();

            if ($userCount > 0 || $templateCount > 0) {
                $strategy = $this->getTableStrategy($tableName);
                $analysis[$tableName] = [
                    'user_records' => $userCount,
                    'template_records' => $templateCount,
                    'strategy' => $strategy
                ];
            }
        }

        // Analyze settings
        $userSettings = DB::table('settings')->where('created_by', $companyId)->pluck('name')->toArray();
        $templateSettings = DB::table('settings')->where('created_by', $templateCompanyId)->pluck('name')->toArray();

        $settingsAnalysis = [
            'will_be_preserved' => array_intersect($userSettings, $this->preservedSettings),
            'will_be_updated' => array_intersect($userSettings, $this->templateSettings),
            'will_be_added' => array_diff($templateSettings, $userSettings),
            'will_be_skipped' => array_intersect($templateSettings, $this->superadminSettings)
        ];

        return [
            'success' => true,
            'company_id' => $companyId,
            'company_currency' => $companyCurrency,
            'template_company_id' => $templateCompanyId,
            'template_company_name' => User::find($templateCompanyId)->name ?? 'Unknown',
            'master_data_analysis' => $analysis,
            'settings_analysis' => $settingsAnalysis,
            'recommendation' => $this->generateRecommendation($analysis, $settingsAnalysis)
        ];
    }

    /**
     * Get strategy description for a table
     */
    private function getTableStrategy($tableName)
    {
        switch ($tableName) {
            case 'chart_of_accounts':
            case 'chart_of_account_parents':
            case 'chart_of_account_types':
            case 'chart_of_account_sub_types':
            case 'taxes':
                return 'template_wins_compliance';

           // case 'templates':
             //   return 'template_wins_always';

            case 'joining_letters':
            case 'experience_certificates':
            case 'generate_offer_letters':
            case 'noc_certificates':
            case 'email_template_langs':
            case 'notification_templates':
            case 'notification_template_langs':
                return 'user_wins_if_content_modified';

            case 'job_categories':
            case 'job_stages':
            case 'departments':
            case 'designations':
            case 'branches':
            case 'roles':
            case 'warehouses':
            case 'bank_accounts':
            case 'company_payment_settings':
            case 'referral_settings':
            case 'landing_page_settings':
                return 'user_wins_completely';

            case 'product_service_categories':
            case 'product_service_units':
            case 'pipelines':
            case 'stages':
            case 'sources':
            case 'labels':
            case 'lead_stages':
            case 'leave_types':
            case 'allowance_options':
            case 'deduction_options':
            case 'loan_options':
            case 'goal_types':
            case 'award_types':
            case 'performance_types':
            case 'termination_types':
            case 'payslip_types':
            case 'training_types':
            case 'custom_questions':
            case 'competencies':
            case 'contract_types':
            case 'bug_statuses':
            case 'task_stages':
                return 'merge_user_wins_conflicts';

            default:
                return 'merge_with_smart_detection';
        }
    }

    /**
     * Generate recommendation based on analysis
     */
    private function generateRecommendation($analysis, $settingsAnalysis)
    {
        $totalRecords = array_sum(array_column($analysis, 'user_records'));
        $totalTemplateChanges = array_sum(array_column($analysis, 'template_records'));
        $settingsChanges = count($settingsAnalysis['will_be_updated']) + count($settingsAnalysis['will_be_added']);

        if ($totalRecords > 1000) {
            return "Large dataset ({$totalRecords} user records). This is an in-place refresh so it's safer, but test with dry run first.";
        } elseif ($settingsChanges > 20) {
            return "Many settings will be updated ({$settingsChanges} changes). Review settings analysis carefully.";
        } elseif ($totalTemplateChanges > 100) {
            return "Template has many new features ({$totalTemplateChanges} records). Good opportunity to get latest improvements.";
        } else {
            return "Safe to proceed - moderate changes expected. In-place refresh minimizes risk.";
        }
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
        $templateId = TemplateCompanyConfig::findTemplateByCurrency($currency);
        if (!$templateId) {
            $available = array_keys(TemplateCompanyConfig::getAvailableCurrencies());
            $issues[] = "No template company available for currency '{$currency}'. Available: " . implode(', ', $available);
        }

        // Check if template company actually exists
        if ($templateId && !User::where('type', 'company')->where('id', $templateId)->exists()) {
            $issues[] = "Template company {$templateId} is configured but does not exist in database";
        }

        return [
            'valid' => empty($issues),
            'issues' => $issues,
            'company_currency' => $currency,
            'template_company_id' => $templateId
        ];
    }

    /**
     * Compare current state vs template (useful for analysis)
     */
    public function compareWithTemplate($companyId)
    {
        $currency = $this->getCompanyCurrency($companyId);
        $templateId = TemplateCompanyConfig::findTemplateByCurrency($currency);

        if (!$templateId) {
            return ['error' => 'No template found for currency: ' . $currency];
        }

        $comparison = [];

        foreach ($this->masterDataTables as $tableName => $matchFields) {
            if (!Schema::hasTable($tableName) || !Schema::hasColumn($tableName, 'created_by')) {
                continue;
            }

            $userCount = DB::table($tableName)->where('created_by', $companyId)->count();
            $templateCount = DB::table($tableName)->where('created_by', $templateId)->count();

            if ($userCount > 0 || $templateCount > 0) {
                $comparison[$tableName] = [
                    'user_records' => $userCount,
                    'template_records' => $templateCount,
                    'difference' => $templateCount - $userCount,
                    'strategy' => $this->getTableStrategy($tableName),
                    'status' => $userCount === 0 ? 'missing_all' :
                               ($templateCount > $userCount ? 'missing_some' : 'complete')
                ];
            }
        }

        return [
            'company_id' => $companyId,
            'template_id' => $templateId,
            'currency' => $currency,
            'table_comparison' => $comparison,
            'total_template_records' => array_sum(array_column($comparison, 'template_records')),
            'total_user_records' => array_sum(array_column($comparison, 'user_records'))
        ];
    }

    /**
     * Get detailed breakdown of what will be processed
     */
    public function getProcessingBreakdown($companyId)
    {
        $currency = $this->getCompanyCurrency($companyId);
        $templateId = TemplateCompanyConfig::findTemplateByCurrency($currency);

        if (!$templateId) {
            return ['error' => 'No template found for currency: ' . $currency];
        }

        $breakdown = [
            'master_data_merging' => [],
            'settings_strategy' => [],
            'missing_configurations' => []
        ];

        // Master data analysis
        foreach ($this->masterDataTables as $table => $fields) {
            if (Schema::hasTable($table) && Schema::hasColumn($table, 'created_by')) {
                $userCount = DB::table($table)->where('created_by', $companyId)->count();
                $templateCount = DB::table($table)->where('created_by', $templateId)->count();

                if ($userCount > 0 || $templateCount > 0) {
                    $breakdown['master_data_merging'][$table] = [
                        'user_records' => $userCount,
                        'template_records' => $templateCount,
                        'match_fields' => $fields,
                        'strategy' => $this->getTableStrategy($table),
                        'action' => $userCount > 0 ? 'merge_and_resolve_conflicts' : 'copy_all_from_template'
                    ];
                }
            }
        }

        // Settings analysis
        $userSettings = DB::table('settings')->where('created_by', $companyId)->pluck('name')->toArray();
        $templateSettings = DB::table('settings')->where('created_by', $templateId)->pluck('name')->toArray();

        $breakdown['settings_strategy'] = [
            'preserved_settings' => array_intersect($userSettings, $this->preservedSettings),
            'template_overrides' => array_intersect($templateSettings, $this->templateSettings),
            'new_from_template' => array_diff($templateSettings, $userSettings),
            'superadmin_skipped' => array_intersect($templateSettings, $this->superadminSettings)
        ];

        // Missing configurations analysis
        $configTables = ['documents', 'indicators', 'milestones'];
        foreach ($configTables as $table) {
            if (Schema::hasTable($table) && Schema::hasColumn($table, 'created_by')) {
                $userCount = DB::table($table)->where('created_by', $companyId)->count();
                $templateCount = DB::table($table)->where('created_by', $templateId)->count();

                if ($userCount === 0 && $templateCount > 0) {
                    $breakdown['missing_configurations'][$table] = [
                        'template_records' => $templateCount,
                        'action' => 'copy_all_from_template'
                    ];
                }
            }
        }

        return $breakdown;
    }

    /**
     * Get detailed conflict analysis for specific table
     */
    public function analyzeTableConflicts($companyId, $tableName)
    {
        if (!isset($this->masterDataTables[$tableName])) {
            return ['error' => "Table {$tableName} is not configured for conflict analysis"];
        }

        $currency = $this->getCompanyCurrency($companyId);
        $templateId = TemplateCompanyConfig::findTemplateByCurrency($currency);

        if (!$templateId) {
            return ['error' => 'No template found for currency: ' . $currency];
        }

        $matchFields = $this->masterDataTables[$tableName];

        // Get user records
        $userRecords = DB::table($tableName)
            ->where('created_by', $companyId)
            ->get()
            ->keyBy(function($record) use ($matchFields) {
                return $this->getRecordMatchingKey($record, $matchFields);
            });

        // Get template records
        $templateRecords = DB::table($tableName)
            ->where('created_by', $templateId)
            ->get()
            ->keyBy(function($record) use ($matchFields) {
                return $this->getRecordMatchingKey($record, $matchFields);
            });

        $conflicts = [];
        $userOnly = [];
        $templateOnly = [];

        // Find conflicts
        foreach ($userRecords as $key => $userRecord) {
            if ($templateRecords->has($key)) {
                $templateRecord = $templateRecords->get($key);
                $willUpdate = $this->shouldUpdateWithTemplate($userRecord, $templateRecord, $tableName);

                $conflicts[] = [
                    'matching_key' => $key,
                    'user_record' => $userRecord,
                    'template_record' => $templateRecord,
                    'will_update' => $willUpdate,
                    'winner' => $willUpdate ? 'template' : 'user'
                ];
            } else {
                $userOnly[] = [
                    'matching_key' => $key,
                    'user_record' => $userRecord,
                    'action' => 'keep_user_record'
                ];
            }
        }

        // Find template-only records
        foreach ($templateRecords as $key => $templateRecord) {
            if (!$userRecords->has($key)) {
                $templateOnly[] = [
                    'matching_key' => $key,
                    'template_record' => $templateRecord,
                    'action' => 'add_from_template'
                ];
            }
        }

        return [
            'table' => $tableName,
            'strategy' => $this->getTableStrategy($tableName),
            'match_fields' => $matchFields,
            'conflicts' => $conflicts,
            'user_only_records' => $userOnly,
            'template_only_records' => $templateOnly,
            'summary' => [
                'total_conflicts' => count($conflicts),
                'template_wins' => count(array_filter($conflicts, fn($c) => $c['winner'] === 'template')),
                'user_wins' => count(array_filter($conflicts, fn($c) => $c['winner'] === 'user')),
                'user_only' => count($userOnly),
                'template_additions' => count($templateOnly)
            ]
        ];
    }

    /**
     * Get settings conflict analysis
     */
    public function analyzeSettingsConflicts($companyId)
    {
        $currency = $this->getCompanyCurrency($companyId);
        $templateId = TemplateCompanyConfig::findTemplateByCurrency($currency);

        if (!$templateId) {
            return ['error' => 'No template found for currency: ' . $currency];
        }

        $userSettings = DB::table('settings')
            ->where('created_by', $companyId)
            ->get()
            ->keyBy('name');

        $templateSettings = DB::table('settings')
            ->where('created_by', $templateId)
            ->get()
            ->keyBy('name');

        $analysis = [
            'preserved' => [],
            'updated' => [],
            'added' => [],
            'skipped' => []
        ];

        foreach ($templateSettings as $name => $templateSetting) {
            if (in_array($name, $this->preservedSettings)) {
                $analysis['preserved'][] = [
                    'name' => $name,
                    'user_value' => $userSettings->has($name) ? $userSettings->get($name)->value : null,
                    'template_value' => $templateSetting->value,
                    'action' => 'keep_user_value'
                ];
            } elseif (in_array($name, $this->superadminSettings)) {
                $analysis['skipped'][] = [
                    'name' => $name,
                    'template_value' => $templateSetting->value,
                    'action' => 'skip_superadmin_only'
                ];
            } elseif (in_array($name, $this->templateSettings)) {
                $analysis['updated'][] = [
                    'name' => $name,
                    'user_value' => $userSettings->has($name) ? $userSettings->get($name)->value : null,
                    'template_value' => $templateSetting->value,
                    'action' => 'use_template_value'
                ];
            } else {
                if (!$userSettings->has($name)) {
                    $analysis['added'][] = [
                        'name' => $name,
                        'template_value' => $templateSetting->value,
                        'action' => 'add_if_missing'
                    ];
                }
            }
        }

        return [
            'company_id' => $companyId,
            'template_id' => $templateId,
            'settings_analysis' => $analysis,
            'summary' => [
                'preserved_count' => count($analysis['preserved']),
                'updated_count' => count($analysis['updated']),
                'added_count' => count($analysis['added']),
                'skipped_count' => count($analysis['skipped'])
            ]
        ];
    }

    /**
     * Get refresh history for a company
     */
    public function getRefreshHistory($companyId)
    {
        $currency = $this->getCompanyCurrency($companyId);
        $templateId = TemplateCompanyConfig::findTemplateByCurrency($currency);

        return [
            'company_id' => $companyId,
            'current_currency' => $currency,
            'available_template' => $templateId,
            'can_refresh' => $templateId !== null,
            'last_refresh' => null, // Would come from refresh_history table
            'refresh_count' => 0,   // Would come from refresh_history table
            'note' => 'Refresh history tracking not yet implemented'
        ];
    }

    /**
     * Analyze currency compatibility
     */
    public function analyzeCurrencyCompatibility($companyId)
    {
        $companyCurrency = $this->getCompanyCurrency($companyId);
        $availableTemplates = TemplateCompanyConfig::getAvailableCurrencies();
        $compatibleTemplate = TemplateCompanyConfig::findTemplateByCurrency($companyCurrency);

        return [
            'company_id' => $companyId,
            'company_currency' => $companyCurrency,
            'available_templates' => $availableTemplates,
            'compatible_template_id' => $compatibleTemplate,
            'is_compatible' => $compatibleTemplate !== null,
            'recommendations' => $this->getCurrencyRecommendations($companyCurrency, $availableTemplates)
        ];
    }

    /**
     * Get currency recommendations
     */
    private function getCurrencyRecommendations($currentCurrency, $availableTemplates)
    {
        if (isset($availableTemplates[$currentCurrency])) {
            return "✅ Perfect match! Template available for {$currentCurrency}";
        }

        $recommendations = [];

        // Suggest similar currencies
        $similarCurrencies = [
            'USD' => ['CAD', 'AUD'],
            'EUR' => ['GBP', 'CHF'],
            'GBP' => ['EUR'],
            'ZAR' => ['USD', 'GBP'], // South African Rand
        ];

        if (isset($similarCurrencies[$currentCurrency])) {
            foreach ($similarCurrencies[$currentCurrency] as $similarCurrency) {
                if (isset($availableTemplates[$similarCurrency])) {
                    $recommendations[] = "Consider creating a {$currentCurrency} template based on the {$similarCurrency} template";
                    break;
                }
            }
        }

        if (empty($recommendations)) {
            $available = implode(', ', array_keys($availableTemplates));
            $recommendations[] = "No template available for {$currentCurrency}. Available templates: {$available}";
            $recommendations[] = "You need to create a template company with {$currentCurrency} currency first";
        }

        return implode('. ', $recommendations);
    }

    /**
     * Emergency restore from backup
     */
    public function restoreFromBackup($companyId, $backupData)
    {
        Log::info("Restoring company {$companyId} from backup data");

        try {
            return DB::transaction(function () use ($companyId, $backupData) {
                $restoredTables = [];

                foreach ($backupData as $tableName => $records) {
                    if (!Schema::hasTable($tableName)) {
                        continue;
                    }

                    // Clear current data
                    DB::table($tableName)->where('created_by', $companyId)->delete();

                    // Restore backup data
                    foreach ($records as $record) {
                        DB::table($tableName)->insert((array) $record);
                    }

                    $restoredTables[] = $tableName;
                    Log::info("Restored {$tableName} with " . count($records) . " records");
                }

                return [
                    'success' => true,
                    'message' => 'Company data restored from backup',
                    'restored_tables' => $restoredTables,
                    'restored_at' => now()
                ];
            });

        } catch (\Exception $e) {
            Log::error("Restore failed: " . $e->getMessage());
            throw $e;
        }
    }
}
