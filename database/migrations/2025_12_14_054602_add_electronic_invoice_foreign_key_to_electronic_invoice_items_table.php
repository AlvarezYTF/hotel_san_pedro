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
        Schema::table('electronic_invoice_items', function (Blueprint $table) {
            $table->foreign('electronic_invoice_id')
                ->references('id')
                ->on('electronic_invoices')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('electronic_invoice_items', function (Blueprint $table) {
            $table->dropForeign(['electronic_invoice_id']);
        });
    }
};
