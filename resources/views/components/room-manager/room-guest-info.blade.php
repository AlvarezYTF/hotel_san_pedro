@props(['room', 'stay'])

@php
    // SINGLE SOURCE OF TRUTH: Este componente recibe $stay explícitamente
    // Si no hay stay, no hay información de huésped para mostrar
    if (!$stay) {
        return;
    }

    // Obtener reserva desde la stay (Single Source of Truth)
    $reservation = $stay->reservation;
    $customer = $reservation?->customer;
    
    // Obtener ReservationRoom asociado para acceder a huéspedes adicionales
    $reservationRoom = null;
    if ($reservation) {
        $reservationRoom = $reservation->reservationRooms
            ->firstWhere('room_id', $room->id);
    }
    
    // Obtener huéspedes adicionales desde reservationRoom
    $additionalGuests = collect();
    if ($reservationRoom) {
        try {
            $additionalGuests = $reservationRoom->getGuests();
        } catch (\Exception $e) {
            // Silently handle error - no mostrar huéspedes adicionales si hay error
            \Log::warning('Error loading additional guests', [
                'reservation_room_id' => $reservationRoom->id,
                'error' => $e->getMessage()
            ]);
        }
    }
    
    // Calcular total de huéspedes (principal + adicionales)
    $totalGuests = 1; // Cliente principal
    if ($additionalGuests->isNotEmpty()) {
        $totalGuests += $additionalGuests->count();
    }
@endphp

@if($reservation && $customer)
    {{-- CASO NORMAL: Reserva con cliente asignado --}}
    <div class="flex flex-col cursor-pointer hover:opacity-80 transition-opacity" 
         x-data
         @click="
             @this.call('loadRoomGuests', {{ $room->id }}).then((data) => {
                 if (data && data.guests) {
                     window.dispatchEvent(new CustomEvent('open-guests-modal', { detail: data }));
                 }
             }).catch(() => {
                 // Silently handle error if reservation no longer exists
             });
         "
         title="Click para ver todos los huéspedes">
        <span class="text-sm font-semibold text-gray-900">{{ $customer->name }}</span>
        @if($reservationRoom && $reservationRoom->check_out_date)
            <span class="text-xs text-blue-600 font-medium">
                Salida: {{ \Carbon\Carbon::parse($reservationRoom->check_out_date)->format('d/m/Y') }}
            </span>
        @endif
        @if($totalGuests > 1)
            <span class="text-[10px] text-gray-500 mt-1">
                <i class="fas fa-users mr-1"></i>
                {{ $totalGuests }} huéspedes
            </span>
        @endif
    </div>
@elseif($reservation && !$customer)
    {{-- CASO EDGE: Reserva activa pero sin cliente asignado (walk-in sin asignar) --}}
    <div class="flex flex-col space-y-1">
        <div class="flex items-center gap-1.5">
            <i class="fas fa-exclamation-triangle text-yellow-600 text-xs"></i>
            <span class="text-sm text-yellow-700 font-semibold">Ocupada sin huésped</span>
        </div>
        <div class="text-xs text-gray-500">
            La estadía existe pero no hay cliente asignado.
        </div>
        <button type="button"
                wire:click="openQuickRent({{ $room->id }})"
                class="text-xs text-blue-600 hover:text-blue-800 underline font-medium mt-1">
            Asignar huésped
        </button>
    </div>
@else
    {{-- CASO EDGE: Stay activo pero sin reserva asociada (inconsistencia de datos) --}}
    <div class="flex flex-col space-y-1">
        <div class="flex items-center gap-1.5">
            <i class="fas fa-exclamation-circle text-orange-600 text-xs"></i>
            <span class="text-sm text-orange-700 font-semibold">Sin cuenta asociada</span>
        </div>
        <div class="text-xs text-gray-500">
            No hay reserva ligada a esta estadía.
        </div>
        <button type="button"
                wire:click="openRoomDetail({{ $room->id }})"
                class="text-xs text-blue-600 hover:text-blue-800 underline font-medium mt-1">
            Ver detalles
        </button>
    </div>
@endif

