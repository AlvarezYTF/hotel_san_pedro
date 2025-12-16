<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('electronic_invoice_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('electronic_invoice_id');
            $table->foreignId('tribute_id')->nullable()->constrained('dian_customer_tributes')->onDelete('restrict');
            $table->foreignId('standard_code_id')->nullable()->constrained('dian_product_standards')->onDelete('restrict');
            
            // Unidad de medida (OBLIGATORIO)
            $table->unsignedBigInteger('unit_measure_id');
            
            // Información del item
            $table->string('code_reference')->nullable();
            $table->string('name');
            $table->decimal('quantity', 10, 3);
            $table->decimal('price', 15, 2);
            
            // Impuestos y descuentos
            $table->decimal('tax_rate', 5, 2)->default(0);
            $table->decimal('tax_amount', 15, 2)->default(0);
            $table->decimal('discount_rate', 5, 2)->default(0);
            $table->decimal('total', 15, 2);
            
            $table->timestamps();
            
            $table->index('electronic_invoice_id');
            $table->index('tribute_id');
            $table->index('unit_measure_id');
        });
    }

    public function down(): void
    {
        if (Schema::hasTable('electronic_invoice_items')) {
            // Drop foreign keys if they exist
            try {
                Schema::table('electronic_invoice_items', function (Blueprint $table) {
                    $table->dropForeign(['electronic_invoice_id']);
                });
            } catch (\Exception $e) {
                // Foreign key might not exist or already dropped
            }
            
            try {
                Schema::table('electronic_invoice_items', function (Blueprint $table) {
                    $table->dropForeign(['tribute_id']);
                });
            } catch (\Exception $e) {
                // Foreign key might not exist or already dropped
            }
            
            try {
                Schema::table('electronic_invoice_items', function (Blueprint $table) {
                    $table->dropForeign(['standard_code_id']);
                });
            } catch (\Exception $e) {
                // Foreign key might not exist or already dropped
            }
        }
        
        Schema::dropIfExists('electronic_invoice_items');
    }
};
