@extends('layouts.app')

@section('title', 'Reporte de Reparaciones')
@section('header', 'Reporte de Reparaciones')

@section('content')
<div class="space-y-4 sm:space-y-6">
    <!-- Header -->
    <div class="bg-white rounded-xl border border-gray-100 p-4 sm:p-6">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div class="flex items-center space-x-3 sm:space-x-4">
                <div class="p-2.5 sm:p-3 rounded-xl bg-amber-50 text-amber-600">
                    <i class="fas fa-tools text-lg sm:text-xl"></i>
                </div>
                <div>
                    <h1 class="text-xl sm:text-2xl font-bold text-gray-900">Reporte de Reparaciones</h1>
                    <p class="text-xs sm:text-sm text-gray-500 mt-1">Análisis detallado de las reparaciones realizadas</p>
                </div>
            </div>
            
            <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3">
                <form method="POST" action="{{ route('reports.pdf') }}" class="inline">
                    @csrf
                    <input type="hidden" name="report_type" value="repairs">
                    <input type="hidden" name="date_from" value="{{ request('date_from') }}">
                    <input type="hidden" name="date_to" value="{{ request('date_to') }}">
                    <input type="hidden" name="repair_status" value="{{ request('repair_status') }}">
                    <input type="hidden" name="customer_id" value="{{ request('customer_id') }}">
                    <button type="submit"
                            class="w-full sm:w-auto inline-flex items-center justify-center px-4 sm:px-5 py-2.5 rounded-xl border-2 border-red-600 bg-red-600 text-white text-sm font-semibold hover:bg-red-700 hover:border-red-700 transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 shadow-sm hover:shadow-md">
                        <i class="fas fa-file-pdf mr-2"></i>
                        Exportar PDF
                    </button>
                </form>
                
                <a href="{{ route('reports.index') }}"
                   class="inline-flex items-center justify-center px-4 sm:px-5 py-2.5 rounded-xl border-2 border-gray-200 bg-white text-gray-700 text-sm font-semibold hover:bg-gray-50 hover:border-gray-300 transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500">
                    <i class="fas fa-arrow-left mr-2"></i>
                    Volver
                </a>
            </div>
        </div>
    </div>
    
    <!-- Filtros -->
    <div class="bg-white rounded-xl border border-gray-100 p-4 sm:p-6">
        <form method="GET" action="{{ route('reports.repairs') }}" class="space-y-4">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <div>
                    <label for="date_from" class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-2">
                        Fecha Desde
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fas fa-calendar-alt text-gray-400 text-sm"></i>
                        </div>
                        <input type="date" id="date_from" name="date_from" value="{{ request('date_from') }}"
                               class="block w-full pl-10 pr-3 py-2.5 border border-gray-300 rounded-xl text-sm text-gray-900 focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-transparent transition-all">
                    </div>
                </div>
                
                <div>
                    <label for="date_to" class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-2">
                        Fecha Hasta
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fas fa-calendar-alt text-gray-400 text-sm"></i>
                        </div>
                        <input type="date" id="date_to" name="date_to" value="{{ request('date_to') }}"
                               class="block w-full pl-10 pr-3 py-2.5 border border-gray-300 rounded-xl text-sm text-gray-900 focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-transparent transition-all">
                    </div>
                </div>
                
                <div>
                    <label for="repair_status" class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-2">
                        Estado
                    </label>
                    <div class="relative">
                        <select id="repair_status" name="repair_status"
                                class="block w-full pl-3 sm:pl-4 pr-10 py-2.5 border border-gray-300 rounded-xl text-sm text-gray-900 focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-transparent appearance-none bg-white">
                            <option value="">Todos los estados</option>
                            <option value="pending" {{ request('repair_status') == 'pending' ? 'selected' : '' }}>Pendiente</option>
                            <option value="in_progress" {{ request('repair_status') == 'in_progress' ? 'selected' : '' }}>En Progreso</option>
                            <option value="completed" {{ request('repair_status') == 'completed' ? 'selected' : '' }}>Completado</option>
                            <option value="delivered" {{ request('repair_status') == 'delivered' ? 'selected' : '' }}>Entregado</option>
                        </select>
                        <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                            <i class="fas fa-chevron-down text-gray-400 text-xs"></i>
                        </div>
                    </div>
                </div>
                
                <div>
                    <label for="customer_id" class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-2">
                        Cliente
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fas fa-user text-gray-400 text-sm"></i>
                        </div>
                        <select id="customer_id" name="customer_id"
                                class="block w-full pl-10 sm:pl-11 pr-10 py-2.5 border border-gray-300 rounded-xl text-sm text-gray-900 focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-transparent appearance-none bg-white">
                            <option value="">Todos los clientes</option>
                            @foreach($customers as $customer)
                                <option value="{{ $customer->id }}" {{ request('customer_id') == $customer->id ? 'selected' : '' }}>
                                    {{ $customer->name }}
                                </option>
                            @endforeach
                        </select>
                        <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                            <i class="fas fa-chevron-down text-gray-400 text-xs"></i>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="flex justify-center pt-2">
                <button type="submit"
                        class="inline-flex items-center justify-center px-4 sm:px-5 py-2.5 rounded-xl border-2 border-amber-600 bg-amber-600 text-white text-sm font-semibold hover:bg-amber-700 hover:border-amber-700 transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-amber-500 shadow-sm hover:shadow-md">
                    <i class="fas fa-filter mr-2"></i>
                    Filtrar Resultados
                </button>
            </div>
        </form>
    </div>
    
    <!-- Resumen -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 sm:gap-6">
        <div class="bg-white rounded-xl border border-gray-100 p-4 sm:p-6 hover:shadow-md transition-shadow duration-200">
            <div class="flex items-center">
                <div class="h-12 w-12 sm:h-14 sm:w-14 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center mr-4 shadow-sm flex-shrink-0">
                    <i class="fas fa-dollar-sign text-lg sm:text-xl"></i>
                </div>
                <div class="min-w-0 flex-1">
                    <p class="text-xs sm:text-sm font-semibold text-gray-500 uppercase tracking-wider mb-1">Ingresos Totales</p>
                    <p class="text-xl sm:text-2xl font-bold text-emerald-600 truncate">${{ number_format($totalRevenue, 2) }}</p>
                </div>
            </div>
        </div>
        
        <div class="bg-white rounded-xl border border-gray-100 p-4 sm:p-6 hover:shadow-md transition-shadow duration-200">
            <div class="flex items-center">
                <div class="h-12 w-12 sm:h-14 sm:w-14 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center mr-4 shadow-sm flex-shrink-0">
                    <i class="fas fa-tools text-lg sm:text-xl"></i>
                </div>
                <div class="min-w-0 flex-1">
                    <p class="text-xs sm:text-sm font-semibold text-gray-500 uppercase tracking-wider mb-1">Total Reparaciones</p>
                    <p class="text-xl sm:text-2xl font-bold text-amber-600">{{ $totalCount }}</p>
                </div>
            </div>
        </div>
        
        <div class="bg-white rounded-xl border border-gray-100 p-4 sm:p-6 hover:shadow-md transition-shadow duration-200">
            <div class="flex items-center">
                <div class="h-12 w-12 sm:h-14 sm:w-14 rounded-xl bg-violet-50 text-violet-600 flex items-center justify-center mr-4 shadow-sm flex-shrink-0">
                    <i class="fas fa-chart-line text-lg sm:text-xl"></i>
                </div>
                <div class="min-w-0 flex-1">
                    <p class="text-xs sm:text-sm font-semibold text-gray-500 uppercase tracking-wider mb-1">Precio Promedio</p>
                    <p class="text-xl sm:text-2xl font-bold text-violet-600 truncate">
                        ${{ $totalCount > 0 ? number_format($totalRevenue / $totalCount, 2) : '0.00' }}
                    </p>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Tabla de reparaciones - Desktop -->
    <div class="hidden lg:block bg-white rounded-xl border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-100">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                            #
                        </th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                            Cliente
                        </th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                            Dispositivo
                        </th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                            Fecha
                        </th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                            Estado
                        </th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                            Costo
                        </th>
                        <th class="px-6 py-4 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">
                            Acciones
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-100">
                    @forelse($repairs as $repair)
                    <tr class="hover:bg-gray-50 transition-colors duration-150">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-semibold text-gray-900">#{{ $repair->id }}</div>
                        </td>
                        
                        <td class="px-6 py-4">
                            <div class="flex items-center">
                                <div class="h-9 w-9 rounded-full bg-gradient-to-br from-emerald-500 to-emerald-600 flex items-center justify-center text-white text-xs font-semibold shadow-sm mr-3 flex-shrink-0">
                                    {{ strtoupper(substr($repair->customer->name, 0, 1)) }}
                                </div>
                                <div class="min-w-0">
                                    <div class="text-sm font-semibold text-gray-900 truncate">{{ $repair->customer->name }}</div>
                                    <div class="text-xs text-gray-500 truncate">{{ $repair->customer->phone ?? 'Sin teléfono' }}</div>
                                </div>
                            </div>
                        </td>
                        
                        <td class="px-6 py-4">
                            <div class="flex items-center">
                                <div class="p-1.5 rounded-lg bg-blue-50 text-blue-600 mr-2">
                                    <i class="fas fa-mobile-alt text-xs"></i>
                                </div>
                                <div class="min-w-0">
                                    <div class="text-sm font-semibold text-gray-900 truncate">{{ $repair->phone_model }}</div>
                                    @if($repair->imei)
                                        <div class="text-xs text-gray-500 font-mono truncate mt-0.5">{{ $repair->imei }}</div>
                                    @endif
                                </div>
                            </div>
                        </td>
                        
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-semibold text-gray-900">{{ $repair->repair_date->format('d/m/Y') }}</div>
                        </td>
                        
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($repair->repair_status === 'pending')
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-amber-50 text-amber-700">
                                    <i class="fas fa-clock mr-1.5"></i>
                                    Pendiente
                                </span>
                            @elseif($repair->repair_status === 'in_progress')
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-blue-50 text-blue-700">
                                    <i class="fas fa-tools mr-1.5"></i>
                                    En Progreso
                                </span>
                            @elseif($repair->repair_status === 'completed')
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700">
                                    <i class="fas fa-check-circle mr-1.5"></i>
                                    Completado
                                </span>
                            @elseif($repair->repair_status === 'delivered')
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-violet-50 text-violet-700">
                                    <i class="fas fa-handshake mr-1.5"></i>
                                    Entregado
                                </span>
                            @endif
                        </td>
                        
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-semibold text-gray-900">${{ number_format($repair->repair_cost, 2) }}</div>
                        </td>
                        
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <a href="{{ route('repairs.show', $repair) }}"
                               class="text-blue-600 hover:text-blue-700 transition-colors"
                               title="Ver detalles">
                                <i class="fas fa-eye"></i>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-6 py-12 text-center">
                            <div class="flex flex-col items-center">
                                <i class="fas fa-tools text-4xl text-gray-300 mb-4"></i>
                                <p class="text-base font-semibold text-gray-500 mb-1">No se encontraron reparaciones</p>
                                <p class="text-sm text-gray-400">Ajusta los filtros para ver más resultados</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    
    <!-- Cards de reparaciones - Mobile/Tablet -->
    <div class="lg:hidden space-y-4">
        @forelse($repairs as $repair)
        <div class="bg-white rounded-xl border border-gray-100 p-4 hover:shadow-md transition-shadow duration-200">
            <div class="flex items-start justify-between mb-4">
                <div class="flex items-center space-x-3 flex-1 min-w-0">
                    <div class="h-12 w-12 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center shadow-sm flex-shrink-0">
                        <i class="fas fa-tools"></i>
                    </div>
                    <div class="min-w-0 flex-1">
                        <h3 class="text-sm font-semibold text-gray-900">#{{ $repair->id }}</h3>
                        <p class="text-xs text-gray-500 mt-0.5">{{ $repair->repair_date->format('d/m/Y') }}</p>
                    </div>
                </div>
                
                <div class="flex flex-col items-end space-y-1">
                    @if($repair->repair_status === 'pending')
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-amber-50 text-amber-700">
                            <i class="fas fa-clock mr-1"></i>
                            Pendiente
                        </span>
                    @elseif($repair->repair_status === 'in_progress')
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-blue-50 text-blue-700">
                            <i class="fas fa-tools mr-1"></i>
                            En Progreso
                        </span>
                    @elseif($repair->repair_status === 'completed')
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700">
                            <i class="fas fa-check-circle mr-1"></i>
                            Completado
                        </span>
                    @elseif($repair->repair_status === 'delivered')
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-violet-50 text-violet-700">
                            <i class="fas fa-handshake mr-1"></i>
                            Entregado
                        </span>
                    @endif
                    <div class="text-sm font-bold text-gray-900">
                        ${{ number_format($repair->repair_cost, 2) }}
                    </div>
                </div>
            </div>
            
            <div class="space-y-3 mb-4">
                <!-- Cliente -->
                <div>
                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1.5">Cliente</p>
                    <div class="flex items-center space-x-2">
                        <div class="h-8 w-8 rounded-full bg-gradient-to-br from-emerald-500 to-emerald-600 flex items-center justify-center text-white text-xs font-semibold shadow-sm flex-shrink-0">
                            {{ strtoupper(substr($repair->customer->name, 0, 1)) }}
                        </div>
                        <div class="min-w-0 flex-1">
                            <div class="text-sm font-semibold text-gray-900 truncate">{{ $repair->customer->name }}</div>
                            <div class="text-xs text-gray-500 truncate">{{ $repair->customer->phone ?? 'Sin teléfono' }}</div>
                        </div>
                    </div>
                </div>
                
                <!-- Dispositivo -->
                <div>
                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1.5">Dispositivo</p>
                    <div class="flex items-center space-x-2">
                        <div class="p-1.5 rounded-lg bg-blue-50 text-blue-600">
                            <i class="fas fa-mobile-alt text-xs"></i>
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="text-sm font-semibold text-gray-900 truncate">{{ $repair->phone_model }}</div>
                            @if($repair->imei)
                                <div class="text-xs text-gray-500 font-mono truncate">{{ $repair->imei }}</div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Acciones -->
            <div class="flex items-center justify-end pt-3 border-t border-gray-100">
                <a href="{{ route('repairs.show', $repair) }}"
                   class="p-2 text-blue-600 hover:bg-blue-50 rounded-lg transition-colors"
                   title="Ver">
                    <i class="fas fa-eye text-sm"></i>
                </a>
            </div>
        </div>
        @empty
        <div class="bg-white rounded-xl border border-gray-100 p-12 text-center">
            <i class="fas fa-tools text-4xl text-gray-300 mb-4"></i>
            <p class="text-base font-semibold text-gray-500 mb-1">No se encontraron reparaciones</p>
            <p class="text-sm text-gray-400">Ajusta los filtros para ver más resultados</p>
        </div>
        @endforelse
    </div>
</div>
@endsection
