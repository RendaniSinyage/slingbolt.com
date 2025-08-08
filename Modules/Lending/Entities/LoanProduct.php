<?php

namespace Modules\Lending\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;

class LoanProduct extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'company_id',
        'product_code',
        'product_name',
        'rate_of_interest',
        'penalty_interest_rate',
        'maximum_loan_amount',
        'days_past_due_threshold_for_npa',
        'is_term_loan',
        'disabled',
        'disbursement_account_id',
        'payment_account_id',
        'loan_account_id',
        'interest_income_account_id',
        'penalty_income_account_id',
        'write_off_account_id',
        'interest_receivable_account_id',
        'penalty_receivable_account_id',
        'suspense_interest_income_id',
    ];

    // ... (casts and booted method)

    public function company()
    {
        return $this->belongsTo(\App\Models\Company::class);
    }

    public function loanPartners()
    {
        return $this->belongsToMany(LoanPartner::class, 'loan_product_loan_partner')
            ->withPivot('share_percentage')
            ->withTimestamps();
    }
}
