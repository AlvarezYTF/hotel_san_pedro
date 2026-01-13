<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            if (!Schema::hasColumn('payments', 'bank_name')) {
                $table->string('bank_name', 150)->nullable()->after('payment_method_id');
            }
            if (!Schema::hasColumn('payments', 'created_at')) {
                $table->timestamp('created_at')->useCurrent();
            }
            if (!Schema::hasColumn('payments', 'updated_at')) {
                $table->timestamp('updated_at')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            if (Schema::hasColumn('payments', 'bank_name')) {
                $table->dropColumn('bank_name');
            }
            if (Schema::hasColumn('payments', 'updated_at')) {
                $table->dropColumn('updated_at');
            }
            if (Schema::hasColumn('payments', 'created_at')) {
                $table->dropColumn('created_at');
            }
        });
    }
};
