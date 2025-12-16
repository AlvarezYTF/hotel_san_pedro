<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dian_customer_tributes', function (Blueprint $table) {
            $table->id();
            $table->string('code', 10)->unique();
            $table->string('name');
            $table->timestamps();
            
            $table->index('code');
        });
    }

    public function down(): void
    {
        // Drop foreign keys from customer_tax_profiles
        if (Schema::hasTable('customer_tax_profiles')) {
            try {
                $foreignKey = DB::selectOne(
                    "SELECT CONSTRAINT_NAME 
                     FROM information_schema.KEY_COLUMN_USAGE 
                     WHERE TABLE_SCHEMA = DATABASE() 
                     AND TABLE_NAME = 'customer_tax_profiles' 
                     AND COLUMN_NAME = 'tribute_id' 
                     AND REFERENCED_TABLE_NAME = 'dian_customer_tributes'"
                );
                
                if ($foreignKey) {
                    DB::statement("ALTER TABLE customer_tax_profiles DROP FOREIGN KEY {$foreignKey->CONSTRAINT_NAME}");
                }
            } catch (\Exception $e) {
                // Foreign key might not exist or already dropped
            }
        }
        
        // Drop foreign keys from electronic_invoice_items
        if (Schema::hasTable('electronic_invoice_items')) {
            try {
                $foreignKey = DB::selectOne(
                    "SELECT CONSTRAINT_NAME 
                     FROM information_schema.KEY_COLUMN_USAGE 
                     WHERE TABLE_SCHEMA = DATABASE() 
                     AND TABLE_NAME = 'electronic_invoice_items' 
                     AND COLUMN_NAME = 'tribute_id' 
                     AND REFERENCED_TABLE_NAME = 'dian_customer_tributes'"
                );
                
                if ($foreignKey) {
                    DB::statement("ALTER TABLE electronic_invoice_items DROP FOREIGN KEY {$foreignKey->CONSTRAINT_NAME}");
                }
            } catch (\Exception $e) {
                // Foreign key might not exist or already dropped
            }
        }
        
        Schema::dropIfExists('dian_customer_tributes');
    }
};
