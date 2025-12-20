@extends('layouts.app')

@section('title', 'Editar Venta')
@section('header', 'Editar Venta')

@section('content')
<div class="max-w-4xl mx-auto">
    <form method="POST" action="{{ route('sales.update', $sale) }}" x-data="{ loading: false }" @submit="loading = true">
        @csrf
        @method('PUT')
        
        <!-- Header -->
        <div class="bg-white rounded-xl border border-gray-100 p-4 sm:p-6 mb-4 sm:mb-6">
            <div class="flex items-center space-x-3 sm:space-x-4">
                <div class="p-2.5 sm:p-3 rounded-xl bg-indigo-50 text-indigo-600">
                    <i class="fas fa-edit text-lg sm:text-xl"></i>
                </div>
                <div>
                    <h1 class="text-xl sm:text-2xl font-bold text-gray-900">Editar Venta</h1>
                    <p class="text-xs sm:text-sm text-gray-500 mt-1">Venta #{{ $sale->id }} - {{ $sale->sale_date->format('d/m/Y') }}</p>
                </div>
            </div>
        </div>

        <div class="space-y-4 sm:space-y-6">
            <!-- Información de la Venta -->
            <div class="bg-white rounded-xl border border-gray-100 p-4 sm:p-6">
                <div class="flex items-center space-x-2 mb-4 sm:mb-6">
                    <div class="p-2 rounded-lg bg-blue-50 text-blue-600">
                        <i class="fas fa-info text-sm"></i>
                    </div>
                    <h2 class="text-base sm:text-lg font-semibold text-gray-900">Información de la Venta</h2>
                </div>
                
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-6">
                    <div>
                        <label class="block text-xs sm:text-sm font-semibold text-gray-700 mb-2">
                            Fecha
                        </label>
                        <div class="px-3 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-sm text-gray-600">
                            {{ $sale->sale_date->format('d/m/Y') }}
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs sm:text-sm font-semibold text-gray-700 mb-2">
                            Recepcionista
                        </label>
                        <div class="px-3 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-sm text-gray-600">
                            {{ $sale->user->name }}
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs sm:text-sm font-semibold text-gray-700 mb-2">
                            Habitación
                        </label>
                        <div class="px-3 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-sm text-gray-600">
                            @if($sale->room)
                                Habitación {{ $sale->room->room_number }}
                            @else
                                Venta Normal
                            @endif
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs sm:text-sm font-semibold text-gray-700 mb-2">
                            Turno
                        </label>
                        <div class="px-3 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-sm text-gray-600">
                            {{ ucfirst($sale->shift) }}
                        </div>
                    </div>
                </div>
            </div>

            <!-- Estado de Deuda (Solo para habitaciones) -->
            @if($sale->room_id)
            <div class="bg-white rounded-xl border border-gray-100 p-4 sm:p-6">
                <div class="flex items-center space-x-2 mb-4 sm:mb-6">
                    <div class="p-2 rounded-lg bg-orange-50 text-orange-600">
                        <i class="fas fa-exclamation-triangle text-sm"></i>
                    </div>
                    <h2 class="text-base sm:text-lg font-semibold text-gray-900">Estado de Deuda</h2>
                </div>
                
                <div class="space-y-4 sm:space-y-6">
                    <div>
                        <label for="debt_status" class="block text-xs sm:text-sm font-semibold text-gray-700 mb-2">
                            Estado de Deuda <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <select id="debt_status" 
                                    name="debt_status"
                                    class="block w-full pl-3 sm:pl-4 pr-10 py-2.5 border border-gray-300 rounded-xl text-sm text-gray-900 focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent appearance-none bg-white @error('debt_status') border-red-300 focus:ring-red-500 @enderror"
                                    required>
                                <option value="pagado" {{ old('debt_status', $sale->debt_status) == 'pagado' ? 'selected' : '' }}>Pagado</option>
                                <option value="pendiente" {{ old('debt_status', $sale->debt_status) == 'pendiente' ? 'selected' : '' }}>Pendiente</option>
                            </select>
                            <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                <i class="fas fa-chevron-down text-gray-400 text-xs"></i>
                            </div>
                        </div>
                        <p class="mt-1.5 text-xs text-gray-500">
                            Solo disponible para ventas asociadas a habitaciones
                        </p>
                        @error('debt_status')
                            <p class="mt-1.5 text-xs text-red-600 flex items-center">
                                <i class="fas fa-exclamation-circle mr-1.5"></i>
                                {{ $message }}
                            </p>
                        @enderror
                    </div>
                </div>
            </div>
            @endif

            <!-- Productos Vendidos -->
            <div class="bg-white rounded-xl border border-gray-100 p-4 sm:p-6">
                <div class="flex items-center space-x-2 mb-4 sm:mb-6">
                    <div class="p-2 rounded-lg bg-amber-50 text-amber-600">
                        <i class="fas fa-box text-sm"></i>
                    </div>
                    <h2 class="text-base sm:text-lg font-semibold text-gray-900">Productos Vendidos</h2>
                </div>
                
                <div class="space-y-3">
                    @foreach($sale->items as $item)
                        <div class="flex justify-between items-center p-3 bg-gray-50 rounded-lg">
                            <div>
                                <span class="font-semibold text-gray-900">{{ $item->product->name }}</span>
                                <span class="text-sm text-gray-600 ml-2">
                                    x{{ $item->quantity }} @ ${{ number_format($item->unit_price, 2, ',', '.') }}
                                </span>
                            </div>
                            <span class="font-semibold text-gray-900">
                                ${{ number_format($item->total, 2, ',', '.') }}
                            </span>
                        </div>
                    @endforeach
                </div>

                <div class="mt-4 pt-4 border-t border-gray-200">
                    <div class="flex justify-between items-center">
                        <span class="text-lg font-semibold text-gray-900">Total:</span>
                        <span class="text-2xl font-bold text-green-600">
                            ${{ number_format($sale->total, 2, ',', '.') }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- Estado de Pago -->
            <div class="bg-white rounded-xl border border-gray-100 p-4 sm:p-6">
                <div class="flex items-center space-x-2 mb-4 sm:mb-6">
                    <div class="p-2 rounded-lg bg-green-50 text-green-600">
                        <i class="fas fa-dollar-sign text-sm"></i>
                    </div>
                    <h2 class="text-base sm:text-lg font-semibold text-gray-900">Estado de Pago</h2>
                </div>
                
                <div class="space-y-4 sm:space-y-6" x-data="{ paymentMethod: '{{ old('payment_method', $sale->payment_method) }}', updateDebtStatus() { if (this.paymentMethod === 'pendiente') { const debtSelect = document.getElementById('debt_status'); if (debtSelect) debtSelect.value = 'pendiente'; } else if (this.paymentMethod !== 'pendiente') { const debtSelect = document.getElementById('debt_status'); if (debtSelect) debtSelect.value = 'pagado'; } } }">
                    <!-- Método de Pago -->
                    <div>
                        <label for="payment_method" class="block text-xs sm:text-sm font-semibold text-gray-700 mb-2">
                            Método de Pago <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <select id="payment_method" 
                                    name="payment_method"
                                    x-model="paymentMethod"
                                    @change="updateDebtStatus"
                                    class="block w-full pl-3 sm:pl-4 pr-10 py-2.5 border border-gray-300 rounded-xl text-sm text-gray-900 focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent appearance-none bg-white @error('payment_method') border-red-300 focus:ring-red-500 @enderror"
                                    required>
                                <option value="efectivo" {{ old('payment_method', $sale->payment_method) == 'efectivo' ? 'selected' : '' }}>Efectivo</option>
                                <option value="transferencia" {{ old('payment_method', $sale->payment_method) == 'transferencia' ? 'selected' : '' }}>Transferencia</option>
                                <option value="ambos" {{ old('payment_method', $sale->payment_method) == 'ambos' ? 'selected' : '' }}>Ambos</option>
                                @if($sale->room_id)
                                <option value="pendiente" {{ old('payment_method', $sale->payment_method) == 'pendiente' ? 'selected' : '' }}>Pendiente</option>
                                @endif
                            </select>
                            <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                <i class="fas fa-chevron-down text-gray-400 text-xs"></i>
                            </div>
                        </div>
                        @error('payment_method')
                            <p class="mt-1.5 text-xs text-red-600 flex items-center">
                                <i class="fas fa-exclamation-circle mr-1.5"></i>
                                {{ $message }}
                            </p>
                        @enderror
                    </div>

                    <!-- Campos para pago mixto -->
                    <div x-show="paymentMethod === 'ambos'" 
                         x-cloak
                         class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label for="cash_amount" class="block text-xs font-semibold text-gray-700 mb-2">
                                Monto en Efectivo <span class="text-red-500">*</span>
                            </label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <span class="text-gray-500 text-sm">$</span>
                                </div>
                                <input type="number" 
                                       id="cash_amount"
                                       name="cash_amount"
                                       value="{{ old('cash_amount', $sale->cash_amount ?? 0) }}"
                                       step="0.01"
                                       min="0"
                                       class="block w-full pl-8 pr-3 py-2.5 border border-gray-300 rounded-xl text-sm text-gray-900 focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent @error('cash_amount') border-red-300 focus:ring-red-500 @enderror"
                                       placeholder="0.00">
                            </div>
                            @error('cash_amount')
                                <p class="mt-1.5 text-xs text-red-600 flex items-center">
                                    <i class="fas fa-exclamation-circle mr-1.5"></i>
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>
                        
                        <div>
                            <label for="transfer_amount" class="block text-xs font-semibold text-gray-700 mb-2">
                                Monto por Transferencia <span class="text-red-500">*</span>
                            </label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <span class="text-gray-500 text-sm">$</span>
                                </div>
                                <input type="number" 
                                       id="transfer_amount"
                                       name="transfer_amount"
                                       value="{{ old('transfer_amount', $sale->transfer_amount ?? 0) }}"
                                       step="0.01"
                                       min="0"
                                       class="block w-full pl-8 pr-3 py-2.5 border border-gray-300 rounded-xl text-sm text-gray-900 focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent @error('transfer_amount') border-red-300 focus:ring-red-500 @enderror"
                                       placeholder="0.00">
                            </div>
                            @error('transfer_amount')
                                <p class="mt-1.5 text-xs text-red-600 flex items-center">
                                    <i class="fas fa-exclamation-circle mr-1.5"></i>
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>
                        
                        <div class="sm:col-span-2">
                            <p class="text-xs text-gray-500">
                                <span class="font-semibold">Total de la venta:</span> 
                                ${{ number_format($sale->total, 2, ',', '.') }}
                            </p>
                        </div>
                    </div>

                    <!-- Notas -->
                    <div>
                        <label for="notes" class="block text-xs sm:text-sm font-semibold text-gray-700 mb-2">
                            Notas
                        </label>
                        <textarea id="notes" 
                                  name="notes"
                                  rows="3"
                                  class="block w-full px-3 sm:px-4 py-2.5 border border-gray-300 rounded-xl text-sm text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent transition-all @error('notes') border-red-300 focus:ring-red-500 @enderror"
                                  placeholder="Notas adicionales...">{{ old('notes', $sale->notes) }}</textarea>
                        @error('notes')
                            <p class="mt-1.5 text-xs text-red-600 flex items-center">
                                <i class="fas fa-exclamation-circle mr-1.5"></i>
                                {{ $message }}
                            </p>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Botones de Acción -->
            <div class="bg-white rounded-xl border border-gray-100 p-4 sm:p-6">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                    <a href="{{ route('sales.show', $sale) }}" 
                       class="inline-flex items-center justify-center px-4 sm:px-6 py-2.5 rounded-xl border border-gray-300 text-sm font-semibold text-gray-700 bg-white hover:bg-gray-50 transition-colors duration-200">
                        <i class="fas fa-arrow-left mr-2"></i>
                        Volver
                    </a>
                    
                    <button type="submit" 
                            x-bind:disabled="loading"
                            class="inline-flex items-center justify-center px-4 sm:px-6 py-2.5 rounded-xl border-2 border-green-600 bg-green-600 text-white text-sm font-semibold hover:bg-green-700 hover:border-green-700 transition-all duration-200 shadow-sm hover:shadow-md disabled:opacity-50 disabled:cursor-not-allowed">
                        <i class="fas fa-save mr-2" x-show="!loading"></i>
                        <i class="fas fa-spinner fa-spin mr-2" x-show="loading"></i>
                        <span x-show="!loading">Actualizar Venta</span>
                        <span x-show="loading">Procesando...</span>
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection

