<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

class PaymentTypeSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        $data = [
            ['code' => 'deposit', 'name' => 'Abono / anticipo'],
            ['code' => 'balance', 'name' => 'Pago restante'],
            ['code' => 'full', 'name' => 'Pago completo'],
            ['code' => 'refund', 'name' => 'Devolucion'],
        ];

        foreach ($data as $row) {
            DB::table('payment_types')->updateOrInsert(
                ['code' => $row['code']],
                array_merge($row, ['created_at' => $now, 'updated_at' => $now])
            );
        }
    }
}
