<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('electronic_invoice_items', function (Blueprint $table) {
            if (!Schema::hasColumn('electronic_invoice_items', 'is_excluded')) {
                $table->boolean('is_excluded')->default(false)->after('discount_rate');
            }
        });
    }

    public function down(): void
    {
        Schema::table('electronic_invoice_items', function (Blueprint $table) {
            if (Schema::hasColumn('electronic_invoice_items', 'is_excluded')) {
                $table->dropColumn('is_excluded');
            }
        });
    }
};
