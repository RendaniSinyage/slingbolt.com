<?php

namespace Modules\Lending\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Lending\Entities\LoanProduct;

class LoanProductController extends Controller
{
    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    public function index()
    {
        $loanProducts = LoanProduct::paginate(15);
        return view('lending::loan_products.index', compact('loanProducts'));
    }

    /**
     * Show the form for creating a new resource.
     * @return Renderable
     */
    public function create()
    {
        return view('lending::loan_products.create');
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Renderable
     */
    public function store(Request $request)
    {
        $request->validate([
            'product_code' => 'required|unique:loan_products,product_code',
            'product_name' => 'required',
            'rate_of_interest' => 'required|numeric',
        ]);

        $data = $request->all();
        $data['company_id'] = auth()->user()->company_id;
        // TODO: The account IDs should be selected from a dropdown in the UI.
        $data['disbursement_account_id'] = 1;
        $data['payment_account_id'] = 1;
        $data['loan_account_id'] = 1;
        $data['interest_income_account_id'] = 1;
        $data['penalty_income_account_id'] = 1;
        $data['write_off_account_id'] = 1;
        $data['interest_receivable_account_id'] = 1;
        $data['penalty_receivable_account_id'] = 1;

        LoanProduct::create($data);

        return redirect()->route('lending.loan-products.index')->with('success', 'Loan Product created successfully.');
    }

    /**
     * Show the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function show(LoanProduct $loanProduct)
    {
        return view('lending::loan_products.show', compact('loanProduct'));
    }

    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function edit(LoanProduct $loanProduct)
    {
        return view('lending::loan_products.edit', compact('loanProduct'));
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     * @return Renderable
     */
    public function update(Request $request, LoanProduct $loanProduct)
    {
        $request->validate([
            'product_code' => 'required|unique:loan_products,product_code,' . $loanProduct->id,
            'product_name' => 'required',
            'rate_of_interest' => 'required|numeric',
        ]);

        $loanProduct->update($request->all());

        return redirect()->route('lending.loan-products.index')->with('success', 'Loan Product updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     * @param int $id
     * @return Renderable
     */
    public function destroy(LoanProduct $loanProduct)
    {
        $loanProduct->delete();
        return redirect()->route('lending.loan-products.index')->with('success', 'Loan Product deleted successfully.');
    }
}
