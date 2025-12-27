<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReservationRoom extends Model
{
    protected $fillable = [
        'reservation_id',
        'room_id',
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
     */
    public function guests()
    {
        return $this->belongsToMany(Customer::class, 'reservation_room_guests', 'reservation_room_id', 'customer_id')
                    ->withTimestamps()
                    ->withTrashed();
    }
}



