<?php

namespace Modules\Tenders\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TenderSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'categories',
        'provinces',
        'type',
        'submission_type',
    ];

    protected static function newFactory()
    {
        // return \Modules\Tenders\Database\factories\TenderSettingFactory::new();
    }
}
