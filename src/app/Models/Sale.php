<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Sale extends Model
{
    protected $fillable = [
        'sale_id',
        'sale_date',
        'total_price',
        'for_pay',
        'finished_price',
        'warehouse_name',
        'region_name',
        'country_name',
        'nm_id',
        'supplier_article',
        'product_category',
        'brand',
        'raw_data'
    ];

    protected $casts = [
        'sale_date' => 'date',
        'total_price' => 'decimal:2',
        'for_pay' => 'decimal:2',
        'finished_price' => 'decimal:2',
        'raw_data' => 'array'
    ];
}
