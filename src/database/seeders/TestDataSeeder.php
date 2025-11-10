<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class TestDataSeeder extends Seeder
{
    /**
     * Создает минимальный набор данных для тестирования команд выгрузки
     */
    public function run(): void
    {
        $this->command->info('🔄 Creating test data...');

        $companies = [
            [
                'name' => 'ООО Ромашка',
                'contact_email' => 'contact@romashka.ru',
                'description' => 'Продажа цветов и растений',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'ИП Иванов',
                'contact_email' => 'ivanov@mail.ru',
                'description' => 'Индивидуальный предприниматель',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'ООО ТехноПрофи',
                'contact_email' => 'info@technoprofi.ru',
                'description' => 'IT компания',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ];

        $companyIds = [];
        foreach ($companies as $company) {
            $existingCompany = DB::table('companies')->where('name', $company['name'])->first();
            if (!$existingCompany) {
                $companyId = DB::table('companies')->insertGetId($company);
                $companyIds[] = $companyId;
                $this->command->info("✅ Created company: {$company['name']}");
            } else {
                $companyIds[] = $existingCompany->id;
            }
        }

        // Получаем ID API сервисов (должны быть созданы в ApiServiceSeeder)
        $wbService = DB::table('api_services')->where('code', 'wb')->first();
        $ozonService = DB::table('api_services')->where('code', 'ozon')->first();

        if (!$wbService) {
            $this->command->error('❌ WB API service not found! Run ApiServiceSeeder first.');
            return;
        }

        $accounts = [
            [
                'company_id' => $companyIds[0],
                'api_service_id' => $wbService->id,
                'name' => 'Основной WB магазин',
                'external_id' => 'WB123456',
                'settings' => json_encode(['timezone' => 'Europe/Moscow']),
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'company_id' => $companyIds[0],
                'api_service_id' => $wbService->id,
                'name' => 'Резервный WB магазин',
                'external_id' => 'WB123457',
                'settings' => json_encode(['timezone' => 'Europe/Moscow']),
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'company_id' => $companyIds[1],
                'api_service_id' => $wbService->id,
                'name' => 'WB магазин ИП Иванов',
                'external_id' => 'WB789012',
                'settings' => json_encode(['timezone' => 'Europe/Moscow']),
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'company_id' => $companyIds[0],
                'api_service_id' => $ozonService->id,
                'name' => 'Ozon магазин Ромашка',
                'external_id' => 'OZ123456',
                'settings' => json_encode(['timezone' => 'Europe/Moscow']),
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ];

        $accountIds = [];
        foreach ($accounts as $account) {
            $existingAccount = DB::table('accounts')
                ->where('company_id', $account['company_id'])
                ->where('api_service_id', $account['api_service_id'])
                ->where('name', $account['name'])
                ->first();

            if (!$existingAccount) {
                $accountId = DB::table('accounts')->insertGetId($account);
                $accountIds[] = $accountId;
                $this->command->info("✅ Created account: {$account['name']}");
            } else {
                $accountIds[] = $existingAccount->id;
            }
        }

        // Получаем ID типа токена bearer
        $bearerTokenType = DB::table('token_types')->where('code', 'bearer')->first();
        $apiKeyTokenType = DB::table('token_types')->where('code', 'api_key')->first();

        if (!$bearerTokenType) {
            $this->command->error('❌ Bearer token type not found! Run TokenTypeSeeder first.');
            return;
        }

        // Создаем тестовые токены
        $tokens = [
            [
                'account_id' => $accountIds[0],
                'token_type_id' => $bearerTokenType->id,
                'name' => 'Основной токен WB',
                'credentials' => Crypt::encryptString(config('services.wb_api.token')),
                'expires_at' => Carbon::now()->addYear(),
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'account_id' => $accountIds[1],
                'token_type_id' => $bearerTokenType->id,
                'name' => 'Резервный токен WB',
                'credentials' => Crypt::encryptString('wb_backup_test_token_67890'),
                'expires_at' => Carbon::now()->addYear(),
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'account_id' => $accountIds[2],
                'token_type_id' => $bearerTokenType->id,
                'name' => 'Токен WB ИП Иванов',
                'credentials' => Crypt::encryptString('wb_ivanov_test_token_54321'),
                'expires_at' => Carbon::now()->addYear(),
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'account_id' => $accountIds[3],
                'token_type_id' => $apiKeyTokenType->id,
                'name' => 'API Key Ozon',
                'credentials' => Crypt::encryptString('ozon_test_api_key_11111'),
                'expires_at' => null,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ];

        foreach ($tokens as $token) {
            $existingToken = DB::table('tokens')
                ->where('account_id', $token['account_id'])
                ->where('token_type_id', $token['token_type_id'])
                ->where('name', $token['name'])
                ->first();

            if (!$existingToken) {
                DB::table('tokens')->insert($token);
                $this->command->info("✅ Created token: {$token['name']}");
            }
        }

        // Создаем тестового пользователя для аутентификации
        $user = [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ];

        $existingUser = DB::table('users')->where('email', $user['email'])->first();
        if (!$existingUser) {
            DB::table('users')->insert($user);
            $this->command->info('✅ Created test user: test@example.com / password');
        }

        $this->command->info('🎉 Test data created successfully!');
        $this->command->info('📋 Account IDs for testing:');
        foreach ($accountIds as $index => $accountId) {
            $this->command->info("   - Account {$accountId}: " . $accounts[$index]['name']);
        }
    }
}
