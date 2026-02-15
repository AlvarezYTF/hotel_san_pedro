@extends('layouts.app')

@section('title', 'Dashboard Recepcionista Día')
@section('header', 'Panel de Recepción - Turno Día')

@section('content')
<div class="space-y-6">
    @php
        $uiLocked = !$activeShift;
        $isAdmin = $user->hasRole('Administrador');
        $hasGlobalActiveShift = isset($globalActiveShift) && $globalActiveShift && (!$activeShift || (int) $globalActiveShift->id !== (int) $activeShift->id);
        $hasGlobalPendingHandover = isset($globalPendingHandover) && $globalPendingHandover;
        $canStartShift = !$activeShift && !$pendingReception && !$hasGlobalActiveShift && !$hasGlobalPendingHandover && ($shiftsOperationalEnabled || $isAdmin);
    @endphp

    <!-- Estado del Turno -->
    <div class="bg-white rounded-xl border border-gray-100 p-6 shadow-sm">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div class="flex items-center gap-4">
                <div class="p-3 {{ $activeShift ? 'bg-emerald-50 text-emerald-600' : 'bg-gray-50 text-gray-400' }} rounded-xl">
                    <i class="fas fa-clock text-xl"></i>
                </div>
                <div>
                    <h3 class="font-bold text-gray-900">Estado del Turno: 
                        <span class="{{ $activeShift ? 'text-emerald-600' : ($pendingReception ? 'text-amber-600' : (($hasGlobalActiveShift || $hasGlobalPendingHandover) ? 'text-blue-600' : 'text-gray-500')) }}">
                            {{ $activeShift ? 'ACTIVO' : ($pendingReception ? 'PENDIENTE POR RECIBIR' : (($hasGlobalActiveShift || $hasGlobalPendingHandover) ? 'EN ESPERA DE CADENA' : 'INACTIVO')) }}
                        </span>
                    </h3>
                    @if($activeShift)
                        <p class="text-xs text-gray-500">
                            Iniciado el {{ $activeShift->started_at->format('d/m/Y H:i') }} ({{ $activeShift->started_at->diffForHumans() }})
                        </p>
                    @elseif($hasGlobalActiveShift)
                        <p class="text-xs text-blue-700 font-semibold">
                            Hay un turno activo en curso por {{ $globalActiveShift->receptionist_display_name ?? ($globalActiveShift->entregadoPor->name ?? 'otro recepcionista') }}.
                        </p>
                    @else
                        @if($pendingReception)
                            <p class="text-xs text-amber-700 font-semibold">Tienes un turno pendiente de recibir.</p>
                        @elseif($hasGlobalPendingHandover)
                            <p class="text-xs text-blue-700 font-semibold">Hay un turno entregado pendiente de recibir en el sistema.</p>
                        @else
                            <p class="text-xs text-gray-500">No hay turnos activos en este momento.</p>
                        @endif
                    @endif
                </div>
            </div>

            <div class="flex flex-wrap gap-2">
                @if($canStartShift)
                    <button onclick="document.getElementById('modalStartShift').classList.remove('hidden')" class="bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-2 rounded-lg text-sm font-bold transition-colors">
                        <i class="fas fa-play mr-2"></i> Iniciar Turno
                    </button>
                @elseif(!$activeShift && !$pendingReception && $hasGlobalActiveShift)
                    <span class="inline-flex items-center px-3 py-2 rounded-lg bg-blue-50 text-blue-700 border border-blue-200 text-xs font-bold">
                        <i class="fas fa-user-clock mr-2"></i> Turno activo por {{ $globalActiveShift->receptionist_display_name ?? ($globalActiveShift->entregadoPor->name ?? 'otro recepcionista') }}
                    </span>
                @elseif(!$activeShift && !$pendingReception && !$hasGlobalActiveShift && $hasGlobalPendingHandover)
                    <span class="inline-flex items-center px-3 py-2 rounded-lg bg-amber-50 text-amber-700 border border-amber-200 text-xs font-bold">
                        <i class="fas fa-hourglass-half mr-2"></i> Hay un turno pendiente por recibir
                    </span>
                @elseif(!$activeShift && !$pendingReception && !$shiftsOperationalEnabled && !$isAdmin)
                    <span class="inline-flex items-center px-3 py-2 rounded-lg bg-red-50 text-red-700 border border-red-200 text-xs font-bold">
                        <i class="fas fa-ban mr-2"></i> Apertura bloqueada por administración
                    </span>
                @endif

                @if($pendingReception)
                    <div class="flex items-center gap-2 p-2 bg-amber-50 text-amber-700 border border-amber-100 rounded-lg">
                        <i class="fas fa-exclamation-triangle"></i>
                        <span class="text-xs font-bold">Tienes un turno pendiente de recibir</span>
                        <a href="{{ route('shift-handovers.receive') }}" class="bg-amber-600 hover:bg-amber-700 text-white px-3 py-1 rounded md text-xs font-bold transition-colors">
                            Recibir Caja
                        </a>
                    </div>
                @endif

                @if($activeShift)
                    <a href="{{ route('shift-handovers.deliver') }}" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-bold transition-colors">
                        <i class="fas fa-hand-holding-usd mr-2"></i> Entregar Turno
                    </a>
                @endif
            </div>
        </div>

        @if($user->hasRole('Administrador') && $operationalShift)
            <div class="mt-4 flex flex-col md:flex-row md:items-center md:justify-between gap-3 rounded-lg border border-blue-100 bg-blue-50 p-3">
                <div class="text-xs text-blue-800">
                    Turno operativo abierto: <span class="font-bold">{{ strtoupper($operationalShift->type->value) }}</span>
                    @if($operationalShift->opened_at)
                        (desde {{ $operationalShift->opened_at->format('d/m H:i') }})
                    @endif
                    @if($operationalShift->openedBy)
                        - iniciado por {{ $operationalShift->openedBy->name }}
                    @endif
                </div>
                <div class="flex items-center gap-2">
                    <button onclick="document.getElementById('modalForceClose').classList.remove('hidden')" class="text-xs font-bold bg-blue-600 text-white px-3 py-2 rounded-lg hover:bg-blue-700 transition-colors">
                        <i class="fas fa-bolt mr-1"></i> Romper cadena de turnos
                    </button>
                </div>
            </div>
        @endif
    </div>

    @if(!$shiftsOperationalEnabled && !$isAdmin && !$activeShift && !$pendingReception)
    <div class="bg-red-50 border border-red-200 rounded-xl p-4 flex items-start gap-3">
        <div class="text-red-600"><i class="fas fa-ban text-xl"></i></div>
        <div>
            <p class="text-sm font-bold text-red-800">La administracion desactivo temporalmente la apertura de turnos.</p>
            <p class="text-xs text-red-700">Solicita habilitacion desde el panel de administrador para iniciar turno.</p>
        </div>
    </div>
    @endif

    @if($uiLocked)
    <div class="bg-amber-50 border border-amber-200 rounded-xl p-4 flex flex-col sm:flex-row sm:items-start gap-3">
        <div class="text-amber-600"><i class="fas fa-lock text-xl"></i></div>
        <div>
            <p class="text-sm font-bold text-amber-800">El panel está bloqueado hasta que recibas o inicies un turno.</p>
            <p class="text-xs text-amber-700">No podrás registrar ventas, reservas ni salidas de dinero sin un turno activo.</p>
            <div class="flex flex-wrap gap-2 mt-3">
                @if($pendingReception)
                    <a href="{{ route('shift-handovers.receive') }}" class="bg-amber-600 hover:bg-amber-700 text-white px-3 py-2 rounded-lg text-xs font-bold transition-colors">Recibir turno pendiente</a>
                @endif
                @if($canStartShift)
                    <button onclick="document.getElementById('modalStartShift').classList.remove('hidden')" class="bg-emerald-600 hover:bg-emerald-700 text-white px-3 py-2 rounded-lg text-xs font-bold transition-colors">Iniciar turno</button>
                @elseif(!$pendingReception && $hasGlobalActiveShift)
                    <span class="inline-flex items-center px-3 py-2 rounded-lg bg-blue-100 text-blue-700 border border-blue-200 text-xs font-bold">
                        <i class="fas fa-user-clock mr-2"></i> Turno activo en curso
                    </span>
                @elseif(!$pendingReception && !$hasGlobalActiveShift && $hasGlobalPendingHandover)
                    <span class="inline-flex items-center px-3 py-2 rounded-lg bg-amber-100 text-amber-700 border border-amber-200 text-xs font-bold">
                        <i class="fas fa-hourglass-half mr-2"></i> Hay entrega pendiente
                    </span>
                @elseif(!$pendingReception && !$shiftsOperationalEnabled && !$isAdmin)
                    <span class="inline-flex items-center px-3 py-2 rounded-lg bg-red-100 text-red-700 border border-red-200 text-xs font-bold">
                        <i class="fas fa-ban mr-2"></i> Apertura bloqueada
                    </span>
                @endif
                @if($user->hasRole('Administrador') && $operationalShift)
                    <button onclick="document.getElementById('modalForceClose').classList.remove('hidden')" class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-2 rounded-lg text-xs font-bold transition-colors">Forzar cierre operativo</button>
                @endif
            </div>
        </div>
    </div>
    @endif

    <div class="{{ $uiLocked ? 'pointer-events-none select-none opacity-50' : '' }}">
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
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mt-4">
            @foreach($alerts as $alert)
                <div class="flex items-start gap-3 p-4 rounded-xl border {{ $alert['type'] === 'warning' ? 'bg-amber-50 border-amber-100 text-amber-800' : ($alert['type'] === 'danger' ? 'bg-red-50 border-red-100 text-red-800' : 'bg-blue-50 border-blue-100 text-blue-800') }}">
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
        <div class="bg-white rounded-xl border border-gray-100 p-6 shadow-sm mt-6">
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

        <div class="space-y-6 mt-6">
            @include('dashboards.partials.shift-tables')
        </div>
    </div>
