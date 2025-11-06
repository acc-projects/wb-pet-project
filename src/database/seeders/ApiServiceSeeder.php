<?php
// database/seeders/ApiServiceSeeder.php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ApiServiceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $apiServices = [
            [
                'name' => 'Wildberries',
                'code' => 'wb',
                'base_url' => env('WB_API_BASE_URL'),
                'description' => 'Wildberries Marketplace API',
                'is_active' => true,
                'supported_token_types' => json_encode(['bearer']),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Ozon',
                'code' => 'ozon',
                'base_url' => 'https://api-seller.ozon.ru',
                'description' => 'Ozon Marketplace API',
                'is_active' => true,
                'supported_token_types' => json_encode(['api_key', 'bearer']),
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('api_services')->insert($apiServices);

        $this->command->info('API services seeded successfully.');
    }
}
