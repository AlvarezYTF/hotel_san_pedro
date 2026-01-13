<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Stay Model
 *
 * Representa una ocupación real de una habitación.
 * Una Stay es un registro que marca cuándo una habitación fue ocupada por un huésped.
 *
 * Diferencia crítica vs Reservation:
 * - Reservation: bloqueo futuro de una habitación (check-in y check-out son FECHAS)
 * - Stay: ocupación real actual (check_in_at y check_out_at son TIMESTAMPS)
 *
 * Una habitación está OCUPADA si hay una Stay activa que intersecta la fecha actual.
 */
class Stay extends Model
{
    protected $table = 'stays';

    // No usamos timestamps en stays (es un log inmutable de ocupaciones)
    public $timestamps = false;

    protected $fillable = [
        'reservation_id',
        'room_id',
        'check_in_at',
        'check_out_at',
        'status', // string: active, pending_checkout, finished
    ];

    protected $casts = [
        'check_in_at' => 'datetime',
        'check_out_at' => 'datetime',
    ];

    /**
     * Get the reservation associated with this stay.
     */
    public function reservation(): BelongsTo
    {
        return $this->belongsTo(Reservation::class);
    }

    /**
     * Get the room where this stay occurred.
     */
    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    /**
     * Check if stay is active (status = 'active').
     * Status values: active, pending_checkout, finished
     */
    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    /**
     * Check if stay is finished (status = 'finished').
     */
    public function isFinished(): bool
    {
        return $this->status === 'finished';
    }

    /**
     * Determine if this stay occupies a specific date.
     *
     * A stay occupies a date if:
     * - check_in_at < end_of_day(date)
     * - AND (check_out_at IS NULL OR check_out_at > start_of_day(date))
     *
     * @param \Carbon\Carbon|null $date
     * @return bool
     */
    public function occupiesDate($date = null): bool
    {
        $date = $date ?? now()->startOfDay();
        $startOfDay = $date->copy()->startOfDay();
        $endOfDay = $date->copy()->endOfDay();

        $hasStartedBeforeEndOfDay = $this->check_in_at?->lt($endOfDay) ?? false;
        $hasNotEndedBeforeStartOfDay = $this->check_out_at === null || $this->check_out_at->gt($startOfDay);

        return $hasStartedBeforeEndOfDay && $hasNotEndedBeforeStartOfDay;
    }

    /**
     * Scope: Get only active stays (status = 'active').
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope: Get stays that overlap with a specific date.
     */
    public function scopeOverlappingDate($query, $date = null)
    {
        $date = $date ?? now();
        $startOfDay = $date->copy()->startOfDay();
        $endOfDay = $date->copy()->endOfDay();

        return $query
            ->where('check_in_at', '<', $endOfDay)
            ->where(function ($q) use ($startOfDay) {
                $q->whereNull('check_out_at')
                  ->orWhere('check_out_at', '>', $startOfDay);
            });
    }
}
