<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Log;

class CompanyCleanupService
{
    /**
     * Main cleanup method
     */
    public static function cascadeDeleteCompanyData($companyId)
    {
        Log::info("Starting comprehensive cascade deletion for company ID: {$companyId}");

        // Get related IDs first for relationship handling
        $relatedIds = self::getRelatedIds($companyId);

        // OPTION 1: Use special relationship handling for problematic tables
        self::handleSpecialRelationships($companyId, $relatedIds);

        // OPTION 2: Use UserController's dynamic approach (faster, covers most cases)
        self::dynamicCascadeDelete($companyId);

        // OPTION 3: Use CleanupUnverifiedCompanies' detailed approach (thorough, handles complex relationships)
        self::detailedCascadeDelete($companyId, $relatedIds);

        // Final cleanup
        self::cleanupRolePermissions($companyId);

        Log::info("Completed comprehensive cascade deletion for company ID: {$companyId}");
    }

    /**
     * Get all related IDs for the company
     */
    private static function getRelatedIds($companyId)
    {
        $relatedIds = [
            'company_id' => $companyId,
            'employee_ids' => [],
            'user_ids' => [],
            'deal_ids' => [],
            'lead_ids' => [],
            'invoice_ids' => [],
            'bill_ids' => [],
            'contract_ids' => [],
            'project_ids' => [],
            'product_ids' => [],
            'chart_account_ids' => [],
        ];

        try {
            // Get employees
            if (Schema::hasTable('employees')) {
                $relatedIds['employee_ids'] = DB::table('employees')
                    ->where('created_by', $companyId)
                    ->pluck('id')->toArray();
            }

            // Get users
            if (Schema::hasTable('users')) {
                $relatedIds['user_ids'] = DB::table('users')
                    ->where('created_by', $companyId)
                    ->pluck('id')->toArray();
            }

            // Get deals
            if (Schema::hasTable('deals')) {
                $relatedIds['deal_ids'] = DB::table('deals')
                    ->where('created_by', $companyId)
                    ->pluck('id')->toArray();
            }

            // Get leads
            if (Schema::hasTable('leads')) {
                $relatedIds['lead_ids'] = DB::table('leads')
                    ->where('created_by', $companyId)
                    ->pluck('id')->toArray();
            }

            // Get invoices
            if (Schema::hasTable('invoices')) {
                $relatedIds['invoice_ids'] = DB::table('invoices')
                    ->where('created_by', $companyId)
                    ->pluck('id')->toArray();
            }

            // Get bills
            if (Schema::hasTable('bills')) {
                $relatedIds['bill_ids'] = DB::table('bills')
                    ->where('created_by', $companyId)
                    ->pluck('id')->toArray();
            }

            // Get contracts
            if (Schema::hasTable('contracts')) {
                $relatedIds['contract_ids'] = DB::table('contracts')
                    ->where('created_by', $companyId)
                    ->pluck('id')->toArray();
            }

            // Get projects
            if (Schema::hasTable('projects')) {
                $relatedIds['project_ids'] = DB::table('projects')
                    ->where('created_by', $companyId)
                    ->pluck('id')->toArray();
            }

            // Get products
            if (Schema::hasTable('product_services')) {
                $relatedIds['product_ids'] = DB::table('product_services')
                    ->where('created_by', $companyId)
                    ->pluck('id')->toArray();
            }

            // Get chart accounts
            if (Schema::hasTable('chart_of_accounts')) {
                $relatedIds['chart_account_ids'] = DB::table('chart_of_accounts')
                    ->where('created_by', $companyId)
                    ->pluck('id')->toArray();
            }

        } catch (\Exception $e) {
            Log::error("Error getting related IDs for company {$companyId}: " . $e->getMessage());
        }

        return $relatedIds;
    }

