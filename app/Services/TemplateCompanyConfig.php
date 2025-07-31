<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use App\Models\User;

class TemplateCompanyConfig
{
    /**
     * Get list of template companies with their currencies
     * Configure this based on your setup
     */
    public static function getTemplateCompanies()
    {
        // TODO: Configure your template companies here
        // Each template company should have a different currency set up

        return [
                    2 => 'ZAR',  // South African template (your main/existing one)
                   // 3 => 'EUR',  // European template
                  //  4 => 'GBP',  // UK template
                   // 5 => 'USD',  // US template
                  //  6 => 'CAD',  // Canadian template
                   // 7 => 'AUD',  // Australian template
];

        // Alternative approach - dynamic discovery:
        // return self::discoverTemplateCompanies();
    }

    /**
     * Get default template company (fallback)
     */
    public static function getDefaultTemplateCompany()
    {
        // For South Africa, you might want ZAR as default instead of USD
        return 2; // ZAR template company

        // Or keep USD as global default:
        // return 2; // USD template company
    }

    /**
     * Get default currency for new companies
     */
    public static function getDefaultCurrency()
    {
        // Since you're in South Africa, ZAR might be better default
        return 'ZAR';

        // Or keep USD as global default:
        // return 'USD';
    }

    /**
     * Find template company by currency
     */
    public static function findTemplateByCurrency($currency)
    {
        $templates = self::getTemplateCompanies();

        foreach ($templates as $templateId => $templateCurrency) {
            if ($templateCurrency === $currency) {
                // Verify this template company actually exists AND has correct currency
                $company = User::where('type', 'company')
                    ->where('id', $templateId)
                    ->first();

                if ($company) {
                    // ✅ CRITICAL FIX: Check actual database currency
                    $actualCurrency = DB::table('settings')
                        ->where('created_by', $templateId)
                        ->where('name', 'site_currency')
                        ->value('value');

                    // Only return if actual currency matches requested currency
                    if ($actualCurrency === $currency) {
                        return $templateId;
                    } else {
                        \Log::warning("Template {$templateId} config says '{$currency}' but database has '{$actualCurrency}' - skipping");
                    }
                }
            }
        }

        return null;
    }

    /**
     * Get available currencies from template companies - FIXED VERSION
     */
    public static function getAvailableCurrencies()
    {
        $templates = self::getTemplateCompanies();
        $currencies = [];

        foreach ($templates as $templateId => $configCurrency) {
            // Verify template exists and get company name
            $company = User::where('type', 'company')
                ->where('id', $templateId)
                ->first(['id', 'name']);

            if ($company) {
                // ✅ Get ACTUAL currency from database
                $actualCurrency = DB::table('settings')
                    ->where('created_by', $templateId)
                    ->where('name', 'site_currency')
                    ->value('value');

                if ($actualCurrency) {
                    // ✅ Use ACTUAL currency as key
                    $currencies[$actualCurrency] = [
                        'template_id' => $templateId,
                        'template_name' => $company->name,
                        'currency' => $actualCurrency,  // ✅ Store actual currency
                        'config_currency' => $configCurrency  // Optional: keep config for reference
                    ];
                }
            }
        }

        return $currencies;
    }

    /**
     * Dynamic discovery of template companies (alternative approach)
     * Discovers companies with specific naming pattern or flag
     */
    private static function discoverTemplateCompanies()
    {
        $templates = [];

        // Option 1: Companies with 'Template' in name
        $templateCompanies = User::where('type', 'company')
            ->where('name', 'LIKE', '%Template%')
            ->get(['id', 'name']);

        foreach ($templateCompanies as $company) {
            // Get currency from company settings
            $currency = DB::table('settings')
                ->where('created_by', $company->id)
                ->where('name', 'site_currency')
                ->value('value');

            if ($currency) {
                $templates[$company->id] = $currency;
            }
        }

        // Option 2: Companies with is_template flag (if you add this column)
        /*
        $templateCompanies = User::where('type', 'company')
            ->where('is_template', 1)
            ->get(['id']);

        foreach ($templateCompanies as $company) {
            $currency = DB::table('settings')
                ->where('created_by', $company->id)
                ->where('name', 'site_currency')
                ->value('value');

            if ($currency) {
                $templates[$company->id] = $currency;
            }
        }
        */

        return $templates;
    }

    /**
     * Validate template company setup
     * Call this to check if all template companies are properly configured
     */
    public static function validateTemplateSetup()
    {
        $templates = self::getTemplateCompanies();
        $issues = [];

        foreach ($templates as $templateId => $expectedCurrency) {
            // Check if company exists
            $company = User::where('type', 'company')
                ->where('id', $templateId)
                ->first();

            if (!$company) {
                $issues[] = "Template company ID {$templateId} does not exist";
                continue;
            }

            // Check if currency is set correctly
            $actualCurrency = DB::table('settings')
                ->where('created_by', $templateId)
                ->where('name', 'site_currency')
                ->value('value');

            if ($actualCurrency !== $expectedCurrency) {
                $issues[] = "Template company {$templateId} ({$company->name}) has currency '{$actualCurrency}' but expected '{$expectedCurrency}'";
            }
        }

        return [
            'valid' => empty($issues),
            'issues' => $issues,
            'template_count' => count($templates)
        ];
    }

    /**
     * Setup helper - creates currency setting for a template company
     */
    public static function setCurrencyForTemplate($templateId, $currency)
    {
        // Verify company exists
        $company = User::where('type', 'company')
            ->where('id', $templateId)
            ->first();

        if (!$company) {
            throw new \Exception("Template company ID {$templateId} does not exist");
        }

        // Set or update currency setting
        DB::table('settings')->updateOrInsert(
            [
                'created_by' => $templateId,
                'name' => 'site_currency'
            ],
            [
                'value' => $currency,
                'created_at' => now(),
                'updated_at' => now()
            ]
        );

        return true;
    }
}
