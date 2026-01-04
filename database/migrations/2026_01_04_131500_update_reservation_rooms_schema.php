<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reservation_rooms', function (Blueprint $table) {
            if (!Schema::hasColumn('reservation_rooms', 'check_in_date')) {
                $table->date('check_in_date')->nullable()->after('room_id');
            }
            if (!Schema::hasColumn('reservation_rooms', 'check_out_date')) {
                $table->date('check_out_date')->nullable()->after('check_in_date');
            }
            if (!Schema::hasColumn('reservation_rooms', 'check_in_time')) {
                $table->time('check_in_time')->nullable()->after('check_out_date');
            }
            if (!Schema::hasColumn('reservation_rooms', 'check_out_time')) {
                $table->time('check_out_time')->nullable()->after('check_in_time');
            }
            if (!Schema::hasColumn('reservation_rooms', 'nights')) {
                $table->integer('nights')->nullable()->after('check_out_time');
            }
            if (!Schema::hasColumn('reservation_rooms', 'price_per_night')) {
                $table->decimal('price_per_night', 12, 2)->nullable()->after('nights');
            }
            if (!Schema::hasColumn('reservation_rooms', 'subtotal')) {
                $table->decimal('subtotal', 12, 2)->default(0)->after('price_per_night');
            }
        });

        // Backfill desde reservations si aún existen columnas antiguas
        if (Schema::hasColumn('reservations', 'check_in_date') && Schema::hasColumn('reservations', 'check_out_date')) {
            DB::statement('UPDATE reservation_rooms rr JOIN reservations r ON rr.reservation_id = r.id
                SET rr.check_in_date = r.check_in_date,
                    rr.check_out_date = r.check_out_date,
                    rr.check_in_time = IFNULL(rr.check_in_time, r.check_in_time),
                    rr.nights = IFNULL(rr.nights, DATEDIFF(r.check_out_date, r.check_in_date))
            ');
        }

        if (Schema::hasColumn('reservations', 'total_amount')) {
            DB::statement('UPDATE reservation_rooms rr JOIN reservations r ON rr.reservation_id = r.id
                SET rr.subtotal = IFNULL(rr.subtotal, r.total_amount),
                    rr.price_per_night = IFNULL(rr.price_per_night,
                        CASE
                            WHEN COALESCE(rr.nights, 0) > 0 THEN r.total_amount / rr.nights
                            ELSE r.total_amount
                        END)
            ');
        }
    }

    public function down(): void
    {
        Schema::table('reservation_rooms', function (Blueprint $table) {
            if (Schema::hasColumn('reservation_rooms', 'subtotal')) {
                $table->dropColumn('subtotal');
            }
            if (Schema::hasColumn('reservation_rooms', 'price_per_night')) {
                $table->dropColumn('price_per_night');
            }
            if (Schema::hasColumn('reservation_rooms', 'nights')) {
                $table->dropColumn('nights');
            }
            if (Schema::hasColumn('reservation_rooms', 'check_out_time')) {
                $table->dropColumn('check_out_time');
            }
            if (Schema::hasColumn('reservation_rooms', 'check_in_time')) {
                $table->dropColumn('check_in_time');
            }
            if (Schema::hasColumn('reservation_rooms', 'check_out_date')) {
                $table->dropColumn('check_out_date');
            }
            if (Schema::hasColumn('reservation_rooms', 'check_in_date')) {
                $table->dropColumn('check_in_date');
            }
        });
    }
};
