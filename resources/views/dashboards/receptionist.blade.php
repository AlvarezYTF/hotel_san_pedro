@extends('layouts.app')

@section('title', 'Dashboard Recepción')
@section('header', 'Panel de Recepción')

@section('content')
<div class="space-y-6">
    <!-- Estadísticas Operativas -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="bg-white rounded-xl border border-gray-100 p-6 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Llegadas Hoy</p>
                    <p class="text-2xl font-bold text-emerald-600">{{ $stats['arrivals_today'] }}</p>
                </div>
                <div class="p-3 bg-emerald-50 text-emerald-600 rounded-xl">
                    <i class="fas fa-sign-in-alt"></i>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-xl border border-gray-100 p-6 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Salidas Hoy</p>
                    <p class="text-2xl font-bold text-amber-600">{{ $stats['departures_today'] }}</p>
                </div>
                <div class="p-3 bg-amber-50 text-amber-600 rounded-xl">
                    <i class="fas fa-sign-out-alt"></i>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-xl border border-gray-100 p-6 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Habitaciones Ocupadas</p>
                    <p class="text-2xl font-bold text-blue-600">{{ $stats['active_reservations'] }}</p>
                </div>
                <div class="p-3 bg-blue-50 text-blue-600 rounded-xl">
                    <i class="fas fa-bed"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Próximas Llegadas -->
        <div class="bg-white rounded-xl border border-gray-100 overflow-hidden shadow-sm">
            <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                <h3 class="font-bold text-gray-900">Próximas Llegadas</h3>
                <a href="{{ route('reservations.index') }}" class="text-xs text-emerald-600 hover:underline">Ver todas</a>
            </div>
            <div class="p-0">
                @if($upcomingArrivals->isEmpty())
                    <p class="p-6 text-sm text-gray-500 text-center">No hay llegadas programadas</p>
                @else
                    <table class="min-w-full divide-y divide-gray-100">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Cliente</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Hab</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Fecha</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-100">
                            @foreach($upcomingArrivals as $reservation)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                        {{ $reservation->customer->name }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $reservation->room->room_number }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-xs text-gray-500">
                                        {{ $reservation->check_in_date->format('d M') }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif
            </div>
        </div>

        <!-- Acciones Rápidas Recepción -->
        <div class="bg-white rounded-xl border border-gray-100 p-6 shadow-sm">
            <h3 class="font-bold text-gray-900 mb-4">Acciones Rápidas</h3>
            <div class="grid grid-cols-2 gap-4">
                <a href="{{ route('reservations.create') }}" class="flex flex-col items-center p-4 rounded-xl border border-emerald-100 bg-emerald-50 hover:bg-emerald-100 transition-colors">
                    <i class="fas fa-calendar-plus text-emerald-600 mb-2"></i>
                    <span class="text-xs font-semibold text-emerald-700">Nueva Reserva</span>
                </a>
                <a href="{{ route('customers.create') }}" class="flex flex-col items-center p-4 rounded-xl border border-blue-100 bg-blue-50 hover:bg-blue-100 transition-colors">
                    <i class="fas fa-user-plus text-blue-600 mb-2"></i>
                    <span class="text-xs font-semibold text-blue-700">Registrar Cliente</span>
                </a>
            </div>
        </div>
    </div>
</div>
@endsection

