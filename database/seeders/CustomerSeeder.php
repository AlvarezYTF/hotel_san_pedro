<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Customer;

class CustomerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $customers = [
            [
                'name' => 'Juan Carlos Pérez',
                'email' => 'juan.perez@email.com',
                'phone' => '+1-555-0201',
                'address' => 'Calle Principal 123',
                'city' => 'Ciudad de México',
                'state' => 'CDMX',
                'zip_code' => '01000',
                'notes' => 'Cliente frecuente, prefiere productos Apple',
                'is_active' => true,
            ],
            [
                'name' => 'María Elena García',
                'email' => 'maria.garcia@email.com',
                'phone' => '+1-555-0202',
                'address' => 'Avenida Central 456',
                'city' => 'Guadalajara',
                'state' => 'Jalisco',
                'zip_code' => '44100',
                'notes' => 'Especialista en reparaciones de Samsung',
                'is_active' => true,
            ],
            [
                'name' => 'Carlos Alberto López',
                'email' => 'carlos.lopez@email.com',
                'phone' => '+1-555-0203',
                'address' => 'Plaza Mayor 789',
                'city' => 'Monterrey',
                'state' => 'Nuevo León',
                'zip_code' => '64000',
                'notes' => 'Cliente corporativo, compras al mayoreo',
                'is_active' => true,
            ],
            [
                'name' => 'Empresa Tech Solutions',
                'email' => 'ventas@techsolutions.com',
                'phone' => '+1-555-0204',
                'address' => 'Zona Industrial 321',
                'city' => 'Tijuana',
                'state' => 'Baja California',
                'zip_code' => '22000',
                'notes' => 'Empresa distribuidora, descuentos especiales',
                'is_active' => true,
            ],
            [
                'name' => 'Ana Sofía Rodríguez',
                'email' => 'ana.rodriguez@email.com',
                'phone' => '+1-555-0205',
                'address' => 'Calle Nueva 654',
                'city' => 'Puebla',
                'state' => 'Puebla',
                'zip_code' => '72000',
                'notes' => 'Estudiante, prefiere productos económicos',
                'is_active' => true,
            ],
            [
                'name' => 'Roberto Martínez',
                'email' => null,
                'phone' => '+1-555-0206',
                'address' => 'Calle Secundaria 987',
                'city' => 'Cancún',
                'state' => 'Quintana Roo',
                'zip_code' => '77500',
                'notes' => 'Cliente ocasional, solo reparaciones',
                'is_active' => false,
            ],
        ];

        foreach ($customers as $customer) {
            // Use email as unique identifier if available, otherwise use name
            if (!empty($customer['email'])) {
                Customer::firstOrCreate(
                    ['email' => $customer['email']],
                    $customer
                );
            } else {
                // For customers without email, use name as identifier
                Customer::firstOrCreate(
                    ['name' => $customer['name'], 'email' => null],
                    $customer
                );
            }
        }
    }
}
