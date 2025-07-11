<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CompanyClonerService
{
    protected $sourceCompanyId;
    protected $targetCompanyId;
    protected $excludedTables;
    protected $fieldsToReset;
    protected $idMappings = []; // Track old ID -> new ID mappings

    public function __construct($targetCompanyId, $sourceCompanyId = null)
    {
        $this->targetCompanyId = $targetCompanyId;
        $this->sourceCompanyId = $sourceCompanyId ?: $this->getFirstCompanyId();
        
        // Tables to exclude from cloning (based on actual database structure)
        $this->excludedTables = [
            // User & Authentication
            'users', 'login_details', 'user_todos', 'notifications', 
            'personal_access_tokens', 'password_resets', 'failed_jobs',
            
            // Transactional Data (Sales)
            'orders', 'order_products', 'invoices', 'invoice_products',
            'payments', 'pos', 'pos_products', 'quotations', 'quotation_products',
            'proposals', 'proposal_products', 'customer_credits',
            
            // Transactional Data (Purchases)  
            'bills', 'bill_products', 'purchases', 'purchase_products',
            'purchase_payments', 'vender_credits', 'debit_notes', 'credit_notes',
            
            // Financial Transactions
            'transactions', 'revenues', 'transaction_lines', 'add_transaction_lines',
            'bank_transfers',
            
            // Plans & Subscriptions
            'plans', 'user_plans', 'subscriptions', 'plan_requests', 'coupons',
            'admin_payment_settings',
            
            // Activity & Logs  
            'activity_logs', 'referral_transactions', 'login_details',
            'user_coupon', 'order_coupons',
            
            // CRM Activities (not master data) - Keep individual records but allow master data
            'lead_calls', 'lead_emails', 'lead_files', 'lead_discussions',
            'deal_calls', 'deal_emails', 'deal_files', 'deal_tasks',
            'leads', 'deals', // Keep individual leads and deals
            // lead_stages, labels, sources will be cloned as they are master data
            
            // Project Activities (not master data)
            'project_files', 'project_comments', 'project_notes', 'project_users',
            'tasks', 'task_comments', 'task_files', 'task_checklists',
            'milestones', 'timesheet', 'trackers',
            'projects', // Keep individual projects
            // projectstages, taskstages will be cloned as they are master data
            
            // HR Activities (not master data) - Keep individual records but allow master data
            'employees', 'employee_documents', 'allowances', 'commissions',
            'other_payments', 'overtimes', 'saturation_deductions', 'loans',
            'leaves', // Keep individual leave records
            'attendance_employees', 'payslips', 'set_salaries',
            'appraisals', // Keep individual appraisal records
            'goal_trackings', // Keep individual goal tracking records
            'trainings', // Keep individual training records
            'job_applications', 'job_on_boards', 'interview_schedules',
            'announcements', 'holidays', 'meetings', 'events',
            'awards', // Keep individual award records
            'transfers', 'resignations', 'travels',
            'promotions', 'complaints', 'warnings', 'terminations',
            'zoom_meetings', 'document_uploads', 'ip_restricts',
            // These WILL be cloned (removed from exclusions):
            // leave_types, allowance_options, deduction_options, 
            // indicators, goal_types, training_types, award_types,
            // career_levels, job_categories, job_stages, custom_questions
            
            // Permission & Role relationships (handle separately)
            'model_has_permissions', 'model_has_roles', 'role_has_permissions',
            
            // System Tables
            'migrations', 'sessions', 'webhook_settings',
            'email_template_langs', // Keep this excluded (language content)
            // user_email_templates will be cloned
            
            // Contract Activities (not master data) - Keep individual records but allow master data
            'contracts', 'contract_attachment', 'contract_comment', 'contract_notes',
            // contract_types will be cloned as it is master data
            
            // Form Builder Data
            'form_fields', 'form_field_responses', 'form_responses',
            'forms', 'form_builders',
        ];

        // Fields that should be reset/modified when cloning (based on your DB structure)
        $this->fieldsToReset = [
            // Financial balances
            'balance' => 0,
            'opening_balance' => '0.00',
            'current_balance' => '0.00',
            'credit_balance' => '0.00',
            
            // Inventory
            'quantity' => 0,
            'stock' => 0,
            
            // Counters
            'total_user' => 0,
            'total_customer' => 0,
            'total_vender' => 0,
            
            // Timestamps
            'created_at' => now(),
            'updated_at' => now(),
            
            // Status resets
            'is_active' => 1,
            'status' => 1,
        ];
    }

    /**
     * Main function: Clone all company data from source to target
     */
    public function cloneAllCompanyData()
    {
        if (!$this->sourceCompanyId) {
            \Log::error('No source company found to clone from');
            throw new \Exception('No source company found to clone from');
        }

        \Log::info("Starting to clone data from company {$this->sourceCompanyId} to company {$this->targetCompanyId}");

        DB::transaction(function () {
            $this->cloneAllTables();
        });

        \Log::info("Successfully completed cloning data to company {$this->targetCompanyId}");
        return true;
    }

    /**
     * Get the first company ID (template company)
     */
    private function getFirstCompanyId()
    {
        // Hardcoded template company ID for now
        $templateCompanyId = 2;
        
        // Verify this company exists
        $templateCompany = User::where('type', 'company')
            ->where('id', $templateCompanyId)
            ->first();
        
        if (!$templateCompany) {
            \Log::error("Template company ID {$templateCompanyId} not found");
            return null;
        }
        
        \Log::info("Using hardcoded company ID {$templateCompanyId} as template for cloning");
        return $templateCompanyId;
    }

    /**
     * Clone all relevant tables in dependency order
     */
    private function cloneAllTables()
    {
        // Define the order of table cloning to handle dependencies
        $tableOrder = [
            // Core types first (no dependencies)
            'chart_of_account_types',
            'chart_of_account_sub_types',
            
            // Then tables that depend on types
            'chart_of_accounts',
            'chart_of_account_parents',
            
            // Other independent tables
            'product_service_categories',
            'units',
            'taxes',
            'warehouses',
            'bank_accounts',
            'customers',
            'venders',
            'product_services',
            
            // Tables with dependencies on above
            'product_stocks',
            'warehouse_products',
            
            // CRM master data
            'pipelines',
            'stages',
            'lead_stages',
            'labels',
            'sources',
            
            // HR master data
            'branches',
            'departments',
            'designations',
            'job_stages',
            'leave_types',
            'allowance_options',
            'deduction_options',
            'indicators',
            'goal_types',
            'training_types',
            'award_types',
            'career_levels',
            'job_categories',
            'custom_questions',
            
            // Project master data
            'projectstages',
            'taskstages',
            
            // Contract master data
            'contract_types',
            
            // Other configuration tables
            'roles',
            'user_email_templates',
            'settings',
            'notification_templates',
            'landing_page_settings',
            'templates',
            'languages',
            'referral_settings',
        ];

        // Clone ordered tables first
        foreach ($tableOrder as $tableName) {
            if ($this->shouldCloneSpecificTable($tableName)) {
                $this->cloneTableWithMapping($tableName);
            }
        }

        // Clone remaining tables
        $allTables = $this->getAllTables();
        foreach ($allTables as $table) {
            $tableName = array_values((array) $table)[0];
            
            if (!in_array($tableName, $tableOrder) && $this->shouldCloneTable($table)) {
                $this->cloneTableWithMapping($tableName);
            }
        }
        
        // Special handling for role permissions
        $this->cloneRolePermissions();
    }

    /**
     * Get all tables in the database
     */
    private function getAllTables()
    {
        return DB::select("SHOW TABLES");
    }

    /**
     * Check if table should be cloned
     */
    private function shouldCloneTable($tableObj)
    {
        $tableName = array_values((array) $tableObj)[0];
        return $this->shouldCloneSpecificTable($tableName);
    }

    /**
     * Check if specific table should be cloned
     */
    private function shouldCloneSpecificTable($tableName)
    {
        // Skip excluded tables
        if (in_array($tableName, $this->excludedTables)) {
            return false;
        }

        // Skip system tables
        if (in_array($tableName, [
            'migrations', 'password_resets', 'failed_jobs', 
            'personal_access_tokens', 'sessions'
        ])) {
            return false;
        }

        // Check if table exists
        if (!Schema::hasTable($tableName)) {
            return false;
        }

        // Only clone tables with 'created_by' column
        return Schema::hasColumn($tableName, 'created_by');
    }

    /**
     * Clone specific table with ID mapping support
     */
    private function cloneTableWithMapping($tableName)
    {
        try {
            \Log::info("Attempting to clone table: {$tableName}");
            
            // Get source data
            $sourceData = DB::table($tableName)
                ->where('created_by', $this->sourceCompanyId)
                ->get();

            if ($sourceData->isEmpty()) {
                \Log::info("No data found in table {$tableName} for company {$this->sourceCompanyId}");
                return;
            }

            \Log::info("Found {$sourceData->count()} records in table {$tableName}");
            
            $columns = Schema::getColumnListing($tableName);
            
            foreach ($sourceData as $record) {
                $recordArray = (array) $record;
                $oldId = $recordArray['id'];
                
                $newRecord = $this->prepareRecordForCloning($recordArray, $columns, $tableName);
                
                $newId = DB::table($tableName)->insertGetId($newRecord);
                
                // Store the mapping for future reference
                $this->idMappings[$tableName][$oldId] = $newId;
            }

            \Log::info("Successfully cloned {$sourceData->count()} records from table {$tableName}");

        } catch (\Exception $e) {
            \Log::error("Error cloning table {$tableName}: " . $e->getMessage());
            \Log::error("Stack trace: " . $e->getTraceAsString());
        }
    }

    /**
     * Prepare record for cloning (modify necessary fields and handle relationships)
     */
    private function prepareRecordForCloning($record, $columns, $tableName)
    {
        // Remove id field (auto-increment)
        unset($record['id']);

        // Update created_by to target company
        $record['created_by'] = $this->targetCompanyId;

        // Handle foreign key relationships
        $record = $this->updateForeignKeys($record, $tableName);

        // Reset specific fields
        foreach ($this->fieldsToReset as $field => $value) {
            if (array_key_exists($field, $record)) {
                $record[$field] = $value;
            }
        }

        // Update timestamps
        $record['created_at'] = now();
        $record['updated_at'] = now();

        return $record;
    }

    /**
     * Update foreign key references to point to new IDs
     */
    private function updateForeignKeys($record, $tableName)
    {
        // Define foreign key relationships
        $foreignKeyMappings = [
            'chart_of_accounts' => [
                'type' => 'chart_of_account_types',
                'sub_type' => 'chart_of_account_sub_types',
                'parent' => 'chart_of_account_parents'
            ],
            'chart_of_account_sub_types' => [
                'type' => 'chart_of_account_types'
            ],
            'chart_of_account_parents' => [
                'type' => 'chart_of_account_types',
                'sub_type' => 'chart_of_account_sub_types',
                'account' => 'chart_of_accounts'
            ],
            'product_services' => [
                'category_id' => 'product_service_categories',
                'unit_id' => 'units',
                'tax_id' => 'taxes',
                'sale_chartaccount_id' => 'chart_of_accounts',
                'expense_chartaccount_id' => 'chart_of_accounts'
            ],
            'product_stocks' => [
                'product_id' => 'product_services',
                'warehouse_id' => 'warehouses'
            ],
            'warehouse_products' => [
                'warehouse_id' => 'warehouses',
                'product_id' => 'product_services'
            ],
            'stages' => [
                'pipeline_id' => 'pipelines'
            ],
            'lead_stages' => [
                'pipeline_id' => 'pipelines'
            ]
        ];

        if (isset($foreignKeyMappings[$tableName])) {
            foreach ($foreignKeyMappings[$tableName] as $foreignKeyField => $referencedTable) {
                if (isset($record[$foreignKeyField]) && $record[$foreignKeyField] > 0) {
                    $oldForeignId = $record[$foreignKeyField];
                    
                    // Check if we have a mapping for this ID
                    if (isset($this->idMappings[$referencedTable][$oldForeignId])) {
                        $record[$foreignKeyField] = $this->idMappings[$referencedTable][$oldForeignId];
                        \Log::info("Updated {$tableName}.{$foreignKeyField} from {$oldForeignId} to {$record[$foreignKeyField]}");
                    } else {
                        // If no mapping found, try to find by name or set to 0
                        if ($foreignKeyField === 'parent' && $referencedTable === 'chart_of_account_parents') {
                            // For parent relationships, set to 0 if no mapping found
                            $record[$foreignKeyField] = 0;
                        }
                    }
                }
            }
        }

        return $record;
    }

    /**
     * Special handling for role permissions
     */
    private function cloneRolePermissions()
    {
        try {
            \Log::info("Cloning role permissions");
            
            // Use the ID mappings for roles
            if (!isset($this->idMappings['roles'])) {
                \Log::warning("No role mappings found, skipping role permissions");
                return;
            }
            
            foreach ($this->idMappings['roles'] as $oldRoleId => $newRoleId) {
                // Get permissions for this role from source
                $rolePermissions = DB::table('role_has_permissions')
                    ->where('role_id', $oldRoleId)
                    ->get();
                
                // Copy permissions to target role
                foreach ($rolePermissions as $permission) {
                    DB::table('role_has_permissions')->insert([
                        'permission_id' => $permission->permission_id,
                        'role_id' => $newRoleId,
                    ]);
                }
                
                \Log::info("Cloned {$rolePermissions->count()} permissions for role ID: {$newRoleId}");
            }
            
        } catch (\Exception $e) {
            \Log::error("Error cloning role permissions: " . $e->getMessage());
        }
    }

    /**
     * Clone only specific module data
     */
    public function cloneSpecificModules($modules = [])
    {
        $moduleTableMap = [
            'crm' => ['pipelines', 'stages', 'lead_stages', 'labels', 'sources'],
            'inventory' => ['warehouses', 'product_service_categories', 'product_services', 'units', 'taxes'],
            'accounting' => ['chart_of_account_types', 'chart_of_account_sub_types', 'chart_of_accounts', 'chart_of_account_parents', 'bank_accounts'],
            'hr' => ['branches', 'departments', 'designations', 'job_stages'],
            'customers' => ['customers'],
            'vendors' => ['venders'],
        ];

        DB::transaction(function () use ($modules, $moduleTableMap) {
            foreach ($modules as $module) {
                if (isset($moduleTableMap[$module])) {
                    foreach ($moduleTableMap[$module] as $table) {
                        $this->cloneTableWithMapping($table);
                    }
                }
            }
        });
    }

    /**
     * Clone specific table
     */
    private function cloneSpecificTable($tableName)
    {
        if (!Schema::hasTable($tableName) || !Schema::hasColumn($tableName, 'created_by')) {
            return;
        }

        $this->cloneTableWithMapping($tableName);
    }

    /**
     * Get cloning summary
     */
    public function getCloningPreview()
    {
        $summary = [];
        $tables = $this->getAllTables();

        foreach ($tables as $tableObj) {
            $tableName = array_values((array) $tableObj)[0];
            
            if ($this->shouldCloneTable($tableObj)) {
                $count = DB::table($tableName)
                    ->where('created_by', $this->sourceCompanyId)
                    ->count();
                
                if ($count > 0) {
                    $summary[$tableName] = $count;
                }
            }
        }

        return $summary;
    }

    /**
     * Clone with exclusions (specify what NOT to clone)
     */
    public function cloneWithExclusions($excludeModules = [])
    {
        $moduleTableMap = [
            'customers' => ['customers'],
            'vendors' => ['venders'],
            'products' => ['product_services'],
            'inventory' => ['product_stocks'],
        ];

        $additionalExclusions = [];
        foreach ($excludeModules as $module) {
            if (isset($moduleTableMap[$module])) {
                $additionalExclusions = array_merge($additionalExclusions, $moduleTableMap[$module]);
            }
        }

        $this->excludedTables = array_merge($this->excludedTables, $additionalExclusions);
        
        return $this->cloneAllCompanyData();
    }

    /**
     * Debug method to check cloned data
     */
    public function debugClonedData($tableName = 'chart_of_accounts')
    {
        \Log::info("=== DEBUG: Checking cloned data for {$tableName} ===");
        
        $clonedData = DB::table($tableName)
            ->where('created_by', $this->targetCompanyId)
            ->get();
            
        \Log::info("Found {$clonedData->count()} records in {$tableName} for company {$this->targetCompanyId}");
        
        if ($tableName === 'chart_of_accounts') {
            // Check the type relationships
            $types = DB::table('chart_of_account_types')
                ->where('created_by', $this->targetCompanyId)
                ->pluck('id')
                ->toArray();
                
            \Log::info("Available type IDs for company {$this->targetCompanyId}: " . implode(', ', $types));
            
            $accountTypes = $clonedData->pluck('type')->unique()->toArray();
            \Log::info("Account type IDs in use: " . implode(', ', $accountTypes));
            
            $missingTypes = array_diff($accountTypes, $types);
            if (!empty($missingTypes)) {
                \Log::error("Missing type IDs: " . implode(', ', $missingTypes));
            } else {
                \Log::info("All type relationships are correct!");
            }
        }
        
        return $clonedData;
    }
}