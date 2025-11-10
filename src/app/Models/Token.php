<?php

namespace App\Models;

use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Crypt;

class Token extends Model
{
    /** @use HasFactory<\Database\Factories\TokenFactory> */
    use HasFactory;

    protected $fillable = [
        'account_id',
        'token_type_id',
        'name',
        'credentials',
        'expires_at',
        'last_used_at',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'expires_at' => 'datetime',
        'last_used_at' => 'datetime'
    ];

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    public function tokenType(): BelongsTo
    {
        return $this->belongsTo(TokenType::class);
    }

    /**
     * Шифрование credentials при сохранении
     */
    public function setCredentialsAttribute($value): void
    {
        $this->attributes['credentials'] = Crypt::encryptString($value);
    }

    /**
     * Дешифровка credentials при чтении
     */
    public function getCredentialsAttribute($value): ?string
    {
        try {
            return $value ? Crypt::decryptString($value) : null;
        } catch (DecryptException $e) {
            return null;
        }
    }

    /**
     * Проверить, не истек ли срок действия токена
     */
    public function isExpired(): bool
    {
        if (!$this->expires_at) {
            return false;
        }

        return $this->expires_at->isPast();
    }

    /**
     * Пометить токен как использованный
     */
    public function markAsUsed(): bool
    {
        return $this->update(['last_used_at' => now()]);
    }

    /**
     * Получить учетные данные в структурированном виде
     */
    public function getStructuredCredentials(): array
    {
        $rawCredentials = $this->credentials;

        if (!$rawCredentials || !$this->tokenType) {
            return [];
        }

        return $this->tokenType->getCredentialsStructure($rawCredentials);
    }

    /**
     * Получить правила валидации для этого типа токена
     */
    public function getValidationRules(): array
    {
        return $this->tokenType
            ? $this->tokenType->getValidationRules()
            : ['required', 'string'];
    }

    /**
     * Валидация credentials этого токена
     */
    public function validateCredentials(): bool
    {
        $rawCredentials = $this->credentials;

        if (!$rawCredentials || !$this->tokenType) {
            return false;
        }

        return $this->tokenType->validateCredentials($rawCredentials);
    }

    /**
     * Scope для активных токенов
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope для не истекших токенов
     */
    public function scopeNotExpired($query)
    {
        return $query->where(function($q) {
            $q->whereNull('expires_at')
                ->orWhere('expires_at', '>', now());
        });
    }

    /**
     * Scope для готовых к использованию токенов (активные + не истекшие)
     */
    public function scopeReadyForUse($query)
    {
        return $query->active()->notExpired();
    }
}
