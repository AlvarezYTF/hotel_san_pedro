<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dian_measurement_units', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('factus_id')->unique();
            $table->string('code', 10)->nullable();
            $table->string('name');
            $table->timestamps();
            
            $table->index('factus_id');
            $table->index('code');
            $table->index('name');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dian_measurement_units');
    }
};
