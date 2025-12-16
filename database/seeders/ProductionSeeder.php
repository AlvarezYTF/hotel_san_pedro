<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

/**
 * Production-safe seeder
 * Only runs seeders that use updateOrInsert/updateOrCreate
 * Does not duplicate existing data
 */
class ProductionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Only executes safe seeders that won't duplicate data
     */
    public function run(): void
    {
        $this->command->info('Running production-safe seeders...');
        
        // DIAN catalog seeders (safe - use updateOrInsert)
        $this->call([
            DianIdentificationDocumentSeeder::class,
            DianLegalOrganizationSeeder::class,
            DianCustomerTributeSeeder::class,
            DianDocumentTypeSeeder::class,
            DianOperationTypeSeeder::class,
            DianPaymentMethodSeeder::class,
            DianPaymentFormSeeder::class,
            DianProductStandardSeeder::class,
        ]);
        
        $this->command->info('Production seeders completed successfully!');
        $this->command->warn('Note: RoleSeeder, UserSeeder, CategorySeeder, ProductSeeder, CustomerSeeder, and SupplierSeeder were NOT executed to prevent data duplication.');
    }
}

