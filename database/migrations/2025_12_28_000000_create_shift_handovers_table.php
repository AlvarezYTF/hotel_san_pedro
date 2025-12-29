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
        Schema::create('shift_handovers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('entregado_por')->constrained('users');
            $table->foreignId('recibido_por')->nullable()->constrained('users');
            $table->string('shift_type'); // dia, noche (from ShiftType enum)
            $table->date('shift_date');
            
            $table->dateTime('started_at');
            $table->dateTime('ended_at')->nullable();
            $table->dateTime('received_at')->nullable();
            
            $table->decimal('base_inicial', 12, 2)->default(0);
            $table->decimal('base_final', 12, 2)->default(0);
            $table->decimal('base_recibida', 12, 2)->default(0);
            
            $table->decimal('total_entradas_efectivo', 12, 2)->default(0);
            $table->decimal('total_entradas_transferencia', 12, 2)->default(0);
            $table->decimal('total_salidas', 12, 2)->default(0);
            
            $table->decimal('base_esperada', 12, 2)->default(0);
            $table->decimal('diferencia', 12, 2)->default(0);
            
            $table->text('observaciones_entrega')->nullable();
            $table->text('observaciones_recepcion')->nullable();
            
            $table->string('status')->default('activo'); // activo, entregado, recibido, cerrado
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shift_handovers');
    }
};

