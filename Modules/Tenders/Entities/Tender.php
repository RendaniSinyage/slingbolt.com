<?php

namespace Modules\Tenders\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Tender extends Model
{
    use HasFactory;

    protected $fillable = [
        'ocid',
        'title',
        'description',
        'status',
        'main_procurement_category',
        'additional_procurement_categories',
        'submission_method',
        'procuring_entity_name',
        'procuring_entity_id',
        'tender_period_start_date',
        'tender_period_end_date',
    ];

    protected static function newFactory()
    {
        // return \Modules\Tenders\Database\factories\TenderFactory::new();
    }
}
