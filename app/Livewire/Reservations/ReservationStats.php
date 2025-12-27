<?php

namespace App\Livewire\Reservations;

use Livewire\Component;
use App\Models\Reservation;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ReservationStats extends Component
{
    protected $listeners = [
        'reservation-created' => '$refresh',
        'reservation-updated' => '$refresh',
        'reservation-deleted' => '$refresh',
    ];

    public function getTotalReservationsProperty(): int
    {
        return Reservation::count();
    }

    public function getActiveReservationsProperty(): int
    {
        $today = Carbon::today();
        
        return Reservation::where('check_in_date', '<=', $today)
            ->where('check_out_date', '>=', $today)
            ->count();
    }

    public function getCancelledReservationsProperty(): int
    {
        // Since reservations are hard deleted, we can't count cancelled ones
        // This metric is kept for future implementation with soft deletes or status field
        return 0;
    }

    public function getOccupiedRoomsTodayProperty(): int
    {
        $today = Carbon::today();
        
        // Get rooms from reservation_rooms pivot table (multi-room reservations)
        $roomsFromPivot = DB::table('reservations')
            ->join('reservation_rooms', 'reservations.id', '=', 'reservation_rooms.reservation_id')
            ->where('reservations.check_in_date', '<=', $today)
            ->where('reservations.check_out_date', '>=', $today)
            ->distinct('reservation_rooms.room_id')
            ->pluck('reservation_rooms.room_id');
        
        // Get rooms from room_id field (backward compatibility for single-room reservations)
        $roomsFromField = DB::table('reservations')
            ->where('check_in_date', '<=', $today)
            ->where('check_out_date', '>=', $today)
            ->whereNotNull('room_id')
            ->distinct('room_id')
            ->pluck('room_id');
        
        // Merge and count unique rooms
        return $roomsFromPivot->merge($roomsFromField)->unique()->count();
    }

    public function getReservationsTodayProperty(): int
    {
        $today = Carbon::today();
        
        return Reservation::whereDate('check_in_date', $today)
            ->count();
    }

    public function getTotalGuestsTodayProperty(): int
    {
        $today = Carbon::today();
        
        return Reservation::where('check_in_date', '<=', $today)
            ->where('check_out_date', '>=', $today)
            ->sum('guests_count');
    }

    public function render()
    {
        return view('livewire.reservations.reservation-stats');
    }
}

