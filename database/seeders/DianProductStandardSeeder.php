<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DianProductStandardSeeder extends Seeder
{
    public function run(): void
    {
        $standards = [
            ['id' => 1, 'name' => 'Estándar contribuyente'],
            ['id' => 2, 'name' => 'UNSPSC'],
            ['id' => 3, 'name' => 'Partida Arancelaria'],
            ['id' => 4, 'name' => 'GTIN'],
        ];

        foreach ($standards as $standard) {
            DB::table('dian_product_standards')->updateOrInsert(
                ['id' => $standard['id']],
                [
                    'name' => $standard['name'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }
}