    /**
     * Handle tables with special relationships
     */
    private static function handleSpecialRelationships($companyId, $relatedIds)
    {
        Log::info("Handling special relationships...");

        $specialTables = [
            // Deal Related - Skip deal_calls as marked with X
            'deal_emails' => [
                'condition' => !empty($relatedIds['deal_ids']),
                'query' => "DELETE FROM deal_emails WHERE deal_id IN (" . str_repeat('?,', count($relatedIds['deal_ids']) - 1) . "?)",
                'params' => $relatedIds['deal_ids']
            ],
            'deal_files' => [
                'condition' => !empty($relatedIds['deal_ids']),
                'query' => "DELETE FROM deal_files WHERE deal_id IN (" . str_repeat('?,', count($relatedIds['deal_ids']) - 1) . "?)",
                'params' => $relatedIds['deal_ids']
            ],
            'deal_tasks' => [
                'condition' => !empty($relatedIds['deal_ids']),
                'query' => "DELETE FROM deal_tasks WHERE deal_id IN (" . str_repeat('?,', count($relatedIds['deal_ids']) - 1) . "?)",
                'params' => $relatedIds['deal_ids']
            ],

            // Lead Related
            'lead_calls' => [
                'condition' => !empty($relatedIds['user_ids']),
                'query' => "DELETE FROM lead_calls WHERE user_id IN (" . str_repeat('?,', count($relatedIds['user_ids']) - 1) . "?)",
                'params' => $relatedIds['user_ids']
            ],
            'lead_emails' => [
                'condition' => !empty($relatedIds['lead_ids']),
                'query' => "DELETE FROM lead_emails WHERE lead_id IN (" . str_repeat('?,', count($relatedIds['lead_ids']) - 1) . "?)",
                'params' => $relatedIds['lead_ids']
            ],
            'lead_files' => [
                'condition' => !empty($relatedIds['lead_ids']),
                'query' => "DELETE FROM lead_files WHERE lead_id IN (" . str_repeat('?,', count($relatedIds['lead_ids']) - 1) . "?)",
                'params' => $relatedIds['lead_ids']
            ],
            'lead_activity_logs' => [
                'condition' => !empty($relatedIds['lead_ids']) || !empty($relatedIds['user_ids']),
                'query' => "DELETE FROM lead_activity_logs WHERE " .
                          (!empty($relatedIds['lead_ids']) ? "lead_id IN (" . str_repeat('?,', count($relatedIds['lead_ids']) - 1) . "?)" : "1=0") .
                          (!empty($relatedIds['user_ids']) ? " OR user_id IN (" . str_repeat('?,', count($relatedIds['user_ids']) - 1) . "?)" : ""),
                'params' => array_merge($relatedIds['lead_ids'], $relatedIds['user_ids'])
            ],

            // Financial/Invoice
            'invoice_products' => [
                'condition' => !empty($relatedIds['product_ids']),
                'query' => "DELETE FROM invoice_products WHERE product_id IN (" . str_repeat('?,', count($relatedIds['product_ids']) - 1) . "?)",
                'params' => $relatedIds['product_ids']
            ],
            'invoice_payments' => [
                'condition' => !empty($relatedIds['invoice_ids']),
                'query' => "DELETE FROM invoice_payments WHERE invoice_id IN (" . str_repeat('?,', count($relatedIds['invoice_ids']) - 1) . "?)",
                'params' => $relatedIds['invoice_ids']
            ],
            'bill_products' => [
                'condition' => !empty($relatedIds['product_ids']),
                'query' => "DELETE FROM bill_products WHERE product_id IN (" . str_repeat('?,', count($relatedIds['product_ids']) - 1) . "?)",
                'params' => $relatedIds['product_ids']
            ],
            'bill_accounts' => [
                'condition' => !empty($relatedIds['chart_account_ids']),
                'query' => "DELETE FROM bill_accounts WHERE chart_account_id IN (" . str_repeat('?,', count($relatedIds['chart_account_ids']) - 1) . "?)",
                'params' => $relatedIds['chart_account_ids']
            ],
            'bill_payments' => [
                'condition' => !empty($relatedIds['bill_ids']),
                'query' => "DELETE FROM bill_payments WHERE bill_id IN (" . str_repeat('?,', count($relatedIds['bill_ids']) - 1) . "?)",
                'params' => $relatedIds['bill_ids']
            ],

            // Contract Related
            'contract_attachment' => [
                'condition' => !empty($relatedIds['contract_ids']),
                'query' => "DELETE FROM contract_attachment WHERE contract_id IN (" . str_repeat('?,', count($relatedIds['contract_ids']) - 1) . "?)",
                'params' => $relatedIds['contract_ids']
            ],
            'contract_comment' => [
                'condition' => !empty($relatedIds['contract_ids']),
                'query' => "DELETE FROM contract_comment WHERE contract_id IN (" . str_repeat('?,', count($relatedIds['contract_ids']) - 1) . "?)",
                'params' => $relatedIds['contract_ids']
            ],
            'contract_notes' => [
                'condition' => !empty($relatedIds['contract_ids']),
                'query' => "DELETE FROM contract_notes WHERE contract_id IN (" . str_repeat('?,', count($relatedIds['contract_ids']) - 1) . "?)",
                'params' => $relatedIds['contract_ids']
            ],

            // System tables with user_id
            'activity_logs' => [
                'condition' => !empty($relatedIds['user_ids']),
                'query' => "DELETE FROM activity_logs WHERE user_id IN (" . str_repeat('?,', count($relatedIds['user_ids']) - 1) . "?)",
                'params' => $relatedIds['user_ids']
            ],

            // Journal items (through journal relationship)
            'journal_items' => [
                'condition' => true,
                'query' => "DELETE ji FROM journal_items ji
                           JOIN journal_entries je ON ji.journal = je.id
                           WHERE je.created_by = ?",
                'params' => [$companyId]
            ],

            // Email template langs (through parent_id)
            'email_template_langs' => [
                'condition' => true,
                'query' => "DELETE etl FROM email_template_langs etl
                           JOIN email_templates et ON etl.parent_id = et.id
                           WHERE et.created_by = ?",
                'params' => [$companyId]
            ],
        ];

        foreach ($specialTables as $table => $config) {
            if (!Schema::hasTable($table)) {
                continue;
            }

            try {
                if ($config['condition'] && !empty($config['params'])) {
                    $deleted = DB::delete($config['query'], $config['params']);
                    if ($deleted > 0) {
                        Log::info("Special relationship: Deleted {$deleted} records from {$table}");
                    }
                }
            } catch (\Exception $e) {
                Log::error("Error in special relationship deletion from {$table}: " . $e->getMessage());
            }
        }
    }

