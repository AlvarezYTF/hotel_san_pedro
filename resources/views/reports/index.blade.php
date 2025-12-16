@extends('layouts.app')

@section('title', 'Reportes')
@section('header', 'Centro de Reportes')

@section('content')
<div class="space-y-4 sm:space-y-6">
    <!-- Header -->
    <div class="bg-white rounded-xl border border-gray-100 p-4 sm:p-6">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div class="flex items-center space-x-3 sm:space-x-4">
                <div class="p-2.5 sm:p-3 rounded-xl bg-violet-50 text-violet-600">
                    <i class="fas fa-chart-pie text-lg sm:text-xl"></i>
                </div>
                <div>
                    <h1 class="text-xl sm:text-2xl font-bold text-gray-900">Centro de Reportes</h1>
                    <p class="text-xs sm:text-sm text-gray-500 mt-1">Análisis y estadísticas de tu negocio</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Reportes disponibles -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-6">
        <!-- Reporte de Ventas -->
        <div class="bg-white rounded-xl border border-gray-100 p-4 sm:p-6 hover:shadow-md transition-shadow duration-200">
            <div class="flex items-center justify-between mb-4">
                <div class="h-12 w-12 sm:h-14 sm:w-14 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center shadow-sm">
                    <i class="fas fa-chart-line text-lg sm:text-xl"></i>
                </div>
                <span class="text-xs sm:text-sm text-gray-500 font-semibold uppercase tracking-wider">Ventas</span>
            </div>

            <h3 class="text-lg sm:text-xl font-semibold text-gray-900 mb-2">Reporte de Ventas</h3>
            <p class="text-sm text-gray-600 mb-4 leading-relaxed">
                Visualiza todas las ventas realizadas con filtros por fecha, cliente y producto. Incluye totales y análisis detallado.
            </p>

            <div class="flex items-center justify-between pt-4 border-t border-gray-100">
                <div class="text-xs text-gray-500 flex items-center">
                    <i class="fas fa-filter mr-1.5"></i>
                    <span>Fecha, Cliente, Producto</span>
                </div>
                <a href="{{ route('reports.sales') }}"
                   class="inline-flex items-center px-3 sm:px-4 py-2 rounded-xl border-2 border-emerald-600 bg-emerald-600 text-white text-xs sm:text-sm font-semibold hover:bg-emerald-700 hover:border-emerald-700 transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-emerald-500 shadow-sm hover:shadow-md">
                    <i class="fas fa-eye mr-2"></i>
                    Ver Reporte
                </a>
            </div>
        </div>

        <!-- Reporte de Reparaciones -->
        <div class="bg-white rounded-xl border border-gray-100 p-4 sm:p-6 hover:shadow-md transition-shadow duration-200">
            <div class="flex items-center justify-between mb-4">
                <div class="h-12 w-12 sm:h-14 sm:w-14 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center shadow-sm">
                    <i class="fas fa-tools text-lg sm:text-xl"></i>
                </div>
                <span class="text-xs sm:text-sm text-gray-500 font-semibold uppercase tracking-wider">Reparaciones</span>
            </div>

            <h3 class="text-lg sm:text-xl font-semibold text-gray-900 mb-2">Reporte de Reparaciones</h3>
            <p class="text-sm text-gray-600 mb-4 leading-relaxed">
                Analiza todas las reparaciones de teléfonos con filtros por fecha, estado y cliente. Incluye ingresos totales.
            </p>

            <div class="flex items-center justify-between pt-4 border-t border-gray-100">
                <div class="text-xs text-gray-500 flex items-center">
                    <i class="fas fa-filter mr-1.5"></i>
                    <span>Fecha, Estado, Cliente</span>
                </div>
                <a href="{{ route('reports.repairs') }}"
                   class="inline-flex items-center px-3 sm:px-4 py-2 rounded-xl border-2 border-amber-600 bg-amber-600 text-white text-xs sm:text-sm font-semibold hover:bg-amber-700 hover:border-amber-700 transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-amber-500 shadow-sm hover:shadow-md">
                    <i class="fas fa-eye mr-2"></i>
                    Ver Reporte
                </a>
            </div>
        </div>

        <!-- Estadísticas Rápidas -->
        <div class="bg-white rounded-xl border border-gray-100 p-4 sm:p-6 hover:shadow-md transition-shadow duration-200">
            <div class="flex items-center justify-between mb-4">
                <div class="h-12 w-12 sm:h-14 sm:w-14 rounded-xl bg-violet-50 text-violet-600 flex items-center justify-center shadow-sm">
                    <i class="fas fa-chart-pie text-lg sm:text-xl"></i>
                </div>
                <span class="text-xs sm:text-sm text-gray-500 font-semibold uppercase tracking-wider">Resumen</span>
            </div>

            <h3 class="text-lg sm:text-xl font-semibold text-gray-900 mb-2">Vista General</h3>
            <p class="text-sm text-gray-600 mb-4 leading-relaxed">
                Resumen rápido de las métricas más importantes de tu negocio en tiempo real.
            </p>

            <div class="space-y-3 pt-4 border-t border-gray-100">
                <div class="flex justify-between items-center">
                    <span class="text-xs sm:text-sm text-gray-600 flex items-center">
                        <i class="fas fa-shopping-cart text-emerald-600 mr-2 text-xs"></i>
                        Ventas del Mes:
                    </span>
                    <span class="text-sm sm:text-base font-bold text-emerald-600">
                        @php
                            $monthlySales = App\Models\Sale::whereMonth('sale_date', date('m'))
                                                           ->whereYear('sale_date', date('Y'))
                                                           ->sum('total');
                        @endphp
                        ${{ number_format($monthlySales, 2) }}
                    </span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-xs sm:text-sm text-gray-600 flex items-center">
                        <i class="fas fa-tools text-amber-600 mr-2 text-xs"></i>
                        Reparaciones Activas:
                    </span>
                    <span class="text-sm sm:text-base font-bold text-amber-600">
                        @php
                            $activeRepairs = App\Models\Repair::whereIn('repair_status', ['pending', 'in_progress'])->count();
                        @endphp
                        {{ $activeRepairs }}
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- Accesos rápidos -->
    <div class="bg-white rounded-xl border border-gray-100 p-4 sm:p-6">
        <div class="flex items-center space-x-2 sm:space-x-3 mb-4 sm:mb-6">
            <div class="p-2 rounded-xl bg-gray-50 text-gray-600">
                <i class="fas fa-bolt text-sm"></i>
            </div>
            <h2 class="text-base sm:text-lg font-semibold text-gray-900">Accesos Rápidos</h2>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 sm:gap-4">
            <a href="{{ route('sales.index') }}"
               class="flex items-center p-3 sm:p-4 bg-emerald-50 rounded-xl border-2 border-emerald-100 hover:bg-emerald-100 hover:border-emerald-200 transition-all duration-200 group">
                <div class="h-10 w-10 rounded-xl bg-emerald-600 text-white flex items-center justify-center mr-3 group-hover:scale-110 transition-transform duration-200">
                    <i class="fas fa-shopping-cart"></i>
                </div>
                <span class="text-sm font-semibold text-gray-700">Gestionar Ventas</span>
            </a>
            <a href="{{ route('repairs.index') }}"
               class="flex items-center p-3 sm:p-4 bg-amber-50 rounded-xl border-2 border-amber-100 hover:bg-amber-100 hover:border-amber-200 transition-all duration-200 group">
                <div class="h-10 w-10 rounded-xl bg-amber-600 text-white flex items-center justify-center mr-3 group-hover:scale-110 transition-transform duration-200">
                    <i class="fas fa-tools"></i>
                </div>
                <span class="text-sm font-semibold text-gray-700">Gestionar Reparaciones</span>
            </a>
            <a href="{{ route('products.index') }}"
               class="flex items-center p-3 sm:p-4 bg-violet-50 rounded-xl border-2 border-violet-100 hover:bg-violet-100 hover:border-violet-200 transition-all duration-200 group">
                <div class="h-10 w-10 rounded-xl bg-violet-600 text-white flex items-center justify-center mr-3 group-hover:scale-110 transition-transform duration-200">
                    <i class="fas fa-boxes"></i>
                </div>
                <span class="text-sm font-semibold text-gray-700">Inventario</span>
            </a>
        </div>
    </div>
</div>
@endsection
