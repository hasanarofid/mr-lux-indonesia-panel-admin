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
        $deliveryNotes = \Illuminate\Support\Facades\DB::table('delivery_notes')->whereNotNull('customer_id')->get();

        foreach ($deliveryNotes as $dn) {
            \Illuminate\Support\Facades\DB::table('delivery_note_customer')->insert([
                'delivery_note_id' => $dn->id,
                'customer_id' => $dn->customer_id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
