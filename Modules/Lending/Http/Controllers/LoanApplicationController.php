<?php

namespace Modules\Lending\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Lending\Entities\LoanApplication;
use Modules\Lending\Entities\LoanProduct;
use Modules\Lending\Entities\LoanSecurity;
use Modules\Lending\Entities\LoanSecurityAssignment;
use Illuminate\Support\Facades\DB;

class LoanApplicationController extends Controller
{
    public function index()
    {
        $loanApplications = LoanApplication::with('loanProduct', 'applicant')->paginate(15);
        return view('lending::loan_applications.index', compact('loanApplications'));
    }

    public function create()
    {
        $customers = \App\Models\Customer::all();
        $loanProducts = LoanProduct::all();
        $securities = LoanSecurity::where('disabled', false)->get();
        return view('lending::loan_applications.create', compact('customers', 'loanProducts', 'securities'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'applicant_type' => 'required|string',
            'applicant_id' => 'required|integer',
            'loan_product_id' => 'required|exists:loan_products,id',
            'loan_amount' => 'required|numeric',
            'securities' => 'sometimes|array',
        ]);

        DB::transaction(function () use ($request) {
            $data = $request->except('securities');
            $data['company_id'] = auth()->user()->company_id;

            $application = LoanApplication::create($data);

            if ($request->has('securities') && $request->is_secured_loan) {
                $assignment = $application->securityAssignments()->create([
                    'company_id' => $application->company_id,
                    'status' => 'Pledge Requested',
                    'total_security_value' => 0, // Should be calculated
                    'maximum_loan_value' => 0, // Should be calculated
                ]);

                $totalValue = 0;
                foreach ($request->securities as $security_id) {
                    $security = LoanSecurity::find($security_id);
                    if ($security) {
                        $assignment->pledges()->create([
                            'loan_security_id' => $security->id,
                            'quantity_pledged' => $security->original_security_value, // Assuming full value pledge
                        ]);
                        $totalValue += $security->original_security_value;
                    }
                }
                // Update total values on assignment
                $assignment->update(['total_security_value' => $totalValue]);
            }
        });

        return redirect()->route('lending.loan-applications.index')->with('success', 'Loan Application created successfully.');
    }

    // ... other methods
}
