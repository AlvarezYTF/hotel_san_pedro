<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('room_cleanings')) {
            Schema::create('room_cleanings', function (Blueprint $table) {
                $table->id();
                $table->foreignId('room_id')->constrained('rooms')->cascadeOnDelete();
                $table->dateTime('cleaned_at');
                $table->foreignId('created_by')->nullable()->constrained('users');
                $table->text('notes')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('room_cleanings');
    }
};
