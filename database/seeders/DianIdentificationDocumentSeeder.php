<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DianIdentificationDocumentSeeder extends Seeder
{
    public function run(): void
    {
        $documents = [
            ['id' => 1, 'code' => null, 'name' => 'Registro civil', 'requires_dv' => false],
            ['id' => 2, 'code' => null, 'name' => 'Tarjeta de identidad', 'requires_dv' => false],
            ['id' => 3, 'code' => 'CC', 'name' => 'Cédula de ciudadanía', 'requires_dv' => false],
            ['id' => 4, 'code' => null, 'name' => 'Tarjeta de extranjería', 'requires_dv' => false],
            ['id' => 5, 'code' => 'CE', 'name' => 'Cédula de extranjería', 'requires_dv' => false],
            ['id' => 6, 'code' => 'NIT', 'name' => 'NIT', 'requires_dv' => true],
            ['id' => 7, 'code' => 'PP', 'name' => 'Pasaporte', 'requires_dv' => false],
            ['id' => 8, 'code' => null, 'name' => 'Documento extranjero', 'requires_dv' => false],
            ['id' => 9, 'code' => null, 'name' => 'PEP', 'requires_dv' => false],
            ['id' => 10, 'code' => null, 'name' => 'NIT otro país', 'requires_dv' => false],
            ['id' => 11, 'code' => null, 'name' => 'NUIP', 'requires_dv' => false],
        ];

        foreach ($documents as $document) {
            DB::table('dian_identification_documents')->updateOrInsert(
                ['id' => $document['id']],
                [
                    'code' => $document['code'],
                    'name' => $document['name'],
                    'requires_dv' => $document['requires_dv'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }
}
