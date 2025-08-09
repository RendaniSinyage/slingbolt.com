<?php

namespace Modules\Lending\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;

class LoanApplication extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'created_by',
        'applicant_type',
        'applicant_id',
        'loan_product_id',
        'status',
        'loan_amount',
        'repayment_method',
        'repayment_periods',
        'is_secured_loan',
        'monthly_income',
        'monthly_debt',
        'failed_debit_orders_last_3_months',
        'reversed_debit_orders_last_3_months',
        'recommendation',
        'recommendation_reason',
    ];

    protected $casts = [
        'loan_amount' => 'decimal:2',
        'is_secured_loan' => 'boolean',
        'failed_debit_orders_last_3_months' => 'array',
    ];

    public function applicant()
    {
        return $this->morphTo();
    }

    public function createdBy()
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }

    public function loanProduct()
    {
        return $this->belongsTo(LoanProduct::class);
    }

    public function securityAssignments()
    {
        return $this->morphMany(LoanSecurityAssignment::class, 'assignable');
    }

    public function documents()
    {
        return $this->hasMany(LoanDocument::class);
    }
}
