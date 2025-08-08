<?php

namespace Modules\Lending\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Lending\Entities\Loan;
use Modules\Lending\Services\LoanService;

class LoanRepaymentController extends Controller
{
    protected $loanService;

    public function __construct(LoanService $loanService)
    {
        $this->loanService = $loanService;
    }

    public function create(Loan $loan)
    {
        return view('lending::repayments.create', compact('loan'));
    }

    public function store(Request $request, Loan $loan)
    {
        $request->validate([
            'amount_paid' => 'required|numeric|min:0.01',
            'payment_date' => 'required|date',
        ]);

        try {
            $this->loanService->processRepayment($loan, $request->all());
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error processing repayment: ' . $e->getMessage())->withInput();
        }

        return redirect()->route('lending.loans.show', $loan->id)->with('success', 'Repayment recorded successfully.');
    }
}
