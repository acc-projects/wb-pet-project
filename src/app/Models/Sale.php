<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Sale extends Model
{
    protected $fillable = [
        'account_id',
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

    /**
     * Продажа принадлежит аккаунту
     */
    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    /**
     * Scope для фильтрации по аккаунту
     */
    public function scopeForAccount($query, int $accountId)
    {
        return $query->where('account_id', $accountId);
    }

    /**
     * Scope для получения свежих данных
     */
    public function scopeFresh($query, ?string $sinceDate = null)
    {
        if ($sinceDate) {
            return $query->where('order_date', '>=', $sinceDate);
        }
        return $query->where('order_date', '>=', now()->subDays(7));
    }
}
