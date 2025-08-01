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
                          {--dry-run : Perform dry run without making permanent changes}
                          {--preview : Show preview only without any changes}
                          {--validate : Validate template setup}
                          {--list-templates : List available template companies}';

    /**
     * The console command description.
     */
    protected $description = 'Refresh a company with latest template data while preserving user customizations (IN-PLACE)';

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

        $this->info("\n📖 COMPANY REFRESH WORKFLOW (IN-PLACE)");
        $this->line("======================================");

        $this->info("1. 🔍 Check template setup:");
        $this->line("   php artisan company:refresh --validate");
        $this->line("   → Validates that template companies exist and have correct currencies");

        $this->info("\n2. 📋 List available templates:");
        $this->line("   php artisan company:refresh --list-templates");
        $this->line("   → Shows all template companies and their currencies");

        $this->info("\n3. 👀 Preview what would happen:");
        $this->line("   php artisan company:refresh [COMPANY_ID] --preview");
        $this->line("   → Shows what data would be refreshed, no changes made");
        $this->line("   → Example: php artisan company:refresh 27 --preview");

        $this->info("\n4. 🧪 Test with dry run (SAFE):");
        $this->line("   php artisan company:refresh [COMPANY_ID] --dry-run");
        $this->line("   → Analyzes changes without applying them permanently");
        $this->line("   → Shows exactly what would be changed");
        $this->line("   → Example: php artisan company:refresh 27 --dry-run");

        $this->info("\n5. 🚀 Perform actual refresh (IN-PLACE):");
        $this->line("   php artisan company:refresh [COMPANY_ID]");
        $this->line("   → Refreshes the company directly (no new company created)");
        $this->line("   → Merges template data with existing data");
        $this->line("   → Example: php artisan company:refresh 27");

        $this->warn("\n⚠️  IMPORTANT NOTES:");
        $this->line("• This is IN-PLACE refresh - no duplicate companies created");
        $this->line("• All business data (invoices, employees, etc.) is preserved");
        $this->line("• Template data is merged with existing configurations");
        $this->line("• Dry run shows changes without applying them");

        $this->info("\n💡 QUICK START:");
        $this->line("  php artisan company:refresh --validate      # Check setup");
        $this->line("  php artisan company:refresh 27 --preview    # Preview");
        $this->line("  php artisan company:refresh 27 --dry-run    # Test");
        $this->line("  php artisan company:refresh 27              # Execute");
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
                foreach ($preview['available_currencies'] as $currency) {
                    $this->line("  • {$currency}");
                }
            }
            return 1;
        }

        // Display preview information
        $this->info("\n📊 REFRESH PREVIEW");
        $this->line("==================");
        $this->info("Company ID: {$preview['company_id']}");
        $this->info("Currency: {$preview['company_currency']}");
        $this->info("Template Company: {$preview['template_company_name']} (ID: {$preview['template_company_id']})");

        // Master data summary
        if (!empty($preview['master_data_analysis'])) {
            $this->info("\n🗂️  Master Data Analysis:");
            foreach ($preview['master_data_analysis'] as $table => $analysis) {
                $this->line("  • {$table}: {$analysis['user_records']} user, {$analysis['template_records']} template ({$analysis['strategy']})");
            }
        }

        // Settings analysis
        if (!empty($preview['settings_analysis'])) {
            $settings = $preview['settings_analysis'];
            $this->info("\n⚙️  Settings Analysis:");
            $this->line("  • Will be preserved: " . count($settings['will_be_preserved']));
            $this->line("  • Will be updated: " . count($settings['will_be_updated']));
            $this->line("  • Will be added: " . count($settings['will_be_added']));
            $this->line("  • Will be skipped (superadmin): " . count($settings['will_be_skipped']));
        }

        // Recommendation
        $this->info("\n💡 Recommendation:");
        $this->line($preview['recommendation']);

        $this->info("\n🚀 Next steps:");
        $this->line("  php artisan company:refresh {$companyId} --dry-run    # Test the changes (safe)");
        $this->line("  php artisan company:refresh {$companyId}              # Apply the changes");

        return 0;
    }

    /**
     * Perform dry run
     */
    private function performDryRun($refreshService, $companyId)
    {
        $this->info("🧪 Starting DRY RUN for company {$companyId}...");
        $this->info("(No permanent changes will be made)");

        $result = $refreshService->dryRun($companyId);

        if ($result['success']) {
            $this->info("\n✅ DRY RUN COMPLETED SUCCESSFULLY!");
            $this->displayResults($result);

            $this->info("\n🚀 Ready for actual refresh:");
            $this->line("  php artisan company:refresh {$companyId}    # Apply these changes permanently");
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
        if (!$this->confirm("⚠️  This will REFRESH company {$companyId} with template data. Continue?")) {
            $this->info("Operation cancelled.");
            return 0;
        }

        $this->info("🔄 Starting ACTUAL REFRESH for company {$companyId}...");
        $this->info("(Changes will be applied permanently)");

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
     * Display refresh results - UPDATED for new response format
     */
    private function displayResults($result)
    {
        $this->info("\n📈 RESULTS SUMMARY");
        $this->line("==================");
        $this->info("Template Used: {$result['template_company_id']}");
        $this->info("Currency: {$result['currency']}");
        $this->info("Company ID: {$result['company_id']}"); // Same company, refreshed in-place
        $this->info("Operation: " . ($result['is_dry_run'] ? 'DRY RUN (no changes applied)' : 'ACTUAL REFRESH (changes applied)'));

        // Summary stats
        $summary = $result['summary'];
        $this->info("\n📊 Processing Summary:");
        $this->line("  • Tables processed: {$summary['tables_processed']}");
        $this->line("  • Records added: {$summary['records_added']}");
        $this->line("  • Records updated: {$summary['records_updated']}");
        $this->line("  • Records preserved: {$summary['records_preserved']}");
        
        if (isset($summary['settings_preserved'])) {
            $this->line("  • Settings preserved: {$summary['settings_preserved']}");
            $this->line("  • Settings updated: {$summary['settings_updated']}");
            $this->line("  • Settings added: {$summary['settings_added']}");
            $this->line("  • Settings skipped: {$summary['settings_skipped']}");
        }

        // Show some recent operations
        if (!empty($result['transfer_log'])) {
            $this->info("\n🔍 Recent Operations:");
            $logCount = 0;
            foreach (array_reverse($result['transfer_log']) as $log) {
                if ($logCount >= 8) break; // Show last 8 operations

                $details = $log['details'] ?? 'N/A';
                $dryRunIndicator = ($log['dry_run'] ?? false) ? ' (DRY RUN)' : '';
                
                switch ($log['action']) {
                    case 'added_from_template':
                        $this->line("  • ✅ Added: {$details}{$dryRunIndicator}");
                        break;
                    case 'updated_with_template':
                        $this->line("  • 🔄 Updated: {$details}{$dryRunIndicator}");
                        break;
                    case 'kept_user_version':
                        $this->line("  • 👤 Kept User: {$details}{$dryRunIndicator}");
                        break;
                    case 'setting_preserved':
                        $this->line("  • 🛡️  Preserved: {$details}{$dryRunIndicator}");
                        break;
                    case 'setting_updated_from_template':
                        $this->line("  • ⚙️  Updated Setting: {$details}{$dryRunIndicator}");
                        break;
                    case 'setting_skipped_superadmin':
                        $this->line("  • ⏭️  Skipped: {$details}{$dryRunIndicator}");
                        break;
                    default:
                        $this->line("  • ℹ️  {$log['action']}: {$details}{$dryRunIndicator}");
                }
                $logCount++;
            }
        }

        $this->info("\nCompleted at: {$result['completed_at']}");
        
        if ($result['is_dry_run']) {
            $this->warn("\n🧪 This was a DRY RUN - no permanent changes were made!");
            $this->info("The analysis above shows what WOULD happen during actual refresh.");
        }
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