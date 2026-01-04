<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('room_cleaning_types')) {
            Schema::create('room_cleaning_types', function (Blueprint $table) {
                $table->id();
                $table->string('code')->unique();
                $table->string('name');
                $table->timestamps();
            });
        }

        Schema::table('room_cleanings', function (Blueprint $table) {
            if (!Schema::hasColumn('room_cleanings', 'type_room_cleaning_id')) {
                $table->foreignId('type_room_cleaning_id')->nullable()->after('room_id')->constrained('room_cleaning_types');
            }
        });
    }

    public function down(): void
    {
        Schema::table('room_cleanings', function (Blueprint $table) {
            if (Schema::hasColumn('room_cleanings', 'type_room_cleaning_id')) {
                $table->dropForeign(['type_room_cleaning_id']);
                $table->dropColumn('type_room_cleaning_id');
            }
        });

        Schema::dropIfExists('room_cleaning_types');
    }
};
