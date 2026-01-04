<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reservation_sales', function (Blueprint $table) {
            if (Schema::hasColumn('reservation_sales', 'payment_method')) {
                $table->dropColumn('payment_method');
            }
            if (Schema::hasColumn('reservation_sales', 'is_paid')) {
                $table->dropColumn('is_paid');
            }
        });
    }

    public function down(): void
    {
        Schema::table('reservation_sales', function (Blueprint $table) {
            if (!Schema::hasColumn('reservation_sales', 'payment_method')) {
                $table->string('payment_method')->nullable()->after('total');
            }
            if (!Schema::hasColumn('reservation_sales', 'is_paid')) {
                $table->boolean('is_paid')->default(false)->after('payment_method');
            }
        });
    }
};
