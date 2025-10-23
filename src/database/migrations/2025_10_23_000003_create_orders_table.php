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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();

            $table->string('g_number')->unique();
            $table->dateTime('order_date');
            $table->decimal('total_price', 12, 2);
            $table->integer('discount_percent');
            $table->string('warehouse_name');
            $table->string('region');
            $table->boolean('is_cancelled')->default(false);
            $table->dateTime('cancelled_at')->nullable();

            $table->bigInteger('nm_id');
            $table->string('supplier_article');
            $table->string('product_category');
            $table->string('brand');

            // Сырые данные
            $table->json('raw_data');

            $table->timestamps();

            $table->index('order_date');
            $table->index('warehouse_name');
            $table->index('nm_id');
            $table->index('is_cancelled');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
