<?php

namespace Modules\Lending\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Lending\Entities\LoanProduct;
use Modules\Lending\Helpers\ComplianceHelper;

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

        $created_by = \Auth::user()->creatorId();
        $data = $request->all();
        $data['created_by'] = $created_by;

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

        // Check for compliance warning
        $max_interest_rate = ComplianceHelper::getSetting('max_interest_rate', $created_by);
        if ($max_interest_rate && $request->rate_of_interest > $max_interest_rate) {
            $warning = 'Warning: The interest rate of ' . $request->rate_of_interest . '% exceeds the compliance limit of ' . $max_interest_rate . '%.';
            return redirect()->route('lending.loan-products.index')->with('success', 'Loan Product created successfully.')->with('warning', $warning);
        }

        return redirect()->route('lending.loan-products.index')->with('success', 'Loan Product created successfully.');
    }

    // ... (other methods)
}
