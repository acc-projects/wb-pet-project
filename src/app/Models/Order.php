<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = [
        'g_number',
        'order_date',
        'total_price',
        'discount_percent',
        'warehouse_name',
        'region',
        'is_cancelled',
        'cancelled_at',
        'nm_id',
        'supplier_article',
        'product_category',
        'brand',
        'raw_data'
    ];

    protected $casts = [
        'order_date' => 'datetime',
        'cancelled_at' => 'datetime',
        'total_price' => 'decimal:2',
        'is_cancelled' => 'boolean',
        'raw_data' => 'array'
    ];
}
