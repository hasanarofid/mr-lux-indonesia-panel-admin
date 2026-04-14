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
        Schema::create('warehouse_pickups', function (Blueprint $table) {
            $table->id();
            $table->string('number')->unique();
            $table->string('driver_name');
            $table->string('vehicle_number')->nullable();
            $table->string('address')->nullable();
            $table->text('note');
            $table->string('status')->default('picked_up'); // picked_up, returned, completed
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('warehouse_pickup_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('warehouse_pickup_id')->constrained()->onDelete('cascade');
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->integer('quantity');
            $table->integer('returned_quantity')->default(0);
            $table->string('unit')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('warehouse_pickup_items');
        Schema::dropIfExists('warehouse_pickups');
    }
};
