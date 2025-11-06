<?php

namespace App\Console\Commands;

use App\Models\TokenType;
use Exception;
use Illuminate\Console\Command;
use JsonException;

class TokenTypeAddCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'token-type:add
                            {name : The name of the token type}
                            {code : Unique code for the token type}
                            {--description= : Token type description}
                            {--validation-rules= : Validation rules as JSON array}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Add a new token type to the system';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $name = $this->argument('name');
        $code = $this->argument('code');
        $description = $this->option('description');
        $validationRules = $this->option('validation-rules');

        if (TokenType::where('code', $code)->exists()) {
            $this->error("Token type with code '{$code}' already exists!");
            return;
        }

        if (!$description) {
            $description = $this->ask('Please enter token type description (optional)');
        }

        // Обрабатываем правила валидации
        $rulesArray = [];

        if (!$validationRules) {
            $this->info("Common validation rules examples:");
            $this->line(" - Bearer token: ['required', 'string', 'min:10']");
            $this->line(" - API Key: ['required', 'string', 'min:5']");
            $this->line(" - Login/Password: ['required', 'string', 'regex:/^[^:]+:[^:]+$/']");

            $rulesInput = $this->ask('Enter validation rules as JSON array (or press enter for default rules)');

            if ($rulesInput) {
                try {
                    $rulesArray = json_decode($rulesInput, true, 512, JSON_THROW_ON_ERROR);
                } catch (JsonException $e) {
                    $this->error("Invalid JSON format for validation rules!");
                    return;
                }
            }
        } else {
            try {
                $rulesArray = json_decode($validationRules, true, 512, JSON_THROW_ON_ERROR);
            } catch (JsonException $e) {
                $this->error("Invalid JSON format for validation rules!");
                return;
            }
        }

        // Создаем тип токена
        try {
            $tokenType = TokenType::create([
                'name' => $name,
                'code' => $code,
                'description' => $description,
                'validation_rules' => $rulesArray,
            ]);

            $this->info("✅ Token type '{$tokenType->name}' created successfully!");
            $this->line("   Code: {$tokenType->code}");
            $this->line("   Validation rules: " . json_encode($rulesArray ?: 'Default rules will be used'));

        } catch (Exception $e) {
            $this->error("Failed to create token type: " . $e->getMessage());
        }
    }
}
