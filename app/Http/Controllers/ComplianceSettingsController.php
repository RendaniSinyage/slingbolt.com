<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ComplianceSetting;
use App\Models\Utility;

class ComplianceSettingsController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        if ($user->type == 'super admin') {
            $settings = ComplianceSetting::whereNull('company_id')->first();
            // Assuming a view exists at 'settings.compliance' for super admin
            return view('settings.compliance', compact('settings'));
        }

        // Company admin part will be handled by a different controller in the module
        // to keep concerns separate. This controller is for super admin only.

        return redirect()->back()->with('error', 'Permission denied.');
    }

    public function store(Request $request)
    {
        if (auth()->user()->type != 'super admin') {
            return redirect()->back()->with('error', 'Permission denied.');
        }

        ComplianceSetting::updateOrCreate(
            ['company_id' => null],
            [
                'max_interest_rate' => $request->max_interest_rate,
                'max_initiation_fee' => $request->max_initiation_fee,
                'max_monthly_service_fee' => $request->max_monthly_service_fee,
            ]
        );

        return redirect()->back()->with('success', 'Default compliance settings saved successfully.');
    }
}
