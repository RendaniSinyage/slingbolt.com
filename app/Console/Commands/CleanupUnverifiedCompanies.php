<?php

namespace App\Console\Commands;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class CleanupUnverifiedCompanies extends Command
{
    protected $signature = 'cleanup:unverified-companies {--hours=48} {--dry-run}';
    protected $description = 'Delete unverified company accounts older than specified hours';

    public function handle()
    {
        $hours = (int) $this->option('hours');
        $dryRun = $this->option('dry-run');
        $cutoffTime = Carbon::now()->subHours($hours);
        
        $unverifiedCompanies = User::where('type', 'company')
            ->whereNull('email_verified_at')
            ->where('created_at', '<', $cutoffTime)
            ->get();

        if ($unverifiedCompanies->isEmpty()) {
            $this->info('No unverified companies found for cleanup.');
            return 0;
        }

        $this->info("Found {$unverifiedCompanies->count()} unverified companies for cleanup.");
        
        if ($dryRun) {
            $this->warn('DRY RUN MODE - No data will be deleted');
            foreach ($unverifiedCompanies as $company) {
                $count = $this->countCompanyData($company->id);
                $this->info("Would delete: {$company->name} ({$company->email}) - {$count} total records");
            }
            return 0;
        }

        $deletedCount = 0;
        foreach ($unverifiedCompanies as $company) {
            try {
                DB::transaction(function () use ($company) {
                    $this->cascadeDeleteCompanyData($company->id);
                    $company->delete();
                });

                $deletedCount++;
                $this->info("Deleted: {$company->name} ({$company->email})");

            } catch (\Exception $e) {
                $this->error("Failed to delete {$company->name}: " . $e->getMessage());
                Log::error("Cleanup failed for company {$company->id}: " . $e->getMessage());
            }
        }

        $this->info("Cleanup completed. Deleted {$deletedCount} companies.");
        return 0;
    }

    private function countCompanyData($companyId)
    {
        $totalCount = 0;
        
        // Get all employees for this company
        $employeeIds = DB::table('employees')->where('created_by', $companyId)->pluck('id')->toArray();
        $userIds = DB::table('users')->where('created_by', $companyId)->pluck('id')->toArray();
        
        $allTables = $this->getAllCleanupTables();
        
        foreach ($allTables as $table => $config) {
            if (!Schema::hasTable($table)) continue;
            
            $count = $this->getTableRecordCount($table, $config, $companyId, $employeeIds, $userIds);
            $totalCount += $count;
        }
        
        return $totalCount;
    }

    private function cascadeDeleteCompanyData($companyId)
    {
        // Get employee and user IDs for this company
        $employeeIds = DB::table('employees')->where('created_by', $companyId)->pluck('id')->toArray();
        $userIds = DB::table('users')->where('created_by', $companyId)->pluck('id')->toArray();
        
        $this->info("Cleaning up data for company {$companyId} with " . count($employeeIds) . " employees and " . count($userIds) . " users");

        // Delete in specific order to handle foreign key constraints
        $deletionOrder = $this->getDeletionOrder();
        
        foreach ($deletionOrder as $table => $config) {
            if (!Schema::hasTable($table)) {
                continue;
            }

            try {
                $deleted = $this->deleteFromTable($table, $config, $companyId, $employeeIds, $userIds);
                if ($deleted > 0) {
                    $this->info("Deleted {$deleted} records from {$table}");
                }
            } catch (\Exception $e) {
                $this->error("Error deleting from {$table}: " . $e->getMessage());
                Log::error("Error deleting from {$table} for company {$companyId}: " . $e->getMessage());
            }
        }

        // Clean up roles and permissions last
        $this->cleanupRolesAndPermissions($companyId);
    }

    private function getDeletionOrder()
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

    private function deleteFromTable($table, $config, $companyId, $employeeIds, $userIds)
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

    private function getTableRecordCount($table, $config, $companyId, $employeeIds, $userIds)
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

    private function getAllCleanupTables()
    {
        return $this->getDeletionOrder();
    }

    private function cleanupRolesAndPermissions($companyId)
    {
        try {
            // Delete role permissions for company roles
            DB::delete("DELETE rp FROM role_has_permissions rp JOIN roles r ON rp.role_id = r.id WHERE r.created_by = ?", [$companyId]);
            
            // Delete user roles for company users
            DB::delete("DELETE ur FROM model_has_roles ur JOIN users u ON ur.model_id = u.id WHERE u.created_by = ?", [$companyId]);
            
            // Delete company roles
            DB::table('roles')->where('created_by', $companyId)->delete();
            
            $this->info("Cleaned up roles and permissions");
        } catch (\Exception $e) {
            $this->error("Error cleaning up roles: " . $e->getMessage());
            Log::error("Error cleaning up roles for company {$companyId}: " . $e->getMessage());
        }
    }
}