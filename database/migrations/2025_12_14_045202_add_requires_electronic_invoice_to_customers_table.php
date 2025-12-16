<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->boolean('requires_electronic_invoice')->default(false)->after('is_active');
            $table->index('requires_electronic_invoice');
        });
    }

    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropIndex(['requires_electronic_invoice']);
            $table->dropColumn('requires_electronic_invoice');
        });
    }
};
