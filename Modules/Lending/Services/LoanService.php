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
use App\Models\JournalEntry;
use App\Models\JournalItem;

class LoanService
{
    public function validateLoan(array $data): void
    {
        $loanProduct = LoanProduct::find($data['loan_product_id']);

        if (isset($loanProduct->maximum_loan_amount) && $data['loan_amount'] > $loanProduct->maximum_loan_amount) {
            throw ValidationException::withMessages([
                'loan_amount' => 'Loan amount cannot be greater than the maximum loan amount defined in the loan product (' . $loanProduct->maximum_loan_amount . ').'
            ]);
        }
    }

    public function createLoan(array $data): Loan
    {
        $this->validateLoan($data);

        $loan = DB::transaction(function () use ($data) {
            $data['company_id'] = auth()->user()->company_id;
            $loan = Loan::create($data);

            if ($loan->status == 'Disbursed') {
                $this->createLoanDisbursementJournalEntry($loan);
            }

            if ($loan->is_term_loan && $loan->repayment_periods > 0) {
                $this->generateRepaymentSchedule($loan);
            }

            return $loan;
        });

        return $loan;
    }

    private function createLoanDisbursementJournalEntry(Loan $loan): void
    {
        $loanProduct = $loan->loanProduct;

        $journalEntry = JournalEntry::create([
            'date' => $loan->disbursement_date ?? $loan->posting_date,
            'reference' => 'Loan ' . $loan->id,
            'description' => 'Loan disbursement for Loan #' . $loan->id,
            'created_by' => auth()->id() ?? 1,
        ]);

        JournalItem::create([
            'journal' => $journalEntry->id,
            'account' => $loanProduct->loan_account_id,
            'debit' => $loan->loan_amount,
            'credit' => 0,
        ]);

        JournalItem::create([
            'journal' => $journalEntry->id,
            'account' => $loanProduct->disbursement_account_id,
            'debit' => 0,
            'credit' => $loan->loan_amount,
        ]);
    }

    public function generateRepaymentSchedule(Loan $loan)
    {
        $principal = $loan->loan_amount;
        $monthlyInterestRate = ($loan->rate_of_interest / 100) / 12;
        $numberOfMonths = $loan->repayment_periods;

        if ($numberOfMonths <= 0) return;

        $monthlyPayment = ($principal * $monthlyInterestRate * pow(1 + $monthlyInterestRate, $numberOfMonths)) / (pow(1 + $monthlyInterestRate, $numberOfMonths) - 1);
        $monthlyPayment = round($monthlyPayment, 2);

        $schedule = LoanRepaymentSchedule::create([
            'company_id' => $loan->company_id,
            'loan_id' => $loan->id,
            'posting_date' => $loan->posting_date,
            'repayment_start_date' => $loan->posting_date->copy()->addMonth(),
            'maturity_date' => $loan->posting_date->copy()->addMonths($numberOfMonths),
            'status' => 'Active',
        ]);

        $outstandingPrincipal = $principal;

        for ($i = 1; $i <= $numberOfMonths; $i++) {
            $interestForMonth = round($outstandingPrincipal * $monthlyInterestRate, 2);
            $principalForMonth = $monthlyPayment - $interestForMonth;

            if ($i == $numberOfMonths) {
                $principalForMonth = $outstandingPrincipal;
                $monthlyPayment = $outstandingPrincipal + $interestForMonth;
            }

            $outstandingPrincipal -= $principalForMonth;

            LoanRepaymentScheduleInstallment::create([
                'loan_repayment_schedule_id' => $schedule->id,
                'payment_date' => $loan->posting_date->copy()->addMonths($i),
                'principal_amount' => $principalForMonth,
                'interest_amount' => $interestForMonth,
                'total_amount' => $monthlyPayment,
                'outstanding_principal_balance' => $outstandingPrincipal,
            ]);
        }
    }

