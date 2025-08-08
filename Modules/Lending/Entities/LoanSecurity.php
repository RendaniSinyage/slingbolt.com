<?php

namespace Modules\Lending\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Builder;

class LoanSecurity extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'loan_security_type_id',
        'loan_security_code',
        'loan_security_name',
        'original_security_value',
        'utilized_security_value',
        'disabled',
    ];

    protected $casts = [
        'original_security_value' => 'decimal:2',
        'utilized_security_value' => 'decimal:2',
        'disabled' => 'boolean',
    ];

    protected static function booted()
    {
        if (auth()->check() && session()->has('company_id')) {
            static::addGlobalScope('company', function (Builder $builder) {
                $builder->where('company_id', session('company_id'));
            });
        }
    }

    public function company()
    {
        return $this->belongsTo(\App\Models\Company::class);
    }

    public function loanSecurityType()
    {
        return $this->belongsTo(LoanSecurityType::class);
    }
}
