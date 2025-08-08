<?php

namespace Modules\LendingTmp\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\LendingTmp\Entities\LoanApplication;
use Modules\LendingTmp\Entities\LoanProduct;

class LoanApplicationController extends Controller
{
    public function index()
    {
        $loanApplications = LoanApplication::with('loanProduct', 'applicant')->paginate(15);
        return view('lendingtmp::loan_applications.index', compact('loanApplications'));
    }

    public function create()
    {
        // Assuming Customer is a model in the main app
        $customers = \App\Models\Customer::all();
        $loanProducts = LoanProduct::all();
        return view('lendingtmp::loan_applications.create', compact('customers', 'loanProducts'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'applicant_type' => 'required|string',
            'applicant_id' => 'required|integer',
            'loan_product_id' => 'required|exists:loan_products,id',
            'loan_amount' => 'required|numeric',
        ]);

        $data = $request->all();
        $data['company_id'] = 1; // Placeholder

        LoanApplication::create($data);

        return redirect()->route('lendingtmp.loan-applications.index')->with('success', 'Loan Application created successfully.');
    }

    public function show(LoanApplication $loanApplication)
    {
        return view('lendingtmp::loan_applications.show', compact('loanApplication'));
    }

    public function edit(LoanApplication $loanApplication)
    {
        $customers = \App\Models\Customer::all();
        $loanProducts = LoanProduct::all();
        return view('lendingtmp::loan_applications.edit', compact('loanApplication', 'customers', 'loanProducts'));
    }

    public function update(Request $request, LoanApplication $loanApplication)
    {
        $request->validate([
            'applicant_type' => 'required|string',
            'applicant_id' => 'required|integer',
            'loan_product_id' => 'required|exists:loan_products,id',
            'loan_amount' => 'required|numeric',
            'status' => 'required|string',
        ]);

        $loanApplication->update($request->all());

        return redirect()->route('lendingtmp.loan-applications.index')->with('success', 'Loan Application updated successfully.');
    }

    public function destroy(LoanApplication $loanApplication)
    {
        $loanApplication->delete();
        return redirect()->route('lendingtmp.loan-applications.index')->with('success', 'Loan Application deleted successfully.');
    }
}
