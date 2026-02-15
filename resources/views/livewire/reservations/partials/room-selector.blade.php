<!-- SECCION 2: HABITACION Y FECHAS -->
<div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-visible">
    <div class="p-5 border-b border-gray-50 bg-gray-50/50 flex items-center">
        <i class="fas fa-bed text-emerald-500 mr-2"></i>
        <h2 class="font-bold text-gray-800">Estancia y Habitacion</h2>
    </div>

    <div class="p-6 space-y-6">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div>
                <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Check-In</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                        <i class="fas fa-calendar-alt text-sm"></i>
                    </div>
                    <input
                        type="date"
                        name="check_in_date"
                        wire:model.live="reservation.checkIn"
                        min="{{ $this->minCheckInDate }}"
                        @disabled($areRoomsLocked ?? false)
                        class="block w-full pl-10 pr-4 py-2.5 border border-gray-300 rounded-xl text-sm focus:ring-emerald-500 focus:border-emerald-500 disabled:bg-gray-100 disabled:text-gray-500 disabled:cursor-not-allowed"
                        required
                    >
                </div>
                @error('reservation.checkIn')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
                @error('checkIn')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Check-Out</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                        <i class="fas fa-door-open text-sm"></i>
                    </div>
                    <input
                        type="date"
                        name="check_out_date"
                        wire:model.live="reservation.checkOut"
                        min="{{ $this->minCheckOutDate }}"
                        @disabled($this->isCheckOutDisabled || ($areRoomsLocked ?? false))
                        class="block w-full pl-10 pr-4 py-2.5 border border-gray-300 rounded-xl text-sm focus:ring-emerald-500 focus:border-emerald-500 disabled:bg-gray-100 disabled:text-gray-500 disabled:cursor-not-allowed"
                        required
                    >
                </div>
                @error('reservation.checkOut')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
                @error('checkOut')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <div class="space-y-4">
            <div>
                <div class="flex items-center justify-between mb-2">
                    <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider">Habitaciones Disponibles</label>
                    <button
                        type="button"
                        @unless($areRoomsLocked ?? false) wire:click="toggleMultiRoomMode" @endunless
                        @disabled($areRoomsLocked ?? false)
                        class="text-xs font-bold text-emerald-600 hover:text-emerald-800 flex items-center disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                        <i class="fas {{ $showMultiRoomSelector ? 'fa-check-circle' : 'fa-plus-circle' }} mr-1"></i>
                        <span>{{ $showMultiRoomSelector ? 'Usar una habitacion' : 'Seleccionar multiples habitaciones' }}</span>
                    </button>
                </div>

                <div class="space-y-3">
                    @if($areRoomsLocked ?? false)
                        <div class="bg-slate-50 text-slate-700 border-slate-200 p-3 rounded-xl border text-xs font-medium flex items-center">
                            <i class="fas fa-lock mr-2"></i>
                            <span>La reserva ya tiene estadia registrada. No se pueden cambiar fechas ni habitaciones.</span>
                        </div>
                    @endif

                    @if(!$datesCompleted)
                        <div class="bg-amber-50 text-amber-700 border-amber-100 p-3 rounded-xl border text-xs font-medium flex items-center">
                            <i class="fas fa-exclamation-circle mr-2"></i>
                            <span>Completa las fechas para ver las habitaciones disponibles</span>
                        </div>
                    @endif

                    <div class="space-y-2">
                        <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">
                            Seleccionar habitacion
                        </label>

                        @if($datesCompleted)
                            <div class="border border-gray-300 rounded-xl bg-white max-h-72 overflow-y-auto">
                                @if(is_array($availableRooms) && count($availableRooms) > 0)
                                    @php
                                        $selectedRoomIdSet = array_fill_keys(array_map('intval', is_array($selectedRoomIds ?? null) ? $selectedRoomIds : []), true);
                                        $selectedSingleRoomId = (int) ($roomId ?? 0);
                                    @endphp

                                    <div class="px-4 py-2 text-[10px] font-bold text-gray-500 uppercase tracking-widest bg-gray-50 border-b border-gray-100 sticky top-0 flex items-center justify-between">
                                        <span>
                                            <i class="fas fa-hand-point-up mr-1"></i>
                                            Desliza para ver mas habitaciones
                                        </span>

                                        @if($showMultiRoomSelector && is_array($selectedRoomIds) && count($selectedRoomIds) > 0 && !($areRoomsLocked ?? false))
                                            <button
                                                type="button"
                                                wire:click="clearSelectedRooms"
                                                class="text-[10px] font-black text-red-600 hover:text-red-800 uppercase tracking-widest"
                                            >
                                                Limpiar
                                            </button>
                                        @endif
                                    </div>

                                    @foreach($availableRooms as $room)
                                        @php
                                            $availableRoomId = (int) ($room['id'] ?? 0);
                                            $roomNumber = (string) ($room['room_number'] ?? $room['number'] ?? '');
                                            $beds = (int) ($room['beds'] ?? $room['beds_count'] ?? 0);
                                            $capacity = (int) ($room['capacity'] ?? $room['max_capacity'] ?? 0);
                                            $isSelectedSingle = !$showMultiRoomSelector && $availableRoomId > 0 && $availableRoomId === $selectedSingleRoomId;
                                            $isSelectedMulti = $showMultiRoomSelector && isset($selectedRoomIdSet[$availableRoomId]);
                                            $isSelected = $isSelectedSingle || $isSelectedMulti;
                                        @endphp

                                        @if($availableRoomId > 0)
                                            <button
                                                type="button"
                                                wire:key="available-room-{{ $availableRoomId }}"
                                                @unless($areRoomsLocked ?? false) wire:click="selectRoom({{ $availableRoomId }})" @endunless
                                                @disabled($areRoomsLocked ?? false)
                                                class="w-full text-left px-4 py-3 transition-colors border-b border-gray-100 last:border-b-0 disabled:cursor-not-allowed {{ $isSelected ? 'bg-emerald-50' : 'hover:bg-emerald-50' }} {{ ($areRoomsLocked ?? false) ? 'opacity-70' : '' }}"
                                            >
                                                <div class="flex items-center justify-between">
                                                    <div class="flex-1">
                                                        <div class="font-bold text-gray-900 text-sm">
                                                            Habitacion {{ $roomNumber }}
                                                        </div>
                                                        <div class="text-xs text-gray-500 mt-0.5">
                                                            <span class="mr-2"><i class="fas fa-bed mr-1"></i>{{ $beds }} {{ $beds == 1 ? 'Cama' : 'Camas' }}</span>
                                                            <span><i class="fas fa-users mr-1"></i>Capacidad {{ $capacity }}</span>
                                                        </div>
                                                    </div>

                                                    @if($showMultiRoomSelector)
                                                        <i class="fas {{ $isSelected ? 'fa-check-square text-emerald-600' : 'fa-square text-gray-300' }}"></i>
                                                    @else
                                                        <i class="fas {{ $isSelected ? 'fa-check-circle text-emerald-600' : 'fa-circle text-gray-300' }}"></i>
                                                    @endif
                                                </div>
                                            </button>
                                        @endif
                                    @endforeach
                                @else
                                    <div class="px-4 py-6 text-center text-sm text-gray-500">
                                        <i class="fas fa-door-closed text-2xl mb-2 opacity-50"></i>
                                        <p>No hay habitaciones disponibles para estas fechas</p>
                                    </div>
                                @endif
                            </div>
                        @else
                            <div class="border border-gray-200 rounded-xl bg-gray-50 p-4 text-xs text-gray-500">
                                Selecciona las fechas para ver las habitaciones disponibles.
                            </div>
                        @endif
                    </div>
                </div>
                @error('reservation.selectedRoomIds')
                    <p class="mt-2 text-xs text-red-600">{{ $message }}</p>
                @enderror
                @error('selectedRoomIds')
                    <p class="mt-2 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>
        </div>

        @if(!$showMultiRoomSelector && $selectedRoom)
            <div class="flex flex-wrap items-center gap-2">
                <span class="inline-flex items-center px-3 py-1.5 rounded-xl bg-emerald-50 text-emerald-700 text-xs font-bold border border-emerald-100">
                    <i class="fas fa-bed mr-2"></i>
                    Habitacion {{ $selectedRoom['number'] ?? $selectedRoom['room_number'] ?? '' }}
                </span>
            </div>
        @endif
    </div>
</div>
