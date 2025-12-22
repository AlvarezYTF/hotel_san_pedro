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
        Schema::create('reservation_room_guests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reservation_room_id')->constrained('reservation_rooms')->onDelete('cascade');
            $table->foreignId('customer_id')->constrained()->onDelete('cascade');
            $table->timestamps();

            // Prevent duplicate guest assignments per room in a reservation
            $table->unique(['reservation_room_id', 'customer_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reservation_room_guests');
    }
};

