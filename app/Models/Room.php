<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Enums\RoomDisplayStatus;
use Carbon\Carbon;

class Room extends Model
{
    use HasFactory;
    protected $fillable = [
        'room_number',
        'room_type_id',
        'ventilation_type_id',
        'beds_count',
        'max_capacity',
        'base_price_per_night',
        'last_cleaned_at',
        'is_active',
        'last_cleaned_at',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'room_type_id' => 'integer',
        'ventilation_type_id' => 'integer',
        'beds_count' => 'integer',
        'max_capacity' => 'integer',
        'base_price_per_night' => 'decimal:2',
        'is_active' => 'boolean',
        'last_cleaned_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the room type associated with the room.
     */
    public function RoomType()
    {
        return $this->belongsTo(RoomType::class);
    }


    /**
     * Get the ventilation type associated with the room.
     */
    public function VentilationType()
    {
        return $this->belongsTo(VentilationType::class);
    }

    /**
     * Get the reservations for the room.
     */
    public function reservations()
    {
        return $this->belongsToMany(Reservation::class,'reservation_rooms');
    }

    /**
     * Get the reservation rooms for the room.
     */
    public function reservationRooms()
    {
        return $this->hasMany(ReservationRoom::class);
    }

    /**
     * Get the actual stays (occupations) for the room.
     * CRITICAL: Stays determine if a room is OCCUPIED, not reservations.
     */
    public function stays()
    {
        return $this->hasMany(Stay::class);
    }

    /**
     * Get the maintenance blocks for the room.
     */
    public function maintenanceBlocks()
    {
        return $this->hasMany(RoomMaintenanceBlock::class);
    }

    public function dailyStatuses(): HasMany
    {
        return $this->hasMany(RoomDailyStatus::class);
    }

    /**
     * Get the special rates for the room.
     */
    public function rates()
    {
        return $this->hasMany(RoomRate::class);
    }

    /**
     * Get the dynamic price list for a specific date.
     */
    public function getPricesForDate($date)
    {
        return $this->rates;
    }

    /**
     * Check if the room is occupied on a specific date.
     * DEPRECATED: Use RoomAvailabilityService instead for correct interval logic.
     * 
     * This method is kept for backward compatibility only.
     * It will be removed in a future version.
     *
     * @param \Carbon\Carbon|null $date Date to check. Defaults to today.
     * @return bool True if room has an active reservation on the given date.
     */
    public function isOccupied(?\Carbon\Carbon $date = null): bool
    {
        return $this->isOccupiedOn($date ?? Carbon::today());
    }

    /**
     * SINGLE SOURCE OF TRUTH: determina si la habitación está ocupada en una fecha.
     * Evalúa stays con lógica de intervalos: check_in <= endOfDay y (check_out NULL o > startOfDay).
     */
    public function isOccupiedOn(Carbon $date): bool
    {
        $startOfDay = $date->copy()->startOfDay();
        $endOfDay = $date->copy()->endOfDay();

        return $this->stays()
            ->where('check_in_at', '<=', $endOfDay)
            ->where(function ($q) use ($startOfDay) {
                $q->whereNull('check_out_at')
                  ->orWhere('check_out_at', '>', $startOfDay);
            })
            ->where('status', '!=', 'finished')
            ->exists();
    }

    /**
     * Get the active reservation for a specific date.
     * DEPRECATED: Use RoomAvailabilityService instead.
     *
     * @param \Carbon\Carbon|null $date Date to check. Defaults to today.
     * @return \App\Models\Reservation|null
     */
    public function getActiveReservation(?\Carbon\Carbon $date = null): ?\App\Models\Reservation
    {
        return $this->getAvailabilityService()->getStayForDate($date)?->reservation;
    }

    /**
     * Check if the room is in maintenance (blocks everything).
     *
     * @return bool
     */
    public function isInMaintenance(): bool
    {
        return $this->maintenanceBlocks()
            ->whereHas('status', function($q) {
                $q->where('code', 'active');
            })
            ->exists();
    }

    /**
     * Get the cleaning status of the room.
     * SINGLE SOURCE OF TRUTH for cleaning status.
     *
     * Rules (Single Responsibility Principle - each rule is clear):
     * - If never cleaned (last_cleaned_at is NULL) → needs cleaning
     * - If room is OCCUPIED and 24+ hours have passed since last_cleaned_at → needs cleaning
     * - If room is FREE → clean (no 24-hour rule, stays clean indefinitely)
     * - If room is OCCUPIED but less than 24 hours have passed → clean
     *
     * IMPORTANT: The 24-hour rule ONLY applies when the room is occupied.
     * A free room that hasn't been used stays clean indefinitely.
     *
     * Cleaning status is managed explicitly:
     * - When a room is released as "libre" or "limpia" → last_cleaned_at = now() (clean)
     * - When a room is released as "pendiente_aseo" → last_cleaned_at = null (needs cleaning)
     * - When a stay is continued → last_cleaned_at = null (will need cleaning when released)
     *
     * @param \Carbon\Carbon|null $date Date to check. Defaults to today.
     * @return array{code: string, label: string, color: string, icon: string}
     */
    public function cleaningStatus(?\Carbon\Carbon $date = null): array
    {
        $date = $date ?? \Carbon\Carbon::today();
        $today = Carbon::today();
        $queryDate = $date->copy()->startOfDay();
        $endOfQueryDay = $queryDate->copy()->endOfDay();
        $isPastDate = $queryDate->lt($today);

        // CRITICAL: For PAST dates, use historical logic (immutable)
        if ($isPastDate) {
            return $this->calculateHistoricalCleaningStatus($date);
        }

        // For TODAY and FUTURE dates: use reactive logic
        return $this->calculateCurrentCleaningStatus($date);
    }

    /**
     * Calculate cleaning status for PAST dates (historical, immutable).
     * 
     * Uses ONLY information that existed ON or BEFORE the query date.
     * Never uses current state or future events.
     * 
     * @param \Carbon\Carbon $date
     * @return array
     */
    private function calculateHistoricalCleaningStatus(Carbon $date): array
    {
        $queryDate = $date->copy()->startOfDay();
        $endOfQueryDay = $queryDate->copy()->endOfDay();

        // Check if there was a stay active on this date
        $stay = $this->getAvailabilityService()->getStayForDate($date);

        if ($stay) {
            // During an active stay, room is always considered clean
            return $this->getCleanStatus();
        }

        // No stay on this date, check if a stay ended on or before this date
        $lastStayBeforeDate = $this->stays()
            ->whereNotNull('check_out_at')
            ->where('check_out_at', '<=', $endOfQueryDay)
            ->orderByDesc('check_out_at')
            ->first();

        if (!$lastStayBeforeDate) {
            // No stay ever existed on or before this date → clean
            return $this->getCleanStatus();
        }

        // There was a checkout on or before this date
        // Check if it was cleaned by the end of this day
        if (!$this->last_cleaned_at) {
            // Never cleaned → pending_cleaning
            return $this->getPendingCleaningStatus();
        }

        $cleaningDate = $this->last_cleaned_at instanceof \Carbon\Carbon
            ? $this->last_cleaned_at
            : \Carbon\Carbon::parse($this->last_cleaned_at);

        // CRITICAL: Only consider cleaning if it happened on or before the query date
        // If cleaning happened AFTER the query date, it doesn't affect the past
        if ($cleaningDate->gt($endOfQueryDay)) {
            // Cleaning happened AFTER this date → pending_cleaning on this date
            return $this->getPendingCleaningStatus();
        }

        // Cleaning happened on or before this date
        // Check if it was after the checkout
        if ($cleaningDate->lt($lastStayBeforeDate->check_out_at)) {
            // Cleaned before checkout → pending_cleaning
            return $this->getPendingCleaningStatus();
        }

        // Cleaned after checkout and on or before query date → clean
        return $this->getCleanStatus();
    }

    /**
     * Calculate cleaning status for TODAY and FUTURE dates (reactive).
     * 
     * @param \Carbon\Carbon $date
     * @return array
     */
    private function calculateCurrentCleaningStatus(Carbon $date): array
    {
        // If never cleaned or explicitly marked as needing cleaning (last_cleaned_at is NULL)
        if (!$this->last_cleaned_at) {
            return $this->getPendingCleaningStatus();
        }

        // Check if room is currently occupied (Dependency Inversion - uses abstraction)
        $isOccupied = $this->isOccupied($date);

        // If room is NOT occupied, it stays clean indefinitely (Open/Closed Principle)
        if (!$isOccupied) {
            return $this->getCleanStatus();
        }

        // If room is OCCUPIED, check if cleaning occurred before check-in
        // Get active reservation to compare cleaning date with check-in date
        $reservation = $this->reservations()
            ->where('check_in_date', '<=', $date)
            ->where('check_out_date', '>', $date)
            ->orderBy('check_in_date', 'asc')
            ->first();

        // Parse and normalize cleaning date
        $cleaningDate = $this->last_cleaned_at instanceof \Carbon\Carbon
            ? $this->last_cleaned_at
            : \Carbon\Carbon::parse($this->last_cleaned_at);

        $cleaningDateNormalized = $cleaningDate->copy()->startOfDay();

        // If room was cleaned BEFORE check-in, it stays clean during the stay
        // Hotel rule: cleaning before guest arrival is valid for the entire initial stay
        if ($reservation) {
            $checkInDate = \Carbon\Carbon::parse($reservation->check_in_date)->startOfDay();

            // If cleaning occurred before check-in, room is clean (no 24-hour rule applies)
            if ($cleaningDateNormalized->lt($checkInDate)) {
                return $this->getCleanStatus();
            }
        }

        // If room was cleaned AFTER check-in (or on check-in day), apply 24-hour rule
        // Calculate hours from last_cleaned_at to the queried date (not now())
        // This ensures consistency when querying past or future dates
        $queryDateNormalized = $date->copy()->startOfDay();

        $hoursSinceLastCleaning = $cleaningDateNormalized->diffInHours($queryDateNormalized);

        if ($hoursSinceLastCleaning >= 24) {
            return $this->getPendingCleaningStatus();
        }

        return $this->getCleanStatus();
    }

    /**
     * Get pending cleaning status array (Single Responsibility)
     *
     * @return array{code: string, label: string, color: string, icon: string}
     */
    private function getPendingCleaningStatus(): array
    {
        return [
            'code' => 'pendiente',
            'label' => 'Pendiente por Aseo',
            'color' => 'bg-yellow-100 text-yellow-800',
            'icon' => 'fa-broom',
        ];
    }

    /**
     * Get clean status array (Single Responsibility)
     *
     * @return array{code: string, label: string, color: string, icon: string}
     */
    private function getCleanStatus(): array
    {
        return [
            'code' => 'limpia',
            'label' => 'Limpia',
            'color' => 'bg-green-100 text-green-800',
            'icon' => 'fa-check-circle',
        ];
    }

    /**
     * SINGLE SOURCE OF TRUTH: Get operational status for room actions menu.
     * 
     * CRITICAL: For PAST dates, this method calculates the state based on what happened
     * ON THAT SPECIFIC DATE, not the current state. Past dates are IMMUTABLE.
     * 
     * Returns the operational state of the room based on:
     * - Active stays (occupied)
     * - Last checkout + cleaning status (pending_cleaning)
     * - Default state (free_clean)
     * 
     * This method drives the room-actions-menu buttons.
     * 
     * @param \Carbon\Carbon $date Date to check
     * @return string 'occupied' | 'pending_cleaning' | 'free_clean'
     */
    public function getOperationalStatus(Carbon $date): string
    {
        $today = Carbon::today();
        $queryDate = $date->copy()->startOfDay();
        $endOfQueryDay = $queryDate->copy()->endOfDay();
        $isPastDate = $queryDate->lt($today);

        // CRITICAL: For PAST dates, use historical logic (immutable)
        if ($isPastDate) {
            return $this->calculateHistoricalOperationalStatus($date);
        }

        // For TODAY and FUTURE dates: use reactive logic
        return $this->calculateCurrentOperationalStatus($date);
    }

    /**
     * Calculate operational status for PAST dates (historical, immutable).
     * 
     * Uses ONLY information that existed ON or BEFORE the query date.
     * Never uses current state or future events.
     * 
     * SINGLE SOURCE OF TRUTH: Based exclusively on stays, reservation_rooms.check_out_date,
     * stays.check_out_at, last_cleaned_at, and the selected date.
     * 
     * @param \Carbon\Carbon $date
     * @return string
     */
    private function calculateHistoricalOperationalStatus(Carbon $date): string
    {
        // Get stay that intersected this date
        $stay = $this->getAvailabilityService()->getStayForDate($date);

        // 1. OCCUPIED: If there was a stay active on this date (check_out_at is NULL or after this date)
        if ($stay && (is_null($stay->check_out_at) || $stay->check_out_at->gt($date->copy()->endOfDay()))) {
            return 'occupied';
        }

        // 2. PENDING_CLEANING: If there was a checkout on or before this date
        // and it wasn't cleaned after checkout (by the end of this day)
        $lastFinishedStay = $this->stays()
            ->whereNotNull('check_out_at')
            ->whereDate('check_out_at', '<=', $date)
            ->latest('check_out_at')
            ->first();

        if ($lastFinishedStay) {
            $endOfQueryDay = $date->copy()->endOfDay();
            
            // If never cleaned OR cleaned before checkout OR cleaned after the query date
            if (!$this->last_cleaned_at) {
                return 'pending_cleaning';
            }

            $cleaningDate = $this->last_cleaned_at instanceof \Carbon\Carbon
                ? $this->last_cleaned_at
                : \Carbon\Carbon::parse($this->last_cleaned_at);

            // CRITICAL: Only consider cleaning if it happened on or before the query date
            if ($cleaningDate->gt($endOfQueryDay)) {
                // Cleaning happened AFTER this date → pending_cleaning on this date
                return 'pending_cleaning';
            }

            // Check if cleaning was after checkout
            if ($cleaningDate->lt($lastFinishedStay->check_out_at)) {
                // Cleaned before checkout → pending_cleaning
                return 'pending_cleaning';
            }

            // Cleaned after checkout and on or before query date → free_clean
            return 'free_clean';
        }

        // 3. FREE_CLEAN: No stay ever existed on or before this date
        return 'free_clean';
    }

    /**
     * Check if room is pending checkout for a specific date.
     * 
     * CRITICAL: This can ONLY be true for TODAY, never for past or future dates.
     * A room is pending checkout if:
     * - There's an active stay (check_out_at is NULL)
     * - The reservation_room.check_out_date is in the past (before today)
     * - The date being queried is TODAY
     * 
     * @param \Carbon\Carbon $date Date to check
     * @return bool True only if pending checkout TODAY
     */
    public function isPendingCheckout(Carbon $date): bool
    {
        // CRITICAL: Only check for TODAY, never for past or future dates
        if (!$date->isToday()) {
            return false;
        }

        // Get stay that intersects today
        $stay = $this->getAvailabilityService()->getStayForDate($date);

        if (!$stay) {
            return false;
        }

        // Stay must be active (check_out_at is NULL)
        if (!is_null($stay->check_out_at)) {
            return false;
        }

        // Get reservation_room to check check_out_date
        $reservation = $stay->reservation;
        if (!$reservation) {
            return false;
        }

        $reservationRoom = $reservation->reservationRooms
            ->where('room_id', $this->id)
            ->first();

        if (!$reservationRoom || !$reservationRoom->check_out_date) {
            return false;
        }

        // Check if check_out_date is in the past (before today)
        $checkoutDate = \Carbon\Carbon::parse($reservationRoom->check_out_date)->startOfDay();
        $today = Carbon::today();

        // Pending checkout if check_out_date < today AND stay is still active
        return $checkoutDate->lt($today);
    }

    /**
     * Calculate operational status for TODAY and FUTURE dates (reactive).
     * 
     * SINGLE SOURCE OF TRUTH: Based exclusively on stays, reservation_rooms.check_out_date,
     * stays.check_out_at, last_cleaned_at, and the selected date.
     * 
     * @param \Carbon\Carbon $date
     * @return string
     */
    private function calculateCurrentOperationalStatus(Carbon $date): string
    {
        // Get stay that intersects this date
        $stay = $this->getAvailabilityService()->getStayForDate($date);

        // 1. OCCUPIED: If there's an active stay on this date (check_out_at is NULL)
        if ($stay && is_null($stay->check_out_at)) {
            return 'occupied';
        }

        // 2. PENDING_CLEANING: If there was a checkout before or on this date
        // and it wasn't cleaned after checkout
        $lastFinishedStay = $this->stays()
            ->whereNotNull('check_out_at')
            ->whereDate('check_out_at', '<=', $date)
            ->latest('check_out_at')
            ->first();

        if ($lastFinishedStay) {
            // If never cleaned OR cleaned before the checkout
            if (!$this->last_cleaned_at || $this->last_cleaned_at->lt($lastFinishedStay->check_out_at)) {
                return 'pending_cleaning';
            }
        }

        // 3. FREE_CLEAN: Default state (free and clean)
        return 'free_clean';
    }

    /**
     * Get the display status of the room for a specific date.
     * 
     * DEPRECATED: Use RoomAvailabilityService->getDisplayStatusOn() instead.
     * This method is kept for backward compatibility.
     *
     * @param \Carbon\Carbon|null $date Date to check. Defaults to today.
     * @return RoomDisplayStatus
     */
    public function getDisplayStatus(?\Carbon\Carbon $date = null): RoomDisplayStatus
    {
        return $this->getAvailabilityService()->getDisplayStatusOn($date);
    }

    /**
     * Get the RoomAvailabilityService for this room.
     * 
     * Centralizes room availability logic and ensures correct interval-based calculations.
     * 
     * @return \App\Services\RoomAvailabilityService
     */
    public function getAvailabilityService(): \App\Services\RoomAvailabilityService
    {
        return new \App\Services\RoomAvailabilityService($this);
    }

    /**
     * Get the reservation that causes Pendiente Checkout status for a specific date.
     *
     * @param \Carbon\Carbon|null $date Date to check. Defaults to today.
     * @return \App\Models\Reservation|null
     */
    public function getPendingCheckoutReservation(?\Carbon\Carbon $date = null): ?\App\Models\Reservation
    {
        $date = $date ?? \Carbon\Carbon::today();

        // If not in Pendiente Checkout status, return null
        if ($this->getDisplayStatus($date) !== RoomDisplayStatus::PENDIENTE_CHECKOUT) {
            return null;
        }

        $previousDay = $date->copy()->subDay();
        $tomorrow = $date->copy()->addDay();

        // Case 1: Was occupied yesterday, checkout today
        $reservationEndingToday = $this->reservations()
            ->where('check_in_date', '<=', $previousDay)
            ->where('check_out_date', '=', $date->toDateString())
            ->first();

        if ($reservationEndingToday) {
            return $reservationEndingToday;
        }

        // Case 2: Reservation starts today and is one-day reservation (check-in today, check-out tomorrow)
        $oneDayReservationStartingToday = $this->reservations()
            ->where('check_in_date', '=', $date->toDateString())
            ->where('check_out_date', '=', $tomorrow->toDateString())
            ->first();

        if ($oneDayReservationStartingToday) {
            return $oneDayReservationStartingToday;
        }

        // Case 3: Reservation has one day remaining (check-out tomorrow)
        $reservationEndingTomorrow = $this->reservations()
            ->where('check_in_date', '<=', $date)
            ->where('check_out_date', '=', $tomorrow->toDateString())
            ->first();

        return $reservationEndingTomorrow;
    }

    /**
     * Accessor for display_status attribute.
     * Uses getDisplayStatus() with today's date by default.
     * This allows using $room->display_status in views.
     *
     * @return RoomDisplayStatus
     */
    public function getDisplayStatusAttribute(): RoomDisplayStatus
    {
        return $this->getDisplayStatus();
    }

    /**
     * Accessor for cleaning_status attribute.
     * Uses cleaningStatus() with today's date by default.
     * This allows using $room->cleaning_status in views.
     *
     * @return array{code: string, label: string, color: string, icon: string}
     */
    public function getCleaningStatusAttribute(): array
    {
        return $this->cleaningStatus();
    }

    /**
     * Validate and clean invalid reservations.
     * This helps maintain data integrity (Interface Segregation Principle)
     *
     * @return array{invalid_count: int, invalid_reservations: \Illuminate\Database\Eloquent\Collection}
     */
    public function validateReservations(): array
    {
        $today = \Carbon\Carbon::today();

        // Find reservations with invalid date ranges (check_out <= check_in)
        $invalidReservations = $this->reservations()
            ->whereColumn('check_out_date', '<=', 'check_in_date')
            ->get();

        if ($invalidReservations->isNotEmpty()) {
            \Illuminate\Support\Facades\Log::warning("Room {$this->room_number} has invalid reservations", [
                'room_id' => $this->id,
                'room_number' => $this->room_number,
                'invalid_count' => $invalidReservations->count(),
                'reservation_ids' => $invalidReservations->pluck('id')->toArray()
            ]);
        }

        return [
            'invalid_count' => $invalidReservations->count(),
            'invalid_reservations' => $invalidReservations
        ];
    }
}
