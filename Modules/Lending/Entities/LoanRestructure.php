<?php

namespace Modules\Lending\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Builder;

class LoanRestructure extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'loan_id',
        'status',
        'restructure_date',
        'old_rate_of_interest',
        'old_repayment_periods',
        'new_rate_of_interest',
        'new_repayment_periods',
        'reason',
    ];

    protected $casts = [
        'restructure_date' => 'date',
        'old_rate_of_interest' => 'decimal:4',
        'new_rate_of_interest' => 'decimal:4',
    ];

    protected static function booted()
    {
        if (auth()->check() && session()->has('company_id')) {
            static::addGlobalScope('company', function (Builder $builder) {
                $builder->where('company_id', session('company_id'));
            });
        }
    }

    public function loan()
    {
        return $this->belongsTo(Loan::class);
    }
}
