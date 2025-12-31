<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Customer;
use App\Models\InventoryMovement;
use App\Models\Product;

class InventoryTestDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Crear 15 clientes de prueba
        Customer::factory(15)->create();

        // Crear 30 movimientos de inventario variados
        InventoryMovement::factory(10)->input()->create();
        InventoryMovement::factory(10)->sale()->create();
        InventoryMovement::factory(5)->output()->create();
        InventoryMovement::factory(3)->adjustment()->create();
        InventoryMovement::factory(2)->roomConsumption()->create();

        $this->command->info('Datos de inventario y clientes creados correctamente!');
    }
}
