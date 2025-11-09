<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AccountSyncDate extends Model
{
    protected $fillable = [
        'account_id',
        'entity_type',
        'last_sync_date',
    ];

    protected $casts = [
        'last_sync_date' => 'datetime',
    ];

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    public function updateSyncDate(?\DateTime $date = null): bool
    {
        $this->last_sync_date = $date ?: now();
        return $this->save();
    }

    public static function findOrCreate(int $accountId, string $entityType): self
    {
        return static::firstOrCreate(
            ['account_id' => $accountId, 'entity_type' => $entityType],
            ['last_sync_date' => null]
        );
    }
}
