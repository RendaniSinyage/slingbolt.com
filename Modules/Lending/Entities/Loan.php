<?php

namespace Modules\Lending\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;

class Loan extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'company_id',
        'applicant_type',
        'applicant_id',
        'loan_product_id',
        'loan_application_id',
        'status',
        'loan_amount',
        'disbursed_amount',
        'rate_of_interest',
        'penalty_charges_rate',
        'posting_date',
        'disbursement_date',
        'closure_date',
        'settlement_date',
        'repayment_method',
        'repayment_periods',
        'repayment_frequency',
        'is_secured_loan',
        'is_term_loan',
        'is_npa',
    ];

    // ... (casts and booted method)

    public function applicant()
    {
        return $this->morphTo();
    }

    public function company()
    {
        return $this->belongsTo(\App\Models\Company::class);
    }

    public function loanProduct()
    {
        return $this->belongsTo(LoanProduct::class);
    }

    public function loanApplication()
    {
        return $this->belongsTo(LoanApplication::class);
    }

    public function schedule()
    {
        return $this->hasOne(LoanRepaymentSchedule::class);
    }

    public function repayments()
    {
        return $this->hasMany(LoanRepayment::class);
    }

    public function restructures()
    {
        return $this->hasMany(LoanRestructure::class);
    }
}
