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
        Schema::table('stocks', function (Blueprint $table) {
            $table->foreignId('account_id')->after('id')->constrained()->onDelete('cascade');
            $table->index('account_id');

            $table->dropUnique(['stock_date', 'warehouse_name', 'nm_id']);
            $table->unique(['account_id', 'stock_date', 'warehouse_name', 'nm_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('stocks', function (Blueprint $table) {
            $table->dropUnique(['account_id', 'stock_date', 'warehouse_name', 'nm_id']);
            $table->unique(['stock_date', 'warehouse_name', 'nm_id']);
            $table->dropForeign(['account_id']);
            $table->dropColumn('account_id');
        });
    }
};
