@extends('layouts.app')

@section('title', $category->name)
@section('header', $category->name)

@section('content')
<div class="max-w-7xl mx-auto space-y-4 sm:space-y-6">
    <!-- Header -->
    <div class="bg-white rounded-xl border border-gray-100 p-4 sm:p-6">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
            <div class="flex items-start space-x-3 sm:space-x-4 flex-1 min-w-0">
                <div class="p-3 sm:p-4 rounded-xl shadow-sm border border-gray-200 flex-shrink-0"
                     data-color="{{ $category->color }}">
                    <i class="fas fa-tag text-2xl sm:text-3xl"></i>
                </div>
                <div class="flex-1 min-w-0">
                    <h1 class="text-xl sm:text-2xl lg:text-3xl font-bold text-gray-900 truncate">{{ $category->name }}</h1>
                    <p class="text-sm text-gray-600 mt-1">Gestiona las subcategorías y productos template de esta categoría</p>
                </div>
            </div>
            
            <div class="flex items-center gap-3">
                <a href="{{ route('categories.index') }}"
                   class="inline-flex items-center justify-center px-4 sm:px-5 py-2.5 rounded-xl border-2 border-gray-200 bg-white text-gray-700 text-sm font-semibold hover:bg-gray-50 hover:border-gray-300 transition-all duration-200">
                    <i class="fas fa-arrow-left mr-2"></i>
                    ← Regresar
                </a>
            </div>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="bg-white rounded-xl border border-gray-100 p-4 sm:p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600 mb-1">Total Subcategorías</p>
                    <p class="text-3xl font-bold text-blue-600">{{ $subcategories->count() }}</p>
                </div>
                <div class="p-3 rounded-xl bg-blue-50 text-blue-600">
                    <i class="fas fa-folder text-2xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl border border-gray-100 p-4 sm:p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600 mb-1">Total Productos</p>
                    <p class="text-3xl font-bold text-green-600">{{ $subcategories->sum('products_count') }}</p>
                </div>
                <div class="p-3 rounded-xl bg-green-50 text-green-600">
                    <i class="fas fa-box text-2xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl border border-gray-100 p-4 sm:p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600 mb-1">Subcategoría Activa</p>
                    <p class="text-lg font-bold text-blue-600" id="active-subcategory">Ninguna</p>
                </div>
                <div class="p-3 rounded-xl bg-blue-50 text-blue-600">
                    <i class="fas fa-bullseye text-2xl"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content: Two Panels -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 sm:gap-6">
        <!-- Left Panel: Subcategorías -->
        <div class="bg-white rounded-xl border border-gray-100 p-4 sm:p-6">
            <div class="mb-4">
                <h2 class="text-lg font-bold text-gray-900 mb-1">Subcategorías</h2>
                <p class="text-sm text-gray-600">Selecciona una subcategoría para ver sus productos</p>
            </div>

            <!-- Search Bar -->
            <div class="mb-4">
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i class="fas fa-search text-gray-400 text-sm"></i>
                    </div>
                    <input type="text" 
                           id="search-subcategory" 
                           class="block w-full pl-10 pr-3 py-2.5 border border-gray-300 rounded-xl text-sm text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                           placeholder="Buscar subcategoría...">
                </div>
            </div>

            <!-- Subcategories List -->
            <div class="space-y-3 max-h-[600px] overflow-y-auto" id="subcategories-list">
                @foreach($subcategories as $subcategory)
                <div class="subcategory-item p-4 border border-gray-200 rounded-xl hover:border-blue-300 hover:shadow-md transition-all cursor-pointer" 
                     data-subcategory-id="{{ $subcategory->id }}"
                     data-subcategory-name="{{ $subcategory->name }}">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-3 flex-1 min-w-0">
                            <div class="h-12 w-12 rounded-xl bg-gray-50 flex items-center justify-center flex-shrink-0"
                                 data-color="{{ $subcategory->color }}">
                                <i class="fas fa-tag text-gray-400"></i>
                            </div>
                            <div class="flex-1 min-w-0">
                                <h3 class="text-sm font-semibold text-gray-900 truncate">{{ $subcategory->name }}</h3>
                                <p class="text-xs text-gray-500 mt-0.5">{{ strtolower($subcategory->name) }}</p>
                                <p class="text-xs text-gray-600 mt-1">{{ $subcategory->products_count ?? 0 }} Productos</p>
                            </div>
                        </div>
                        <button type="button" 
                                class="manage-btn ml-3 px-4 py-2 bg-blue-600 text-white text-xs font-semibold rounded-lg hover:bg-blue-700 transition-colors flex items-center space-x-2"
                                data-subcategory-id="{{ $subcategory->id }}"
                                data-subcategory-name="{{ $subcategory->name }}">
                            <i class="fas fa-bullseye"></i>
                            <span>Gestionar</span>
                        </button>
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        <!-- Right Panel: Productos Template -->
        <div class="bg-white rounded-xl border border-gray-100 p-4 sm:p-6">
            <div class="mb-4">
                <h2 class="text-lg font-bold text-gray-900 mb-1">Productos Template</h2>
                <p class="text-sm text-gray-600">Lista de productos preset de la subcategoría seleccionada</p>
            </div>

            <!-- Products Container -->
            <div id="products-container" class="min-h-[400px]">
                <!-- Empty State -->
                <div id="empty-state" class="flex flex-col items-center justify-center py-12 text-center">
                    <div class="h-16 w-16 rounded-full bg-gray-50 flex items-center justify-center mb-4">
                        <i class="fas fa-info-circle text-2xl text-gray-300"></i>
                    </div>
                    <p class="text-sm font-semibold text-gray-500 mb-1">Selecciona una subcategoría</p>
                    <p class="text-xs text-gray-400">Haz clic en "Gestionar" de una subcategoría para ver sus productos</p>
                </div>

                <!-- Loading State -->
                <div id="loading-state" class="hidden flex flex-col items-center justify-center py-12">
                    <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600 mb-4"></div>
                    <p class="text-sm text-gray-600">Cargando productos...</p>
                </div>

                <!-- Products List -->
                <div id="products-list" class="hidden space-y-3"></div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Apply colors to category icons
    document.querySelectorAll('[data-color]').forEach(function(element) {
        const color = element.getAttribute('data-color');
        if (color && element.querySelector('i')) {
            element.style.backgroundColor = color + '20';
            element.querySelector('i').style.color = color;
        }
    });

    // Search functionality
    const searchInput = document.getElementById('search-subcategory');
    const subcategoriesList = document.getElementById('subcategories-list');
    
    searchInput.addEventListener('input', function(e) {
        const searchTerm = e.target.value.toLowerCase();
        const items = subcategoriesList.querySelectorAll('.subcategory-item');
        
        items.forEach(function(item) {
            const name = item.getAttribute('data-subcategory-name').toLowerCase();
            if (name.includes(searchTerm)) {
                item.style.display = 'block';
            } else {
                item.style.display = 'none';
            }
        });
    });

    // Manage button click handler
    document.querySelectorAll('.manage-btn').forEach(function(btn) {
        btn.addEventListener('click', function(e) {
            e.stopPropagation();
            const subcategoryId = this.getAttribute('data-subcategory-id');
            const subcategoryName = this.getAttribute('data-subcategory-name');
            
            loadProducts(subcategoryId, subcategoryName);
        });
    });

    function loadProducts(subcategoryId, subcategoryName) {
        // Update active subcategory
        document.getElementById('active-subcategory').textContent = subcategoryName;
        
        // Show loading state
        document.getElementById('empty-state').classList.add('hidden');
        document.getElementById('products-list').classList.add('hidden');
        document.getElementById('loading-state').classList.remove('hidden');
        
        // Remove active state from all items
        document.querySelectorAll('.subcategory-item').forEach(function(item) {
            item.classList.remove('border-blue-500', 'bg-blue-50');
        });
        
        // Add active state to selected item
        const selectedItem = document.querySelector(`[data-subcategory-id="${subcategoryId}"]`);
        if (selectedItem) {
            selectedItem.classList.add('border-blue-500', 'bg-blue-50');
        }
        
        // Fetch products - Build URL dynamically based on current path
        const isAdminRoute = window.location.pathname.includes('/admin/');
        const baseUrl = isAdminRoute 
            ? '/admin/categories/{{ $category->id }}/subcategories/' 
            : '/categories/{{ $category->id }}/subcategories/';
        const url = baseUrl + subcategoryId + '/products';
        
        fetch(url, {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
            },
        })
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            document.getElementById('loading-state').classList.add('hidden');
            
            if (data.success && data.products && data.products.length > 0) {
                displayProducts(data.products);
            } else {
                showEmptyState('No hay productos en esta subcategoría');
            }
        })
        .catch(error => {
            console.error('Error loading products:', error);
            console.error('URL attempted:', url);
            document.getElementById('loading-state').classList.add('hidden');
            showEmptyState('Error al cargar los productos. Revisa la consola para más detalles.');
        });
    }

    function displayProducts(products) {
        const productsList = document.getElementById('products-list');
        productsList.innerHTML = '';
        
        products.forEach(function(product) {
            const productCard = document.createElement('div');
            productCard.className = 'p-4 border border-gray-200 rounded-xl hover:border-gray-300 hover:shadow-sm transition-all';
            productCard.innerHTML = `
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-3 flex-1 min-w-0">
                        <div class="h-10 w-10 rounded-xl bg-gray-50 flex items-center justify-center flex-shrink-0">
                            <i class="fas fa-box text-gray-400 text-sm"></i>
                        </div>
                        <div class="flex-1 min-w-0">
                            <h3 class="text-sm font-semibold text-gray-900 truncate">${product.name}</h3>
                            <p class="text-xs text-gray-500 mt-0.5">SKU: ${product.sku}</p>
                        </div>
                    </div>
                    <div class="flex items-center space-x-4 ml-3">
                        <div class="text-right">
                            <p class="text-sm font-semibold text-gray-900">${product.quantity} unidades</p>
                            <p class="text-xs text-gray-500">$${parseFloat(product.price).toFixed(2)}</p>
                        </div>
                        ${product.low_stock ? '<span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-semibold bg-amber-50 text-amber-700"><i class="fas fa-exclamation-triangle mr-1"></i>Bajo</span>' : ''}
                    </div>
                </div>
            `;
            productsList.appendChild(productCard);
        });
        
        document.getElementById('products-list').classList.remove('hidden');
    }

    function showEmptyState(message) {
        const emptyState = document.getElementById('empty-state');
        emptyState.querySelector('p:first-of-type').textContent = message;
        emptyState.classList.remove('hidden');
    }
});
</script>
@endpush
@endsection
