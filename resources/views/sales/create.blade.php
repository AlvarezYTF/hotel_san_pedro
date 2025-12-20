@extends('layouts.app')

@section('title', 'Nueva Venta')
@section('header', 'Nueva Venta')

@section('content')
<div class="max-w-6xl mx-auto" x-data="saleForm()" x-init="
    // Aplicar lógica inicial de bloqueo del estado de deuda
    if (saleData.payment_method !== 'pendiente' && saleData.room_id) {
        saleData.debt_status = 'pagado';
    }
">
    <form method="POST" action="{{ route('sales.store') }}" @submit.prevent="submitForm">
        @csrf
        
        <!-- Header -->
        <div class="bg-white rounded-xl border border-gray-100 p-4 sm:p-6 mb-4 sm:mb-6">
            <div class="flex items-center space-x-3 sm:space-x-4">
                <div class="p-2.5 sm:p-3 rounded-xl bg-green-50 text-green-600">
                    <i class="fas fa-plus text-lg sm:text-xl"></i>
                </div>
                <div>
                    <h1 class="text-xl sm:text-2xl font-bold text-gray-900">Nueva Venta</h1>
                    <p class="text-xs sm:text-sm text-gray-500 mt-1">Registre una nueva venta de productos</p>
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
                
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-6">
                    <!-- Fecha de Venta -->
                    <div>
                        <label for="sale_date" class="block text-xs sm:text-sm font-semibold text-gray-700 mb-2">
                            Fecha de Venta <span class="text-red-500">*</span>
                        </label>
                        <input type="date" 
                               id="sale_date" 
                               name="sale_date" 
                               x-model="saleData.sale_date"
                               value="{{ old('sale_date', date('Y-m-d')) }}"
                               class="block w-full px-3 sm:px-4 py-2.5 border border-gray-300 rounded-xl text-sm text-gray-900 focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent transition-all @error('sale_date') border-red-300 focus:ring-red-500 @enderror"
                               required>
                        @error('sale_date')
                            <p class="mt-1.5 text-xs text-red-600 flex items-center">
                                <i class="fas fa-exclamation-circle mr-1.5"></i>
                                {{ $message }}
                            </p>
                        @enderror
                    </div>

                    <!-- Habitación (Opcional) -->
                    <div>
                        <label for="room_id" class="block text-xs sm:text-sm font-semibold text-gray-700 mb-2">
                            Habitación (Opcional)
                        </label>
                        <div class="relative">
                            <select id="room_id" 
                                    name="room_id"
                                    x-model="saleData.room_id"
                                    @change="updateDebtStatusVisibility"
                                    class="block w-full pl-3 sm:pl-4 pr-10 py-2.5 border border-gray-300 rounded-xl text-sm text-gray-900 focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent appearance-none bg-white @error('room_id') border-red-300 focus:ring-red-500 @enderror">
                                <option value="">Venta Normal</option>
                                @foreach($rooms as $room)
                                    @php
                                        $currentReservation = $room->current_reservation ?? $room->reservations->first();
                                        $customerName = $currentReservation && $currentReservation->customer ? $currentReservation->customer->name : '';
                                    @endphp
                                    <option value="{{ $room->id }}" 
                                            data-customer="{{ $customerName }}"
                                            {{ old('room_id') == $room->id ? 'selected' : '' }}>
                                        Habitación {{ $room->room_number }}@if($customerName) - {{ $customerName }}@endif
                                    </option>
                                @endforeach
                            </select>
                            <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                <i class="fas fa-chevron-down text-gray-400 text-xs"></i>
                            </div>
                        </div>
                        <p class="mt-1.5 text-xs text-gray-500">
                            Deje vacío para venta normal
                        </p>
                        <div x-show="saleData.room_id && saleData.room_id !== ''" 
                             x-cloak
                             class="mt-2 p-2 bg-blue-50 border border-blue-200 rounded-lg">
                            <p class="text-xs text-blue-700">
                                <i class="fas fa-info-circle mr-1"></i>
                                <span x-text="getCustomerName()"></span>
                            </p>
                        </div>
                        @error('room_id')
                            <p class="mt-1.5 text-xs text-red-600 flex items-center">
                                <i class="fas fa-exclamation-circle mr-1.5"></i>
                                {{ $message }}
                            </p>
                        @enderror
                    </div>

                    <!-- Turno (Auto-determinado por rol) -->
                    <div>
                        <label for="shift" class="block text-xs sm:text-sm font-semibold text-gray-700 mb-2">
                            Turno <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            @php
                                $userRole = Auth::user()->roles->first()?->name;
                                $autoShift = 'dia';
                                if ($userRole === 'Recepcionista Día') {
                                    $autoShift = 'dia';
                                } elseif ($userRole === 'Recepcionista Noche') {
                                    $autoShift = 'noche';
                                } else {
                                    $autoShift = $defaultShift;
                                }
                            @endphp
                            <select id="shift" 
                                    name="shift"
                                    x-model="saleData.shift"
                                    class="block w-full pl-3 sm:pl-4 pr-10 py-2.5 border border-gray-300 rounded-xl text-sm text-gray-900 focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent appearance-none bg-white @error('shift') border-red-300 focus:ring-red-500 @enderror"
                                    required>
                                <option value="dia" {{ old('shift', $autoShift) == 'dia' ? 'selected' : '' }}>Día</option>
                                <option value="noche" {{ old('shift', $autoShift) == 'noche' ? 'selected' : '' }}>Noche</option>
                            </select>
                            <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                <i class="fas fa-chevron-down text-gray-400 text-xs"></i>
                            </div>
                        </div>
                        <p class="mt-1.5 text-xs text-gray-500">
                            @if($userRole === 'Recepcionista Día')
                                Turno automático: Día
                            @elseif($userRole === 'Recepcionista Noche')
                                Turno automático: Noche
                            @else
                                Turno determinado por hora
                            @endif
                        </p>
                        @error('shift')
                            <p class="mt-1.5 text-xs text-red-600 flex items-center">
                                <i class="fas fa-exclamation-circle mr-1.5"></i>
                                {{ $message }}
                            </p>
                        @enderror
                    </div>

                    <!-- Método de Pago -->
                    <div class="sm:col-span-2">
                        <label for="payment_method" class="block text-xs sm:text-sm font-semibold text-gray-700 mb-2">
                            Método de Pago <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <select id="payment_method" 
                                    name="payment_method"
                                    x-model="saleData.payment_method"
                                    @change="updatePaymentFields"
                                    class="block w-full pl-3 sm:pl-4 pr-10 py-2.5 border border-gray-300 rounded-xl text-sm text-gray-900 focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent appearance-none bg-white @error('payment_method') border-red-300 focus:ring-red-500 @enderror"
                                    required>
                                <option value="efectivo" {{ old('payment_method', 'efectivo') == 'efectivo' ? 'selected' : '' }}>Efectivo</option>
                                <option value="transferencia" {{ old('payment_method') == 'transferencia' ? 'selected' : '' }}>Transferencia</option>
                                <option value="ambos" {{ old('payment_method') == 'ambos' ? 'selected' : '' }}>Ambos</option>
                                <template x-if="saleData.room_id && saleData.room_id !== ''">
                                <option value="pendiente" {{ old('payment_method') == 'pendiente' ? 'selected' : '' }}>Pendiente</option>
                                </template>
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
                        
                        <!-- Campos para pago mixto -->
                        <div x-show="saleData.payment_method === 'ambos'" 
                             x-cloak
                             class="mt-4 grid grid-cols-1 sm:grid-cols-2 gap-4">
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
                                           x-model.number="saleData.cash_amount"
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
                                           x-model.number="saleData.transfer_amount"
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
                                    <span class="font-semibold">Total:</span> 
                                    <span x-text="'$' + formatCurrency((saleData.cash_amount || 0) + (saleData.transfer_amount || 0))"></span>
                                    <span class="text-red-600" x-show="Math.abs(((saleData.cash_amount || 0) + (saleData.transfer_amount || 0)) - total) > 0.01">
                                        (Debe ser igual al total de la venta: <span x-text="'$' + formatCurrency(total)"></span>)
                                    </span>
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Estado de Deuda (Solo para habitaciones) -->
                    <div x-show="saleData.room_id && saleData.room_id !== ''" 
                         x-cloak
                         class="sm:col-span-2">
                        <label for="debt_status" class="block text-xs sm:text-sm font-semibold text-gray-700 mb-2">
                            Estado de Deuda <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <select id="debt_status" 
                                    name="debt_status"
                                    x-model="saleData.debt_status"
                                    x-bind:disabled="saleData.payment_method !== 'pendiente'"
                                    class="block w-full pl-3 sm:pl-4 pr-10 py-2.5 border border-gray-300 rounded-xl text-sm text-gray-900 focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent appearance-none bg-white disabled:bg-gray-100 disabled:cursor-not-allowed disabled:text-gray-500 @error('debt_status') border-red-300 focus:ring-red-500 @enderror">
                                <option value="pagado" {{ old('debt_status', 'pagado') == 'pagado' ? 'selected' : '' }}>Pagado</option>
                                <option value="pendiente" {{ old('debt_status') == 'pendiente' ? 'selected' : '' }}>Pendiente</option>
                            </select>
                            <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                <i class="fas fa-chevron-down text-gray-400 text-xs"></i>
                            </div>
                        </div>
                        <p class="mt-1.5 text-xs text-gray-500" x-show="saleData.room_id && saleData.room_id !== ''">
                            <span x-show="saleData.payment_method === 'pendiente'">
                                Seleccione "Pendiente" si el pago aún no se ha realizado
                            </span>
                            <span x-show="saleData.payment_method !== 'pendiente'" class="text-amber-600">
                                <i class="fas fa-lock mr-1"></i>
                                Bloqueado automáticamente en "Pagado" porque el método de pago no es "Pendiente"
                            </span>
                        </p>
                        @error('debt_status')
                            <p class="mt-1.5 text-xs text-red-600 flex items-center">
                                <i class="fas fa-exclamation-circle mr-1.5"></i>
                                {{ $message }}
                            </p>
                        @enderror
                    </div>

                    <!-- Notas -->
                    <div class="sm:col-span-2">
                        <label for="notes" class="block text-xs sm:text-sm font-semibold text-gray-700 mb-2">
                            Notas
                        </label>
                        <textarea id="notes" 
                                  name="notes"
                                  rows="2"
                                  class="block w-full px-3 sm:px-4 py-2.5 border border-gray-300 rounded-xl text-sm text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent transition-all @error('notes') border-red-300 focus:ring-red-500 @enderror"
                                  placeholder="Notas adicionales sobre la venta...">{{ old('notes') }}</textarea>
                        @error('notes')
                            <p class="mt-1.5 text-xs text-red-600 flex items-center">
                                <i class="fas fa-exclamation-circle mr-1.5"></i>
                                {{ $message }}
                            </p>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Productos -->
            <div class="bg-white rounded-xl border border-gray-100 p-4 sm:p-6">
                <div class="flex items-center justify-between mb-4 sm:mb-6">
                    <div class="flex items-center space-x-2">
                        <div class="p-2 rounded-lg bg-amber-50 text-amber-600">
                            <i class="fas fa-box text-sm"></i>
                        </div>
                        <h2 class="text-base sm:text-lg font-semibold text-gray-900">Productos</h2>
                    </div>
                    <div class="flex items-center space-x-3">
                        <!-- Filtro de Categoría -->
                        <div class="flex items-center space-x-2">
                            <label for="category_filter" class="text-xs font-semibold text-gray-700">Filtrar por:</label>
                            <select id="category_filter"
                                    x-model="selectedCategory"
                                    @change="filterProducts"
                                    class="px-3 py-2 border border-gray-300 rounded-lg text-sm text-gray-900 focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent">
                                <option value="">Todos</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                                @endforeach
                            </select>
                    </div>
                    <button type="button" 
                            @click="addProduct"
                            class="inline-flex items-center justify-center px-3 py-2 rounded-lg border-2 border-green-600 bg-green-600 text-white text-xs font-semibold hover:bg-green-700 transition-all">
                        <i class="fas fa-plus mr-1.5"></i>
                        Agregar Producto
                    </button>
                    </div>
                </div>

                <div class="space-y-4" x-show="items.length > 0">
                    <template x-for="(item, index) in items" :key="index">
                        <div class="border border-gray-200 rounded-xl p-4">
                            <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
                                <!-- Producto -->
                                <div class="sm:col-span-2">
                                    <label class="block text-xs font-semibold text-gray-700 mb-2">
                                        Producto <span class="text-red-500">*</span>
                                    </label>
                                    <select x-model="item.product_id"
                                            @change="loadProduct(index)"
                                            :name="`items[${index}][product_id]`"
                                            class="block w-full px-3 py-2.5 border border-gray-300 rounded-lg text-sm text-gray-900 focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent"
                                            required>
                                        <option value="">Seleccionar producto...</option>
                                        @foreach($products as $product)
                                            <option value="{{ $product->id }}" 
                                                    data-price="{{ $product->price }}"
                                                    data-stock="{{ $product->quantity }}"
                                                    data-category="{{ $product->category_id }}"
                                                    x-show="!selectedCategory || selectedCategory == '' || selectedCategory == '{{ $product->category_id }}'">
                                                {{ $product->name }} - Stock: {{ $product->quantity }} - ${{ number_format($product->price, 2, ',', '.') }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <!-- Cantidad -->
                                <div>
                                    <label class="block text-xs font-semibold text-gray-700 mb-2">
                                        Cantidad <span class="text-red-500">*</span>
                                    </label>
                                    <input type="number" 
                                           x-model.number="item.quantity"
                                           @input="validateQuantity(index)"
                                           @change="validateQuantity(index)"
                                           :name="`items[${index}][quantity]`"
                                           min="1"
                                           :max="item.maxStock"
                                           class="block w-full px-3 py-2.5 border border-gray-300 rounded-lg text-sm text-gray-900 focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent"
                                           required>
                                    <p class="mt-1 text-xs text-gray-500" x-show="item.maxStock">
                                        Stock disponible: <span x-text="item.maxStock"></span>
                                    </p>
                                </div>

                                <!-- Total -->
                                <div>
                                    <label class="block text-xs font-semibold text-gray-700 mb-2">
                                        Total
                                    </label>
                                    <div class="relative">
                                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                            <span class="text-gray-500 text-sm">$</span>
                                        </div>
                                        <input type="text" 
                                               x-model="item.totalFormatted"
                                               readonly
                                               class="block w-full pl-8 pr-3 py-2.5 border border-gray-300 rounded-lg text-sm text-gray-900 bg-gray-50">
                                    </div>
                                </div>
                            </div>

                            <div class="mt-3 flex justify-end">
                                <button type="button" 
                                        @click="removeProduct(index)"
                                        class="text-red-600 hover:text-red-800 text-sm">
                                    <i class="fas fa-trash mr-1"></i>
                                    Eliminar
                                </button>
                            </div>
                        </div>
                    </template>
                </div>

                <div x-show="items.length === 0" class="text-center py-8 text-gray-500">
                    <i class="fas fa-box-open text-3xl mb-2"></i>
                    <p class="text-sm">No hay productos agregados. Haga clic en "Agregar Producto" para comenzar.</p>
                </div>

                <!-- Total General -->
                <div class="mt-6 pt-6 border-t border-gray-200">
                    <div class="flex justify-between items-center">
                        <span class="text-lg font-semibold text-gray-900">Total de la Venta:</span>
                        <span class="text-2xl font-bold text-green-600" x-text="'$' + formatCurrency(total)"></span>
                    </div>
                </div>

                @error('items')
                    <p class="mt-2 text-xs text-red-600 flex items-center">
                        <i class="fas fa-exclamation-circle mr-1.5"></i>
                        {{ $message }}
                    </p>
                @enderror
            </div>

            <!-- Botones de Acción -->
            <div class="bg-white rounded-xl border border-gray-100 p-4 sm:p-6">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                    <p class="text-xs text-gray-500">
                        Los campos marcados con <span class="text-red-500">*</span> son obligatorios
                    </p>
                    
                    <div class="flex flex-col sm:flex-row gap-3">
                        <a href="{{ route('sales.index') }}" 
                           class="inline-flex items-center justify-center px-4 sm:px-6 py-2.5 rounded-xl border border-gray-300 text-sm font-semibold text-gray-700 bg-white hover:bg-gray-50 transition-colors duration-200">
                            <i class="fas fa-arrow-left mr-2"></i>
                            Volver
                        </a>
                        
                        <button type="submit" 
                                x-bind:disabled="loading || items.length === 0"
                                class="inline-flex items-center justify-center px-4 sm:px-6 py-2.5 rounded-xl border-2 border-green-600 bg-green-600 text-white text-sm font-semibold hover:bg-green-700 hover:border-green-700 transition-all duration-200 shadow-sm hover:shadow-md disabled:opacity-50 disabled:cursor-not-allowed">
                            <i class="fas fa-save mr-2" x-show="!loading"></i>
                            <i class="fas fa-spinner fa-spin mr-2" x-show="loading"></i>
                            <span x-show="!loading">Registrar Venta</span>
                            <span x-show="loading">Procesando...</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

@push('scripts')
<script>
function saleForm() {
    return {
        loading: false,
        saleData: {
            sale_date: '{{ old('sale_date', date('Y-m-d')) }}',
            room_id: '{{ old('room_id', '') }}',
            shift: '{{ old('shift', $autoShift ?? $defaultShift) }}',
            payment_method: '{{ old('payment_method', 'efectivo') }}',
            cash_amount: {{ old('cash_amount', 0) }},
            transfer_amount: {{ old('transfer_amount', 0) }},
            debt_status: '{{ old('debt_status', 'pagado') }}',
        },
        selectedCategory: '',
        items: [],
        
        get total() {
            return this.items.reduce((sum, item) => sum + (item.total || 0), 0);
        },
        
        addProduct() {
            this.items.push({
                product_id: '',
                quantity: 1,
                unit_price: 0,
                total: 0,
                totalFormatted: '$0.00',
                maxStock: 0
            });
        },
        
        removeProduct(index) {
            this.items.splice(index, 1);
        },
        
        loadProduct(index) {
            const select = event.target;
            const option = select.options[select.selectedIndex];
            
            if (option.value) {
                const price = parseFloat(option.getAttribute('data-price')) || 0;
                const stock = parseInt(option.getAttribute('data-stock')) || 0;
                
                this.items[index].unit_price = price;
                this.items[index].maxStock = stock;
                this.items[index].quantity = Math.min(this.items[index].quantity, stock);
                
                this.calculateItemTotal(index);
            } else {
                this.items[index].unit_price = 0;
                this.items[index].maxStock = 0;
                this.items[index].total = 0;
                this.items[index].totalFormatted = '$0.00';
            }
        },
        
        calculateItemTotal(index) {
            const item = this.items[index];
            const quantity = parseInt(item.quantity) || 0;
            const price = parseFloat(item.unit_price) || 0;
            
            item.total = quantity * price;
            item.totalFormatted = '$' + this.formatCurrency(item.total);
        },
        
        formatCurrency(value) {
            return new Intl.NumberFormat('es-CO', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            }).format(value);
        },
        
        updatePaymentFields() {
            // Reset amounts when payment method changes
            if (this.saleData.payment_method !== 'ambos') {
                this.saleData.cash_amount = 0;
                this.saleData.transfer_amount = 0;
            }
            
            // Lógica de bloqueo automático del estado de deuda
            if (this.saleData.payment_method === 'pendiente') {
                // Si el método de pago es "pendiente", habilitar y establecer en "pendiente"
                this.saleData.debt_status = 'pendiente';
            } else {
                // Si el método de pago NO es "pendiente", bloquear automáticamente en "pagado"
                this.saleData.debt_status = 'pagado';
            }
        },
        
        updateDebtStatusVisibility() {
            // If no room selected, ensure debt_status is 'pagado'
            if (!this.saleData.room_id || this.saleData.room_id === '') {
                this.saleData.debt_status = 'pagado';
            } else {
                // If room is selected, apply payment method logic
                if (this.saleData.payment_method !== 'pendiente') {
                    this.saleData.debt_status = 'pagado';
                } else {
                    this.saleData.debt_status = 'pendiente';
                }
            }
        },
        
        validateQuantity(index) {
            const item = this.items[index];
            // Ensure quantity is at least 1
            if (!item.quantity || item.quantity < 1) {
                item.quantity = 1;
            }
            // Ensure quantity doesn't exceed stock
            if (item.maxStock && item.quantity > item.maxStock) {
                item.quantity = item.maxStock;
            }
            this.calculateItemTotal(index);
        },
        
        getCustomerName() {
            if (!this.saleData.room_id) return '';
            const select = document.getElementById('room_id');
            const option = select.options[select.selectedIndex];
            const customerName = option.getAttribute('data-customer');
            return customerName ? `Titular: ${customerName}` : 'Habitación seleccionada';
        },
        
        filterProducts() {
            // Reset product selection when category filter changes if product doesn't match filter
            this.items.forEach((item, index) => {
                if (item.product_id) {
                    const select = document.querySelector(`select[name="items[${index}][product_id]"]`);
                    if (select) {
                        const option = Array.from(select.options).find(opt => opt.value == item.product_id);
                        if (option) {
                            const productCategory = option.getAttribute('data-category');
                            if (this.selectedCategory && productCategory && productCategory != this.selectedCategory) {
                                item.product_id = '';
                                item.unit_price = 0;
                                item.maxStock = 0;
                                item.total = 0;
                                item.totalFormatted = '$0.00';
                            }
                        }
                    }
                }
            });
        },
        
        submitForm() {
            if (this.items.length === 0) {
                alert('Debe agregar al menos un producto');
                return false;
            }
            
            this.loading = true;
            this.$el.submit();
        }
    }
}
</script>
@endpush
@endsection

