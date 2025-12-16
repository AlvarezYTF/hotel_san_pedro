<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Supplier;

class SupplierSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $suppliers = [
            [
                'name' => 'TechSupply Pro',
                'email' => 'contacto@techsupplypro.com',
                'phone' => '+1-555-0101',
                'address' => '123 Tech Street, Silicon Valley, CA',
                'contact_person' => 'Juan Pérez',
                'is_active' => true,
            ],
            [
                'name' => 'MobileParts Inc',
                'email' => 'ventas@mobileparts.com',
                'phone' => '+1-555-0102',
                'address' => '456 Mobile Ave, Tech City, TX',
                'contact_person' => 'María García',
                'is_active' => true,
            ],
            [
                'name' => 'Accessory World',
                'email' => 'info@accessoryworld.com',
                'phone' => '+1-555-0103',
                'address' => '789 Accessory Blvd, Gadget Town, FL',
                'contact_person' => 'Carlos López',
                'is_active' => true,
            ],
            [
                'name' => 'Repair Tools Co',
                'email' => 'ventas@repairtools.com',
                'phone' => '+1-555-0104',
                'address' => '321 Tool Road, Workshop City, NY',
                'contact_person' => 'Ana Rodríguez',
                'is_active' => true,
            ],
        ];

        foreach ($suppliers as $supplier) {
            Supplier::firstOrCreate(
                ['email' => $supplier['email']],
                $supplier
            );
        }
    }
}
