<?php

namespace Modules\LendingTmp\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\LendingTmp\Entities\Loan;
use Modules\LendingTmp\Services\LoanService;

class LoanRepaymentController extends Controller
{
    protected $loanService;

    public function __construct(LoanService $loanService)
    {
        $this->loanService = $loanService;
    }

    public function create(Loan $loan)
    {
        return view('lendingtmp::repayments.create', compact('loan'));
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

        return redirect()->route('lendingtmp.loans.show', $loan->id)->with('success', 'Repayment recorded successfully.');
    }
}
