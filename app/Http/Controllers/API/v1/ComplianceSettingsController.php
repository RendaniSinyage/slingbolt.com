<?php

namespace App\Http\Controllers\API\v1;

use App\Http\Controllers\Controller;
use App\Models\ComplianceSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ComplianceSettingsController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        if ($user->type == 'super admin') {
            $settings = ComplianceSetting::whereNull('company_id')->first();
            return response()->json($settings);
        }

        return response()->json(['error' => 'Permission denied.'], 403);
    }

    public function store(Request $request)
    {
        $user = Auth::user();
        if ($user->type != 'super admin') {
            return response()->json(['error' => 'Permission denied.'], 403);
        }

        $validatedData = $request->validate([
            'max_interest_rate' => 'nullable|numeric',
            'max_initiation_fee' => 'nullable|numeric',
            'max_monthly_service_fee' => 'nullable|numeric',
        ]);

        $settings = ComplianceSetting::updateOrCreate(
            ['company_id' => null],
            $validatedData
        );

        return response()->json(['message' => 'Default compliance settings saved successfully.', 'data' => $settings]);
    }
}
