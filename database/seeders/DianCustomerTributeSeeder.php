<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DianCustomerTributeSeeder extends Seeder
{
    public function run(): void
    {
        $tributes = [
            ['id' => 18, 'code' => '01', 'name' => 'IVA'],
            ['id' => 21, 'code' => 'ZZ', 'name' => 'No aplica'],
        ];

        foreach ($tributes as $tribute) {
            DB::table('dian_customer_tributes')->updateOrInsert(
                ['id' => $tribute['id']],
                [
                    'code' => $tribute['code'],
                    'name' => $tribute['name'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }
}
