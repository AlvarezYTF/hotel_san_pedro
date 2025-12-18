@extends('layouts.app')

@section('title', 'Inventario')
@section('header', 'Inventario de Productos')

@section('content')
<div class="space-y-4 sm:space-y-6">
    <!-- Header -->
    <div class="bg-white rounded-xl border border-gray-100 p-4 sm:p-6">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div class="flex items-center space-x-3 sm:space-x-4">
                <div class="p-2.5 sm:p-3 rounded-xl bg-blue-50 text-blue-600">
                    <i class="fas fa-boxes text-lg sm:text-xl"></i>
                    </div>
                    <div>
                    <h1 class="text-xl sm:text-2xl font-bold text-gray-900">Gestión de Inventario</h1>
                    <div class="flex items-center space-x-2 mt-1">
                        <span class="text-xs sm:text-sm text-gray-500">
                            <span class="font-semibold text-gray-900">{{ $products->total() }}</span> productos registrados
                            </span>
                        <span class="text-gray-300 hidden sm:inline">•</span>
                        <span class="text-xs sm:text-sm text-gray-500 hidden sm:inline">
                                <i class="fas fa-chart-line mr-1"></i> Panel de control
                            </span>
                    </div>
                </div>
            </div>
            
            @can('create_products')
            <a href="{{ route('products.create') }}" 
               class="inline-flex items-center justify-center px-4 sm:px-5 py-2.5 rounded-xl border-2 border-blue-600 bg-blue-600 text-white text-sm font-semibold hover:bg-blue-700 hover:border-blue-700 transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 shadow-sm hover:shadow-md">
                <i class="fas fa-plus mr-2"></i>
                <span>Nuevo Producto</span>
            </a>
            @endcan
        </div>
    </div>
    
    <!-- Filtros -->
    <div class="bg-white rounded-xl border border-gray-100 p-4 sm:p-6">
        <form method="GET" action="{{ route('products.index') }}" class="space-y-4">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <div>
                    <label for="search" class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-2">
                        Buscar
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fas fa-search text-gray-400 text-sm"></i>
                        </div>
                <input type="text" id="search" name="search" value="{{ request('search') }}" 
                               class="block w-full pl-10 pr-3 py-2.5 border border-gray-300 rounded-xl text-sm text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
                               placeholder="Nombre o SKU...">
                    </div>
            </div>
            
            <div>
                    <label for="category_id" class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-2">
                        Categoría
                    </label>
                    <div class="relative">
                        <select id="category_id" name="category_id"
                                class="block w-full pl-3 pr-10 py-2.5 border border-gray-300 rounded-xl text-sm text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent appearance-none bg-white">
                    <option value="">Todas las categorías</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}" {{ request('category_id') == $category->id ? 'selected' : '' }}>
                            {{ $category->name }}
                        </option>
                    @endforeach
                </select>
                        <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                            <i class="fas fa-chevron-down text-gray-400 text-xs"></i>
                        </div>
                    </div>
            </div>
            
            <div>
                    <label for="status" class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-2">
                        Estado
                    </label>
                    <div class="relative">
                        <select id="status" name="status"
                                class="block w-full pl-3 pr-10 py-2.5 border border-gray-300 rounded-xl text-sm text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent appearance-none bg-white">
                    <option value="">Todos los estados</option>
                    <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Activo</option>
                    <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactivo</option>
                    <option value="discontinued" {{ request('status') == 'discontinued' ? 'selected' : '' }}>Descontinuado</option>
                </select>
                        <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                            <i class="fas fa-chevron-down text-gray-400 text-xs"></i>
                        </div>
                    </div>
            </div>
            
            <div class="flex items-end">
                    <button type="submit"
                            class="w-full inline-flex items-center justify-center px-4 py-2.5 rounded-xl border-2 border-gray-200 bg-white text-gray-700 text-sm font-semibold hover:bg-gray-50 hover:border-gray-300 transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        <i class="fas fa-filter mr-2"></i>
                    Filtrar
                </button>
                </div>
            </div>
        </form>
    </div>
    
    <!-- Tabla de productos - Desktop -->
    <div class="hidden lg:block bg-white rounded-xl border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-100">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                            Producto
                        </th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                            Categoría
                        </th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                            Stock
                        </th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                            Precio
                        </th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                            Estado
                        </th>
                        <th class="px-6 py-4 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">
                            Acciones
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-100">
                    @forelse($products as $product)
                    <tr class="hover:bg-gray-50 transition-colors duration-150">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="h-10 w-10 rounded-lg bg-gray-100 flex items-center justify-center mr-3">
                                    <i class="fas fa-box text-gray-400 text-sm"></i>
                                </div>
                                <div>
                                    <div class="text-sm font-semibold text-gray-900">{{ $product->name }}</div>
                                </div>
                            </div>
                        </td>
                        
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold"
                                  data-color="{{ $product->category->color ?? '#6B7280' }}">
                                {{ $product->category->name ?? 'Sin categoría' }}
                            </span>
                        </td>
                        
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-semibold text-gray-900">
                                {{ $product->quantity }} unidades
                            </div>
                            @if($product->hasLowStock())
                                <div class="text-xs text-red-600 mt-1 font-medium">Stock bajo</div>
                            @elseif($product->quantity == 0)
                                <div class="text-xs text-red-600 mt-1 font-medium">Sin stock</div>
                            @endif
                        </td>
                        
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-semibold text-gray-900">${{ number_format($product->price, 2) }}</div>
                        </td>
                        
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($product->status == 'active')
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700">
                                    Activo
                                </span>
                            @elseif($product->status == 'inactive')
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-gray-100 text-gray-700">
                                    Inactivo
                                </span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-red-50 text-red-700">
                                    Descontinuado
                                </span>
                            @endif
                        </td>
                        
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <div class="flex items-center justify-end space-x-3">
                                @can('view_products')
                                <a href="{{ route('products.show', $product) }}"
                                   class="text-blue-600 hover:text-blue-700 transition-colors"
                                   title="Ver detalles">
                                    <i class="fas fa-eye"></i>
                                </a>
                                @endcan
                                
                                @can('edit_products')
                                <a href="{{ route('products.edit', $product) }}"
                                   class="text-indigo-600 hover:text-indigo-700 transition-colors"
                                   title="Editar">
                                    <i class="fas fa-edit"></i>
                                </a>
                                @endcan
                                
                                @can('delete_products')
                                <button type="button"
                                        onclick="openDeleteModal({{ $product->id }}, {{ json_encode($product->name) }}, {{ json_encode($product->sku) }})"
                                        class="text-red-600 hover:text-red-700 transition-colors"
                                        title="Eliminar">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                @endcan
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-12 text-center">
                            <div class="flex flex-col items-center">
                                <i class="fas fa-boxes text-4xl text-gray-300 mb-4"></i>
                                <p class="text-base font-semibold text-gray-500 mb-1">No se encontraron productos</p>
                                <p class="text-sm text-gray-400">Crea tu primer producto para comenzar</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <!-- Paginación Desktop -->
        @if($products->hasPages())
        <div class="bg-white px-6 py-4 border-t border-gray-100">
            {{ $products->appends(request()->query())->links() }}
        </div>
        @endif
    </div>
    
    <!-- Cards de productos - Mobile/Tablet -->
    <div class="lg:hidden space-y-4">
        @forelse($products as $product)
        <div class="bg-white rounded-xl border border-gray-100 p-4 hover:shadow-md transition-shadow duration-200">
            <div class="flex items-start justify-between mb-4">
                <div class="flex items-center space-x-3 flex-1 min-w-0">
                    <div class="h-12 w-12 rounded-xl bg-gray-100 flex items-center justify-center flex-shrink-0">
                        <i class="fas fa-box text-gray-400"></i>
                    </div>
                    <div class="min-w-0 flex-1">
                        <h3 class="text-sm font-semibold text-gray-900 truncate">{{ $product->name }}</h3>
                        <p class="text-xs text-gray-500 mt-0.5">{{ $product->sku }}</p>
                    </div>
                </div>
                
                <div class="flex items-center space-x-2 ml-2">
                    @can('view_products')
                    <a href="{{ route('products.show', $product) }}"
                       class="p-2 text-blue-600 hover:bg-blue-50 rounded-lg transition-colors"
                       title="Ver">
                        <i class="fas fa-eye text-sm"></i>
                    </a>
                    @endcan
                    
                    @can('edit_products')
                    <a href="{{ route('products.edit', $product) }}"
                       class="p-2 text-indigo-600 hover:bg-indigo-50 rounded-lg transition-colors"
                       title="Editar">
                        <i class="fas fa-edit text-sm"></i>
                    </a>
                    @endcan
                    
                    @can('delete_products')
                    <button type="button"
                            onclick="openDeleteModal({{ $product->id }}, {{ json_encode($product->name) }}, {{ json_encode($product->sku) }})"
                            class="p-2 text-red-600 hover:bg-red-50 rounded-lg transition-colors"
                            title="Eliminar">
                        <i class="fas fa-trash text-sm"></i>
                    </button>
                    @endcan
                </div>
            </div>
            
            <div class="grid grid-cols-2 gap-4 mb-4">
                <div>
                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">Categoría</p>
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold"
                          data-color="{{ $product->category->color ?? '#6B7280' }}">
                        {{ $product->category->name ?? 'Sin categoría' }}
                    </span>
                </div>
                
                <div>
                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">Estado</p>
                    @if($product->status == 'active')
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700">
                            Activo
                        </span>
                    @elseif($product->status == 'inactive')
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-gray-100 text-gray-700">
                            Inactivo
                        </span>
                    @else
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-red-50 text-red-700">
                            Descontinuado
                        </span>
                    @endif
                </div>
            </div>
            
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">Stock</p>
                    <p class="text-sm font-semibold text-gray-900">{{ $product->quantity }} disponible</p>
                    <div class="flex items-center space-x-2 mt-1">
                        <span class="text-xs text-gray-500">Inicial: <span class="font-medium">{{ $product->initial_stock }}</span></span>
                        <span class="text-xs text-emerald-600">Vendido: <span class="font-medium">{{ $product->sold_quantity ?? 0 }}</span></span>
                    </div>
                    @if($product->hasLowStock())
                        <p class="text-xs text-red-600 font-medium mt-1">Stock bajo</p>
                    @elseif($product->quantity == 0)
                        <p class="text-xs text-red-600 font-medium mt-1">Sin stock</p>
                    @endif
                </div>
                
                <div>
                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">Precio</p>
                    <p class="text-sm font-semibold text-gray-900">${{ number_format($product->price, 2) }}</p>
                    @if($product->cost_price)
                        <p class="text-xs text-gray-500 mt-1">Costo: ${{ number_format($product->cost_price, 2) }}</p>
                    @endif
                </div>
            </div>
        </div>
        @empty
        <div class="bg-white rounded-xl border border-gray-100 p-12 text-center">
            <i class="fas fa-boxes text-4xl text-gray-300 mb-4"></i>
            <p class="text-base font-semibold text-gray-500 mb-1">No se encontraron productos</p>
            <p class="text-sm text-gray-400">Crea tu primer producto para comenzar</p>
        </div>
        @endforelse
        
        <!-- Paginación Mobile -->
        @if($products->hasPages())
        <div class="bg-white rounded-xl border border-gray-100 p-4">
            {{ $products->appends(request()->query())->links() }}
        </div>
        @endif
    </div>
