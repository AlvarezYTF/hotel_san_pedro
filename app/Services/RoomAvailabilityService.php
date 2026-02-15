<?php

namespace App\Services;

use App\Models\Room;
use App\Models\Stay;
use App\Models\ReservationRoom;
use App\Enums\RoomDisplayStatus;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * RoomAvailabilityService
 * 
 * Determina el estado de una habitación en una fecha específica o para un rango de fechas.
 * Implementa la regla de negocio: una ocupación es un INTERVALO DE TIEMPO.
 * 
 * Una habitación estuvo OCUPADA en una fecha X SÍ Y SOLO SÍ:
 * - check_in_date < end_of_day(X)  [ocupación comenzó antes de que termine el día X]
 * - Y (check_out_date IS NULL OR check_out_date > start_of_day(X))  [aún no ha salido el día X]
 * 
 * Responsabilidades:
 * - Calcular correctamente la intersección entre intervalos de tiempo
 * - Respetar que días pasados son históricos (solo lectura)
 * - No permitir modificaciones en días pasados
 * - Retornar un estado claro y bloquear acciones operativas si es necesario
 * - MVP: Fase 1 - Soportar verificación de disponibilidad por rango de fechas
 */
class RoomAvailabilityService
{
    private ?Room $room;

    public function __construct(?Room $room = null)
    {
        $this->room = $room;
    }

    /**
     * Determina si una habitación estuvo ocupada en una fecha específica.
     * 
     * DELEGADO A: getStayForDate() - single source of truth
     * 
     * @param Carbon|null $date Fecha a consultar. Por defecto, hoy.
     * @return bool True si la habitación estuvo ocupada en esa fecha.
     */
    public function isOccupiedOn(?Carbon $date = null): bool
    {
        return $this->getStayForDate($date) !== null;
    }

    /**
     * SINGLE SOURCE OF TRUTH: Obtiene el stay que intersecta una fecha específica.
     * 
     * Implementa la regla de negocio correcta:
     * Un stay intersecta con una fecha D si y solo si:
     * - check_in_at < endOfDay(D)  [el stay comenzó antes de que termine el día D]
     * - Y check_out_at >= startOfDay(D)  [el stay no terminó antes del día D]
     * 
     * CRÍTICO: 
     * - NO filtra por status='active' porque para fechas históricas necesitamos stays finished
     * - Si check_out_at IS NULL, usa la check_out_date de reservation_rooms como fallback
     * - El estado del stay es independiente de si intersecta una fecha
     * 
     * @param Carbon|null $date Fecha a consultar. Por defecto, hoy.
     * @return \App\Models\Stay|null El stay que ocupa la habitación en esa fecha, o null
     */
    public function getStayForDate(?Carbon $date = null): ?\App\Models\Stay
    {
        $date = $date ?? Carbon::today();
        $startOfDay = $date->copy()->startOfDay();
        $endOfDay = $date->copy()->endOfDay();

        // CRITICAL: Usar nueva query directamente, NO la relación en memoria
        // Esto evita problemas de caché cuando se crea una nueva stay
        $stayQuery = \App\Models\Stay::query()
            ->where('room_id', $this->room->id)
            ->with([
                'reservation.customer',
                'reservation.reservationRooms' => function ($query) {
                    $query->where('room_id', $this->room->id);
                }
            ])
            ->where('check_in_at', '<=', $endOfDay);

        // CRÍTICO: Debe haber un check_out que sea >= startOfDay
        // Si check_out_at IS NULL, usamos la fecha de checkout de reservation_rooms
        // IMPORTANTE: Para fechas futuras, solo retornar stay si la fecha está ANTES del checkout
        $isFutureDate = $date->isFuture();
        
        $stayQuery->where(function ($query) use ($startOfDay, $endOfDay, $isFutureDate) {
            $query->where(function ($q) use ($startOfDay) {
                // Caso 1: check_out_at IS NOT NULL y es >= startOfDay
                // Esto significa que el checkout ocurrió en o después de esta fecha
                $q->whereNotNull('check_out_at')
                  ->where('check_out_at', '>=', $startOfDay);
            })
            ->orWhere(function ($q) use ($startOfDay, $endOfDay, $isFutureDate) {
                // Caso 2: check_out_at IS NULL, pero reservation_rooms.check_out_date debe ser >= startOfDay
                $q->whereNull('check_out_at')
                  ->whereHas('reservation', function ($r) use ($startOfDay, $endOfDay, $isFutureDate) {
                      $r->whereHas('reservationRooms', function ($rr) use ($startOfDay, $endOfDay, $isFutureDate) {
                          $rr->where('room_id', $this->room->id)
                             ->where('check_out_date', '>=', $startOfDay->toDateString());
                          
                          // CRITICAL: Para fechas futuras, solo retornar si la fecha consultada está DENTRO del rango
                          // Regla: check_out_date >= endOfDay (la fecha consultada es <= día del checkout)
                          // Esto asegura que:
                          // - Si la fecha consultada es ANTES del checkout: check_out_date > endOfDay → retorna stay
                          // - Si la fecha consultada ES el día del checkout: check_out_date = endOfDay → retorna stay
                          // - Si la fecha consultada es DESPUÉS del checkout: check_out_date < endOfDay → NO retorna stay
                          if ($isFutureDate) {
                              $rr->where('check_out_date', '>=', $endOfDay->toDateString());
                          }
                      });
                  });
            });
        });

        return $stayQuery
            ->orderBy('check_in_at', 'desc') // El más reciente primero
            ->first();
    }

