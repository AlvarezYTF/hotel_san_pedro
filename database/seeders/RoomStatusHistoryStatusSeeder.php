<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

class RoomStatusHistoryStatusSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        $data = [
            ['code' => 'reserved', 'name' => 'Bloqueada por reserva'],
            ['code' => 'occupied', 'name' => 'Ocupada por estadía'],
            ['code' => 'maintenance', 'name' => 'Mantenimiento'],
            ['code' => 'no_available', 'name' => 'Bloqueada manualmente / fuera de servicio'],
        ];

        foreach ($data as $row) {
            DB::table('room_status_history_statuses')->updateOrInsert(
                ['code' => $row['code']],
                array_merge($row, ['created_at' => $now, 'updated_at' => $now])
            );
        }
    }
}
