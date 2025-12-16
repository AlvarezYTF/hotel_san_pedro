<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dian_legal_organizations', function (Blueprint $table) {
            $table->id();
            $table->string('code', 10)->nullable();
            $table->string('name')->unique();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        // Drop foreign keys that reference dian_legal_organizations table first
        if (Schema::hasTable('customer_tax_profiles')) {
            try {
                $foreignKey = DB::selectOne(
                    "SELECT CONSTRAINT_NAME 
                     FROM information_schema.KEY_COLUMN_USAGE 
                     WHERE TABLE_SCHEMA = DATABASE() 
                     AND TABLE_NAME = 'customer_tax_profiles' 
                     AND COLUMN_NAME = 'legal_organization_id' 
                     AND REFERENCED_TABLE_NAME = 'dian_legal_organizations'"
                );
                
                if ($foreignKey) {
                    DB::statement("ALTER TABLE customer_tax_profiles DROP FOREIGN KEY {$foreignKey->CONSTRAINT_NAME}");
                }
            } catch (\Exception $e) {
                // Foreign key might not exist or already dropped
            }
        }
        
        Schema::dropIfExists('dian_legal_organizations');
    }
};
