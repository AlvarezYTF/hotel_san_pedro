<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Category;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Teléfonos',
                'description' => 'Smartphones y teléfonos móviles',
                'color' => '#3B82F6',
                'is_active' => true,
            ],
            [
                'name' => 'Accesorios',
                'description' => 'Carcasas, protectores y accesorios varios',
                'color' => '#10B981',
                'is_active' => true,
            ],
            [
                'name' => 'Cables y Cargadores',
                'description' => 'Cables USB, cargadores y adaptadores',
                'color' => '#F59E0B',
                'is_active' => true,
            ],
            [
                'name' => 'Auriculares',
                'description' => 'Auriculares con cable y bluetooth',
                'color' => '#8B5CF6',
                'is_active' => true,
            ],
            [
                'name' => 'Repuestos',
                'description' => 'Pantallas, baterías y otros repuestos',
                'color' => '#EF4444',
                'is_active' => true,
            ],
            [
                'name' => 'Herramientas',
                'description' => 'Herramientas para reparación de dispositivos',
                'color' => '#6B7280',
                'is_active' => true,
            ],
        ];

        foreach ($categories as $category) {
            Category::firstOrCreate(
                ['name' => $category['name']],
                $category
            );
        }
    }
}
