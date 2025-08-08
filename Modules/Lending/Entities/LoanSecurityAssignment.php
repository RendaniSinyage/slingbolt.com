<?php

namespace Modules\Lending\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Builder;

class LoanSecurityAssignment extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'assignable_id',
        'assignable_type',
        'status',
        'total_security_value',
        'maximum_loan_value',
        'pledge_time',
        'release_time',
    ];

    protected $casts = [
        'total_security_value' => 'decimal:2',
        'maximum_loan_value' => 'decimal:2',
        'pledge_time' => 'datetime',
        'release_time' => 'datetime',
    ];

    protected static function booted()
    {
        if (auth()->check() && session()->has('company_id')) {
            static::addGlobalScope('company', function (Builder $builder) {
                $builder->where('company_id', session('company_id'));
            });
        }
    }

    public function assignable()
    {
        return $this->morphTo();
    }

    public function company()
    {
        return $this->belongsTo(\App\Models\Company::class);
    }

    public function pledges()
    {
        return $this->hasMany(LoanSecurityPledge::class);
    }
}
