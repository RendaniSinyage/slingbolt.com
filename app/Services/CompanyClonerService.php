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
            'bank_transfers', 'journal_entries', 'journal_items',
            
            // Plans & Subscriptions
            'plans', 'user_plans', 'subscriptions', 'plan_requests', 'coupons',
            'admin_payment_settings',
            
            // Activity & Logs  
            'activity_logs', 'referral_transactions', 'login_details',
            'user_coupon', 'order_coupons',
            
            // CRM Activities (not master data)
            'lead_calls', 'lead_emails', 'lead_files', 'lead_discussions',
            'deal_calls', 'deal_emails', 'deal_files', 'deal_tasks',
            'leads', 'deals', 'user_leads', 'user_deals',
            
            // Project Activities (not master data)
            'project_files', 'project_comments', 'project_notes', 'project_users',
            'tasks', 'task_comments', 'task_files', 'task_checklists',
            'milestones', 'timesheet', 'trackers', 'time_trackers',
            'projects', 'project_tasks', 'project_expenses', 'project_invoices',
            
            // HR Activities (not master data)
            'employees', 'employee_documents', 'allowances', 'commissions',
            'other_payments', 'overtimes', 'saturation_deductions', 'loans',
            'leaves', 'attendance_employees', 'payslips', 'set_salaries', 'pay_slips',
            'appraisals', 'goal_trackings', 'trainings', 'awards',
            'job_applications', 'job_on_boards', 'interview_schedules',
            'announcements', 'holidays', 'meetings', 'events',
            'transfers', 'resignations', 'travels', 'promotions', 
            'complaints', 'warnings', 'terminations', 'zoom_meetings',
            
            // Certificates and generated documents
            'joining_letters', 'experience_certificates', 'generate_offer_letters', 'noc_certificates',
            
            // Contract Activities (not master data)
            'contracts', 'contract_attachment', 'contract_comment', 'contract_notes',
            
            // Form Builder Data
            'form_fields', 'form_field_responses', 'form_responses',
            'forms', 'form_builders',
            
            // Permission & Role relationships (handle separately)
            'model_has_permissions', 'model_has_roles', 'role_has_permissions',
            
            // System Tables
            'migrations', 'sessions', 'webhook_settings',
            'email_template_langs', 'notification_template_langs',
            
            // Other activity data
            'ducument_uploads', 'ip_restricts', 'custom_field_values',
        ];

        // Fields that should be reset/modified when cloning
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
     * Clone all relevant tables in correct dependency order
     */
    private function cloneAllTables()
    {
        // Define the CORRECT order of table cloning to handle ALL dependencies
        $tableOrder = [
            // LEVEL 1: No dependencies (foundation tables)
            'chart_of_account_types',
            'product_service_categories',
            'product_service_units',
            'taxes',
            'warehouses',
            'branches',
            'departments',
            'designations',
            'pipelines',
            'bug_statuses',
            'sources',
            
            // LEVEL 2: Depend on Level 1
            'chart_of_account_sub_types', // depends on chart_of_account_types
            'stages', // depends on pipelines
            'lead_stages', // depends on pipelines
            
            // LEVEL 3: Chart of accounts (complex dependencies)
            'chart_of_accounts', // depends on types, sub_types
            
            // LEVEL 4: Chart of account parents (self-referencing)
            'chart_of_account_parents', // depends on chart_of_accounts
            
            // LEVEL 5: Products and services
            'product_services', // depends on categories, units, taxes, chart_of_accounts
            
            // LEVEL 6: Dependent on products/services
            'warehouse_products', // depends on warehouses, product_services
            
            // LEVEL 7: Customers and vendors (may reference chart_of_accounts)
            'customers',
            'venders',
            
            // LEVEL 8: Bank accounts (may reference chart_of_accounts)
            'bank_accounts',
            
            // LEVEL 9: HR Configuration
            'leave_types',
            'allowance_options',
            'deduction_options',
            'loan_options',
            'goal_types',
            'training_types',
            'award_types',
            'performance_types',
            'job_categories',
            'job_stages',
            'termination_types',
            'payslip_types',
            
            // LEVEL 10: Project Configuration
            'task_stages',
            
            // LEVEL 11: Contract Configuration
            'contract_types',
            
            // LEVEL 12: Other Configuration
            'competencies',
            'labels',
            'custom_questions',
            'documents',
            
            // LEVEL 13: Settings and Templates
            'settings',
            'email_templates',
            'notification_templates',
            'user_email_templates',
            'landing_page_settings',
            'templates',
            'company_payment_settings',
            'referral_settings',
            
            // LEVEL 14: Roles (should be cloned last before permissions)
            'roles',
        ];

        // Clone ordered tables first
        foreach ($tableOrder as $tableName) {
            if ($this->shouldCloneSpecificTable($tableName)) {
                $this->cloneTableWithMapping($tableName);
            }
        }

        // Clone remaining tables that aren't in the ordered list
        $allTables = $this->getAllTables();
        foreach ($allTables as $table) {
            $tableName = array_values((array) $table)[0];
            
            if (!in_array($tableName, $tableOrder) && $this->shouldCloneTable($table)) {
                $this->cloneTableWithMapping($tableName);
            }
        }
        
        // Handle self-referencing relationships after all records are created
        $this->fixSelfReferencingRelationships();
        
        // Special handling for role permissions (LAST)
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
                ->orderBy('id') // Important for self-referencing tables
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
            // COMPREHENSIVE foreign key mappings
            $foreignKeyMappings = [
                // Chart of Accounts relationships
                'chart_of_account_sub_types' => [
                    'type' => 'chart_of_account_types'
                ],
                'chart_of_accounts' => [
                    'type' => 'chart_of_account_types',
                    'sub_type' => 'chart_of_account_sub_types',
                    // parent handled separately due to self-referencing
                ],
                'chart_of_account_parents' => [
                    'type' => 'chart_of_account_types',
                    'sub_type' => 'chart_of_account_sub_types',
                    'account' => 'chart_of_accounts'
                ],
                
                // Product and Service relationships
                'product_services' => [
                    'category_id' => 'product_service_categories',
                    'unit_id' => 'product_service_units',
                    'tax_id' => 'taxes',
                    'sale_chartaccount_id' => 'chart_of_accounts',
                    'expense_chartaccount_id' => 'chart_of_accounts'
                ],
                'warehouse_products' => [
                    'warehouse_id' => 'warehouses',
                    'product_id' => 'product_services'
                ],
                
                // CRM relationships
                'stages' => [
                    'pipeline_id' => 'pipelines'
                ],
                'lead_stages' => [
                    'pipeline_id' => 'pipelines'
                ],
                
                // HR RELATIONSHIPS (MISSING BEFORE!)
                'departments' => [
                    'branch_id' => 'branches'  // This was missing!
                ],
                'designations' => [
                    'department_id' => 'departments',  // This was missing!
                    'branch_id' => 'branches'  // This might also exist
                ],
                'employees' => [
                    'branch_id' => 'branches',
                    'department_id' => 'departments',
                    'designation_id' => 'designations'
                ],
                
                // Customer/Vendor relationships
                'customers' => [
                    'billing_address' => 'chart_of_accounts',
                    'shipping_address' => 'chart_of_accounts',
                ],
                'venders' => [
                    'billing_address' => 'chart_of_accounts',
                ],
                
                // Bank Account relationships
                'bank_accounts' => [
                    'chart_account_id' => 'chart_of_accounts'
                ],
                
                // Job/Recruitment relationships
                'job_stages' => [
                    'pipeline_id' => 'pipelines'
                ],
                'jobs' => [
                    'branch_id' => 'branches',
                    'department_id' => 'departments',
                    'category_id' => 'job_categories'
                ],
                
                // Leave/HR Policy relationships
                'leave_types' => [
                    'department_id' => 'departments'  // If leave types are department-specific
                ],
                
                // Goal and Performance relationships
                'goals' => [
                    'department_id' => 'departments',
                    'branch_id' => 'branches',
                    'goal_type_id' => 'goal_types'
                ],
                'indicators' => [
                    'department_id' => 'departments',
                    'designation_id' => 'designations'
                ],
                
                // Training relationships
                'trainings' => [
                    'branch_id' => 'branches',
                    'department_id' => 'departments',
                    'training_type_id' => 'training_types'
                ],
                
                // Project relationships
                'projects' => [
                    'branch_id' => 'branches',
                    'department_id' => 'departments'
                ],
                'tasks' => [
                    'project_id' => 'projects'
                ],
                
                // Award relationships
                'awards' => [
                    'employee_id' => 'employees',
                    'award_type_id' => 'award_types'
                ],
                
                // Contract relationships
                'contracts' => [
                    'contract_type_id' => 'contract_types'
                ],
                
                // Email template relationships
                'user_email_templates' => [
                    'template_id' => 'email_templates',
                    'user_id' => 'users' // Skip this one since we don't clone users
                ],
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
                        // If no mapping found, handle gracefully
                        if ($foreignKeyField === 'user_id') {
                            // For user_id, set to null or 0 since we don't clone users
                            $record[$foreignKeyField] = null;
                        } elseif (in_array($foreignKeyField, ['parent', 'parent_id'])) {
                            // For parent relationships, set to null initially (will fix later)
                            $record[$foreignKeyField] = null;
                        } else {
                            \Log::warning("No mapping found for {$tableName}.{$foreignKeyField} = {$oldForeignId} (table: {$referencedTable})");
                            // Keep the original value, might work if it's a system reference
                        }
                    }
                }
            }
        }

        return $record;
    }

    /**
     * Fix self-referencing relationships after all records are created
     */
    private function fixSelfReferencingRelationships()
    {
        \Log::info("Fixing self-referencing relationships...");
        
        // Handle chart_of_accounts parent relationships
        if (isset($this->idMappings['chart_of_accounts'])) {
            $this->fixChartOfAccountsParents();
        }
        
        // Handle chart_of_account_parents
        if (isset($this->idMappings['chart_of_account_parents'])) {
            $this->fixChartOfAccountParentReferences();
        }
        
        // Add other self-referencing tables as needed
    }

    /**
     * Fix chart of accounts parent relationships
     */
    private function fixChartOfAccountsParents()
    {
        // Get original parent relationships
        $originalAccounts = DB::table('chart_of_accounts')
            ->where('created_by', $this->sourceCompanyId)
            ->whereNotNull('parent')
            ->where('parent', '>', 0)
            ->get(['id', 'parent']);
            
        foreach ($originalAccounts as $account) {
            $oldId = $account->id;
            $oldParentId = $account->parent;
            
            // Find the new IDs
            $newId = $this->idMappings['chart_of_accounts'][$oldId] ?? null;
            $newParentId = $this->idMappings['chart_of_accounts'][$oldParentId] ?? null;
            
            if ($newId && $newParentId) {
                DB::table('chart_of_accounts')
                    ->where('id', $newId)
                    ->update(['parent' => $newParentId]);
                    
                \Log::info("Fixed chart_of_accounts parent: ID {$newId} -> Parent {$newParentId}");
            }
        }
    }

    /**
     * Fix chart of account parents table references
     */
    private function fixChartOfAccountParentReferences()
    {
        // Similar logic for chart_of_account_parents if it has self-references
        // This table might reference chart_of_accounts, which should already be handled
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
                    // Check if this permission assignment already exists
                    $exists = DB::table('role_has_permissions')
                        ->where('role_id', $newRoleId)
                        ->where('permission_id', $permission->permission_id)
                        ->exists();
                        
                    if (!$exists) {
                        DB::table('role_has_permissions')->insert([
                            'permission_id' => $permission->permission_id,
                            'role_id' => $newRoleId,
                        ]);
                    }
                }
                
                \Log::info("Cloned {$rolePermissions->count()} permissions for role ID: {$newRoleId}");
            }
            
        } catch (\Exception $e) {
            \Log::error("Error cloning role permissions: " . $e->getMessage());
        }
    }

    /**
     * Debug method to analyze table relationships and identify missing foreign keys
     */
    public function analyzeTableRelationships($tableName = null)
    {
        \Log::info("=== ANALYZING TABLE RELATIONSHIPS ===");
        
        $tablesToAnalyze = $tableName ? [$tableName] : [
            'branches', 'departments', 'designations', 'product_service_categories', 
            'product_services', 'customers', 'venders', 'jobs'
        ];
        
        foreach ($tablesToAnalyze as $table) {
            if (!Schema::hasTable($table)) {
                \Log::warning("Table {$table} does not exist");
                continue;
            }
            
            $columns = Schema::getColumnListing($table);
            \Log::info("=== TABLE: {$table} ===");
            \Log::info("Columns: " . implode(', ', $columns));
            
            // Look for potential foreign key columns
            $foreignKeyColumns = array_filter($columns, function($column) {
                return preg_match('/_id$/', $column) || 
                       in_array($column, ['parent', 'type', 'sub_type', 'category', 'department', 'branch']);
            });
            
            if (!empty($foreignKeyColumns)) {
                \Log::info("Potential FK columns in {$table}: " . implode(', ', $foreignKeyColumns));
                
                // Check actual data for this company
                $sampleData = DB::table($table)
                    ->where('created_by', $this->targetCompanyId)
                    ->limit(3)
                    ->get();
                    
                foreach ($sampleData as $record) {
                    $recordArray = (array) $record;
                    $fkData = [];
                    foreach ($foreignKeyColumns as $fkCol) {
                        if (isset($recordArray[$fkCol]) && $recordArray[$fkCol] > 0) {
                            $fkData[$fkCol] = $recordArray[$fkCol];
                        }
                    }
                    if (!empty($fkData)) {
                        \Log::info("Sample FK data in {$table} ID {$recordArray['id']}: " . json_encode($fkData));
                    }
                }
            } else {
                \Log::info("No foreign key columns found in {$table}");
            }
        }
        
        return true;
    }

    /**
     * Debug method to check cloned data and relationships
     */
    public function debugClonedData($tableName = 'chart_of_accounts')
    {
        \Log::info("=== DEBUG: Checking cloned data for {$tableName} ===");
        
        $clonedData = DB::table($tableName)
            ->where('created_by', $this->targetCompanyId)
            ->get();
            
        \Log::info("Found {$clonedData->count()} records in {$tableName} for company {$this->targetCompanyId}");
        
        if ($tableName === 'departments') {
            // Check branch relationships
            $branches = DB::table('branches')
                ->where('created_by', $this->targetCompanyId)
                ->pluck('id', 'name')
                ->toArray();
                
            \Log::info("Available branches for company {$this->targetCompanyId}: " . json_encode($branches));
            
            foreach ($clonedData as $dept) {
                $branchInfo = isset($dept->branch_id) ? 
                    " (branch_id: {$dept->branch_id})" : 
                    " (no branch_id column or value)";
                \Log::info("Department: {$dept->name}{$branchInfo}");
            }
        }
        
        if ($tableName === 'designations') {
            // Check department relationships
            $departments = DB::table('departments')
                ->where('created_by', $this->targetCompanyId)
                ->pluck('id', 'name')
                ->toArray();
                
            \Log::info("Available departments for company {$this->targetCompanyId}: " . json_encode($departments));
            
            foreach ($clonedData as $designation) {
                $deptInfo = isset($designation->department_id) ? 
                    " (department_id: {$designation->department_id})" : 
                    " (no department_id column or value)";
                \Log::info("Designation: {$designation->name}{$deptInfo}");
            }
        }
        
        if ($tableName === 'chart_of_accounts') {
            // Check the type relationships
            $types = DB::table('chart_of_account_types')
                ->where('created_by', $this->targetCompanyId)
                ->pluck('id', 'name')
                ->toArray();
                
            \Log::info("Available type IDs for company {$this->targetCompanyId}: " . json_encode($types));
            
            // Check parent relationships
            $parentAccounts = $clonedData->where('parent', '>', 0);
            \Log::info("Accounts with parents: {$parentAccounts->count()}");
            
            foreach ($parentAccounts as $account) {
                $parentExists = $clonedData->where('id', $account->parent)->first();
                if (!$parentExists) {
                    \Log::error("Broken parent relationship: Account ID {$account->id} references non-existent parent {$account->parent}");
                } else {
                    \Log::info("Valid parent relationship: Account ID {$account->id} -> Parent {$account->parent} ({$parentExists->name})");
                }
            }
        }
        
        return $clonedData;
    }

    // ... (rest of the methods remain the same)
    public function cloneSpecificModules($modules = [])
    {
        $moduleTableMap = [
            'crm' => ['pipelines', 'stages', 'lead_stages', 'labels', 'sources'],
            'inventory' => ['warehouses', 'product_service_categories', 'product_services', 'product_service_units', 'taxes'],
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

    public function cloneWithExclusions($excludeModules = [])
    {
        $moduleTableMap = [
            'customers' => ['customers'],
            'vendors' => ['venders'],
            'products' => ['product_services'],
            'inventory' => ['warehouse_products'],
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
}