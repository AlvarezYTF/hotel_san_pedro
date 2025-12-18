@extends('layouts.app')

@section('title', 'Reservas')
@section('header', 'Gestión de Reservas')

@section('content')
<div class="space-y-4 sm:space-y-6">
    <!-- Header -->
    <div class="bg-white rounded-xl border border-gray-100 p-4 sm:p-6">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div class="flex items-center space-x-3 sm:space-x-4">
                <div class="p-2.5 sm:p-3 rounded-xl bg-emerald-50 text-emerald-600">
                    <i class="fas fa-calendar-check text-lg sm:text-xl"></i>
                </div>
                <div>
                    <h1 class="text-xl sm:text-2xl font-bold text-gray-900">Gestión de Reservas</h1>
                    <div class="flex items-center space-x-2 mt-1">
                        <span class="text-xs sm:text-sm text-gray-500">
                            <span class="font-semibold text-gray-900">{{ $reservations->total() }}</span> reservas registradas
                        </span>
                    </div>
                </div>
            </div>
            
            <a href="{{ route('reservations.create') }}"
               class="inline-flex items-center justify-center px-4 sm:px-5 py-2.5 rounded-xl border-2 border-emerald-600 bg-emerald-600 text-white text-sm font-semibold hover:bg-emerald-700 hover:border-emerald-700 transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-emerald-500 shadow-sm hover:shadow-md">
                <i class="fas fa-plus mr-2"></i>
                <span>Nueva Reserva</span>
            </a>
        </div>
    </div>
    
    <!-- Tabla de reservas - Desktop -->
    <div class="hidden lg:block bg-white rounded-xl border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto -mx-6 lg:mx-0">
            <table class="min-w-full divide-y divide-gray-100">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Cliente</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Habitación</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Entrada / Salida</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Total / Abono</th>
                        <th class="px-6 py-4 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">Acciones</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-100">
                    @forelse($reservations as $reservation)
                    <tr class="hover:bg-gray-50 transition-colors duration-150">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="h-10 w-10 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center text-sm font-semibold">
                                    {{ strtoupper(substr($reservation->customer->name, 0, 1)) }}
                                </div>
                                <div class="ml-3">
                                    <div class="text-sm font-semibold text-gray-900">{{ $reservation->customer->name }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                            <span class="font-semibold">{{ $reservation->room->room_number }}</span>
                            <span class="text-xs text-gray-500 block">{{ $reservation->room->room_type }}</span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                            <div><i class="fas fa-sign-in-alt text-emerald-500 mr-2"></i>{{ $reservation->check_in_date->format('d/m/Y') }}</div>
                            <div><i class="fas fa-sign-out-alt text-red-500 mr-2"></i>{{ $reservation->check_out_date->format('d/m/Y') }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                            <div class="flex flex-col space-y-1 min-w-[120px]">
                                <div class="flex items-center justify-between">
                                    <span class="text-gray-500 text-xs uppercase font-bold tracking-wider">Total:</span>
                                    <span class="font-bold text-gray-900">${{ number_format($reservation->total_amount, 0, ',', '.') }}</span>
                                </div>
                                <div class="flex items-center justify-between text-xs">
                                    <span class="text-gray-400">Abono:</span>
                                    <span class="text-emerald-600 font-semibold">${{ number_format($reservation->deposit, 0, ',', '.') }}</span>
                                </div>
                                <div class="pt-1 mt-1 border-t border-gray-100 flex items-center justify-between">
                                    <span class="text-gray-500 text-[10px] uppercase font-bold">Saldo:</span>
                                    @php
                                        $balance = $reservation->total_amount - $reservation->deposit;
                                    @endphp
                                    @if($balance > 0)
                                        <span class="text-xs text-red-600 font-bold bg-red-50 px-1.5 py-0.5 rounded">${{ number_format($balance, 0, ',', '.') }}</span>
                                    @else
                                        <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-700 uppercase">
                                            <i class="fas fa-check-circle mr-1"></i> Pagado
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <div class="flex items-center justify-end space-x-2">
                                <a href="{{ route('reservations.download', $reservation) }}"
                                   class="p-2 text-red-600 hover:bg-red-50 rounded-lg transition-colors"
                                   title="Descargar PDF">
                                    <i class="fas fa-file-pdf"></i>
                                </a>
                                <a href="{{ route('reservations.edit', $reservation) }}"
                                   class="p-2 text-indigo-600 hover:bg-indigo-50 rounded-lg transition-colors"
                                   title="Editar">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <button type="button"
                                        onclick="openDeleteModal({{ $reservation->id }})"
                                        class="p-2 text-red-600 hover:bg-red-50 rounded-lg transition-colors"
                                        title="Eliminar">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-6 py-16 text-center text-gray-500">No hay reservas registradas.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        @if($reservations->hasPages())
        <div class="bg-white px-6 py-4 border-t border-gray-100">
            {{ $reservations->links() }}
        </div>
        @endif
    </div>
</div>

<!-- Modal de Eliminación -->
<div id="delete-modal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-xl rounded-xl bg-white">
        <div class="mt-3 text-center">
            <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-red-100 mb-4">
                <i class="fas fa-exclamation-triangle text-red-600"></i>
            </div>
            <h3 class="text-lg leading-6 font-medium text-gray-900">Eliminar Reserva</h3>
            <div class="mt-2 px-7 py-3">
                <p class="text-sm text-gray-500">¿Estás seguro de eliminar esta reserva? Esta acción no se puede deshacer.</p>
            </div>
            <div class="items-center px-4 py-3">
                <form id="delete-form" method="POST">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="px-4 py-2 bg-red-600 text-white text-base font-medium rounded-lg w-full shadow-sm hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-300">Eliminar</button>
                    <button type="button" onclick="closeDeleteModal()" class="mt-3 px-4 py-2 bg-white text-gray-700 text-base font-medium rounded-lg w-full border border-gray-300 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-gray-300">Cancelar</button>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function openDeleteModal(id) {
    const modal = document.getElementById('delete-modal');
    const form = document.getElementById('delete-form');
    form.action = '{{ route("reservations.destroy", ":id") }}'.replace(':id', id);
    modal.classList.remove('hidden');
}

function closeDeleteModal() {
    document.getElementById('delete-modal').classList.add('hidden');
}
</script>
@endpush
@endsection

