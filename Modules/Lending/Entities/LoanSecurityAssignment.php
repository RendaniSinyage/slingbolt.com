<?php

namespace Modules\Lending\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Builder;

class LoanSecurityAssignment extends Model
{
    use HasFactory;

    protected $fillable = [
        'created_by',
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

    public function assignable()
    {
        return $this->morphTo();
    }

    public function createdBy()
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }

    public function pledges()
    {
        return $this->hasMany(LoanSecurityPledge::class);
    }
}
