<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * API сервисы - это маркетплейсы (Wildberries, Ozon и т.д.)
     * У каждого сервиса свой API и свои требования к авторизации.
     */
    public function up(): void
    {
        Schema::create('api_services', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->unique(); // Уникальный код: "wb", "ozon"
            $table->string('base_url');
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->json('supported_token_types')->nullable();
            $table->timestamps();

            $table->index('code');
            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('api_services');
    }
};
