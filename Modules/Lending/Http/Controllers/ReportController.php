<?php

namespace Modules\Lending\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Lending\Entities\Loan;
use Modules\Lending\Entities\LoanRepayment;

class ReportController extends Controller
{
    public function portfolio()
    {
        $loans = Loan::with('loanProduct', 'applicant')->where('status', '!=', 'Closed')->get();
        return view('lending::reports.portfolio', compact('loans'));
    }

    public function collections(Request $request)
    {
        $startDate = $request->input('start_date', now()->startOfMonth()->toDateString());
        $endDate = $request->input('end_date', now()->endOfMonth()->toDateString());

        $repayments = LoanRepayment::with('loan.applicant')
            ->whereBetween('payment_date', [$startDate, $endDate])
            ->get();

        return view('lending::reports.collections', compact('repayments', 'startDate', 'endDate'));
    }
}
