<?php

namespace Modules\Lending\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Lending\Entities\Loan;
use Modules\Lending\Services\LoanService;

class LoanWriteOffController extends Controller
{
    protected $loanService;

    public function __construct(LoanService $loanService)
    {
        $this->loanService = $loanService;
    }

    public function create(Loan $loan)
    {
        return view('lending::write_offs.create', compact('loan'));
    }

    public function store(Request $request, Loan $loan)
    {
        $request->validate([
            'write_off_date' => 'required|date',
        ]);

        try {
            $this->loanService->writeOffLoan($loan, $request->all());
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error writing off loan: ' . $e->getMessage())->withInput();
        }

        return redirect()->route('lending.loans.show', $loan->id)->with('success', 'Loan written off successfully.');
    }
}
