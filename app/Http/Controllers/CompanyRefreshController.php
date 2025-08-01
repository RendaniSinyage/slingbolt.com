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
        if (auth()->user()->type !== 'super admin') {
            abort(403, 'Unauthorized');
        }

        $dryRun = $request->query('dry', true);
        $company = User::where('id', $id)->where('type', 'company')->first();
        if (!$company) {
            return response()->json(['error' => 'Company not found.'], 404);
        }

        if ($dryRun) {
            $this->refreshService->dryRun($id);
            return response()->json(['success' => 'Dry run completed.']);
        } else {
            $this->refreshService->refreshCompany($id);
            return response()->json(['success' => 'Company refreshed.']);
        }
    }
}