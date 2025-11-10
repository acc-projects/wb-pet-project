<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Validator;

class TokenType extends Model
{
    /** @use HasFactory<\Database\Factories\TokenTypeFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'description',
        'validation_rules',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'validation_rules' => 'array'
    ];

    /**
     * У типа токена может быть много токенов
     */
    public function tokens(): HasMany
    {
        return $this->hasMany(Token::class);
    }

    /**
     * Получить структуру credentials для этого типа токена
     */
    public function getCredentialsStructure(string $rawCredentials): array
    {
        return match($this->code) {
            'bearer' => $this->parseBearerToken($rawCredentials),
            'api_key' => $this->parseApiKey($rawCredentials),
            'login_password' => $this->parseLoginPassword($rawCredentials),
            default => $this->parseDefault($rawCredentials),
        };
    }

    /**
     * Парсинг Bearer токена
     */
    private function parseBearerToken(string $credentials): array
    {
        return ['token' => $credentials];
    }

    /**
     * Парсинг API Key
     */
    private function parseApiKey(string $credentials): array
    {
        return ['api_key' => $credentials];
    }

    /**
     * Парсинг логина и пароля из строки формата "login:password"
     */
    private function parseLoginPassword(string $credentials): array
    {
        $parts = explode(':', $credentials, 2);

        return [
            'login' => $parts[0] ?? '',
            'password' => $parts[1] ?? ''
        ];
    }

    /**
     * Парсинг по умолчанию
     */
    private function parseDefault(string $credentials): array
    {
        return ['credentials' => $credentials];
    }

    /**
     * Получить правила валидации для этого типа токена
     */
    public function getValidationRules(): array
    {
        // Если правила заданы в БД - используем их
        if ($this->validation_rules) {
            return $this->validation_rules;
        }

        // Иначе используем правила по умолчанию для известных типов
        return match($this->code) {
            'bearer' => ['required', 'string', 'min:10'],
            'api_key' => ['required', 'string', 'min:5'],
            'login_password' => ['required', 'string', 'regex:/^[^:]+:[^:]+$/'],
            default => ['required', 'string'],
        };
    }

    /**
     * Валидация credentials для этого типа токена
     */
    public function validateCredentials(string $credentials): bool
    {
        $rules = $this->getValidationRules();

        $validator = Validator::make(['credentials' => $credentials], [
            'credentials' => $rules
        ]);

        return !$validator->fails();
    }

    /**
     * Получить сообщения об ошибках валидации
     */
    public function getValidationMessages(string $credentials): array
    {
        $rules = $this->getValidationRules();

        $validator = Validator::make(['credentials' => $credentials], [
            'credentials' => $rules
        ]);

        return $validator->errors()->all();
    }

    /**
     * Scope для активных типов токенов
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Найти тип токена по коду
     */
    public static function findByCode(string $code): ?self
    {
        return static::where('code', $code)->first();
    }

    /**
     * Проверить, поддерживается ли тип токена API сервисом
     */
    public function isSupportedByApiService(ApiService $apiService): bool
    {
        $supportedTypes = $apiService->supported_token_types ?? [];

        return in_array($this->code, $supportedTypes);
    }
}
