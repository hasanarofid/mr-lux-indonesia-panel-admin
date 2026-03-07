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
        Schema::table('delivery_notes', function (Blueprint $table) {
            $table->foreignId('sale_id')->nullable()->change();
            $table->foreignId('customer_id')->nullable()->constrained()->after('sale_id');
            $table->string('type')->default('AUTOMATIC')->after('number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('delivery_notes', function (Blueprint $table) {
            $table->dropForeign(['customer_id']);
            $table->dropColumn(['customer_id', 'type']);
            $table->foreignId('sale_id')->nullable(false)->change();
        });
    }
};