</div>

<!-- Modal de Confirmación de Eliminación -->
<div id="delete-modal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50" style="display: none;">
    <div class="relative top-10 sm:top-20 mx-auto p-4 sm:p-6 border w-11/12 sm:w-96 shadow-xl rounded-xl bg-white max-h-[90vh] overflow-y-auto">
        <div class="mt-3">
            <!-- Header del modal -->
            <div class="flex items-center justify-between mb-4 pb-4 border-b border-gray-200">
                <div class="flex items-center space-x-3">
                    <div class="p-2.5 rounded-xl bg-red-50 text-red-600">
                        <i class="fas fa-exclamation-triangle text-lg"></i>
                    </div>
                    <h3 class="text-lg sm:text-xl font-bold text-gray-900">Eliminar Producto</h3>
                </div>
                <button type="button" onclick="closeDeleteModal()"
                        class="text-gray-400 hover:text-gray-600 focus:outline-none transition-colors">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>

            <!-- Contenido del modal -->
            <div class="mb-6">
                <p class="text-sm text-gray-600 mb-4">
                    ¿Estás seguro de que deseas eliminar este producto? Esta acción no se puede deshacer.
                </p>
                
                <div class="bg-red-50 border border-red-200 rounded-xl p-4">
                    <div class="flex items-start space-x-3">
                        <div class="p-2 rounded-lg bg-red-100 text-red-600 flex-shrink-0">
                            <i class="fas fa-box text-sm"></i>
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="text-sm font-semibold text-gray-900 mb-1" id="delete-product-name"></div>
                            <div class="text-xs text-gray-500" id="delete-product-sku"></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Footer del modal -->
            <form id="delete-form" method="POST" class="inline">
                @csrf
                @method('DELETE')
                <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-end gap-3 pt-4 border-t border-gray-200">
                    <button type="button" onclick="closeDeleteModal()"
                            class="inline-flex items-center justify-center px-4 sm:px-5 py-2.5 rounded-xl border-2 border-gray-200 bg-white text-gray-700 text-sm font-semibold hover:bg-gray-50 hover:border-gray-300 transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500">
                        <i class="fas fa-times mr-2"></i>
                        Cancelar
                    </button>

                    <button type="submit"
                            class="inline-flex items-center justify-center px-4 sm:px-5 py-2.5 rounded-xl border-2 border-red-600 bg-red-600 text-white text-sm font-semibold hover:bg-red-700 hover:border-red-700 transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 shadow-sm hover:shadow-md">
                        <i class="fas fa-trash mr-2"></i>
                        Eliminar Producto
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Aplicar colores a las etiquetas de categoría
    document.querySelectorAll('[data-color]').forEach(function(element) {
        const color = element.getAttribute('data-color');
        if (color) {
        element.style.backgroundColor = color + '20';
        element.style.color = color;
        }
    });
});

function openDeleteModal(productId, productName, productSku) {
    const modal = document.getElementById('delete-modal');
    const form = document.getElementById('delete-form');
    const nameElement = document.getElementById('delete-product-name');
    const skuElement = document.getElementById('delete-product-sku');
    
    // Establecer la acción del formulario
    form.action = '{{ route("products.destroy", ":id") }}'.replace(':id', productId);
    
    // Establecer el nombre y SKU del producto
    nameElement.textContent = productName;
    skuElement.textContent = 'SKU: ' + productSku;
    
    // Mostrar el modal
    modal.classList.remove('hidden');
    modal.style.display = 'block';
    document.body.style.overflow = 'hidden';
}

function closeDeleteModal() {
    const modal = document.getElementById('delete-modal');
    modal.classList.add('hidden');
    modal.style.display = 'none';
    document.body.style.overflow = 'auto';
}

// Cerrar modal al hacer clic fuera
document.getElementById('delete-modal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeDeleteModal();
    }
});

// Cerrar modal con ESC
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        const modal = document.getElementById('delete-modal');
        if (!modal.classList.contains('hidden')) {
            closeDeleteModal();
        }
    }
});
</script>
@endpush
@endsection
