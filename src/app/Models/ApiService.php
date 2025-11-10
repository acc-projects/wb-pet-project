<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ApiService extends Model
{
    /** @use HasFactory<\Database\Factories\ApiServiceFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'base_url',
        'description',
        'is_active',
        'supported_token_types'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'supported_token_types' => 'array' // JSON поле как массив
    ];

    /**
     * У сервиса может быть много аккаунтов
     */
    public function accounts(): HasMany
    {
        return $this->hasMany(Account::class);
    }

    /**
     * У сервиса может быть много токенов (через аккаунты)
     */
    public function tokens(): HasMany
    {
        return $this->hasMany(Token::class);
    }

    /**
     * Получить поддерживаемые типы токенов как коллекцию моделей TokenType
     */
    public function getSupportedTokenTypes(): Collection
    {
        $supportedCodes = $this->supported_token_types ?? [];

        return TokenType::whereIn('code', $supportedCodes)
            ->active()
            ->get();
    }

    /**
     * Проверить, поддерживается ли тип токена
     */
    public function supportsTokenType(TokenType $tokenType): bool
    {
        $supportedCodes = $this->supported_token_types ?? [];

        return in_array($tokenType->code, $supportedCodes);
    }

    /**
     * Добавить поддержку типа токена
     */
    public function addSupportedTokenType(TokenType $tokenType): bool
    {
        $supportedCodes = $this->supported_token_types ?? [];

        if (!in_array($tokenType->code, $supportedCodes)) {
            $supportedCodes[] = $tokenType->code;
            $this->supported_token_types = $supportedCodes;
            return $this->save();
        }

        return true;
    }

    /**
     * Удалить поддержку типа токена
     */
    public function removeSupportedTokenType(TokenType $tokenType): bool
    {
        $supportedCodes = $this->supported_token_types ?? [];

        $key = array_search($tokenType->code, $supportedCodes);
        if ($key !== false) {
            unset($supportedCodes[$key]);
            $this->supported_token_types = array_values($supportedCodes);
            return $this->save();
        }

        return true;
    }

    /**
     * Scope для активных сервисов
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Найти сервис по коду
     */
    public static function findByCode(string $code): ?self
    {
        return static::where('code', $code)->first();
    }
}
