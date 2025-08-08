<?php

namespace Modules\LendingTmp\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;

class LoanApplication extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'company_id',
        'applicant_type',
        'applicant_id',
        'loan_product_id',
        'status',
        'loan_amount',
        'repayment_method',
        'repayment_periods',
        'is_secured_loan',
    ];

    protected $casts = [
        'loan_amount' => 'decimal:2',
        'is_secured_loan' => 'boolean',
    ];

    /**
     * The "booted" method of the model.
     *
     * @return void
     */
    protected static function booted()
    {
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
        return $this->belongsTo(\App\Models\Company::class);
    }

    public function loanProduct()
    {
        return $this->belongsTo(LoanProduct::class);
    }
}
