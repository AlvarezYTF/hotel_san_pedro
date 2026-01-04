<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('room_release_history');
    }

    public function down(): void
    {
        if (!Schema::hasTable('room_release_history')) {
            Schema::create('room_release_history', function ($table) {
                $table->id();
                $table->unsignedBigInteger('room_id');
                $table->unsignedBigInteger('released_by');
                $table->dateTime('release_date');
                $table->string('room_number')->nullable();
                $table->string('customer_name')->nullable();
                $table->string('customer_identification')->nullable();
                $table->text('reason')->nullable();
                $table->timestamps();
            });
        }
    }
};
