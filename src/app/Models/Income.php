<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Income extends Model
{
    protected $fillable = [
        'account_id',
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
