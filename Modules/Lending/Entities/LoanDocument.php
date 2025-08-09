<?php

namespace Modules\Lending\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class LoanDocument extends Model
{
    use HasFactory;

    protected $fillable = [
        'loan_application_id',
        'file_name',
        'file_path',
        'file_size',
        'created_by',
    ];

    public function loanApplication()
    {
        return $this->belongsTo(LoanApplication::class);
    }
}
