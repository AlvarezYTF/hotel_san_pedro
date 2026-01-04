<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

class RoomMaintenanceBlockStatusSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        $data = [
            ['code' => 'active', 'name' => 'Activo'],
            ['code' => 'finished', 'name' => 'Finalizado'],
        ];

        foreach ($data as $row) {
            DB::table('room_maintenance_block_statuses')->updateOrInsert(
                ['code' => $row['code']],
                array_merge($row, ['created_at' => $now, 'updated_at' => $now])
            );
        }
    }
}
