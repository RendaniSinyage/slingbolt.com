<?php

namespace Modules\Lending\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Lending\Entities\LoanSecurityAssignment;
use Modules\Lending\Services\LoanService;

class LoanSecurityReleaseController extends Controller
{
    protected $loanService;

    public function __construct(LoanService $loanService)
    {
        $this->loanService = $loanService;
    }

    public function create(LoanSecurityAssignment $assignment)
    {
        $assignment->load('pledges.security');
        return view('lending::securities.release.create', compact('assignment'));
    }

    public function store(Request $request, LoanSecurityAssignment $assignment)
    {
        // For now, we assume a full release. A more advanced implementation
        // would allow partial releases.

        try {
            $this->loanService->releaseSecurities($assignment);
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error releasing securities: ' . $e->getMessage())->withInput();
        }

        // Redirect back to the loan show page
        return redirect()->route('lending.loans.show', $assignment->assignable_id)->with('success', 'Securities released successfully.');
    }
}
