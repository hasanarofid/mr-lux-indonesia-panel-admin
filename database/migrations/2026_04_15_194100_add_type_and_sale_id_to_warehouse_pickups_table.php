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
        Schema::table('warehouse_pickups', function (Blueprint $table) {
            $table->string('type')->default('manual')->after('number'); // manual, invoice
            $table->foreignId('sale_id')->nullable()->after('type')->constrained()->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('warehouse_pickups', function (Blueprint $table) {
            $table->dropForeign(['sale_id']);
            $table->dropColumn(['type', 'sale_id']);
        });
    }
};
