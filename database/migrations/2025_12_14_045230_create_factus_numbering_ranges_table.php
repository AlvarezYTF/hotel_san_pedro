<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('factus_numbering_ranges', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('factus_id')->unique();
            $table->string('document');
            $table->string('document_code')->nullable();
            $table->string('prefix')->nullable();
            $table->unsignedBigInteger('range_from');
            $table->unsignedBigInteger('range_to');
            $table->unsignedBigInteger('current')->default(0);
            $table->string('resolution_number')->nullable();
            $table->string('technical_key')->nullable();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->boolean('is_expired')->default(false);
            $table->boolean('is_active')->default(false);
            $table->timestamps();
            
            $table->index('factus_id');
            $table->index('is_active');
            $table->index('is_expired');
            $table->index('document');
            $table->index('document_code');
            $table->index(['is_active', 'is_expired', 'document']);
            $table->index('start_date');
            $table->index('end_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('factus_numbering_ranges');
    }
};
