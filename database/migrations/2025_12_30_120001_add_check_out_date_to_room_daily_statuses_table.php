<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Si es una vista, aplicar el cambio sobre la tabla de respaldo y salir
        $isView = DB::selectOne("SELECT TABLE_NAME FROM information_schema.VIEWS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'room_daily_statuses'");
        if ($isView) {
            if (Schema::hasTable('room_daily_statuses_data')) {
                Schema::table('room_daily_statuses_data', function (Blueprint $table) {
                    if (!Schema::hasColumn('room_daily_statuses_data', 'check_out_date')) {
                        $table->date('check_out_date')->nullable()->after('guest_name');
                    }
                });
            }
            return;
        }

        Schema::table('room_daily_statuses', function (Blueprint $table) {
            if (!Schema::hasColumn('room_daily_statuses', 'check_out_date')) {
                $table->date('check_out_date')->nullable()->after('guest_name');
            }
        });
    }

    public function down(): void
    {
        $isView = DB::selectOne("SELECT TABLE_NAME FROM information_schema.VIEWS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'room_daily_statuses'");
        if ($isView) {
            if (Schema::hasTable('room_daily_statuses_data')) {
                Schema::table('room_daily_statuses_data', function (Blueprint $table) {
                    if (Schema::hasColumn('room_daily_statuses_data', 'check_out_date')) {
                        $table->dropColumn('check_out_date');
                    }
                });
            }
            return;
        }

        Schema::table('room_daily_statuses', function (Blueprint $table) {
            if (Schema::hasColumn('room_daily_statuses', 'check_out_date')) {
                $table->dropColumn('check_out_date');
            }
        });
    }
};

