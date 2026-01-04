<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('room_rates', function (Blueprint $table) {
            if (Schema::hasColumn('room_rates', 'start_date')) {
                $table->dropColumn('start_date');
            }
            if (Schema::hasColumn('room_rates', 'end_date')) {
                $table->dropColumn('end_date');
            }
            if (Schema::hasColumn('room_rates', 'occupancy_prices')) {
                $table->dropColumn('occupancy_prices');
            }
            if (Schema::hasColumn('room_rates', 'event_name')) {
                $table->dropColumn('event_name');
            }

            if (!Schema::hasColumn('room_rates', 'min_guests')) {
                $table->integer('min_guests')->default(1)->after('room_id');
            }
            if (!Schema::hasColumn('room_rates', 'max_guests')) {
                $table->integer('max_guests')->default(1)->after('min_guests');
            }
            if (!Schema::hasColumn('room_rates', 'price_per_night')) {
                $table->decimal('price_per_night', 12, 2)->default(0)->after('max_guests');
            }
        });
    }

    public function down(): void
    {
        Schema::table('room_rates', function (Blueprint $table) {
            if (Schema::hasColumn('room_rates', 'price_per_night')) {
                $table->dropColumn('price_per_night');
            }
            if (Schema::hasColumn('room_rates', 'max_guests')) {
                $table->dropColumn('max_guests');
            }
            if (Schema::hasColumn('room_rates', 'min_guests')) {
                $table->dropColumn('min_guests');
            }

            if (!Schema::hasColumn('room_rates', 'start_date')) {
                $table->date('start_date')->after('room_id');
            }
            if (!Schema::hasColumn('room_rates', 'end_date')) {
                $table->date('end_date')->after('start_date');
            }
            if (!Schema::hasColumn('room_rates', 'occupancy_prices')) {
                $table->json('occupancy_prices')->after('end_date');
            }
            if (!Schema::hasColumn('room_rates', 'event_name')) {
                $table->string('event_name')->nullable()->after('occupancy_prices');
            }
        });
    }
};
