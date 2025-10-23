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
        Schema::create('sales', function (Blueprint $table) {
            $table->id();

            $table->string('sale_id')->unique();
            $table->date('sale_date');
            $table->decimal('total_price', 12, 2);
            $table->decimal('for_pay', 12, 2);
            $table->decimal('finished_price', 12, 2);
            $table->string('warehouse_name');
            $table->string('region_name');
            $table->string('country_name');

            $table->bigInteger('nm_id');
            $table->string('supplier_article');
            $table->string('product_category');
            $table->string('brand');

            // Сырые данные
            $table->json('raw_data');

            $table->timestamps();

            $table->index('sale_date');
            $table->index('warehouse_name');
            $table->index('nm_id');
            $table->index('region_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sales');
    }
};