    /**
     * UserController's approach - dynamic table discovery (UNCHANGED)
     */
    private static function dynamicCascadeDelete($companyId)
    {
        Log::info("Running dynamic cascade deletion...");

        $allTables = self::getAllDatabaseTables();

        $excludedTables = [
            'migrations', 'password_resets', 'failed_jobs', 'personal_access_tokens',
            'sessions', 'permissions', 'plans', 'coupons', 'admin_payment_settings',
            'orders', 'order_products', 'plan_requests', 'subscriptions', 'user_plans',
            'order_coupons', 'user_coupons', 'transaction_orders', 'join_us' // Added join_us as you marked "don't delete"
        ];

        // Skip tables we handle in special relationships
        $speciallyHandledTables = [
            'deal_emails', 'deal_files', 'deal_tasks', 'lead_calls', 'lead_emails',
            'lead_files', 'lead_activity_logs', 'invoice_products', 'invoice_payments',
            'bill_products', 'bill_accounts', 'bill_payments', 'contract_attachment',
            'contract_comment', 'contract_notes', 'activity_logs', 'journal_items',
            'email_template_langs'
        ];

        $excludedTables = array_merge($excludedTables, $speciallyHandledTables);

        // Certificate and letter tables - these are HUGE and need cleanup
        $specialTables = [
            'joining_letters' => [
                'query' => "DELETE FROM joining_letters WHERE created_by = ?",
                'params' => [$companyId]
            ],
            'experience_certificates' => [
                'query' => "DELETE FROM experience_certificates WHERE created_by = ?",
                'params' => [$companyId]
            ],
            'generate_offer_letters' => [
                'query' => "DELETE FROM generate_offer_letters WHERE created_by = ?",
                'params' => [$companyId]
            ],
            'noc_certificates' => [
                'query' => "DELETE FROM noc_certificates WHERE created_by = ?",
                'params' => [$companyId]
            ],
        ];

        // Alternative company identification columns
        $companyIdColumns = [
            'created_by' => $companyId,
            'company_id' => $companyId,
            'user_id' => $companyId
        ];

        // Delete from special tables first
        foreach ($specialTables as $table => $config) {
            try {
                if (Schema::hasTable($table)) {
                    $deleted = DB::delete($config['query'], $config['params']);
                    if ($deleted > 0) {
                        Log::info("Deleted {$deleted} records from special table: {$table}");
                    }
                }
            } catch (\Exception $e) {
                Log::error("Error deleting from special table {$table}: " . $e->getMessage());
            }
        }

        // Dynamic deletion for remaining tables
        foreach ($allTables as $table) {
            if (in_array($table, $excludedTables) || array_key_exists($table, $specialTables)) {
                continue;
            }

            try {
                if (!Schema::hasTable($table)) continue;

                $columns = Schema::getColumnListing($table);
                $companyColumn = null;

                foreach ($companyIdColumns as $column => $value) {
                    if (in_array($column, $columns)) {
                        $companyColumn = $column;
                        break;
                    }
                }

                if (!$companyColumn) continue;

                $deleted = DB::table($table)->where($companyColumn, $companyId)->delete();
                if ($deleted > 0) {
                    Log::info("Deleted {$deleted} records from table: {$table}");
                }

            } catch (\Exception $e) {
                Log::error("Error deleting from table {$table}: " . $e->getMessage());
            }
        }
    }

