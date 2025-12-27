@extends('layouts.app')

@section('title', 'Editar Reserva')
@section('header', 'Editar Reserva')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8" x-data="reservationForm()">
    <!-- Header Contextual -->
    <div class="mb-6 bg-white rounded-2xl border border-gray-100 shadow-sm">
        <div class="p-5 border-b border-gray-50 bg-gray-50/50 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div class="flex items-center space-x-3">
                <div class="p-3 rounded-2xl bg-indigo-100 text-indigo-600 shadow-sm">
                    <i class="fas fa-calendar-edit text-2xl"></i>
                </div>
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 leading-tight">Editar Reserva #{{ $reservation->id }}</h1>
                    <p class="text-sm text-gray-500">Actualiza la información de la reserva</p>
                </div>
            </div>

            <div class="flex items-center space-x-3">
                <a href="{{ route('reservations.index') }}" class="px-4 py-2 text-sm font-semibold text-gray-600 bg-white border border-gray-200 rounded-xl hover:bg-gray-50 transition-all">
                    Cancelar
                </a>
                <button type="submit" form="reservation-form"
                        :disabled="!isValid || loading"
                        class="px-6 py-2 text-sm font-bold text-white bg-indigo-600 rounded-xl hover:bg-indigo-700 disabled:opacity-50 disabled:cursor-not-allowed shadow-sm transition-all flex items-center">
                    <i class="fas fa-save mr-2" x-show="!loading"></i>
                    <i class="fas fa-spinner fa-spin mr-2" x-show="loading"></i>
                    Actualizar Reserva
                </button>
            </div>
        </div>
    </div>

    <form id="reservation-form" method="POST" action="{{ route('reservations.update', $reservation) }}" class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        @csrf
        @method('PUT')

        <!-- Columna Principal (2/3) -->
        <div class="lg:col-span-2 space-y-6">

            <!-- SECCIÓN 1: CLIENTE -->
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm">
                <div class="p-5 border-b border-gray-50 flex items-center justify-between bg-gray-50/50">
                    <div class="flex items-center space-x-2">
                        <i class="fas fa-user-circle text-blue-500"></i>
                        <h2 class="font-bold text-gray-800">Información del Cliente</h2>
                    </div>
                    <button type="button" @click="openNewCustomerModal()" class="text-xs font-bold text-blue-600 hover:text-blue-800 flex items-center">
                        <i class="fas fa-plus-circle mr-1"></i> NUEVO CLIENTE
                    </button>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-1 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Seleccionar Huésped</label>
                            <select name="customer_id" id="customer_id" x-model="customerId" required class="w-full">
                                <option value="">Buscar por nombre o identificación...</option>
                            </select>
                        </div>

                        <!-- Info Preview del Cliente Seleccionado -->
                        <template x-if="selectedCustomerInfo">
                            <div class="mt-2 p-3 bg-blue-50 rounded-xl flex items-center justify-between border border-blue-100 transition-all animate-fadeIn">
                                <div class="flex items-center space-x-4 text-sm text-blue-800">
                                    <div class="flex items-center">
                                        <i class="fas fa-id-card mr-2 opacity-60"></i>
                                        <span x-text="selectedCustomerInfo.id"></span>
                                    </div>
                                    <div class="flex items-center border-l border-blue-200 pl-4">
                                        <i class="fas fa-phone mr-2 opacity-60"></i>
                                        <span x-text="selectedCustomerInfo.phone"></span>
                                    </div>
                                </div>
                                <span class="text-[10px] font-bold bg-blue-200 text-blue-800 px-2 py-0.5 rounded-full uppercase">Verificado</span>
                            </div>
                        </template>
                    </div>
                </div>
            </div>

            <!-- SECCIÓN 2: HABITACIÓN Y FECHAS -->
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
                <div class="p-5 border-b border-gray-50 bg-gray-50/50 flex items-center">
                    <i class="fas fa-bed text-emerald-500 mr-2"></i>
                    <h2 class="font-bold text-gray-800">Estancia y Habitación</h2>
                </div>
                <div class="p-6 space-y-6">
                    <div class="space-y-4">
                        <!-- Selector de Habitaciones (Múltiples) -->
                        <div>
                            <div class="flex items-center justify-between mb-2">
                                <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider">Habitaciones</label>
                                <button type="button" @click="showMultiRoomSelector = !showMultiRoomSelector"
                                        class="text-xs font-bold text-emerald-600 hover:text-emerald-800 flex items-center">
                                    <i class="fas" :class="showMultiRoomSelector ? 'fa-check-circle' : 'fa-plus-circle'" class="mr-1"></i>
                                    <span x-text="showMultiRoomSelector ? 'Usar una habitación' : 'Seleccionar múltiples habitaciones'"></span>
                                </button>
                            </div>

                            <!-- Modo una habitación (backward compatibility) -->
                            <div x-show="!showMultiRoomSelector" x-cloak>
                                <select name="room_id" id="room_id" x-model="roomId" :required="!showMultiRoomSelector" class="w-full">
                                    <option value="">Seleccionar número...</option>
                                    @foreach($rooms as $room)
                                        <option value="{{ $room->id }}">
                                            Habitación {{ $room->room_number }} ({{ $room->beds_count }} {{ $room->beds_count == 1 ? 'Cama' : 'Camas' }})
                                        </option>
                                    @endforeach
                                </select>

                                <!-- Status de Disponibilidad -->
                                <div x-show="roomId" class="mt-3">
                                    <template x-if="isChecking">
                                        <span class="text-xs text-gray-500 flex items-center">
                                            <i class="fas fa-spinner fa-spin mr-2"></i> Verificando disponibilidad...
                                        </span>
                                    </template>
                                    <template x-if="!isChecking && availability !== null">
                                        <div :class="availability ? 'bg-emerald-50 text-emerald-700 border-emerald-100' : 'bg-red-50 text-red-700 border-red-100'"
                                             class="p-2.5 rounded-xl border text-xs font-bold flex items-center">
                                            <i :class="availability ? 'fas fa-check-circle' : 'fas fa-times-circle'" class="mr-2"></i>
                                            <span x-text="availability ? 'HABITACIÓN DISPONIBLE' : 'NO DISPONIBLE PARA ESTAS FECHAS'"></span>
                                        </div>
                                    </template>
                                </div>
                            </div>

                            <!-- Modo múltiples habitaciones -->
                            <div x-show="showMultiRoomSelector" x-cloak class="space-y-3">
                                <select name="room_ids[]" id="room_ids" multiple class="w-full">
                                    @foreach($rooms as $room)
                                        <option value="{{ $room->id }}">
                                            Habitación {{ $room->room_number }} (Capacidad: {{ $room->max_capacity }} pers.)
                                        </option>
                                    @endforeach
                                </select>
                                <p class="text-xs text-gray-500">
                                    <i class="fas fa-info-circle mr-1"></i>
                                    Selecciona una o más habitaciones de la lista
                                </p>
                            </div>
                        </div>

                        <!-- Detalles Habitación (modo una habitación) -->
                        <template x-if="!showMultiRoomSelector && selectedRoom">
                            <div class="bg-gray-50 rounded-2xl p-4 border border-gray-100 flex flex-col justify-center space-y-3">
                                <div class="flex justify-between items-center text-sm">
                                    <span class="text-gray-500 font-medium italic">Precio por noche:</span>
                                    <span class="font-bold text-gray-900" x-text="formatCurrency(getPriceForGuests())"></span>
                                </div>
                                <template x-if="guestsCount > 0">
                                    <div class="text-[10px] text-gray-500 italic text-center">
                                        <span x-text="'Para ' + guestsCount + (guestsCount == 1 ? ' persona' : ' personas')"></span>
                                    </div>
                                </template>
                                <div class="flex justify-between items-center">
                                    <span class="px-2 py-1 bg-white border border-gray-200 rounded-lg text-[10px] font-bold text-gray-600 uppercase" x-text="selectedRoom.beds + (selectedRoom.beds == 1 ? ' Cama' : ' Camas')"></span>
                                    <div class="flex items-center text-xs text-gray-600">
                                        <i class="fas fa-users mr-1.5 opacity-60"></i>
                                        <span x-text="'Capacidad: ' + selectedRoom.capacity + ' pers.'"></span>
                                    </div>
                                </div>
                            </div>
                        </template>

                        <!-- Lista de Habitaciones Seleccionadas (modo múltiples) -->
                        <template x-if="showMultiRoomSelector && selectedRoomIds.length > 0">
                            <div class="space-y-3">
                                <template x-for="roomId in selectedRoomIds" :key="roomId">
                                    <div class="bg-gray-50 rounded-xl p-4 border border-gray-100">
                                        <div class="flex items-center justify-between mb-3">
                                            <div class="flex items-center space-x-2">
                                                <i class="fas fa-bed text-emerald-500"></i>
                                                <span class="font-bold text-gray-900" x-text="getRoomById(roomId)?.number || 'Habitación ' + roomId"></span>
                                                <span class="text-xs text-gray-500">
                                                    (Capacidad: <span x-text="getRoomById(roomId)?.capacity || 0"></span> pers.)
                                                </span>
                                            </div>
                                            <button type="button" @click="removeRoom(roomId)"
                                                    class="text-red-500 hover:text-red-700 text-xs">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        </div>
                                        <div class="text-xs text-gray-600 mb-2">
                                            Precio por noche: <span class="font-bold" x-text="formatCurrency(getRoomPriceForGuests(roomId, getRoomGuestsCount(roomId)))"></span>
                                            <template x-if="getRoomGuestsCount(roomId) > 0">
                                                <span class="text-[10px] text-gray-500 ml-2 italic">
                                                    (Precio total para <span x-text="getRoomGuestsCount(roomId)"></span> <span x-text="getRoomGuestsCount(roomId) === 1 ? 'persona' : 'personas'"></span>)
                                                </span>
                                            </template>
                                            <template x-if="getRoomGuestsCount(roomId) === 0">
                                                <span class="text-[10px] text-gray-500 ml-2 italic">
                                                    (Precio base - 1 persona)
                                                </span>
                                            </template>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </template>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 pt-4 border-t border-gray-50">
                        <!-- Número de Personas (solo para modo una habitación) -->
                        <div x-show="!showMultiRoomSelector">
                            <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Número de Personas</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                                    <i class="fas fa-users text-sm"></i>
                                </div>
                                <input type="number" name="guests_count" x-model="guestsCount" min="1"
                                       :max="selectedRoom ? selectedRoom.capacity : 10" required
                                       class="block w-full pl-10 pr-4 py-2.5 border border-gray-300 rounded-xl text-sm focus:ring-emerald-500 focus:border-emerald-500">
                            </div>
                            <template x-if="selectedRoom && guestsCount > selectedRoom.capacity">
                                <span class="mt-2 text-[10px] font-bold text-red-500 uppercase tracking-tighter">
                                    <i class="fas fa-exclamation-triangle mr-1"></i> Excede la capacidad máxima de la habitación
                                </span>
                            </template>
                        </div>
                        <!-- Para modo múltiples habitaciones, calculamos el total automáticamente -->
                        <template x-if="showMultiRoomSelector">
                            <div>
                                <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Total de Personas</label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                                        <i class="fas fa-users text-sm"></i>
                                    </div>
                                    <input type="number" name="guests_count" :value="calculateTotalGuestsCount()" readonly
                                           class="block w-full pl-10 pr-4 py-2.5 border border-gray-300 rounded-xl text-sm bg-gray-100 text-gray-600">
                                </div>
                                <p class="mt-1 text-xs text-gray-500">Calculado automáticamente según las asignaciones por habitación</p>
                            </div>
                        </template>

                        <!-- Fecha Entrada -->
                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Check-In</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                                    <i class="fas fa-calendar-alt text-sm"></i>
                                </div>
                                <input type="date" name="check_in_date" x-model="checkIn" required
                                       class="block w-full pl-10 pr-4 py-2.5 border border-gray-300 rounded-xl text-sm focus:ring-emerald-500 focus:border-emerald-500">
                            </div>
                        </div>

                        <!-- Fecha Salida -->
                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Check-Out</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                                    <i class="fas fa-door-open text-sm"></i>
                                </div>
                                <input type="date" name="check_out_date" x-model="checkOut" required
                                       class="block w-full pl-10 pr-4 py-2.5 border border-gray-300 rounded-xl text-sm focus:ring-emerald-500 focus:border-emerald-500">
                            </div>
                            <template x-if="nights > 0">
                                <div class="mt-2 text-[10px] font-black tracking-widest text-emerald-600 uppercase flex items-center">
                                    <i class="fas fa-moon mr-1.5"></i>
                                    <span x-text="nights + (nights === 1 ? ' NOCHE' : ' NOCHES')"></span>
                                </div>
                            </template>
                            <template x-if="nights < 1 && checkIn && checkOut">
                                <span class="mt-2 text-[10px] font-bold text-red-500 uppercase tracking-tighter">
                                    <i class="fas fa-exclamation-triangle mr-1"></i> La fecha de salida debe ser posterior a la de entrada
                                </span>
                            </template>
                        </div>
                    </div>
                </div>
            </div>

            <!-- SECCIÓN 2.5: ASIGNACIÓN DE HUÉSPEDES (Modo una habitación) -->
            <div x-show="!showMultiRoomSelector && selectedRoom && selectedRoom.capacity > 1 && guestsCount > 1"
                 x-cloak
                 class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden"
                 style="display: none;">
                <div class="p-5 border-b border-gray-50 bg-gray-50/50 flex items-center justify-between">
                    <div class="flex items-center">
                        <i class="fas fa-users text-purple-500 mr-2"></i>
                        <h2 class="font-bold text-gray-800">Asignación de Huéspedes a la Habitación</h2>
                    </div>
                    <div class="flex items-center space-x-3">
                        <template x-if="selectedRoom && canAssignMoreGuests">
                            <span class="text-xs text-gray-600 font-medium">
                                <span x-text="availableSlots"></span> espacio(s) disponible(s)
                            </span>
                        </template>
                        <button type="button"
                                @click="openGuestModal(null)"
                                :disabled="!canAssignMoreGuests"
                                :class="canAssignMoreGuests ? 'bg-purple-600 hover:bg-purple-700' : 'bg-gray-400 cursor-not-allowed'"
                                class="px-4 py-2 text-xs font-bold text-white rounded-xl transition-all flex items-center">
                            <i class="fas fa-plus mr-2"></i>
                            Asignar Persona
                        </button>
                    </div>
                </div>
                <div class="p-6">
                    <div class="mb-4 p-3 bg-blue-50 border border-blue-100 rounded-xl">
                        <p class="text-xs text-blue-800 font-medium">
                            <i class="fas fa-info-circle mr-2"></i>
                            <span x-text="'Cliente principal: ' + (selectedCustomerInfo ? selectedCustomerInfo.id : 'No seleccionado')"></span>
                            <span class="text-gray-400 mx-2">•</span>
                            <span x-text="'Capacidad de la habitación: ' + (selectedRoom ? selectedRoom.capacity : 0) + ' personas'"></span>
                        </p>
                    </div>
                    <template x-if="assignedGuests.length === 0">
                        <div class="text-center py-8 text-gray-400">
                            <i class="fas fa-user-plus text-4xl mb-3 opacity-50"></i>
                            <p class="text-sm">No hay personas adicionales asignadas aún</p>
                            <p class="text-xs mt-1" x-show="canAssignMoreGuests">Haz clic en "Asignar Persona" para agregar huéspedes adicionales</p>
                            <p class="text-xs mt-1 text-amber-600" x-show="!canAssignMoreGuests">La habitación ha alcanzado su capacidad máxima</p>
                        </div>
                    </template>
                    <template x-if="assignedGuests.length > 0">
                        <div class="space-y-3">
                            <template x-for="(guest, index) in assignedGuests" :key="guest.id">
                                <div class="flex items-center justify-between p-3 bg-gray-50 rounded-xl border border-gray-100">
                                    <div class="flex items-center space-x-3">
                                        <div class="w-10 h-10 rounded-full bg-purple-100 text-purple-600 flex items-center justify-center font-bold">
                                            <i class="fas fa-user"></i>
                                        </div>
                                        <div>
                                            <p class="font-bold text-gray-900 text-sm" x-text="guest.name"></p>
                                            <div class="flex items-center space-x-3 text-xs text-gray-500 mt-1">
                                                <span x-text="'ID: ' + (guest.identification || 'S/N')"></span>
                                                <span class="text-gray-300">•</span>
                                                <span x-text="'Tel: ' + (guest.phone || 'S/N')"></span>
                                            </div>
                                        </div>
                                    </div>
                                    <button type="button" @click="removeGuest(null, index)"
                                            class="text-red-500 hover:text-red-700 transition-colors">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                            </template>
                        </div>
                    </template>
                </div>
            </div>

            <!-- SECCIÓN 2.6: ASIGNACIÓN DE HUÉSPEDES POR HABITACIÓN (Modo múltiples habitaciones) -->
            <div x-show="showMultiRoomSelector && selectedRoomIds.length > 0"
                 x-cloak
                 class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden"
                 style="display: none;">
                <div class="p-5 border-b border-gray-50 bg-gray-50/50 flex items-center">
                    <i class="fas fa-users text-purple-500 mr-2"></i>
                    <h2 class="font-bold text-gray-800">Asignación de Huéspedes por Habitación</h2>
                </div>
                <div class="p-6 space-y-6">
                    <div class="mb-4 p-3 bg-blue-50 border border-blue-100 rounded-xl">
                        <p class="text-xs text-blue-800 font-medium">
                            <i class="fas fa-info-circle mr-2"></i>
                            Asigna huéspedes a cada habitación según su capacidad. El cliente principal puede ser asignado opcionalmente a una habitación.
                        </p>
                    </div>
                    <template x-for="roomId in selectedRoomIds" :key="roomId">
                        <div class="border border-gray-200 rounded-xl p-4 bg-gray-50">
                            <div class="flex items-center justify-between mb-4">
                                <div class="flex items-center space-x-2">
                                    <i class="fas fa-bed text-emerald-500"></i>
                                    <h3 class="font-bold text-gray-900" x-text="'Habitación ' + (getRoomById(roomId)?.number || roomId)"></h3>
                                    <span class="text-xs text-gray-500">
                                        (Capacidad: <span x-text="getRoomById(roomId)?.capacity || 0"></span> pers.)
                                    </span>
                                </div>
                                <button type="button"
                                        @click="openGuestModal(roomId)"
                                        :disabled="!canAssignMoreGuestsToRoom(roomId)"
                                        :class="canAssignMoreGuestsToRoom(roomId) ? 'bg-purple-600 hover:bg-purple-700' : 'bg-gray-400 cursor-not-allowed'"
                                        class="px-3 py-1.5 text-xs font-bold text-white rounded-lg transition-all flex items-center">
                                    <i class="fas fa-plus mr-1"></i>
                                    Asignar
                                </button>
                            </div>
                            <template x-if="getRoomGuestsCount(roomId) === 0">
                                <div class="text-center py-4 text-gray-400">
                                    <i class="fas fa-user-plus text-2xl mb-2 opacity-50"></i>
                                    <p class="text-xs">No hay huéspedes asignados a esta habitación</p>
                                </div>
                            </template>
                            <template x-if="getRoomGuestsCount(roomId) > 0">
                                <div class="space-y-2">
                                    <template x-for="(guest, index) in getRoomGuests(roomId)" :key="guest.id">
                                        <div class="flex items-center justify-between p-2 bg-white rounded-lg border border-gray-200">
                                            <div class="flex items-center space-x-2">
                                                <div class="w-8 h-8 rounded-full bg-purple-100 text-purple-600 flex items-center justify-center text-xs">
                                                    <i class="fas fa-user"></i>
                                                </div>
                                                <div>
                                                    <p class="font-bold text-gray-900 text-xs" x-text="guest.name"></p>
                                                    <div class="flex items-center space-x-2 text-[10px] text-gray-500">
                                                        <span x-text="guest.identification || 'S/N'"></span>
                                                        <span class="text-gray-300">•</span>
                                                        <span x-text="guest.phone || 'S/N'"></span>
                                                    </div>
                                                </div>
                                            </div>
                                            <button type="button" @click="removeGuest(roomId, index)"
                                                    class="text-red-500 hover:text-red-700 transition-colors text-xs">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        </div>
                                    </template>
                                    <div class="text-xs text-gray-600 mt-2">
                                        <span x-text="getRoomGuestsCount(roomId)"></span> / <span x-text="getRoomById(roomId)?.capacity || 0"></span> personas asignadas
                                    </div>
                                </div>
                            </template>
                        </div>
                    </template>
                </div>
            </div>

            <!-- SECCIÓN 3: NOTAS -->
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
                <div class="p-5 border-b border-gray-50 bg-gray-50/50 flex items-center">
                    <i class="fas fa-sticky-note text-amber-500 mr-2"></i>
                    <h2 class="font-bold text-gray-800">Observaciones y Requerimientos</h2>
                </div>
                <div class="p-6">
                    <textarea name="notes" rows="3" x-model="notes" class="w-full border-gray-300 rounded-xl text-sm focus:ring-emerald-500 focus:border-emerald-500"
                              placeholder="Ej: Solicitud especial, alergias, llegada tarde, decoración para aniversario..."></textarea>
                </div>
            </div>
        </div>

        <!-- Columna Lateral: Resumen Económico (1/3) -->
        <div class="space-y-6">
            <div class="bg-gray-800 rounded-2xl shadow-xl overflow-hidden sticky top-24 border border-gray-700">
                <div class="p-5 border-b border-gray-700 bg-gray-900/50">
                    <div class="flex items-center justify-between">
                        <h2 class="text-lg font-bold text-white tracking-tight">Resumen de Cobro</h2>
                        <i class="fas fa-wallet text-gray-400"></i>
                    </div>
                </div>

                <div class="p-6 space-y-6">
                    <!-- Valor Total -->
                    <div class="space-y-2">
                        <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Total Estancia</label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-4 flex items-center text-gray-400 font-bold">$</span>
                            <input type="number" name="total_amount" x-model="total" step="1" required
                                   class="block w-full pl-8 pr-4 py-4 bg-gray-700 border-none rounded-xl text-xl font-black text-white focus:ring-2 focus:ring-emerald-500 transition-all">
                        </div>
                        <template x-if="autoCalculatedTotal > 0 && total != autoCalculatedTotal">
                            <button type="button" @click="total = autoCalculatedTotal" class="text-[10px] font-bold text-emerald-400 hover:text-emerald-300 underline uppercase tracking-tighter">
                                Restaurar total sugerido: <span x-text="formatCurrency(autoCalculatedTotal)"></span>
                            </button>
                        </template>
                    </div>

                    <!-- Abono / Depósito -->
                    <div class="space-y-2">
                        <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Abono Inicial</label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-4 flex items-center text-gray-400 font-bold">$</span>
                            <input type="number" name="deposit" x-model="deposit" step="1" required
                                   class="block w-full pl-8 pr-4 py-3 bg-gray-700 border-none rounded-xl text-lg font-bold text-white focus:ring-2 focus:ring-blue-500 transition-all">
                        </div>
                    </div>

                    <!-- Saldo Pendiente -->
                    <div class="pt-6 border-t border-gray-700 space-y-4">
                        <div class="flex justify-between items-end">
                            <div class="space-y-1">
                                <span class="text-[10px] font-black text-gray-500 uppercase tracking-widest">Saldo Pendiente</span>
                                <p class="text-3xl font-black text-white" :class="balance < 0 ? 'text-red-400' : 'text-white'" x-text="formatCurrency(balance)"></p>
                            </div>
                            <div class="mb-1">
                                <template x-if="balance <= 0">
                                    <span class="px-3 py-1 bg-emerald-500/20 text-emerald-400 rounded-full text-[10px] font-black uppercase tracking-widest border border-emerald-500/30">
                                        Liquidado
                                    </span>
                                </template>
                                <template x-if="balance > 0">
                                    <span class="px-3 py-1 bg-amber-500/20 text-amber-400 rounded-full text-[10px] font-black uppercase tracking-widest border border-amber-500/30">
                                        Pendiente
                                    </span>
                                </template>
                            </div>
                        </div>

                        <!-- Alertas de Pago -->
                        <template x-if="balance < 0">
                            <div class="p-3 bg-red-500/20 border border-red-500/30 rounded-xl text-[10px] font-bold text-red-400 text-center animate-bounce uppercase tracking-tighter">
                                <i class="fas fa-exclamation-triangle mr-1"></i> El abono supera el total de la reserva
                            </div>
                        </template>
                    </div>
                </div>

                <!-- Footer del Resumen -->
                <div class="px-6 py-4 bg-black/20 text-center">
                    <input type="hidden" name="reservation_date" value="{{ $reservation->reservation_date->format('Y-m-d') }}">

                    <!-- Single room mode: guest_ids (backward compatibility) -->
                    <template x-if="!showMultiRoomSelector">
                        <template x-for="(guest, index) in assignedGuests" :key="guest.id">
                            <template x-if="guest && guest.id">
                                <input type="hidden" :name="`guest_ids[${index}]`" :value="guest.id">
                            </template>
                        </template>
                    </template>

                    <!-- Multiple rooms mode: room_ids and room_guests -->
                    <template x-if="showMultiRoomSelector">
                        <template x-for="roomId in selectedRoomIds" :key="roomId">
                            <input type="hidden" :name="`room_ids[]`" :value="roomId">
                            <template x-for="(guest, index) in getRoomGuests(roomId)" :key="guest.id">
                                <template x-if="guest && guest.id">
                                    <input type="hidden" :name="`room_guests[${roomId}][${index}]`" :value="guest.id">
                                </template>
                            </template>
                        </template>
                    </template>

                    <p class="text-[10px] text-gray-500 font-medium">Fecha de Registro: <span class="font-bold">{{ $reservation->reservation_date->format('d/m/Y') }}</span></p>
                </div>
            </div>

            <!-- Widget de Ayuda -->
            <div class="bg-blue-50 border border-blue-100 rounded-2xl p-5 shadow-sm">
                <div class="flex items-start space-x-3">
                    <div class="bg-blue-600 rounded-full p-2 text-white text-[10px]">
                        <i class="fas fa-info"></i>
                    </div>
                    <div>
                        <h4 class="text-sm font-bold text-blue-900 mb-1">Nota rápida</h4>
                        <p class="text-xs text-blue-700 leading-relaxed">Asegúrate de confirmar la disponibilidad de la habitación antes de procesar el pago inicial.</p>
                    </div>
                </div>
            </div>
        </div>
    </form>

    <!-- MODAL: CREAR NUEVO CLIENTE PRINCIPAL -->
    <div x-show="newCustomerModalOpen"
         x-cloak
         class="fixed inset-0 z-50 overflow-y-auto"
         @click.self="newCustomerModalOpen = false"
         style="display: none;">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm"></div>
            <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-2xl overflow-hidden transform transition-all"
                 @click.stop>
                <!-- Header del Modal -->
                <div class="px-6 py-5 border-b border-gray-100 flex items-center justify-between bg-gray-50">
                    <div class="flex items-center space-x-3">
                        <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center">
                            <i class="fas fa-user-plus"></i>
                        </div>
                        <h3 class="text-xl font-bold text-gray-900">Crear Nuevo Cliente</h3>
                    </div>
                    <button @click="newCustomerModalOpen = false" class="text-gray-400 hover:text-gray-900 transition-colors">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>

                <!-- Contenido del Modal -->
                <div class="p-6 max-h-[80vh] overflow-y-auto">
                    <div class="p-3 bg-blue-50 border border-blue-100 rounded-xl mb-6">
                        <p class="text-xs text-blue-800 font-medium">
                            <i class="fas fa-info-circle mr-2"></i>
                            Complete los datos del cliente principal para la reserva
                        </p>
                    </div>

                    <!-- Información del Cliente -->
                    <div class="space-y-4 mb-6">
                        <h4 class="text-sm font-bold text-gray-800 flex items-center">
                            <i class="fas fa-user mr-2 text-blue-500"></i>
                            Información del Cliente
                        </h4>

                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">
                                Nombre Completo <span class="text-red-500">*</span>
                            </label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i class="fas fa-user text-gray-400 text-sm"></i>
                                </div>
                                <input type="text"
                                       x-model="newMainCustomer.name"
                                       @input="validateMainCustomerField('name')"
                                       @blur="validateMainCustomerField('name')"
                                       maxlength="255"
                                       class="w-full pl-10 pr-4 py-2.5 border rounded-xl text-sm focus:ring-blue-500 focus:border-blue-500 uppercase"
                                       :class="newMainCustomerErrors.name ? 'border-red-500' : 'border-gray-300'"
                                       placeholder="EJ: JUAN PÉREZ GARCÍA">
                            </div>
                            <template x-if="newMainCustomerErrors.name">
                                <p class="mt-1 text-xs text-red-600 flex items-center" x-text="newMainCustomerErrors.name"></p>
                            </template>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">
                                    Número de identificación <span class="text-red-500">*</span>
                                </label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <i class="fas fa-id-card text-gray-400 text-sm"></i>
                                    </div>
                                    <input type="text"
                                           x-model="newMainCustomer.identification"
                                           @input="validateMainCustomerField('identification'); checkMainCustomerIdentification()"
                                           @blur="validateMainCustomerField('identification')"
                                           @keypress="onlyNumbers($event)"
                                           maxlength="11"
                                           class="w-full pl-10 pr-4 py-2.5 border rounded-xl text-sm focus:ring-blue-500 focus:border-blue-500"
                                           :class="newMainCustomerErrors.identification || mainCustomerIdentificationExists ? 'border-red-500' : 'border-gray-300'"
                                           placeholder="Ej: 12345678">
                                </div>
                                <template x-if="mainCustomerIdentificationMessage">
                                    <p class="mt-1 text-xs flex items-center"
                                       :class="mainCustomerIdentificationExists ? 'text-red-600' : 'text-emerald-600'"
                                       x-text="mainCustomerIdentificationMessage"></p>
                                </template>
                                <template x-if="newMainCustomerErrors.identification">
                                    <p class="mt-1 text-xs text-red-600 flex items-center" x-text="newMainCustomerErrors.identification"></p>
                                </template>
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">
                                    Teléfono <span class="text-red-500">*</span>
                                </label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <i class="fas fa-phone text-gray-400 text-sm"></i>
                                    </div>
                                    <input type="text"
                                           x-model="newMainCustomer.phone"
                                           @input="validateMainCustomerField('phone')"
                                           @keypress="onlyNumbers($event)"
                                           maxlength="10"
                                           class="w-full pl-10 pr-4 py-2.5 border rounded-xl text-sm focus:ring-blue-500 focus:border-blue-500"
                                           :class="newMainCustomerErrors.phone ? 'border-red-500' : 'border-gray-300'"
                                           placeholder="Ej: 3001234567">
                                </div>
                                <template x-if="newMainCustomerErrors.phone">
                                    <p class="mt-1 text-xs text-red-600 flex items-center" x-text="newMainCustomerErrors.phone"></p>
                                </template>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">
                                    Correo electrónico (opcional)
                                </label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <i class="fas fa-envelope text-gray-400 text-sm"></i>
                                    </div>
                                    <input type="email"
                                           x-model="newMainCustomer.email"
                                           @input="validateMainCustomerField('email')"
                                           @blur="validateMainCustomerField('email')"
                                           maxlength="255"
                                           class="w-full pl-10 pr-4 py-2.5 border rounded-xl text-sm focus:ring-blue-500 focus:border-blue-500"
                                           :class="newMainCustomerErrors.email ? 'border-red-500' : 'border-gray-300'"
                                           placeholder="juan.perez@email.com">
                                </div>
                                <template x-if="newMainCustomerErrors.email">
                                    <p class="mt-1 text-xs text-red-600 flex items-center" x-text="newMainCustomerErrors.email"></p>
                                </template>
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">
                                    Dirección (opcional)
                                </label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <i class="fas fa-map-marker-alt text-gray-400 text-sm"></i>
                                    </div>
                                    <input type="text"
                                           x-model="newMainCustomer.address"
                                           maxlength="500"
                                           class="w-full pl-10 pr-4 py-2.5 border border-gray-300 rounded-xl text-sm focus:ring-blue-500 focus:border-blue-500"
                                           placeholder="Calle 123 #45-67">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Facturación Electrónica DIAN -->
                    <div class="border-t border-gray-200 pt-6 mb-6">
                        <div class="flex items-center justify-between mb-4">
                            <div class="flex items-center space-x-3">
                                <div class="p-2 rounded-xl bg-blue-50 text-blue-600">
                                    <i class="fas fa-file-invoice text-sm"></i>
                                </div>
                                <div>
                                    <h4 class="text-sm font-bold text-gray-900">Facturación Electrónica DIAN</h4>
                                    <p class="text-xs text-gray-500 mt-0.5">Activa esta opción si el cliente requiere facturación electrónica</p>
                                </div>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox"
                                       x-model="newMainCustomer.requiresElectronicInvoice"
                                       @change="updateMainCustomerRequiredFields()"
                                       class="sr-only peer">
                                <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-emerald-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-emerald-600"></div>
                            </label>
                        </div>

                        <!-- Campos DIAN -->
                        <div x-show="newMainCustomer.requiresElectronicInvoice"
                             x-transition:enter="transition ease-out duration-200"
                             x-transition:enter-start="opacity-0 transform scale-95"
                             x-transition:enter-end="opacity-100 transform scale-100"
                             class="space-y-4 border-t border-gray-200 pt-4" x-cloak>

                            <div class="bg-blue-50 border border-blue-200 rounded-lg p-3">
                                <div class="flex items-start">
                                    <i class="fas fa-info-circle text-blue-600 mt-0.5 mr-2"></i>
                                    <div class="text-xs text-blue-800">
                                        <p class="font-semibold mb-1">Campos Obligatorios para Facturación Electrónica</p>
                                        <p>Complete todos los campos marcados con <span class="text-red-500 font-bold">*</span> para poder generar facturas electrónicas válidas según la normativa DIAN.</p>
                                    </div>
                                </div>
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">
                                    Tipo de Documento <span class="text-red-500">*</span>
                                </label>
                                <select x-model="newMainCustomer.identificationDocumentId"
                                        @change="updateMainCustomerRequiredFields()"
                                        class="block w-full px-3 py-2.5 border border-gray-300 rounded-lg text-sm"
                                        :class="newMainCustomerErrors.identification_document_id ? 'border-red-300' : ''">
                                    <option value="">Seleccione...</option>
                                    @foreach($identificationDocuments as $doc)
                                        <option value="{{ $doc->id }}"
                                                data-code="{{ $doc->code }}"
                                                data-requires-dv="{{ $doc->requires_dv ? 'true' : 'false' }}">
                                            {{ $doc->name }}@if($doc->code) ({{ $doc->code }})@endif
                                        </option>
                                    @endforeach
                                </select>
                                <template x-if="newMainCustomerErrors.identification_document_id">
                                    <p class="mt-1 text-xs text-red-600" x-text="newMainCustomerErrors.identification_document_id"></p>
                                </template>
                            </div>

                            <!-- Dígito Verificador -->
                            <div x-show="mainCustomerRequiresDV" x-cloak>
                                <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">
                                    Dígito Verificador (DV) <span class="text-red-500">*</span>
                                </label>
                                <input type="text"
                                       x-model="newMainCustomer.dv"
                                       maxlength="1"
                                       readonly
                                       class="block w-full px-3 py-2.5 border border-gray-200 bg-gray-50 rounded-lg text-sm text-gray-600 cursor-not-allowed font-bold"
                                       placeholder="Automático">
                                <p class="mt-1 text-xs text-blue-600">
                                    <i class="fas fa-magic mr-1"></i> Calculado automáticamente por el sistema
                                </p>
                            </div>

                            <!-- Razón Social / Nombre Comercial -->
                            <div x-show="mainCustomerIsJuridicalPerson" class="grid grid-cols-1 md:grid-cols-2 gap-4" x-cloak>
                                <div>
                                    <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">
                                        Razón Social / Empresa <span class="text-red-500">*</span>
                                    </label>
                                    <input type="text"
                                           x-model="newMainCustomer.company"
                                           @blur="validateMainCustomerField('company')"
                                           maxlength="255"
                                           class="block w-full px-3 py-2.5 border border-gray-300 rounded-lg text-sm uppercase"
                                           :class="newMainCustomerErrors.company ? 'border-red-300' : ''"
                                           placeholder="RAZÓN SOCIAL">
                                    <template x-if="newMainCustomerErrors.company">
                                        <p class="mt-1 text-xs text-red-600" x-text="newMainCustomerErrors.company"></p>
                                    </template>
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">
                                        Nombre Comercial
                                    </label>
                                    <input type="text"
                                           x-model="newMainCustomer.tradeName"
                                           maxlength="255"
                                           class="block w-full px-3 py-2.5 border border-gray-300 rounded-lg text-sm uppercase"
                                           placeholder="NOMBRE COMERCIAL">
                                </div>
                            </div>

                            <!-- Municipio -->
                            <div>
                                <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">
                                    Municipio <span class="text-red-500">*</span>
                                </label>
                                <select x-model="newMainCustomer.municipalityId"
                                        @change="validateMainCustomerField('municipality_id')"
                                        class="block w-full px-3 py-2.5 border border-gray-300 rounded-lg text-sm"
                                        :class="newMainCustomerErrors.municipality_id ? 'border-red-300' : ''">
                                    <option value="">Seleccione un municipio...</option>
                                    @php
                                        $currentDepartment = null;
                                    @endphp
                                    @foreach($municipalities as $municipality)
                                        @if($currentDepartment !== $municipality->department)
                                            @if($currentDepartment !== null)
                                                </optgroup>
                                            @endif
                                            <optgroup label="{{ $municipality->department }}">
                                            @php
                                                $currentDepartment = $municipality->department;
                                            @endphp
                                        @endif
                                        <option value="{{ $municipality->factus_id }}">
                                            {{ $municipality->department }} - {{ $municipality->name }}
                                        </option>
                                        @if($loop->last)
                                            </optgroup>
                                        @endif
                                    @endforeach
                                </select>
                                <template x-if="newMainCustomerErrors.municipality_id">
                                    <p class="mt-1 text-xs text-red-600" x-text="newMainCustomerErrors.municipality_id"></p>
                                </template>
                            </div>

                            <!-- Tipo de Organización Legal -->
                            <div>
                                <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">
                                    Tipo de Organización Legal
                                </label>
                                <select x-model="newMainCustomer.legalOrganizationId"
                                        class="block w-full px-3 py-2.5 border border-gray-300 rounded-lg text-sm">
                                    <option value="">Seleccione...</option>
                                    @foreach($legalOrganizations as $org)
                                        <option value="{{ $org->id }}">{{ $org->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Régimen Tributario -->
                            <div>
                                <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">
                                    Régimen Tributario
                                </label>
                                <select x-model="newMainCustomer.tributeId"
                                        class="block w-full px-3 py-2.5 border border-gray-300 rounded-lg text-sm">
                                    <option value="">Seleccione...</option>
                                    @foreach($tributes as $tribute)
                                        <option value="{{ $tribute->id }}">{{ $tribute->name }} ({{ $tribute->code }})</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="flex justify-end space-x-3 pt-4 border-t border-gray-100">
                        <button @click="newCustomerModalOpen = false"
                                class="px-4 py-2 text-sm font-semibold text-gray-600 bg-white border border-gray-200 rounded-xl hover:bg-gray-50 transition-all">
                            Cancelar
                        </button>
                        <button @click="createAndSelectMainCustomer()"
                                :disabled="!isMainCustomerFormValid() || creatingMainCustomer"
                                class="px-6 py-2 text-sm font-bold text-white bg-blue-600 rounded-xl hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed transition-all flex items-center">
                            <i class="fas fa-spinner fa-spin mr-2" x-show="creatingMainCustomer"></i>
                            <i class="fas fa-check mr-2" x-show="!creatingMainCustomer"></i>
                            <span x-text="creatingMainCustomer ? 'Creando...' : 'Crear y Seleccionar'"></span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- MODAL: ASIGNAR HUÉSPED -->
    <div x-show="guestModalOpen"
         x-cloak
         class="fixed inset-0 z-50 overflow-y-auto"
         @click.self="guestModalOpen = false"
         style="display: none;">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm"></div>
            <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-2xl overflow-hidden transform transition-all"
                 @click.stop>
                <!-- Header del Modal -->
                <div class="px-6 py-5 border-b border-gray-100 flex items-center justify-between bg-gray-50">
                    <div class="flex items-center space-x-3">
                        <div class="w-10 h-10 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center">
                            <i class="fas fa-user-plus"></i>
                        </div>
                        <h3 class="text-xl font-bold text-gray-900">Asignar Persona a la Habitación</h3>
                    </div>
                    <button @click="guestModalOpen = false" class="text-gray-400 hover:text-gray-900 transition-colors">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>

                <!-- Contenido del Modal -->
                <div class="p-6 space-y-6">
                    <!-- Info de capacidad -->
                    <template x-if="currentRoomForGuestAssignment !== null && currentRoomForGuestAssignment !== undefined">
                        <div class="p-3 bg-purple-50 border border-purple-100 rounded-xl">
                            <p class="text-xs text-purple-800 font-medium">
                                <i class="fas fa-info-circle mr-2"></i>
                                Habitación: <span class="font-bold" x-text="getRoomById(currentRoomForGuestAssignment)?.number || currentRoomForGuestAssignment"></span>
                                <span class="text-gray-400 mx-2">•</span>
                                Capacidad: <span class="font-bold" x-text="getRoomById(currentRoomForGuestAssignment)?.capacity || 0"></span> personas
                                <span class="text-gray-400 mx-2">•</span>
                                Espacios disponibles: <span class="font-bold" x-text="getRoomById(currentRoomForGuestAssignment) ? (getRoomById(currentRoomForGuestAssignment).capacity - getRoomGuestsCount(currentRoomForGuestAssignment)) : 0"></span>
                            </p>
                        </div>
                    </template>
                    <template x-if="currentRoomForGuestAssignment === null || currentRoomForGuestAssignment === undefined">
                        <div class="p-3 bg-purple-50 border border-purple-100 rounded-xl">
                            <p class="text-xs text-purple-800 font-medium">
                                <i class="fas fa-info-circle mr-2"></i>
                                <template x-if="selectedRoom">
                                    Capacidad de la habitación: <span class="font-bold" x-text="selectedRoom.capacity"></span> personas
                                    <span class="text-gray-400 mx-2">•</span>
                                    Espacios disponibles: <span class="font-bold" x-text="availableSlots"></span>
                                </template>
                            </p>
                        </div>
                    </template>

                    <!-- Tabs: Buscar / Crear -->
                    <div class="flex border-b border-gray-200">
                        <button @click="guestModalTab = 'search'"
                                :class="guestModalTab === 'search' ? 'border-b-2 border-purple-600 text-purple-600' : 'text-gray-500'"
                                class="px-4 py-2 font-bold text-sm transition-colors">
                            <i class="fas fa-search mr-2"></i> Buscar Persona
                        </button>
                        <button @click="guestModalTab = 'create'"
                                :class="guestModalTab === 'create' ? 'border-b-2 border-purple-600 text-purple-600' : 'text-gray-500'"
                                class="px-4 py-2 font-bold text-sm transition-colors">
                            <i class="fas fa-plus mr-2"></i> Crear Nueva Persona
                        </button>
                    </div>

                    <!-- Tab: Buscar -->
                    <div x-show="guestModalTab === 'search'"
                         x-transition
                         class="space-y-4">
                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">
                                Buscar por nombre, documento o teléfono
                            </label>
                            <div wire:ignore id="guest-search-container">
                                <select id="guest-search-select" class="w-full"></select>
                            </div>
                        </div>
                        <div x-show="selectedGuestForAdd" class="p-4 bg-purple-50 rounded-xl border border-purple-100">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="font-bold text-gray-900" x-text="selectedGuestForAdd?.name || ''"></p>
                                    <div class="flex items-center space-x-3 text-xs text-gray-500 mt-1">
                                        <span x-text="'ID: ' + (selectedGuestForAdd?.identification || 'S/N')"></span>
                                        <span class="text-gray-300">•</span>
                                        <span x-text="'Tel: ' + (selectedGuestForAdd?.phone || 'S/N')"></span>
                                    </div>
                                </div>
                                <button @click="addGuest()"
                                        :disabled="currentRoomForGuestAssignment === null || currentRoomForGuestAssignment === undefined ? !canAssignMoreGuests : !canAssignMoreGuestsToRoom(currentRoomForGuestAssignment)"
                                        :class="(currentRoomForGuestAssignment === null || currentRoomForGuestAssignment === undefined ? canAssignMoreGuests : canAssignMoreGuestsToRoom(currentRoomForGuestAssignment)) ? 'bg-purple-600 hover:bg-purple-700' : 'bg-gray-400 cursor-not-allowed'"
                                        class="px-4 py-2 text-sm font-bold text-white rounded-xl transition-all">
                                    <i class="fas fa-check mr-2"></i> Asignar
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Tab: Crear -->
                    <div x-show="guestModalTab === 'create'" class="space-y-4 max-h-[70vh] overflow-y-auto">
                        <!-- Información del Cliente -->
                        <div class="space-y-4">
                            <h4 class="text-sm font-bold text-gray-800 flex items-center">
                                <i class="fas fa-user mr-2 text-purple-500"></i>
                                Información del Cliente
                            </h4>

                            <div>
                                <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">
                                    Nombre Completo <span class="text-red-500">*</span>
                                </label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <i class="fas fa-user text-gray-400 text-sm"></i>
                                    </div>
                                    <input type="text"
                                           x-model="newCustomer.name"
                                           @input="validateCustomerField('name')"
                                           @blur="validateCustomerField('name')"
                                           maxlength="255"
                                           class="w-full pl-10 pr-4 py-2.5 border rounded-xl text-sm focus:ring-purple-500 focus:border-purple-500 uppercase"
                                           :class="newCustomerErrors.name ? 'border-red-500' : 'border-gray-300'"
                                           placeholder="EJ: JUAN PÉREZ GARCÍA">
                                </div>
                                <template x-if="newCustomerErrors.name">
                                    <p class="mt-1 text-xs text-red-600 flex items-center" x-text="newCustomerErrors.name"></p>
                                </template>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">
                                        Número de identificación <span class="text-red-500">*</span>
                                    </label>
                                    <div class="relative">
                                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                            <i class="fas fa-id-card text-gray-400 text-sm"></i>
                                        </div>
                                        <input type="text"
                                               x-model="newCustomer.identification"
                                               @input="validateCustomerField('identification'); checkCustomerIdentification()"
                                               @blur="validateCustomerField('identification')"
                                               @keypress="onlyNumbers($event)"
                                               maxlength="11"
                                               class="w-full pl-10 pr-4 py-2.5 border rounded-xl text-sm focus:ring-purple-500 focus:border-purple-500"
                                               :class="newCustomerErrors.identification || customerIdentificationExists ? 'border-red-500' : 'border-gray-300'"
                                               placeholder="Ej: 12345678">
                                    </div>
                                    <template x-if="customerIdentificationMessage">
                                        <p class="mt-1 text-xs flex items-center"
                                           :class="customerIdentificationExists ? 'text-red-600' : 'text-emerald-600'"
                                           x-text="customerIdentificationMessage"></p>
                                    </template>
                                    <template x-if="newCustomerErrors.identification">
                                        <p class="mt-1 text-xs text-red-600 flex items-center" x-text="newCustomerErrors.identification"></p>
                                    </template>
                                </div>

                                <div>
                                    <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">
                                        Teléfono <span class="text-red-500">*</span>
                                    </label>
                                    <div class="relative">
                                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                            <i class="fas fa-phone text-gray-400 text-sm"></i>
                                        </div>
                                        <input type="text"
                                               x-model="newCustomer.phone"
                                               @input="validateCustomerField('phone')"
                                               @keypress="onlyNumbers($event)"
                                               maxlength="10"
                                               class="w-full pl-10 pr-4 py-2.5 border rounded-xl text-sm focus:ring-purple-500 focus:border-purple-500"
                                               :class="newCustomerErrors.phone ? 'border-red-500' : 'border-gray-300'"
                                               placeholder="Ej: 3001234567">
                                    </div>
                                    <template x-if="newCustomerErrors.phone">
                                        <p class="mt-1 text-xs text-red-600 flex items-center" x-text="newCustomerErrors.phone"></p>
                                    </template>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">
                                        Correo electrónico (opcional)
                                    </label>
                                    <div class="relative">
                                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                            <i class="fas fa-envelope text-gray-400 text-sm"></i>
                                        </div>
                                        <input type="email"
                                               x-model="newCustomer.email"
                                               @input="validateCustomerField('email')"
                                               @blur="validateCustomerField('email')"
                                               maxlength="255"
                                               class="w-full pl-10 pr-4 py-2.5 border rounded-xl text-sm focus:ring-purple-500 focus:border-purple-500"
                                               :class="newCustomerErrors.email ? 'border-red-500' : 'border-gray-300'"
                                               placeholder="juan.perez@email.com">
                                    </div>
                                    <template x-if="newCustomerErrors.email">
                                        <p class="mt-1 text-xs text-red-600 flex items-center" x-text="newCustomerErrors.email"></p>
                                    </template>
                                </div>

                                <div>
                                    <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">
                                        Dirección (opcional)
                                    </label>
                                    <div class="relative">
                                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                            <i class="fas fa-map-marker-alt text-gray-400 text-sm"></i>
                                        </div>
                                        <input type="text"
                                               x-model="newCustomer.address"
                                               maxlength="500"
                                               class="w-full pl-10 pr-4 py-2.5 border border-gray-300 rounded-xl text-sm focus:ring-purple-500 focus:border-purple-500"
                                               placeholder="Calle 123 #45-67">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Facturación Electrónica DIAN -->
                        <div class="border-t border-gray-200 pt-4">
                            <div class="flex items-center justify-between mb-4">
                                <div class="flex items-center space-x-3">
                                    <div class="p-2 rounded-xl bg-purple-50 text-purple-600">
                                        <i class="fas fa-file-invoice text-sm"></i>
                                    </div>
                                    <div>
                                        <h4 class="text-sm font-bold text-gray-900">Facturación Electrónica DIAN</h4>
                                        <p class="text-xs text-gray-500 mt-0.5">Activa esta opción si el cliente requiere facturación electrónica</p>
                                    </div>
                                </div>
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox"
                                           x-model="newCustomer.requiresElectronicInvoice"
                                           @change="updateCustomerRequiredFields()"
                                           class="sr-only peer">
                                    <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-purple-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-purple-600"></div>
                                </label>
                            </div>

                            <!-- Campos DIAN -->
                            <div x-show="newCustomer.requiresElectronicInvoice"
                                 x-transition:enter="transition ease-out duration-200"
                                 x-transition:enter-start="opacity-0 transform scale-95"
                                 x-transition:enter-end="opacity-100 transform scale-100"
                                 class="space-y-4 border-t border-gray-200 pt-4" x-cloak>

                                <div class="bg-purple-50 border border-purple-200 rounded-lg p-3">
                                    <div class="flex items-start">
                                        <i class="fas fa-info-circle text-purple-600 mt-0.5 mr-2"></i>
                                        <div class="text-xs text-purple-800">
                                            <p class="font-semibold mb-1">Campos Obligatorios para Facturación Electrónica</p>
                                            <p>Complete todos los campos marcados con <span class="text-red-500 font-bold">*</span> para poder generar facturas electrónicas válidas según la normativa DIAN.</p>
                                        </div>
                                    </div>
                                </div>

                                <div>
                                    <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">
                                        Tipo de Documento <span class="text-red-500">*</span>
                                    </label>
                                    <select x-model="newCustomer.identificationDocumentId"
                                            @change="updateCustomerRequiredFields()"
                                            class="block w-full px-3 py-2.5 border border-gray-300 rounded-lg text-sm"
                                            :class="newCustomerErrors.identification_document_id ? 'border-red-300' : ''">
                                        <option value="">Seleccione...</option>
                                        @foreach($identificationDocuments as $doc)
                                            <option value="{{ $doc->id }}"
                                                    data-code="{{ $doc->code }}"
                                                    data-requires-dv="{{ $doc->requires_dv ? 'true' : 'false' }}">
                                                {{ $doc->name }}@if($doc->code) ({{ $doc->code }})@endif
                                            </option>
                                        @endforeach
                                    </select>
                                    <template x-if="newCustomerErrors.identification_document_id">
                                        <p class="mt-1 text-xs text-red-600" x-text="newCustomerErrors.identification_document_id"></p>
                                    </template>
                                </div>

                                <!-- Dígito Verificador -->
                                <div x-show="customerRequiresDV" x-cloak>
                                    <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">
                                        Dígito Verificador (DV) <span class="text-red-500">*</span>
                                    </label>
                                    <input type="text"
                                           x-model="newCustomer.dv"
                                           maxlength="1"
                                           readonly
                                           class="block w-full px-3 py-2.5 border border-gray-200 bg-gray-50 rounded-lg text-sm text-gray-600 cursor-not-allowed font-bold"
                                           placeholder="Automático">
                                    <p class="mt-1 text-xs text-blue-600">
                                        <i class="fas fa-magic mr-1"></i> Calculado automáticamente por el sistema
                                    </p>
                                </div>

                                <!-- Razón Social / Nombre Comercial -->
                                <div x-show="customerIsJuridicalPerson" class="grid grid-cols-1 md:grid-cols-2 gap-4" x-cloak>
                                    <div>
                                        <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">
                                            Razón Social / Empresa <span class="text-red-500">*</span>
                                        </label>
                                        <input type="text"
                                               x-model="newCustomer.company"
                                               @blur="validateCustomerField('company')"
                                               maxlength="255"
                                               class="block w-full px-3 py-2.5 border border-gray-300 rounded-lg text-sm uppercase"
                                               :class="newCustomerErrors.company ? 'border-red-300' : ''"
                                               placeholder="RAZÓN SOCIAL">
                                        <template x-if="newCustomerErrors.company">
                                            <p class="mt-1 text-xs text-red-600" x-text="newCustomerErrors.company"></p>
                                        </template>
                                    </div>
                                    <div>
                                        <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">
                                            Nombre Comercial
                                        </label>
                                        <input type="text"
                                               x-model="newCustomer.tradeName"
                                               maxlength="255"
                                               class="block w-full px-3 py-2.5 border border-gray-300 rounded-lg text-sm uppercase"
                                               placeholder="NOMBRE COMERCIAL">
                                    </div>
                                </div>

                                <!-- Municipio -->
                                <div>
                                    <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">
                                        Municipio <span class="text-red-500">*</span>
                                    </label>
                                    <select x-model="newCustomer.municipalityId"
                                            @change="validateCustomerField('municipality_id')"
                                            class="block w-full px-3 py-2.5 border border-gray-300 rounded-lg text-sm"
                                            :class="newCustomerErrors.municipality_id ? 'border-red-300' : ''">
                                        <option value="">Seleccione un municipio...</option>
                                        @php
                                            $currentDepartment = null;
                                        @endphp
                                        @foreach($municipalities as $municipality)
                                            @if($currentDepartment !== $municipality->department)
                                                @if($currentDepartment !== null)
                                                    </optgroup>
                                                @endif
                                                <optgroup label="{{ $municipality->department }}">
                                                @php
                                                    $currentDepartment = $municipality->department;
                                                @endphp
                                            @endif
                                            <option value="{{ $municipality->factus_id }}">
                                                {{ $municipality->department }} - {{ $municipality->name }}
                                            </option>
                                            @if($loop->last)
                                                </optgroup>
                                            @endif
                                        @endforeach
                                    </select>
                                    <template x-if="newCustomerErrors.municipality_id">
                                        <p class="mt-1 text-xs text-red-600" x-text="newCustomerErrors.municipality_id"></p>
                                    </template>
                                </div>

                                <!-- Tipo de Organización Legal -->
                                <div>
                                    <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">
                                        Tipo de Organización Legal
                                    </label>
                                    <select x-model="newCustomer.legalOrganizationId"
                                            class="block w-full px-3 py-2.5 border border-gray-300 rounded-lg text-sm">
                                        <option value="">Seleccione...</option>
                                        @foreach($legalOrganizations as $org)
                                            <option value="{{ $org->id }}">{{ $org->name }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <!-- Régimen Tributario -->
                                <div>
                                    <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">
                                        Régimen Tributario
                                    </label>
                                    <select x-model="newCustomer.tributeId"
                                            class="block w-full px-3 py-2.5 border border-gray-300 rounded-lg text-sm">
                                        <option value="">Seleccione...</option>
                                        @foreach($tributes as $tribute)
                                            <option value="{{ $tribute->id }}">{{ $tribute->name }} ({{ $tribute->code }})</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="flex justify-end space-x-3 pt-4 border-t border-gray-100">
                            <button @click="guestModalOpen = false"
                                    class="px-4 py-2 text-sm font-semibold text-gray-600 bg-white border border-gray-200 rounded-xl hover:bg-gray-50 transition-all">
                                Cancelar
                            </button>
                            <button @click="createAndAddGuest()"
                                    :disabled="!isCustomerFormValid() || creatingCustomer || (currentRoomForGuestAssignment === null ? !canAssignMoreGuests : !canAssignMoreGuestsToRoom(currentRoomForGuestAssignment))"
                                    class="px-6 py-2 text-sm font-bold text-white bg-purple-600 rounded-xl hover:bg-purple-700 disabled:opacity-50 disabled:cursor-not-allowed transition-all flex items-center">
                                <i class="fas fa-spinner fa-spin mr-2" x-show="creatingCustomer"></i>
                                <i class="fas fa-check mr-2" x-show="!creatingCustomer"></i>
                                <span x-text="creatingCustomer ? 'Creando...' : 'Crear y Asignar'"></span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/css/tom-select.css" rel="stylesheet">
<style>
    .ts-dropdown {
        z-index: 10000 !important;
    }
    [x-show="guestModalOpen"] .ts-dropdown,
    [x-show="guestModalOpen"] .ts-wrapper .ts-dropdown {
        z-index: 10001 !important;
        position: absolute !important;
    }
    #guest-search-container {
        position: relative;
    }
    #guest-search-container .ts-wrapper {
        position: relative;
    }
    [x-show="guestModalOpen"] {
        z-index: 50;
    }
    [x-show="guestModalOpen"] .relative.bg-white {
        z-index: 51;
    }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/js/tom-select.complete.min.js"></script>
<script>
function reservationForm() {
    return {
        loading: false,
        isChecking: false,
        availability: null,

        customerId: '{{ $reservation->customer_id }}',
        roomId: '{{ $singleRoomId ?? "" }}',
        checkIn: '{{ $reservation->check_in_date->format('Y-m-d') }}',
        checkOut: '{{ $reservation->check_out_date->format('Y-m-d') }}',
        total: {{ round($reservation->total_amount) }},
        deposit: {{ round($reservation->deposit) }},
        guestsCount: {{ $reservation->guests_count ?? 1 }},
        notes: `{{ $reservation->notes ?? '' }}`,

        // Multiple rooms support
        showMultiRoomSelector: {{ $isMultiRoom ? 'true' : 'false' }},
        selectedRoomIds: @json($selectedRoomIds),
        roomGuests: @json($roomGuests),
        currentRoomForGuestAssignment: null,

        rooms: @json($roomsData),
        customerSelect: null,
        roomSelect: null,
        multiRoomSelect: null,

        // Guest assignment modal
        guestModalOpen: false,
        guestModalTab: 'search',
        assignedGuests: @json($legacyGuests),
        selectedGuestForAdd: null,
        newCustomer: {
            name: '',
            identification: '',
            phone: '',
            email: '',
            address: '',
            requiresElectronicInvoice: false,
            identificationDocumentId: '',
            dv: '',
            company: '',
            tradeName: '',
            municipalityId: '',
            legalOrganizationId: '',
            tributeId: ''
        },
        creatingCustomer: false,
        guestSelect: null,
        newCustomerErrors: {
            name: null,
            identification: null,
            phone: null,
            email: null,
            identification_document_id: null,
            company: null,
            municipality_id: null
        },
        customerIdentificationMessage: '',
        customerIdentificationExists: false,
        customerRequiresDV: false,
        customerIsJuridicalPerson: false,

        // New customer modal (for main customer)
        newCustomerModalOpen: false,
        newMainCustomer: {
            name: '',
            identification: '',
            phone: '',
            email: '',
            address: '',
            requiresElectronicInvoice: false,
            identificationDocumentId: '',
            dv: '',
            company: '',
            tradeName: '',
            municipalityId: '',
            legalOrganizationId: '',
            tributeId: ''
        },
        creatingMainCustomer: false,
        newMainCustomerErrors: {
            name: null,
            identification: null,
            phone: null,
            email: null,
            identification_document_id: null,
            company: null,
            municipality_id: null
        },
        mainCustomerIdentificationMessage: '',
        mainCustomerIdentificationExists: false,
        mainCustomerRequiresDV: false,
        mainCustomerIsJuridicalPerson: false,

        init() {
            this.initSelectors();

            // Set initial customer value if exists
            if (this.customerId && this.customerSelect) {
                this.$nextTick(() => {
                    if (this.customerSelect) {
                        this.customerSelect.setValue(this.customerId);
                    }
                });
            }

            // Re-calcular disponibilidad cuando cambien los datos clave
            this.$watch('roomId', () => {
                if (!this.showMultiRoomSelector) {
                    this.checkAvailability();
                }
            });
            this.$watch('showMultiRoomSelector', (newVal) => {
                this.$nextTick(() => {
                    if (newVal) {
                        // Switch to multi-room mode
                        this.roomId = ''; // Clear single room selection
                        if (this.roomSelect) {
                            this.roomSelect.destroy();
                            this.roomSelect = null;
                        }
                        this.initMultiRoomSelector();
                    } else {
                        // Switch to single room mode
                        this.selectedRoomIds = []; // Clear multi-room selection
                        this.roomGuests = {};
                        if (this.multiRoomSelect) {
                            this.multiRoomSelect.destroy();
                            this.multiRoomSelect = null;
                        }
                        this.initSingleRoomSelector();
                    }
                    this.recalculateTotal();
                });
            });
            this.$watch('checkIn', () => {
                if (!this.showMultiRoomSelector) {
                    this.checkAvailability();
                }
                this.recalculateTotal();
            });
            this.$watch('checkOut', () => {
                if (!this.showMultiRoomSelector) {
                    this.checkAvailability();
                }
                this.recalculateTotal();
            });
            this.$watch('guestsCount', () => {
                this.recalculateTotal();
            });
            this.$watch('selectedRoomIds', () => {
                this.recalculateTotal();
            });
            this.$watch('roomGuests', () => {
                this.recalculateTotal();
            }, { deep: true });

            // Watch for guest modal tab changes to reinitialize selector
            this.$watch('guestModalTab', (newTab) => {
                if (newTab === 'search' && this.guestModalOpen) {
                    this.$nextTick(() => {
                        setTimeout(() => {
                            this.initGuestSelector();
                        }, 100);
                    });
                }
            });

            // Watch for main customer identification changes to calculate DV
            this.$watch('newMainCustomer.identification', (value) => {
                if (this.mainCustomerIsJuridicalPerson) {
                    this.calculateMainCustomerDV(value);
                }
            });

            // Watch for main customer identification document changes
            this.$watch('newMainCustomer.identificationDocumentId', () => {
                this.updateMainCustomerRequiredFields();
            });

            // Watch for customer identification changes to calculate DV
            this.$watch('newCustomer.identification', (value) => {
                if (this.customerIsJuridicalPerson) {
                    this.calculateCustomerDV(value);
                }
            });

            // Watch for customer identification document changes
            this.$watch('newCustomer.identificationDocumentId', () => {
                this.updateCustomerRequiredFields();
            });
        },

        initSelectors() {
            this.initCustomerSelector();
            if (this.showMultiRoomSelector) {
                this.initMultiRoomSelector();
            } else {
                this.initSingleRoomSelector();
            }
        },

        initCustomerSelector() {
            this.customerSelect = new TomSelect('#customer_id', {
                valueField: 'id',
                labelField: 'name',
                searchField: ['name', 'identification', 'phone'],
                loadThrottle: 400,
                maxOptions: 5,
                minLength: 0,
                placeholder: 'Buscar por nombre, identificación o teléfono...',
                dropdownParent: 'body',
                shouldLoad: () => {
                    return true; // Always allow loading
                },
                load: (query, callback) => {
                    const searchQuery = query || '';
                    const url = `/api/customers/search?q=${encodeURIComponent(searchQuery)}`;
                    fetch(url, {
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    })
                        .then(response => response.json())
                        .then(json => {
                            callback(json.results || []);
                        }).catch(() => {
                            callback();
                        });
                },
                render: {
                    option: function(item, escape) {
                        return `
                            <div class="px-4 py-3 border-b border-gray-50 hover:bg-emerald-50 transition-colors">
                                <div class="flex flex-col">
                                    <span class="font-bold text-gray-900 text-sm mb-1">${escape(item.name)}</span>
                                    <div class="flex items-center space-x-3">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-medium bg-gray-100 text-gray-600">
                                            <i class="fas fa-id-card mr-1 opacity-50"></i> ID: ${escape(item.identification || 'S/N')}
                                        </span>
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-medium bg-blue-50 text-blue-600">
                                            <i class="fas fa-phone mr-1 opacity-50"></i> ${escape(item.phone || 'S/N')}
                                        </span>
                                    </div>
                                </div>
                            </div>`;
                    },
                    item: function(item, escape) {
                        return `<div class="font-bold text-gray-800">${escape(item.name)} <span class="text-gray-400 font-normal ml-1">(${escape(item.identification || 'S/N')})</span></div>`;
                    },
                    no_results: (data) => `<div class="px-4 py-3 text-sm text-gray-500 italic">No se encontraron resultados para "${data.input}"</div>`,
                    loading: () => `<div class="px-4 py-3 text-sm text-gray-500 flex items-center"><i class="fas fa-spinner fa-spin mr-2"></i> Buscando...</div>`
                },
                onChange: (val) => this.customerId = val,
                onFocus: () => {
                    // Load last 5 customers when field receives focus
                    if (!this.customerSelect.isLoading && this.customerSelect.options.length === 0) {
                        this.customerSelect.load('');
                    }
                },
                onType: (str) => {
                    // Force load when user types
                    this.customerSelect.load(str || '');
                }
            });

            // Load initial customers when selector is ready
            this.$nextTick(() => {
                if (this.customerSelect) {
                    this.customerSelect.load('');
                    // Set initial value if exists
                    if (this.customerId) {
                        this.customerSelect.setValue(this.customerId);
                    }
                }
            });

        },

        initSingleRoomSelector() {
            // Destroy existing if any
            if (this.roomSelect) {
                try {
                    this.roomSelect.destroy();
                } catch (e) {
                    console.warn('Error destroying room select:', e);
                }
                this.roomSelect = null;
            }

            this.$nextTick(() => {
                setTimeout(() => {
                    const roomSelectEl = document.getElementById('room_id');
                    if (!roomSelectEl) {
                        console.warn('room_id element not found');
                        return;
                    }

                    try {
                        this.roomSelect = new TomSelect('#room_id', {
                            create: false,
                            dropdownParent: 'body',
                            render: {
                                option: function(data, escape) {
                                    return `<div class="px-4 py-2 hover:bg-emerald-50 transition-colors"><strong>${escape(data.text)}</strong></div>`;
                                }
                            },
                            onChange: (val) => this.roomId = val
                        });
                        // Set initial value if exists
                        if (this.roomId) {
                            this.roomSelect.setValue(this.roomId);
                        }
                    } catch (error) {
                        console.error('Error initializing room selector:', error);
                    }
                }, 100);
            });
        },

        initMultiRoomSelector() {
            // Destroy existing if any
            if (this.multiRoomSelect) {
                try {
                    this.multiRoomSelect.destroy();
                } catch (e) {
                    console.warn('Error destroying multi room select:', e);
                }
                this.multiRoomSelect = null;
            }

            // Wait for DOM to be ready
            this.$nextTick(() => {
                setTimeout(() => {
                    const multiRoomSelectEl = document.getElementById('room_ids');
                    if (!multiRoomSelectEl) {
                        console.warn('room_ids element not found');
                        return;
                    }

                    try {
                        this.multiRoomSelect = new TomSelect('#room_ids', {
                            create: false,
                            multiple: true,
                            maxItems: null,
                            dropdownParent: 'body',
                            placeholder: 'Selecciona una o más habitaciones...',
                            render: {
                                option: function(data, escape) {
                                    return `<div class="px-4 py-2 hover:bg-emerald-50 transition-colors"><strong>${escape(data.text)}</strong></div>`;
                                },
                                item: function(data, escape) {
                                    return `<div class="item">${escape(data.text)}</div>`;
                                }
                            },
                            onChange: (values) => {
                                if (values && values.length > 0) {
                                    this.selectedRoomIds = values.map(v => parseInt(v));
                                } else {
                                    this.selectedRoomIds = [];
                                }
                                this.onRoomsSelectionChange();
                            },
                            onInitialize: () => {
                                // Set initial values if any
                                if (this.selectedRoomIds && this.selectedRoomIds.length > 0) {
                                    const stringValues = this.selectedRoomIds.map(id => id.toString());
                                    this.multiRoomSelect.setValue(stringValues);
                                }
                            }
                        });
                    } catch (error) {
                        console.error('Error initializing multi room selector:', error);
                    }
                }, 100);
            });
        },

        get selectedRoom() {
            return this.rooms.find(r => r.id == this.roomId) || null;
        },

        getRoomById(roomId) {
            return this.rooms.find(r => r.id == roomId) || null;
        },

        getRoomGuests(roomId) {
            const id = parseInt(roomId);
            return this.roomGuests[id] || [];
        },

        getRoomGuestsCount(roomId) {
            const id = parseInt(roomId);
            const guests = this.roomGuests[id] || [];
            return Array.isArray(guests) ? guests.length : 0;
        },

        canAssignMoreGuestsToRoom(roomId) {
            const id = parseInt(roomId);
            const room = this.getRoomById(id);
            if (!room) return false;
            const currentCount = this.getRoomGuestsCount(id);
            return currentCount < room.capacity;
        },

        isGuestAlreadyAssignedToAnyRoom(guestId, excludeRoomId = null) {
            // Check if guest is already assigned to any room (excluding the specified room if provided)
            const excludeId = excludeRoomId !== null && excludeRoomId !== undefined ? parseInt(excludeRoomId) : null;
            for (const roomId in this.roomGuests) {
                const id = parseInt(roomId);
                // Skip the excluded room
                if (excludeId !== null && id === excludeId) {
                    continue;
                }
                const guests = this.roomGuests[roomId];
                if (Array.isArray(guests) && guests.some(g => g.id === parseInt(guestId))) {
                    return true;
                }
            }
            return false;
        },

        getRoomWhereGuestIsAssigned(guestId) {
            // Return the room ID where the guest is currently assigned
            for (const roomId in this.roomGuests) {
                const guests = this.roomGuests[roomId];
                if (Array.isArray(guests) && guests.some(g => g.id === parseInt(guestId))) {
                    const room = this.getRoomById(parseInt(roomId));
                    return room ? room.number : roomId;
                }
            }
            return null;
        },

        onRoomsSelectionChange() {
            // Ensure all IDs are integers
            this.selectedRoomIds = this.selectedRoomIds.map(id => parseInt(id));

            // Initialize roomGuests for newly selected rooms
            const newRoomGuests = {};
            this.selectedRoomIds.forEach(roomId => {
                const id = parseInt(roomId);
                // Preserve existing guests or initialize empty array
                newRoomGuests[id] = this.roomGuests[id] || [];
            });
            // Update roomGuests with only selected rooms
            this.roomGuests = newRoomGuests;
            this.recalculateTotal();
        },

        removeRoom(roomId) {
            const id = parseInt(roomId);
            this.selectedRoomIds = this.selectedRoomIds.filter(rid => parseInt(rid) !== id);
            if (this.roomGuests[id]) {
                delete this.roomGuests[id];
                // Trigger reactivity
                this.roomGuests = { ...this.roomGuests };
            }
            // Update TomSelect if it exists
            if (this.multiRoomSelect) {
                const stringIds = this.selectedRoomIds.map(rid => rid.toString());
                this.multiRoomSelect.setValue(stringIds);
            }
            this.recalculateTotal();
        },

        calculateTotalGuestsCount() {
            if (!this.showMultiRoomSelector) {
                return this.guestsCount || 1;
            }
            let total = 0;
            this.selectedRoomIds.forEach(roomId => {
                total += this.getRoomGuestsCount(roomId);
            });
            return total || 1; // Minimum 1 for the reservation
        },

        get selectedCustomerInfo() {
            if (!this.customerId) return null;
            const option = this.customerSelect?.options[this.customerId];
            if (!option) return null;
            return {
                id: option.identification || 'S/N',
                phone: option.phone || 'S/N'
            };
        },

        get nights() {
            if (!this.checkIn || !this.checkOut) return 0;
            const start = new Date(this.checkIn);
            const end = new Date(this.checkOut);
            const diff = Math.ceil((end - start) / (1000 * 60 * 60 * 24));
            return diff > 0 ? diff : 0;
        },

        getPriceForGuests() {
            if (!this.selectedRoom || !this.guestsCount || this.guestsCount <= 0) {
                // Return default price (1 person) if no guests selected
                const prices = this.selectedRoom?.occupancyPrices || {};
                return prices[1] || this.selectedRoom?.price || 0;
            }

            const prices = this.selectedRoom.occupancyPrices || {};
            const capacity = this.selectedRoom.capacity || 2;
            const actualGuests = parseInt(this.guestsCount);
            const baseGuests = Math.min(actualGuests, capacity);
            const exceededGuests = Math.max(0, actualGuests - capacity);

            // Get base price for 1 person
            let basePrice = 0;
            if (prices[1] !== undefined && parseFloat(prices[1]) > 0) {
                basePrice = parseFloat(prices[1]);
            } else if (this.selectedRoom.price1Person && parseFloat(this.selectedRoom.price1Person) > 0) {
                basePrice = parseFloat(this.selectedRoom.price1Person);
            } else {
                basePrice = parseFloat(this.selectedRoom.price) || 0;
            }

            // Add price for additional persons within capacity (from person 2 onwards)
            // For 2 persons: basePrice + priceAdditionalPerson
            // For 3 persons: basePrice + (priceAdditionalPerson * 2)
            // etc.
            const additionalPersonsWithinCapacity = Math.max(0, baseGuests - 1);
            if (additionalPersonsWithinCapacity > 0 && this.selectedRoom.priceAdditionalPerson) {
                const additionalPrice = parseFloat(this.selectedRoom.priceAdditionalPerson) || 0;
                basePrice += additionalPrice * additionalPersonsWithinCapacity;
            }

            // Add price for exceeded guests (beyond capacity)
            if (exceededGuests > 0 && this.selectedRoom.priceAdditionalPerson) {
                const additionalPrice = parseFloat(this.selectedRoom.priceAdditionalPerson) || 0;
                basePrice += additionalPrice * exceededGuests;
            }

            return basePrice;
        },

        getRoomPriceForGuests(roomId, guestCount) {
            // Calculate price for the ACTUAL number of guests assigned to this room
            // Formula: basePrice + (priceAdditionalPerson * (numberOfGuests - 1))
            const room = this.getRoomById(roomId);
            if (!room) return 0;

            const capacity = room.capacity || 2;
            const actualGuests = (guestCount && guestCount > 0) ? parseInt(guestCount) : 1;
            const baseGuests = Math.min(actualGuests, capacity);
            const exceededGuests = Math.max(0, actualGuests - capacity);

            // Get base price for 1 person
            let basePrice = 0;
            const prices = room.occupancyPrices || {};

            if (prices[1] !== undefined && parseFloat(prices[1]) > 0) {
                basePrice = parseFloat(prices[1]);
            } else if (room.price1Person && parseFloat(room.price1Person) > 0) {
                basePrice = parseFloat(room.price1Person);
            } else {
                basePrice = parseFloat(room.price) || 0;
            }

            // Add price for additional persons within capacity (from person 2 onwards)
            // For 2 persons: basePrice + priceAdditionalPerson
            // For 3 persons: basePrice + (priceAdditionalPerson * 2)
            // etc.
            const additionalPersonsWithinCapacity = Math.max(0, baseGuests - 1);
            if (additionalPersonsWithinCapacity > 0 && room.priceAdditionalPerson) {
                const additionalPrice = parseFloat(room.priceAdditionalPerson) || 0;
                basePrice += additionalPrice * additionalPersonsWithinCapacity;
            }

            // Add price for exceeded guests (beyond capacity)
            if (exceededGuests > 0 && room.priceAdditionalPerson) {
                const additionalPrice = parseFloat(room.priceAdditionalPerson) || 0;
                basePrice += additionalPrice * exceededGuests;
            }

            return basePrice;
        },

        get autoCalculatedTotal() {
            if (this.showMultiRoomSelector) {
                // Calculate total for multiple rooms
                if (this.selectedRoomIds.length === 0 || this.nights <= 0) return 0;
                let total = 0;
                this.selectedRoomIds.forEach(roomId => {
                    const guestCount = this.getRoomGuestsCount(roomId);
                    // Get the total room price based on actual number of assigned guests
                    // If no guests assigned, getRoomPriceForGuests will use price for 1 person (minimum)
                    const pricePerNight = this.getRoomPriceForGuests(roomId, guestCount);
                    total += pricePerNight * this.nights;
                });
                return total;
            } else {
                // Single room mode (backward compatibility)
                if (!this.selectedRoom || this.nights <= 0) return 0;
                const pricePerNight = this.getPriceForGuests();
                return pricePerNight * this.nights;
            }
        },

        get balance() {
            return this.total - this.deposit;
        },

        get isValid() {
            const basicValid = this.customerId &&
                              this.checkIn &&
                              this.checkOut &&
                              this.nights > 0 &&
                              this.balance >= 0;

            if (this.showMultiRoomSelector) {
                return basicValid &&
                       this.selectedRoomIds.length > 0 &&
                       this.selectedRoomIds.every(roomId => {
                           const room = this.getRoomById(roomId);
                           const guestCount = this.getRoomGuestsCount(roomId);
                           return room && guestCount <= room.capacity;
                       });
            } else {
                return basicValid &&
                       this.roomId &&
                       (this.availability === true || this.availability === null) &&
                       this.isWithinCapacity();
            }
        },

        get canAssignMoreGuests() {
            if (!this.selectedRoom) return false;
            const totalGuests = this.assignedGuests.length;
            return totalGuests < this.selectedRoom.capacity;
        },

        get availableSlots() {
            if (!this.selectedRoom) return 0;
            const totalGuests = this.assignedGuests.length;
            return Math.max(0, this.selectedRoom.capacity - totalGuests);
        },

        isWithinCapacity() {
            if (!this.selectedRoom) return true;
            const totalGuests = this.assignedGuests.length;
            return totalGuests <= this.selectedRoom.capacity;
        },

        recalculateTotal() {
            // Solo auto-asignar si el total actual es 0 o coincide con el cálculo anterior
            // Esto permite al usuario editar el total manualmente si lo desea
            if (this.autoCalculatedTotal > 0) {
                this.total = this.autoCalculatedTotal;
            }
        },

        async checkAvailability() {
            if (!this.roomId || !this.checkIn || !this.checkOut || this.nights <= 0) {
                this.availability = null;
                return;
            }

            this.isChecking = true;
            try {
                const url = `{{ route('api.check-availability') }}?room_id=${this.roomId}&check_in_date=${this.checkIn}&check_out_date=${this.checkOut}&reservation_id={{ $reservation->id }}`;
                const response = await fetch(url);
                const data = await response.json();
                this.availability = data.available;
            } catch (error) {
                console.error('Error checking availability:', error);
                this.availability = null;
            } finally {
                this.isChecking = false;
            }
        },

        formatCurrency(val) {
            return new Intl.NumberFormat('es-CO', {
                style: 'currency',
                currency: 'COP',
                minimumFractionDigits: 0
            }).format(val);
        },

        // Guest assignment methods
        openGuestModal(roomId = null) {
            this.currentRoomForGuestAssignment = roomId;
            this.guestModalOpen = true;
            this.guestModalTab = 'search';
            this.selectedGuestForAdd = null;
            this.newCustomer = {
                name: '',
                identification: '',
                phone: '',
                email: '',
                address: '',
                requiresElectronicInvoice: false,
                identificationDocumentId: '',
                dv: '',
                company: '',
                tradeName: '',
                municipalityId: '',
                legalOrganizationId: '',
                tributeId: ''
            };
            this.newCustomerErrors = {
                name: null,
                identification: null,
                phone: null,
                email: null,
                identification_document_id: null,
                company: null,
                municipality_id: null
            };
            this.customerIdentificationMessage = '';
            this.customerIdentificationExists = false;
            this.customerRequiresDV = false;
            this.customerIsJuridicalPerson = false;

            // Initialize guest selector when modal opens
            this.$nextTick(() => {
                setTimeout(() => {
                    this.initGuestSelector();
                }, 100);
            });
        },

        initGuestSelector() {
            const selectElement = document.getElementById('guest-search-select');
            if (!selectElement) return;

            // Destroy existing instance if it exists
            if (this.guestSelect) {
                try {
                    this.guestSelect.destroy();
                } catch (e) {
                    console.warn('Error destroying guest select:', e);
                }
                this.guestSelect = null;
            }

            // Create new instance
            try {
                this.guestSelect = new TomSelect('#guest-search-select', {
                    valueField: 'id',
                    labelField: 'name',
                    searchField: ['name', 'identification', 'phone'],
                    loadThrottle: 300,
                    maxOptions: 5,
                    minLength: 0,
                    placeholder: 'Escribe nombre, documento o teléfono...',
                    dropdownParent: 'body',
                    shouldLoad: () => {
                        return true; // Always allow loading
                    },
                    load: (query, callback) => {
                        const searchQuery = query || '';
                        const url = `/api/customers/search?q=${encodeURIComponent(searchQuery)}`;
                        fetch(url, {
                            headers: {
                                'Accept': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest'
                            }
                        })
                            .then(response => {
                                if (!response.ok) {
                                    throw new Error('Network response was not ok');
                                }
                                return response.json();
                            })
                            .then(json => {
                                const results = json.results || [];
                                // Limit to 5 results
                                callback(results.slice(0, 5));
                            }).catch((error) => {
                                console.error('Error loading guests:', error);
                                callback();
                            });
                    },
                    render: {
                        option: (item, escape) => {
                            return `
                                <div class="px-4 py-3 border-b border-gray-50 hover:bg-purple-50 transition-colors cursor-pointer">
                                    <div class="flex flex-col">
                                        <span class="font-bold text-gray-900 text-sm mb-1">${escape(item.name)}</span>
                                        <div class="flex items-center space-x-3">
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-medium bg-gray-100 text-gray-600">
                                                <i class="fas fa-id-card mr-1 opacity-50"></i> ID: ${escape(item.identification || 'S/N')}
                                            </span>
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-medium bg-blue-50 text-blue-600">
                                                <i class="fas fa-phone mr-1 opacity-50"></i> ${escape(item.phone || 'S/N')}
                                            </span>
                                        </div>
                                    </div>
                                </div>`;
                        },
                        item: (item, escape) => {
                            return `<div class="font-bold text-gray-800">${escape(item.name)} <span class="text-gray-400 font-normal ml-1">(${escape(item.identification || 'S/N')})</span></div>`;
                        },
                        no_results: (data) => `<div class="px-4 py-3 text-sm text-gray-500 italic">No se encontraron resultados para "${escape(data.input)}"</div>`,
                        loading: () => `<div class="px-4 py-3 text-sm text-gray-500 flex items-center"><i class="fas fa-spinner fa-spin mr-2"></i> Buscando...</div>`,
                        loading_more: () => `<div class="px-4 py-3 text-sm text-gray-500 flex items-center"><i class="fas fa-spinner fa-spin mr-2"></i> Cargando más resultados...</div>`
                    },
                    onChange: (val) => {
                        if (val) {
                            const option = this.guestSelect.options[val];
                            if (option) {
                                this.selectedGuestForAdd = {
                                    id: option.id,
                                    name: option.name,
                                    identification: option.identification || 'S/N',
                                    phone: option.phone || 'S/N'
                                };
                            }
                        } else {
                            this.selectedGuestForAdd = null;
                        }
                    },
                    onFocus: () => {
                        // Load clients when field receives focus
                        if (!this.guestSelect.isLoading && this.guestSelect.options.length === 0) {
                            this.guestSelect.load('');
                        }
                    },
                    onType: (str) => {
                        // Force load when user types
                        this.guestSelect.load(str || '');
                    }
                });

                // Load initial clients when selector is ready
                this.$nextTick(() => {
                    if (this.guestSelect) {
                        this.guestSelect.load('');
                    }
                });
            } catch (error) {
                console.error('Error initializing guest selector:', error);
            }
        },

        addGuest() {
            if (!this.selectedGuestForAdd || !this.selectedGuestForAdd.id) return;

            const roomId = this.currentRoomForGuestAssignment;

            if (roomId !== null && roomId !== undefined) {
                // Multiple rooms mode
                const id = parseInt(roomId);
                const room = this.getRoomById(id);
                if (!room) {
                    console.error('Room not found:', id);
                    return;
                }

                // Check if guest is already assigned to this specific room
                const roomGuests = this.getRoomGuests(id);
                const alreadyAssignedToThisRoom = roomGuests.some(g => g.id === this.selectedGuestForAdd.id);
                if (alreadyAssignedToThisRoom) {
                    alert('Este cliente ya está asignado a esta habitación');
                    return;
                }

                // Check if guest is already assigned to any other room (excluding current room)
                const alreadyAssignedToOtherRoom = this.isGuestAlreadyAssignedToAnyRoom(this.selectedGuestForAdd.id, id);
                if (alreadyAssignedToOtherRoom) {
                    const otherRoom = this.getRoomWhereGuestIsAssigned(this.selectedGuestForAdd.id);
                    alert('Este cliente ya está asignado a la habitación ' + otherRoom + '. No se puede asignar la misma persona a múltiples habitaciones.');
                    return;
                }

                // Check capacity
                const currentCount = this.getRoomGuestsCount(id);
                if (currentCount >= room.capacity) {
                    alert('No se pueden asignar más huéspedes. La habitación ha alcanzado su capacidad máxima de ' + room.capacity + ' personas.');
                    return;
                }

                // Initialize array if needed
                if (!this.roomGuests[id] || !Array.isArray(this.roomGuests[id])) {
                    this.roomGuests[id] = [];
                }

                this.roomGuests[id].push({ ...this.selectedGuestForAdd });
                // Trigger reactivity by creating new object
                this.roomGuests = { ...this.roomGuests };
                // Close modal after successful assignment
                this.guestModalOpen = false;
            } else {
                // Single room mode (backward compatibility)
                const alreadyAssigned = this.assignedGuests.some(g => g.id === this.selectedGuestForAdd.id);
                if (alreadyAssigned) {
                    alert('Este cliente ya está asignado a la habitación');
                    return;
                }

                if (!this.canAssignMoreGuests) {
                    alert('No se pueden asignar más huéspedes. La habitación ha alcanzado su capacidad máxima de ' + this.selectedRoom.capacity + ' personas.');
                    return;
                }

                this.assignedGuests.push({ ...this.selectedGuestForAdd });
                // Close modal after successful assignment
                this.guestModalOpen = false;
            }

            this.selectedGuestForAdd = null;
            if (this.guestSelect) {
                this.guestSelect.clear();
            }
            this.recalculateTotal();
        },

        removeGuest(roomId, index) {
            if (roomId !== null && roomId !== undefined) {
                // Multiple rooms mode
                const id = parseInt(roomId);
                if (this.roomGuests[id] && Array.isArray(this.roomGuests[id]) && this.roomGuests[id][index]) {
                    this.roomGuests[id].splice(index, 1);
                    // Trigger reactivity
                    this.roomGuests = { ...this.roomGuests };
                }
            } else {
                // Single room mode
                this.assignedGuests.splice(index, 1);
            }
            this.recalculateTotal();
        },

        async createAndAddGuest() {
            // Validate all fields before submitting
            this.validateCustomerField('name');
            this.validateCustomerField('identification');
            this.validateCustomerField('phone');
            if (this.newCustomer.email) {
                this.validateCustomerField('email');
            }

            if (this.newCustomer.requiresElectronicInvoice) {
                this.validateCustomerField('identification_document_id');
                this.validateCustomerField('municipality_id');
                if (this.customerIsJuridicalPerson) {
                    this.validateCustomerField('company');
                }
            }

            if (!this.isCustomerFormValid()) {
                if (this.customerIdentificationExists) {
                    alert('Este documento de identidad ya está registrado. Por favor verifique los datos.');
                }
                return;
            }

            this.creatingCustomer = true;
            try {
                const requestData = {
                    name: this.newCustomer.name,
                    identification: this.newCustomer.identification,
                    phone: this.newCustomer.phone,
                    email: this.newCustomer.email || null,
                    address: this.newCustomer.address || null,
                    is_active: true,
                    requires_electronic_invoice: this.newCustomer.requiresElectronicInvoice || false
                };

                if (this.newCustomer.requiresElectronicInvoice) {
                    requestData.identification_document_id = this.newCustomer.identificationDocumentId;
                    requestData.municipality_id = this.newCustomer.municipalityId;
                    if (this.newCustomer.dv) {
                        requestData.dv = this.newCustomer.dv;
                    }
                    if (this.newCustomer.company) {
                        requestData.company = this.newCustomer.company;
                    }
                    if (this.newCustomer.tradeName) {
                        requestData.trade_name = this.newCustomer.tradeName;
                    }
                    if (this.newCustomer.legalOrganizationId) {
                        requestData.legal_organization_id = this.newCustomer.legalOrganizationId;
                    }
                    if (this.newCustomer.tributeId) {
                        requestData.tribute_id = this.newCustomer.tributeId;
                    }
                } else {
                    // Default to CC for non-electronic invoice customers
                    requestData.identification_document_id = 3;
                }

                const response = await fetch('{{ route("customers.store") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(requestData)
                });

                const data = await response.json();

                if (data.success && data.customer) {
                    const roomId = this.currentRoomForGuestAssignment;
                    const newGuest = {
                        id: data.customer.id,
                        name: data.customer.name,
                        identification: data.customer.tax_profile?.identification || this.newCustomer.identification,
                        phone: this.newCustomer.phone
                    };

                    if (roomId !== null && roomId !== undefined) {
                        // Multiple rooms mode
                        const id = parseInt(roomId);
                        const room = this.getRoomById(id);
                        if (!room) {
                            console.error('Room not found:', id);
                            return;
                        }

                        // Check if guest is already assigned to this specific room
                        const roomGuests = this.getRoomGuests(id);
                        const alreadyAssignedToThisRoom = roomGuests.some(g => g.id === newGuest.id);
                        if (alreadyAssignedToThisRoom) {
                            alert('Este cliente ya está asignado a esta habitación');
                        } else {
                            // Check if guest is already assigned to any other room (excluding current room)
                            const alreadyAssignedToOtherRoom = this.isGuestAlreadyAssignedToAnyRoom(newGuest.id, id);
                            if (alreadyAssignedToOtherRoom) {
                                const otherRoom = this.getRoomWhereGuestIsAssigned(newGuest.id);
                                alert('Este cliente ya está asignado a la habitación ' + otherRoom + '. No se puede asignar la misma persona a múltiples habitaciones.');
                            } else {
                                const currentCount = this.getRoomGuestsCount(id);
                                if (currentCount >= room.capacity) {
                                    alert('No se pueden asignar más huéspedes. La habitación ha alcanzado su capacidad máxima de ' + room.capacity + ' personas.');
                                } else {
                                    // Initialize array if needed
                                    if (!this.roomGuests[id] || !Array.isArray(this.roomGuests[id])) {
                                        this.roomGuests[id] = [];
                                    }
                                    this.roomGuests[id].push(newGuest);
                                    // Trigger reactivity
                                    this.roomGuests = { ...this.roomGuests };
                                    this.openGuestModal(id);
                                    this.guestModalOpen = false;
                                }
                            }
                        }
                    } else {
                        // Single room mode (backward compatibility)
                        const alreadyAssigned = this.assignedGuests.some(g => g.id === newGuest.id);
                        if (alreadyAssigned) {
                            alert('Este cliente ya está asignado a la habitación');
                        } else if (!this.canAssignMoreGuests) {
                            alert('No se pueden asignar más huéspedes. La habitación ha alcanzado su capacidad máxima de ' + this.selectedRoom.capacity + ' personas.');
                        } else {
                            this.assignedGuests.push(newGuest);
                            this.openGuestModal(null);
                            this.guestModalOpen = false;
                        }
                    }
                } else {
                    const errors = data.errors || {};

                    // Map backend errors to frontend error fields
                    if (errors.name) {
                        this.newCustomerErrors.name = Array.isArray(errors.name) ? errors.name[0] : errors.name;
                    }
                    if (errors.identification) {
                        this.newCustomerErrors.identification = Array.isArray(errors.identification) ? errors.identification[0] : errors.identification;
                    }
                    if (errors.phone) {
                        this.newCustomerErrors.phone = Array.isArray(errors.phone) ? errors.phone[0] : errors.phone;
                    }
                    if (errors.email) {
                        this.newCustomerErrors.email = Array.isArray(errors.email) ? errors.email[0] : errors.email;
                    }
                    if (errors.identification_document_id) {
                        this.newCustomerErrors.identification_document_id = Array.isArray(errors.identification_document_id) ? errors.identification_document_id[0] : errors.identification_document_id;
                    }
                    if (errors.company) {
                        this.newCustomerErrors.company = Array.isArray(errors.company) ? errors.company[0] : errors.company;
                    }
                    if (errors.municipality_id) {
                        this.newCustomerErrors.municipality_id = Array.isArray(errors.municipality_id) ? errors.municipality_id[0] : errors.municipality_id;
                    }

                    // If there are other errors not mapped, show alert
                    const unmappedErrors = Object.keys(errors).filter(key => !['name', 'identification', 'phone', 'email', 'identification_document_id', 'company', 'municipality_id'].includes(key));
                    if (unmappedErrors.length > 0 || (!errors.name && !errors.identification && !errors.phone && !errors.email)) {
                        const errorMessages = Object.values(errors).flat().join('\n');
                        alert('Error al crear el cliente: ' + (errorMessages || data.message || 'Error desconocido'));
                    }
                }
            } catch (error) {
                console.error('Error creating customer:', error);
                alert('Error al crear el cliente. Por favor intente nuevamente.');
            } finally {
                this.creatingCustomer = false;
            }
        },

        // New customer modal methods (for main customer)
        openNewCustomerModal() {
            this.newCustomerModalOpen = true;
            this.newMainCustomer = {
                name: '',
                identification: '',
                phone: '',
                email: '',
                address: '',
                requiresElectronicInvoice: false,
                identificationDocumentId: '',
                dv: '',
                company: '',
                tradeName: '',
                municipalityId: '',
                legalOrganizationId: '',
                tributeId: ''
            };
            this.newMainCustomerErrors = {
                name: null,
                identification: null,
                phone: null,
                email: null,
                identification_document_id: null,
                company: null,
                municipality_id: null
            };
            this.mainCustomerIdentificationMessage = '';
            this.mainCustomerIdentificationExists = false;
            this.mainCustomerRequiresDV = false;
            this.mainCustomerIsJuridicalPerson = false;
        },

        updateMainCustomerRequiredFields() {
            if (!this.newMainCustomer.identificationDocumentId) {
                this.mainCustomerRequiresDV = false;
                this.mainCustomerIsJuridicalPerson = false;
                this.newMainCustomer.dv = '';
                return;
            }

            // Find the select element and get the selected option
            this.$nextTick(() => {
                const select = this.$el.querySelector('select[x-model="newMainCustomer.identificationDocumentId"]');
                if (!select) return;

                const selectedOption = select.options[select.selectedIndex];
                if (selectedOption) {
                    this.mainCustomerRequiresDV = selectedOption.dataset.requiresDv === 'true';
                    this.mainCustomerIsJuridicalPerson = selectedOption.dataset.code === 'NIT';

                    if (this.mainCustomerIsJuridicalPerson) {
                        this.calculateMainCustomerDV(this.newMainCustomer.identification);
                    } else {
                        this.newMainCustomer.dv = '';
                    }
                }
            });
        },

        calculateMainCustomerDV(nit) {
            if (!nit || !this.mainCustomerIsJuridicalPerson) {
                this.newMainCustomer.dv = '';
                return;
            }

            // Algorithm for DIAN Verification Digit
            const weights = [3, 7, 13, 17, 19, 23, 29, 37, 41, 43, 47, 53, 59, 67, 71];
            let sum = 0;
            const nitStr = nit.toString().replace(/\D/g, '');

            for (let i = 0; i < nitStr.length; i++) {
                sum += parseInt(nitStr.charAt(nitStr.length - 1 - i)) * weights[i];
            }

            const remainder = sum % 11;
            this.newMainCustomer.dv = remainder < 2 ? remainder : 11 - remainder;
        },

        async checkMainCustomerIdentification() {
            if (!this.newMainCustomer.identification || this.newMainCustomer.identification.length < 5) {
                this.mainCustomerIdentificationMessage = '';
                this.mainCustomerIdentificationExists = false;
                return;
            }

            this.mainCustomerIdentificationMessage = 'Verificando...';
            this.mainCustomerIdentificationExists = false;

            try {
                const response = await fetch(`{{ route('api.customers.check-identification') }}?identification=${this.newMainCustomer.identification}`);
                if (!response.ok) throw new Error('Error en la validación');

                const data = await response.json();

                if (data.exists) {
                    this.mainCustomerIdentificationExists = true;
                    this.mainCustomerIdentificationMessage = `Este cliente ya está registrado como: ${data.name}`;
                } else {
                    this.mainCustomerIdentificationExists = false;
                    this.mainCustomerIdentificationMessage = 'Documento disponible';
                }
            } catch (error) {
                console.error('Error checking identification:', error);
                this.mainCustomerIdentificationMessage = 'No se pudo verificar el documento';
                this.mainCustomerIdentificationExists = false;
            }
        },

        validateMainCustomerField(field) {
            this.newMainCustomerErrors[field] = null;

            if (field === 'name') {
                const value = this.newMainCustomer.name?.trim() || '';
                if (!value) {
                    this.newMainCustomerErrors.name = 'El nombre es obligatorio';
                } else if (value.length > 255) {
                    this.newMainCustomerErrors.name = 'El nombre no puede exceder 255 caracteres';
                } else if (!/^[a-zA-ZáéíóúÁÉÍÓÚñÑüÜ\s]+$/.test(value)) {
                    this.newMainCustomerErrors.name = 'El nombre solo puede contener letras y espacios';
                }
            }

            if (field === 'identification') {
                const value = this.newMainCustomer.identification?.trim() || '';
                if (!value) {
                    this.newMainCustomerErrors.identification = 'El documento de identidad es obligatorio';
                } else if (value.length < 5) {
                    this.newMainCustomerErrors.identification = 'El documento debe tener al menos 5 números';
                } else if (value.length > 11) {
                    this.newMainCustomerErrors.identification = 'El documento no puede exceder 11 números';
                } else if (!/^\d+$/.test(value)) {
                    this.newMainCustomerErrors.identification = 'El documento solo puede contener números';
                }
            }

            if (field === 'phone') {
                const value = this.newMainCustomer.phone?.trim() || '';
                if (!value) {
                    this.newMainCustomerErrors.phone = 'El teléfono es obligatorio';
                } else if (value.length < 7) {
                    this.newMainCustomerErrors.phone = 'El teléfono debe tener al menos 7 números';
                } else if (value.length > 10) {
                    this.newMainCustomerErrors.phone = 'El teléfono no puede exceder 10 números';
                } else if (!/^\d+$/.test(value)) {
                    this.newMainCustomerErrors.phone = 'El teléfono solo puede contener números';
                }
            }

            if (field === 'email') {
                const value = this.newMainCustomer.email?.trim() || '';
                if (value) {
                    if (value.length > 255) {
                        this.newMainCustomerErrors.email = 'El email no puede exceder 255 caracteres';
                    } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value)) {
                        this.newMainCustomerErrors.email = 'El formato del email no es válido';
                    }
                }
            }

            // DIAN validations
            if (this.newMainCustomer.requiresElectronicInvoice) {
                if (field === 'identification_document_id') {
                    if (!this.newMainCustomer.identificationDocumentId) {
                        this.newMainCustomerErrors.identification_document_id = 'El tipo de documento es obligatorio para facturación electrónica';
                    } else {
                        // Clear error if field is valid
                        this.newMainCustomerErrors.identification_document_id = null;
                    }
                }
                if (field === 'company') {
                    if (this.mainCustomerIsJuridicalPerson && !this.newMainCustomer.company?.trim()) {
                        this.newMainCustomerErrors.company = 'La razón social es obligatoria para NIT';
                    } else if (this.newMainCustomer.company && this.newMainCustomer.company.length > 255) {
                        this.newMainCustomerErrors.company = 'La razón social no puede exceder 255 caracteres';
                    } else {
                        this.newMainCustomerErrors.company = null;
                    }
                }
                if (field === 'municipality_id') {
                    if (!this.newMainCustomer.municipalityId) {
                        this.newMainCustomerErrors.municipality_id = 'El municipio es obligatorio para facturación electrónica';
                    } else {
                        this.newMainCustomerErrors.municipality_id = null;
                    }
                }
                if (field === 'dv' && this.mainCustomerRequiresDV && !this.newMainCustomer.dv) {
                    // DV is calculated automatically, but we validate it exists
                    if (!this.newMainCustomer.dv && this.mainCustomerIsJuridicalPerson && this.newMainCustomer.identification) {
                        this.calculateMainCustomerDV(this.newMainCustomer.identification);
                    }
                }
            }
        },

        validateCustomerField(field) {
            this.newCustomerErrors[field] = null;

            if (field === 'name') {
                const value = this.newCustomer.name?.trim() || '';
                if (!value) {
                    this.newCustomerErrors.name = 'El nombre es obligatorio';
                } else if (value.length > 255) {
                    this.newCustomerErrors.name = 'El nombre no puede exceder 255 caracteres';
                } else if (!/^[a-zA-ZáéíóúÁÉÍÓÚñÑüÜ\s]+$/.test(value)) {
                    this.newCustomerErrors.name = 'El nombre solo puede contener letras y espacios';
                }
            }

            if (field === 'identification') {
                const value = this.newCustomer.identification?.trim() || '';
                if (!value) {
                    this.newCustomerErrors.identification = 'El documento de identidad es obligatorio';
                } else if (value.length < 5) {
                    this.newCustomerErrors.identification = 'El documento debe tener al menos 5 números';
                } else if (value.length > 11) {
                    this.newCustomerErrors.identification = 'El documento no puede exceder 11 números';
                } else if (!/^\d+$/.test(value)) {
                    this.newCustomerErrors.identification = 'El documento solo puede contener números';
                }
            }

            if (field === 'phone') {
                const value = this.newCustomer.phone?.trim() || '';
                if (!value) {
                    this.newCustomerErrors.phone = 'El teléfono es obligatorio';
                } else if (value.length < 7) {
                    this.newCustomerErrors.phone = 'El teléfono debe tener al menos 7 números';
                } else if (value.length > 10) {
                    this.newCustomerErrors.phone = 'El teléfono no puede exceder 10 números';
                } else if (!/^\d+$/.test(value)) {
                    this.newCustomerErrors.phone = 'El teléfono solo puede contener números';
                }
            }

            if (field === 'email') {
                const value = this.newCustomer.email?.trim() || '';
                if (value) {
                    if (value.length > 255) {
                        this.newCustomerErrors.email = 'El email no puede exceder 255 caracteres';
                    } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value)) {
                        this.newCustomerErrors.email = 'El formato del email no es válido';
                    }
                }
            }

            // DIAN validations
            if (this.newCustomer.requiresElectronicInvoice) {
                if (field === 'identification_document_id') {
                    if (!this.newCustomer.identificationDocumentId) {
                        this.newCustomerErrors.identification_document_id = 'El tipo de documento es obligatorio para facturación electrónica';
                    } else {
                        // Clear error if field is valid
                        this.newCustomerErrors.identification_document_id = null;
                    }
                }
                if (field === 'company') {
                    if (this.customerIsJuridicalPerson && !this.newCustomer.company?.trim()) {
                        this.newCustomerErrors.company = 'La razón social es obligatoria para NIT';
                    } else if (this.newCustomer.company && this.newCustomer.company.length > 255) {
                        this.newCustomerErrors.company = 'La razón social no puede exceder 255 caracteres';
                    } else {
                        this.newCustomerErrors.company = null;
                    }
                }
                if (field === 'municipality_id') {
                    if (!this.newCustomer.municipalityId) {
                        this.newCustomerErrors.municipality_id = 'El municipio es obligatorio para facturación electrónica';
                    } else {
                        this.newCustomerErrors.municipality_id = null;
                    }
                }
                if (field === 'dv' && this.customerRequiresDV && !this.newCustomer.dv) {
                    // DV is calculated automatically, but we validate it exists
                    if (!this.newCustomer.dv && this.customerIsJuridicalPerson && this.newCustomer.identification) {
                        this.calculateCustomerDV(this.newCustomer.identification);
                    }
                }
            }
        },

        updateCustomerRequiredFields() {
            if (!this.newCustomer.identificationDocumentId) {
                this.customerRequiresDV = false;
                this.customerIsJuridicalPerson = false;
                this.newCustomer.dv = '';
                return;
            }

            // Find the select element and get the selected option
            this.$nextTick(() => {
                const select = this.$el.querySelector('select[x-model="newCustomer.identificationDocumentId"]');
                if (!select) return;

                const selectedOption = select.options[select.selectedIndex];
                if (selectedOption) {
                    this.customerRequiresDV = selectedOption.dataset.requiresDv === 'true';
                    this.customerIsJuridicalPerson = selectedOption.dataset.code === 'NIT';

                    if (this.customerIsJuridicalPerson) {
                        this.calculateCustomerDV(this.newCustomer.identification);
                    } else {
                        this.newCustomer.dv = '';
                    }
                }
            });
        },

        calculateCustomerDV(nit) {
            if (!nit || !this.customerIsJuridicalPerson) {
                this.newCustomer.dv = '';
                return;
            }

            // Algorithm for DIAN Verification Digit
            const weights = [3, 7, 13, 17, 19, 23, 29, 37, 41, 43, 47, 53, 59, 67, 71];
            let sum = 0;
            const nitStr = nit.toString().replace(/\D/g, '');

            for (let i = 0; i < nitStr.length; i++) {
                sum += parseInt(nitStr.charAt(nitStr.length - 1 - i)) * weights[i];
            }

            const remainder = sum % 11;
            this.newCustomer.dv = remainder < 2 ? remainder : 11 - remainder;
        },

        async checkCustomerIdentification() {
            if (!this.newCustomer.identification || this.newCustomer.identification.length < 5) {
                this.customerIdentificationMessage = '';
                this.customerIdentificationExists = false;
                return;
            }

            this.customerIdentificationMessage = 'Verificando...';
            this.customerIdentificationExists = false;

            try {
                const response = await fetch(`{{ route('api.customers.check-identification') }}?identification=${this.newCustomer.identification}`);
                if (!response.ok) throw new Error('Error en la validación');

                const data = await response.json();

                if (data.exists) {
                    this.customerIdentificationExists = true;
                    this.customerIdentificationMessage = `Este cliente ya está registrado como: ${data.name}`;
                } else {
                    this.customerIdentificationExists = false;
                    this.customerIdentificationMessage = 'Documento disponible';
                }
            } catch (error) {
                console.error('Error checking identification:', error);
                this.customerIdentificationMessage = 'No se pudo verificar el documento';
                this.customerIdentificationExists = false;
            }
        },

        onlyNumbers(event) {
            const char = String.fromCharCode(event.which);
            if (!/[0-9]/.test(char)) {
                event.preventDefault();
            }
        },

        isMainCustomerFormValid() {
            const basicValid = this.newMainCustomer.name?.trim() &&
                   this.newMainCustomer.identification?.trim() &&
                   this.newMainCustomer.phone?.trim() &&
                   !this.newMainCustomerErrors.name &&
                   !this.newMainCustomerErrors.identification &&
                   !this.newMainCustomerErrors.phone &&
                   (!this.newMainCustomer.email || !this.newMainCustomerErrors.email) &&
                   !this.mainCustomerIdentificationExists;

            if (!this.newMainCustomer.requiresElectronicInvoice) {
                return basicValid;
            }

            // DIAN validations
            const dianValid = this.newMainCustomer.identificationDocumentId &&
                   !this.newMainCustomerErrors.identification_document_id &&
                   this.newMainCustomer.municipalityId &&
                   !this.newMainCustomerErrors.municipality_id;

            if (this.mainCustomerIsJuridicalPerson) {
                return basicValid && dianValid &&
                       this.newMainCustomer.company?.trim() &&
                       !this.newMainCustomerErrors.company;
            }

            return basicValid && dianValid;
        },

        isCustomerFormValid() {
            const basicValid = this.newCustomer.name?.trim() &&
                   this.newCustomer.identification?.trim() &&
                   this.newCustomer.phone?.trim() &&
                   !this.newCustomerErrors.name &&
                   !this.newCustomerErrors.identification &&
                   !this.newCustomerErrors.phone &&
                   (!this.newCustomer.email || !this.newCustomerErrors.email) &&
                   !this.customerIdentificationExists;

            if (!this.newCustomer.requiresElectronicInvoice) {
                return basicValid;
            }

            // DIAN validations
            const dianValid = this.newCustomer.identificationDocumentId &&
                   !this.newCustomerErrors.identification_document_id &&
                   this.newCustomer.municipalityId &&
                   !this.newCustomerErrors.municipality_id;

            if (this.customerIsJuridicalPerson) {
                return basicValid && dianValid &&
                       this.newCustomer.company?.trim() &&
                       !this.newCustomerErrors.company;
            }

            return basicValid && dianValid;
        },

        async createAndSelectMainCustomer() {
            // Validate all fields before submitting
            this.validateMainCustomerField('name');
            this.validateMainCustomerField('identification');
            this.validateMainCustomerField('phone');
            if (this.newMainCustomer.email) {
                this.validateMainCustomerField('email');
            }

            if (this.newMainCustomer.requiresElectronicInvoice) {
                this.validateMainCustomerField('identification_document_id');
                this.validateMainCustomerField('municipality_id');
                if (this.mainCustomerIsJuridicalPerson) {
                    this.validateMainCustomerField('company');
                }
            }

            if (!this.isMainCustomerFormValid()) {
                if (this.mainCustomerIdentificationExists) {
                    alert('Este documento de identidad ya está registrado. Por favor verifique los datos.');
                }
                return;
            }

            this.creatingMainCustomer = true;
            try {
                const requestData = {
                    name: this.newMainCustomer.name,
                    identification: this.newMainCustomer.identification,
                    phone: this.newMainCustomer.phone,
                    email: this.newMainCustomer.email || null,
                    address: this.newMainCustomer.address || null,
                    is_active: true,
                    requires_electronic_invoice: this.newMainCustomer.requiresElectronicInvoice || false
                };

                if (this.newMainCustomer.requiresElectronicInvoice) {
                    requestData.identification_document_id = this.newMainCustomer.identificationDocumentId;
                    requestData.municipality_id = this.newMainCustomer.municipalityId;
                    if (this.newMainCustomer.dv) {
                        requestData.dv = this.newMainCustomer.dv;
                    }
                    if (this.newMainCustomer.company) {
                        requestData.company = this.newMainCustomer.company;
                    }
                    if (this.newMainCustomer.tradeName) {
                        requestData.trade_name = this.newMainCustomer.tradeName;
                    }
                    if (this.newMainCustomer.legalOrganizationId) {
                        requestData.legal_organization_id = this.newMainCustomer.legalOrganizationId;
                    }
                    if (this.newMainCustomer.tributeId) {
                        requestData.tribute_id = this.newMainCustomer.tributeId;
                    }
                } else {
                    // Default to CC for non-electronic invoice customers
                    requestData.identification_document_id = 3;
                }

                const response = await fetch('{{ route("customers.store") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(requestData)
                });

                const data = await response.json();

                if (data.success && data.customer) {
                    // Add the new customer to the selector
                    if (this.customerSelect) {
                        this.customerSelect.addOption({
                            id: data.customer.id,
                            name: data.customer.name,
                            identification: data.customer.tax_profile?.identification || this.newMainCustomer.identification,
                            phone: this.newMainCustomer.phone || 'S/N'
                        });
                        // Select the newly created customer
                        this.customerSelect.setValue(data.customer.id);
                        this.customerId = data.customer.id;
                    }
                    // Close modal and reset form
                    this.openNewCustomerModal();
                    this.newCustomerModalOpen = false;
                } else {
                    const errors = data.errors || {};

                    // Map backend errors to frontend error fields
                    if (errors.name) {
                        this.newMainCustomerErrors.name = Array.isArray(errors.name) ? errors.name[0] : errors.name;
                    }
                    if (errors.identification) {
                        this.newMainCustomerErrors.identification = Array.isArray(errors.identification) ? errors.identification[0] : errors.identification;
                    }
                    if (errors.phone) {
                        this.newMainCustomerErrors.phone = Array.isArray(errors.phone) ? errors.phone[0] : errors.phone;
                    }
                    if (errors.email) {
                        this.newMainCustomerErrors.email = Array.isArray(errors.email) ? errors.email[0] : errors.email;
                    }
                    if (errors.identification_document_id) {
                        this.newMainCustomerErrors.identification_document_id = Array.isArray(errors.identification_document_id) ? errors.identification_document_id[0] : errors.identification_document_id;
                    }
                    if (errors.company) {
                        this.newMainCustomerErrors.company = Array.isArray(errors.company) ? errors.company[0] : errors.company;
                    }
                    if (errors.municipality_id) {
                        this.newMainCustomerErrors.municipality_id = Array.isArray(errors.municipality_id) ? errors.municipality_id[0] : errors.municipality_id;
                    }

                    // If there are other errors not mapped, show alert
                    const unmappedErrors = Object.keys(errors).filter(key => !['name', 'identification', 'phone', 'email', 'identification_document_id', 'company', 'municipality_id'].includes(key));
                    if (unmappedErrors.length > 0 || (!errors.name && !errors.identification && !errors.phone && !errors.email)) {
                        const errorMessages = Object.values(errors).flat().join('\n');
                        alert('Error al crear el cliente: ' + (errorMessages || data.message || 'Error desconocido'));
                    }
                }
            } catch (error) {
                console.error('Error creating customer:', error);
                alert('Error al crear el cliente. Por favor intente nuevamente.');
            } finally {
                this.creatingMainCustomer = false;
            }
        }
    }
}
</script>
@endpush
@endsection
