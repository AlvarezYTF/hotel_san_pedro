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
        Schema::table('customer_tax_profiles', function (Blueprint $table) {
            $table->string('address')->nullable()->after('municipality_id');
            $table->string('email')->nullable()->after('address');
            $table->string('phone')->nullable()->after('email');
            $table->string('names')->nullable()->after('trade_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('customer_tax_profiles', function (Blueprint $table) {
            $table->dropColumn(['address', 'email', 'phone', 'names']);
        });
    }
};