    /**
     * CleanupUnverifiedCompanies' approach - detailed order with relationships (MODIFIED)
     */
    private static function detailedCascadeDelete($companyId, $relatedIds)
    {
        Log::info("Running detailed cascade deletion...");

        $employeeIds = $relatedIds['employee_ids'];
        $userIds = $relatedIds['user_ids'];

        Log::info("Found " . count($employeeIds) . " employees and " . count($userIds) . " users for relationship cleanup");

        $deletionOrder = self::getDeletionOrder();

        // Skip tables we've already handled in special relationships
        $skipTables = [
            'deal_emails', 'deal_files', 'deal_tasks', 'lead_calls', 'lead_emails',
            'lead_files', 'lead_activity_logs', 'invoice_products', 'invoice_payments',
            'bill_products', 'bill_accounts', 'bill_payments', 'contract_attachment',
            'contract_comment', 'contract_notes', 'activity_logs', 'journal_items',
            'email_template_langs'
        ];

        foreach ($deletionOrder as $table => $config) {
            if (!Schema::hasTable($table) || in_array($table, $skipTables)) continue;

            try {
                $deleted = self::deleteFromTable($table, $config, $companyId, $employeeIds, $userIds);
                if ($deleted > 0) {
                    Log::info("Detailed deletion: {$deleted} records from {$table}");
                }
            } catch (\Exception $e) {
                Log::error("Error in detailed deletion from {$table}: " . $e->getMessage());
            }
        }
    }

