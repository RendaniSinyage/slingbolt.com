<?php

namespace Modules\Lending\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Lending\Entities\LoanSecurity;
use Modules\Lending\Entities\LoanSecurityType;

class LoanSecurityController extends Controller
{
    public function index()
    {
        $securities = LoanSecurity::with('loanSecurityType')->paginate(15);
        return view('lending::securities.index', compact('securities'));
    }

    public function create()
    {
        $types = LoanSecurityType::all();
        return view('lending::securities.create', compact('types'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'loan_security_code' => 'required|unique:loan_securities,loan_security_code',
            'loan_security_name' => 'required',
            'loan_security_type_id' => 'required|exists:loan_security_types,id',
            'original_security_value' => 'required|numeric',
        ]);

        $data = $request->all();
        $data['company_id'] = 1; // Placeholder

        LoanSecurity::create($data);

        return redirect()->route('lending.loan-securities.index')->with('success', 'Loan Security created successfully.');
    }

    // ... (show, edit, update, destroy methods would go here)
}
