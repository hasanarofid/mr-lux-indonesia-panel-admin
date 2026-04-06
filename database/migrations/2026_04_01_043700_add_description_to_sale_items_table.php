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
        Schema::table('sale_items', function (Blueprint $table) {
            // Make product_id nullable to allow manual items without a product
            $table->foreignId('product_id')->nullable()->change();
            
            // Add description column for manual items if missing
            if (!Schema::hasColumn('sale_items', 'description')) {
                $table->string('description')->nullable()->after('product_id');
            }
            
            // Add discount_percent column if missing
            if (!Schema::hasColumn('sale_items', 'discount_percent')) {
                $table->decimal('discount_percent', 5, 2)->default(0)->after('quantity');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sale_items', function (Blueprint $table) {
            $table->dropColumn('description');
            $table->dropColumn('discount_percent');
            $table->foreignId('product_id')->nullable(false)->change();
        });
    }
};
