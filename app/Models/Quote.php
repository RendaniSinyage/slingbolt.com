<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Quote extends Model
{
    protected $fillable = [
        'quote_id',
        'customer_id',
        'issue_date',
        'due_date',
        'ref_number',
        'status',
        'category_id',
        'created_by',
    ];

    public function items()
    {
        return $this->hasMany(QuoteProduct::class, 'quote_id', 'id');
    }

    public function customer()
    {
        return $this->hasOne(Customer::class, 'id', 'customer_id');
    }

    public function category()
    {
        return $this->hasOne(ProductServiceCategory::class, 'id', 'category_id');
    }
}
