<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('company_tax_settings', function (Blueprint $table) {
            $table->id();
            $table->string('company_name');
            $table->string('nit', 20);
            $table->string('dv', 1);
            $table->string('email');
            $table->unsignedBigInteger('municipality_id');
            $table->string('economic_activity', 10)->nullable();
            $table->string('logo_url')->nullable();
            $table->string('factus_company_id')->nullable()->unique();
            $table->timestamps();
            
            $table->index('municipality_id');
            $table->index('factus_company_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('company_tax_settings');
    }
};
