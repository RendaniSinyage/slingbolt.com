<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class CleanupOrphanedData extends Command
{
    protected $signature = 'cleanup:orphaned-data {--dry-run} {--table=} {--limit=1000}';
    protected $description = 'Clean up orphaned data from deleted companies with dry-run option';

    public function handle()
    {
        $dryRun = $this->option('dry-run');
        $specificTable = $this->option('table');
        $limit = (int) $this->option('limit');

        if ($dryRun) {
            $this->warn('🔍 DRY RUN MODE - No data will be deleted, just showing what would be cleaned');
        }

        $this->info('🧹 Starting orphaned data cleanup...');

        // Get all existing company IDs (only companies, not super admins)
        $existingCompanyIds = DB::table('users')
            ->where('type', 'company')
            ->pluck('id')
            ->toArray();

        // Get all valid user IDs (companies + super admins + other valid users)
        $allValidUserIds = DB::table('users')
            ->whereIn('type', ['company', 'super admin', 'user', 'employee'])
            ->pluck('id')
            ->toArray();

        $this->info('📊 Found ' . count($existingCompanyIds) . ' active companies');
        $this->info('📊 Found ' . count($allValidUserIds) . ' total valid users (including super admins)');

        if ($specificTable) {
            $this->cleanupTable($specificTable, $existingCompanyIds, $allValidUserIds, $dryRun, $limit);
        } else {
            $this->cleanupAllTables($existingCompanyIds, $allValidUserIds, $dryRun, $limit);
        }

        $this->info('✅ Orphaned data cleanup completed!');
        
        if ($dryRun) {
            $this->warn('ℹ️  This was a dry run. Add --no-dry-run to actually delete the data');
        }

        return 0;
    }

    private function cleanupAllTables($existingCompanyIds, $allValidUserIds, $dryRun, $limit)
    {
        $tables = $this->getTablesWithOrphanedData();
        $totalOrphaned = 0;
        $totalDeleted = 0;
        $orphanedCompanyData = [];

        foreach ($tables as $tableConfig) {
            $result = $this->cleanupTable(
                $tableConfig['table'], 
                $existingCompanyIds, 
                $allValidUserIds,
                $dryRun, 
                $limit, 
                $tableConfig
            );
            
            $totalOrphaned += $result['orphaned'];
            $totalDeleted += $result['deleted'];
            
            // Collect orphaned company data
            if (isset($result['orphaned_companies'])) {
                foreach ($result['orphaned_companies'] as $companyId => $count) {
                    if (!isset($orphanedCompanyData[$companyId])) {
                        $orphanedCompanyData[$companyId] = ['tables' => 0, 'total_records' => 0];
                    }
                    $orphanedCompanyData[$companyId]['tables']++;
                    $orphanedCompanyData[$companyId]['total_records'] += $count;
                }
            }
        }

        $this->info("📈 Summary:");
        $this->info("   Total orphaned records found: " . number_format($totalOrphaned));
        if (!$dryRun) {
            $this->info("   Total records deleted: " . number_format($totalDeleted));
        }
        
        // Show orphaned company breakdown
        if (!empty($orphanedCompanyData)) {
            $this->info("");
            $this->info("🏢 Orphaned Data by Deleted Company ID:");
            
            // Sort by total records descending
            arsort($orphanedCompanyData, SORT_NUMERIC);
            
            $this->table(
                ['Company ID', 'Tables Affected', 'Total Records', 'Avg Records/Table'],
                array_map(function($companyId, $data) {
                    return [
                        $companyId,
                        $data['tables'],
                        number_format($data['total_records']),
                        number_format($data['total_records'] / $data['tables'], 1)
                    ];
                }, array_keys($orphanedCompanyData), $orphanedCompanyData)
            );
            
            $this->info("💡 These company IDs were deleted but left behind data in the database.");
        }
    }

    private function cleanupTable($tableName, $existingCompanyIds, $allValidUserIds, $dryRun, $limit, $config = null)
    {
        if (!Schema::hasTable($tableName)) {
            $this->warn("⚠️  Table '{$tableName}' does not exist");
            return ['orphaned' => 0, 'deleted' => 0];
        }

        $this->info("🔍 Checking table: {$tableName}");

        try {
            if ($config && isset($config['special_query'])) {
                return $this->handleSpecialTable($tableName, $config, $existingCompanyIds, $allValidUserIds, $dryRun, $limit);
            } else {
                return $this->handleRegularTable($tableName, $existingCompanyIds, $allValidUserIds, $dryRun, $limit);
            }
        } catch (\Exception $e) {
            $this->error("❌ Error processing table {$tableName}: " . $e->getMessage());
            Log::error("Orphaned cleanup error for table {$tableName}: " . $e->getMessage());
            return ['orphaned' => 0, 'deleted' => 0];
        }
    }

    private function handleRegularTable($tableName, $existingCompanyIds, $allValidUserIds, $dryRun, $limit)
    {
        $columns = Schema::getColumnListing($tableName);
        $companyColumn = null;

        // Find company reference column
        foreach (['created_by', 'company_id', 'user_id'] as $column) {
            if (in_array($column, $columns)) {
                $companyColumn = $column;
                break;
            }
        }

        if (!$companyColumn) {
            $this->warn("   ⏭️  No company reference column found");
            return ['orphaned' => 0, 'deleted' => 0];
        }

        // Determine which valid IDs to use based on column type
        $validIds = $existingCompanyIds; // Default to only companies
        
        // For user_id columns, we should preserve all valid users (including super admins)
        if ($companyColumn === 'user_id') {
            $validIds = $allValidUserIds;
        }
        
        // For created_by columns, check if table should preserve super admin data
        if ($companyColumn === 'created_by') {
            $systemTables = ['settings', 'email_templates', 'users']; // Tables that might have super admin data
            if (in_array($tableName, $systemTables)) {
                $validIds = $allValidUserIds;
            }
        }

        // Count orphaned records (excluding system records with 0 or null values)
        $orphanedCount = DB::table($tableName)
            ->whereNotIn($companyColumn, $validIds)
            ->where($companyColumn, '>', 0) // Exclude system records
            ->whereNotNull($companyColumn)
            ->count();

        if ($orphanedCount === 0) {
            $this->info("   ✅ No orphaned records");
            return ['orphaned' => 0, 'deleted' => 0];
        }

        $this->warn("   🗑️  Found {$orphanedCount} orphaned records (column: {$companyColumn})");

        // Get orphaned company breakdown - only show deleted company IDs, not super admin IDs
        $orphanedCompanies = DB::table($tableName)
            ->whereNotIn($companyColumn, $validIds)
            ->where($companyColumn, '>', 0)
            ->whereNotNull($companyColumn)
            ->select($companyColumn, DB::raw('COUNT(*) as count'))
            ->groupBy($companyColumn)
            ->pluck('count', $companyColumn)
            ->toArray();

        // Filter out super admin IDs from the company breakdown
        $deletedCompanyIds = [];
        foreach ($orphanedCompanies as $id => $count) {
            // Check if this ID was actually a company (not super admin)
            $wasCompany = DB::table('users')->where('id', $id)->where('type', 'company')->exists();
            if ($wasCompany || !DB::table('users')->where('id', $id)->exists()) {
                // Either was a company or the user record is completely gone
                $deletedCompanyIds[$id] = $count;
            }
        }

        if ($dryRun) {
            // Show sample orphaned company IDs
            $sampleOrphanedIds = array_keys($deletedCompanyIds);
            $this->info("      Sample orphaned company IDs: " . implode(', ', array_slice($sampleOrphanedIds, 0, 10)));
            return ['orphaned' => $orphanedCount, 'deleted' => 0, 'orphaned_companies' => $deletedCompanyIds];
        }

        // Delete orphaned records in batches
        $totalDeleted = 0;
        do {
            $deleted = DB::table($tableName)
                ->whereNotIn($companyColumn, $validIds)
                ->where($companyColumn, '>', 0)
                ->whereNotNull($companyColumn)
                ->limit($limit)
                ->delete();
            
            $totalDeleted += $deleted;
            
            if ($deleted > 0) {
                $this->info("      Deleted {$deleted} records (total: {$totalDeleted})");
            }
        } while ($deleted > 0);

        return ['orphaned' => $orphanedCount, 'deleted' => $totalDeleted, 'orphaned_companies' => $deletedCompanyIds];
    }

    private function handleSpecialTable($tableName, $config, $existingCompanyIds, $allValidUserIds, $dryRun, $limit)
    {
        // For role_has_permissions, we need to be more careful about super admin roles
        if ($tableName === 'role_has_permissions') {
            // Get all super admin user IDs dynamically
            $superAdminIds = DB::table('users')->where('type', 'super admin')->pluck('id')->toArray();
            $superAdminIdsList = empty($superAdminIds) ? [0] : array_merge([0], $superAdminIds);
            
            // Count only roles created by deleted companies, not system roles or super admin roles
            $countQuery = "
                SELECT COUNT(*) as count 
                FROM role_has_permissions rp 
                JOIN roles r ON rp.role_id = r.id 
                WHERE r.created_by NOT IN (" . implode(',', array_merge($existingCompanyIds, $superAdminIdsList)) . ") 
                AND r.created_by > 0
                AND r.created_by IS NOT NULL
            ";
            
            $orphanedCount = DB::select($countQuery)[0]->count ?? 0;
            
            if ($orphanedCount === 0) {
                $this->info("   ✅ No orphaned records");
                return ['orphaned' => 0, 'deleted' => 0];
            }
            
            $this->warn("   🗑️  Found {$orphanedCount} orphaned records (special query)");
            
            if ($dryRun) {
                // Show sample of orphaned roles
                $sampleQuery = "
                    SELECT r.created_by, r.name, COUNT(*) as permission_count
                    FROM role_has_permissions rp 
                    JOIN roles r ON rp.role_id = r.id 
                    WHERE r.created_by NOT IN (" . implode(',', array_merge($existingCompanyIds, $superAdminIdsList)) . ") 
                    AND r.created_by > 0
                    AND r.created_by IS NOT NULL
                    GROUP BY r.created_by, r.name 
                    LIMIT 5
                ";
                $sampleResults = DB::select($sampleQuery);
                $this->info("      Sample data: " . json_encode($sampleResults));
                
                $orphanedCompanies = [];
                foreach ($sampleResults as $sample) {
                    if (isset($sample->created_by) && $sample->created_by > 0) {
                        $orphanedCompanies[$sample->created_by] = $sample->permission_count;
                    }
                }
                
                return ['orphaned' => $orphanedCount, 'deleted' => 0, 'orphaned_companies' => $orphanedCompanies];
            }
            
            // Delete only roles from deleted companies
            $deleteQuery = "
                DELETE rp FROM role_has_permissions rp 
                JOIN roles r ON rp.role_id = r.id 
                WHERE r.created_by NOT IN (" . implode(',', array_merge($existingCompanyIds, $superAdminIdsList)) . ") 
                AND r.created_by > 0
                AND r.created_by IS NOT NULL
            ";
            $deleted = DB::delete($deleteQuery);
            $this->info("      Deleted {$deleted} records");
            
            return ['orphaned' => $orphanedCount, 'deleted' => $deleted];
        }
        
        // For other special tables, use the original logic but with company checking
        $deleteQuery = $config['special_query']['delete'];
        
        // Convert DELETE query to COUNT query properly
        if (preg_match('/DELETE\s+(\w+)\s+FROM/', $deleteQuery, $matches)) {
            $alias = $matches[1];
            $countQuery = preg_replace('/DELETE\s+\w+\s+FROM/', "SELECT COUNT(*) as count FROM", $deleteQuery);
        } else {
            $countQuery = preg_replace('/DELETE\s+FROM/', "SELECT COUNT(*) as count FROM", $deleteQuery);
        }
        
        $orphanedCount = DB::select($countQuery)[0]->count ?? 0;

        if ($orphanedCount === 0) {
            $this->info("   ✅ No orphaned records");
            return ['orphaned' => 0, 'deleted' => 0];
        }

        $this->warn("   🗑️  Found {$orphanedCount} orphaned records (special query)");

        $orphanedCompanies = [];
        if ($dryRun) {
            if (isset($config['special_query']['sample'])) {
                $sampleResults = DB::select($config['special_query']['sample']);
                $this->info("      Sample data: " . json_encode(array_slice($sampleResults, 0, 3)));
                
                // Try to extract company IDs from sample data for the summary
                foreach ($sampleResults as $sample) {
                    if (isset($sample->created_by) && $sample->created_by > 0) {
                        $count = isset($sample->permission_count) ? $sample->permission_count : 1;
                        $orphanedCompanies[$sample->created_by] = $count;
                    }
                }
            }
            return ['orphaned' => $orphanedCount, 'deleted' => 0, 'orphaned_companies' => $orphanedCompanies];
        }

        // Delete using special query
        $deleted = DB::delete($config['special_query']['delete']);
        $this->info("      Deleted {$deleted} records");

        return ['orphaned' => $orphanedCount, 'deleted' => $deleted, 'orphaned_companies' => $orphanedCompanies];
    }

    private function getTablesWithOrphanedData()
    {
        return [
            // High-priority tables (the big ones causing space issues)
            [
                'table' => 'joining_letters',
                'description' => 'Employee joining letters'
            ],
            [
                'table' => 'experience_certificates', 
                'description' => 'Employee experience certificates'
            ],
            [
                'table' => 'generate_offer_letters',
                'description' => 'Generated offer letters'
            ],
            [
                'table' => 'noc_certificates',
                'description' => 'No objection certificates'
            ],
            [
                'table' => 'chart_of_accounts',
                'description' => 'Company chart of accounts'
            ],
            [
                'table' => 'role_has_permissions',
                'special_query' => [
                    'delete' => "DELETE rp FROM role_has_permissions rp 
                                JOIN roles r ON rp.role_id = r.id 
                                LEFT JOIN users u ON r.created_by = u.id 
                                WHERE u.id IS NULL OR u.type != 'company'",
                    'sample' => "SELECT r.created_by, r.name, COUNT(*) as permission_count
                                FROM role_has_permissions rp 
                                JOIN roles r ON rp.role_id = r.id 
                                LEFT JOIN users u ON r.created_by = u.id 
                                WHERE u.id IS NULL OR u.type != 'company'
                                GROUP BY r.created_by, r.name LIMIT 5"
                ],
                'description' => 'Orphaned role permissions'
            ],
            [
                'table' => 'email_template_langs',
                'special_query' => [
                    'delete' => "DELETE etl FROM email_template_langs etl 
                                LEFT JOIN email_templates et ON etl.parent_id = et.id 
                                WHERE et.id IS NULL",
                    'sample' => "SELECT etl.parent_id, etl.lang, COUNT(*) as count
                                FROM email_template_langs etl 
                                LEFT JOIN email_templates et ON etl.parent_id = et.id 
                                WHERE et.id IS NULL 
                                GROUP BY etl.parent_id, etl.lang LIMIT 5"
                ],
                'description' => 'Orphaned email template languages'
            ],
            [
                'table' => 'notification_template_langs',
                'special_query' => [
                    'delete' => "DELETE ntl FROM notification_template_langs ntl 
                                LEFT JOIN notification_templates nt ON ntl.parent_id = nt.id 
                                WHERE nt.id IS NULL",
                    'sample' => "SELECT ntl.parent_id, ntl.lang, COUNT(*) as count
                                FROM notification_template_langs ntl 
                                LEFT JOIN notification_templates nt ON ntl.parent_id = nt.id 
                                WHERE nt.id IS NULL 
                                GROUP BY ntl.parent_id, ntl.lang LIMIT 5"
                ],
                'description' => 'Orphaned notification template languages'
            ],

            // Regular tables with direct company references
            ['table' => 'competencies'],
            ['table' => 'user_email_templates'],
            ['table' => 'contract_types'],
            ['table' => 'templates'],
            ['table' => 'users'],
            ['table' => 'custom_field_values'],
            ['table' => 'labels'],
            ['table' => 'performance_types'],
            ['table' => 'user_leads'],
            ['table' => 'company_payment_settings'],
            ['table' => 'departments'],
            ['table' => 'designations'],
            ['table' => 'documents'],
            ['table' => 'email_templates'],
            ['table' => 'employees'],
            ['table' => 'settings'],
            ['table' => 'landing_page_settings'],
            ['table' => 'branches'],
            ['table' => 'warehouses'],
            ['table' => 'bank_accounts'],
            ['table' => 'taxes'],
            ['table' => 'product_services'],
            ['table' => 'product_service_categories'],
            ['table' => 'product_service_units'],
            ['table' => 'award_types'],
            ['table' => 'allowance_options'],
            ['table' => 'deduction_options'],
            ['table' => 'loan_options'],
            ['table' => 'goal_types'],
            ['table' => 'job_categories'],
            ['table' => 'job_stages'],
            ['table' => 'lead_stages'],
            ['table' => 'leave_types'],
            ['table' => 'payslip_types'],
            ['table' => 'sources'],
            ['table' => 'stages'],
            ['table' => 'task_stages'],
            ['table' => 'termination_types'],
            ['table' => 'training_types'],
            ['table' => 'bug_statuses'],
            ['table' => 'pipelines'],
            ['table' => 'milestones'],
            ['table' => 'roles'],
            ['table' => 'notification_templates'],
            ['table' => 'deals'],
            ['table' => 'leads'],
            ['table' => 'projects'],
            ['table' => 'customers'],
            ['table' => 'venders'],
            ['table' => 'announcements'],
            ['table' => 'events'],
            ['table' => 'meetings'],
            ['table' => 'holidays'],
            ['table' => 'company_policies'],
            ['table' => 'complaints'],
            ['table' => 'jobs'],
            ['table' => 'custom_fields'],
            ['table' => 'activity_logs'],
            ['table' => 'ip_restricts'],
            ['table' => 'webhook_settings'],
        ];
    }

    private function getTableSize($tableName)
    {
        try {
            $result = DB::select("
                SELECT 
                    ROUND(((data_length + index_length) / 1024 / 1024), 2) AS size_mb,
                    table_rows
                FROM information_schema.tables 
                WHERE table_schema = DATABASE() AND table_name = ?
            ", [$tableName]);
            
            return $result[0] ?? (object)['size_mb' => 0, 'table_rows' => 0];
        } catch (\Exception $e) {
            return (object)['size_mb' => 0, 'table_rows' => 0];
        }
    }
}