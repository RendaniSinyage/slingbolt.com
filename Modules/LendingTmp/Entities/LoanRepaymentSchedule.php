<?php

namespace Modules\LendingTmp\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class LoanRepaymentSchedule extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'loan_id',
        'posting_date',
        'repayment_start_date',
        'maturity_date',
        'status',
    ];

    protected $casts = [
        'posting_date' => 'date',
        'repayment_start_date' => 'date',
        'maturity_date' => 'date',
    ];

    public function loan()
    {
        return $this->belongsTo(Loan::class);
    }

    public function installments()
    {
        return $this->hasMany(LoanRepaymentScheduleInstallment::class);
    }
}
