<!-- SECCIÓN ASIGNACIÓN DE HUÉSPEDES -->
<div>
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="p-5 border-b border-gray-50 bg-gray-50/50 flex items-center justify-between">
            <div class="flex items-center">
                <i class="fas fa-users text-blue-500 mr-2"></i>
                <h2 class="font-bold text-gray-800">Asignación de Huéspedes</h2>
            </div>
            <button type="button" wire:click="openGuestModal" class="text-xs font-bold text-blue-600 hover:text-blue-800 flex items-center">
                <i class="fas fa-user-plus mr-1"></i> AGREGAR HUÉSPED
            </button>
        </div>

        <div class="p-6">
            @if(!$showMultiRoomSelector && $roomId)
                <!-- Modo Simple: Una Habitación -->
                <div class="space-y-4">
                    @if(count($assignedGuests) > 0)
                        <div class="space-y-2">
                            @foreach($assignedGuests as $index => $guest)
                                <div class="flex items-center justify-between p-3 bg-blue-50 rounded-xl border border-blue-100">
                                    <div class="flex-1">
                                        <p class="font-medium text-gray-900 text-sm">{{ $guest['name'] ?? '' }}</p>
                                        <p class="text-xs text-gray-600">{{ $guest['identification'] ?? 'S/N' }}</p>
                                    </div>
                                    <button type="button" wire:click="removeGuest(null, {{ $index }})"
                                            class="text-red-500 hover:text-red-700 text-sm">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-6 text-gray-500">
                            <i class="fas fa-user-friends text-2xl mb-2 opacity-50"></i>
                            <p class="text-sm">No hay huéspedes asignados</p>
                        </div>
                    @endif
                </div>
            @elseif($showMultiRoomSelector && is_array($selectedRoomIds) && count($selectedRoomIds) > 0)
                <!-- Modo Multi: Múltiples Habitaciones -->
                <div class="space-y-4">
                    @foreach($selectedRoomIds as $selectedRoomId)
                        @php $guestCount = $this->getRoomGuestsCount($selectedRoomId); @endphp
                        <div class="border border-gray-200 rounded-xl p-4">
                            <div class="flex items-center justify-between mb-3">
                                <h3 class="font-bold text-gray-900">Habitación {{ $selectedRoomId }}</h3>
                                <span class="text-xs font-bold bg-blue-100 text-blue-800 px-2 py-1 rounded-full">
                                    {{ $guestCount }} huésped{{ $guestCount != 1 ? 'es' : '' }}
                                </span>
                                <button type="button" wire:click="openGuestModal({{ $selectedRoomId }})"
                                        class="text-xs font-bold text-blue-600 hover:text-blue-800">
                                    <i class="fas fa-user-plus mr-1"></i> Agregar
                                </button>
                            </div>

                            @php $currentRoomGuests = $roomGuests[$selectedRoomId] ?? []; @endphp
                            @if(is_array($currentRoomGuests) && count($currentRoomGuests) > 0)
                                <div class="space-y-2">
                                    @foreach($currentRoomGuests as $index => $guest)
                                        <div class="flex items-center justify-between p-2 bg-blue-50 rounded-lg border border-blue-100 text-sm">
                                            <div>
                                                <p class="font-medium text-gray-900">{{ $guest['name'] ?? '' }}</p>
                                                <p class="text-xs text-gray-600">{{ $guest['identification'] ?? 'S/N' }}</p>
                                            </div>
                                            <button type="button" wire:click="removeGuest({{ $selectedRoomId }}, {{ $index }})"
                                                    class="text-red-500 hover:text-red-700 text-xs">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <p class="text-xs text-gray-500 italic">Sin huéspedes asignados</p>
                            @endif
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-6 text-gray-500">
                    <i class="fas fa-exclamation-circle text-2xl mb-2 opacity-50"></i>
                    <p class="text-sm">Selecciona una habitación para asignar huéspedes</p>
                </div>
            @endif
        </div>
    </div>

    <!-- MODAL DE ASIGNACIÓN DE HUÉSPEDES -->
    @if($guestModalOpen)
        <div class="fixed inset-0 bg-gray-900/60 z-50 flex items-center justify-center">
            <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full mx-4">
                <div class="p-5 border-b border-gray-200 flex items-center justify-between">
                    <h3 class="font-bold text-gray-900">Agregar Huésped</h3>
                    <button type="button" wire:click="closeGuestModal" class="text-gray-400 hover:text-gray-600">
                        <i class="fas fa-times"></i>
                    </button>
                </div>

                <div class="p-6">
                    <div class="relative mb-4">
                        <input type="text" wire:model.live.debounce.300ms="guestSearchTerm"
                               class="block w-full px-4 py-2.5 border border-gray-300 rounded-xl text-sm"
                               placeholder="Buscar por nombre, identificación...">
                    </div>

                    <div class="space-y-2 max-h-96 overflow-y-auto">
                        @foreach($filteredGuests as $guest)
                            <button type="button"
                                    wire:click="selectGuestForAssignment({{ $guest['id'] }})"
                                    class="w-full text-left p-3 hover:bg-blue-50 rounded-xl border border-gray-200 transition-colors">
                                <p class="font-medium text-gray-900 text-sm">{{ $guest['name'] ?? '' }}</p>
                                <p class="text-xs text-gray-600">{{ $guest['taxProfile']['identification'] ?? 'S/N' }}</p>
                            </button>
                        @endforeach
                    </div>
                </div>

                <div class="p-4 border-t border-gray-200 flex justify-end">
                    <button type="button" wire:click="closeGuestModal"
                            class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200">
                        Cerrar
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
