<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DianDocumentTypeSeeder extends Seeder
{
    public function run(): void
    {
        $types = [
            ['code' => '01', 'name' => 'Factura electrónica de venta'],
            ['code' => '03', 'name' => 'Instrumento electrónico de transmisión'],
        ];

        foreach ($types as $type) {
            DB::table('dian_document_types')->updateOrInsert(
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
