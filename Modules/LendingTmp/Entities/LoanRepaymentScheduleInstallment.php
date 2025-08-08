<?php

namespace Modules\LendingTmp\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class LoanRepaymentScheduleInstallment extends Model
{
    use HasFactory;

    protected $fillable = [
        'loan_repayment_schedule_id',
        'payment_date',
        'principal_amount',
        'interest_amount',
        'total_amount',
        'outstanding_principal_balance',
        'amount_paid',
        'is_paid',
    ];

    protected $casts = [
        'payment_date' => 'date',
        'principal_amount' => 'decimal:2',
        'interest_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'outstanding_principal_balance' => 'decimal:2',
        'amount_paid' => 'decimal:2',
        'is_paid' => 'boolean',
    ];

    public function schedule()
    {
        return $this->belongsTo(LoanRepaymentSchedule::class, 'loan_repayment_schedule_id');
    }
}
