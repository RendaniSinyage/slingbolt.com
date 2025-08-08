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
    // ... (existing methods)

    public function processRepayment(Loan $loan, array $data)
    {
        return DB::transaction(function () use ($loan, $data) {
            $paymentAmount = $data['amount_paid'];

            // Simplified breakdown logic for now. A real system would be more complex.
            $totalOutstandingPenalty = $loan->schedule->installments()->sum('penalty_amount');
            $totalOutstandingInterest = $loan->schedule->installments()->sum('interest_amount'); // This is not accurate, just a placeholder

            $penaltyPaid = min($paymentAmount, $totalOutstandingPenalty);
            $paymentAmountAfterPenalty = $paymentAmount - $penaltyPaid;

            $interestPaid = min($paymentAmountAfterPenalty, $totalOutstandingInterest);
            $paymentAmountAfterInterest = $paymentAmountAfterPenalty - $interestPaid;

            $principalPaid = $paymentAmountAfterInterest;

            $repayment = $loan->repayments()->create([
                'company_id' => $loan->company_id,
                'amount_paid' => $data['amount_paid'],
                'payment_date' => $data['payment_date'],
                'remarks' => $data['remarks'] ?? null,
            ]);

            $journalEntry = $this->createRepaymentJournalEntry($loan, $repayment, $principalPaid, $interestPaid, $penaltyPaid);
            $repayment->update(['journal_entry_id' => $journalEntry->id]);

            return $repayment;
        });
    }

    private function createRepaymentJournalEntry(Loan $loan, LoanRepayment $repayment, float $principal, float $interest, float $penalty): JournalEntry
    {
        $loanProduct = $loan->loanProduct;

        $journalEntry = JournalEntry::create([
            'date' => $repayment->payment_date,
            'reference' => 'Repayment for Loan #' . $loan->id,
            'description' => 'Loan repayment of ' . $repayment->amount_paid,
            'created_by' => auth()->id() ?? 1,
        ]);

        // Debit the Payment Account (e.g., Bank)
        JournalItem::create([
            'journal' => $journalEntry->id,
            'account' => $loanProduct->payment_account_id,
            'debit' => $repayment->amount_paid,
            'credit' => 0,
        ]);

        // Credit the respective receivable accounts
        if ($principal > 0) {
            JournalItem::create([
                'journal' => $journalEntry->id,
                'account' => $loanProduct->loan_account_id,
                'debit' => 0,
                'credit' => $principal,
            ]);
        }
        if ($interest > 0) {
            JournalItem::create([
                'journal' => $journalEntry->id,
                'account' => $loanProduct->interest_receivable_account_id,
                'debit' => 0,
                'credit' => $interest,
            ]);
        }
        if ($penalty > 0) {
            JournalItem::create([
                'journal' => $journalEntry->id,
                'account' => $loanProduct->penalty_receivable_account_id,
                'debit' => 0,
                'credit' => $penalty,
            ]);
        }

        return $journalEntry;
    }

    // ... (rest of the service)
}
