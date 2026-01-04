<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('payment_types')) {
            Schema::create('payment_types', function (Blueprint $table) {
                $table->id();
                $table->string('code')->unique();
                $table->string('name');
                $table->timestamps();
            });
        }

        if (Schema::hasTable('payments')) {
            Schema::table('payments', function (Blueprint $table) {
                if (!Schema::hasColumn('payments', 'payment_type_id')) {
                    $table->foreignId('payment_type_id')->nullable()->after('payment_method_id')->constrained('payment_types');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('payments')) {
            Schema::table('payments', function (Blueprint $table) {
                if (Schema::hasColumn('payments', 'payment_type_id')) {
                    $table->dropForeign(['payment_type_id']);
                    $table->dropColumn('payment_type_id');
                }
            });
        }

        if (Schema::hasTable('payment_types')) {
            Schema::drop('payment_types');
        }
    }
};
