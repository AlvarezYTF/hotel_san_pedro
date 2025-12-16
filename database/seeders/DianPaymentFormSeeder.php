<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DianPaymentFormSeeder extends Seeder
{
    public function run(): void
    {
        $forms = [
            ['code' => '1', 'name' => 'Contado'],
            ['code' => '2', 'name' => 'Crédito'],
        ];

        foreach ($forms as $form) {
            DB::table('dian_payment_forms')->updateOrInsert(
                ['code' => $form['code']],
                [
                    'name' => $form['name'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }
}
