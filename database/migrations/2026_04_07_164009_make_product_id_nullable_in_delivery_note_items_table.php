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
        Schema::table('delivery_note_items', function (Blueprint $table) {
            $table->foreignId('product_id')->nullable()->change();
            $table->string('description')->nullable()->change();
            $table->string('unit')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('delivery_note_items', function (Blueprint $table) {
            $table->foreignId('product_id')->nullable(false)->change();
            $table->string('description')->nullable(false)->change();
            $table->string('unit')->nullable(false)->change();
        });
    }
};
