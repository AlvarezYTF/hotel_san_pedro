<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('reservation_room_guests', 'customer_id')) {
            // Drop FK if exists
            $fk = DB::selectOne("SELECT CONSTRAINT_NAME FROM information_schema.KEY_COLUMN_USAGE WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'reservation_room_guests' AND COLUMN_NAME = 'customer_id' AND REFERENCED_TABLE_NAME IS NOT NULL LIMIT 1");
            if ($fk) {
                DB::statement('ALTER TABLE reservation_room_guests DROP FOREIGN KEY ' . $fk->CONSTRAINT_NAME);
            }

            Schema::table('reservation_room_guests', function (Blueprint $table) {
                $table->dropColumn('customer_id');
            });
        }
    }

    public function down(): void
    {
        if (!Schema::hasColumn('reservation_room_guests', 'customer_id')) {
            Schema::table('reservation_room_guests', function (Blueprint $table) {
                $table->unsignedBigInteger('customer_id')->nullable()->after('reservation_guest_id');
            });

            Schema::table('reservation_room_guests', function (Blueprint $table) {
                $table->foreign('customer_id')->references('id')->on('customers')->onDelete('cascade');
            });
        }
    }
};
