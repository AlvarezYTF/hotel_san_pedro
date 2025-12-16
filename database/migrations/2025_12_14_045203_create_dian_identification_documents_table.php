<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dian_identification_documents', function (Blueprint $table) {
            $table->id();
            $table->string('code', 10)->nullable();
            $table->string('name')->unique();
            $table->boolean('requires_dv')->default(false);
            $table->timestamps();
            
            $table->index('code');
        });
    }

    public function down(): void
    {
        if (Schema::hasTable('customer_tax_profiles')) {
            try {
                $foreignKey = DB::selectOne(
                    "SELECT CONSTRAINT_NAME 
                     FROM information_schema.KEY_COLUMN_USAGE 
                     WHERE TABLE_SCHEMA = DATABASE() 
                     AND TABLE_NAME = 'customer_tax_profiles' 
                     AND COLUMN_NAME = 'identification_document_id' 
                     AND REFERENCED_TABLE_NAME = 'dian_identification_documents'"
                );
                
                if ($foreignKey) {
                    DB::statement("ALTER TABLE customer_tax_profiles DROP FOREIGN KEY {$foreignKey->CONSTRAINT_NAME}");
                }
            } catch (\Exception $e) {
                // Foreign key might not exist or already dropped
            }
        }
        
        Schema::dropIfExists('dian_identification_documents');
    }
};
