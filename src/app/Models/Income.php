<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Income extends Model
{
    protected $fillable = [
        'income_id',
        'income_date',
        'date_close',
        'warehouse_name',
        'nm_id',
        'quantity',
        'total_price',
        'supplier_article',
        'raw_data'
    ];

    protected $casts = [
        'income_date' => 'date',
        'date_close' => 'date',
        'total_price' => 'decimal:2',
        'raw_data' => 'array'
    ];
}
