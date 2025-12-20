@extends('layouts.app')

@section('title', 'Detalle de Venta')
@section('header', 'Detalle de Venta')

@section('content')
<div class="max-w-4xl mx-auto space-y-4 sm:space-y-6">
    <!-- Header -->
    <div class="bg-white rounded-xl border border-gray-100 p-4 sm:p-6">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div class="flex items-center space-x-3 sm:space-x-4">
                <div class="p-2.5 sm:p-3 rounded-xl bg-green-50 text-green-600">
                    <i class="fas fa-receipt text-lg sm:text-xl"></i>
                </div>
                <div>
                    <h1 class="text-xl sm:text-2xl font-bold text-gray-900">Detalle de Venta</h1>
                    <p class="text-xs sm:text-sm text-gray-500 mt-1">
                        Venta #{{ $sale->id }} - {{ $sale->sale_date->format('d/m/Y') }}
                    </p>
                </div>
            </div>
            
            <div class="flex gap-2">
                @can('edit_sales')
                <a href="{{ route('sales.edit', $sale) }}" 
                   class="inline-flex items-center justify-center px-4 py-2.5 rounded-xl border-2 border-indigo-600 bg-indigo-600 text-white text-sm font-semibold hover:bg-indigo-700 transition-all">
                    <i class="fas fa-edit mr-2"></i>
                    Editar
                </a>
                @endcan
                <a href="{{ route('sales.index') }}" 
                   class="inline-flex items-center justify-center px-4 py-2.5 rounded-xl border-2 border-gray-200 bg-white text-gray-700 text-sm font-semibold hover:bg-gray-50 transition-all">
                    <i class="fas fa-arrow-left mr-2"></i>
                    Volver
                </a>
            </div>
        </div>
    </div>

    <!-- Información General -->
    <div class="bg-white rounded-xl border border-gray-100 p-4 sm:p-6">
        <h2 class="text-base sm:text-lg font-semibold text-gray-900 mb-4">Información General</h2>
        
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label class="block text-xs font-semibold text-gray-500 uppercase mb-1">Fecha</label>
                <p class="text-sm font-medium text-gray-900">{{ $sale->sale_date->format('d/m/Y') }}</p>
            </div>
            
            <div>
                <label class="block text-xs font-semibold text-gray-500 uppercase mb-1">Recepcionista</label>
                <p class="text-sm font-medium text-gray-900">{{ $sale->user->name }}</p>
            </div>
            
            <div>
                <label class="block text-xs font-semibold text-gray-500 uppercase mb-1">Habitación</label>
                <p class="text-sm font-medium text-gray-900">
                    @if($sale->room)
                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                            Habitación {{ $sale->room->room_number }}
                        </span>
                    @else
                        <span class="text-gray-500">Venta Normal</span>
                    @endif
                </p>
            </div>
            
            <div>
                <label class="block text-xs font-semibold text-gray-500 uppercase mb-1">Turno</label>
                <p class="text-sm font-medium text-gray-900">
                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium {{ $sale->shift === 'dia' ? 'bg-yellow-100 text-yellow-800' : 'bg-indigo-100 text-indigo-800' }}">
                        {{ ucfirst($sale->shift) }}
                    </span>
                </p>
            </div>
            
            <div class="sm:col-span-2">
                <label class="block text-xs font-semibold text-gray-500 uppercase mb-1">Método de Pago</label>
                <div class="space-y-2">
                    @if($sale->payment_method === 'ambos')
                        <div>
                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                                Ambos
                            </span>
                        </div>
                        <div class="text-sm text-gray-700 space-y-1">
                            <div>
                                <span class="font-semibold">Efectivo:</span> 
                                ${{ number_format($sale->cash_amount ?? 0, 2, ',', '.') }}
                            </div>
                            <div>
                                <span class="font-semibold">Transferencia:</span> 
                                ${{ number_format($sale->transfer_amount ?? 0, 2, ',', '.') }}
                            </div>
                        </div>
                    @elseif($sale->payment_method === 'pendiente')
                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-orange-100 text-orange-800">
                            Pendiente
                        </span>
                    @else
                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium {{ $sale->payment_method === 'efectivo' ? 'bg-green-100 text-green-800' : 'bg-blue-100 text-blue-800' }}">
                            {{ ucfirst($sale->payment_method) }}
                        </span>
                    @endif
                </div>
            </div>
            
            <div>
                <label class="block text-xs font-semibold text-gray-500 uppercase mb-1">Estado</label>
                <p class="text-sm font-medium text-gray-900">
                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                        Pagado
                    </span>
                </p>
            </div>
        </div>
        
        @if($sale->notes)
            <div class="mt-4 pt-4 border-t border-gray-200">
                <label class="block text-xs font-semibold text-gray-500 uppercase mb-1">Notas</label>
                <p class="text-sm text-gray-700">{{ $sale->notes }}</p>
            </div>
        @endif
    </div>

    <!-- Productos -->
    <div class="bg-white rounded-xl border border-gray-100 p-4 sm:p-6">
        <h2 class="text-base sm:text-lg font-semibold text-gray-900 mb-4">Productos Vendidos</h2>
        
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase">Producto</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase">Cantidad</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase">Precio Unitario</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold text-gray-700 uppercase">Total</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($sale->items as $item)
                        <tr>
                            <td class="px-4 py-3 text-sm text-gray-900">
                                {{ $item->product->name }}
                                @if($item->product->category)
                                    <span class="text-xs text-gray-500">({{ $item->product->category->name }})</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-900">{{ $item->quantity }}</td>
                            <td class="px-4 py-3 text-sm text-gray-900">${{ number_format($item->unit_price, 2, ',', '.') }}</td>
                            <td class="px-4 py-3 text-sm font-semibold text-gray-900 text-right">
                                ${{ number_format($item->total, 2, ',', '.') }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot class="bg-gray-50">
                    <tr>
                        <td colspan="3" class="px-4 py-3 text-right text-sm font-semibold text-gray-900">
                            Total:
                        </td>
                        <td class="px-4 py-3 text-right text-lg font-bold text-green-600">
                            ${{ number_format($sale->total, 2, ',', '.') }}
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    @if($sale->room_id)
    <!-- Resumen de Deuda y Abonos -->
    <div class="bg-white rounded-xl border border-gray-100 p-4 sm:p-6">
        <div class="flex items-center space-x-2 mb-4 sm:mb-6">
            <div class="p-2 rounded-lg bg-orange-50 text-orange-600">
                <i class="fas fa-calculator text-sm"></i>
            </div>
            <h2 class="text-base sm:text-lg font-semibold text-gray-900">Resumen de Deuda y Abonos</h2>
        </div>
        
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-6">
            <!-- Deuda de Habitación -->
            <div class="p-4 bg-blue-50 border border-blue-200 rounded-xl">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-xs font-semibold text-blue-700 uppercase">Deuda de Habitación</span>
                    <i class="fas fa-bed text-blue-600"></i>
                </div>
                <p class="text-2xl font-bold text-blue-900">
                    ${{ number_format(0, 2, ',', '.') }}
                </p>
                <p class="text-xs text-blue-600 mt-1">Por definir</p>
            </div>

            <!-- Deuda de Consumo -->
            <div class="p-4 bg-amber-50 border border-amber-200 rounded-xl">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-xs font-semibold text-amber-700 uppercase">Deuda de Consumo</span>
                    <i class="fas fa-shopping-cart text-amber-600"></i>
                </div>
                <p class="text-2xl font-bold text-amber-900">
                    @if($sale->debt_status === 'pendiente')
                        ${{ number_format($sale->total, 2, ',', '.') }}
                    @else
                        ${{ number_format(0, 2, ',', '.') }}
                    @endif
                </p>
                <p class="text-xs text-amber-600 mt-1">
                    @if($sale->debt_status === 'pendiente')
                        Pendiente de pago
                    @else
                        Pagado
                    @endif
                </p>
            </div>

            <!-- Total Abonado -->
            <div class="p-4 bg-green-50 border border-green-200 rounded-xl">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-xs font-semibold text-green-700 uppercase">Total Abonado</span>
                    <i class="fas fa-money-bill-wave text-green-600"></i>
                </div>
                <p class="text-2xl font-bold text-green-900">
                    @if($sale->debt_status === 'pagado')
                        ${{ number_format($sale->total, 2, ',', '.') }}
                    @else
                        ${{ number_format(0, 2, ',', '.') }}
                    @endif
                </p>
                <p class="text-xs text-green-600 mt-1">
                    @if($sale->payment_method === 'ambos')
                        Efectivo: ${{ number_format($sale->cash_amount ?? 0, 2, ',', '.') }}<br>
                        Transferencia: ${{ number_format($sale->transfer_amount ?? 0, 2, ',', '.') }}
                    @elseif($sale->payment_method !== 'pendiente')
                        {{ ucfirst($sale->payment_method) }}
                    @else
                        Sin abonos
                    @endif
                </p>
            </div>

            <!-- Total Debe -->
            <div class="p-4 bg-red-50 border border-red-200 rounded-xl">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-xs font-semibold text-red-700 uppercase">Total Debe</span>
                    <i class="fas fa-exclamation-triangle text-red-600"></i>
                </div>
                <p class="text-2xl font-bold text-red-900">
                    @if($sale->debt_status === 'pendiente')
                        ${{ number_format($sale->total, 2, ',', '.') }}
                    @else
                        ${{ number_format(0, 2, ',', '.') }}
                    @endif
                </p>
                <p class="text-xs text-red-600 mt-1">
                    @if($sale->debt_status === 'pendiente')
                        Pendiente de pago
                    @else
                        Sin deuda
                    @endif
                </p>
            </div>
        </div>

        @if($sale->room && $sale->room->reservations->first())
            <div class="mt-4 pt-4 border-t border-gray-200">
                <p class="text-xs text-gray-600">
                    <span class="font-semibold">Titular:</span> 
                    {{ $sale->room->reservations->first()->customer->name ?? 'N/A' }}
                </p>
            </div>
        @endif
    </div>
    @endif
</div>
@endsection

