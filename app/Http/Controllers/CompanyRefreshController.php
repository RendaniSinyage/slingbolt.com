<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\CompanyRefreshService;
use App\Models\User;

class CompanyRefreshController extends Controller
{
    protected $refreshService;

    public function __construct(CompanyRefreshService $refreshService)
    {
        $this->refreshService = $refreshService;
    }

    public function refresh($id, Request $request)
    {
        \Log::info("CompanyRefreshController: refresh method called with ID: {$id}");
        \Log::info("CompanyRefreshController: Request parameters: " . json_encode($request->all()));
        \Log::info("CompanyRefreshController: Query parameters: " . json_encode($request->query()));
        \Log::info("CompanyRefreshController: Auth user type: " . auth()->user()->type);

        if (auth()->user()->type !== 'super admin') {
            \Log::warning("CompanyRefreshController: Unauthorized access attempt by user: " . auth()->user()->id);
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $dryRun = $request->query('dry', 'true') === 'true';
        \Log::info("CompanyRefreshController: Dry run mode: " . ($dryRun ? 'true' : 'false'));

        $company = User::where('id', $id)->where('type', 'company')->first();

        if (!$company) {
            \Log::error("CompanyRefreshController: Company not found with ID: {$id}");
            return response()->json(['error' => 'Company not found.'], 404);
        }

        \Log::info("CompanyRefreshController: Found company: {$company->name} (ID: {$company->id})");

        try {
            if ($dryRun) {
                \Log::info("CompanyRefreshController: Starting dry run for company {$id}");
                $this->refreshService->dryRun($id);
                \Log::info("CompanyRefreshController: Dry run completed successfully for company {$id}");

                $response = response()->json(['success' => 'Dry run completed successfully.'], 200);
                \Log::info("CompanyRefreshController: Returning dry run success response");
                return $response;
            } else {
                \Log::info("CompanyRefreshController: Starting actual refresh for company {$id}");
                $this->refreshService->refreshCompany($id);
                \Log::info("CompanyRefreshController: Actual refresh completed successfully for company {$id}");

                $response = response()->json(['success' => 'Company refreshed successfully.'], 200);
                \Log::info("CompanyRefreshController: Returning actual refresh success response");
                return $response;
            }
        } catch (\Exception $e) {
            \Log::error("CompanyRefreshController: Exception occurred for company {$id}: " . $e->getMessage());
            \Log::error("CompanyRefreshController: Exception trace: " . $e->getTraceAsString());
            return response()->json(['error' => 'Failed to refresh company. Please try again.'], 500);
        }
    }
}
