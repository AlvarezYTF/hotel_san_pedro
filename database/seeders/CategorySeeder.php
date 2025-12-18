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
        // Delete all other categories
        \App\Models\Category::whereNotIn('name', ['Bebidas', 'Mecato'])->delete();

        $categories = [
            [
                'name' => 'Bebidas',
                'description' => 'Todo lo relacionado con bebidas y líquidos',
                'color' => '#3B82F6',
                'is_active' => true,
            ],
            [
                'name' => 'Mecato',
                'description' => 'Todo lo relacionado con comida y snacks',
                'color' => '#F59E0B',
                'is_active' => true,
            ],
        ];

        foreach ($categories as $category) {
            \App\Models\Category::updateOrCreate(
                ['name' => $category['name']],
                $category
            );
        }
    }
}
