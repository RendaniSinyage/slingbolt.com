<?php

namespace Modules\Lending\Services;

use Modules\Lending\Entities\LoanApplication;
use Modules\Lending\Helpers\ComplianceHelper;

class EligibilityService
{
    public function checkEligibility(LoanApplication $application)
    {
        $created_by = $application->created_by;

        // Fetch compliance settings
        $min_income = ComplianceHelper::getSetting('min_income', $created_by) ?? 5000;
        $max_dti_ratio = ComplianceHelper::getSetting('max_dti_ratio', $created_by) ?? 0.40;

        // Reversed Debit Orders Check
        if ($application->reversed_debit_orders_last_3_months >= 1) {
            $application->recommendation = 'ineligible';
            $application->recommendation_reason = 'Reversed debit order found.';
            $application->save();
            return;
        }

        // Failed Debit Orders Check
        if (is_array($application->failed_debit_orders_last_3_months)) {
            foreach ($application->failed_debit_orders_last_3_months as $month => $count) {
                if ($count >= 2) {
                    $application->recommendation = 'needs_review';
                    $application->recommendation_reason = '2 or more failed debit orders in a month.';
                    $application->save();
                    return;
                }
            }
        }

        // Income Check
        if ($application->monthly_income < $min_income) {
            $application->recommendation = 'ineligible';
            $application->recommendation_reason = 'Income below minimum requirement.';
            $application->save();
            return;
        }

        // DTI Check
        if ($application->monthly_income > 0) {
            $dti_ratio = $application->monthly_debt / $application->monthly_income;
            if ($dti_ratio > $max_dti_ratio) {
                $application->recommendation = 'ineligible';
                $application->recommendation_reason = 'DTI ratio exceeds maximum limit.';
                $application->save();
                return;
            }
        }

        // All checks passed
        $application->recommendation = 'eligible';
        $application->recommendation_reason = 'All eligibility criteria met.';
        $application->save();
    }
}
