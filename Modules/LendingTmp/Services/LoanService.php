<?php

namespace Modules\LendingTmp\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Modules\LendingTmp\Entities\LoanProduct;
use Modules\LendingTmp\Entities\Loan;
use App\Models\JournalEntry;
use App\Models\JournalItem;

class LoanService
{
    /**
     * Validate the data for a new loan before creation.
     *
     * @param array $data
     * @throws ValidationException
     */
    public function validateLoan(array $data): void
    {
        $loanProduct = LoanProduct::find($data['loan_product_id']);

        if (isset($loanProduct->maximum_loan_amount) && $data['loan_amount'] > $loanProduct->maximum_loan_amount) {
            throw ValidationException::withMessages([
                'loan_amount' => 'Loan amount cannot be greater than the maximum loan amount defined in the loan product (' . $loanProduct->maximum_loan_amount . ').'
            ]);
        }

        if (isset($data['repayment_method']) && $data['repayment_method'] == 'Repay Over Number of Periods') {
            if (empty($data['repayment_periods'])) {
                throw ValidationException::withMessages([
                    'repayment_periods' => 'Repayment periods is mandatory when the repayment method is "Repay Over Number of Periods".'
                ]);
            }
        }
    }

    /**
     * Create a new loan and its corresponding journal entry if disbursed.
     *
     * @param array $data
     * @return Loan
     */
    public function createLoan(array $data): Loan
    {
        $this->validateLoan($data);

        $loan = DB::transaction(function () use ($data) {

            $data['company_id'] = 1; // Placeholder for multi-tenancy

            $loan = Loan::create($data);

            if ($loan->status == 'Disbursed') {
                $this->createLoanDisbursementJournalEntry($loan);
            }

            return $loan;
        });

        return $loan;
    }

    /**
     * Creates the journal entry for a loan disbursement.
     *
     * @param Loan $loan
     */
    private function createLoanDisbursementJournalEntry(Loan $loan): void
    {
        $loanProduct = $loan->loanProduct;

        $journalEntry = JournalEntry::create([
            'date' => $loan->disbursement_date ?? $loan->posting_date,
            'reference' => 'Loan ' . $loan->id,
            'description' => 'Loan disbursement for Loan #' . $loan->id,
            'created_by' => auth()->id() ?? 1, // Placeholder for user ID
        ]);

        // Debit the Loan Account (Asset) - The amount receivable from the borrower
        JournalItem::create([
            'journal' => $journalEntry->id,
            'account' => $loanProduct->loan_account_id,
            'debit' => $loan->loan_amount,
            'credit' => 0,
        ]);

        // Credit the Disbursement Account (e.g., Bank Account) - The cash going out
        JournalItem::create([
            'journal' => $journalEntry->id,
            'account' => $loanProduct->disbursement_account_id,
            'debit' => 0,
            'credit' => $loan->loan_amount,
        ]);
    }
}