    public function processRepayment(Loan $loan, array $data)
    {
        return DB::transaction(function () use ($loan, $data) {
            $paymentAmount = $data['amount_paid'];

            $totalOutstandingPenalty = $loan->schedule->installments()->sum('penalty_amount');
            $totalOutstandingInterest = $loan->schedule->installments()->sum('interest_amount');

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

        JournalItem::create([
            'journal' => $journalEntry->id,
            'account' => $loanProduct->payment_account_id,
            'debit' => $repayment->amount_paid,
            'credit' => 0,
        ]);

        if ($principal > 0) {
            JournalItem::create(['journal' => $journalEntry->id, 'account' => $loanProduct->loan_account_id, 'credit' => $principal]);
        }
        if ($interest > 0) {
            JournalItem::create(['journal' => $journalEntry->id, 'account' => $loanProduct->interest_receivable_account_id, 'credit' => $interest]);
        }
        if ($penalty > 0) {
            JournalItem::create(['journal' => $journalEntry->id, 'account' => $loanProduct->penalty_receivable_account_id, 'credit' => $penalty]);
        }

        return $journalEntry;
    }

    public function applyPenalties()
    {
        $overdueInstallments = LoanRepaymentScheduleInstallment::where('is_paid', false)
            ->where('payment_date', '<', now()->toDateString())
            ->get();

        foreach ($overdueInstallments as $installment) {
            $loan = $installment->schedule->loan;
            $loanProduct = $loan->loanProduct;

            if ($loanProduct->penalty_interest_rate > 0) {
                $outstandingAmount = ($installment->principal_amount + $installment->interest_amount + $installment->penalty_amount) - $installment->amount_paid;

                if ($outstandingAmount <= 0) continue;

                $dailyPenaltyRate = ($loanProduct->penalty_interest_rate / 100) / 365;
                $penalty = round($outstandingAmount * $dailyPenaltyRate, 2);

                if ($penalty > 0) {
                    DB::transaction(function () use ($installment, $penalty, $loan) {
                        $installment->increment('penalty_amount', $penalty);
                        $this->createPenaltyJournalEntry($loan, $penalty);
                    });
                }
            }
        }
    }

    private function createPenaltyJournalEntry(Loan $loan, float $penaltyAmount): void
    {
        $loanProduct = $loan->loanProduct;

        $journalEntry = JournalEntry::create([
            'date' => now()->toDateString(),
            'reference' => 'Penalty for Loan #' . $loan->id,
            'description' => 'Penalty of ' . $penaltyAmount . ' applied to Loan #' . $loan->id,
            'created_by' => auth()->id() ?? 1,
        ]);

        JournalItem::create([
            'journal' => $journalEntry->id,
            'account' => $loanProduct->penalty_receivable_account_id,
            'debit' => $penaltyAmount,
            'credit' => 0,
        ]);

        JournalItem::create([
            'journal' => $journalEntry->id,
            'account' => $loanProduct->penalty_income_account_id,
            'debit' => 0,
            'credit' => $penaltyAmount,
        ]);
    }

    public function restructureLoan(Loan $loan, array $data)
    {
        return DB::transaction(function () use ($loan, $data) {
            $restructure = $loan->restructures()->create([
                'company_id' => $loan->company_id,
                'status' => 'Approved', // Simplified
                'restructure_date' => $data['restructure_date'],
                'old_rate_of_interest' => $loan->rate_of_interest,
                'old_repayment_periods' => $loan->repayment_periods,
                'new_rate_of_interest' => $data['new_rate_of_interest'],
                'new_repayment_periods' => $data['new_repayment_periods'],
                'reason' => $data['reason'] ?? null,
            ]);

            $loan->update([
                'rate_of_interest' => $data['new_rate_of_interest'],
                'repayment_periods' => $data['new_repayment_periods'],
            ]);

            if ($loan->schedule) {
                $loan->schedule->update(['status' => 'Restructured']);
            }

            $loan->is_term_loan = true;
            $this->generateRepaymentSchedule($loan);

            return $restructure;
        });
    }
}
