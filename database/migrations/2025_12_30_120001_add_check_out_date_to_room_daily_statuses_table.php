<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('room_daily_statuses', function (Blueprint $table) {
            if (!Schema::hasColumn('room_daily_statuses', 'check_out_date')) {
                $table->date('check_out_date')->nullable()->after('guest_name');
            }
        });
    }

    public function down(): void
    {
        Schema::table('room_daily_statuses', function (Blueprint $table) {
            if (Schema::hasColumn('room_daily_statuses', 'check_out_date')) {
                $table->dropColumn('check_out_date');
            }
        });
    }
};

