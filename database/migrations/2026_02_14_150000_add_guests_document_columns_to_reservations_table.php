<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('reservations')) {
            return;
        }

        Schema::table('reservations', function (Blueprint $table) {
            if (!Schema::hasColumn('reservations', 'guests_document_path')) {
                $table->string('guests_document_path')->nullable()->after('notes');
            }

            if (!Schema::hasColumn('reservations', 'guests_document_original_name')) {
                $table->string('guests_document_original_name')->nullable()->after('guests_document_path');
            }

            if (!Schema::hasColumn('reservations', 'guests_document_mime_type')) {
                $table->string('guests_document_mime_type')->nullable()->after('guests_document_original_name');
            }

            if (!Schema::hasColumn('reservations', 'guests_document_size')) {
                $table->unsignedBigInteger('guests_document_size')->nullable()->after('guests_document_mime_type');
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('reservations')) {
            return;
        }

        Schema::table('reservations', function (Blueprint $table) {
            if (Schema::hasColumn('reservations', 'guests_document_size')) {
                $table->dropColumn('guests_document_size');
            }

            if (Schema::hasColumn('reservations', 'guests_document_mime_type')) {
                $table->dropColumn('guests_document_mime_type');
            }

            if (Schema::hasColumn('reservations', 'guests_document_original_name')) {
                $table->dropColumn('guests_document_original_name');
            }

            if (Schema::hasColumn('reservations', 'guests_document_path')) {
                $table->dropColumn('guests_document_path');
            }
        });
    }
};
