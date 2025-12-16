<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dian_document_types', function (Blueprint $table) {
            $table->id();
            $table->string('code', 10)->unique();
            $table->string('name');
            $table->timestamps();
            
            $table->index('code');
        });
    }

    public function down(): void
    {
        if (Schema::hasTable('electronic_invoices')) {
            try {
                $foreignKey = DB::selectOne(
                    "SELECT CONSTRAINT_NAME 
                     FROM information_schema.KEY_COLUMN_USAGE 
                     WHERE TABLE_SCHEMA = DATABASE() 
                     AND TABLE_NAME = 'electronic_invoices' 
                     AND COLUMN_NAME = 'document_type_id' 
                     AND REFERENCED_TABLE_NAME = 'dian_document_types'"
                );
                
                if ($foreignKey) {
                    DB::statement("ALTER TABLE electronic_invoices DROP FOREIGN KEY {$foreignKey->CONSTRAINT_NAME}");
                }
            } catch (\Exception $e) {
                // Foreign key might not exist or already dropped
            }
        }
        
        Schema::dropIfExists('dian_document_types');
    }
};
