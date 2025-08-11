<?php

namespace Modules\Lending\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class LoanSecurityPledge extends Model
{
    use HasFactory;

    protected $fillable = [
        'loan_security_assignment_id',
        'loan_security_id',
        'quantity_pledged',
    ];

    protected $casts = [
        'quantity_pledged' => 'decimal:2',
    ];

    public function assignment()
    {
        return $this->belongsTo(LoanSecurityAssignment::class, 'loan_security_assignment_id');
    }

    public function security()
    {
        return $this->belongsTo(LoanSecurity::class, 'loan_security_id');
    }
}
