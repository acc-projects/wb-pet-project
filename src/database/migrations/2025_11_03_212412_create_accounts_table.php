<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Аккаунты - это привязка компании к конкретному маркетплейсу.
     * Например: "Компания А" → "Аккаунт WB магазин №1"
     */
    public function up(): void
    {
        Schema::create('accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->onDelete('cascade'); // Принадлежит компании
            $table->foreignId('api_service_id')->constrained()->onDelete('cascade'); // Для какого API сервиса
            $table->string('name');
            $table->string('external_id')->nullable(); // Внешний ID аккаунта на маркетплейсе
            $table->json('settings')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            // Одна компания не может иметь два аккаунта с одинаковым именем для одного сервиса
            $table->unique(['company_id', 'api_service_id', 'name']);

            $table->index('company_id');
            $table->index('api_service_id');
            $table->index('is_active');
            $table->index('external_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('accounts');
    }
};