    /**
     * Determina si una fecha es histórica (pasada).
     * 
     * Una fecha es histórica si es anterior a hoy.
     * El sistema NO debe permitir modificaciones en fechas históricas.
     * 
     * @param Carbon|null $date Fecha a consultar. Por defecto, ahora.
     * @return bool True si la fecha es histórica.
     */
    public function isHistoricDate(?Carbon $date = null): bool
    {
        $date = $date ?? now();
        return $date->copy()->startOfDay()->lt(Carbon::today());
    }

    /**
     * Determina si las modificaciones operativas (check-in, checkout, cambios de estado)
     * están permitidas para una fecha específica.
     * 
     * Regla: Solo hoy y fechas futuras permiten modificaciones.
     * Las fechas históricas son solo lectura.
     * 
     * @param Carbon|null $date Fecha a consultar. Por defecto, ahora.
     * @return bool True si se permiten modificaciones.
     */
    public function canModifyOn(?Carbon $date = null): bool
    {
        return !$this->isHistoricDate($date);
    }

    /**
     * Obtiene el estado de checkout pendiente para una fecha.
     * 
     * Una habitación está en PENDIENTE_CHECKOUT si:
     * - Estuvo ocupada ayer
     * - La ocupación termina hoy (check_out_at = hoy)
     * 
     * @param Carbon|null $date Fecha a consultar. Por defecto, hoy.
     * @return bool
     */
    public function hasPendingCheckoutOn(?Carbon $date = null): bool
    {
        $date = $date ?? Carbon::today();
        $previousDay = $date->copy()->subDay();

        // Verificar si había ocupación el día anterior
        $stayYesterday = $this->getStayForDate($previousDay);
        
        if (!$stayYesterday) {
            return false;
        }

        // Verificar si ese stay terminó hoy (check_out_at dentro de hoy)
        if ($stayYesterday->check_out_at) {
            return $stayYesterday->check_out_at->isSameDay($date);
        }

        return false;
    }

    /**
     * Obtiene el estado de limpieza de la habitación para una fecha específica.
     * 
     * Utiliza el método existente cleaningStatus() del modelo Room.
     * 
     * @param Carbon|null $date Fecha a consultar. Por defecto, hoy.
     * @return array{code: string, label: string, color: string, icon: string}
     */
    public function getCleaningStatusOn(?Carbon $date = null): array
    {
        return $this->room->cleaningStatus($date);
    }

    /**
     * Determina el estado de display de la habitación para una fecha específica.
     * 
     * Implementa la prioridad de estados:
     * 1. MANTENIMIENTO (bloquea todo)
     * 2. OCUPADA (hay stay activa)
     * 3. PENDIENTE_CHECKOUT (checkout hoy, después de ocupación ayer)
     * 4. SUCIA (needs cleaning)
     * 5. RESERVADA (reserva futura)
     * 6. LIBRE (default)
     * 
     * @param Carbon|null $date Fecha a consultar. Por defecto, hoy.
     * @return RoomDisplayStatus
     */
    public function getDisplayStatusOn(?Carbon $date = null): RoomDisplayStatus
    {
        $date = $date ?? Carbon::today();

        // Priority 1: Maintenance blocks everything
        if ($this->room->isInMaintenance()) {
            return RoomDisplayStatus::MANTENIMIENTO;
        }

        // Priority 2: Active stay = occupied
        if ($this->isOccupiedOn($date)) {
            return RoomDisplayStatus::OCUPADA;
        }

        // Priority 3: Pending checkout
        if ($this->hasPendingCheckoutOn($date)) {
            return RoomDisplayStatus::PENDIENTE_CHECKOUT;
        }

        // Priority 4: Needs cleaning
        $cleaningStatus = $this->getCleaningStatusOn($date);
        if ($cleaningStatus['code'] === 'pendiente') {
            return RoomDisplayStatus::SUCIA;
        }

        // Priority 5: Future reservation
        $hasFutureReservation = $this->room->reservationRooms()
            ->where('check_in_date', '>', $date->copy()->endOfDay()->toDateString())
            ->exists();

        if ($hasFutureReservation) {
            return RoomDisplayStatus::RESERVADA;
        }

        // Priority 6: Free (default)
        return RoomDisplayStatus::LIBRE;
    }

