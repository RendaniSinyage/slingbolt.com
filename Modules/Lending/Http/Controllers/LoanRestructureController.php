<?php

namespace Modules\Lending\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Lending\Entities\Loan;
use Modules\Lending\Services\LoanService;

class LoanRestructureController extends Controller
{
    protected $loanService;

    public function __construct(LoanService $loanService)
    {
        $this->loanService = $loanService;
    }

    public function create(Loan $loan)
    {
        return view('lending::restructures.create', compact('loan'));
    }

    public function store(Request $request, Loan $loan)
    {
        $request->validate([
            'restructure_date' => 'required|date',
            'new_rate_of_interest' => 'required|numeric',
            'new_repayment_periods' => 'required|integer',
        ]);

        try {
            $this->loanService->restructureLoan($loan, $request->all());
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error restructuring loan: ' . $e->getMessage())->withInput();
        }

        return redirect()->route('lending.loans.show', $loan->id)->with('success', 'Loan restructured successfully.');
    }
}
