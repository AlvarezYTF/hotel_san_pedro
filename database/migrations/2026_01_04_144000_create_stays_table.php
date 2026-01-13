<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Catálogo de estados de estancias
        if (!Schema::hasTable('stay_statuses')) {
            Schema::create('stay_statuses', function (Blueprint $table) {
                $table->id();
                $table->string('code')->unique();
                $table->string('name');
                $table->timestamps();
            });

            DB::table('stay_statuses')->insert([
                ['code' => 'active', 'name' => 'Active', 'created_at' => now(), 'updated_at' => now()],
                ['code' => 'finished', 'name' => 'Finished', 'created_at' => now(), 'updated_at' => now()],
            ]);
        }

        // Tabla de estancias
        if (!Schema::hasTable('stays')) {
            Schema::create('stays', function (Blueprint $table) {
                $table->id();
                $table->foreignId('reservation_id')->constrained('reservations')->cascadeOnDelete();
                $table->foreignId('room_id')->constrained('rooms')->cascadeOnDelete();
                $table->dateTime('check_in_at');
                $table->dateTime('check_out_at')->nullable();
                $table->string('status')->default('active'); // active, pending_checkout, finished
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('stays');
        Schema::dropIfExists('stay_statuses');
    }
};