    /**
     * Obtiene un array con información de acceso para una fecha.
     * 
     * Útil para que el controller/Livewire sepa si puede permitir acciones.
     * 
     * @param Carbon|null $date Fecha a consultar. Por defecto, hoy.
     * @return array{isHistoric: bool, canModify: bool, status: RoomDisplayStatus, reason: string}
     */
    public function getAccessInfo(?Carbon $date = null): array
    {
        $date = $date ?? now();
        $isHistoric = $this->isHistoricDate($date);
        $canModify = $this->canModifyOn($date);
        $status = $this->getDisplayStatusOn($date);

        $reason = 'OK';
        if ($isHistoric) {
            $reason = 'Fecha histórica: datos en solo lectura.';
        } elseif ($status === RoomDisplayStatus::MANTENIMIENTO) {
            $reason = 'Habitación en mantenimiento.';
        }

        return [
            'isHistoric' => $isHistoric,
            'canModify' => $canModify,
            'status' => $status,
            'reason' => $reason,
        ];
    }

    // ============================================================================
    // FASE 1: MÉTODOS DE DISPONIBILIDAD POR RANGO DE FECHAS (MVP)
    // ============================================================================

    /**
     * Verificar si una habitación está disponible para un rango de fechas
     *
     * MVP: Validación simplificada - verificar stays activas y reservaciones futuras
     *
     * @param int $roomId ID de la habitación
     * @param Carbon $checkIn Fecha de entrada
     * @param Carbon $checkOut Fecha de salida
     * @return bool True si la habitación está disponible
     */
    public function isRoomAvailableForDates(
        int $roomId,
        Carbon $checkIn,
        Carbon $checkOut,
        ?int $excludeReservationId = null
    ): bool
    {
        Log::debug('🔍 VERIFICANDO DISPONIBILIDAD DE HABITACIÓN', [
            'roomId' => $roomId,
            'checkIn' => $checkIn->format('Y-m-d'),
            'checkOut' => $checkOut->format('Y-m-d')
        ]);

        try {
            // 1. Verificar stays que intersectan el rango para esta habitación
            $conflictingStay = $this->findConflictingStay($roomId, $checkIn, $checkOut, $excludeReservationId);
            if ($conflictingStay) {
                Log::debug("❌ Habitación {$roomId} NO disponible - Stay en conflicto", [
                    'stayId' => $conflictingStay->id,
                    'reservationId' => $conflictingStay->reservation_id,
                    'stayStatus' => $conflictingStay->status,
                    'stayCheckIn' => optional($conflictingStay->check_in_at)->format('Y-m-d H:i:s'),
                    'stayCheckOut' => optional($conflictingStay->check_out_at)->format('Y-m-d H:i:s'),
                ]);
                return false;
            }

            // 2. Verificar reservation_rooms en conflicto sin stay para esa habitación
            $conflictingReservationRoom = $this->findConflictingReservationRoom($roomId, $checkIn, $checkOut, false, $excludeReservationId);
            if ($conflictingReservationRoom) {
                Log::debug("❌ Habitación {$roomId} NO disponible - ReservationRoom en conflicto", [
                    'reservationRoomId' => $conflictingReservationRoom->id,
                    'reservationId' => $conflictingReservationRoom->reservation_id,
                    'reservationCheckIn' => optional($conflictingReservationRoom->check_in_date)->format('Y-m-d'),
                    'reservationCheckOut' => optional($conflictingReservationRoom->check_out_date)->format('Y-m-d'),
                ]);
                return false;
            }

            Log::debug("✅ Habitación {$roomId} disponible - Sin stays activas ni reservaciones");
            return true;

        } catch (\Exception $e) {
            Log::error('❌ ERROR VERIFICANDO DISPONIBILIDAD:', [
                'roomId' => $roomId,
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return false;
        }
    }

    /**
     * Obtener lista de habitaciones disponibles para un rango de fechas
     *
     * @param Carbon $checkIn Fecha de entrada
     * @param Carbon $checkOut Fecha de salida
     * @param array $rooms Lista completa de habitaciones (con datos de precios)
     * @return array Habitaciones disponibles
     */
    public function getAvailableRooms(
        Carbon $checkIn,
        Carbon $checkOut,
        array $rooms = [],
        ?int $excludeReservationId = null
    ): array
    {
        Log::info('📋 OBTENIENDO HABITACIONES DISPONIBLES', [
            'checkIn' => $checkIn->format('Y-m-d'),
            'checkOut' => $checkOut->format('Y-m-d'),
            'totalRooms' => count($rooms)
        ]);

        try {
            // Si no se proporcionan las habitaciones, obtenerlas de la BD
            if (empty($rooms)) {
                $rooms = Room::query()
                    ->active()
                    ->orderBy('room_number')
                    ->get()
                    ->map(fn($room) => [
                        'id' => $room->id,
                        'number' => $room->room_number,
                        'room_number' => $room->room_number,
                        'capacity' => $room->max_capacity,
                        'max_capacity' => $room->max_capacity,
                        'beds' => $room->beds_count,
                    ])
                    ->toArray();
            }

            // Validar rango de fechas
            if ($checkOut->lte($checkIn)) {
                Log::warning('⚠️ Rango de fechas inválido - checkOut <= checkIn');
                return [];
            }

            $availableRooms = [];

            foreach ($rooms as $room) {
                if (!is_array($room) || empty($room['id'])) {
                    Log::warning('⚠️ Habitación inválida o sin ID', ['room' => $room]);
                    continue;
                }

                $roomId = (int)$room['id'];

                // Verificar disponibilidad
                if ($this->isRoomAvailableForDates($roomId, $checkIn, $checkOut, $excludeReservationId)) {
                    $availableRooms[] = $room;
                }
            }

            Log::info('✅ BÚSQUEDA COMPLETADA', [
                'available' => count($availableRooms),
                'total' => count($rooms)
            ]);

            return $availableRooms;

        } catch (\Exception $e) {
            Log::error('❌ ERROR OBTENIENDO HABITACIONES DISPONIBLES:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return [];
        }
    }

    /**
     * Validar parámetros de disponibilidad
     *
     * @param string $checkIn Fecha de entrada (formato string)
     * @param string $checkOut Fecha de salida (formato string)
     * @return array ['isValid' => bool, 'errors' => array]
     */
    public function validateAvailabilityDates(string $checkIn, string $checkOut): array
    {
        $errors = [];

        // Verificar que las fechas no estén vacías
        if (empty($checkIn)) {
            $errors['checkIn'] = 'La fecha de entrada es requerida.';
        }

        if (empty($checkOut)) {
            $errors['checkOut'] = 'La fecha de salida es requerida.';
        }

        if (!empty($errors)) {
            return ['isValid' => false, 'errors' => $errors];
        }

        try {
            $checkInDate = Carbon::parse($checkIn)->startOfDay();
            $checkOutDate = Carbon::parse($checkOut)->startOfDay();

            // Verificar que check-out sea posterior a check-in
            if ($checkOutDate->lte($checkInDate)) {
                $errors['dates'] = 'La fecha de salida debe ser posterior a la fecha de entrada.';
            }

            // Verificar que check-in no sea en el pasado
            if ($checkInDate < now()->startOfDay()) {
                $errors['checkIn'] = 'La fecha de entrada no puede ser anterior a hoy.';
            }

        } catch (\Exception $e) {
            Log::warning('⚠️ ERROR PARSEANDO FECHAS:', [
                'checkIn' => $checkIn,
                'checkOut' => $checkOut,
                'message' => $e->getMessage()
            ]);
            $errors['dates'] = 'Formato de fecha inválido.';
        }

        return [
            'isValid' => empty($errors),
            'errors' => $errors
        ];
    }

    /**
     * Obtener habitaciones no disponibles para debugging
     *
     * @param Carbon $checkIn Fecha de entrada
     * @param Carbon $checkOut Fecha de salida
     * @return array Habitaciones no disponibles con razón
     */
    public function getUnavailableRooms(
        Carbon $checkIn,
        Carbon $checkOut,
        ?int $excludeReservationId = null
    ): array
    {
        $unavailable = [];

        $allRooms = Room::query()
            ->active()
            ->orderBy('room_number')
            ->get();

        foreach ($allRooms as $room) {
            $roomId = $room->id;

            $conflictingStay = $this->findConflictingStay($roomId, $checkIn, $checkOut, $excludeReservationId);
            if ($conflictingStay) {
                $unavailable[] = [
                    'roomId' => $roomId,
                    'roomNumber' => $room->room_number,
                    'reason' => 'stay_conflict',
                    'details' => [
                        'stayId' => $conflictingStay->id,
                        'reservationId' => $conflictingStay->reservation_id,
                        'status' => $conflictingStay->status,
                        'checkInAt' => $conflictingStay->check_in_at?->format('Y-m-d H:i') ?? 'No definido',
                        'checkOutAt' => $conflictingStay->check_out_at?->format('Y-m-d H:i') ?? 'No definido',
                    ]
                ];
                continue;
            }

            $conflictingReservationRoom = $this->findConflictingReservationRoom($roomId, $checkIn, $checkOut, true, $excludeReservationId);
            if ($conflictingReservationRoom) {
                $unavailable[] = [
                    'roomId' => $roomId,
                    'roomNumber' => $room->room_number,
                    'reason' => 'reservation_conflict',
                    'details' => [
                        'reservationRoomId' => $conflictingReservationRoom->id,
                        'reservationId' => $conflictingReservationRoom->reservation_id,
                        'reservationCode' => $conflictingReservationRoom->reservation?->reservation_code,
                        'checkInDate' => optional($conflictingReservationRoom->check_in_date)->format('Y-m-d'),
                        'checkOutDate' => optional($conflictingReservationRoom->check_out_date)->format('Y-m-d'),
                    ]
                ];
            }
        }

        return $unavailable;
    }

    /**
     * Busca stays en conflicto para una habitación y rango.
     * Incluye fallback a reservation_rooms.check_out_date si check_out_at es null.
     */
    private function findConflictingStay(
        int $roomId,
        Carbon $checkIn,
        Carbon $checkOut,
        ?int $excludeReservationId = null
    ): ?Stay
    {
        $query = Stay::query()
            ->where('room_id', $roomId)
            ->whereIn('status', ['active', 'pending_checkout'])
            ->where('check_in_at', '<', $checkOut)
            ->where(function ($q) use ($checkIn, $roomId) {
                $q->where(function ($q2) use ($checkIn) {
                    $q2->whereNotNull('check_out_at')
                        ->where('check_out_at', '>', $checkIn);
                })->orWhere(function ($q2) use ($checkIn, $roomId) {
                    $q2->whereNull('check_out_at')
                        // Regla de negocio temporal:
                        // Si check_out_at es NULL, tratar la stay como de 1 noche.
                        ->whereRaw('DATE_ADD(check_in_at, INTERVAL 1 DAY) > ?', [$checkIn->toDateTimeString()]);
                });
            })
            ->orderByDesc('check_in_at');

        if (!empty($excludeReservationId)) {
            $query->where('reservation_id', '!=', (int)$excludeReservationId);
        }

        return $query->first();
    }

    /**
     * Busca reservation_rooms en conflicto que aún no tienen stay en esa misma habitación.
     */
    private function findConflictingReservationRoom(
        int $roomId,
        Carbon $checkIn,
        Carbon $checkOut,
        bool $withReservation = false,
        ?int $excludeReservationId = null
    ): ?ReservationRoom {
        $query = ReservationRoom::query()
            ->where('room_id', $roomId)
            ->whereDate('check_in_date', '<', $checkOut->toDateString())
            ->whereDate('check_out_date', '>', $checkIn->toDateString())
            ->whereHas('reservation', fn ($q) => $q->whereNull('deleted_at'))
            ->whereNotExists(function ($sub) use ($roomId) {
                $sub->select(DB::raw(1))
                    ->from('stays')
                    ->whereColumn('stays.reservation_id', 'reservation_rooms.reservation_id')
                    ->whereColumn('stays.room_id', 'reservation_rooms.room_id')
                    ->where('stays.room_id', $roomId);
            });

        if (!empty($excludeReservationId)) {
            $query->where('reservation_id', '!=', (int)$excludeReservationId);
        }

        if ($withReservation) {
            $query->with('reservation');
        }

        return $query->first();
    }
}
