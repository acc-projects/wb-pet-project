<?php

namespace App\Console\Commands;

use App\Models\ApiService;
use App\Models\TokenType;
use Illuminate\Console\Command;

class ApiServiceAddCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'api-service:add
                            {name : The name of the API service}
                            {code : Unique code for the service (e.g., wb, ozon)}
                            {base_url : Base URL for the API}
                            {--description= : Service description}
                            {--token-types=* : Supported token types (codes separated by comma)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Add a new API service (marketplace) to the system';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $name = $this->argument('name');
        $code = $this->argument('code');
        $baseUrl = $this->argument('base_url');
        $description = $this->option('description');
        $tokenTypes = $this->option('token-types');

        // Проверяем, не существует ли уже сервис с таким кодом
        if (ApiService::where('code', $code)->exists()) {
            $this->error("API service with code '{$code}' already exists!");
            return;
        }

        if (!$description) {
            $description = $this->ask('Please enter service description (optional)');
        }

        // Обрабатываем типы токенов
        $supportedTokenTypes = [];

        if (empty($tokenTypes)) {
            $this->info("Available token types:");
            $availableTypes = TokenType::active()->get();

            foreach ($availableTypes as $type) {
                $this->line(" - {$type->code}: {$type->name}");
            }

            $selectedTypes = $this->ask('Enter supported token types (comma-separated codes, or press enter for none)');

            if ($selectedTypes) {
                $supportedTokenTypes = array_map('trim', explode(',', $selectedTypes));
            }
        } else {
            $supportedTokenTypes = is_array($tokenTypes) ? $tokenTypes : [$tokenTypes];
        }

        // Валидируем типы токенов
        $validTokenTypes = [];
        foreach ($supportedTokenTypes as $typeCode) {
            $tokenType = TokenType::where('code', $typeCode)->first();
            if ($tokenType) {
                $validTokenTypes[] = $typeCode;
            } else {
                $this->warn("Token type '{$typeCode}' not found, skipping...");
            }
        }

        try {
            $apiService = ApiService::create([
                'name' => $name,
                'code' => $code,
                'base_url' => $baseUrl,
                'description' => $description,
                'supported_token_types' => $validTokenTypes,
            ]);

            $this->info("✅ API Service '{$apiService->name}' created successfully!");
            $this->line("   Code: {$apiService->code}");
            $this->line("   Base URL: {$apiService->base_url}");
            $this->line("   Supported token types: " . implode(', ', $validTokenTypes ?: ['None']));

        } catch (\Exception $e) {
            $this->error("Failed to create API service: " . $e->getMessage());
        }
    }
}
