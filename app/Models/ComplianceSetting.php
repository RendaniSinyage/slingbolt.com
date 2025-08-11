<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ComplianceSetting extends Model
{
    use HasFactory;

    protected $table = 'compliance_settings';

    protected $fillable = [
        'company_id',
        'max_interest_rate',
        'max_initiation_fee',
        'max_monthly_service_fee',
    ];

    protected $casts = [
        'max_interest_rate' => 'decimal:4',
        'max_initiation_fee' => 'decimal:2',
        'max_monthly_service_fee' => 'decimal:2',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }
}
