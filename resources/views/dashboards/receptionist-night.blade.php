@extends('layouts.app')

@section('title', 'Dashboard Recepcionista Noche')
@section('header', 'Panel de Recepción - Turno Noche')

@section('content')
<div class="space-y-6">
    <!-- Estado del Turno -->
    <div class="bg-white rounded-xl border border-gray-100 p-6 shadow-sm">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div class="flex items-center gap-4">
                <div class="p-3 {{ $activeShift ? 'bg-indigo-50 text-indigo-600' : 'bg-gray-50 text-gray-400' }} rounded-xl">
                    <i class="fas fa-moon text-xl"></i>
                </div>
                <div>
                    <h3 class="font-bold text-gray-900">Estado del Turno: 
                        <span class="{{ $activeShift ? 'text-indigo-600' : 'text-gray-500' }}">
                            {{ $activeShift ? 'ACTIVO' : 'INACTIVO' }}
                        </span>
                    </h3>
                    @if($activeShift)
                        <p class="text-xs text-gray-500">
                            Iniciado el {{ $activeShift->started_at->format('d/m/Y H:i') }} ({{ $activeShift->started_at->diffForHumans() }})
                        </p>
                    @else
                        <p class="text-xs text-gray-500">No tienes un turno activo en este momento.</p>
                    @endif
                </div>
            </div>

            <div class="flex flex-wrap gap-2">
                @if(!$activeShift && !$pendingReception)
                    <button onclick="document.getElementById('modalStartShift').classList.remove('hidden')" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg text-sm font-bold transition-colors">
                        <i class="fas fa-play mr-2"></i> Iniciar Turno
                    </button>
                @endif

                @if($pendingReception)
                    <div class="flex items-center gap-2 p-2 bg-amber-50 text-amber-700 border border-amber-100 rounded-lg">
                        <i class="fas fa-exclamation-triangle"></i>
                        <span class="text-xs font-bold">Tienes un turno pendiente de recibir</span>
                        <a href="{{ route('shift-handovers.receive') }}" class="bg-amber-600 hover:bg-amber-700 text-white px-3 py-1 rounded-md text-xs font-bold transition-colors">
                            Recibir Caja
                        </a>
                    </div>
                @endif

                @if($activeShift)
                    <button onclick="document.getElementById('modalEndShift').classList.remove('hidden')" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-bold transition-colors">
                        <i class="fas fa-hand-holding-usd mr-2"></i> Entregar Turno
                    </button>
                @endif
            </div>
        </div>
    </div>

    @if($activeShift)
    <!-- Resumen Operativo del Turno -->
    <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-5 gap-4">
        <div class="bg-white rounded-xl border border-gray-100 p-6 shadow-sm">
            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">Base en Caja</p>
            <p class="text-2xl font-bold text-gray-900">${{ number_format($activeShift->base_inicial, 2) }}</p>
        </div>
        <div class="bg-white rounded-xl border border-gray-100 p-6 shadow-sm">
            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">Ventas Efectivo</p>
            <p class="text-2xl font-bold text-emerald-600">${{ number_format($activeShift->total_entradas_efectivo, 2) }}</p>
        </div>
        <div class="bg-white rounded-xl border border-gray-100 p-6 shadow-sm">
            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">Ventas Transferencia</p>
            <p class="text-2xl font-bold text-blue-600">${{ number_format($activeShift->total_entradas_transferencia, 2) }}</p>
        </div>
        <div class="bg-white rounded-xl border border-gray-100 p-6 shadow-sm">
            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">Total Salidas</p>
            <p class="text-2xl font-bold text-red-600">${{ number_format($activeShift->total_salidas, 2) }}</p>
        </div>
        <div class="bg-white rounded-xl border border-gray-100 p-6 shadow-sm">
            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">Base Esperada</p>
            <p class="text-2xl font-bold text-indigo-600">${{ number_format($activeShift->base_esperada, 2) }}</p>
        </div>
    </div>
    @endif

    <!-- Alertas Importantes -->
    @if(count($alerts) > 0)
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        @foreach($alerts as $alert)
            <div class="flex items-start gap-3 p-4 rounded-xl border {{ $alert['type'] === 'warning' ? 'bg-amber-50 border-amber-100 text-amber-800' : ($alert['type'] === 'danger' ? 'bg-red-50 border-red-100 text-red-800' : 'bg-indigo-50 border-indigo-100 text-indigo-800') }}">
                <div class="mt-0.5">
                    @if($alert['type'] === 'warning') <i class="fas fa-exclamation-triangle"></i>
                    @elseif($alert['type'] === 'danger') <i class="fas fa-exclamation-circle"></i>
                    @else <i class="fas fa-info-circle"></i> @endif
                </div>
                <div>
                    <h4 class="font-bold text-sm">{{ $alert['title'] }}</h4>
                    <p class="text-xs opacity-90 mb-2">{{ $alert['message'] }}</p>
                    <a href="{{ $alert['link'] }}" class="text-xs font-bold underline hover:opacity-75">
                        {{ $alert['link_text'] }}
                    </a>
                </div>
            </div>
        @endforeach
    </div>
    @endif

    <!-- Habitaciones -->
    <div class="bg-white rounded-xl border border-gray-100 p-6 shadow-sm">
        <h3 class="font-bold text-gray-900 mb-4 uppercase text-xs tracking-wider">Estado de Habitaciones</h3>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-center">
            <div class="p-4 rounded-xl bg-emerald-50 border border-emerald-100">
                <p class="text-2xl font-bold text-emerald-600">{{ $roomsSummary['available'] }}</p>
                <p class="text-xs text-emerald-700 font-medium">Libres</p>
            </div>
            <div class="p-4 rounded-xl bg-blue-50 border border-blue-100">
                <p class="text-2xl font-bold text-blue-600">{{ $roomsSummary['occupied'] }}</p>
                <p class="text-xs text-blue-700 font-medium">Ocupadas</p>
            </div>
            <div class="p-4 rounded-xl bg-red-50 border border-red-100">
                <p class="text-2xl font-bold text-red-600">{{ $roomsSummary['dirty'] }}</p>
                <p class="text-xs text-red-700 font-medium">Sucias</p>
            </div>
            <div class="p-4 rounded-xl bg-amber-50 border border-amber-100">
                <p class="text-2xl font-bold text-amber-600">{{ $roomsSummary['cleaning'] }}</p>
                <p class="text-xs text-amber-700 font-medium">En Limpieza</p>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Acciones Rápidas -->
        <div class="bg-white rounded-xl border border-gray-100 p-6 shadow-sm">
            <h3 class="font-bold text-gray-900 mb-4 uppercase text-xs tracking-wider">Acciones Rápidas</h3>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                @can('create_sales')
                <a href="{{ route('sales.create') }}" class="flex flex-col items-center p-4 rounded-xl border border-emerald-100 bg-emerald-50 hover:bg-emerald-100 transition-colors">
                    <i class="fas fa-cart-plus text-emerald-600 mb-2"></i>
                    <span class="text-xs font-semibold text-emerald-700">Nueva Venta</span>
                </a>
                @endcan
                
                <a href="{{ route('rooms.index') }}" class="flex flex-col items-center p-4 rounded-xl border border-blue-100 bg-blue-50 hover:bg-blue-100 transition-colors">
                    <i class="fas fa-bed text-blue-600 mb-2"></i>
                    <span class="text-xs font-semibold text-blue-700">Habitaciones</span>
                </a>

                @can('create_customers')
                <a href="{{ route('customers.create') }}" class="flex flex-col items-center p-4 rounded-xl border border-amber-100 bg-amber-50 hover:bg-amber-100 transition-colors">
                    <i class="fas fa-user-plus text-amber-600 mb-2"></i>
                    <span class="text-xs font-semibold text-amber-700">Nuevo Cliente</span>
                </a>
                @endcan
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-1 gap-6">
        <!-- Últimas Salidas -->
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                <h3 class="font-bold text-gray-900 uppercase text-xs tracking-wider">Últimas Salidas</h3>
                <a href="{{ route('cash-outflows.index') }}" class="text-xs text-blue-600 hover:underline">Ver todas</a>
            </div>
            <div class="p-0">
                @if($lastOutflows->isEmpty())
                    <p class="p-6 text-sm text-gray-500 text-center">No hay salidas registradas recientemente</p>
                @else
                    <table class="min-w-full divide-y divide-gray-100">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Motivo</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Monto</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-100">
                            @foreach($lastOutflows as $outflow)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ $outflow->reason }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-right font-bold text-red-600">
                                        ${{ number_format($outflow->amount, 2) }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Modal Iniciar Turno -->
<div id="modalStartShift" class="hidden fixed inset-0 bg-gray-900 bg-opacity-50 overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-xl bg-white">
        <div class="mt-3">
            <h3 class="text-lg leading-6 font-bold text-gray-900 text-center mb-4">Iniciar Turno Noche</h3>
            <form action="{{ route('shift.start') }}" method="POST">
                @csrf
                <input type="hidden" name="shift_type" value="noche">
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Base Inicial en Caja ($)</label>
                    <input type="text" name="base_inicial" oninput="formatNumberInput(this)" class="w-full px-3 py-2 border rounded-lg focus:ring-indigo-500 focus:border-indigo-500" placeholder="0" required>
                </div>
                <div class="flex gap-3">
                    <button type="button" onclick="document.getElementById('modalStartShift').classList.add('hidden')" class="flex-1 px-4 py-2 bg-gray-100 text-gray-700 rounded-lg text-sm font-bold hover:bg-gray-200 transition-colors">
                        Cancelar
                    </button>
                    <button type="submit" class="flex-1 px-4 py-2 bg-indigo-600 text-white rounded-lg text-sm font-bold hover:bg-indigo-700 transition-colors">
                        Iniciar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Entregar Turno -->
<div id="modalEndShift" class="hidden fixed inset-0 bg-gray-900 bg-opacity-50 overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-xl bg-white">
        <div class="mt-3">
            <h3 class="text-lg leading-6 font-bold text-gray-900 text-center mb-4">Entregar Turno</h3>
            <form action="{{ route('shift.end') }}" method="POST">
                @csrf
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Base Final en Caja ($)</label>
                    <input type="text" name="base_final" value="{{ $activeShift ? number_format($activeShift->base_esperada, 0, ',', '.') : '' }}" oninput="formatNumberInput(this)" class="w-full px-3 py-2 border rounded-lg focus:ring-blue-500 focus:border-blue-500" required>
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Observaciones</label>
                    <textarea name="observaciones" class="w-full px-3 py-2 border rounded-lg focus:ring-blue-500 focus:border-blue-500" placeholder="Opcional..."></textarea>
                </div>
                <div class="flex gap-3">
                    <button type="button" onclick="document.getElementById('modalEndShift').classList.add('hidden')" class="flex-1 px-4 py-2 bg-gray-100 text-gray-700 rounded-lg text-sm font-bold hover:bg-gray-200 transition-colors">
                        Cancelar
                    </button>
                    <button type="submit" class="flex-1 px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-bold hover:bg-blue-700 transition-colors">
                        Entregar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

