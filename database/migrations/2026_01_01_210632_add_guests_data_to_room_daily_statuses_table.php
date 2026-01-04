<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Si es una vista, aplicar en la tabla de respaldo
        $isView = DB::selectOne("SELECT TABLE_NAME FROM information_schema.VIEWS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'room_daily_statuses'");
        if ($isView) {
            if (Schema::hasTable('room_daily_statuses_data')) {
                Schema::table('room_daily_statuses_data', function (Blueprint $table) {
                    if (!Schema::hasColumn('room_daily_statuses_data', 'guests_data')) {
                        $table->json('guests_data')->nullable()->after('guest_name');
                    }
                });
            }
            return;
        }

        Schema::table('room_daily_statuses', function (Blueprint $table) {
            if (!Schema::hasColumn('room_daily_statuses', 'guests_data')) {
                $table->json('guests_data')->nullable()->after('guest_name');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $isView = DB::selectOne("SELECT TABLE_NAME FROM information_schema.VIEWS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'room_daily_statuses'");
        if ($isView) {
            if (Schema::hasTable('room_daily_statuses_data')) {
                Schema::table('room_daily_statuses_data', function (Blueprint $table) {
                    if (Schema::hasColumn('room_daily_statuses_data', 'guests_data')) {
                        $table->dropColumn('guests_data');
                    }
                });
            }
            return;
        }

        Schema::table('room_daily_statuses', function (Blueprint $table) {
            if (Schema::hasColumn('room_daily_statuses', 'guests_data')) {
                $table->dropColumn('guests_data');
            }
        });
    }
};

