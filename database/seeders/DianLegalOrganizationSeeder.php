<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DianLegalOrganizationSeeder extends Seeder
{
    public function run(): void
    {
        $organizations = [
            ['id' => 1, 'code' => null, 'name' => 'Persona Jurídica'],
            ['id' => 2, 'code' => null, 'name' => 'Persona Natural'],
        ];

        foreach ($organizations as $org) {
            DB::table('dian_legal_organizations')->updateOrInsert(
                ['id' => $org['id']],
                [
                    'code' => $org['code'],
                    'name' => $org['name'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }
}
