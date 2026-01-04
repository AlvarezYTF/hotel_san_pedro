<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('room_maintenance_block_statuses', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('name');
            $table->timestamps();
        });

        Schema::create('room_maintenance_blocks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('room_id')->constrained('rooms')->cascadeOnDelete();
            $table->dateTime('start_at');
            $table->dateTime('end_at')->nullable();
            $table->text('reason');
            $table->foreignId('status_id')->constrained('room_maintenance_block_statuses');
            $table->foreignId('created_by')->nullable()->constrained('users');
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('room_maintenance_blocks');
        Schema::dropIfExists('room_maintenance_block_statuses');
    }
};
