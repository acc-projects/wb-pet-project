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
        Schema::create('stocks', function (Blueprint $table) {
            $table->id();

            $table->date('stock_date');
            $table->string('warehouse_name');
            $table->bigInteger('nm_id');
            $table->integer('quantity');
            $table->integer('in_way_to_client');
            $table->integer('in_way_from_client');
            $table->decimal('price', 12, 2);
            $table->integer('discount_percent');

            $table->string('supplier_article');
            $table->string('product_category');
            $table->string('brand');

            // Сырые данные
            $table->json('raw_data');

            $table->timestamps();

            // Уникальность: на одну дату + склад + товар
            $table->unique(['stock_date', 'warehouse_name', 'nm_id']);

            $table->index('stock_date');
            $table->index('warehouse_name');
            $table->index('nm_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stocks');
    }
};
