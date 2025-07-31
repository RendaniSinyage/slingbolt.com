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

    /**
     * Master data tables that should be merged with template
     * These contain user customizations that should be preserved vs template defaults
     */
    private $masterDataTables = [
        // Financial Master Data
        'chart_of_accounts' => ['account', 'account_code', 'name'],
        'product_categories' => ['name'],
        'units' => ['name'],
        'warehouses' => ['name'],
        'pipelines' => ['name'],
        'stages' => ['name', 'pipeline_id'],
        'taxes' => ['name', 'rate'],
        'chart_of_account_parents' => ['name', 'account'],
        'bank_accounts' => ['account_number', 'holder_name', 'bank_name'],
        'roles' => ['name'], // Users can customize role names

        // Email & Communication Templates
        'email_templates' => ['subject', 'content', 'lang'],
        'notification_templates' => ['subject', 'content', 'lang'],
        'email_template_langs' => ['template_id', 'lang', 'subject'],
        'notification_template_langs' => ['template_id', 'lang', 'subject'],

        // Certificate Templates (CRITICAL for revenue)
        'joining_letters' => ['content', 'is_enabled'],
        'experience_certificates' => ['content', 'is_enabled'],
        'generate_offer_letters' => ['content', 'is_enabled'],
        'noc_certificates' => ['content', 'is_enabled'],

        // Configuration that should be merged
        'company_payment_settings' => ['value'], // Payment gateway configurations
        'landing_page_settings' => ['value'], // Brand settings
        'referral_settings' => ['value'], // Referral configurations
    ];

    /**
     * Settings that should be preserved from user (never overwritten by template)
     */
    private $preservedSettings = [
        // Company Identity
        'company_name', 'company_email', 'company_address',
        'company_city', 'company_state', 'company_zipcode',
        'company_country', 'company_telephone',
        'registration_number', 'vat_number',
        'company_logo', 'company_favicon',

        // Payment Gateway Settings
        'stripe_key', 'stripe_secret', 'paypal_mode',
        'paypal_client_id', 'paypal_client_secret',
        'razorpay_public_key', 'razorpay_secret_key',
        'paystack_public_key', 'paystack_secret_key',
        'flutterwave_public_key', 'flutterwave_secret_key',
        'payfast_merchant_id', 'payfast_merchant_key',
        'mercado_app_id', 'mercado_secret_key',

        // API Keys
        'chat_gpt_key', 'chat_gpt_model',

        // User Preferences
        'timezone', 'default_language',
    ];

    /**
     * Settings that should come from template (always overwritten)
     */
    private $templateSettings = [
        // System Configuration
        'site_currency', 'site_currency_symbol', 'site_currency_symbol_position',
        'site_currency_symbol_space', 'site_currency_symbol_name',
        'decimal_number_format', 'site_decimal_separator', 'site_thousands_separator',
        'site_date_format', 'site_time_format',

        // Default Templates
        'invoice_template', 'proposal_template', 'bill_template',
        'invoice_qr_display', 'qr_display',
        'invoice_color', 'proposal_color', 'bill_color',

        // System Features
        'tracking_interval', 'application_url',
        'storage_setting', 'mail_driver',

        // Theme (can be overridden but template provides good defaults)
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

                // Step 4: Update settings strategically
                $this->mergeTemplateSettings();

                // Step 5: Add missing template configurations
                $this->addMissingTemplateConfigurations();

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

            $userCount = DB::table($tableName)->where('created_by', $companyId)->count();
            $templateCount = DB::table($tableName)->where('created_by', $templateId)->count();

            if ($userCount > 0 || $templateCount > 0) {
                $comparison[$tableName] = [
                    'user_records' => $userCount,
                    'template_records' => $templateCount,
                    'difference' => $templateCount - $userCount,
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
                        'strategy' => $userCount > 0 ? 'merge_and_resolve_conflicts' : 'copy_all_from_template'
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
            'new_from_template' => array_diff($templateSettings, $userSettings)
        ];

        // Missing configurations analysis
        $configTables = ['templates', 'languages', 'custom_questions', 'competencies', 'labels'];
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
     * Get refresh history for a company (requires implementing history tracking)
     */
    public function getRefreshHistory($companyId)
    {
        // This would require a refresh_history table to track previous refreshes
        // For now, return basic info based on current state
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

    // =============================================================================
    // DEBUGGING & TESTING METHODS
    // =============================================================================

    /**
     * Test dry run vs actual refresh (for development)
     */
    public function testRefreshModes($companyId)
    {
        try {
            // Run dry run first
            Log::info("=== TESTING DRY RUN ===");
            $dryRunResult = $this->dryRun($companyId);

            // Get state after dry run
            $stateAfterDryRun = $this->getCompanyDataSnapshot($companyId);

            Log::info("=== DRY RUN COMPLETED ===");

            return [
                'dry_run_result' => $dryRunResult,
                'state_after_dry_run' => $stateAfterDryRun,
                'note' => 'Dry run completed. Original data unchanged. Use this to verify expected changes.'
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'note' => 'Test failed during dry run phase'
            ];
        }
    }

    /**
     * Get snapshot of company data (for testing)
     */
    private function getCompanyDataSnapshot($companyId)
    {
        $snapshot = [];

        foreach ($this->masterDataTables as $tableName => $fields) {
            if (Schema::hasTable($tableName) && Schema::hasColumn($tableName, 'created_by')) {
                $snapshot[$tableName] = DB::table($tableName)
                    ->where('created_by', $companyId)
                    ->count();
            }
        }

        $snapshot['settings_count'] = DB::table('settings')
            ->where('created_by', $companyId)
            ->count();

        return $snapshot;
    }

    /**
     * Reset company to clean slate (DANGEROUS - for development only)
     */
    public function resetToCleanSlate($companyId, $confirmationCode)
    {
        // Safety check
        if ($confirmationCode !== 'RESET_' . $companyId . '_CONFIRMED') {
            throw new \Exception('Invalid confirmation code. This operation requires explicit confirmation.');
        }

        Log::warning("DANGEROUS: Resetting company {$companyId} to clean slate");

        try {
            return DB::transaction(function () use ($companyId) {
                // Delete all company data except the company record itself and users
                $tablesToClean = array_merge(
                    array_keys($this->masterDataTables),
                    ['settings', 'company_payment_settings', 'landing_page_settings', 'referral_settings']
                );

                $deletedCounts = [];
                foreach ($tablesToClean as $table) {
                    if (Schema::hasTable($table) && Schema::hasColumn($table, 'created_by')) {
                        $count = DB::table($table)->where('created_by', $companyId)->count();
                        DB::table($table)->where('created_by', $companyId)->delete();
                        $deletedCounts[$table] = $count;
                    }
                }

                Log::warning("Reset completed - deleted data from " . count($deletedCounts) . " tables");

                return [
                    'success' => true,
                    'message' => 'Company reset to clean slate - all configuration data deleted',
                    'deleted_counts' => $deletedCounts,
                    'note' => 'Users and business data preserved. Run refresh to restore template configuration.'
                ];
            });

        } catch (\Exception $e) {
            Log::error("Reset failed: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Emergency restore from backup (if available)
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
     * Merge a single table with template data
     */
    private function mergeTable($tableName, $matchFields)
    {
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

                    $this->transferLog[] = [
                        'action' => 'updated_with_template',
                        'table' => $tableName,
                        'details' => "Updated existing record with template data: {$matchingKey}",
                        'dry_run' => $this->isDryRun
                    ];
                } else {
                    $skipped++;
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

        Log::info("Settings processing: {$preserved} preserved, {$updated} updated, {$added} added");
    }

    /**
     * Step 5: Add missing template configurations that user doesn't have
     */
    private function addMissingTemplateConfigurations()
    {
        Log::info("Step 5: Adding missing template configurations");

        // Tables that should be copied entirely if user has none
        $configurationTables = [
            'templates', // Document templates
            'languages', // Available languages
            'custom_questions', // HR questions
            'competencies', // HR competencies
            'labels', // Project labels
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
            $value = is_object($record) ? $record->{$field} : $record[$field];
            $keyParts[] = strtolower(trim($value ?? ''));
        }

        return implode('|', $keyParts);
    }

    /**
     * Decide if existing record should be updated with template version
     */
    private function shouldUpdateWithTemplate($existingRecord, $templateRecord, $tableName)
    {
        switch ($tableName) {
            case 'chart_of_accounts':
                // Template chart of accounts usually has better structure
                return false; // Keep user's chart of accounts

            case 'taxes':
                // User's tax rates are probably more accurate for their region
                return false; // Keep user's taxes

            case 'roles':
                // User's role names should be preserved
                return false; // Keep user's role names

            case 'email_templates':
            case 'notification_templates':
            case 'email_template_langs':
            case 'notification_template_langs':
                // Template might have newer/better email templates
                return true; // Update with template

            case 'joining_letters':
            case 'experience_certificates':
            case 'generate_offer_letters':
            case 'noc_certificates':
                // Template might have improved certificate formats
                return true; // Update with template

            default:
                // Default: prefer template updates
                return true;
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
            'settings_added' => 0
        ];

        foreach ($this->transferLog as $log) {
            if (strpos($log['action'], 'added') !== false) {
                $summary['records_added'] += $log['count'] ?? 1;
            } elseif (strpos($log['action'], 'updated') !== false) {
                $summary['records_updated'] += $log['count'] ?? 1;
            } elseif (strpos($log['action'], 'kept') !== false || strpos($log['action'], 'preserved') !== false) {
                $summary['records_preserved'] += $log['count'] ?? 1;
            }

            if ($log['table'] === 'settings') {
                if (strpos($log['action'], 'preserved') !== false) {
                    $summary['settings_preserved']++;
                } elseif (strpos($log['action'], 'updated') !== false) {
                    $summary['settings_updated']++;
                } elseif (strpos($log['action'], 'added') !== false) {
                    $summary['settings_added']++;
                }
            }

            if (!isset($summary['tables_list'])) {
                $summary['tables_list'] = [];
            }
            if (!in_array($log['table'], $summary['tables_list'])) {
                $summary['tables_list'][] = $log['table'];
                $summary['tables_processed']++;
            }
        }

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
                $analysis[$tableName] = [
                    'user_records' => $userCount,
                    'template_records' => $templateCount,
                    'strategy' => 'merge_with_conflict_resolution'
                ];
            }
        }

        // Analyze settings
        $userSettings = DB::table('settings')->where('created_by', $companyId)->pluck('name')->toArray();
        $templateSettings = DB::table('settings')->where('created_by', $templateCompanyId)->pluck('name')->toArray();

        $settingsAnalysis = [
            'will_be_preserved' => array_intersect($userSettings, $this->preservedSettings),
            'will_be_updated' => array_intersect($userSettings, $this->templateSettings),
            'will_be_added' => array_diff($templateSettings, $userSettings)
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
