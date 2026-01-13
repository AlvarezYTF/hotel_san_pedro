<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReservationRoom extends Model
{

    protected $table = "reservation_rooms";

    protected $fillable = [
        'reservation_id',
        'room_id',
        'check_in_date',
        'check_out_date',
        'check_in_time',
        'check_out_time',
        'nights',
        'price_per_night',
        'subtotal',
        'created_at',
        'updated_at'
    ];

    /**
     * Get the reservation that owns this room assignment.
     */
    public function reservation()
    {
        return $this->belongsTo(Reservation::class);
    }

    /**
     * Get the room assigned to this reservation.
     */
    public function room()
    {
        return $this->belongsTo(Room::class);
    }

    /**
     * Get the guests assigned to this specific room in the reservation.
     * 
     * Estructura de BD:
     * - reservation_room_guests.reservation_guest_id -> reservation_guests.id
     * - reservation_guests.guest_id -> customers.id
     * 
     * Relación personalizada usando subquery para acceder a customers
     * a través de reservation_room_guests -> reservation_guests.
     * 
     * NOTA: Esta relación retorna un Builder, no una relación Eloquent estándar.
     * Para usar con eager loading, cargar manualmente o usar el método getGuests().
     */
    public function guests()
    {
        return Customer::query()
            ->whereIn('id', function ($query) {
                $query->select('reservation_guests.guest_id')
                    ->from('reservation_room_guests')
                    ->join('reservation_guests', 'reservation_room_guests.reservation_guest_id', '=', 'reservation_guests.id')
                    ->where('reservation_room_guests.reservation_room_id', $this->getKey())
                    ->whereNotNull('reservation_guests.guest_id');
            })
            ->withTrashed();
    }
    
    /**
     * Get guests as a collection (helper method for easier usage).
     */
    public function getGuests()
    {
        try {
            return $this->guests()->get();
        } catch (\Exception $e) {
            \Log::warning('Error loading guests for ReservationRoom', [
                'reservation_room_id' => $this->id,
                'error' => $e->getMessage()
            ]);
            return collect();
        }
    }
}



