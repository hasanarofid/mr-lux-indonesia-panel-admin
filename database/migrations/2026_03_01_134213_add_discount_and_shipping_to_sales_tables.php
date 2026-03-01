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
        Schema::table('sales', function (Blueprint $table) {
            $table->decimal('shipping_cost', 15, 2)->default(0)->after('ppn_amount');
        });

        Schema::table('sale_items', function (Blueprint $table) {
            $table->decimal('discount_percent', 5, 2)->default(0)->after('price');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->dropColumn('shipping_cost');
        });

        Schema::table('sale_items', function (Blueprint $table) {
            $table->dropColumn('discount_percent');
        });
    }
};
