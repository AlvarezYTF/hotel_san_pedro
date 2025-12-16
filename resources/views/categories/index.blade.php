@extends('layouts.app')

@section('title', 'Panel de Categorías')
@section('header', 'Panel de Categorías')

@section('content')
<div class="max-w-7xl mx-auto space-y-4 sm:space-y-6">
    <!-- Header -->
    <div class="bg-white rounded-xl border border-gray-100 p-4 sm:p-6">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div class="flex items-center space-x-3 sm:space-x-4">
                <div class="p-2.5 sm:p-3 rounded-xl bg-violet-50 text-violet-600">
                    <i class="fas fa-tags text-lg sm:text-xl"></i>
                    </div>
                    <div>
                    <h1 class="text-xl sm:text-2xl font-bold text-gray-900">Gestión de Categorías</h1>
                    <div class="flex items-center space-x-2 mt-1">
                        <span class="text-xs sm:text-sm text-gray-500">
                            <span class="font-semibold text-gray-900">{{ $categories->total() }}</span> categorías registradas
                            </span>
                        <span class="text-gray-300 hidden sm:inline">•</span>
                        <span class="text-xs sm:text-sm text-gray-500 hidden sm:inline">
                                <i class="fas fa-chart-line mr-1"></i> Panel de administración
                            </span>
                    </div>
                </div>
            </div>
            
            @can('create_categories')
            <a href="{{ route('categories.create') }}" 
               class="inline-flex items-center justify-center px-4 sm:px-5 py-2.5 rounded-xl border-2 border-violet-600 bg-violet-600 text-white text-sm font-semibold hover:bg-violet-700 hover:border-violet-700 transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-violet-500 shadow-sm hover:shadow-md">
                <i class="fas fa-plus mr-2"></i>
                <span>+ Crear Categoría</span>
            </a>
            @endcan
        </div>
    </div>

    <!-- Summary Statistics -->
    @php
        $totalCategories = $categories->total();
        $activeCategories = $categories->where('is_active', true)->count();
        $inactiveCategories = $categories->where('is_active', false)->count();
    @endphp
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="bg-white rounded-xl border border-gray-100 p-4 sm:p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600 mb-1">Total Categorías</p>
                    <p class="text-3xl font-bold text-blue-600">{{ $totalCategories }}</p>
                </div>
                <div class="p-3 rounded-xl bg-blue-50 text-blue-600">
                    <i class="fas fa-folder text-2xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl border border-gray-100 p-4 sm:p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600 mb-1">Categorías Activas</p>
                    <p class="text-3xl font-bold text-green-600">{{ $activeCategories }}</p>
                </div>
                <div class="p-3 rounded-xl bg-green-50 text-green-600">
                    <i class="fas fa-check-circle text-2xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl border border-gray-100 p-4 sm:p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600 mb-1">Categorías Inactivas</p>
                    <p class="text-3xl font-bold text-red-600">{{ $inactiveCategories }}</p>
                </div>
                <div class="p-3 rounded-xl bg-red-50 text-red-600">
                    <i class="fas fa-times-circle text-2xl"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Filtros -->
    <div class="bg-white rounded-xl border border-gray-100 p-4 sm:p-6">
        <form method="GET" action="{{ route('categories.index') }}" class="space-y-4">
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div>
                    <label for="search" class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-2">
                        Buscar
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fas fa-search text-gray-400 text-sm"></i>
                        </div>
                <input type="text" id="search" name="search" value="{{ request('search') }}" 
                               class="block w-full pl-10 pr-3 py-2.5 border border-gray-300 rounded-xl text-sm text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-violet-500 focus:border-transparent transition-all"
                               placeholder="Nombre o descripción...">
                    </div>
            </div>
            
            <div>
                    <label for="status" class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-2">
                        Estado
                    </label>
                    <div class="relative">
                        <select id="status" name="status"
                                class="block w-full pl-3 sm:pl-4 pr-10 py-2.5 border border-gray-300 rounded-xl text-sm text-gray-900 focus:outline-none focus:ring-2 focus:ring-violet-500 focus:border-transparent appearance-none bg-white">
                    <option value="">Todos los estados</option>
                    <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Activo</option>
                    <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactivo</option>
                </select>
                        <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                            <i class="fas fa-chevron-down text-gray-400 text-xs"></i>
                        </div>
                    </div>
            </div>
            
            <div class="flex items-end">
                    <button type="submit"
                            class="w-full inline-flex items-center justify-center px-4 py-2.5 rounded-xl border-2 border-gray-200 bg-white text-gray-700 text-sm font-semibold hover:bg-gray-50 hover:border-gray-300 transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-violet-500">
                        <i class="fas fa-filter mr-2"></i>
                    Filtrar
                </button>
                </div>
            </div>
        </form>
    </div>
    
    <!-- Tabla de categorías - Desktop -->
    <div class="hidden lg:block bg-white rounded-xl border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-100">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                            Categoría
                        </th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                            Descripción
                        </th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                            Color
                        </th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                            Productos
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
                    @forelse($categories as $category)
                    <tr class="hover:bg-gray-50 transition-colors duration-150">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="h-10 w-10 rounded-xl flex items-center justify-center mr-3"
                                     data-color="{{ $category->color }}">
                                    <i class="fas fa-tag text-sm"></i>
                                </div>
                                <div>
                                    <div class="text-sm font-semibold text-gray-900">{{ $category->name }}</div>
                                    <div class="text-xs text-gray-500 mt-0.5">ID: {{ $category->id }}</div>
                                </div>
                            </div>
                        </td>
                        
                        <td class="px-6 py-4">
                            <div class="text-sm text-gray-700 max-w-md">
                                {{ $category->description ? Str::limit($category->description, 60) : 'Sin descripción' }}
                            </div>
                        </td>
                        
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center space-x-2">
                                <div class="h-6 w-6 rounded-full border-2 border-gray-200 shadow-sm"
                                     data-color-circle="{{ $category->color }}"></div>
                                <span class="text-xs font-mono text-gray-600">{{ $category->color }}</span>
                            </div>
                        </td>
                        
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="inline-flex items-center px-2.5 py-1 rounded-full bg-gray-100 text-gray-700 text-sm font-semibold">
                                {{ $category->products_count ?? $category->products->count() ?? 0 }}
                            </span>
                        </td>
                        
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($category->is_active)
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700">
                                    <i class="fas fa-check-circle mr-1.5"></i>
                                    Activo
                                </span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-gray-100 text-gray-700">
                                    <i class="fas fa-times-circle mr-1.5"></i>
                                    Inactivo
                                </span>
                            @endif
                        </td>
                        
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <div class="flex items-center justify-end space-x-3">
                                @can('view_categories')
                                <a href="{{ route('categories.show', $category) }}"
                                   class="text-blue-600 hover:text-blue-700 transition-colors"
                                   title="Ver detalles">
                                    <i class="fas fa-eye"></i>
                                </a>
                                @endcan
                                
                                @can('edit_categories')
                                <a href="{{ route('categories.edit', $category) }}"
                                   class="text-indigo-600 hover:text-indigo-700 transition-colors"
                                   title="Editar">
                                    <i class="fas fa-edit"></i>
                                </a>
                                @endcan
                                
                                @can('delete_categories')
                                <button type="button"
                                        onclick="openDeleteModal({{ $category->id }}, {{ json_encode($category->name) }})"
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
                                <i class="fas fa-tags text-4xl text-gray-300 mb-4"></i>
                                <p class="text-base font-semibold text-gray-500 mb-1">No se encontraron categorías</p>
                                <p class="text-sm text-gray-400">Crea tu primera categoría para comenzar</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
            @forelse($categories as $category)
            <div class="border border-gray-200 rounded-xl p-4 hover:shadow-md transition-shadow duration-200 relative">
                <!-- Status indicator -->
                @if($category->is_active)
                    <div class="absolute top-3 right-3 h-3 w-3 rounded-full bg-green-500"></div>
                @else
                    <div class="absolute top-3 right-3 h-3 w-3 rounded-full bg-gray-400"></div>
                @endif

                <div class="flex items-start space-x-3 mb-3">
                    <div class="h-12 w-12 rounded-xl flex items-center justify-center flex-shrink-0"
                         data-color="{{ $category->color }}">
                        <i class="fas fa-tag text-gray-400"></i>
                    </div>
                    <div class="flex-1 min-w-0">
                        <h3 class="text-sm font-semibold text-gray-900 truncate">{{ $category->name }}</h3>
                    </div>
                </div>

                <div class="flex items-center justify-between mb-3">
                    <div class="flex items-center space-x-1 text-sm text-gray-600">
                        <i class="fas fa-folder text-xs"></i>
                        <span>{{ $category->products_count ?? 0 }}</span>
                    </div>
                </div>

                <a href="{{ url('/categories/' . $category->id) }}"
                   class="w-full inline-flex items-center justify-center px-4 py-2 bg-blue-600 text-white text-xs font-semibold rounded-lg hover:bg-blue-700 transition-colors">
                    <i class="fas fa-bullseye mr-2"></i>
                    Gestionar
                </a>
            </div>
            @empty
            <div class="col-span-full bg-white rounded-xl border border-gray-100 p-12 text-center">
                <i class="fas fa-tags text-4xl text-gray-300 mb-4"></i>
                <p class="text-base font-semibold text-gray-500 mb-1">No se encontraron categorías</p>
                <p class="text-sm text-gray-400">Crea tu primera categoría para comenzar</p>
            </div>
            @endforelse
        </div>

        <!-- Paginación -->
        @if($categories->hasPages())
        <div class="mt-6 pt-6 border-t border-gray-100">
            {{ $categories->appends(request()->query())->links() }}
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
                    <h3 class="text-lg sm:text-xl font-bold text-gray-900">Eliminar Categoría</h3>
                </div>
                <button type="button" onclick="closeDeleteModal()"
                        class="text-gray-400 hover:text-gray-600 focus:outline-none transition-colors">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>

            <!-- Contenido del modal -->
            <div class="mb-6">
                <p class="text-sm text-gray-600 mb-4">
                    ¿Estás seguro de que deseas eliminar esta categoría? Esta acción no se puede deshacer.
                </p>
                
                <div class="bg-red-50 border border-red-200 rounded-xl p-4">
                    <div class="flex items-start space-x-3">
                        <div class="p-2 rounded-lg bg-red-100 text-red-600 flex-shrink-0">
                            <i class="fas fa-tag text-sm"></i>
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="text-sm font-semibold text-gray-900 mb-1" id="delete-category-name"></div>
                            <div class="text-xs text-gray-500">Los productos asociados no se eliminarán</div>
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
                        Eliminar Categoría
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Aplicar colores a los íconos de categorías
    document.querySelectorAll('[data-color]').forEach(function(element) {
        const color = element.getAttribute('data-color');
        if (color && element.querySelector('i')) {
            element.style.backgroundColor = color + '20';
            element.querySelector('i').style.color = color;
        }
    });
    
    // Aplicar colores a los círculos de color
    document.querySelectorAll('[data-color-circle]').forEach(function(element) {
        const color = element.getAttribute('data-color-circle');
        if (color) {
            element.style.backgroundColor = color;
        }
    });
});

function openDeleteModal(categoryId, categoryName) {
    const modal = document.getElementById('delete-modal');
    const form = document.getElementById('delete-form');
    const nameElement = document.getElementById('delete-category-name');
    
    // Establecer la acción del formulario
    form.action = '{{ route("categories.destroy", ":id") }}'.replace(':id', categoryId);
    
    // Establecer el nombre de la categoría
    nameElement.textContent = categoryName;
    
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
