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
        if (Schema::hasTable('electronic_invoices') && !Schema::hasColumn('electronic_invoices', 'sale_id')) {
            Schema::table('electronic_invoices', function (Blueprint $table) {
                $table->unsignedBigInteger('sale_id')->nullable()->after('id')->index();
                $table->foreign('sale_id')->references('id')->on('sales')->onDelete('set null');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('electronic_invoices', function (Blueprint $table) {
            $table->dropForeign(['sale_id']);
            $table->dropColumn('sale_id');
        });
    }
};
