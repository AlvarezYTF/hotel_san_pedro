@extends('layouts.app')

@section('title', 'Dashboard')
@section('header', 'Dashboard')

@section('content')
<div class="space-y-5 sm:space-y-8">
    <!-- Estadísticas principales -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-5">
        <!-- Total Productos -->
        <div class="bg-white rounded-xl border border-gray-100 p-4 sm:p-6 hover:shadow-md transition-all duration-300 group">
            <div class="flex items-start justify-between">
                <div class="flex-1">
                    <p class="text-xs font-medium text-gray-500 uppercase tracking-wider mb-1">Total Productos</p>
                    <p class="text-2xl sm:text-3xl font-bold text-gray-900 mb-2">{{ $stats['total_products'] }}</p>
                </div>
                <div class="p-2 sm:p-3 rounded-xl bg-blue-50 text-blue-600 group-hover:bg-blue-100 transition-colors duration-300">
                    <i class="fas fa-boxes text-lg sm:text-xl"></i>
                </div>
            </div>
        </div>
        
        <!-- Total Ventas -->
        <div class="bg-white rounded-xl border border-gray-100 p-4 sm:p-6 hover:shadow-md transition-all duration-300 group">
            <div class="flex items-start justify-between">
                <div class="flex-1">
                    <p class="text-xs font-medium text-gray-500 uppercase tracking-wider mb-1">Total Ventas</p>
                    <p class="text-2xl sm:text-3xl font-bold text-gray-900 mb-2">{{ $stats['total_sales'] }}</p>
                </div>
                <div class="p-2 sm:p-3 rounded-xl bg-emerald-50 text-emerald-600 group-hover:bg-emerald-100 transition-colors duration-300">
                    <i class="fas fa-shopping-cart text-lg sm:text-xl"></i>
                </div>
            </div>
        </div>
        
        <!-- Reparaciones Pendientes -->
        <div class="bg-white rounded-xl border border-gray-100 p-4 sm:p-6 hover:shadow-md transition-all duration-300 group">
            <div class="flex items-start justify-between">
                <div class="flex-1">
                    <p class="text-xs font-medium text-gray-500 uppercase tracking-wider mb-1">Reparaciones Pendientes</p>
                    <p class="text-2xl sm:text-3xl font-bold text-gray-900 mb-2">{{ $stats['pending_repairs'] }}</p>
                </div>
                <div class="p-2 sm:p-3 rounded-xl bg-amber-50 text-amber-600 group-hover:bg-amber-100 transition-colors duration-300">
                    <i class="fas fa-tools text-lg sm:text-xl"></i>
                </div>
            </div>
        </div>
        
        <!-- Total Clientes -->
        <div class="bg-white rounded-xl border border-gray-100 p-4 sm:p-6 hover:shadow-md transition-all duration-300 group">
            <div class="flex items-start justify-between">
                <div class="flex-1">
                    <p class="text-xs font-medium text-gray-500 uppercase tracking-wider mb-1">Total Clientes</p>
                    <p class="text-2xl sm:text-3xl font-bold text-gray-900 mb-2">{{ $stats['total_customers'] }}</p>
                </div>
                <div class="p-2 sm:p-3 rounded-xl bg-violet-50 text-violet-600 group-hover:bg-violet-100 transition-colors duration-300">
                    <i class="fas fa-users text-lg sm:text-xl"></i>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Información adicional -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 sm:gap-5">
        <!-- Ventas del mes -->
        <div class="bg-white rounded-xl border border-gray-100 p-5 sm:p-8">
            <div class="flex items-center justify-between mb-4 sm:mb-6">
                <h3 class="text-xs sm:text-sm font-semibold text-gray-900 uppercase tracking-wider">Ventas del Mes</h3>
                <div class="p-2 rounded-lg bg-emerald-50 text-emerald-600">
                    <i class="fas fa-dollar-sign text-xs sm:text-sm"></i>
                </div>
            </div>
            <div>
                <p class="text-3xl sm:text-4xl font-bold text-gray-900 mb-2">${{ number_format($monthlySales, 2) }}</p>
                <p class="text-xs text-gray-500">Ingresos del mes actual</p>
            </div>
        </div>
        
        <!-- Productos con bajo stock -->
        <div class="bg-white rounded-xl border border-gray-100 p-5 sm:p-8">
            <div class="flex items-center justify-between mb-4 sm:mb-6">
                <h3 class="text-xs sm:text-sm font-semibold text-gray-900 uppercase tracking-wider">Productos con Bajo Stock</h3>
                <div class="p-2 rounded-lg bg-red-50 text-red-600">
                    <i class="fas fa-exclamation-triangle text-xs sm:text-sm"></i>
                </div>
            </div>
            <div>
                <p class="text-3xl sm:text-4xl font-bold text-gray-900 mb-2">{{ $stats['low_stock_products'] }}</p>
                <p class="text-xs text-gray-500">Productos con menos de 10 unidades</p>
            </div>
        </div>
    </div>
    
    <!-- Gráficos y tablas -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 sm:gap-5">
        <!-- Productos más vendidos -->
        <div class="bg-white rounded-xl border border-gray-100 p-5 sm:p-6">
            <h3 class="text-xs sm:text-sm font-semibold text-gray-900 uppercase tracking-wider mb-4 sm:mb-6">Productos Más Vendidos</h3>
            @if($topProducts->count() > 0)
                <div class="space-y-4">
                    @foreach($topProducts as $index => $product)
                    <div class="flex items-center justify-between py-2 border-b border-gray-50 last:border-0">
                        <div class="flex items-center space-x-3">
                            <span class="flex items-center justify-center w-6 h-6 rounded-full bg-gray-100 text-gray-600 text-xs font-medium">
                                {{ $index + 1 }}
                            </span>
                            <span class="text-sm text-gray-700">{{ $product->name }}</span>
                        </div>
                        <span class="text-sm font-semibold text-gray-900">{{ $product->total_sold }}</span>
                    </div>
                    @endforeach
                </div>
            @else
                <div class="flex flex-col items-center justify-center py-12 text-gray-400">
                    <i class="fas fa-chart-line text-3xl mb-3 opacity-50"></i>
                    <p class="text-sm">No hay datos de ventas disponibles</p>
                </div>
            @endif
        </div>
        
        <!-- Estado de reparaciones -->
        <div class="bg-white rounded-xl border border-gray-100 p-5 sm:p-6">
            <h3 class="text-xs sm:text-sm font-semibold text-gray-900 uppercase tracking-wider mb-4 sm:mb-6">Estado de Reparaciones</h3>
            @if($repairStatuses->count() > 0)
                <div class="space-y-4">
                    @foreach($repairStatuses as $status)
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-600 capitalize">{{ str_replace('_', ' ', $status->repair_status) }}</span>
                        <span class="text-sm font-medium text-gray-900">{{ $status->total }}</span>
                    </div>
                    @endforeach
                </div>
            @else
                <div class="flex flex-col items-center justify-center py-12 text-gray-400">
                    <i class="fas fa-wrench text-3xl mb-3 opacity-50"></i>
                    <p class="text-sm">No hay reparaciones registradas</p>
                </div>
            @endif
        </div>
    </div>
    
    <!-- Acciones rápidas -->
    <div class="bg-white rounded-xl border border-gray-100 p-5 sm:p-6">
        <h3 class="text-xs sm:text-sm font-semibold text-gray-900 uppercase tracking-wider mb-4 sm:mb-6">Acciones Rápidas</h3>
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 sm:gap-4">
            @can('create_products')
            <a href="{{ route('products.create') }}" class="group flex flex-col items-center p-4 sm:p-6 rounded-xl border-2 border-gray-100 hover:border-blue-200 hover:bg-blue-50 transition-all duration-300">
                <div class="p-2 sm:p-3 rounded-xl bg-blue-100 text-blue-600 mb-2 sm:mb-3 group-hover:bg-blue-600 group-hover:text-white transition-colors duration-300">
                    <i class="fas fa-plus text-lg sm:text-xl"></i>
                </div>
                <span class="text-xs sm:text-sm font-medium text-gray-700 group-hover:text-gray-900 text-center">Nuevo Producto</span>
            </a>
            @endcan
            
            @can('create_sales')
            <a href="{{ route('sales.create') }}" class="group flex flex-col items-center p-4 sm:p-6 rounded-xl border-2 border-gray-100 hover:border-emerald-200 hover:bg-emerald-50 transition-all duration-300">
                <div class="p-2 sm:p-3 rounded-xl bg-emerald-100 text-emerald-600 mb-2 sm:mb-3 group-hover:bg-emerald-600 group-hover:text-white transition-colors duration-300">
                    <i class="fas fa-shopping-cart text-lg sm:text-xl"></i>
                </div>
                <span class="text-xs sm:text-sm font-medium text-gray-700 group-hover:text-gray-900 text-center">Nueva Venta</span>
            </a>
            @endcan
            
            @can('create_repairs')
            <a href="{{ route('repairs.create') }}" class="group flex flex-col items-center p-4 sm:p-6 rounded-xl border-2 border-gray-100 hover:border-amber-200 hover:bg-amber-50 transition-all duration-300">
                <div class="p-2 sm:p-3 rounded-xl bg-amber-100 text-amber-600 mb-2 sm:mb-3 group-hover:bg-amber-600 group-hover:text-white transition-colors duration-300">
                    <i class="fas fa-tools text-lg sm:text-xl"></i>
                </div>
                <span class="text-xs sm:text-sm font-medium text-gray-700 group-hover:text-gray-900 text-center">Nueva Reparación</span>
            </a>
            @endcan
            
            @can('view_reports')
            <a href="{{ route('reports.index') }}" class="group flex flex-col items-center p-4 sm:p-6 rounded-xl border-2 border-gray-100 hover:border-violet-200 hover:bg-violet-50 transition-all duration-300">
                <div class="p-2 sm:p-3 rounded-xl bg-violet-100 text-violet-600 mb-2 sm:mb-3 group-hover:bg-violet-600 group-hover:text-white transition-colors duration-300">
                    <i class="fas fa-chart-bar text-lg sm:text-xl"></i>
                </div>
                <span class="text-xs sm:text-sm font-medium text-gray-700 group-hover:text-gray-900 text-center">Ver Reportes</span>
            </a>
            @endcan
        </div>
    </div>
</div>
@endsection