</div>

<!-- Modal Iniciar Turno -->
<div id="modalStartShift" class="hidden fixed inset-0 bg-gray-900 bg-opacity-50 overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-xl bg-white">
        <div class="mt-3">
            <h3 class="text-lg leading-6 font-bold text-gray-900 text-center mb-4">Iniciar Turno Día</h3>
            <form action="{{ route('shift.start') }}" method="POST">
                @csrf
                <input type="hidden" name="shift_type" value="dia">
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nombre del Recepcionista</label>
                    <input type="text" name="receptionist_name" value="{{ old('receptionist_name', auth()->user()->name ?? '') }}" class="w-full px-3 py-2 border rounded-lg focus:ring-emerald-500 focus:border-emerald-500" maxlength="120" required>
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Base Inicial en Caja ($)</label>
                    <input type="text" name="base_inicial" value="{{ old('base_inicial') }}" oninput="formatNumberInput(this)" class="w-full px-3 py-2 border rounded-lg focus:ring-emerald-500 focus:border-emerald-500" placeholder="0" required>
                </div>
                <div class="flex gap-3">
                    <button type="button" onclick="document.getElementById('modalStartShift').classList.add('hidden')" class="flex-1 px-4 py-2 bg-gray-100 text-gray-700 rounded-lg text-sm font-bold hover:bg-gray-200 transition-colors">
                        Cancelar
                    </button>
                    <button type="submit" class="flex-1 px-4 py-2 bg-emerald-600 text-white rounded-lg text-sm font-bold hover:bg-emerald-700 transition-colors">
                        Iniciar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Forzar cierre operativo (Admin) -->
@if($user->hasRole('Administrador'))
<div id="modalForceClose" class="hidden fixed inset-0 bg-gray-900 bg-opacity-50 overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-xl bg-white">
        <div class="mt-3">
            <h3 class="text-lg leading-6 font-bold text-gray-900 text-center mb-4">Forzar cierre operativo</h3>
            <form action="{{ route('shift.force-close') }}" method="POST">
                @csrf
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Motivo</label>
                    <textarea name="reason" rows="3" class="w-full px-3 py-2 border rounded-lg focus:ring-blue-500 focus:border-blue-500" placeholder="Ej: Turno atascado o sin recepción" required>Romper cadena de turnos desde panel</textarea>
                </div>
                <div class="flex gap-3">
                    <button type="button" onclick="document.getElementById('modalForceClose').classList.add('hidden')" class="flex-1 px-4 py-2 bg-gray-100 text-gray-700 rounded-lg text-sm font-bold hover:bg-gray-200 transition-colors">
                        Cancelar
                    </button>
                    <button type="submit" class="flex-1 px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-bold hover:bg-blue-700 transition-colors">
                        Forzar cierre
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif
@endsection
