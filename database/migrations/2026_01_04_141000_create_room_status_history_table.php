<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Eliminar versión previa si existía
        Schema::dropIfExists('room_status_history');
        Schema::dropIfExists('room_status_history_statuses');

        // Catálogo de estados
        Schema::create('room_status_history_statuses', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('name');
            $table->timestamps();
        });

        // Catálogo de fuentes
        Schema::create('room_status_history_sources', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('name');
            $table->timestamps();
        });

        Schema::create('room_status_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('room_id')->constrained('rooms')->cascadeOnDelete();
            $table->date('date');
            $table->foreignId('status_id')->constrained('room_status_history_statuses');
            $table->foreignId('source_id')->constrained('room_status_history_sources');
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('room_status_history');
        Schema::dropIfExists('room_status_history_sources');
        Schema::dropIfExists('room_status_history_statuses');
    }
};
