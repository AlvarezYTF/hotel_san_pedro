<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

class ReservationStatusSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        $data = [
            ['code' => 'pending', 'name' => 'Pending'],
            ['code' => 'confirmed', 'name' => 'Confirmed'],
            ['code' => 'checked_in', 'name' => 'Checked In'],
            ['code' => 'checked_out', 'name' => 'Checked Out'],
            ['code' => 'cancelled', 'name' => 'Cancelled'],
            ['code' => 'no_show', 'name' => 'No Show'],
        ];

        foreach ($data as $row) {
            DB::table('reservation_statuses')->updateOrInsert(
                ['code' => $row['code']],
                array_merge($row, ['updated_at' => $now, 'created_at' => $now])
            );
        }
    }
}
