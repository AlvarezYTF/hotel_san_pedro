<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customer_tax_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->unique()->constrained()->onDelete('cascade');
            
            // Identificación Fiscal
            $table->foreignId('identification_document_id')->constrained('dian_identification_documents')->onDelete('restrict');
            $table->string('identification', 20);
            $table->string('dv', 1)->nullable();
            
            // Información Legal
            $table->foreignId('legal_organization_id')->nullable()->constrained('dian_legal_organizations')->onDelete('set null');
            $table->string('company')->nullable();
            $table->string('trade_name')->nullable();
            
            // Régimen Tributario
            $table->foreignId('tribute_id')->nullable()->constrained('dian_customer_tributes')->onDelete('set null');
            
            // Ubicación Fiscal - usa factus_id de dian_municipalities
            $table->unsignedBigInteger('municipality_id');
            
            $table->timestamps();
            
            $table->index('customer_id');
            $table->index(['identification', 'identification_document_id'], 'ctp_ident_doc_idx');
            $table->index('municipality_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customer_tax_profiles');
    }
};
