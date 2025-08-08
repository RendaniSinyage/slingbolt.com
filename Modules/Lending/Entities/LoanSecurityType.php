<?php

namespace Modules\Lending\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class LoanSecurityType extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'haircut',
    ];

    protected $casts = [
        'haircut' => 'decimal:2',
    ];
}
