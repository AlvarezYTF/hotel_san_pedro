<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1) Quitar FKs existentes que usan guest_id o reservation_room_id antes de tocar índices
        $fkGuest = DB::selectOne("SELECT CONSTRAINT_NAME FROM information_schema.KEY_COLUMN_USAGE WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'reservation_room_guests' AND COLUMN_NAME = 'guest_id' AND REFERENCED_TABLE_NAME IS NOT NULL LIMIT 1");
        if ($fkGuest) {
            DB::statement('ALTER TABLE reservation_room_guests DROP FOREIGN KEY ' . $fkGuest->CONSTRAINT_NAME);
        }

        $fkRoom = DB::selectOne("SELECT CONSTRAINT_NAME FROM information_schema.KEY_COLUMN_USAGE WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'reservation_room_guests' AND COLUMN_NAME = 'reservation_room_id' AND REFERENCED_TABLE_NAME IS NOT NULL LIMIT 1");
        if ($fkRoom) {
            DB::statement('ALTER TABLE reservation_room_guests DROP FOREIGN KEY ' . $fkRoom->CONSTRAINT_NAME);
        }

        // 2) Eliminar índice único viejo (ya sin FKs que lo usen) y la columna guest_id
        $uniq = DB::selectOne("SELECT CONSTRAINT_NAME FROM information_schema.TABLE_CONSTRAINTS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'reservation_room_guests' AND CONSTRAINT_TYPE = 'UNIQUE' AND CONSTRAINT_NAME = 'reservation_room_guests_room_guest_unique' LIMIT 1");
        if ($uniq) {
            DB::statement('ALTER TABLE reservation_room_guests DROP INDEX reservation_room_guests_room_guest_unique');
        }

        if (Schema::hasColumn('reservation_room_guests', 'guest_id')) {
            Schema::table('reservation_room_guests', function (Blueprint $table) {
                $table->dropColumn('guest_id');
            });
        }

        // 3) Agregar nueva columna
        if (!Schema::hasColumn('reservation_room_guests', 'reservation_guest_id')) {
            Schema::table('reservation_room_guests', function (Blueprint $table) {
                $table->unsignedBigInteger('reservation_guest_id')->nullable()->after('reservation_room_id');
            });
        }

        // 4) Crear FKs nuevas
        // FK reservation_room_id -> reservation_rooms
        $fkRoomNew = DB::selectOne("SELECT CONSTRAINT_NAME FROM information_schema.KEY_COLUMN_USAGE WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'reservation_room_guests' AND COLUMN_NAME = 'reservation_room_id' AND REFERENCED_TABLE_NAME IS NOT NULL LIMIT 1");
        if ($fkRoomNew) {
            DB::statement('ALTER TABLE reservation_room_guests DROP FOREIGN KEY ' . $fkRoomNew->CONSTRAINT_NAME);
        }
        Schema::table('reservation_room_guests', function (Blueprint $table) {
            if (Schema::hasColumn('reservation_room_guests', 'reservation_room_id')) {
                $table->foreign('reservation_room_id')->references('id')->on('reservation_rooms')->onDelete('cascade');
            }
        });

        // FK reservation_guest_id -> reservation_guests
        $fkNew = DB::selectOne("SELECT CONSTRAINT_NAME FROM information_schema.KEY_COLUMN_USAGE WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'reservation_room_guests' AND COLUMN_NAME = 'reservation_guest_id' AND REFERENCED_TABLE_NAME IS NOT NULL LIMIT 1");
        if ($fkNew) {
            DB::statement('ALTER TABLE reservation_room_guests DROP FOREIGN KEY ' . $fkNew->CONSTRAINT_NAME);
        }
        Schema::table('reservation_room_guests', function (Blueprint $table) {
            if (Schema::hasColumn('reservation_room_guests', 'reservation_guest_id')) {
                $table->foreign('reservation_guest_id')->references('id')->on('reservation_guests')->onDelete('cascade');
            }
        });

        // 5) Unique nuevo
        $uniqNew = DB::selectOne("SELECT CONSTRAINT_NAME FROM information_schema.TABLE_CONSTRAINTS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'reservation_room_guests' AND CONSTRAINT_TYPE = 'UNIQUE' AND CONSTRAINT_NAME = 'reservation_room_guests_room_resguest_unique' LIMIT 1");
        if (!$uniqNew) {
            DB::statement('ALTER TABLE reservation_room_guests ADD CONSTRAINT reservation_room_guests_room_resguest_unique UNIQUE (reservation_room_id, reservation_guest_id)');
        }
    }

    public function down(): void
    {
        // Revertir a guest_id apuntando a customers
        Schema::table('reservation_room_guests', function (Blueprint $table) {
            // Drop new unique and FK
            $uniq = DB::selectOne("SELECT CONSTRAINT_NAME FROM information_schema.TABLE_CONSTRAINTS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'reservation_room_guests' AND CONSTRAINT_TYPE = 'UNIQUE' AND CONSTRAINT_NAME = 'reservation_room_guests_room_resguest_unique' LIMIT 1");
            if ($uniq) {
                DB::statement('ALTER TABLE reservation_room_guests DROP INDEX reservation_room_guests_room_resguest_unique');
            }
            $fk = DB::selectOne("SELECT CONSTRAINT_NAME FROM information_schema.KEY_COLUMN_USAGE WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'reservation_room_guests' AND COLUMN_NAME = 'reservation_guest_id' AND REFERENCED_TABLE_NAME IS NOT NULL LIMIT 1");
            if ($fk) {
                DB::statement('ALTER TABLE reservation_room_guests DROP FOREIGN KEY ' . $fk->CONSTRAINT_NAME);
            }
            if (Schema::hasColumn('reservation_room_guests', 'reservation_guest_id')) {
                $table->dropColumn('reservation_guest_id');
            }

            if (!Schema::hasColumn('reservation_room_guests', 'guest_id')) {
                $table->unsignedBigInteger('guest_id')->nullable()->after('reservation_room_id');
            }
        });

        Schema::table('reservation_room_guests', function (Blueprint $table) {
            if (Schema::hasColumn('reservation_room_guests', 'guest_id')) {
                $table->foreign('guest_id')->references('id')->on('customers')->onDelete('cascade');
            }
        });

        $uniqOld = DB::selectOne("SELECT CONSTRAINT_NAME FROM information_schema.TABLE_CONSTRAINTS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'reservation_room_guests' AND CONSTRAINT_TYPE = 'UNIQUE' AND CONSTRAINT_NAME = 'reservation_room_guests_room_guest_unique' LIMIT 1");
        if (!$uniqOld) {
            DB::statement('ALTER TABLE reservation_room_guests ADD CONSTRAINT reservation_room_guests_room_guest_unique UNIQUE (reservation_room_id, guest_id)');
        }
    }
};
