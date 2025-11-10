<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Токены доступа к API маркетплейсов.
     * У одного аккаунта может быть несколько токенов разного типа.
     */
    public function up(): void
    {
        Schema::create('tokens', function (Blueprint $table) {
            $table->id();
            $table->foreignId('account_id')->constrained()->onDelete('cascade'); // Принадлежит аккаунту
            $table->foreignId('token_type_id')->constrained()->onDelete('cascade');
            $table->string('name')->nullable();

            $table->text('credentials')->nullable(); // Зашифрованные учетные данные

            // Мета-информация
            $table->datetime('expires_at')->nullable(); // Срок действия
            $table->datetime('last_used_at')->nullable(); // Когда последний раз использовался
            $table->boolean('is_active')->default(true);

            $table->timestamps();

            // Уникальность: один аккаунт + один тип токена = один активный токен
            $table->unique(['account_id', 'token_type_id', 'is_active']);

            $table->index('account_id');
            $table->index('token_type_id');
            $table->index('is_active');
            $table->index('expires_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tokens');
    }
};
