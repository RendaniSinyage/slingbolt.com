<?php

namespace Modules\Tenders\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class DeniedTender extends Model
{
    use HasFactory;

    protected $fillable = [
        'created_by',
        'ocid',
    ];

    protected static function newFactory()
    {
        // return \Modules\Tenders\Database\factories\DeniedTenderFactory::new();
    }
}
