<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QuoteProduct extends Model
{
    protected $fillable = [
        'quote_id',
        'product_id',
        'quantity',
        'tax',
        'discount',
        'price',
        'description',
    ];

    public function product()
    {
        return $this->hasOne(ProductService::class, 'id', 'product_id');
    }
}
