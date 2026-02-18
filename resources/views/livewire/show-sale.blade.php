<div class="{{ $isModal ? '' : 'max-w-4xl mx-auto' }} space-y-4 sm:space-y-6" x-data="{ confirmingDelete: false }">
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
                    @if($isModal)
                        <button type="button"
                                wire:click="$dispatch('sales-open-edit-modal', { saleId: {{ $sale->id }} })"
                                class="inline-flex items-center justify-center px-4 py-2.5 rounded-xl border-2 border-indigo-600 bg-indigo-600 text-white text-sm font-semibold hover:bg-indigo-700 transition-all">
                            <i class="fas fa-edit mr-2"></i>
                            Editar
                        </button>
                    @else
                        <a href="{{ route('sales.edit', $sale) }}" 
                           class="inline-flex items-center justify-center px-4 py-2.5 rounded-xl border-2 border-indigo-600 bg-indigo-600 text-white text-sm font-semibold hover:bg-indigo-700 transition-all">
                            <i class="fas fa-edit mr-2"></i>
                            Editar
                        </a>
                    @endif
                @endcan
                @if($isModal)
                    <button type="button"
                            wire:click="$dispatch('sales-close-modal')"
                            class="inline-flex items-center justify-center px-4 py-2.5 rounded-xl border border-gray-300 text-sm font-semibold text-gray-700 bg-white hover:bg-gray-50 transition-all">
                        <i class="fas fa-times mr-2"></i>
                        Cerrar
                    </button>
                @else
                    <a href="{{ route('sales.index') }}" 
                       class="inline-flex items-center justify-center px-4 py-2.5 rounded-xl border border-gray-300 text-sm font-semibold text-gray-700 bg-white hover:bg-gray-50 transition-all">
                        <i class="fas fa-arrow-left mr-2"></i>
                        Volver
                    </a>
                @endif
            </div>
        </div>
    </div>

    <!-- Información General -->
    <div class="bg-white rounded-xl border border-gray-100 p-4 sm:p-6">
        <div class="flex items-center space-x-2 mb-4 sm:mb-6">
            <div class="p-2 rounded-lg bg-blue-50 text-blue-600">
                <i class="fas fa-info text-sm"></i>
            </div>
            <h2 class="text-base sm:text-lg font-semibold text-gray-900">Información General</h2>
        </div>
        
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-6">
            <div>
                <label class="block text-xs font-semibold text-gray-500 mb-1">Fecha</label>
                <p class="text-sm font-medium text-gray-900">{{ $sale->sale_date->format('d/m/Y') }} {{ $sale->created_at?->format('H:i') }}</p>
            </div>
            
            <div>
                <label class="block text-xs font-semibold text-gray-500 mb-1">Recepcionista</label>
                <p class="text-sm font-medium text-gray-900">{{ $sale->user->name }}</p>
            </div>
            
            @if($sale->room)
                <div>
                    <label class="block text-xs font-semibold text-gray-500 mb-1">Habitación</label>
                    <p class="text-sm font-medium text-gray-900">Habitación {{ $sale->room->room_number }}</p>
                </div>
            @endif
            
            <div>
                <label class="block text-xs font-semibold text-gray-500 mb-1">Estado de Deuda</label>
                @if($sale->debt_status === 'pagado')
                    <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-medium bg-green-50 text-green-700 border border-green-100">
                        <i class="fas fa-check-circle mr-1.5 text-xs"></i>
                        Pagado
                    </span>
                @else
                    <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-medium bg-red-50 text-red-700 border border-red-100">
                        <i class="fas fa-exclamation-circle mr-1.5 text-xs"></i>
                        Pendiente
                    </span>
                @endif
            </div>
            
            <div>
                <label class="block text-xs font-semibold text-gray-500 mb-1">Total</label>
                <p class="text-lg font-bold text-gray-900">{{ formatCurrency($sale->total) }}</p>
            </div>
        </div>
    </div>

    <!-- Productos -->
    <div class="bg-white rounded-xl border border-gray-100 p-4 sm:p-6">
        <div class="flex items-center space-x-2 mb-4 sm:mb-6">
            <div class="p-2 rounded-lg bg-amber-50 text-amber-600">
                <i class="fas fa-box text-sm"></i>
            </div>
            <h2 class="text-base sm:text-lg font-semibold text-gray-900">Productos</h2>
        </div>
        
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase">Producto</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase">Categoría</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase">Cantidad</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase">Precio Unit.</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold text-gray-700 uppercase">Total</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($sale->items as $item)
                        <tr>
                            <td class="px-4 py-3 text-sm text-gray-900">{{ $item->product->name }}</td>
                            <td class="px-4 py-3 text-sm text-gray-600">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-medium 
                                    {{ $item->product->category->name === 'Bebidas' ? 'bg-blue-100 text-blue-700' : 'bg-amber-100 text-amber-700' }}">
                                    {{ $item->product->category->name ?? 'Sin categoría' }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-900">{{ $item->quantity }}</td>
                            <td class="px-4 py-3 text-sm text-gray-900">{{ formatCurrency($item->unit_price) }}</td>
                            <td class="px-4 py-3 text-sm font-medium text-gray-900 text-right">{{ formatCurrency($item->total) }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot class="bg-gray-50">
                    <tr>
                        <td colspan="4" class="px-4 py-3 text-sm font-semibold text-gray-900 text-right">Total:</td>
                        <td class="px-4 py-3 text-lg font-bold text-gray-900 text-right">{{ formatCurrency($sale->total) }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    <!-- Método de Pago -->
    <div class="bg-white rounded-xl border border-gray-100 p-4 sm:p-6">
        <div class="flex items-center space-x-2 mb-4 sm:mb-6">
            <div class="p-2 rounded-lg bg-green-50 text-green-600">
                <i class="fas fa-money-bill-wave text-sm"></i>
            </div>
            <h2 class="text-base sm:text-lg font-semibold text-gray-900">Método de Pago</h2>
        </div>
        
        <div class="space-y-3">
            <div>
                <label class="block text-xs font-semibold text-gray-500 mb-1">Método</label>
                @if($sale->payment_method === 'ambos')
                    <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-medium bg-purple-50 text-purple-700 border border-purple-100">
                        <i class="fas fa-exchange-alt mr-1.5 text-xs"></i>
                        Ambos
                    </span>
                @elseif($sale->payment_method === 'pendiente')
                    <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-medium bg-orange-50 text-orange-700 border border-orange-100">
                        <i class="fas fa-clock mr-1.5 text-xs"></i>
                        Pendiente
                    </span>
                @else
                    <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-medium {{ $sale->payment_method === 'efectivo' ? 'bg-green-50 text-green-700 border border-green-100' : 'bg-blue-50 text-blue-700 border border-blue-100' }}">
                        <i class="fas fa-{{ $sale->payment_method === 'efectivo' ? 'money-bill-wave' : 'university' }} mr-1.5 text-xs"></i>
                        {{ ucfirst($sale->payment_method) }}
                    </span>
                @endif
            </div>
            
            @if($sale->payment_method === 'ambos')
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-gray-500 mb-1">Efectivo</label>
                        <p class="text-sm font-medium text-gray-900">{{ formatCurrency($sale->cash_amount ?? 0) }}</p>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-500 mb-1">Transferencia</label>
                        <p class="text-sm font-medium text-gray-900">{{ formatCurrency($sale->transfer_amount ?? 0) }}</p>
                    </div>
                </div>
            @endif
        </div>
    </div>

    @if($sale->room_id)
        <!-- Estado de Cuenta de Habitación -->
        <div class="bg-white rounded-xl border border-gray-100 p-4 sm:p-6">
            <div class="flex items-center space-x-2 mb-4 sm:mb-6">
                <div class="p-2 rounded-lg bg-blue-50 text-blue-600">
                    <i class="fas fa-bed text-sm"></i>
                </div>
                <h2 class="text-base sm:text-lg font-semibold text-gray-900">Estado de Cuenta de Habitación</h2>
            </div>
            
            @php
                $reservation = $sale->room->reservations->first();
                $customer = $reservation && $reservation->customer ? $reservation->customer : null;
                $pendingSales = \App\Models\Sale::where('room_id', $sale->room_id)
                    ->where('debt_status', 'pendiente')
                    ->sum('total');
                $paidSales = \App\Models\Sale::where('room_id', $sale->room_id)
                    ->where('debt_status', 'pagado')
                    ->sum('total');
            @endphp
            
            <div class="space-y-3">
                @if($customer)
                    <div>
                        <label class="block text-xs font-semibold text-gray-500 mb-1">Titular</label>
                        <p class="text-sm font-medium text-gray-900">{{ $customer->name }}</p>
                    </div>
                @endif
                
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-gray-500 mb-1">Deuda de Consumo Pendiente</label>
                        <p class="text-lg font-bold text-red-600">${{ number_format($pendingSales, 2, ',', '.') }}</p>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-500 mb-1">Total Pagado</label>
                        <p class="text-lg font-bold text-green-600">${{ number_format($paidSales, 2, ',', '.') }}</p>
                    </div>
                </div>
            </div>
        </div>
    @endif

    @if($sale->notes)
        <!-- Notas -->
        <div class="bg-white rounded-xl border border-gray-100 p-4 sm:p-6">
            <div class="flex items-center space-x-2 mb-4 sm:mb-6">
                <div class="p-2 rounded-lg bg-gray-50 text-gray-600">
                    <i class="fas fa-sticky-note text-sm"></i>
                </div>
                <h2 class="text-base sm:text-lg font-semibold text-gray-900">Notas</h2>
            </div>
            <p class="text-sm text-gray-700">{{ $sale->notes }}</p>
        </div>
    @endif

    <!-- Acciones -->
    @can('delete_sales')
    <div class="bg-white rounded-xl border border-red-200 p-4 sm:p-6">
        <div class="flex items-center justify-between">
<div>
                <h3 class="text-sm font-semibold text-gray-900 mb-1">Zona de Peligro</h3>
                <p class="text-xs text-gray-500">Eliminar esta venta restaurará el stock de los productos.</p>
            </div>
            <button type="button" 
                    @click="confirmingDelete = true"
                        class="inline-flex items-center justify-center px-4 py-2.5 rounded-xl border border-red-300 text-sm font-semibold text-red-700 bg-white hover:bg-red-50 transition-colors">
                    <i class="fas fa-trash mr-2"></i>
                    Eliminar Venta
                </button>
        </div>
    </div>
    @endcan

    <!-- Modal de Confirmación Estilizado -->
    <div x-show="confirmingDelete" 
         class="fixed inset-0 z-[100] overflow-y-auto" 
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         x-cloak>
        <!-- Fondo desenfocado -->
        <div class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm transition-opacity"></div>

        <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
            <div class="relative transform overflow-hidden rounded-2xl bg-white text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-lg border border-gray-100"
                 @click.away="confirmingDelete = false"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95">
                <div class="bg-white px-4 pb-4 pt-5 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                        <div class="mx-auto flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-full bg-red-50 sm:mx-0 sm:h-10 sm:w-10">
                            <i class="fas fa-exclamation-triangle text-red-600"></i>
                        </div>
                        <div class="mt-3 text-center sm:ml-4 sm:mt-0 sm:text-left">
                            <h3 class="text-lg font-bold leading-6 text-gray-900">¿Eliminar Venta?</h3>
                            <div class="mt-2">
                                <p class="text-sm text-gray-500">
                                    Esta acción no se puede deshacer. Los productos vendidos se **restaurarán automáticamente** al inventario.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6 gap-2">
                    <form action="{{ route('sales.destroy', $sale) }}" method="POST" x-ref="deleteForm">
                        @csrf
                        @method('DELETE')
                        <button type="submit" 
                                class="inline-flex w-full justify-center rounded-xl bg-red-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-red-700 sm:ml-3 sm:w-auto transition-all">
                            Confirmar Eliminación
                        </button>
                    </form>
                    <button type="button" 
                            @click="confirmingDelete = false"
                            class="mt-3 inline-flex w-full justify-center rounded-xl bg-white px-4 py-2.5 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 sm:mt-0 sm:w-auto transition-all">
                        Cancelar
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
