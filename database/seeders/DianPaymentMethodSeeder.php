<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DianPaymentMethodSeeder extends Seeder
{
    public function run(): void
    {
        $methods = [
            ['code' => '10', 'name' => 'Efectivo'],
            ['code' => '42', 'name' => 'Consignación'],
            ['code' => '20', 'name' => 'Cheque'],
            ['code' => '47', 'name' => 'Transferencia'],
            ['code' => '71', 'name' => 'Bonos'],
            ['code' => '72', 'name' => 'Vales'],
            ['code' => '1', 'name' => 'No definido'],
            ['code' => '49', 'name' => 'Tarjeta Débito'],
            ['code' => '48', 'name' => 'Tarjeta Crédito'],
            ['code' => 'ZZZ', 'name' => 'Otro'],
        ];

        foreach ($methods as $method) {
            DB::table('dian_payment_methods')->updateOrInsert(
                ['code' => $method['code']],
                [
                    'name' => $method['name'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }
}
