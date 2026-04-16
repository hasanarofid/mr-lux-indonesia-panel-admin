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
        Schema::create('production_returns', function (Blueprint $table) {
            $table->id();
            $table->string('number')->unique();
            $table->date('date');
            $table->foreignId('warehouse_pickup_id')->constrained('warehouse_pickups')->cascadeOnDelete();
            $table->boolean('is_represented_by_warehouse')->default(false);
            $table->string('driver_name')->nullable();
            $table->string('vehicle_number')->nullable();
            $table->text('note')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('production_return_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('production_return_id')->constrained('production_returns')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->integer('quantity');
            $table->string('unit')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('production_return_items');
        Schema::dropIfExists('production_returns');
    }
};
