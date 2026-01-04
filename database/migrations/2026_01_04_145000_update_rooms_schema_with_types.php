<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Catálogo de tipos de habitación
        if (!Schema::hasTable('room_types')) {
            Schema::create('room_types', function (Blueprint $table) {
                $table->id();
                $table->string('code')->unique();
                $table->string('name');
                $table->timestamps();
            });
        }

        // Catálogo de tipos de ventilación
        if (!Schema::hasTable('ventilation_types')) {
            Schema::create('ventilation_types', function (Blueprint $table) {
                $table->id();
                $table->string('code')->unique();
                $table->string('name');
                $table->timestamps();
            });
        }

        Schema::table('rooms', function (Blueprint $table) {
            // Nuevas columnas
            if (!Schema::hasColumn('rooms', 'room_type_id')) {
                $table->foreignId('room_type_id')->nullable()->after('room_number')->constrained('room_types');
            }
            if (!Schema::hasColumn('rooms', 'ventilation_type_id')) {
                $table->foreignId('ventilation_type_id')->nullable()->after('room_type_id')->constrained('ventilation_types');
            }
            if (!Schema::hasColumn('rooms', 'base_price_per_night')) {
                $table->decimal('base_price_per_night', 12, 2)->nullable()->after('max_capacity');
            }
            if (!Schema::hasColumn('rooms', 'is_active')) {
                $table->boolean('is_active')->default(true)->after('base_price_per_night');
            }

            // Asegurar unicidad de room_number
            $indexes = DB::select("SELECT INDEX_NAME FROM information_schema.STATISTICS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'rooms' AND COLUMN_NAME = 'room_number'");
            $hasUnique = collect($indexes)->contains(function ($idx) {
                return isset($idx->INDEX_NAME) && $idx->INDEX_NAME === 'rooms_room_number_unique';
            });
            if (!$hasUnique) {
                $table->unique('room_number');
            }
        });

        // Backfill base_price_per_night a partir de columnas antiguas si existen
        if (Schema::hasColumn('rooms', 'base_price_per_night')) {
            $hasPrice = Schema::hasColumn('rooms', 'price_per_night') || Schema::hasColumn('rooms', 'price_1_person');
            if ($hasPrice) {
                DB::statement("UPDATE rooms SET base_price_per_night = COALESCE(price_per_night, price_1_person) WHERE base_price_per_night IS NULL");
            }
        }

        // Eliminar columnas obsoletas
        Schema::table('rooms', function (Blueprint $table) {
            if (Schema::hasColumn('rooms', 'ventilation_type')) {
                $table->dropColumn('ventilation_type');
            }
            if (Schema::hasColumn('rooms', 'price_per_night')) {
                $table->dropColumn('price_per_night');
            }
            if (Schema::hasColumn('rooms', 'price_1_person')) {
                $table->dropColumn('price_1_person');
            }
            if (Schema::hasColumn('rooms', 'price_2_persons')) {
                $table->dropColumn('price_2_persons');
            }
            if (Schema::hasColumn('rooms', 'price_additional_person')) {
                $table->dropColumn('price_additional_person');
            }
            if (Schema::hasColumn('rooms', 'occupancy_prices')) {
                $table->dropColumn('occupancy_prices');
            }
        });
    }

    public function down(): void
    {
        Schema::table('rooms', function (Blueprint $table) {
            // Restaurar columnas eliminadas
            if (!Schema::hasColumn('rooms', 'ventilation_type')) {
                $table->string('ventilation_type')->nullable()->after('max_capacity');
            }
            if (!Schema::hasColumn('rooms', 'price_per_night')) {
                $table->decimal('price_per_night', 12, 2)->nullable()->after('ventilation_type');
            }
            if (!Schema::hasColumn('rooms', 'price_1_person')) {
                $table->decimal('price_1_person', 12, 2)->nullable()->after('price_per_night');
            }
            if (!Schema::hasColumn('rooms', 'price_2_persons')) {
                $table->decimal('price_2_persons', 12, 2)->nullable()->after('price_1_person');
            }
            if (!Schema::hasColumn('rooms', 'price_additional_person')) {
                $table->decimal('price_additional_person', 12, 2)->nullable()->after('price_2_persons');
            }
            if (!Schema::hasColumn('rooms', 'occupancy_prices')) {
                $table->json('occupancy_prices')->nullable()->after('price_additional_person');
            }

            if (Schema::hasColumn('rooms', 'room_type_id')) {
                $table->dropForeign(['room_type_id']);
                $table->dropColumn('room_type_id');
            }
            if (Schema::hasColumn('rooms', 'ventilation_type_id')) {
                $table->dropForeign(['ventilation_type_id']);
                $table->dropColumn('ventilation_type_id');
            }
            if (Schema::hasColumn('rooms', 'base_price_per_night')) {
                $table->dropColumn('base_price_per_night');
            }
            if (Schema::hasColumn('rooms', 'is_active')) {
                $table->dropColumn('is_active');
            }
        });

        // Quitar unique si lo agregamos
        $indexes = DB::select("SELECT INDEX_NAME, NON_UNIQUE FROM information_schema.STATISTICS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'rooms' AND INDEX_NAME = 'rooms_room_number_unique' LIMIT 1");
        if ($indexes && isset($indexes[0]) && !$indexes[0]->NON_UNIQUE) {
            Schema::table('rooms', function (Blueprint $table) {
                $table->dropUnique('rooms_room_number_unique');
            });
        }

        Schema::dropIfExists('room_types');
        Schema::dropIfExists('ventilation_types');
    }
};
