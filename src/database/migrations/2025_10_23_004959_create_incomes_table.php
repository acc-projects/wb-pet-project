<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('incomes', function (Blueprint $table) {
            $table->id();

            $table->bigInteger('income_id');
            $table->date('income_date');
            $table->date('date_close');
            $table->string('warehouse_name');
            $table->bigInteger('nm_id');
            $table->integer('quantity');
            $table->decimal('total_price', 12, 2);

            $table->string('supplier_article');

            // Сырые данные
            $table->json('raw_data');

            $table->timestamps();

            $table->index('income_date');
            $table->index('warehouse_name');
            $table->index('nm_id');
            $table->index('income_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('incomes');
    }
};
