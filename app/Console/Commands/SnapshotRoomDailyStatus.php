<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Carbon\Carbon;
use App\Models\Room;
use App\Models\RoomDailyStatus;
use App\Enums\RoomStatus;

class SnapshotRoomDailyStatus extends Command
{
    protected $signature = 'rooms:snapshot {date : Fecha (YYYY-MM-DD) a registrar}';

    protected $description = 'Genera snapshot diario de estados de habitaciones para la fecha indicada.';

    public function handle(): int
    {
        $dateInput = $this->argument('date');

        try {
            $snapshotDate = Carbon::parse($dateInput)->startOfDay();
        } catch (\Throwable $e) {
            $this->error('Fecha inválida, usa formato YYYY-MM-DD.');
            return Command::FAILURE;
        }

        $today = Carbon::today();
        if ($snapshotDate->gt($today)) {
            $this->error('No se permiten snapshots de fechas futuras.');
            return Command::FAILURE;
        }

        Room::chunkById(100, function ($rooms) use ($snapshotDate) {
            foreach ($rooms as $room) {
                // Capture the DISPLAY status - this is what the user sees in the interface
                // This includes "Ocupada" when there's a reservation, even if status field is "Libre"
                // This preserves exactly how the room appeared at the end of the day
                $displayStatus = $room->getDisplayStatus($snapshotDate);
                
                // Capture the cleaning status at the end of the day
                $cleaning = $room->cleaningStatus($snapshotDate);

                // Get reservation info if exists (for historical reference)
                $reservation = $room->reservations()
                    ->where('check_in_date', '<=', $snapshotDate)
                    ->where('check_out_date', '>', $snapshotDate)
                    ->with('customer')
                    ->first();

                // If no active reservation, check for pending checkout
                if (!$reservation) {
                    $reservation = $room->getPendingCheckoutReservation($snapshotDate);
                }

                RoomDailyStatus::updateOrCreate(
                    [
                        'room_id' => $room->id,
                        'date' => $snapshotDate->toDateString(),
                    ],
                    [
                        'status' => $displayStatus, // Display status - what user sees (includes "Ocupada" from reservations)
                        'cleaning_status' => $cleaning['code'],
                        'reservation_id' => $reservation?->id,
                        'guest_name' => $reservation?->customer?->name ?? null,
                        'check_out_date' => $reservation?->check_out_date ?? null,
                        'total_amount' => $reservation?->total_amount ?? 0,
                    ]
                );
            }
        });

        $this->info("Snapshot generado para {$snapshotDate->toDateString()}.");
        return Command::SUCCESS;
    }
}
