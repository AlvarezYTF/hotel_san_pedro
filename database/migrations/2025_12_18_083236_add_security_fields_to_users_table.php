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
        Schema::table('users', function (Blueprint $table) {
            $table->string('allowed_ip')->nullable()->after('password');
            $table->json('working_hours')->nullable()->after('allowed_ip'); // e.g. {"start": "08:00", "end": "18:00"}
            $table->string('security_pin', 4)->nullable()->after('working_hours');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['allowed_ip', 'working_hours', 'security_pin']);
        });
    }
};
