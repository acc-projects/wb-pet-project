<?php

namespace App\Console\Commands;

use App\Models\Account;
use App\Models\Token;
use App\Models\TokenType;
use Carbon\Carbon;
use Illuminate\Console\Command;

class TokenAddCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'token:add
                            {account_id : ID of the account}
                            {token_type_id : ID of the token type}
                            {credentials : The actual token credentials}
                            {--name= : Token name}
                            {--expires-at= : Expiration date (YYYY-MM-DD)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Add a new token to an account';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $accountId = $this->argument('account_id');
        $tokenTypeId = $this->argument('token_type_id');
        $credentials = $this->argument('credentials');
        $name = $this->option('name');
        $expiresAt = $this->option('expires-at');

        $account = Account::with(['company', 'apiService'])->find($accountId);
        if (!$account) {
            $this->error("Account with ID {$accountId} not found!");
            return;
        }

        $tokenType = TokenType::find($tokenTypeId);
        if (!$tokenType) {
            $this->error("Token type with ID {$tokenTypeId} not found!");
            return;
        }

        // Проверяем, поддерживается ли этот тип токена API сервисом
        if (!$account->apiService->supportsTokenType($tokenType)) {
            $this->error("Token type '{$tokenType->name}' is not supported by API service '{$account->apiService->name}'!");
            $this->line("Supported types: " . implode(', ', $account->apiService->supported_token_types ?? []));
            return;
        }

        // Валидируем credentials
        if (!$tokenType->validateCredentials($credentials)) {
            $this->error("Invalid credentials for token type '{$tokenType->name}'!");
            $errors = $tokenType->getValidationMessages($credentials);
            foreach ($errors as $error) {
                $this->line(" - {$error}");
            }
            return;
        }

        // Если имя не указано, генерируем его
        if (!$name) {
            $name = "{$tokenType->name} for {$account->name}";
        }

        // Обрабатываем дату истечения
        $expiresAtDate = null;
        if ($expiresAt) {
            try {
                $expiresAtDate = Carbon::createFromFormat('Y-m-d', $expiresAt);
            } catch (\Exception $e) {
                $this->error("Invalid date format. Use YYYY-MM-DD.");
                return;
            }
        }

        // Проверяем, не существует ли уже активный токен такого типа для этого аккаунта
        $existingToken = Token::where('account_id', $accountId)
            ->where('token_type_id', $tokenTypeId)
            ->where('is_active', true)
            ->first();

        if ($existingToken) {
            if (!$this->confirm("An active token of this type already exists for this account. Do you want to deactivate it and create a new one?")) {
                $this->info("Token creation cancelled.");
                return;
            }

            $existingToken->update(['is_active' => false]);
        }

        try {
            $token = Token::create([
                'account_id' => $accountId,
                'token_type_id' => $tokenTypeId,
                'name' => $name,
                'credentials' => $credentials,
                'expires_at' => $expiresAtDate,
            ]);

            $expiresAt = $token->expires_at ? $token->expires_at->format('Y-m-d') : 'Never';

            $this->info("✅ Token '{$token->name}' created successfully!");
            $this->line("   Account: {$account->name} ({$account->company->name})");
            $this->line("   API Service: {$account->apiService->name}");
            $this->line("   Token Type: {$tokenType->name}");
            $this->line("   Expires: {$expiresAt}");

        } catch (\Exception $e) {
            $this->error("Failed to create token: " . $e->getMessage());
        }
    }
}
