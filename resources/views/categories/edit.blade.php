@extends('layouts.app')

@section('title', 'Editar Categoría')
@section('header', 'Editar Categoría')

@section('content')
<div class="max-w-4xl mx-auto space-y-4 sm:space-y-6">
    <!-- Header -->
    <div class="bg-white rounded-xl border border-gray-100 p-4 sm:p-6">
        <div class="flex items-center space-x-3 sm:space-x-4">
            <div class="p-2.5 sm:p-3 rounded-xl bg-violet-50 text-violet-600">
                <i class="fas fa-edit text-lg sm:text-xl"></i>
            </div>
            <div>
                <h1 class="text-xl sm:text-2xl font-bold text-gray-900">Editar Categoría</h1>
                <p class="text-xs sm:text-sm text-gray-500 mt-1">Modifica la información y personalización de "{{ $category->name }}"</p>
            </div>
        </div>
    </div>

    <form method="POST" action="{{ route('categories.update', $category) }}" id="category-form" x-data="{ loading: false }" @submit="loading = true">
        @csrf
        @method('PUT')

        <!-- Información Básica -->
        <div class="bg-white rounded-xl border border-gray-100 p-4 sm:p-6">
            <div class="flex items-center space-x-2 sm:space-x-3 mb-4 sm:mb-6">
                <div class="p-2 rounded-xl bg-blue-50 text-blue-600">
                    <i class="fas fa-info text-sm"></i>
                </div>
                <h2 class="text-base sm:text-lg font-semibold text-gray-900">Información Básica</h2>
            </div>

            <div class="space-y-5 sm:space-y-6">
                <!-- Nombre -->
                <div>
                    <label for="name" class="block text-xs sm:text-sm font-semibold text-gray-700 mb-2">
                        Nombre de la categoría <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 sm:pl-4 flex items-center pointer-events-none">
                            <i class="fas fa-tag text-gray-400 text-sm"></i>
                        </div>
                        <input type="text"
                               id="name"
                               name="name"
                               value="{{ old('name', $category->name) }}"
                               class="block w-full pl-10 sm:pl-11 pr-3 sm:pr-4 py-2.5 border border-gray-300 rounded-xl text-sm text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-violet-500 focus:border-transparent transition-all @error('name') border-red-300 focus:ring-red-500 @enderror"
                               placeholder="Ej: Smartphones, Accesorios, Reparaciones..."
                               required>
                    </div>
                    <p class="mt-1.5 text-xs text-gray-500">
                        Nombre descriptivo que identifique el tipo de productos
                    </p>
                    @error('name')
                        <p class="mt-1.5 text-xs text-red-600 flex items-center">
                            <i class="fas fa-exclamation-circle mr-1.5"></i>
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                <!-- Descripción -->
                <div>
                    <label for="description" class="block text-xs sm:text-sm font-semibold text-gray-700 mb-2">
                        Descripción
                    </label>
                    <div class="relative">
                        <textarea id="description"
                                  name="description"
                                  rows="3"
                                  class="block w-full px-3 sm:px-4 py-2.5 border border-gray-300 rounded-xl text-sm text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-violet-500 focus:border-transparent transition-all resize-none @error('description') border-red-300 focus:ring-red-500 @enderror"
                                  placeholder="Descripción opcional de la categoría...">{{ old('description', $category->description) }}</textarea>
                    </div>
                    <p class="mt-1.5 text-xs text-gray-500">
                        Información adicional sobre qué productos incluye esta categoría
                    </p>
                    @error('description')
                        <p class="mt-1.5 text-xs text-red-600 flex items-center">
                            <i class="fas fa-exclamation-circle mr-1.5"></i>
                            {{ $message }}
                        </p>
                    @enderror
                </div>
            </div>
        </div>

        <!-- Personalización Visual -->
        <div class="bg-white rounded-xl border border-gray-100 p-4 sm:p-6">
            <div class="flex items-center space-x-2 sm:space-x-3 mb-4 sm:mb-6">
                <div class="p-2 rounded-xl bg-violet-50 text-violet-600">
                    <i class="fas fa-palette text-sm"></i>
                </div>
                <h2 class="text-base sm:text-lg font-semibold text-gray-900">Personalización Visual</h2>
            </div>

            <div class="space-y-4 sm:space-y-5">
                <div>
                    <label for="color" class="block text-xs sm:text-sm font-semibold text-gray-700 mb-2">
                        Color de la categoría <span class="text-red-500">*</span>
                    </label>

                    <!-- Colores predefinidos -->
                    <div class="mb-4">
                        <p class="text-xs text-gray-500 mb-3">Colores sugeridos:</p>
                        <div class="grid grid-cols-6 sm:grid-cols-8 gap-2 sm:gap-3">
                            <button type="button" class="color-option h-10 sm:h-12 w-10 sm:w-12 rounded-xl border-2 border-gray-200 hover:border-gray-400 hover:shadow-md transition-all focus:outline-none focus:ring-2 focus:ring-violet-500 focus:ring-offset-2" data-color="#3B82F6" style="background-color: #3B82F6;" title="Azul"></button>
                            <button type="button" class="color-option h-10 sm:h-12 w-10 sm:w-12 rounded-xl border-2 border-gray-200 hover:border-gray-400 hover:shadow-md transition-all focus:outline-none focus:ring-2 focus:ring-violet-500 focus:ring-offset-2" data-color="#10B981" style="background-color: #10B981;" title="Verde"></button>
                            <button type="button" class="color-option h-10 sm:h-12 w-10 sm:w-12 rounded-xl border-2 border-gray-200 hover:border-gray-400 hover:shadow-md transition-all focus:outline-none focus:ring-2 focus:ring-violet-500 focus:ring-offset-2" data-color="#F59E0B" style="background-color: #F59E0B;" title="Amarillo"></button>
                            <button type="button" class="color-option h-10 sm:h-12 w-10 sm:w-12 rounded-xl border-2 border-gray-200 hover:border-gray-400 hover:shadow-md transition-all focus:outline-none focus:ring-2 focus:ring-violet-500 focus:ring-offset-2" data-color="#EF4444" style="background-color: #EF4444;" title="Rojo"></button>
                            <button type="button" class="color-option h-10 sm:h-12 w-10 sm:w-12 rounded-xl border-2 border-gray-200 hover:border-gray-400 hover:shadow-md transition-all focus:outline-none focus:ring-2 focus:ring-violet-500 focus:ring-offset-2" data-color="#8B5CF6" style="background-color: #8B5CF6;" title="Púrpura"></button>
                            <button type="button" class="color-option h-10 sm:h-12 w-10 sm:w-12 rounded-xl border-2 border-gray-200 hover:border-gray-400 hover:shadow-md transition-all focus:outline-none focus:ring-2 focus:ring-violet-500 focus:ring-offset-2" data-color="#EC4899" style="background-color: #EC4899;" title="Rosa"></button>
                            <button type="button" class="color-option h-10 sm:h-12 w-10 sm:w-12 rounded-xl border-2 border-gray-200 hover:border-gray-400 hover:shadow-md transition-all focus:outline-none focus:ring-2 focus:ring-violet-500 focus:ring-offset-2" data-color="#6B7280" style="background-color: #6B7280;" title="Gris"></button>
                            <button type="button" class="color-option h-10 sm:h-12 w-10 sm:w-12 rounded-xl border-2 border-gray-200 hover:border-gray-400 hover:shadow-md transition-all focus:outline-none focus:ring-2 focus:ring-violet-500 focus:ring-offset-2" data-color="#14B8A6" style="background-color: #14B8A6;" title="Cyan"></button>
                        </div>
                    </div>

                    <!-- Selector personalizado -->
                    <div class="flex flex-col sm:flex-row items-start sm:items-center gap-3 sm:gap-4">
                        <div class="relative">
                            <input type="color"
                                   id="color"
                                   name="color"
                                   value="{{ old('color', $category->color) }}"
                                   class="h-12 w-16 sm:w-20 rounded-xl border-2 border-gray-300 cursor-pointer focus:outline-none focus:ring-2 focus:ring-violet-500 focus:ring-offset-2 transition-all @error('color') border-red-300 @enderror">
                        </div>
                        <div class="flex-1 w-full sm:w-auto">
                            <input type="text"
                                   id="color_text"
                                   value="{{ old('color', $category->color) }}"
                                   class="block w-full sm:w-auto px-3 sm:px-4 py-2.5 border border-gray-300 rounded-xl text-sm text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-violet-500 focus:border-transparent transition-all font-mono @error('color') border-red-300 focus:ring-red-500 @enderror"
                                   placeholder="#3B82F6"
                                   pattern="^#[0-9A-Fa-f]{6}$"
                                   required>
                        </div>
                    </div>

                    <p class="mt-2 text-xs text-gray-500">
                        El color se usará para identificar la categoría en listas y formularios
                    </p>
                    @error('color')
                        <p class="mt-1.5 text-xs text-red-600 flex items-center">
                            <i class="fas fa-exclamation-circle mr-1.5"></i>
                            {{ $message }}
                        </p>
                    @enderror
                </div>
            </div>
        </div>

        <!-- Configuración -->
        <div class="bg-white rounded-xl border border-gray-100 p-4 sm:p-6">
            <div class="flex items-center space-x-2 sm:space-x-3 mb-4 sm:mb-6">
                <div class="p-2 rounded-xl bg-emerald-50 text-emerald-600">
                    <i class="fas fa-toggle-on text-sm"></i>
                </div>
                <h2 class="text-base sm:text-lg font-semibold text-gray-900">Configuración</h2>
            </div>

            <div class="bg-gray-50 rounded-xl p-4 sm:p-5 border border-gray-200">
                <label class="inline-flex items-center cursor-pointer">
                    <input type="checkbox"
                           name="is_active"
                           value="1"
                           {{ old('is_active', $category->is_active) ? 'checked' : '' }}
                           class="h-4 w-4 sm:h-5 sm:w-5 text-violet-600 border-gray-300 rounded focus:ring-violet-500 focus:ring-2 transition-colors">
                    <span class="ml-3 text-sm font-medium text-gray-700">Categoría activa</span>
                </label>
                <p class="mt-2 text-xs text-gray-500">
                    Las categorías inactivas no aparecerán en los formularios de productos
                </p>
            </div>
        </div>

        <!-- Vista Previa -->
        <div class="bg-white rounded-xl border border-gray-100 p-4 sm:p-6">
            <div class="flex items-center space-x-2 sm:space-x-3 mb-4 sm:mb-6">
                <div class="p-2 rounded-xl bg-gray-50 text-gray-600">
                    <i class="fas fa-eye text-sm"></i>
                </div>
                <h2 class="text-base sm:text-lg font-semibold text-gray-900">Vista Previa</h2>
            </div>

            <div class="bg-gradient-to-br from-gray-50 to-gray-100 rounded-xl p-4 sm:p-6 border border-gray-200">
                <div class="flex items-center space-x-3 sm:space-x-4">
                    <div class="h-12 w-12 sm:h-14 sm:w-14 rounded-xl flex items-center justify-center shadow-sm border border-gray-200 flex-shrink-0"
                         id="preview-icon"
                         style="background-color: {{ old('color', $category->color) }}20;">
                        <i class="fas fa-tag text-lg sm:text-xl" style="color: {{ old('color', $category->color) }};"></i>
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="text-base sm:text-lg font-semibold text-gray-900 truncate" id="preview-name">{{ old('name', $category->name) }}</div>
                        <div class="text-xs sm:text-sm text-gray-600 mt-0.5 line-clamp-2" id="preview-description">{{ old('description', $category->description) ?: 'Sin descripción' }}</div>
                        <div class="mt-2">
                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold"
                                  id="preview-badge"
                                  style="background-color: {{ old('color', $category->color) }}20; color: {{ old('color', $category->color) }};">
                                <i class="fas fa-circle mr-1.5 text-xs" style="color: {{ old('color', $category->color) }};"></i>
                                Categoría {{ old('is_active', $category->is_active) ? 'activa' : 'inactiva' }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Información del Sistema -->
        <div class="bg-white rounded-xl border border-gray-100 p-4 sm:p-6">
            <div class="flex items-center space-x-2 sm:space-x-3 mb-4 sm:mb-6">
                <div class="p-2 rounded-xl bg-gray-50 text-gray-600">
                    <i class="fas fa-info-circle text-sm"></i>
                </div>
                <h2 class="text-base sm:text-lg font-semibold text-gray-900">Información del Sistema</h2>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-6">
                <div class="bg-gray-50 rounded-xl p-4 border border-gray-200">
                    <div class="flex items-center space-x-3">
                        <div class="h-10 w-10 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center shadow-sm">
                            <i class="fas fa-hashtag text-sm"></i>
                        </div>
                        <div>
                            <div class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">ID de la Categoría</div>
                            <div class="text-base sm:text-lg font-bold text-gray-900">#{{ $category->id }}</div>
                        </div>
                    </div>
                </div>

                <div class="bg-gray-50 rounded-xl p-4 border border-gray-200">
                    <div class="flex items-center space-x-3">
                        <div class="h-10 w-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center shadow-sm">
                            <i class="fas fa-boxes text-sm"></i>
                        </div>
                        <div>
                            <div class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">Productos Asociados</div>
                            <div class="text-base sm:text-lg font-bold text-gray-900">{{ $category->products->count() }} productos</div>
                        </div>
                    </div>
                </div>

                <div class="bg-gray-50 rounded-xl p-4 border border-gray-200">
                    <div class="flex items-center space-x-3">
                        <div class="h-10 w-10 rounded-xl bg-violet-50 text-violet-600 flex items-center justify-center shadow-sm">
                            <i class="fas fa-calendar-plus text-sm"></i>
                        </div>
                        <div>
                            <div class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">Fecha de Creación</div>
                            <div class="text-sm font-semibold text-gray-900">{{ $category->created_at->format('d/m/Y H:i') }}</div>
                        </div>
                    </div>
                </div>

                <div class="bg-gray-50 rounded-xl p-4 border border-gray-200">
                    <div class="flex items-center space-x-3">
                        <div class="h-10 w-10 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center shadow-sm">
                            <i class="fas fa-calendar-edit text-sm"></i>
                        </div>
                        <div>
                            <div class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">Última Actualización</div>
                            <div class="text-sm font-semibold text-gray-900">{{ $category->updated_at->format('d/m/Y H:i') }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Botones de Acción -->
        <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-4 pt-4 border-t border-gray-100">
            <div class="text-xs sm:text-sm text-gray-500 flex items-center">
                <i class="fas fa-info-circle mr-1.5"></i>
                Los campos marcados con <span class="text-red-500 ml-1">*</span> son obligatorios
            </div>

            <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3">
                <a href="{{ route('categories.index') }}"
                   class="inline-flex items-center justify-center px-4 sm:px-5 py-2.5 rounded-xl border-2 border-gray-200 bg-white text-gray-700 text-sm font-semibold hover:bg-gray-50 hover:border-gray-300 transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500">
                    <i class="fas fa-arrow-left mr-2"></i>
                    Volver
                </a>

                <button type="submit"
                        class="inline-flex items-center justify-center px-4 sm:px-5 py-2.5 rounded-xl border-2 border-violet-600 bg-violet-600 text-white text-sm font-semibold hover:bg-violet-700 hover:border-violet-700 transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-violet-500 shadow-sm hover:shadow-md"
                        :disabled="loading">
                    <template x-if="!loading">
                        <i class="fas fa-save mr-2"></i>
                    </template>
                    <template x-if="loading">
                        <i class="fas fa-spinner fa-spin mr-2"></i>
                    </template>
                    <span x-text="loading ? 'Procesando...' : 'Actualizar Categoría'">Actualizar Categoría</span>
                </button>
            </div>
        </div>
    </form>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const colorInput = document.getElementById('color');
    const colorText = document.getElementById('color_text');
    const nameInput = document.getElementById('name');
    const descriptionInput = document.getElementById('description');
    const isActiveCheckbox = document.querySelector('input[name="is_active"]');
    const previewIcon = document.getElementById('preview-icon');
    const previewName = document.getElementById('preview-name');
    const previewDescription = document.getElementById('preview-description');
    const previewBadge = document.getElementById('preview-badge');
    const colorOptions = document.querySelectorAll('.color-option');

    // Inicializar selección de color actual
    const currentColor = colorText.value.toUpperCase();
    updateColorSelection(currentColor);

    // Manejar colores predefinidos
    colorOptions.forEach(option => {
        option.addEventListener('click', function() {
            const color = this.getAttribute('data-color');
            colorInput.value = color;
            colorText.value = color.toUpperCase();

            // Actualizar selección visual
            colorOptions.forEach(opt => {
                opt.classList.remove('ring-2', 'ring-violet-500', 'ring-offset-2');
            });
            this.classList.add('ring-2', 'ring-violet-500', 'ring-offset-2');

            updatePreview();
        });
    });

    // Sincronizar color picker con input de texto
    colorInput.addEventListener('input', function() {
        colorText.value = this.value.toUpperCase();
        updateColorSelection(this.value.toUpperCase());
        updatePreview();
    });

    colorText.addEventListener('input', function() {
        const value = this.value.toUpperCase();
        if (/^#[0-9A-Fa-f]{6}$/.test(value)) {
            colorInput.value = value;
            updateColorSelection(value);
            updatePreview();
        }
    });

    // Actualizar selección de color predefinido
    function updateColorSelection(color) {
        colorOptions.forEach(option => {
            if (option.getAttribute('data-color').toUpperCase() === color.toUpperCase()) {
                option.classList.add('ring-2', 'ring-violet-500', 'ring-offset-2');
            } else {
                option.classList.remove('ring-2', 'ring-violet-500', 'ring-offset-2');
            }
        });
    }

    // Actualizar vista previa
    function updatePreview() {
        const color = colorText.value || '{{ $category->color }}';
        const name = nameInput.value.trim() || 'Nombre de la categoría';
        const description = descriptionInput.value.trim() || 'Sin descripción';
        const isActive = isActiveCheckbox.checked;

        // Actualizar icono y colores
        const colorOpacity = color + '20';
        previewIcon.style.backgroundColor = colorOpacity;
        const iconElement = previewIcon.querySelector('i');
        if (iconElement) {
            iconElement.style.color = color;
        }

        // Actualizar texto
        previewName.textContent = name;
        previewDescription.textContent = description;

        // Actualizar badge de estado
        if (isActive) {
            previewBadge.style.backgroundColor = colorOpacity;
            previewBadge.style.color = color;
            const badgeIcon = previewBadge.querySelector('i');
            if (badgeIcon) {
                badgeIcon.style.color = color;
            }
            previewBadge.innerHTML = '<i class="fas fa-circle mr-1.5 text-xs" style="color: ' + color + ';"></i>Categoría activa';
        } else {
            previewBadge.style.backgroundColor = '#6B728020';
            previewBadge.style.color = '#6B7280';
            previewBadge.innerHTML = '<i class="fas fa-pause-circle mr-1.5 text-xs" style="color: #6B7280;"></i>Categoría inactiva';
        }
    }

    // Actualizar vista previa cuando cambien los inputs
    nameInput.addEventListener('input', updatePreview);
    descriptionInput.addEventListener('input', updatePreview);
    isActiveCheckbox.addEventListener('change', updatePreview);

    // Validación en tiempo real
    colorText.addEventListener('blur', function() {
        if (!/^#[0-9A-Fa-f]{6}$/.test(this.value)) {
            this.classList.add('border-red-300');
        } else {
            this.classList.remove('border-red-300');
        }
    });

    // Inicializar vista previa
    updatePreview();
});
</script>
@endpush
@endsection
