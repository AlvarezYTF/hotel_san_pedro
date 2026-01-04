<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

class StayStatusSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        $data = [
            ['code' => 'active', 'name' => 'Ocupada (normal)'],
            ['code' => 'pending_checkout', 'name' => 'Pendiente checkout'],
            ['code' => 'finished', 'name' => 'Salida confirmada'],
        ];

        foreach ($data as $row) {
            DB::table('stay_statuses')->updateOrInsert(
                ['code' => $row['code']],
                array_merge($row, ['updated_at' => $now, 'created_at' => $now])
            );
        }
    }
}
