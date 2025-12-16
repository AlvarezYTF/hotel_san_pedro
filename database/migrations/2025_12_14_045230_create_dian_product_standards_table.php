<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dian_product_standards', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        if (Schema::hasTable('electronic_invoice_items')) {
            try {
                $foreignKey = DB::selectOne(
                    "SELECT CONSTRAINT_NAME 
                     FROM information_schema.KEY_COLUMN_USAGE 
                     WHERE TABLE_SCHEMA = DATABASE() 
                     AND TABLE_NAME = 'electronic_invoice_items' 
                     AND COLUMN_NAME = 'standard_code_id' 
                     AND REFERENCED_TABLE_NAME = 'dian_product_standards'"
                );
                
                if ($foreignKey) {
                    DB::statement("ALTER TABLE electronic_invoice_items DROP FOREIGN KEY {$foreignKey->CONSTRAINT_NAME}");
                }
            } catch (\Exception $e) {
                // Foreign key might not exist or already dropped
            }
        }
        
        Schema::dropIfExists('dian_product_standards');
    }
};
