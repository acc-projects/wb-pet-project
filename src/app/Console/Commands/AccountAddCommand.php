<?php

namespace App\Console\Commands;

use App\Models\Account;
use App\Models\ApiService;
use App\Models\Company;
use Illuminate\Console\Command;

class AccountAddCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'account:add
                            {company_id : ID of the company}
                            {api_service_id : ID of the API service}
                            {name : Account name}
                            {--external_id= : External account ID on the marketplace}
                            {--settings= : Settings as JSON string}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Add a new account to a company for a specific API service';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $companyId = $this->argument('company_id');
        $apiServiceId = $this->argument('api_service_id');
        $name = $this->argument('name');
        $externalId = $this->option('external_id');
        $settings = $this->option('settings');

        $company = Company::find($companyId);
        if (!$company) {
            $this->error("Company with ID {$companyId} not found!");
            return;
        }

        $apiService = ApiService::find($apiServiceId);
        if (!$apiService) {
            $this->error("API service with ID {$apiServiceId} not found!");
            return;
        }

        // Проверяем, не существует ли уже аккаунт с таким именем для этой компании и сервиса
        if (Account::where('company_id', $companyId)
            ->where('api_service_id', $apiServiceId)
            ->where('name', $name)
            ->exists()) {
            $this->error("Account with name '{$name}' already exists for this company and API service!");
            return;
        }

        if (!$externalId) {
            $externalId = $this->ask('Please enter external account ID (optional)');
        }

        // Обрабатываем настройки
        $settingsArray = [];

        if (!$settings) {
            $settingsInput = $this->ask('Enter account settings as JSON string (optional)');
            if ($settingsInput) {
                try {
                    $settingsArray = json_decode($settingsInput, true, 512, JSON_THROW_ON_ERROR);
                } catch (\JsonException $e) {
                    $this->error("Invalid JSON format for settings!");
                    return;
                }
            }
        } else {
            try {
                $settingsArray = json_decode($settings, true, 512, JSON_THROW_ON_ERROR);
            } catch (\JsonException $e) {
                $this->error("Invalid JSON format for settings!");
                return;
            }
        }

        try {
            $account = Account::create([
                'company_id' => $companyId,
                'api_service_id' => $apiServiceId,
                'name' => $name,
                'external_id' => $externalId,
                'settings' => $settingsArray,
            ]);

            $externalId = $account->external_id ?? 'Not set';

            $this->info("✅ Account '{$account->name}' created successfully!");
            $this->line("   ID: {$account->id}");
            $this->line("   Company: {$company->name}");
            $this->line("   API Service: {$apiService->name}");
            $this->line("   External ID: {$externalId}");

        } catch (\Exception $e) {
            $this->error("Failed to create account: " . $e->getMessage());
        }
    }
}
