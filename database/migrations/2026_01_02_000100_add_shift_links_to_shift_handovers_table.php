<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('shift_handovers', function (Blueprint $table) {
            $table->foreignId('from_shift_id')->nullable()->after('id')->constrained('shifts');
            $table->foreignId('to_shift_id')->nullable()->after('from_shift_id')->constrained('shifts');
            $table->json('summary')->nullable()->after('observaciones_recepcion');
            $table->foreignId('validated_by')->nullable()->after('to_shift_id')->constrained('users');
        });
    }

    public function down(): void
    {
        Schema::table('shift_handovers', function (Blueprint $table) {
            $table->dropForeign(['from_shift_id']);
            $table->dropForeign(['to_shift_id']);
            $table->dropForeign(['validated_by']);
            $table->dropColumn(['from_shift_id', 'to_shift_id', 'summary', 'validated_by']);
        });
    }
};
