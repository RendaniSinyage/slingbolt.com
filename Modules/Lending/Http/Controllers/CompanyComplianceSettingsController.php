<?php

namespace Modules\Lending\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use App\Models\ComplianceSetting;

class CompanyComplianceSettingsController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        if ($user->type != 'company') {
            return redirect()->back()->with('error', 'Permission denied.');
        }

        $settings = ComplianceSetting::where('company_id', $user->id)->first();
        $defaults = ComplianceSetting::whereNull('company_id')->first();

        return view('lending::settings.company_compliance', compact('settings', 'defaults'));
    }

    public function store(Request $request)
    {
        $user = auth()->user();
        if ($user->type != 'company') {
            return redirect()->back()->with('error', 'Permission denied.');
        }

        ComplianceSetting::updateOrCreate(
            ['company_id' => $user->id],
            [
                'max_interest_rate' => $request->max_interest_rate,
                'max_initiation_fee' => $request->max_initiation_fee,
                'max_monthly_service_fee' => $request->max_monthly_service_fee,
            ]
        );

        return redirect()->back()->with('success', 'Compliance settings saved successfully.');
    }
}
