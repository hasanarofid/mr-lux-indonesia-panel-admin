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
        Schema::table('stock_entry_items', function (Blueprint $table) {
            $table->integer('quantity_carton')->default(0)->after('product_id');
            $table->integer('quantity_unit')->default(0)->after('quantity_carton');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('stock_entry_items', function (Blueprint $table) {
            $table->dropColumn(['quantity_carton', 'quantity_unit']);
        });
    }
};
