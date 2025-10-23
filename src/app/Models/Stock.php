<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Stock extends Model
{
    protected $fillable = [
        'stock_date',
        'warehouse_name',
        'nm_id',
        'quantity',
        'in_way_to_client',
        'in_way_from_client',
        'price',
        'discount_percent',
        'supplier_article',
        'product_category',
        'brand',
        'raw_data'
    ];

    protected $casts = [
        'stock_date' => 'date',
        'price' => 'decimal:2',
        'raw_data' => 'array'
    ];
}
