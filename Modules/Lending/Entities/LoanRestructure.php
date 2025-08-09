<?php

namespace Modules\Lending\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Builder;

class LoanRestructure extends Model
{
    use HasFactory;

    protected $fillable = [
        'created_by',
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

    public function createdBy()
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }

    public function loan()
    {
        return $this->belongsTo(Loan::class);
    }
}
