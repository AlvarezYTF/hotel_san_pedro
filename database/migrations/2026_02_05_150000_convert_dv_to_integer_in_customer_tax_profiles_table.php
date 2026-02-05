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
            // Convertir el campo dv de varchar a integer
            $table->integer('dv')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('customer_tax_profiles', function (Blueprint $table) {
            // Revertir a varchar
            $table->string('dv')->nullable()->change();
        });
    }
};
