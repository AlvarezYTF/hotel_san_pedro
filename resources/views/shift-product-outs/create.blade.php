@extends('layouts.app')

@section('title', 'Registrar Salida de Producto')
@section('header', 'Registrar Salida de Producto')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="p-6">
            <div class="mb-6 flex items-center justify-between">
                <h3 class="text-lg font-bold text-gray-900">Nueva Salida de Inventario</h3>
                <a href="{{ route('shift-product-outs.index') }}" class="text-gray-500 hover:text-gray-700 text-sm font-medium">
                    <i class="fas fa-arrow-left mr-1"></i> Volver al listado
                </a>
            </div>

            @if($activeShift)
            <div class="mb-6 bg-green-50 border border-green-100 rounded-lg p-3 flex items-center text-sm text-green-800">
                <i class="fas fa-info-circle mr-2 text-green-500"></i>
                <div>
                    Registrando en turno: <span class="font-bold">{{ $activeShift->shift_type->value }}</span> del {{ $activeShift->shift_date->format('d/m/Y') }}
                </div>
            </div>
            @endif

            <form action="{{ route('shift-product-outs.store') }}" method="POST" class="space-y-5">
                @csrf

                <div>
                    <label for="product_id" class="block text-sm font-black text-gray-700 mb-1">Seleccionar Producto</label>
                    <select name="product_id" id="product_id" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm" required>
                        <option value="">Seleccione un producto...</option>
                        @foreach($products as $product)
                            <option value="{{ $product->id }}" data-stock="{{ $product->quantity }}" {{ old('product_id') == $product->id ? 'selected' : '' }}>
                                {{ $product->name }} (Stock: {{ $product->quantity }})
                            </option>
                        @endforeach
                    </select>
                    @error('product_id') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label for="quantity" class="block text-sm font-black text-gray-700 mb-1">Cantidad</label>
                        <input type="number" name="quantity" id="quantity" step="1" min="1" value="{{ old('quantity', 1) }}" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm" required>
                        @error('quantity') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="reason" class="block text-sm font-black text-gray-700 mb-1">Motivo de Salida</label>
                        <select name="reason" id="reason" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm" required>
                            <option value="">Seleccione motivo...</option>
                            @foreach($reasons as $reason)
                                <option value="{{ $reason->value }}" {{ old('reason') == $reason->value ? 'selected' : '' }}>
                                    {{ $reason->label() }}
                                </option>
                            @endforeach
                        </select>
                        @error('reason') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div>
                    <label for="observations" class="block text-sm font-black text-gray-700 mb-1">Observaciones / Detalles</label>
                    <textarea name="observations" id="observations" rows="3" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm" placeholder="Ej: Se rompió al limpiar la estantería...">{{ old('observations') }}</textarea>
                    @error('observations') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>

                <div class="pt-4 border-t border-gray-100 flex justify-end gap-3">
                    <a href="{{ route('shift-product-outs.index') }}" class="px-4 py-2 text-sm font-bold text-gray-700 hover:bg-gray-50 rounded-lg transition-colors">
                        Cancelar
                    </a>
                    <button type="submit" class="px-6 py-2 text-sm font-black text-white bg-blue-600 hover:bg-blue-700 rounded-lg shadow-sm transition-colors">
                        Registrar Salida
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const productSelect = document.getElementById('product_id');
        const quantityInput = document.getElementById('quantity');

        productSelect.addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            const stock = selectedOption.getAttribute('data-stock');
            
            if (stock) {
                quantityInput.setAttribute('max', stock);
                if (parseInt(quantityInput.value) > parseInt(stock)) {
                    quantityInput.value = stock;
                }
            }
        });
    });
</script>
@endpush
@endsection

