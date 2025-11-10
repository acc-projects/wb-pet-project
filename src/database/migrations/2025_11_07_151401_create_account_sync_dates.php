<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('account_sync_dates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('account_id')->constrained()->onDelete('cascade');
            $table->string('entity_type'); // orders, sales, stocks, incomes
            $table->datetime('last_sync_date')->nullable();
            $table->timestamps();

            $table->unique(['account_id', 'entity_type']);
            $table->index('account_id');
            $table->index('entity_type');
            $table->index('last_sync_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('account_sync_dates');
    }
};
