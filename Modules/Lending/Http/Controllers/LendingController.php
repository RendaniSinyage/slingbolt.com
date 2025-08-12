<?php

namespace Modules\Lending\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Lending\Entities\Loan;
use Modules\Lending\Entities\LoanProduct;
use Modules\Lending\Services\LoanService;
// Assuming a Customer model exists in the main app
use App\Models\Customer;

class LendingController extends Controller
{
    protected $loanService;

    public function __construct(LoanService $loanService)
    {
        $this->loanService = $loanService;
    }

    public function index()
    {
        $loans = Loan::with('loanProduct', 'applicant')->paginate(15);
        return view('lending::loans.index', compact('loans'));
    }

    public function create()
    {
        $loanProducts = LoanProduct::all();
        $customers = Customer::all();
        return view('lending::loans.create', compact('loanProducts', 'customers'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'applicant_type' => 'required|string',
            'applicant_id' => 'required|integer',
            'loan_product_id' => 'required|exists:loan_products,id',
            'loan_amount' => 'required|numeric',
            'posting_date' => 'required|date',
            'status' => 'required|string|in:Sanctioned,Disbursed',
            'disbursement_date' => 'required_if:status,Disbursed|nullable|date',
        ]);

        try {
            $this->loanService->createLoan($request->all());
        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->back()->withErrors($e->errors())->withInput();
        }

        return redirect()->route('lending.lending-loans.index')->with('success', 'Loan created successfully.');
    }

    public function show(Loan $loan)
    {
        $loan->load('applicant', 'loanProduct', 'schedule.installments', 'repayments', 'securityAssignments.pledges.security');
        return view('lending::loans.show', compact('loan'));
    }

    public function edit(Loan $loan)
    {
        $loanProducts = LoanProduct::all();
        $customers = Customer::all();
        return view('lending::loans.edit', compact('loan', 'loanProducts', 'customers'));
    }

    public function update(Request $request, Loan $loan)
    {
        $request->validate([
            'status' => 'required|string',
            'loan_amount' => 'required|numeric',
        ]);

        // In a real scenario, update would also use the service for validation and logic
        $loan->update($request->all());

        return redirect()->route('lending.lending-loans.index')->with('success', 'Loan updated successfully.');
    }

    public function destroy(Loan $loan)
    {
        $loan->delete();
        return redirect()->route('lending.lending-loans.index')->with('success', 'Loan deleted successfully.');
    }
}
