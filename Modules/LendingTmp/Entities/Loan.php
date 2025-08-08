<?php

namespace Modules\LendingTmp\Entities;

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

    protected $casts = [
        'loan_amount' => 'decimal:2',
        'disbursed_amount' => 'decimal:2',
        'rate_of_interest' => 'decimal:4',
        'penalty_charges_rate' => 'decimal:4',
        'posting_date' => 'date',
        'disbursement_date' => 'date',
        'closure_date' => 'date',
        'settlement_date' => 'date',
        'is_secured_loan' => 'boolean',
        'is_term_loan' => 'boolean',
        'is_npa' => 'boolean',
    ];

    /**
     * The "booted" method of the model.
     *
     * @return void
     */
    protected static function booted()
    {
        // Assuming a session variable or auth helper provides the current company_id
        // In a real scenario, this might be more robust, e.g., using a dedicated tenancy package.
        if (auth()->check() && session()->has('company_id')) {
            static::addGlobalScope('company', function (Builder $builder) {
                $builder->where('company_id', session('company_id'));
            });
        }
    }

    public function applicant()
    {
        return $this->morphTo();
    }

    public function company()
    {
        // Assuming a Company model exists in the main App namespace
        return $this->belongsTo(\App\Models\Company::class);
    }

    public function loanProduct()
    {
        // Assuming a LoanProduct model will be created in this module
        return $this->belongsTo(LoanProduct::class);
    }

    public function loanApplication()
    {
        // Assuming a LoanApplication model will be created in this module
        return $this->belongsTo(LoanApplication::class);
    }
}
