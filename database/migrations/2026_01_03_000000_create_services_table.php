<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('services', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code_reference', 100)->nullable()->unique();
            $table->text('description')->nullable();
            
            // Relaciones con catálogos DIAN
            $table->foreignId('standard_code_id')->nullable()->constrained('dian_product_standards')->onDelete('restrict');
            $table->foreignId('unit_measure_id')->constrained('dian_measurement_units', 'factus_id')->onDelete('restrict');
            $table->foreignId('tribute_id')->nullable()->constrained('dian_customer_tributes')->onDelete('restrict');
            
            // Precio e impuestos
            $table->decimal('price', 15, 2);
            $table->decimal('tax_rate', 5, 2)->default(0);
            
            // Estado
            $table->boolean('is_active')->default(true);
            
            $table->timestamps();
            $table->softDeletes();
            
            // Índices
            $table->index('is_active');
            $table->index('code_reference');
            $table->index('standard_code_id');
            $table->index('unit_measure_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('services');
    }
};

