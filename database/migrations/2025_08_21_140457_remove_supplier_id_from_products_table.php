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
        Schema::table('products', function (Blueprint $table) {
            // Quitar la columna supplier_id
            $table->dropForeign(['supplier_id']);
            $table->dropColumn('supplier_id');
            
            // Asegurar que el status tenga valor por defecto 'active'
            $table->enum('status', ['active', 'inactive', 'discontinued'])->default('active')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            // Restaurar la columna supplier_id como nullable para evitar problemas
            $table->foreignId('supplier_id')->nullable()->constrained()->onDelete('cascade');
            
            // Revertir el cambio del status
            $table->enum('status', ['active', 'inactive', 'discontinued'])->default('active')->change();
        });
    }
};
