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
        Schema::table('customers', function (Blueprint $table) {
            $table->string('category')->nullable()->after('group');
            $table->string('phone_business')->nullable()->after('phone');
            $table->string('handphone')->nullable()->after('phone_business');
            $table->string('whatsapp')->nullable()->after('handphone');
            $table->string('email')->nullable()->after('whatsapp');
            $table->string('fax')->nullable()->after('email');
            $table->string('website')->nullable()->after('fax');
            $table->text('billing_street')->nullable()->after('address');
            $table->string('billing_city')->nullable()->after('billing_street');
            $table->string('billing_postcode')->nullable()->after('billing_city');
            $table->string('billing_province')->nullable()->after('billing_postcode');
            $table->string('billing_country')->nullable()->after('billing_province');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropColumn([
                'category',
                'phone_business',
                'handphone',
                'whatsapp',
                'email',
                'fax',
                'website',
                'billing_street',
                'billing_city',
                'billing_postcode',
                'billing_province',
                'billing_country',
            ]);
        });
    }
};
