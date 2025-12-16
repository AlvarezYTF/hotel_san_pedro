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
            // Agregar nuevas columnas
            $table->string('city')->nullable()->after('address');
            $table->string('state')->nullable()->after('city');
            $table->string('zip_code')->nullable()->after('state');
            $table->text('notes')->nullable()->after('zip_code');
            
            // Eliminar columnas que no necesitamos
            $table->dropColumn(['identification', 'type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            // Revertir cambios
            $table->dropColumn(['city', 'state', 'zip_code', 'notes']);
            $table->string('identification')->nullable();
            $table->string('type')->nullable();
        });
    }
};
