<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use App\Models\User;
use App\Services\TemplateCompanyConfig;
use App\Services\CompanyCleanupService;
use App\Services\CompanyClonerService;

class CompanyRefreshService
{
    private $templateCompanyId;
    private $oldCompanyId;
    private $newCompanyId;
    private $transferLog = [];
    private $refreshSummary = [];
    private $idMappings = []; // Track old ID -> new ID mappings
    private $deferredRelationships = []; // Store relationships to fix later
    private $originalCompanyData = null; // Store original company data for restoration

    // ALL master data tables that users can add to OR modify
    private $masterDataTables = [

        // Email & Communication
        'email_templates',
        'notification_templates',
        'user_email_templates',

        // Certificate Templates
        'joining_letters',
        'experience_certificates',
        'generate_offer_letters',
        'noc_certificates',

        // Settings & Configuration
        'landing_page_settings',
        'referral_settings',

        // Chart of accounts system
        'chart_of_account_parents',
        'chart_of_account_types',
        'chart_of_account_sub_types',
        'chart_of_accounts',

        // Product & inventory system
       // 'product_service_categories',
       // 'product_service_units',
        //'product_services',
        'taxes',
       // 'warehouses',

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
        //'users', // Handled separately
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

        // HR system - User customizations should win
            'job_categories' => ['name'],
            'job_stages' => ['name', 'order'],
            'leave_types' => ['name', 'days'],
            'allowance_options' => ['name'],
            'deduction_options' => ['name'],
            'loan_options' => ['name'],
            'award_types' => ['name'],
            'training_types' => ['name'],
            'goal_types' => ['name'],
            'performance_types' => ['name'],
            'termination_types' => ['name'],
            'payslip_types' => ['name'],

            // Project system - User customizations should win
            'task_stages' => ['name', 'order'],

            // Contract system - User customizations should win
            'contract_types' => ['name'],

            // Other systems - User customizations should win
            'competencies' => ['name', 'type'],
            'custom_questions' => ['question', 'type'],
            'documents' => ['name', 'type'],

            // CRM - User customizations should win
            'lead_stages' => ['name', 'order'],

            // Chart framework - Add missing ones
            'chart_of_account_types' => ['name'],
            'chart_of_account_sub_types' => ['name'],
            'chart_of_account_parents' => ['name', 'account'],
            'chart_of_accounts' => ['name', 'description', 'is_enabled', 'parent'],
        //'product_services' => ['name', 'description', 'sale_price', 'purchase_price', 'is_enabled'],
        //'product_service_categories' => ['name', 'color'],

        'warehouses' => ['name', 'address', 'city', 'state'],
        'branches' => ['name', 'address', 'city', 'state'],
        'departments' => ['name', 'branch_id'],
        'designations' => ['name', 'department_id', 'branch_id'],
        'labels' => ['name', 'color'],
        'sources' => ['name'],
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
            'avatar' => $oldCompany->avatar ?? 'avatar.png',
            'plan' => $oldCompany->plan,
            'plan_expire_date' => $oldCompany->plan_expire_date,
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
            'is_disable' => $oldCompany->is_disable,
            'is_enable_login' => $oldCompany->is_enable_login,
            'is_active' => $oldCompany->is_active ?? 1,
            'referral_code' => $oldCompany->referral_code,
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


        // Email templates - user customizations take precedence for content
                'email_templates' => function($field, $old, $template) {
                    if (in_array($field, ['subject', 'content'])) {
                        return 'use_user'; // Always preserve user's custom email content
                    }
                    return $this->useMostRecent($old, $template);
                },

                'notification_templates' => function($field, $old, $template) {
                    if (in_array($field, ['subject', 'content'])) {
                        return 'use_user'; // Always preserve user's custom notification content
                    }
                    return $this->useMostRecent($old, $template);
                },

                // Certificate templates - ALWAYS preserve user customizations (revenue critical)
                'joining_letters' => function($field, $old, $template) {
                    if ($field === 'content') {
                        return 'use_user'; // NEVER lose custom certificate templates
                    }
                    return $this->useMostRecent($old, $template);
                },

                'experience_certificates' => function($field, $old, $template) {
                    if ($field === 'content') {
                        return 'use_user'; // NEVER lose custom certificate templates
                    }
                    return $this->useMostRecent($old, $template);
                },

                'generate_offer_letters' => function($field, $old, $template) {
                    if ($field === 'content') {
                        return 'use_user'; // NEVER lose custom offer letter templates
                    }
                    return $this->useMostRecent($old, $template);
                },

                'noc_certificates' => function($field, $old, $template) {
                    if ($field === 'content') {
                        return 'use_user'; // NEVER lose custom NOC templates
                    }
                    return $this->useMostRecent($old, $template);
                },

                // Landing page and referral settings - preserve user customizations
                'landing_page_settings' => function($field, $old, $template) {
                    return 'use_user'; // User branding/customizations take precedence
                },

                'referral_settings' => function($field, $old, $template) {
                    return 'use_user'; // User referral configurations take precedence
                },

                // HR system - ALL user customizations win
                        'job_categories' => function($field, $old, $template) {
                            return 'use_user'; // Company-specific job categories
                        },
                        'job_stages' => function($field, $old, $template) {
                            return 'use_user'; // Company-specific recruitment process
                        },
                        'leave_types' => function($field, $old, $template) {
                            return 'use_user'; // Company-specific leave policies
                        },
                        'allowance_options' => function($field, $old, $template) {
                            return 'use_user'; // Company-specific allowances
                        },
                        'deduction_options' => function($field, $old, $template) {
                            return 'use_user'; // Company-specific deductions
                        },
                        'loan_options' => function($field, $old, $template) {
                            return 'use_user'; // Company-specific loan policies
                        },
                        'award_types' => function($field, $old, $template) {
                            return 'use_user'; // Company-specific recognition programs
                        },
                        'training_types' => function($field, $old, $template) {
                            return 'use_user'; // Company-specific training programs
                        },
                        'goal_types' => function($field, $old, $template) {
                            return 'use_user'; // Company-specific goal frameworks
                        },
                        'performance_types' => function($field, $old, $template) {
                            return 'use_user'; // Company-specific performance metrics
                        },
                        'termination_types' => function($field, $old, $template) {
                            return 'use_user'; // Company-specific termination policies
                        },
                        'payslip_types' => function($field, $old, $template) {
                            return 'use_user'; // Company-specific payslip formats
                        },

                        // Project system - User customizations win
                        'task_stages' => function($field, $old, $template) {
                            return 'use_user'; // Company-specific project workflows
                        },

                        // Contract system - User customizations win
                        'contract_types' => function($field, $old, $template) {
                            return 'use_user'; // Company-specific contract types
                        },

                        // Other systems - User customizations win
                        'competencies' => function($field, $old, $template) {
                            return 'use_user'; // Company-specific competency framework
                        },
                        'custom_questions' => function($field, $old, $template) {
                            return 'use_user'; // Company-specific questions
                        },
                        'documents' => function($field, $old, $template) {
                            return 'use_user'; // Company-specific document types
                        },

                        // CRM - User customizations win
                        'lead_stages' => function($field, $old, $template) {
                            return 'use_user'; // Company-specific lead processes
                        },

                        // Chart framework - Template structure should win (for consistency)
                        'chart_of_account_types' => function($field, $old, $template) {
                            return 'use_template'; // Accounting framework should be consistent
                        },
                        'chart_of_account_sub_types' => function($field, $old, $template) {
                            return 'use_template'; // Accounting framework should be consistent
                        },
                        'chart_of_account_parents' => function($field, $old, $template) {
                            return 'use_template'; // Accounting framework should be consistent
                        },

                        'chart_of_accounts' => function($field, $old, $template) {
                            // Template controls structure and admin decisions
                            if (in_array($field, ['is_enabled', 'parent', 'type', 'sub_type', 'code'])) {
                                return 'use_template'; // Admin/structure decisions from template
                            }

                            // User controls customizations
                            if (in_array($field, ['name', 'description'])) {
                                return $this->useMostRecent($old, $template); // User can customize names
                            }

                            // Default to template for any other fields
                            return 'use_template';
                        },

            //'product_services' => function($field, $old, $template) {
                // User pricing takes precedence
               // if (in_array($field, ['sale_price', 'purchase_price'])) {
               //     return 'use_user';
               // }
               // return $this->useMostRecent($old, $template);
            //},

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
                // Skip company type users (they're handled in cloneTemplateToNewCompanyWithPrefix)
                if (isset($recordArray['type']) && $recordArray['type'] === 'company') {
                    Log::info("Skipping company type user in copyTableData");
                    continue;
                }

                // Skip non-company users - they should be handled by copyUsersToNewCompanyWithPrefix method
                Log::info("Skipping user in copyTableData - will be handled by copyUsersToNewCompanyWithPrefix");
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
     * Step 6: Copy users to new company with prefixed emails
     */
    private function copyUsersToNewCompanyWithPrefix($isDryRun)
    {
        Log::info("Step 6: Copying users to new company with prefixed emails");

        // Get all users from old company (except the main company user)
        $users = User::where('created_by', $this->oldCompanyId)
            ->where('type', '!=', 'company')
            ->get();

        Log::info("Found {$users->count()} users to copy");

        $prefix = $isDryRun ? 'dryrun_' : 'refresh_';

        foreach ($users as $user) {
            // Create copy of user in new company using replicate to preserve all fields
            $newUser = $user->replicate();

            // Update necessary fields
            $newUser->created_by = $this->newCompanyId;
            $newUser->email = $prefix . time() . '_' . $user->email; // ALWAYS prefix
            $newUser->updated_at = now();

            // Preserve important fields that might get lost
            $newUser->password = $user->password;
            $newUser->plan = $user->plan;
            $newUser->plan_expire_date = $user->plan_expire_date;
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

            Log::info("Copied user {$user->name} with prefixed email (Old ID: {$user->id}, New ID: {$newUser->id})");
        }

        $this->transferLog[] = [
            'table' => 'users',
            'action' => 'copied_with_prefix',
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
     * Step 7: Delete old company (ACTUAL REFRESH ONLY)
     */
    private function deleteOldCompany()
    {
        Log::info("Step 7: Deleting old company {$this->oldCompanyId}");

        // FIRST: Transfer ownership BEFORE deletion
        $this->transferPaymentAndSubscriptionRecords();

        // THEN: Delete the company
        $oldCompany = User::find($this->oldCompanyId);
        if ($oldCompany) {
            CompanyCleanupService::cascadeDeleteCompanyData($this->oldCompanyId);
            $oldCompany->delete();
        }
    }

    /**
     * Transfer payment and subscription records to new company
     * These should NOT be deleted as they represent actual financial transactions
     */
    private function transferPaymentAndSubscriptionRecords()
    {
        Log::info("Transferring payment and subscription records to new company {$this->newCompanyId}");

        // Tables that should be transferred (not deleted) because they represent financial records
        $paymentTables = [
            // Primary payment/subscription tables
            'orders' => ['user_id'], // This is effectively their subscription/plan record
            'plan_requests' => ['user_id'], // Plan change requests

            // Transaction tables (check both user_id and created_by to be safe)
            'transactions' => ['user_id', 'created_by'], // Financial transactions

            // Coupon/discount tables
            'user_coupons' => ['user_id'], // Coupons assigned to company

            // Transaction linking tables
            'transaction_orders' => ['req_user_id'], // Links between transactions and orders
        ];

        foreach ($paymentTables as $tableName => $companyFields) {
            if (!Schema::hasTable($tableName)) {
                Log::info("Table {$tableName} does not exist, skipping");
                continue;
            }

            $totalUpdated = 0;

            // Update each field that might reference the old company
            foreach ($companyFields as $field) {
                if (Schema::hasColumn($tableName, $field)) {
                    $updated = DB::table($tableName)
                        ->where($field, $this->oldCompanyId)
                        ->update([
                            $field => $this->newCompanyId,
                            'updated_at' => now()
                        ]);

                    $totalUpdated += $updated;

                    if ($updated > 0) {
                        Log::info("Updated {$updated} records in {$tableName}.{$field}");
                    }
                }
            }

            if ($totalUpdated > 0) {
                Log::info("Total transferred: {$totalUpdated} {$tableName} records to new company");
            }
        }

        // Verify the transfer worked for critical tables
        $this->verifyPaymentTransfer();
    }

    /**
     * Verify that payment records were transferred correctly
     */
    private function verifyPaymentTransfer()
    {
        Log::info("Verifying payment record transfer...");

        // Check orders (most critical - this is their subscription)
        if (Schema::hasTable('orders')) {
            $orderCount = DB::table('orders')->where('user_id', $this->newCompanyId)->count();
            if ($orderCount > 0) {
                Log::info("✓ Verified: {$orderCount} orders now belong to new company");
            }
        }

        // Check plan requests
        if (Schema::hasTable('plan_requests')) {
            $planRequestCount = DB::table('plan_requests')->where('user_id', $this->newCompanyId)->count();
            if ($planRequestCount > 0) {
                Log::info("✓ Verified: {$planRequestCount} plan requests now belong to new company");
            }
        }

        // Check for any orphaned records that still reference old company
        $orphanedTables = ['orders', 'plan_requests', 'transactions', 'user_coupons', 'transaction_orders'];

        foreach ($orphanedTables as $tableName) {
            if (!Schema::hasTable($tableName)) continue;

            $orphanedCount = 0;
            $checkFields = [];

            // Determine which fields to check based on table
            switch ($tableName) {
                case 'orders':
                case 'plan_requests':
                case 'user_coupons':
                    $checkFields = ['user_id'];
                    break;
                case 'transactions':
                    $checkFields = ['user_id', 'created_by'];
                    break;
                case 'transaction_orders':
                    $checkFields = ['req_user_id'];
                    break;
            }

            foreach ($checkFields as $field) {
                if (Schema::hasColumn($tableName, $field)) {
                    $count = DB::table($tableName)->where($field, $this->oldCompanyId)->count();
                    $orphanedCount += $count;
                }
            }

            if ($orphanedCount > 0) {
                Log::warning("⚠️ Found {$orphanedCount} orphaned records in {$tableName} still referencing old company {$this->oldCompanyId}");
            }
        }
    }

    /**
     * Step 8: Remove prefixes and finalize company data (ACTUAL REFRESH ONLY)
     */
    private function removePrefixesAndFinalize()
    {
        Log::info("Step 8: Removing prefixes and finalizing company data");

        // Use the stored original company data (since old company is now deleted)
        if (!$this->originalCompanyData) {
            throw new \Exception("Original company data not available for restoration");
        }

        // Update main company record to remove prefix and restore original data
        DB::table('users')->where('id', $this->newCompanyId)->update([
            'name' => $this->originalCompanyData->name, // Remove (REFRESH) suffix
            'email' => $this->originalCompanyData->email, // Restore original email
            'updated_at' => now(),
        ]);

        Log::info("Restored company email to: {$this->originalCompanyData->email}");

        // Get all copied users and restore their original emails
        $copiedUsers = User::where('created_by', $this->newCompanyId)
            ->where('type', '!=', 'company')
            ->where('email', 'like', 'refresh_%')
            ->get();

        foreach ($copiedUsers as $copiedUser) {
            // Extract original email from prefixed email
            $originalEmail = preg_replace('/^refresh_\d+_/', '', $copiedUser->email);

            try {
                $copiedUser->email = $originalEmail;
                $copiedUser->save();

                Log::info("Restored original email for user {$copiedUser->name}: {$originalEmail}");
            } catch (\Exception $e) {
                Log::warning("Could not restore email for user {$copiedUser->name}: " . $e->getMessage());
                // Continue with other users even if one fails
            }
        }

        Log::info("Successfully removed all prefixes and finalized company data");
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
            'users_copied' => 0
        ];

        foreach ($this->transferLog as $log) {
            if ($log['action'] === 'copied' || $log['action'] === 'copied_with_prefix') {
                $summary['tables_processed']++;
                if (isset($log['count'])) {
                    $summary['records_copied'] += $log['count'];
                }

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
                CompanyCleanupService::cascadeDeleteCompanyData($this->newCompanyId);
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
}
