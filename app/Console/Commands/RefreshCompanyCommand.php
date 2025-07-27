<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\CompanyRefreshService;
use App\Services\TemplateCompanyConfig;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class RefreshCompanyCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'company:refresh
                          {company_id? : The ID of the company to refresh (optional for some operations)}
                          {--template= : Force specific template company ID (optional)}
                          {--dry-run : Perform dry run without deleting old company}
                          {--preview : Show preview only without any changes}
                          {--cleanup= : Cleanup dry run company by ID}
                          {--validate : Validate template setup}
                          {--list-templates : List available template companies}';

    /**
     * The console command description.
     */
    protected $description = 'Refresh a company with latest template data while preserving user customizations';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        try {
            // Handle special operations that don't need company_id
            if ($this->option('validate')) {
                return $this->validateTemplateSetup();
            }

            if ($this->option('list-templates')) {
                return $this->listTemplateCompanies();
            }

            if ($this->option('cleanup')) {
                return $this->cleanupDryRun();
            }

            // For other operations, company_id is required
            $companyId = $this->argument('company_id');

            if (!$companyId) {
                $this->showUsageHelp();
                return 1;
            }

            // Validate company exists
            if (!$this->validateCompany($companyId)) {
                return 1;
            }

            $refreshService = new CompanyRefreshService();

            // Handle preview mode
            if ($this->option('preview')) {
                return $this->showPreview($refreshService, $companyId);
            }

            // Handle dry run
            if ($this->option('dry-run')) {
                return $this->performDryRun($refreshService, $companyId);
            }

            // Perform actual refresh
            return $this->performRefresh($refreshService, $companyId);

        } catch (\Exception $e) {
            $this->error('Command failed: ' . $e->getMessage());
            $this->error('Stack trace:');
            $this->line($e->getTraceAsString());
            return 1;
        }
    }

    /**
     * Show detailed usage help and workflow guide
     */
    private function showUsageHelp()
    {
        $this->error('Company ID is required for this operation.');

        $this->info("\n📖 COMPANY REFRESH WORKFLOW");
        $this->line("================================");

        $this->info("1. 🔍 Check template setup:");
        $this->line("   php artisan company:refresh --validate");
        $this->line("   → Validates that template companies exist and have correct currencies");

        $this->info("\n2. 📋 List available templates:");
        $this->line("   php artisan company:refresh --list-templates");
        $this->line("   → Shows all template companies and their currencies");

        $this->info("\n3. 👀 Preview what would happen:");
        $this->line("   php artisan company:refresh [OLD_COMPANY_ID] --preview");
        $this->line("   → Shows what data would be refreshed, no changes made");
        $this->line("   → Example: php artisan company:refresh 6 --preview");

        $this->info("\n4. 🧪 Test with dry run (SAFE):");
        $this->line("   php artisan company:refresh [OLD_COMPANY_ID] --dry-run");
        $this->line("   → Creates NEW test company with refreshed data");
        $this->line("   → Keeps OLD company unchanged for comparison");
        $this->line("   → Example: php artisan company:refresh 6 --dry-run");
        $this->line("   → Output: 'Created test company ID 25'");

        $this->info("\n5. 🔍 Examine both companies:");
        $this->line("   → Login to your app and compare:");
        $this->line("   → Company 6 (original) = old data with user customizations");
        $this->line("   → Company 25 (test) = refreshed data with latest template");

        $this->info("\n6. 🧹 Clean up test company:");
        $this->line("   php artisan company:refresh --cleanup=[TEST_COMPANY_ID]");
        $this->line("   → Deletes the test company created during dry run");
        $this->line("   → Example: php artisan company:refresh --cleanup=25");
        $this->line("   → This deletes company 25, keeps original company 6");

        $this->info("\n7. 🚀 Perform actual refresh (DESTRUCTIVE):");
        $this->line("   php artisan company:refresh [OLD_COMPANY_ID]");
        $this->line("   → Creates NEW company with refreshed data");
        $this->line("   → DELETES the old company permanently");
        $this->line("   → Example: php artisan company:refresh 6");
        $this->line("   → Result: Company 6 is replaced with refreshed version");

        $this->warn("\n⚠️  IMPORTANT NOTES:");
        $this->line("• OLD_COMPANY_ID = Company with user data you want to refresh");
        $this->line("• Template company is found automatically by currency matching");
        $this->line("• Always run --dry-run first to test before actual refresh");
        $this->line("• Actual refresh is destructive - old company is deleted!");

        $this->info("\n💡 QUICK START:");
        $this->line("  php artisan company:refresh --validate      # Check setup");
        $this->line("  php artisan company:refresh 6 --preview     # Preview");
        $this->line("  php artisan company:refresh 6 --dry-run     # Test");
        $this->line("  php artisan company:refresh 6               # Execute");
    }

    /**
     * Validate that the company exists and is valid for refresh
     */
    private function validateCompany($companyId)
    {
        $company = User::where('id', $companyId)->where('type', 'company')->first();

        if (!$company) {
            $this->error("Company with ID {$companyId} not found or is not a company type.");
            return false;
        }

        // Don't allow refreshing template companies
        $templateIds = array_keys(TemplateCompanyConfig::getTemplateCompanies());
        if (in_array($companyId, $templateIds)) {
            $this->error("Cannot refresh template company ID {$companyId}.");
            $this->info("Template companies: " . implode(', ', $templateIds));
            return false;
        }

        $this->info("✓ Company validated: {$company->name} (ID: {$companyId})");
        return true;
    }

    /**
     * Show preview of what would be refreshed
     */
    private function showPreview($refreshService, $companyId)
    {
        $this->info("🔍 Generating refresh preview for company {$companyId}...");

        $preview = $refreshService->previewRefresh($companyId);

        if (!$preview['success']) {
            $this->error("❌ " . $preview['error']);

            if (isset($preview['available_currencies'])) {
                $this->info("\n📋 Available template currencies:");
                foreach ($preview['available_currencies'] as $currency => $info) {
                    $this->line("  • {$currency}: {$info['template_name']} (ID: {$info['template_id']})");
                }
            }
            return 1;
        }

        // Display preview information
        $this->info("\n📊 REFRESH PREVIEW");
        $this->line("==================");
        $this->info("Old Company ID: {$preview['old_company_id']}");
        $this->info("Currency: {$preview['old_company_currency']}");
        $this->info("Template Company: {$preview['template_company_id']}");
        $this->info("Users to copy: {$preview['users_to_copy']}");

        // Master data summary
        if (!empty($preview['master_data_to_process'])) {
            $this->info("\n🗂️  Master Data to Process:");
            foreach ($preview['master_data_to_process'] as $table => $count) {
                $this->line("  • {$table}: {$count} records");
            }
        }

        // User data summary
        if (!empty($preview['user_data_to_copy'])) {
            $this->info("\n📋 User Data to Copy:");
            $totalRecords = 0;
            foreach ($preview['user_data_to_copy'] as $table => $count) {
                $this->line("  • {$table}: {$count} records");
                $totalRecords += $count;
            }
            $this->info("Total user records: {$totalRecords}");
        }

        // Recommendation
        $this->info("\n💡 Recommendation:");
        $this->line($preview['recommendation']);

        $this->info("\n🚀 Next steps:");
        $this->line("  php artisan company:refresh {$companyId} --dry-run    # Test the refresh (safe)");
        $this->line("  php artisan company:refresh {$companyId}              # Actual refresh (destructive)");

        $this->showWorkflowGuide($companyId);

        return 0;
    }

    /**
     * Perform dry run
     */
    private function performDryRun($refreshService, $companyId)
    {
        $this->info("🧪 Starting DRY RUN for company {$companyId}...");
        $this->info("(Old company will be preserved for testing)");

        $result = $refreshService->dryRun($companyId);

        if ($result['success']) {
            $this->info("\n✅ DRY RUN COMPLETED SUCCESSFULLY!");
            $this->displayResults($result);

            $this->info("\n🧹 Cleanup options:");
            $this->line("  php artisan company:refresh --cleanup={$result['new_company_id']}    # Delete test company {$result['new_company_id']}");
            $this->line("  php artisan company:refresh {$companyId}                            # Perform actual refresh");

            $this->showWorkflowGuide($companyId, $result['new_company_id']);
        } else {
            $this->error("❌ Dry run failed!");
        }

        return $result['success'] ? 0 : 1;
    }

    /**
     * Perform actual refresh
     */
    private function performRefresh($refreshService, $companyId)
    {
        // Safety confirmation
        if (!$this->confirm("⚠️  This will REPLACE company {$companyId} with refreshed data. Continue?")) {
            $this->info("Operation cancelled.");
            return 0;
        }

        $this->info("🔄 Starting ACTUAL REFRESH for company {$companyId}...");
        $this->info("(Old company will be deleted after successful refresh)");

        $result = $refreshService->refreshCompany($companyId);

        if ($result['success']) {
            $this->info("\n🎉 REFRESH COMPLETED SUCCESSFULLY!");
            $this->displayResults($result);
        } else {
            $this->error("❌ Refresh failed!");
        }

        return $result['success'] ? 0 : 1;
    }

    /**
     * Display refresh results
     */
    private function displayResults($result)
    {
        $this->info("\n📈 RESULTS SUMMARY");
        $this->line("==================");
        $this->info("Template Used: {$result['template_company_id']}");
        $this->info("Currency: {$result['currency_matched']}");
        $this->info("New Company ID: {$result['new_company_id']}");

        if (!$result['is_dry_run']) {
            $this->info("Old Company ID: {$result['old_company_id']} (DELETED)");
        } else {
            $this->info("Old Company ID: {$result['old_company_id']} (PRESERVED)");
        }

        // Summary stats
        $summary = $result['summary'];
        $this->info("\n📊 Processing Summary:");
        $this->line("  • Tables processed: {$summary['tables_processed']}");
        $this->line("  • Records copied: {$summary['records_copied']}");
        $this->line("  • Users copied: {$summary['users_copied']}");
        $this->line("  • Conflicts resolved: {$summary['conflicts_resolved']}");

        // Show some transfer log details
        if (!empty($result['transfer_log'])) {
            $this->info("\n🔍 Recent Operations:");
            $logCount = 0;
            foreach (array_reverse($result['transfer_log']) as $log) {
                if ($logCount >= 5) break; // Show last 5 operations

                if ($log['action'] === 'copied') {
                    $this->line("  • Copied {$log['count']} records from {$log['table']}");
                } elseif ($log['action'] === 'conflict_resolved') {
                    $this->line("  • Resolved conflict in {$log['table']}.{$log['field']}: {$log['resolution']}");
                } elseif ($log['action'] === 'copied_user_added') {
                    $this->line("  • Added user record to {$log['table']} (ID: {$log['old_id']} → {$log['new_id']})");
                }
                $logCount++;
            }
        }

        $this->info("\nCompleted at: {$result['completed_at']}");
    }

    /**
     * Show workflow guide after operations
     */
    private function showWorkflowGuide($companyId, $testCompanyId = null)
    {
        $this->info("\n📖 COMPLETE WORKFLOW GUIDE");
        $this->line("==========================");

        $this->info("1. 🔍 Validate setup:");
        $this->line("   php artisan company:refresh --validate");

        $this->info("\n2. 👀 Preview refresh:");
        $this->line("   php artisan company:refresh {$companyId} --preview");
        $this->line("   → Shows what would be refreshed for company {$companyId}");

        $this->info("\n3. 🧪 Test with dry run:");
        $this->line("   php artisan company:refresh {$companyId} --dry-run");
        $this->line("   → Creates test company, keeps company {$companyId} unchanged");

        if ($testCompanyId) {
            $this->info("\n4. 🔍 Current state:");
            $this->line("   → Company {$companyId} (original) = unchanged user data");
            $this->line("   → Company {$testCompanyId} (test) = refreshed with latest template");
            $this->line("   → Login to your app and compare both companies");

            $this->info("\n5. 🧹 Clean up test:");
            $this->line("   php artisan company:refresh --cleanup={$testCompanyId}");
            $this->line("   → Deletes test company {$testCompanyId}, keeps original {$companyId}");
        } else {
            $this->info("\n4. 🔍 Examine results:");
            $this->line("   → Login to your app and check the test company");
            $this->line("   → Verify all data looks correct");
        }

        $this->info("\n6. 🚀 Actual refresh:");
        $this->line("   php artisan company:refresh {$companyId}");
        $this->line("   → REPLACES company {$companyId} with refreshed version");
        $this->line("   → Original company {$companyId} will be DELETED permanently");
    }

    /**
     * Cleanup dry run company
     */
    private function cleanupDryRun()
    {
        $cleanupId = $this->option('cleanup');

        if (!$cleanupId) {
            $this->error("Please provide company ID to cleanup: --cleanup=123");
            return 1;
        }

        $this->info("🧹 Cleaning up dry run company {$cleanupId}...");

        $refreshService = new CompanyRefreshService();
        $result = $refreshService->cleanupDryRun(['new_company_id' => $cleanupId]);

        if ($result['success']) {
            $this->info("✅ " . $result['message']);
        } else {
            $this->error("❌ " . $result['error']);
        }

        return $result['success'] ? 0 : 1;
    }

    /**
     * Validate template setup
     */
    private function validateTemplateSetup()
    {
        $this->info("🔍 Validating template company setup...");

        $validation = TemplateCompanyConfig::validateTemplateSetup();

        if ($validation['valid']) {
            $this->info("✅ Template setup is valid!");
            $this->info("Found {$validation['template_count']} template companies.");
        } else {
            $this->error("❌ Template setup has issues:");
            foreach ($validation['issues'] as $issue) {
                $this->line("  • {$issue}");
            }
        }

        // Show template companies with ACTUAL reality
        $this->info("\n📋 Configured Templates:");
        $templates = TemplateCompanyConfig::getTemplateCompanies();
        foreach ($templates as $templateId => $configCurrency) {
            $company = User::where('type', 'company')->where('id', $templateId)->first();

            if (!$company) {
                $status = "✗ MISSING";
                $name = "Not found";
                $actualCurrency = "N/A";
            } else {
                // Get ACTUAL currency from database
                $actualCurrency = DB::table('settings')
                    ->where('created_by', $templateId)
                    ->where('name', 'site_currency')
                    ->value('value') ?: 'No currency set';

                $name = $company->name;

                // Show status based on currency match
                if ($actualCurrency === $configCurrency) {
                    $status = "✓";
                } else {
                    $status = "⚠️  MISMATCH";
                }
            }

            // Show both config and actual currency
            if ($actualCurrency === $configCurrency || $actualCurrency === 'N/A') {
                $this->line("  • ID {$templateId}: {$configCurrency} - {$name} {$status}");
            } else {
                $this->line("  • ID {$templateId}: {$configCurrency} (actually {$actualCurrency}) - {$name} {$status}");
            }
        }

        return $validation['valid'] ? 0 : 1;
    }

    /**
     * List available template companies
     */
    private function listTemplateCompanies()
    {
        $this->info("📋 Available Template Companies:");

        $currencies = TemplateCompanyConfig::getAvailableCurrencies();

        if (empty($currencies)) {
            $this->warn("No template companies found!");
            $this->info("Run: php artisan company:refresh --validate");
            return 1;
        }

        foreach ($currencies as $currency => $info) {
            $this->line("🏢 {$currency}");
            $this->line("   Template ID: {$info['template_id']}");
            $this->line("   Company: {$info['template_name']}");
            $this->line("");
        }

        return 0;
    }
}
