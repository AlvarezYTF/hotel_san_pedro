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
        Schema::create('shift_cash_outs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shift_handover_id')->constrained('shift_handovers')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users');
            
            $table->decimal('amount', 12, 2);
            $table->string('concept');
            $table->text('observations')->nullable();
            
            $table->string('shift_type');
            $table->date('shift_date');
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shift_cash_outs');
    }
};

