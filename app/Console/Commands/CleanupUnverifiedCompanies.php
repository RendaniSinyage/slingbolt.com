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
    protected $signature = 'cleanup:unverified-companies {--hours=48}';
    protected $description = 'Delete unverified company accounts older than specified hours';

    public function handle()
    {
        $hours = (int) $this->option('hours');
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

    private function cascadeDeleteCompanyData($companyId)
    {
        // Get all tables
        $tables = DB::select('SHOW TABLES');
        $databaseName = DB::getDatabaseName();
        $tableKey = "Tables_in_{$databaseName}";
        $allTables = array_map(fn($table) => $table->$tableKey, $tables);

        // Tables to skip
        $excludedTables = [
            'migrations', 'password_resets', 'failed_jobs', 'personal_access_tokens',
            'sessions', 'permissions', 'plans', 'coupons', 'admin_payment_settings',
            'orders', 'order_products', 'subscriptions', 'user_plans'
        ];

        // Special tables that need joins
        $specialTables = [
            'user_leads' => "DELETE ul FROM user_leads ul JOIN leads l ON ul.lead_id = l.id WHERE l.created_by = ?",
            'user_deals' => "DELETE ud FROM user_deals ud JOIN deals d ON ud.deal_id = d.id WHERE d.created_by = ?",
            'project_users' => "DELETE pu FROM project_users pu JOIN projects p ON pu.project_id = p.id WHERE p.created_by = ?",
            'task_files' => "DELETE tf FROM task_files tf JOIN tasks t ON tf.task_id = t.id WHERE t.created_by = ?"
        ];

        // Delete from special tables first
        foreach ($specialTables as $table => $query) {
            if (Schema::hasTable($table)) {
                try {
                    DB::delete($query, [$companyId]);
                } catch (\Exception $e) {
                    Log::error("Error deleting from {$table}: " . $e->getMessage());
                }
            }
        }

        // Delete from regular tables
        foreach ($allTables as $table) {
            if (in_array($table, $excludedTables) || array_key_exists($table, $specialTables)) {
                continue;
            }

            if (!Schema::hasTable($table)) {
                continue;
            }

            $columns = Schema::getColumnListing($table);
            $companyColumn = null;

            // Find company reference column
            foreach (['created_by', 'company_id', 'user_id'] as $column) {
                if (in_array($column, $columns)) {
                    $companyColumn = $column;
                    break;
                }
            }

            if ($companyColumn) {
                try {
                    DB::table($table)->where($companyColumn, $companyId)->delete();
                } catch (\Exception $e) {
                    Log::error("Error deleting from {$table}: " . $e->getMessage());
                }
            }
        }

        // Clean up roles and permissions
        try {
            DB::delete("DELETE rp FROM role_has_permissions rp JOIN roles r ON rp.role_id = r.id WHERE r.created_by = ?", [$companyId]);
            DB::delete("DELETE ur FROM model_has_roles ur JOIN users u ON ur.model_id = u.id WHERE u.created_by = ?", [$companyId]);
        } catch (\Exception $e) {
            Log::error("Error cleaning up roles: " . $e->getMessage());
        }
    }
}