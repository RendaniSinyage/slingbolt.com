<?php

namespace Modules\Lending\Helpers;

use App\Models\ComplianceSetting;

class ComplianceHelper
{
    /**
     * Get a compliance setting for a specific company, falling back to the system default.
     *
     * @param string $key The setting key to retrieve (e.g., 'max_interest_rate').
     * @param int|null $company_id The ID of the company.
     * @return mixed|null The setting value or null if not found.
     */
    public static function getSetting(string $key, ?int $company_id)
    {
        // Try to find a company-specific setting first
        if ($company_id) {
            $companySetting = ComplianceSetting::where('company_id', $company_id)->first();
            // Use the company setting only if it's explicitly set (not null)
            if ($companySetting && !is_null($companySetting->$key)) {
                return $companySetting->$key;
            }
        }

        // If no company-specific override, fall back to the default system setting
        $defaultSetting = ComplianceSetting::whereNull('company_id')->first();
        if ($defaultSetting && !is_null($defaultSetting->$key)) {
            return $defaultSetting->$key;
        }

        return null;
    }
}
