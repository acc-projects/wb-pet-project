<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Order extends Model
{
    protected $fillable = [
        'account_id',
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

    /**
     * Заказ принадлежит аккаунту
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
