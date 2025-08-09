<?php

namespace Modules\Lending\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Lending\Entities\LoanApplication;
use App\Models\Contract;
use App\Models\ContractType;

class ContractController extends Controller
{
    public function create(LoanApplication $application)
    {
        if ($application->status != 'Approved') {
            return redirect()->back()->with('error', 'Loan application is not approved yet.');
        }

        // Assuming a default contract type for loans
        $contractType = ContractType::firstOrCreate(['name' => 'Loan Agreement']);

        $contract = Contract::create([
            'client_name' => $application->applicant->id, // Assuming applicant is a user
            'subject' => 'Loan Agreement - ' . $application->loanProduct->product_name,
            'project_id' => null, // Or link to a project if applicable
            'type' => $contractType->id,
            'value' => $application->loan_amount,
            'start_date' => now()->format('Y-m-d'),
            'end_date' => now()->addMonths($application->repayment_periods)->format('Y-m-d'),
            'description' => 'This is a loan agreement for the loan application #' . $application->id,
            'created_by' => $application->created_by,
        ]);

        // Redirect to the core contract show page
        return redirect()->route('contract.show', $contract->id)
            ->with('success', 'Contract generated successfully.');
    }
}
