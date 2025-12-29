<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('shift_cash_outs', function (Blueprint $table) {
            $table->foreignId('shift_handover_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('shift_cash_outs', function (Blueprint $table) {
            $table->foreignId('shift_handover_id')->nullable(false)->change();
        });
    }
};
