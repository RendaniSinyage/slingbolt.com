<?php

namespace Modules\Lending\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Builder;

class LoanSecurity extends Model
{
    use HasFactory;

    protected $fillable = [
        'created_by',
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

    public function createdBy()
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }

    public function loanSecurityType()
    {
        return $this->belongsTo(LoanSecurityType::class);
    }
}
