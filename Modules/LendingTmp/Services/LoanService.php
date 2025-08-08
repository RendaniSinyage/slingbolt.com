<?php

namespace Modules\LendingTmp\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Modules\LendingTmp\Entities\LoanProduct;
use Modules\LendingTmp\Entities\Loan;
use Modules\LendingTmp\Entities\LoanRepayment;
use Modules\LendingTmp\Entities\LoanRepaymentSchedule;
use Modules\LendingTmp\Entities\LoanRepaymentScheduleInstallment;
use App\Models\JournalEntry;
use App\Models\JournalItem;

class LoanService
{
    // ... (validateLoan, createLoan, createLoanDisbursementJournalEntry, generateRepaymentSchedule methods)

    public function processRepayment(Loan $loan, array $data)
    {
        return DB::transaction(function () use ($loan, $data) {
            $repayment = $loan->repayments()->create([
                'company_id' => $loan->company_id,
                'amount_paid' => $data['amount_paid'],
                'payment_date' => $data['payment_date'],
                'remarks' => $data['remarks'] ?? null,
            ]);

            $journalEntry = $this->createRepaymentJournalEntry($loan, $repayment);

            $repayment->journal_entry_id = $journalEntry->id;
            $repayment->save();

            // In a real application, you would add logic here to update the loan's balance
            // and the status of the repayment schedule installments.

            return $repayment;
        });
    }

    private function createRepaymentJournalEntry(Loan $loan, LoanRepayment $repayment): JournalEntry
    {
        $loanProduct = $loan->loanProduct;

        $journalEntry = JournalEntry::create([
            'date' => $repayment->payment_date,
            'reference' => 'Repayment for Loan #' . $loan->id,
            'description' => 'Loan repayment of ' . $repayment->amount_paid,
            'created_by' => auth()->id() ?? 1,
        ]);

        // Debit the Payment Account (e.g., Bank) - Cash coming in
        JournalItem::create([
            'journal' => $journalEntry->id,
            'account' => $loanProduct->payment_account_id,
            'debit' => $repayment->amount_paid,
            'credit' => 0,
        ]);

        // Credit the Loan Account (Asset) - Reducing the amount receivable
        JournalItem::create([
            'journal' => $journalEntry->id,
            'account' => $loanProduct->loan_account_id,
            'debit' => 0,
            'credit' => $repayment->amount_paid,
        ]);

        return $journalEntry;
    }
}
