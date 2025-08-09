<?php

namespace Modules\Lending\Helpers;

use App\Models\ComplianceSetting;

class ComplianceHelper
{
    /**
     * Get a compliance setting for a specific company, falling back to the system default.
     *
     * @param string $key The setting key to retrieve (e.g., 'max_interest_rate').
     * @param int|null $created_by The ID of the creator.
     * @return mixed|null The setting value or null if not found.
     */
    public static function getSetting(string $key, ?int $created_by)
    {
        // Try to find a company-specific setting first
        if ($created_by) {
            $companySetting = ComplianceSetting::where('created_by', $created_by)->first();
            // Use the company setting only if it's explicitly set (not null)
            if ($companySetting && !is_null($companySetting->$key)) {
                return $companySetting->$key;
            }
        }

        // If no company-specific override, fall back to the default system setting
        $defaultSetting = ComplianceSetting::whereNull('created_by')->first();
        if ($defaultSetting && !is_null($defaultSetting->$key)) {
            return $defaultSetting->$key;
        }

        return null;
    }
}