    /**
     * Get all database tables
     */
    public static function getAllDatabaseTables()
    {
        try {
            $tables = DB::select('SHOW TABLES');
            $databaseName = DB::getDatabaseName();
            $tableKey = "Tables_in_{$databaseName}";

            return array_map(function($table) use ($tableKey) {
                return $table->$tableKey;
            }, $tables);

        } catch (\Exception $e) {
            Log::error("Error getting database tables: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get deletion order from CleanupUnverifiedCompanies (UNCHANGED)
     */
    public static function getDeletionOrder()
    {
        return [
            // Employee-related data (delete first due to FK constraints)
            'attendance_employees' => ['employee_id' => 'employees'],
            'announcement_employees' => ['employee_id' => 'employees'],
            'event_employees' => ['employee_id' => 'employees'],
            'meeting_employees' => ['employee_id' => 'employees'],
            'employee_documents' => ['employee_id' => 'employees'],
            'pay_slips' => ['employee_id' => 'employees'],
            'leaves' => ['employee_id' => 'employees'],
            'overtimes' => ['employee_id' => 'employees'],
            'awards' => ['employee_id' => 'employees'],
            'promotions' => ['employee_id' => 'employees'],
            'resignations' => ['employee_id' => 'employees'],
            'terminations' => ['employee_id' => 'employees'],
            'travels' => ['employee_id' => 'employees'],
            'warnings' => ['employee_id' => 'employees'],
            'time_trackers' => ['employee_id' => 'employees'],
            'timesheets' => ['employee_id' => 'employees'],
            'track_photos' => ['employee_id' => 'employees'],
            'appraisals' => ['employee_id' => 'employees'],
            'commissions' => ['employee_id' => 'employees'],
            'allowances' => ['employee_id' => 'employees'],
            'loans' => ['employee_id' => 'employees'],
            'saturation_deductions' => ['employee_id' => 'employees'],
            'set_salaries' => ['employee_id' => 'employees'],
            'genrate_payslip_options' => ['employee_id' => 'employees'],

            // Task/Project related (delete before projects)
            'task_checklists' => ['created_by' => 'direct'],
            'task_comments' => ['created_by' => 'direct'],
            'task_files' => ['created_by' => 'direct'],
            'project_tasks' => ['created_by' => 'direct'],
            'project_expenses' => ['created_by' => 'direct'],
            'project_invoices' => ['created_by' => 'direct'],
            'project_users' => ['project_id' => 'projects'],

            // User relationships (delete before deals/leads)
            'user_deals' => ['user_id' => 'users'],
            'user_leads' => ['user_id' => 'users'],
            'user_contacts' => ['user_id' => 'users'],
            'user_to_dos' => ['user_id' => 'users'],
            'user_coupons' => ['user_id' => 'users'],
            'user_email_templates' => ['created_by' => 'direct'],

            // Financial records
            'invoices' => ['created_by' => 'direct'],
            'bills' => ['created_by' => 'direct'],
            'revenues' => ['created_by' => 'direct'],
            'expenses' => ['created_by' => 'direct'],
            'payments' => ['created_by' => 'direct'],
            'other_payments' => ['created_by' => 'direct'],
            'bank_transfers' => ['created_by' => 'direct'],
            'transactions' => ['created_by' => 'direct'],
            'transaction_lines' => ['created_by' => 'direct'],
            'add_transaction_lines' => ['created_by' => 'direct'],
            'transaction_orders' => ['created_by' => 'direct'],
            'journal_entries' => ['created_by' => 'direct'],
            'budgets' => ['created_by' => 'direct'],
            'credit_notes' => ['created_by' => 'direct'],
            'debit_notes' => ['created_by' => 'direct'],
            'customer_credit_notes' => ['created_by' => 'direct'],
            'customer_debit_notes' => ['created_by' => 'direct'],

            // Purchase/Sales
            'purchases' => ['created_by' => 'direct'],
            'purchase_products' => ['created_by' => 'direct'],
            'purchase_payments' => ['created_by' => 'direct'],
            'quotations' => ['created_by' => 'direct'],
            'quotation_products' => ['created_by' => 'direct'],
            'proposals' => ['created_by' => 'direct'],
            'proposal_products' => ['created_by' => 'direct'],
            'estimations' => ['created_by' => 'direct'],

            // POS
            'pos' => ['created_by' => 'direct'],
            'pos_products' => ['created_by' => 'direct'],
            'pos_payments' => ['created_by' => 'direct'],

            // Inventory
            'warehouse_products' => ['created_by' => 'direct'],
            'warehouse_transfers' => ['created_by' => 'direct'],
            'stock_reports' => ['created_by' => 'direct'],

            // Forms and Applications
            'form_builders' => ['created_by' => 'direct'],
            'form_fields' => ['created_by' => 'direct'],
            'form_responses' => ['created_by' => 'direct'],
            'form_field_responses' => ['created_by' => 'direct'],
            'job_applications' => ['created_by' => 'direct'],
            'job_application_notes' => ['created_by' => 'direct'],
            'interview_schedules' => ['created_by' => 'direct'],
            'job_on_boards' => ['created_by' => 'direct'],

            // Bug tracking
            'bug_comments' => ['created_by' => 'direct'],
            'bug_files' => ['created_by' => 'direct'],
            'bugs' => ['created_by' => 'direct'],

            // Communications
            'ch_favorites' => ['user_id' => 'users'],
            'ch_messages' => ['from_id' => 'users'],
            'notifications' => ['created_by' => 'direct'],
            'zoom_meetings' => ['created_by' => 'direct'],

            // Goals and performance
            'goal_trackings' => ['created_by' => 'direct'],
            'goals' => ['created_by' => 'direct'],
            'indicators' => ['created_by' => 'direct'],

            // Training
            'trainings' => ['created_by' => 'direct'],
            'trainers' => ['created_by' => 'direct'],

            // Support
            'supports' => ['created_by' => 'direct'],
            'support_replies' => ['created_by' => 'direct'],

            // Main entities (delete after dependencies)
            'deals' => ['created_by' => 'direct'],
            'leads' => ['created_by' => 'direct'],
            'projects' => ['created_by' => 'direct'],
            'employees' => ['created_by' => 'direct'],
            'customers' => ['created_by' => 'direct'],
            'venders' => ['created_by' => 'direct'],
            'assets' => ['created_by' => 'direct'],
            'contracts' => ['created_by' => 'direct'],

            // Company settings and configurations
            'announcements' => ['created_by' => 'direct'],
            'events' => ['created_by' => 'direct'],
            'meetings' => ['created_by' => 'direct'],
            'holidays' => ['created_by' => 'direct'],
            'company_policies' => ['created_by' => 'direct'],
            'complaints' => ['created_by' => 'direct'],
            'ducument_uploads' => ['created_by' => 'direct'],
            'documents' => ['created_by' => 'direct'],
            'jobs' => ['created_by' => 'direct'],
            'custom_questions' => ['created_by' => 'direct'],
            'custom_fields' => ['created_by' => 'direct'],
            'custom_field_values' => ['created_by' => 'direct'],

            // System data
            'log_activities' => ['created_by' => 'direct'],
            'login_details' => ['user_id' => 'users'],
            'users_verify' => ['user_id' => 'users'],
            'ip_restricts' => ['created_by' => 'direct'],
            'webhook_settings' => ['created_by' => 'direct'],

            // Template and configuration data
            'email_templates' => ['created_by' => 'direct'],
            'project_email_templates' => ['created_by' => 'direct'],
            'notification_templates' => ['created_by' => 'direct'],
            'notification_template_langs' => ['created_by' => 'direct'],
            'templates' => ['created_by' => 'direct'],
            'settings' => ['created_by' => 'direct'],
            'landing_page_settings' => ['created_by' => 'direct'],
            'company_payment_settings' => ['created_by' => 'direct'],
            'referral_settings' => ['created_by' => 'direct'],
            'referral_transactions' => ['created_by' => 'direct'],

            // Company structure
            'branches' => ['created_by' => 'direct'],
            'departments' => ['created_by' => 'direct'],
            'designations' => ['created_by' => 'direct'],
            'warehouses' => ['created_by' => 'direct'],
            'bank_accounts' => ['created_by' => 'direct'],

            // Company master data
            'chart_of_accounts' => ['created_by' => 'direct'],
            'chart_of_account_parents' => ['created_by' => 'direct'],
            'chart_of_account_sub_types' => ['created_by' => 'direct'],
            'chart_of_account_types' => ['created_by' => 'direct'],
            'taxes' => ['created_by' => 'direct'],
            'product_services' => ['created_by' => 'direct'],
            'product_service_categories' => ['created_by' => 'direct'],
            'product_service_units' => ['created_by' => 'direct'],

            // Company configuration options
            'competencies' => ['created_by' => 'direct'],
            'award_types' => ['created_by' => 'direct'],
            'allowance_options' => ['created_by' => 'direct'],
            'deduction_options' => ['created_by' => 'direct'],
            'loan_options' => ['created_by' => 'direct'],
            'goal_types' => ['created_by' => 'direct'],
            'job_categories' => ['created_by' => 'direct'],
            'job_stages' => ['created_by' => 'direct'],
            'lead_stages' => ['created_by' => 'direct'],
            'leave_types' => ['created_by' => 'direct'],
            'payslip_types' => ['created_by' => 'direct'],
            'performance_types' => ['created_by' => 'direct'],
            'sources' => ['created_by' => 'direct'],
            'stages' => ['created_by' => 'direct'],
            'task_stages' => ['created_by' => 'direct'],
            'termination_types' => ['created_by' => 'direct'],
            'training_types' => ['created_by' => 'direct'],
            'contract_types' => ['created_by' => 'direct'],
            'bug_statuses' => ['created_by' => 'direct'],
            'pipelines' => ['created_by' => 'direct'],
            'projectstages' => ['created_by' => 'direct'],
            'milestones' => ['created_by' => 'direct'],
            'labels' => ['created_by' => 'direct'],

            // Documents and certificates
            'joining_letters' => ['created_by' => 'direct'],
            'experience_certificates' => ['created_by' => 'direct'],
            'generate_offer_letters' => ['created_by' => 'direct'],
            'noc_certificates' => ['created_by' => 'direct'],

            // Finally, users (delete last)
            'users' => ['created_by' => 'direct'],
        ];
    }

    /**
     * Delete from table with relationship handling
     */
    public static function deleteFromTable($table, $config, $companyId, $employeeIds, $userIds)
    {
        $deleted = 0;

        foreach ($config as $column => $type) {
            if ($type === 'direct') {
                $deleted += DB::table($table)->where($column, $companyId)->delete();
            } elseif ($type === 'employees' && !empty($employeeIds)) {
                $deleted += DB::table($table)->whereIn($column, $employeeIds)->delete();
            } elseif ($type === 'users' && !empty($userIds)) {
                $deleted += DB::table($table)->whereIn($column, $userIds)->delete();
            } elseif ($type === 'projects') {
                $projectIds = DB::table('projects')->where('created_by', $companyId)->pluck('id')->toArray();
                if (!empty($projectIds)) {
                    $deleted += DB::table($table)->whereIn($column, $projectIds)->delete();
                }
            }
        }

        return $deleted;
    }

    /**
     * Get table record count
     */
    public static function getTableRecordCount($table, $config, $companyId, $employeeIds, $userIds)
    {
        $count = 0;

        foreach ($config as $column => $type) {
            if ($type === 'direct') {
                $count += DB::table($table)->where($column, $companyId)->count();
            } elseif ($type === 'employees' && !empty($employeeIds)) {
                $count += DB::table($table)->whereIn($column, $employeeIds)->count();
            } elseif ($type === 'users' && !empty($userIds)) {
                $count += DB::table($table)->whereIn($column, $userIds)->count();
            } elseif ($type === 'projects') {
                $projectIds = DB::table('projects')->where('created_by', $companyId)->pluck('id')->toArray();
                if (!empty($projectIds)) {
                    $count += DB::table($table)->whereIn($column, $projectIds)->count();
                }
            }
        }

        return $count;
    }

    /**
     * Final role and permission cleanup
     */
    private static function cleanupRolePermissions($companyId)
    {
        try {
            // Delete role permissions for company roles
            $deleted = DB::delete("
                DELETE rp FROM role_has_permissions rp
                JOIN roles r ON rp.role_id = r.id
                WHERE r.created_by = ?
            ", [$companyId]);

            if ($deleted > 0) {
                Log::info("Deleted {$deleted} role permissions for company: {$companyId}");
            }

            // Delete user role assignments for company users
            $deleted = DB::delete("
                DELETE ur FROM model_has_roles ur
                JOIN users u ON ur.model_id = u.id
                WHERE u.created_by = ? AND ur.model_type = 'App\\\\Models\\\\User'
            ", [$companyId]);

            if ($deleted > 0) {
                Log::info("Deleted {$deleted} user role assignments for company: {$companyId}");
            }

            // Delete user permissions for company users
            $deleted = DB::delete("
                DELETE up FROM model_has_permissions up
                JOIN users u ON up.model_id = u.id
                WHERE u.created_by = ? AND up.model_type = 'App\\\\Models\\\\User'
            ", [$companyId]);

            if ($deleted > 0) {
                Log::info("Deleted {$deleted} user permissions for company: {$companyId}");
            }

        } catch (\Exception $e) {
            Log::error("Error cleaning up role permissions for company {$companyId}: " . $e->getMessage());
        }
    }

    /**
     * Preview what would be deleted (for UserController)
     */
    public static function getDeletionPreview($companyId)
    {
        $allTables = self::getAllDatabaseTables();
        $excludedTables = [
            'password_resets', 'failed_jobs', 'admin_payment_settings',
            'orders', 'plan_requests', 'subscriptions', 'order_coupons',
            'user_coupons', 'transaction_orders', 'join_us'
        ];

        $companyIdColumns = ['created_by', 'company_id', 'user_id'];
        $preview = [];

        foreach ($allTables as $table) {
            if (in_array($table, $excludedTables)) continue;

            try {
                if (!Schema::hasTable($table)) continue;

                $columns = Schema::getColumnListing($table);

                foreach ($companyIdColumns as $column) {
                    if (in_array($column, $columns)) {
                        $count = DB::table($table)->where($column, $companyId)->count();
                        if ($count > 0) {
                            $preview[$table] = [
                                'column' => $column,
                                'count' => $count
                            ];
                        }
                        break;
                    }
                }
            } catch (\Exception $e) {
                $preview[$table] = ['error' => $e->getMessage()];
            }
        }

        return $preview;
    }

    /**
     * Check if specific tables exist in database
     */
    public static function checkTablesExist($tableNames)
    {
        $results = [];

        foreach ($tableNames as $table) {
            $results[$table] = [
                'exists' => Schema::hasTable($table),
                'columns' => []
            ];

            if ($results[$table]['exists']) {
                try {
                    $results[$table]['columns'] = Schema::getColumnListing($table);
                } catch (\Exception $e) {
                    $results[$table]['error'] = $e->getMessage();
                }
            }
        }

        return $results;
    }

    /**
     * Get company data summary before deletion
     */
    public static function getCompanyDataSummary($companyId)
    {
        $relatedIds = self::getRelatedIds($companyId);
        $summary = [
            'company_id' => $companyId,
            'related_entities' => [
                'employees' => count($relatedIds['employee_ids']),
                'users' => count($relatedIds['user_ids']),
                'deals' => count($relatedIds['deal_ids']),
                'leads' => count($relatedIds['lead_ids']),
                'invoices' => count($relatedIds['invoice_ids']),
                'bills' => count($relatedIds['bill_ids']),
                'contracts' => count($relatedIds['contract_ids']),
                'projects' => count($relatedIds['project_ids']),
                'products' => count($relatedIds['product_ids']),
                'chart_accounts' => count($relatedIds['chart_account_ids']),
            ],
            'estimated_records_to_delete' => 0
        ];

        // Get estimated deletion count from all tables
        $deletionOrder = self::getDeletionOrder();
        $employeeIds = $relatedIds['employee_ids'];
        $userIds = $relatedIds['user_ids'];

        foreach ($deletionOrder as $table => $config) {
            if (Schema::hasTable($table)) {
                try {
                    $count = self::getTableRecordCount($table, $config, $companyId, $employeeIds, $userIds);
                    if ($count > 0) {
                        $summary['tables_with_data'][$table] = $count;
                        $summary['estimated_records_to_delete'] += $count;
                    }
                } catch (\Exception $e) {
                    // Silent skip for problematic tables
                }
            }
        }

        return $summary;
    }
}
