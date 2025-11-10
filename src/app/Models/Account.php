<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Account extends Model
{
    /** @use HasFactory<\Database\Factories\AccountFactory> */
    use HasFactory;

    protected $fillable = [
        'company_id',
        'api_service_id',
        'name',
        'external_id',
        'settings',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'settings' => 'array'
    ];

    /**
     * Аккаунт принадлежит компании
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Аккаунт принадлежит API сервису
     */
    public function apiService(): BelongsTo
    {
        return $this->belongsTo(ApiService::class);
    }

    /**
     * У аккаунта может быть несколько токенов
     */
    public function tokens(): HasMany
    {
        return $this->hasMany(Token::class);
    }

    /**
     * Получить активные токены аккаунта
     */
    public function activeTokens(): HasMany
    {
        return $this->tokens()->where('is_active', true);
    }
}
