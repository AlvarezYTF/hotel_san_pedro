@extends('layouts.app')

@section('title', 'Ventas por Habitación')
@section('header', 'Ventas por Habitación')

@section('content')
<div class="space-y-4 sm:space-y-6">
    <!-- Header -->
    <div class="bg-white rounded-xl border border-gray-100 p-4 sm:p-6">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div class="flex items-center space-x-3 sm:space-x-4">
                <div class="p-2.5 sm:p-3 rounded-xl bg-blue-50 text-blue-600">
                    <i class="fas fa-bed text-lg sm:text-xl"></i>
                </div>
                <div>
                    <h1 class="text-xl sm:text-2xl font-bold text-gray-900">Ventas por Habitación</h1>
                    <p class="text-xs sm:text-sm text-gray-500 mt-1">Ventas agrupadas por habitación y categoría</p>
                </div>
            </div>
            
            <div class="flex gap-2">
                <a href="{{ route('sales.index') }}" 
                   class="inline-flex items-center justify-center px-4 sm:px-5 py-2.5 rounded-xl border-2 border-gray-200 bg-white text-gray-700 text-sm font-semibold hover:bg-gray-50 hover:border-gray-300 transition-all duration-200">
                    <i class="fas fa-arrow-left mr-2"></i>
                    <span>Volver</span>
                </a>
            </div>
        </div>
    </div>

    <!-- Filtros -->
    <div class="bg-white rounded-xl border border-gray-100 p-4 sm:p-6">
        <form method="GET" action="{{ route('sales.byRoom') }}" class="space-y-4">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <div>
                    <label for="date" class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-2">
                        Fecha
                    </label>
                    <input type="date" id="date" name="date" value="{{ request('date') }}" 
                           class="block w-full px-3 py-2.5 border border-gray-300 rounded-xl text-sm text-gray-900 focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent">
                </div>
                
                <div>
                    <label for="room_id" class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-2">
                        Habitación
                    </label>
                    <select id="room_id" name="room_id"
                            class="block w-full pl-3 pr-10 py-2.5 border border-gray-300 rounded-xl text-sm text-gray-900 focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent appearance-none bg-white">
                        <option value="">Todas</option>
                        @foreach($rooms as $room)
                            <option value="{{ $room->id }}" {{ request('room_id') == $room->id ? 'selected' : '' }}>
                                Habitación {{ $room->room_number }}
                            </option>
                        @endforeach
                    </select>
                </div>
                
                <div>
                    <label for="category_id" class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-2">
                        Categoría
                    </label>
                    <select id="category_id" name="category_id"
                            class="block w-full pl-3 pr-10 py-2.5 border border-gray-300 rounded-xl text-sm text-gray-900 focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent appearance-none bg-white">
                        <option value="">Todas</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" {{ request('category_id') == $category->id ? 'selected' : '' }}>
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                
                <div>
                    <label for="shift" class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-2">
                        Turno
                    </label>
                    <select id="shift" name="shift"
                            class="block w-full pl-3 pr-10 py-2.5 border border-gray-300 rounded-xl text-sm text-gray-900 focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent appearance-none bg-white">
                        <option value="">Todos</option>
                        <option value="dia" {{ request('shift') == 'dia' ? 'selected' : '' }}>Día</option>
                        <option value="noche" {{ request('shift') == 'noche' ? 'selected' : '' }}>Noche</option>
                    </select>
                </div>
            </div>
            
            <div class="flex gap-2">
                <button type="submit"
                        class="inline-flex items-center justify-center px-4 py-2.5 rounded-xl border-2 border-gray-200 bg-white text-gray-700 text-sm font-semibold hover:bg-gray-50 hover:border-gray-300 transition-all duration-200">
                    <i class="fas fa-filter mr-2"></i>
                    Filtrar
                </button>
                <a href="{{ route('sales.byRoom') }}"
                   class="inline-flex items-center justify-center px-4 py-2.5 rounded-xl border-2 border-gray-200 bg-white text-gray-700 text-sm font-semibold hover:bg-gray-50 hover:border-gray-300 transition-all duration-200">
                    <i class="fas fa-times mr-2"></i>
                    Limpiar
                </a>
            </div>
        </form>
    </div>

    <!-- Ventas por Habitación -->
    @if($roomsData->count() > 0)
        <div class="space-y-4">
            @foreach($roomsData as $roomData)
                <div class="bg-white rounded-xl border border-gray-100 p-4 sm:p-6">
                    <!-- Header de Habitación -->
                    <div class="flex items-center justify-between mb-4">
                        <div class="flex items-center space-x-3">
                            <div class="p-3 rounded-lg bg-blue-50 text-blue-600">
                                <i class="fas fa-bed text-xl"></i>
                            </div>
                            <div>
                                <h2 class="text-lg font-bold text-gray-900">
                                    Habitación {{ $roomData['room']->room_number }}
                                </h2>
                                @if($roomData['customer'])
                                    <p class="text-sm text-gray-600">
                                        Titular: <span class="font-semibold">{{ $roomData['customer']->name }}</span>
                                    </p>
                                @endif
                            </div>
                        </div>
                        <div class="text-right">
                            <p class="text-xs text-gray-500 uppercase">Total</p>
                            <p class="text-2xl font-bold text-green-600">
                                ${{ number_format($roomData['total'], 2, ',', '.') }}
                            </p>
                        </div>
                    </div>

                    <!-- Resumen por Categoría -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
                        @foreach($roomData['byCategory'] as $categoryName => $data)
                            <div class="p-3 rounded-lg border {{ $categoryName === 'Bebidas' ? 'bg-blue-50 border-blue-200' : 'bg-amber-50 border-amber-200' }}">
                                <div class="flex items-center justify-between">
                                    <span class="text-sm font-semibold {{ $categoryName === 'Bebidas' ? 'text-blue-700' : 'text-amber-700' }}">
                                        {{ $categoryName }}
                                    </span>
                                    <div class="text-right">
                                        <p class="text-xs text-gray-600">{{ $data['count'] }} producto(s)</p>
                                        <p class="text-lg font-bold {{ $categoryName === 'Bebidas' ? 'text-blue-900' : 'text-amber-900' }}">
                                            ${{ number_format($data['total'], 2, ',', '.') }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <!-- Detalle de Ventas -->
                    <div class="mt-4 pt-4 border-t border-gray-200">
                        <h3 class="text-sm font-semibold text-gray-700 mb-3">Detalle de Ventas</h3>
                        <div class="space-y-2">
                            @foreach($roomData['sales'] as $sale)
                                <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                                    <div class="flex-1">
                                        <div class="flex items-center space-x-2">
                                            <span class="text-sm font-medium text-gray-900">
                                                {{ $sale->sale_date->format('d/m/Y') }}
                                            </span>
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium {{ $sale->shift === 'dia' ? 'bg-yellow-100 text-yellow-800' : 'bg-indigo-100 text-indigo-800' }}">
                                                {{ ucfirst($sale->shift) }}
                                            </span>
                                            <span class="text-xs text-gray-500">
                                                {{ $sale->user->name }}
                                            </span>
                                        </div>
                                        <div class="mt-1 flex flex-wrap gap-1">
                                            @foreach($sale->items->groupBy('product.category.name') as $catName => $items)
                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium 
                                                    {{ $catName === 'Bebidas' ? 'bg-blue-100 text-blue-800' : 'bg-amber-100 text-amber-800' }}">
                                                    {{ $catName ?? 'Sin categoría' }} ({{ $items->count() }})
                                                </span>
                                            @endforeach
                                        </div>
                                    </div>
                                    <div class="text-right">
                                        <p class="text-sm font-semibold text-gray-900">
                                            ${{ number_format($sale->total, 2, ',', '.') }}
                                        </p>
                                        <p class="text-xs {{ $sale->debt_status === 'pendiente' ? 'text-red-600' : 'text-green-600' }}">
                                            {{ $sale->debt_status === 'pendiente' ? 'Pendiente' : 'Pagado' }}
                                        </p>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div class="bg-white rounded-xl border border-gray-100 p-12 text-center">
            <i class="fas fa-bed text-4xl text-gray-300 mb-4"></i>
            <p class="text-gray-500 text-sm">No se encontraron ventas con los filtros aplicados.</p>
        </div>
    @endif
</div>
@endsection

