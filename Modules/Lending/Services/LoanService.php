<?php

namespace Modules\Lending\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Modules\Lending\Entities\LoanProduct;
use Modules\Lending\Entities\Loan;
use Modules\Lending\Entities\LoanRepayment;
use Modules\Lending\Entities\LoanRepaymentSchedule;
use Modules\Lending\Entities\LoanRepaymentScheduleInstallment;
use Modules\Lending\Entities\LoanRestructure;
use Modules\Lending\Entities\LoanSecurityAssignment;
use Modules\Lending\Entities\LoanWriteOff;
use App\Models\JournalEntry;
use App\Models\JournalItem;

class LoanService
{
    // ... (existing methods)

    public function waiveInterest(LoanRepaymentScheduleInstallment $installment)
    {
        $amountToWaive = $installment->interest_amount; // Simplified
        if ($amountToWaive <= 0) return;

        DB::transaction(function () use ($installment, $amountToWaive) {
            $installment->update(['interest_waived' => $amountToWaive]);
            $this->createWaiverJournalEntry($installment->schedule->loan, $amountToWaive, 'interest');
        });
    }

    public function waivePenalty(LoanRepaymentScheduleInstallment $installment)
    {
        $amountToWaive = $installment->penalty_amount;
        if ($amountToWaive <= 0) return;

        DB::transaction(function () use ($installment, $amountToWaive) {
            $installment->update(['penalty_waived' => $amountToWaive]);
            $this->createWaiverJournalEntry($installment->schedule->loan, $amountToWaive, 'penalty');
        });
    }

    private function createWaiverJournalEntry(Loan $loan, float $amount, string $type)
    {
        $loanProduct = $loan->loanProduct;
        $receivableAccount = ($type == 'interest') ? $loanProduct->interest_receivable_account_id : $loanProduct->penalty_receivable_account_id;
        $waiverAccount = ($type == 'interest') ? $loanProduct->interest_waiver_account_id : $loanProduct->penalty_waiver_account_id;

        if (!$receivableAccount || !$waiverAccount) {
            throw new \Exception("Waiver accounts are not configured for this loan product.");
        }

        $journalEntry = JournalEntry::create([
            'date' => now()->toDateString(),
            'reference' => ucfirst($type) . ' Waiver for Loan #' . $loan->id,
            'description' => ucfirst($type) . ' waiver of ' . $amount,
            'created_by' => auth()->id() ?? 1,
        ]);

        // Debit Waiver Expense Account
        JournalItem::create([
            'journal' => $journalEntry->id,
            'account' => $waiverAccount,
            'debit' => $amount,
            'credit' => 0,
        ]);

        // Credit Receivable Account
        JournalItem::create([
            'journal' => $journalEntry->id,
            'account' => $receivableAccount,
            'debit' => 0,
            'credit' => $amount,
        ]);
    }
}
