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

        // OPTION 1: Use UserController's dynamic approach (faster, covers most cases)
        self::dynamicCascadeDelete($companyId);

        // OPTION 2: Use CleanupUnverifiedCompanies' detailed approach (thorough, handles complex relationships)
        self::detailedCascadeDelete($companyId);

        // Final cleanup
        self::cleanupRolePermissions($companyId);

        Log::info("Completed comprehensive cascade deletion for company ID: {$companyId}");
    }

    /**
     * UserController's approach - dynamic table discovery
     */
    private static function dynamicCascadeDelete($companyId)
    {
        Log::info("Running dynamic cascade deletion...");

        $allTables = self::getAllDatabaseTables();

        $excludedTables = [
            'migrations', 'password_resets', 'failed_jobs', 'personal_access_tokens',
            'sessions', 'permissions', 'plans', 'coupons', 'admin_payment_settings',
            'orders', 'order_products', 'plan_requests', 'subscriptions', 'user_plans',
            'order_coupons', 'user_coupons', 'transaction_orders'
        ];

        $specialTables = [
            // Certificate and letter tables - these are HUGE and need cleanup
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
            // Template language tables
            'email_template_langs' => [
                'query' => "DELETE etl FROM email_template_langs etl
                           JOIN email_templates et ON etl.parent_id = et.id
                           WHERE et.created_by = ?",
                'params' => [$companyId]
            ],
            'notification_template_langs' => [
                'query' => "DELETE ntl FROM notification_template_langs ntl
                           JOIN notification_templates nt ON ntl.parent_id = nt.id
                           WHERE nt.created_by = ?",
                'params' => [$companyId]
            ],
            // Other relationship tables
            'journal_items' => [
                'query' => "DELETE ji FROM journal_items ji
                           JOIN journal_entries je ON ji.journal = je.id
                           WHERE je.created_by = ?",
                'params' => [$companyId]
            ],
            'user_leads' => [
                'query' => "DELETE ul FROM user_leads ul
                           JOIN leads l ON ul.lead_id = l.id
                           WHERE l.created_by = ?",
                'params' => [$companyId]
            ],
            'user_deals' => [
                'query' => "DELETE ud FROM user_deals ud
                           JOIN deals d ON ud.deal_id = d.id
                           WHERE d.created_by = ?",
                'params' => [$companyId]
            ],
            'project_users' => [
                'query' => "DELETE pu FROM project_users pu
                           JOIN projects p ON pu.project_id = p.id
                           WHERE p.created_by = ?",
                'params' => [$companyId]
            ],
            'project_files' => [
                'query' => "DELETE pf FROM project_files pf
                           JOIN projects p ON pf.project_id = p.id
                           WHERE p.created_by = ?",
                'params' => [$companyId]
            ],
            'project_comments' => [
                'query' => "DELETE pc FROM project_comments pc
                           JOIN projects p ON pc.project_id = p.id
                           WHERE p.created_by = ?",
                'params' => [$companyId]
            ],
            'task_files' => [
                'query' => "DELETE tf FROM task_files tf
                           JOIN tasks t ON tf.task_id = t.id
                           WHERE t.created_by = ?",
                'params' => [$companyId]
            ],
            'task_comments' => [
                'query' => "DELETE tc FROM task_comments tc
                           JOIN tasks t ON tc.task_id = t.id
                           WHERE t.created_by = ?",
                'params' => [$companyId]
            ],
            'task_checklists' => [
                'query' => "DELETE tcl FROM task_checklists tcl
                           JOIN tasks t ON tcl.task_id = t.id
                           WHERE t.created_by = ?",
                'params' => [$companyId]
            ],
            'lead_calls' => [
                'query' => "DELETE lc FROM lead_calls lc
                           JOIN leads l ON lc.lead_id = l.id
                           WHERE l.created_by = ?",
                'params' => [$companyId]
            ],
            'lead_emails' => [
                'query' => "DELETE le FROM lead_emails le
                           JOIN leads l ON le.lead_id = l.id
                           WHERE l.created_by = ?",
                'params' => [$companyId]
            ],
            'lead_files' => [
                'query' => "DELETE lf FROM lead_files lf
                           JOIN leads l ON lf.lead_id = l.id
                           WHERE l.created_by = ?",
                'params' => [$companyId]
            ],
            'lead_discussions' => [
                'query' => "DELETE ld FROM lead_discussions ld
                           JOIN leads l ON ld.lead_id = l.id
                           WHERE l.created_by = ?",
                'params' => [$companyId]
            ],
            'deal_calls' => [
                'query' => "DELETE dc FROM deal_calls dc
                           JOIN deals d ON dc.deal_id = d.id
                           WHERE d.created_by = ?",
                'params' => [$companyId]
            ],
            'deal_emails' => [
                'query' => "DELETE de FROM deal_emails de
                           JOIN deals d ON de.deal_id = d.id
                           WHERE d.created_by = ?",
                'params' => [$companyId]
            ],
            'deal_files' => [
                'query' => "DELETE df FROM deal_files df
                           JOIN deals d ON df.deal_id = d.id
                           WHERE d.created_by = ?",
                'params' => [$companyId]
            ],
            'deal_tasks' => [
                'query' => "DELETE dt FROM deal_tasks dt
                           JOIN deals d ON dt.deal_id = d.id
                           WHERE d.created_by = ?",
                'params' => [$companyId]
            ],
            'contract_attachment' => [
                'query' => "DELETE ca FROM contract_attachment ca
                           JOIN contracts c ON ca.contract_id = c.id
                           WHERE c.created_by = ?",
                'params' => [$companyId]
            ],
            'contract_comment' => [
                'query' => "DELETE cc FROM contract_comment cc
                           JOIN contracts c ON cc.contract_id = c.id
                           WHERE c.created_by = ?",
                'params' => [$companyId]
            ],
            'contract_notes' => [
                'query' => "DELETE cn FROM contract_notes cn
                           JOIN contracts c ON cn.contract_id = c.id
                           WHERE c.created_by = ?",
                'params' => [$companyId]
            ],
            'form_fields' => [
                'query' => "DELETE ff FROM form_fields ff
                           JOIN forms f ON ff.form_id = f.id
                           WHERE f.created_by = ?",
                'params' => [$companyId]
            ],
            'form_field_responses' => [
                'query' => "DELETE ffr FROM form_field_responses ffr
                           JOIN form_responses fr ON ffr.response_id = fr.id
                           JOIN forms f ON fr.form_id = f.id
                           WHERE f.created_by = ?",
                'params' => [$companyId]
            ],
            'form_responses' => [
                'query' => "DELETE fr FROM form_responses fr
                           JOIN forms f ON fr.form_id = f.id
                           WHERE f.created_by = ?",
                'params' => [$companyId]
            ]
        ];

        // Alternative company identification columns - no need to check user types since we have the specific companyId
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
        $companyIdColumns = ['created_by' => $companyId, 'company_id' => $companyId, 'user_id' => $companyId];

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
     * CleanupUnverifiedCompanies' approach - detailed order with relationships
     */
    private static function detailedCascadeDelete($companyId)
    {
        Log::info("Running detailed cascade deletion...");

        // Get employee and user IDs for relationship handling
        $employeeIds = DB::table('employees')->where('created_by', $companyId)->pluck('id')->toArray();
        $userIds = DB::table('users')->where('created_by', $companyId)->pluck('id')->toArray();

        Log::info("Found " . count($employeeIds) . " employees and " . count($userIds) . " users for relationship cleanup");

        $deletionOrder = self::getDeletionOrder();

        foreach ($deletionOrder as $table => $config) {
            if (!Schema::hasTable($table)) continue;

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
     * Get all database tables - NOW PUBLIC
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
     * Get deletion order from CleanupUnverifiedCompanies
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

            // Deal/Lead activities (delete before deals/leads)
            'deal_calls' => ['created_by' => 'direct'],
            'deal_discussions' => ['created_by' => 'direct'],
            'deal_emails' => ['created_by' => 'direct'],
            'deal_files' => ['created_by' => 'direct'],
            'deal_tasks' => ['created_by' => 'direct'],
            'lead_calls' => ['created_by' => 'direct'],
            'lead_discussions' => ['created_by' => 'direct'],
            'lead_emails' => ['created_by' => 'direct'],
            'lead_files' => ['created_by' => 'direct'],
            'lead_activity_logs' => ['created_by' => 'direct'],

            // User relationships (delete before deals/leads)
            'user_deals' => ['user_id' => 'users'],
            'user_leads' => ['user_id' => 'users'],
            'user_contacts' => ['user_id' => 'users'],
            'user_to_dos' => ['user_id' => 'users'],
            'user_coupons' => ['user_id' => 'users'],
            'user_email_templates' => ['created_by' => 'direct'],

            // Financial records
            'invoices' => ['created_by' => 'direct'],
            'invoice_products' => ['created_by' => 'direct'],
            'invoice_payments' => ['created_by' => 'direct'],
            'invoice_bank_transfers' => ['created_by' => 'direct'],
            'bills' => ['created_by' => 'direct'],
            'bill_products' => ['created_by' => 'direct'],
            'bill_accounts' => ['created_by' => 'direct'],
            'bill_payments' => ['created_by' => 'direct'],
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
            'journal_items' => ['created_by' => 'direct'],
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
            'contract_attachment' => ['created_by' => 'direct'],
            'contract_comment' => ['created_by' => 'direct'],
            'contract_notes' => ['created_by' => 'direct'],

            // Company settings and configurations
            'announcements' => ['created_by' => 'direct'],
            'events' => ['created_by' => 'direct'],
            'meetings' => ['created_by' => 'direct'],
            'holidays' => ['created_by' => 'direct'],
            'company_policies' => ['created_by' => 'direct'],
            'complaints' => ['created_by' => 'direct'],
            'ducument_uploads' => ['created_by' => 'direct'],
            'documents' => ['created_by' => 'direct'],
            'join_us' => ['created_by' => 'direct'],
            'jobs' => ['created_by' => 'direct'],
            'custom_questions' => ['created_by' => 'direct'],
            'custom_fields' => ['created_by' => 'direct'],
            'custom_field_values' => ['created_by' => 'direct'],

            // System data
            'activity_logs' => ['created_by' => 'direct'],
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
            'email_template_langs' => ['created_by' => 'direct'],
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
     * Delete from table with relationship handling - NOW PUBLIC
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
     * Get table record count - NEWLY ADDED PUBLIC METHOD
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
            'user_coupons', 'transaction_orders'

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
}
