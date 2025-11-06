<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TokenTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $tokenTypes = [
            [
                'name' => 'Bearer Token',
                'code' => 'bearer',
                'description' => 'OAuth2 Bearer token authentication',
                'validation_rules' => json_encode(['required', 'string', 'min:10']),
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'API Key',
                'code' => 'api_key',
                'description' => 'Simple API key authentication',
                'validation_rules' => json_encode(['required', 'string', 'min:5']),
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Login & Password',
                'code' => 'login_password',
                'description' => 'Basic login and password authentication',
                'validation_rules' => json_encode(['required', 'string', 'regex:/^[^:]+:[^:]+$/']),
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('token_types')->insert($tokenTypes);

        $this->command->info('Token types seeded successfully.');
    }
}
