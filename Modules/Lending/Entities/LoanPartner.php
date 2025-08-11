<?php

namespace Modules\Lending\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class LoanPartner extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
    ];

    public function loanProducts()
    {
        return $this->belongsToMany(LoanProduct::class, 'loan_product_loan_partner')
            ->withPivot('share_percentage')
            ->withTimestamps();
    }
}
