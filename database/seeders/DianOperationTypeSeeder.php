<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DianOperationTypeSeeder extends Seeder
{
    public function run(): void
    {
        $types = [
            ['code' => '10', 'name' => 'Estándar'],
            ['code' => '11', 'name' => 'Mandatos'],
            ['code' => '12', 'name' => 'Transporte'],
        ];

        foreach ($types as $type) {
            DB::table('dian_operation_types')->updateOrInsert(
                ['code' => $type['code']],
                [
                    'name' => $type['name'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }
}
