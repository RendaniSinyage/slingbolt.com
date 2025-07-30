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

            // Get employee and user IDs
            $employeeIds = DB::table('employees')->where('created_by', $companyId)->pluck('id')->toArray();
            $userIds = DB::table('users')->where('created_by', $companyId)->pluck('id')->toArray();

            // Use service method:
            $allTables = \App\Services\CompanyCleanupService::getDeletionOrder();

            foreach ($allTables as $table => $config) {
                if (!Schema::hasTable($table)) continue;

                // Use service method:
                $count = \App\Services\CompanyCleanupService::getTableRecordCount($table, $config, $companyId, $employeeIds, $userIds);
                $totalCount += $count;
            }

            return $totalCount;
        }

    private function cascadeDeleteCompanyData($companyId)
    {
        \App\Services\CompanyCleanupService::cascadeDeleteCompanyData($companyId);
    }


private function deleteFromTable($table, $config, $companyId, $employeeIds, $userIds)
    {
        return \App\Services\CompanyCleanupService::deleteFromTable($table, $config, $companyId, $employeeIds, $userIds);
    }

    private function getAllCleanupTables()
        {
            // Call service method instead of local method:
            return \App\Services\CompanyCleanupService::getDeletionOrder();
        }

}
