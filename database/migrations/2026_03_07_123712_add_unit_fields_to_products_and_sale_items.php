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
        Schema::table('products', function (Blueprint $table) {
            $table->integer('isi_set')->default(1)->after('isi');
            $table->decimal('price_per_set', 15, 2)->default(0)->after('price_per_carton');
        });

        Schema::table('sale_items', function (Blueprint $table) {
            $table->string('unit')->default('PCS')->after('product_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['isi_set', 'price_per_set']);
        });

        Schema::table('sale_items', function (Blueprint $table) {
            $table->dropColumn('unit');
        });
    }
};
