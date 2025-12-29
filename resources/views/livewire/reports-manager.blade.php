<div class="space-y-6"
     x-data="{}"
     x-init="setTimeout(() => { 
        if (window.reportsManager) {
            window.reportsManager.initializeCharts();
            window.reportsManager.initializeFilterSelects();
        }
     }, 500)">
    <!-- Header and Filters -->
    <div class="bg-white rounded-xl border border-gray-100 p-4 sm:p-6">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
            <div class="flex items-center space-x-3 sm:space-x-4">
                <div class="p-2.5 sm:p-3 rounded-xl bg-violet-50 text-violet-600">
                    <i class="fas fa-chart-pie text-lg sm:text-xl"></i>
                </div>
                <div>
                    <h1 class="text-xl sm:text-2xl font-bold text-gray-900">Centro de Reportes</h1>
                    <p class="text-xs sm:text-sm text-gray-500 mt-1">Análisis y estadísticas del hotel</p>
                </div>
            </div>
            <!-- Botón de Búsqueda Global -->
            <div class="relative w-full sm:w-auto" x-data="{ searchOpen: false }">
                <button @click="searchOpen = !searchOpen" 
                        class="w-full sm:w-auto flex items-center justify-center space-x-2 px-4 py-2.5 bg-violet-600 text-white rounded-xl hover:bg-violet-700 transition-all duration-200 shadow-sm hover:shadow-md">
                    <i class="fas fa-search"></i>
                    <span class="text-sm font-semibold">Buscar Reportes</span>
                </button>
                <div x-show="searchOpen" 
                     @click.away="searchOpen = false"
                     x-transition:enter="transition ease-out duration-200"
                     x-transition:enter-start="opacity-0 transform scale-95"
                     x-transition:enter-end="opacity-100 transform scale-100"
                     x-transition:leave="transition ease-in duration-150"
                     x-transition:leave-start="opacity-100 transform scale-100"
                     x-transition:leave-end="opacity-0 transform scale-95"
                     class="absolute right-0 mt-2 w-80 bg-white rounded-xl shadow-xl border border-gray-200 z-50 p-4"
                     style="display: none;">
                    <div class="mb-3">
                        <label class="block text-xs font-semibold text-gray-700 mb-2">Búsqueda Global</label>
                        <div class="relative">
                            <input type="text" 
                                   wire:model.live.debounce.300ms="searchQuery"
                                   placeholder="Buscar por nombre, tipo, módulo..."
                                   class="w-full pl-10 pr-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-violet-500 focus:border-transparent">
                            <i class="fas fa-search absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                        </div>
                    </div>
                    <div class="text-xs text-gray-500">
                        <i class="fas fa-info-circle mr-1"></i>
                        Busca reportes por módulo o tipo
                    </div>
                </div>
            </div>
        </div>

        <!-- Selector de Módulo y Tipo de Reporte -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-4" wire:key="filters-header-{{ $entity_type }}">
            <div>
                <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-2">Módulo / Tipo de Reporte</label>
                <select wire:change="setEntityType($event.target.value)"
                        wire:key="entity-type-select-{{ $entity_type }}"
                        class="block w-full pl-3 pr-10 py-2.5 border border-gray-300 rounded-xl text-sm text-gray-900 focus:outline-none focus:ring-2 focus:ring-violet-500 focus:border-transparent appearance-none bg-white">
                    @foreach($availableReports as $key => $report)
                        <option value="{{ $key }}" @selected($entity_type === $key)>{{ $report['label'] }}</option>
                    @endforeach
                </select>
                @if(!empty($availableReports[$entity_type]['description'] ?? ''))
                    <p class="text-xs text-gray-500 mt-1" wire:key="desc-{{ $entity_type }}">
                        <i class="fas fa-info-circle"></i> {{ $availableReports[$entity_type]['description'] ?? '' }}
                    </p>
                @endif
            </div>

            <!-- Filtro Específico por Módulo: Habitación -->
            @if($entity_type === 'rooms')
                <div wire:key="filter-rooms-header">
                    <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-2">
                        <i class="fas fa-door-open mr-1"></i> Seleccionar Habitación Específica
                    </label>
                    <select wire:model.live="filters.room_id" 
                            class="block w-full pl-3 pr-10 py-2.5 border border-gray-300 rounded-xl text-sm text-gray-900 focus:outline-none focus:ring-2 focus:ring-violet-500 focus:border-transparent appearance-none bg-white">
                        <option value="">Todas las habitaciones</option>
                        @foreach($rooms as $room)
                            <option value="{{ $room->id }}">Habitación {{ $room->room_number }} - {{ $room->status->label() }}</option>
                        @endforeach
                    </select>
                </div>
            @endif

            <!-- Filtros Específicos por Módulo: Ventas -->
            @if($entity_type === 'sales')
                <div wire:key="filter-sales-header-rec">
                    <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-2">
                        <i class="fas fa-user-tie mr-1"></i> Seleccionar Recepcionista
                    </label>
                    <select wire:model.live="filters.receptionist_id" 
                            class="block w-full pl-3 pr-10 py-2.5 border border-gray-300 rounded-xl text-sm text-gray-900 focus:outline-none focus:ring-2 focus:ring-violet-500 focus:border-transparent appearance-none bg-white">
                        <option value="">Todos los recepcionistas</option>
                        @foreach($receptionists as $receptionist)
                            <option value="{{ $receptionist->id }}">{{ $receptionist->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div wire:key="filter-sales-header-room">
                    <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-2">
                        <i class="fas fa-door-open mr-1"></i> Seleccionar Habitación
                    </label>
                    <select wire:model.live="filters.room_id" 
                            class="block w-full pl-3 pr-10 py-2.5 border border-gray-300 rounded-xl text-sm text-gray-900 focus:outline-none focus:ring-2 focus:ring-violet-500 focus:border-transparent appearance-none bg-white">
                        <option value="">Todas las habitaciones</option>
                        @foreach($rooms as $room)
                            <option value="{{ $room->id }}">Habitación {{ $room->room_number }}</option>
                        @endforeach
                    </select>
                </div>
            @endif

            <!-- Filtros Específicos por Módulo: Reservaciones -->
            @if($entity_type === 'reservations')
                <div wire:key="filter-res-customer">
                    <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-2">
                        <i class="fas fa-user mr-1"></i> Seleccionar Cliente
                    </label>
                    <select wire:model.live="filters.customer_id" 
                            id="filter_customer_reservations"
                            wire:ignore
                            class="block w-full pl-3 pr-10 py-2.5 border border-gray-300 rounded-xl text-sm text-gray-900 focus:outline-none focus:ring-2 focus:ring-violet-500 focus:border-transparent appearance-none bg-white">
                        <option value="">Todos los clientes</option>
                        @foreach($customers as $customer)
                            <option value="{{ $customer->id }}">{{ $customer->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div wire:key="filter-res-room">
                    <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-2">
                        <i class="fas fa-door-open mr-1"></i> Seleccionar Habitación
                    </label>
                    <select wire:model.live="filters.room_id" 
                            class="block w-full pl-3 pr-10 py-2.5 border border-gray-300 rounded-xl text-sm text-gray-900 focus:outline-none focus:ring-2 focus:ring-violet-500 focus:border-transparent appearance-none bg-white">
                        <option value="">Todas las habitaciones</option>
                        @foreach($rooms as $room)
                            <option value="{{ $room->id }}">Habitación {{ $room->room_number }} - {{ $room->status->label() }}</option>
                        @endforeach
                    </select>
                </div>
            @endif

            <!-- Filtros Específicos por Módulo: Productos -->
            @if($entity_type === 'products')
                <div wire:key="filter-products-cat">
                    <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-2">
                        <i class="fas fa-tags mr-1"></i> Seleccionar Categoría
                    </label>
                    <select wire:model.live="filters.category_id" 
                            class="block w-full pl-3 pr-10 py-2.5 border border-gray-300 rounded-xl text-sm text-gray-900 focus:outline-none focus:ring-2 focus:ring-violet-500 focus:border-transparent appearance-none bg-white">
                        <option value="">Todas las categorías</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                        @endforeach
                    </select>
                </div>
            @endif

            <!-- Filtros Específicos por Módulo: Facturas Electrónicas -->
            @if($entity_type === 'electronic_invoices')
                <div wire:key="filter-invoices-customer">
                    <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-2">
                        <i class="fas fa-user mr-1"></i> Seleccionar Cliente
                    </label>
                    <select wire:model.live="filters.customer_id" 
                            class="block w-full pl-3 pr-10 py-2.5 border border-gray-300 rounded-xl text-sm text-gray-900 focus:outline-none focus:ring-2 focus:ring-violet-500 focus:border-transparent appearance-none bg-white">
                        <option value="">Todos los clientes</option>
                        @foreach($customers as $customer)
                            <option value="{{ $customer->id }}">{{ $customer->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div wire:key="filter-invoices-doc">
                    <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-2">
                        <i class="fas fa-file-invoice mr-1"></i> Tipo de Documento
                    </label>
                    <select wire:model.live="filters.document_type_id" 
                            class="block w-full pl-3 pr-10 py-2.5 border border-gray-300 rounded-xl text-sm text-gray-900 focus:outline-none focus:ring-2 focus:ring-violet-500 focus:border-transparent appearance-none bg-white">
                        <option value="">Todos los tipos</option>
                        @foreach($documentTypes as $docType)
                            <option value="{{ $docType->id }}">{{ $docType->name }}</option>
                        @endforeach
                    </select>
                </div>
            @endif

            <div>
                <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-2">Fecha Inicio</label>
                <input type="date" 
                       wire:model.live="startDate"
                       class="block w-full pl-3 pr-10 py-2.5 border border-gray-300 rounded-xl text-sm text-gray-900 focus:outline-none focus:ring-2 focus:ring-violet-500 focus:border-transparent">
            </div>

            <div>
                <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-2">Fecha Fin</label>
                <input type="date" 
                       wire:model.live="endDate"
                       class="block w-full pl-3 pr-10 py-2.5 border border-gray-300 rounded-xl text-sm text-gray-900 focus:outline-none focus:ring-2 focus:ring-violet-500 focus:border-transparent">
            </div>

            @if(!empty($groupingOptions))
                <div>
                    <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-2">Agrupar Por</label>
                    <select wire:model.live="groupBy"
                            class="block w-full pl-3 pr-10 py-2.5 border border-gray-300 rounded-xl text-sm text-gray-900 focus:outline-none focus:ring-2 focus:ring-violet-500 focus:border-transparent appearance-none bg-white">
                        <option value="">Sin agrupación</option>
                        @foreach($groupingOptions as $option)
                            <option value="{{ $option }}">{{ app(\App\Services\ReportService::class)->translateGroupingOption($option) }}</option>
                        @endforeach
                    </select>
                </div>
            @endif
        </div>

        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mt-4 border-t border-gray-200 pt-4">
            <div class="text-xs text-gray-500">
                @if($this->hasPendingChanges())
                    <span class="font-semibold text-violet-700">Cambios sin aplicar.</span>
                    <span>Presiona Buscar para actualizar el reporte.</span>
                @else
                    <span class="font-semibold text-gray-700">Filtros aplicados.</span>
                @endif
            </div>
            <div class="flex gap-2">
                <button type="button"
                        wire:click="applyFilters"
                        class="inline-flex items-center px-4 py-2 bg-violet-600 text-white text-sm font-semibold rounded-lg hover:bg-violet-700 transition-colors">
                    <i class="fas fa-search mr-2"></i>
                    Buscar
                </button>
            </div>
        </div>

        <!-- Presets Rápidos de Fechas -->
        <div class="border-t border-gray-200 pt-4 mb-4">
            <div class="flex items-center justify-between mb-3">
                <div class="flex items-center space-x-2">
                    <i class="fas fa-calendar-alt text-violet-600"></i>
                    <h3 class="text-sm font-semibold text-gray-700 uppercase tracking-wider">Presets de Fecha</h3>
                </div>
            </div>
            <div class="flex flex-wrap gap-2">
                <button wire:click="applyPreset('today')" 
                        class="px-4 py-2 text-xs font-medium rounded-lg border-2 transition-all duration-200 flex items-center space-x-2 {{ $activePreset === 'today' ? 'border-violet-600 bg-violet-50 text-violet-700 shadow-md' : 'border-gray-300 bg-white text-gray-700 hover:bg-gray-50 hover:border-violet-400 hover:text-violet-700' }}">
                    <i class="fas fa-calendar-day {{ $activePreset === 'today' ? 'text-violet-600' : '' }}"></i>
                    <span class="font-semibold">Hoy</span>
                    <span class="text-xs opacity-75">({{ now()->translatedFormat('d/m/Y') }})</span>
                    @if($activePreset === 'today')
                        <i class="fas fa-check-circle text-violet-600"></i>
                    @endif
                </button>
                <button wire:click="applyPreset('yesterday')" 
                        class="px-4 py-2 text-xs font-medium rounded-lg border-2 transition-all duration-200 flex items-center space-x-2 {{ $activePreset === 'yesterday' ? 'border-violet-600 bg-violet-50 text-violet-700 shadow-md' : 'border-gray-300 bg-white text-gray-700 hover:bg-gray-50 hover:border-violet-400 hover:text-violet-700' }}">
                    <i class="fas fa-calendar-minus {{ $activePreset === 'yesterday' ? 'text-violet-600' : '' }}"></i>
                    <span class="font-semibold">Ayer</span>
                    <span class="text-xs opacity-75">({{ now()->subDay()->translatedFormat('d/m/Y') }})</span>
                    @if($activePreset === 'yesterday')
                        <i class="fas fa-check-circle text-violet-600"></i>
                    @endif
                </button>
                <button wire:click="applyPreset('this_week')" 
                        class="px-4 py-2 text-xs font-medium rounded-lg border-2 transition-all duration-200 flex items-center space-x-2 {{ $activePreset === 'this_week' ? 'border-violet-600 bg-violet-50 text-violet-700 shadow-md' : 'border-gray-300 bg-white text-gray-700 hover:bg-gray-50 hover:border-violet-400 hover:text-violet-700' }}">
                    <i class="fas fa-calendar-week {{ $activePreset === 'this_week' ? 'text-violet-600' : '' }}"></i>
                    <span class="font-semibold">Esta Semana</span>
                    <span class="text-xs opacity-75">({{ now()->startOfWeek()->translatedFormat('d/m') }} - {{ now()->endOfWeek()->translatedFormat('d/m/Y') }})</span>
                    @if($activePreset === 'this_week')
                        <i class="fas fa-check-circle text-violet-600"></i>
                    @endif
                </button>
                <button wire:click="applyPreset('this_month')" 
                        class="px-4 py-2 text-xs font-medium rounded-lg border-2 transition-all duration-200 flex items-center space-x-2 {{ $activePreset === 'this_month' ? 'border-violet-600 bg-violet-50 text-violet-700 shadow-md' : 'border-gray-300 bg-white text-gray-700 hover:bg-gray-50 hover:border-violet-400 hover:text-violet-700' }}">
                    <i class="fas fa-calendar {{ $activePreset === 'this_month' ? 'text-violet-600' : '' }}"></i>
                    <span class="font-semibold">Este Mes</span>
                    <span class="text-xs opacity-75">({{ now()->translatedFormat('F Y') }})</span>
                    @if($activePreset === 'this_month')
                        <i class="fas fa-check-circle text-violet-600"></i>
                    @endif
                </button>
                <button wire:click="applyPreset('last_month')" 
                        class="px-4 py-2 text-xs font-medium rounded-lg border-2 transition-all duration-200 flex items-center space-x-2 {{ $activePreset === 'last_month' ? 'border-violet-600 bg-violet-50 text-violet-700 shadow-md' : 'border-gray-300 bg-white text-gray-700 hover:bg-gray-50 hover:border-violet-400 hover:text-violet-700' }}">
                    <i class="fas fa-calendar-check {{ $activePreset === 'last_month' ? 'text-violet-600' : '' }}"></i>
                    <span class="font-semibold">Mes Anterior</span>
                    <span class="text-xs opacity-75">({{ now()->subMonth()->translatedFormat('F Y') }})</span>
                    @if($activePreset === 'last_month')
                        <i class="fas fa-check-circle text-violet-600"></i>
                    @endif
                </button>
                <button wire:click="applyPreset('this_year')" 
                        class="px-4 py-2 text-xs font-medium rounded-lg border-2 transition-all duration-200 flex items-center space-x-2 {{ $activePreset === 'this_year' ? 'border-violet-600 bg-violet-50 text-violet-700 shadow-md' : 'border-gray-300 bg-white text-gray-700 hover:bg-gray-50 hover:border-violet-400 hover:text-violet-700' }}">
                    <i class="fas fa-calendar-alt {{ $activePreset === 'this_year' ? 'text-violet-600' : '' }}"></i>
                    <span class="font-semibold">Este Año</span>
                    <span class="text-xs opacity-75">({{ now()->year }})</span>
                    @if($activePreset === 'this_year')
                        <i class="fas fa-check-circle text-violet-600"></i>
                    @endif
                </button>
            </div>
        </div>

        <!-- Filtros Avanzados -->
        @if(!empty($filterOptions))
            <div class="border-t border-gray-200 pt-4" 
                 x-data="{ filtersOpen: {{ $this->getActiveFiltersCount() > 0 ? 'true' : 'false' }} }">
                <div class="flex items-center justify-between mb-4">
                    <div class="flex items-center space-x-3">
                        <button @click="filtersOpen = !filtersOpen" 
                                class="flex items-center space-x-2 text-sm font-semibold text-gray-700 hover:text-violet-600 transition-colors">
                            <i class="fas fa-filter text-violet-600"></i>
                            <span class="uppercase tracking-wider">Filtros Avanzados</span>
                            @if($this->getActiveFiltersCount() > 0)
                                <span class="px-2 py-0.5 text-xs font-bold text-white bg-violet-600 rounded-full">
                                    {{ $this->getActiveFiltersCount() }}
                                </span>
                            @endif
                        </button>
                    </div>
                    @if($this->getActiveFiltersCount() > 0)
                        <button wire:click="clearFilters" 
                                class="flex items-center space-x-1 px-3 py-1.5 text-xs font-medium text-red-600 hover:text-red-700 hover:bg-red-50 rounded-lg transition-all duration-200">
                            <i class="fas fa-times-circle"></i>
                            <span>Limpiar Filtros</span>
                        </button>
                    @endif
                </div>

                <div x-show="filtersOpen" 
                     x-transition:enter="transition ease-out duration-300"
                     x-transition:enter-start="opacity-0 transform -translate-y-2"
                     x-transition:enter-end="opacity-100 transform translate-y-0"
                     x-transition:leave="transition ease-in duration-200"
                     x-transition:leave-start="opacity-100 transform translate-y-0"
                     x-transition:leave-end="opacity-0 transform -translate-y-2"
                     style="display: none;">
                    <div class="bg-gray-50 rounded-xl p-4 space-y-6">
                        <!-- Grupo 1: Personal y Ubicaciones -->
                        @if(in_array('receptionist_id', $filterOptions) || in_array('room_id', $filterOptions) || in_array('customer_id', $filterOptions))
                            <div>
                                <h4 class="text-xs font-bold text-gray-600 uppercase tracking-wider mb-3 flex items-center">
                                    <i class="fas fa-users mr-2 text-violet-600"></i>
                                    Personal y Ubicaciones
                                </h4>
                                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                                    @if(in_array('receptionist_id', $filterOptions))
                                        <div>
                                            <label class="block text-xs font-semibold text-gray-700 mb-2 flex items-center justify-between">
                                                <span class="flex items-center">
                                                    <i class="fas fa-user-tie mr-1.5 text-violet-600"></i>
                                                    Recepcionista
                                                </span>
                                                @if(!empty($filters['receptionist_id'] ?? ''))
                                                    <span class="w-2.5 h-2.5 bg-violet-600 rounded-full animate-pulse"></span>
                                                @endif
                                            </label>
                                            <select wire:ignore
                                                    id="filter_receptionist_id"
                                                    class="filter-select block w-full pl-3 pr-10 py-2.5 border border-gray-300 rounded-lg text-sm text-gray-900 focus:outline-none focus:ring-2 focus:ring-violet-500 focus:border-transparent bg-white shadow-sm hover:shadow-md transition-shadow">
                                                <option value="">🔍 Buscar recepcionista...</option>
                                                @foreach($receptionists as $receptionist)
                                                    <option value="{{ $receptionist->id }}" {{ ($filters['receptionist_id'] ?? '') == $receptionist->id ? 'selected' : '' }}>
                                                        {{ $receptionist->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            <p class="text-xs text-gray-500 mt-1 flex items-center">
                                                <i class="fas fa-info-circle mr-1"></i>
                                                Escribe para buscar
                                            </p>
                                        </div>
                                    @endif

                                    @if(in_array('room_id', $filterOptions))
                                        <div>
                                            <label class="block text-xs font-semibold text-gray-700 mb-2 flex items-center justify-between">
                                                <span class="flex items-center">
                                                    <i class="fas fa-door-open mr-1.5 text-violet-600"></i>
                                                    Habitación
                                                </span>
                                                @if(!empty($filters['room_id'] ?? ''))
                                                    <span class="w-2.5 h-2.5 bg-violet-600 rounded-full animate-pulse"></span>
                                                @endif
                                            </label>
                                            <select wire:ignore
                                                    id="filter_room_id"
                                                    class="filter-select block w-full pl-3 pr-10 py-2.5 border border-gray-300 rounded-lg text-sm text-gray-900 focus:outline-none focus:ring-2 focus:ring-violet-500 focus:border-transparent bg-white shadow-sm hover:shadow-md transition-shadow">
                                                <option value="">🔍 Buscar habitación...</option>
                                                @foreach($rooms as $room)
                                                    <option value="{{ $room->id }}" {{ ($filters['room_id'] ?? '') == $room->id ? 'selected' : '' }}>
                                                        Habitación {{ $room->room_number }} - {{ $room->status->label() }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            <p class="text-xs text-gray-500 mt-1 flex items-center">
                                                <i class="fas fa-info-circle mr-1"></i>
                                                Escribe para buscar
                                            </p>
                                        </div>
                                    @endif

                                    @if(in_array('customer_id', $filterOptions))
                                        <div>
                                            <label class="block text-xs font-semibold text-gray-700 mb-2 flex items-center justify-between">
                                                <span class="flex items-center">
                                                    <i class="fas fa-user mr-1.5 text-violet-600"></i>
                                                    Cliente
                                                </span>
                                                @if(!empty($filters['customer_id'] ?? ''))
                                                    <span class="w-2.5 h-2.5 bg-violet-600 rounded-full animate-pulse"></span>
                                                @endif
                                            </label>
                                            <select wire:ignore
                                                    id="filter_customer_id"
                                                    class="filter-select block w-full pl-3 pr-10 py-2.5 border border-gray-300 rounded-lg text-sm text-gray-900 focus:outline-none focus:ring-2 focus:ring-violet-500 focus:border-transparent bg-white shadow-sm hover:shadow-md transition-shadow">
                                                <option value="">🔍 Buscar cliente...</option>
                                                @foreach($customers as $customer)
                                                    <option value="{{ $customer->id }}" {{ ($filters['customer_id'] ?? '') == $customer->id ? 'selected' : '' }}>
                                                        {{ $customer->name }} @if($customer->identification) - {{ $customer->identification }} @endif
                                                    </option>
                                                @endforeach
                                            </select>
                                            <p class="text-xs text-gray-500 mt-1 flex items-center">
                                                <i class="fas fa-info-circle mr-1"></i>
                                                Escribe para buscar
                                            </p>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endif

                        <!-- Grupo 2: Operaciones y Estados -->
                        @if(in_array('status', $filterOptions) || in_array('debt_status', $filterOptions) || in_array('is_active', $filterOptions) || in_array('low_stock', $filterOptions))
                            <div>
                                <h4 class="text-xs font-bold text-gray-600 uppercase tracking-wider mb-3 flex items-center">
                                    <i class="fas fa-tasks mr-2 text-violet-600"></i>
                                    Operaciones y Estados
                                </h4>
                                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                                    @if(in_array('status', $filterOptions))
                                        <div>
                                            <label class="block text-xs font-semibold text-gray-700 mb-2 flex items-center justify-between">
                                                <span>Estado</span>
                                                @if(!empty($filters['status'] ?? ''))
                                                    <span class="w-2 h-2 bg-violet-600 rounded-full"></span>
                                                @endif
                                            </label>
                                            <select wire:model.live="filters.status" 
                                                    class="block w-full pl-3 pr-10 py-2.5 border border-gray-300 rounded-lg text-sm text-gray-900 focus:outline-none focus:ring-2 focus:ring-violet-500 focus:border-transparent bg-white">
                                                <option value="">Todos los estados</option>
                                                @if($entity_type === 'rooms')
                                                    <option value="libre">🟢 Libre</option>
                                                    <option value="ocupada">🔴 Ocupada</option>
                                                    <option value="reservada">🟡 Reservada</option>
                                                    <option value="limpieza">🔵 En Limpieza</option>
                                                    <option value="sucia">⚫ Sucia</option>
                                                    <option value="mantenimiento">🟠 Mantenimiento</option>
                                                @elseif($entity_type === 'products')
                                                    <option value="active">Activo</option>
                                                    <option value="inactive">Inactivo</option>
                                                @elseif($entity_type === 'electronic_invoices')
                                                    <option value="pending">Pendiente</option>
                                                    <option value="validated">Validada</option>
                                                    <option value="rejected">Rechazada</option>
                                                @endif
                                            </select>
                                        </div>
                                    @endif

                                    @if(in_array('debt_status', $filterOptions))
                                        <div>
                                            <label class="block text-xs font-semibold text-gray-700 mb-2 flex items-center justify-between">
                                                <span>Estado de Deuda</span>
                                                @if(!empty($filters['debt_status'] ?? ''))
                                                    <span class="w-2 h-2 bg-violet-600 rounded-full"></span>
                                                @endif
                                            </label>
                                            <select wire:model.live="filters.debt_status" 
                                                    class="block w-full pl-3 pr-10 py-2.5 border border-gray-300 rounded-lg text-sm text-gray-900 focus:outline-none focus:ring-2 focus:ring-violet-500 focus:border-transparent bg-white">
                                                <option value="">Todos los estados</option>
                                                <option value="pagado">Pagado</option>
                                                <option value="pendiente">Pendiente</option>
                                            </select>
                                        </div>
                                    @endif

                                    @if(in_array('is_active', $filterOptions))
                                        <div>
                                            <label class="block text-xs font-semibold text-gray-700 mb-2 flex items-center justify-between">
                                                <span>Estado Activo</span>
                                                @if(!empty($filters['is_active'] ?? ''))
                                                    <span class="w-2 h-2 bg-violet-600 rounded-full"></span>
                                                @endif
                                            </label>
                                            <select wire:model.live="filters.is_active" 
                                                    class="block w-full pl-3 pr-10 py-2.5 border border-gray-300 rounded-lg text-sm text-gray-900 focus:outline-none focus:ring-2 focus:ring-violet-500 focus:border-transparent bg-white">
                                                <option value="">Todos</option>
                                                <option value="true">Activos</option>
                                                <option value="false">Inactivos</option>
                                            </select>
                                        </div>
                                    @endif

                                    @if(in_array('low_stock', $filterOptions))
                                        <div>
                                            <label class="block text-xs font-semibold text-gray-700 mb-2 flex items-center justify-between">
                                                <span>Stock Bajo</span>
                                                @if(!empty($filters['low_stock'] ?? ''))
                                                    <span class="w-2 h-2 bg-violet-600 rounded-full"></span>
                                                @endif
                                            </label>
                                            <select wire:model.live="filters.low_stock" 
                                                    class="block w-full pl-3 pr-10 py-2.5 border border-gray-300 rounded-lg text-sm text-gray-900 focus:outline-none focus:ring-2 focus:ring-violet-500 focus:border-transparent bg-white">
                                                <option value="">Todos</option>
                                                <option value="true">⚠️ Solo Bajo Stock</option>
                                            </select>
                                        </div>
                                    @endif

                                    @if(in_array('requires_electronic_invoice', $filterOptions))
                                        <div>
                                            <label class="block text-xs font-semibold text-gray-700 mb-2 flex items-center justify-between">
                                                <span>Facturación Electrónica</span>
                                                @if(!empty($filters['requires_electronic_invoice'] ?? ''))
                                                    <span class="w-2 h-2 bg-violet-600 rounded-full"></span>
                                                @endif
                                            </label>
                                            <select wire:model.live="filters.requires_electronic_invoice" 
                                                    class="block w-full pl-3 pr-10 py-2.5 border border-gray-300 rounded-lg text-sm text-gray-900 focus:outline-none focus:ring-2 focus:ring-violet-500 focus:border-transparent bg-white">
                                                <option value="">Todos</option>
                                                <option value="true">Con Facturación</option>
                                                <option value="false">Sin Facturación</option>
                                            </select>
                                        </div>
                                    @endif

                                    @if(in_array('category_id', $filterOptions))
                                        <div>
                                            <label class="block text-xs font-semibold text-gray-700 mb-2 flex items-center justify-between">
                                                <span class="flex items-center">
                                                    <i class="fas fa-tags mr-1.5 text-violet-600"></i>
                                                    Categoría
                                                </span>
                                                @if(!empty($filters['category_id'] ?? ''))
                                                    <span class="w-2.5 h-2.5 bg-violet-600 rounded-full animate-pulse"></span>
                                                @endif
                                            </label>
                                            <select wire:model.live="filters.category_id" 
                                                    class="block w-full pl-3 pr-10 py-2.5 border border-gray-300 rounded-lg text-sm text-gray-900 focus:outline-none focus:ring-2 focus:ring-violet-500 focus:border-transparent bg-white shadow-sm hover:shadow-md transition-shadow">
                                                <option value="">Todas las categorías</option>
                                                @foreach($categories as $category)
                                                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    @endif

                                    @if(in_array('document_type_id', $filterOptions))
                                        <div>
                                            <label class="block text-xs font-semibold text-gray-700 mb-2 flex items-center justify-between">
                                                <span class="flex items-center">
                                                    <i class="fas fa-file-invoice mr-1.5 text-violet-600"></i>
                                                    Tipo de Documento
                                                </span>
                                                @if(!empty($filters['document_type_id'] ?? ''))
                                                    <span class="w-2.5 h-2.5 bg-violet-600 rounded-full animate-pulse"></span>
                                                @endif
                                            </label>
                                            <select wire:model.live="filters.document_type_id" 
                                                    class="block w-full pl-3 pr-10 py-2.5 border border-gray-300 rounded-lg text-sm text-gray-900 focus:outline-none focus:ring-2 focus:ring-violet-500 focus:border-transparent bg-white shadow-sm hover:shadow-md transition-shadow">
                                                <option value="">Todos los tipos</option>
                                                @foreach($documentTypes as $docType)
                                                    <option value="{{ $docType->id }}">{{ $docType->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endif

                        <!-- Grupo 3: Pagos -->
                        @if(in_array('payment_method', $filterOptions) || in_array('payment_status', $filterOptions))
                            <div>
                                <h4 class="text-xs font-bold text-gray-600 uppercase tracking-wider mb-3 flex items-center">
                                    <i class="fas fa-money-bill-wave mr-2 text-violet-600"></i>
                                    Pagos y Facturación
                                </h4>
                                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                                    @if(in_array('payment_method', $filterOptions))
                                        <div>
                                            <label class="block text-xs font-semibold text-gray-700 mb-2 flex items-center justify-between">
                                                <span>Método de Pago</span>
                                                @if(!empty($filters['payment_method'] ?? ''))
                                                    <span class="w-2 h-2 bg-violet-600 rounded-full"></span>
                                                @endif
                                            </label>
                                            <select wire:model.live="filters.payment_method" 
                                                    class="block w-full pl-3 pr-10 py-2.5 border border-gray-300 rounded-lg text-sm text-gray-900 focus:outline-none focus:ring-2 focus:ring-violet-500 focus:border-transparent bg-white">
                                                <option value="">Todos los métodos</option>
                                                <option value="efectivo">Efectivo</option>
                                                <option value="transferencia">Transferencia</option>
                                                <option value="ambos">Ambos</option>
                                                <option value="pendiente">Pendiente</option>
                                            </select>
                                        </div>
                                    @endif

                                    @if(in_array('payment_status', $filterOptions))
                                        <div>
                                            <label class="block text-xs font-semibold text-gray-700 mb-2 flex items-center justify-between">
                                                <span>Estado de Pago</span>
                                                @if(!empty($filters['payment_status'] ?? ''))
                                                    <span class="w-2 h-2 bg-violet-600 rounded-full"></span>
                                                @endif
                                            </label>
                                            <select wire:model.live="filters.payment_status" 
                                                    class="block w-full pl-3 pr-10 py-2.5 border border-gray-300 rounded-lg text-sm text-gray-900 focus:outline-none focus:ring-2 focus:ring-violet-500 focus:border-transparent bg-white">
                                                <option value="">Todos los estados</option>
                                                <option value="paid">Pagado</option>
                                                <option value="partially_paid">Parcialmente Pagado</option>
                                                <option value="unpaid">No Pagado</option>
                                            </select>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endif

                        <!-- Grupo 4: Rangos Numéricos -->
                        @if(in_array('receptionist_id', $filterOptions) || in_array('room_id', $filterOptions))
                            <div>
                                <h4 class="text-xs font-bold text-gray-600 uppercase tracking-wider mb-3 flex items-center">
                                    <i class="fas fa-sliders-h mr-2 text-violet-600"></i>
                                    Rangos y Cantidades
                                </h4>
                                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                                    <div>
                                        <label class="block text-xs font-semibold text-gray-700 mb-2 flex items-center justify-between">
                                            <span>Monto Mínimo</span>
                                            @if($minAmount !== null && $minAmount !== '')
                                                <span class="w-2 h-2 bg-violet-600 rounded-full"></span>
                                            @endif
                                        </label>
                                        <div class="relative">
                                            <span class="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400 text-sm">$</span>
                                            <input type="number" 
                                                   wire:model.live.debounce.500ms="minAmount"
                                                   step="0.01"
                                                   min="0"
                                                   placeholder="0.00"
                                                   class="block w-full pl-8 pr-3 py-2.5 border border-gray-300 rounded-lg text-sm text-gray-900 focus:outline-none focus:ring-2 focus:ring-violet-500 focus:border-transparent bg-white">
                                        </div>
                                    </div>
                                    <div>
                                        <label class="block text-xs font-semibold text-gray-700 mb-2 flex items-center justify-between">
                                            <span>Monto Máximo</span>
                                            @if($maxAmount !== null && $maxAmount !== '')
                                                <span class="w-2 h-2 bg-violet-600 rounded-full"></span>
                                            @endif
                                        </label>
                                        <div class="relative">
                                            <span class="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400 text-sm">$</span>
                                            <input type="number" 
                                                   wire:model.live.debounce.500ms="maxAmount"
                                                   step="0.01"
                                                   min="0"
                                                   placeholder="Sin límite"
                                                   class="block w-full pl-8 pr-3 py-2.5 border border-gray-300 rounded-lg text-sm text-gray-900 focus:outline-none focus:ring-2 focus:ring-violet-500 focus:border-transparent bg-white">
                                        </div>
                                    </div>
                                    <div>
                                        <label class="block text-xs font-semibold text-gray-700 mb-2 flex items-center justify-between">
                                            <span>Cantidad Mínima</span>
                                            @if($minCount !== null && $minCount !== '')
                                                <span class="w-2 h-2 bg-violet-600 rounded-full"></span>
                                            @endif
                                        </label>
                                        <input type="number" 
                                               wire:model.live.debounce.500ms="minCount"
                                               min="0"
                                               placeholder="0"
                                               class="block w-full pl-3 pr-3 py-2.5 border border-gray-300 rounded-lg text-sm text-gray-900 focus:outline-none focus:ring-2 focus:ring-violet-500 focus:border-transparent bg-white">
                                    </div>
                                    <div>
                                        <label class="block text-xs font-semibold text-gray-700 mb-2 flex items-center justify-between">
                                            <span>Cantidad Máxima</span>
                                            @if($maxCount !== null && $maxCount !== '')
                                                <span class="w-2 h-2 bg-violet-600 rounded-full"></span>
                                            @endif
                                        </label>
                                        <input type="number" 
                                               wire:model.live.debounce.500ms="maxCount"
                                               min="0"
                                               placeholder="Sin límite"
                                               class="block w-full pl-3 pr-3 py-2.5 border border-gray-300 rounded-lg text-sm text-gray-900 focus:outline-none focus:ring-2 focus:ring-violet-500 focus:border-transparent bg-white">
                                    </div>
                                </div>
                            </div>
                        @endif

                        <!-- Chips de Filtros Activos -->
                        @if($this->getActiveFiltersCount() > 0)
                            <div class="pt-4 border-t-2 border-violet-200">
                                <div class="flex items-center justify-between mb-3">
                                    <h4 class="text-xs font-bold text-gray-600 uppercase tracking-wider flex items-center">
                                        <i class="fas fa-tags mr-2 text-violet-600"></i>
                                        Filtros Activos ({{ $this->getActiveFiltersCount() }})
                                    </h4>
                                    <button wire:click="clearFilters" 
                                            class="text-xs text-red-600 hover:text-red-700 font-medium flex items-center space-x-1 px-2 py-1 hover:bg-red-50 rounded transition-colors">
                                        <i class="fas fa-trash-alt"></i>
                                        <span>Limpiar Todo</span>
                                    </button>
                                </div>
                                <div class="flex flex-wrap gap-2">
                                    @foreach($filters as $key => $value)
                                        @if($value !== null && $value !== '')
                                            <span class="inline-flex items-center px-3 py-1.5 rounded-full text-xs font-medium bg-violet-100 text-violet-800 border border-violet-200">
                                                <i class="fas fa-filter mr-1.5 text-violet-600"></i>
                                                <span class="font-semibold">{{ $this->getFilterLabel($key) }}:</span>
                                                <span class="ml-1">{{ $this->getFilterValue($key) }}</span>
                                                <button wire:click="removeFilter('{{ $key }}')" 
                                                        class="ml-2 text-violet-600 hover:text-violet-800 transition-colors">
                                                    <i class="fas fa-times text-xs"></i>
                                                </button>
                                            </span>
                                        @endif
                                    @endforeach
                                    @if($minAmount !== null && $minAmount !== '')
                                        <span class="inline-flex items-center px-3 py-1.5 rounded-full text-xs font-medium bg-violet-100 text-violet-800 border border-violet-200">
                                            <i class="fas fa-dollar-sign mr-1.5 text-violet-600"></i>
                                            <span class="font-semibold">Monto Mínimo:</span>
                                            <span class="ml-1">${{ number_format($minAmount, 2, ',', '.') }}</span>
                                            <button wire:click="$set('minAmount', null)" 
                                                    class="ml-2 text-violet-600 hover:text-violet-800 transition-colors">
                                                <i class="fas fa-times text-xs"></i>
                                            </button>
                                        </span>
                                    @endif
                                    @if($maxAmount !== null && $maxAmount !== '')
                                        <span class="inline-flex items-center px-3 py-1.5 rounded-full text-xs font-medium bg-violet-100 text-violet-800 border border-violet-200">
                                            <i class="fas fa-dollar-sign mr-1.5 text-violet-600"></i>
                                            <span class="font-semibold">Monto Máximo:</span>
                                            <span class="ml-1">${{ number_format($maxAmount, 2, ',', '.') }}</span>
                                            <button wire:click="$set('maxAmount', null)" 
                                                    class="ml-2 text-violet-600 hover:text-violet-800 transition-colors">
                                                <i class="fas fa-times text-xs"></i>
                                            </button>
                                        </span>
                                    @endif
                                    @if($minCount !== null && $minCount !== '')
                                        <span class="inline-flex items-center px-3 py-1.5 rounded-full text-xs font-medium bg-violet-100 text-violet-800 border border-violet-200">
                                            <i class="fas fa-hashtag mr-1.5 text-violet-600"></i>
                                            <span class="font-semibold">Cantidad Mínima:</span>
                                            <span class="ml-1">{{ $minCount }}</span>
                                            <button wire:click="$set('minCount', null)" 
                                                    class="ml-2 text-violet-600 hover:text-violet-800 transition-colors">
                                                <i class="fas fa-times text-xs"></i>
                                            </button>
                                        </span>
                                    @endif
                                    @if($maxCount !== null && $maxCount !== '')
                                        <span class="inline-flex items-center px-3 py-1.5 rounded-full text-xs font-medium bg-violet-100 text-violet-800 border border-violet-200">
                                            <i class="fas fa-hashtag mr-1.5 text-violet-600"></i>
                                            <span class="font-semibold">Cantidad Máxima:</span>
                                            <span class="ml-1">{{ $maxCount }}</span>
                                            <button wire:click="$set('maxCount', null)" 
                                                    class="ml-2 text-violet-600 hover:text-violet-800 transition-colors">
                                                <i class="fas fa-times text-xs"></i>
                                            </button>
                                        </span>
                                    @endif
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        @endif
    </div>

    @php
        $reportsContentKey = 'reports-content|'
            . $applied_entity_type . '|'
            . $applied_startDate . '|'
            . $applied_endDate . '|'
            . ($applied_groupBy ?? '') . '|'
            . md5(json_encode([$applied_filters, $applied_minAmount, $applied_maxAmount, $applied_minCount, $applied_maxCount, $appliedRevision]));
    @endphp

    <livewire:reports-content
        :entity_type="$applied_entity_type"
        :startDate="$applied_startDate"
        :endDate="$applied_endDate"
        :groupBy="$applied_groupBy"
        :filters="$applied_filters"
        :minAmount="$applied_minAmount"
        :maxAmount="$applied_maxAmount"
        :minCount="$applied_minCount"
        :maxCount="$applied_maxCount"
        :pausePolling="(bool) ($selectedDetailId !== null)"
        :key="$reportsContentKey"
    />

    @if(false)
    <!-- Report Content (disabled - moved to reports-content to prevent UI/data desync) -->
    <div class="transition-opacity duration-200">
        @if(!empty($reportData))
            <div class="bg-white rounded-xl border border-gray-100 p-4 sm:p-6" wire:key="report-content-{{ $entity_type }}">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
                <h2 class="text-lg font-semibold text-gray-900">
                    Reporte de {{ $entityTypeLabel }} - 
                    {{ \Illuminate\Support\Carbon::parse($startDate)->translatedFormat('d/m/Y') }} al 
                    {{ \Illuminate\Support\Carbon::parse($endDate)->translatedFormat('d/m/Y') }}
                </h2>
                
                <!-- Action Buttons -->
                <div class="flex flex-wrap gap-2">
                    <form method="POST" action="{{ route('reports.pdf') }}" target="_blank" class="inline">
                        @csrf
                        <input type="hidden" name="entity_type" value="{{ $entity_type }}">
                        <input type="hidden" name="start_date" value="{{ $startDate }}">
                        <input type="hidden" name="end_date" value="{{ $endDate }}">
                        @if($groupBy)
                            <input type="hidden" name="group_by" value="{{ $groupBy }}">
                        @endif
                        @foreach($filters as $key => $value)
                            <input type="hidden" name="filters[{{ $key }}]" value="{{ $value }}">
                        @endforeach
                        <button type="submit" 
                                class="inline-flex items-center px-4 py-2 bg-red-600 text-white text-sm font-semibold rounded-lg hover:bg-red-700 transition-colors">
                            <i class="fas fa-file-pdf mr-2"></i>
                            Exportar PDF
                        </button>
                    </form>
                </div>
            </div>
            
            <!-- Summary Cards - Enhanced with more details -->
            @if(isset($reportData['summary']))
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
                    @foreach($reportData['summary'] as $key => $value)
                        @if(is_numeric($value) && !is_array($value))
                            <div class="bg-gradient-to-br from-violet-50 to-violet-100 rounded-xl p-4 border border-violet-200 hover:shadow-md transition-shadow">
                                <div class="flex items-center justify-between mb-1">
                                    <p class="text-xs font-semibold text-violet-700 uppercase tracking-wider">{{ app(\App\Services\ReportService::class)->translateSummaryKey($key) }}</p>
                                    @if(str_contains($key, 'total') || str_contains($key, 'amount') || str_contains($key, 'sales') || str_contains($key, 'revenue'))
                                        <i class="fas fa-dollar-sign text-violet-600 text-xs"></i>
                                    @elseif(str_contains($key, 'count'))
                                        <i class="fas fa-hashtag text-violet-600 text-xs"></i>
                                    @endif
                                </div>
                                <p class="text-2xl font-bold text-violet-900 mt-1">
                                    @if(str_contains($key, 'revenue') || str_contains($key, 'amount') || str_contains($key, 'cash') || str_contains($key, 'transfer') || str_contains($key, 'debt') || str_contains($key, 'deposit') || str_contains($key, 'pending') || 
                                        (str_contains($key, 'total') && !str_contains($key, 'count') && !str_contains($key, 'products') && !str_contains($key, 'rooms') && !str_contains($key, 'reservations') && !str_contains($key, 'receptionists') && !str_contains($key, 'customers')) ||
                                        (str_contains($key, 'sales') && !str_contains($key, 'count')))
                                        ${{ number_format($value, 2, ',', '.') }}
                                    @else
                                        {{ number_format($value, 0, ',', '.') }}
                                    @endif
                                </p>
                            </div>
                        @endif
                    @endforeach
                    
                    <!-- Additional Summary Cards for Sales Module -->
                    @if($entity_type === 'sales' && isset($reportData['summary']['room_sales_count']))
                        <div class="bg-gradient-to-br from-blue-50 to-blue-100 rounded-xl p-4 border border-blue-200 hover:shadow-md transition-shadow">
                            <div class="flex items-center justify-between mb-1">
                                <p class="text-xs font-semibold text-blue-700 uppercase tracking-wider">Ventas en Habitaciones</p>
                                <i class="fas fa-door-open text-blue-600 text-xs"></i>
                            </div>
                            <p class="text-xl font-bold text-blue-900 mt-1">{{ number_format($reportData['summary']['room_sales_count'] ?? 0, 0, ',', '.') }}</p>
                            <p class="text-xs text-blue-600 mt-1">${{ number_format($reportData['summary']['room_sales_total'] ?? 0, 2, ',', '.') }}</p>
                        </div>
                        <div class="bg-gradient-to-br from-green-50 to-green-100 rounded-xl p-4 border border-green-200 hover:shadow-md transition-shadow">
                            <div class="flex items-center justify-between mb-1">
                                <p class="text-xs font-semibold text-green-700 uppercase tracking-wider">Ventas Normales</p>
                                <i class="fas fa-user text-green-600 text-xs"></i>
                            </div>
                            <p class="text-xl font-bold text-green-900 mt-1">{{ number_format($reportData['summary']['individual_sales_count'] ?? 0, 0, ',', '.') }}</p>
                            <p class="text-xs text-green-600 mt-1">${{ number_format($reportData['summary']['individual_sales_total'] ?? 0, 2, ',', '.') }}</p>
                        </div>
                    @endif
                </div>
            @endif

            <!-- Charts Section - Compact and Minimalist - Show for ALL modules -->
            @if(!empty($reportData) && (isset($reportData['grouped']) || isset($reportData['summary'])))
                <div class="mb-6">
                    <h3 class="text-sm font-semibold text-gray-700 uppercase tracking-wider mb-3 flex items-center">
                        <i class="fas fa-chart-bar mr-2 text-violet-600"></i>
                        Visualización Gráfica
                    </h3>
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                        <!-- Charts for Sales Module -->
                        @if($entity_type === 'sales')
                            @if($groupBy === 'receptionist')
                                <!-- Receptionist Sales Chart -->
                                <div class="bg-white rounded-lg border border-gray-200 p-3 shadow-sm" wire:ignore>
                                    <h4 class="text-xs font-semibold text-gray-600 uppercase tracking-wider mb-2 flex items-center">
                                        <i class="fas fa-user-tie mr-1 text-violet-600"></i>Ventas por Recepcionista
                                    </h4>
                                    <div style="height: 180px;">
                                        <canvas id="receptionistChart"></canvas>
                                    </div>
                                </div>
                                <!-- Payment Method Chart -->
                                <div class="bg-white rounded-lg border border-gray-200 p-3 shadow-sm" wire:ignore>
                                    <h4 class="text-xs font-semibold text-gray-600 uppercase tracking-wider mb-2 flex items-center">
                                        <i class="fas fa-money-bill-wave mr-1 text-violet-600"></i>Método de Pago
                                    </h4>
                                    <div style="height: 180px;">
                                        <canvas id="paymentChart"></canvas>
                                    </div>
                                </div>
                            @endif
                            <!-- Payment Method Distribution -->
                            <div class="bg-white rounded-lg border border-gray-200 p-3 shadow-sm" wire:ignore>
                                <h4 class="text-xs font-semibold text-gray-600 uppercase tracking-wider mb-2 flex items-center">
                                    <i class="fas fa-chart-pie mr-1 text-violet-600"></i>Distribución de Pagos
                                </h4>
                                <div style="height: 180px;">
                                    <canvas id="salesPaymentChart"></canvas>
                                </div>
                            </div>
                            <!-- Sales Type Distribution -->
                            <div class="bg-white rounded-lg border border-gray-200 p-3 shadow-sm" wire:ignore>
                                <h4 class="text-xs font-semibold text-gray-600 uppercase tracking-wider mb-2 flex items-center">
                                    <i class="fas fa-chart-pie mr-1 text-violet-600"></i>Tipo de Venta
                                </h4>
                                <div style="height: 180px;">
                                    <canvas id="salesTypeChart"></canvas>
                                </div>
                            </div>
                            <!-- Default Bar Chart for Sales -->
                            <div class="bg-white rounded-lg border border-gray-200 p-3 shadow-sm" wire:ignore>
                                <h4 class="text-xs font-semibold text-gray-600 uppercase tracking-wider mb-2 flex items-center">
                                    <i class="fas fa-chart-bar mr-1 text-violet-600"></i>Distribución de Ventas
                                </h4>
                                <div style="height: 180px;">
                                    <canvas id="groupedChart-sales"></canvas>
                                </div>
                            </div>
                        @endif

                        <!-- Charts for Rooms Module -->
                        @if($entity_type === 'rooms')
                            <div class="bg-white rounded-lg border border-gray-200 p-3 shadow-sm" wire:ignore>
                                <h4 class="text-xs font-semibold text-gray-600 uppercase tracking-wider mb-2 flex items-center">
                                    <i class="fas fa-door-open mr-1 text-violet-600"></i>Estado de Habitaciones
                                </h4>
                                <div style="height: 180px;">
                                    <canvas id="summaryPieChart"></canvas>
                                </div>
                            </div>
                            <div class="bg-white rounded-lg border border-gray-200 p-3 shadow-sm" wire:ignore>
                                <h4 class="text-xs font-semibold text-gray-600 uppercase tracking-wider mb-2 flex items-center">
                                    <i class="fas fa-chart-bar mr-1 text-violet-600"></i>Distribución
                                </h4>
                                <div style="height: 180px;">
                                    <canvas id="groupedChart-rooms"></canvas>
                                </div>
                            </div>
                        @endif

                        <!-- Charts for Reservations Module -->
                        @if($entity_type === 'reservations')
                            <div class="bg-white rounded-lg border border-gray-200 p-3 shadow-sm" wire:ignore>
                                <h4 class="text-xs font-semibold text-gray-600 uppercase tracking-wider mb-2 flex items-center">
                                    <i class="fas fa-calendar-check mr-1 text-violet-600"></i>Distribución de Reservas
                                </h4>
                                <div style="height: 180px;">
                                    <canvas id="groupedChart-reservations"></canvas>
                                </div>
                            </div>
                            <div class="bg-white rounded-lg border border-gray-200 p-3 shadow-sm" wire:ignore>
                                <h4 class="text-xs font-semibold text-gray-600 uppercase tracking-wider mb-2 flex items-center">
                                    <i class="fas fa-chart-pie mr-1 text-violet-600"></i>Proporción (Pagado vs Pendiente)
                                </h4>
                                <div style="height: 180px;">
                                    <canvas id="pieChart-reservations"></canvas>
                                </div>
                            </div>
                        @endif

                        <!-- Charts for Receptionists Module -->
                        @if($entity_type === 'receptionists')
                            <div class="bg-white rounded-lg border border-gray-200 p-3 shadow-sm" wire:ignore>
                                <h4 class="text-xs font-semibold text-gray-600 uppercase tracking-wider mb-2 flex items-center">
                                    <i class="fas fa-user-tie mr-1 text-violet-600"></i>Rendimiento por Recepcionista
                                </h4>
                                <div style="height: 180px;">
                                    <canvas id="groupedChart-receptionists"></canvas>
                                </div>
                            </div>
                        @endif

                        <!-- Charts for Cleaning Module -->
                        @if($entity_type === 'cleaning')
                            <div class="bg-white rounded-lg border border-gray-200 p-3 shadow-sm" wire:ignore>
                                <h4 class="text-xs font-semibold text-gray-600 uppercase tracking-wider mb-2 flex items-center">
                                    <i class="fas fa-broom mr-1 text-violet-600"></i>Estado de Limpieza
                                </h4>
                                <div style="height: 180px;">
                                    <canvas id="cleaningStatusChart"></canvas>
                                </div>
                            </div>
                            <div class="bg-white rounded-lg border border-gray-200 p-3 shadow-sm" wire:ignore>
                                <h4 class="text-xs font-semibold text-gray-600 uppercase tracking-wider mb-2 flex items-center">
                                    <i class="fas fa-chart-bar mr-1 text-violet-600"></i>Distribución
                                </h4>
                                <div style="height: 180px;">
                                    <canvas id="groupedChart-cleaning"></canvas>
                                </div>
                            </div>
                        @endif

                        <!-- Charts for Customers Module -->
                        @if($entity_type === 'customers')
                            <div class="bg-white rounded-lg border border-gray-200 p-3 shadow-sm" wire:ignore>
                                <h4 class="text-xs font-semibold text-gray-600 uppercase tracking-wider mb-2 flex items-center">
                                    <i class="fas fa-users mr-1 text-violet-600"></i>Frecuencia de Clientes
                                </h4>
                                <div style="height: 180px;">
                                    <canvas id="groupedChart-customers"></canvas>
                                </div>
                            </div>
                        @endif

                        <!-- Charts for Products Module -->
                        @if($entity_type === 'products')
                            <div class="bg-white rounded-lg border border-gray-200 p-3 shadow-sm" wire:ignore>
                                <h4 class="text-xs font-semibold text-gray-600 uppercase tracking-wider mb-2 flex items-center">
                                    <i class="fas fa-box mr-1 text-violet-600"></i>Rendimiento de Productos
                                </h4>
                                <div style="height: 180px;">
                                    <canvas id="groupedChart-products"></canvas>
                                </div>
                            </div>
                        @endif

                        <!-- Charts for Electronic Invoices Module -->
                        @if($entity_type === 'electronic_invoices')
                            <div class="bg-white rounded-lg border border-gray-200 p-3 shadow-sm" wire:ignore>
                                <h4 class="text-xs font-semibold text-gray-600 uppercase tracking-wider mb-2 flex items-center">
                                    <i class="fas fa-file-invoice mr-1 text-violet-600"></i>Distribución de Facturas
                                </h4>
                                <div style="height: 180px;">
                                    <canvas id="groupedChart-electronic_invoices"></canvas>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            @endif

            <!-- Tabla Detallada de Ventas -->
            @if($entity_type === 'sales' && !empty($reportData['data'] ?? []))
                <div class="mb-6" wire:key="table-sales">
                    <h3 class="text-md font-semibold text-gray-900 mb-3">Desglose Detallado de Ventas</h3>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Fecha</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Recepcionista</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Tipo</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Habitación</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Método de Pago</th>
                                    <th class="px-4 py-3 text-right text-xs font-semibold text-gray-700 uppercase tracking-wider">Total</th>
                                    <th class="px-4 py-3 text-right text-xs font-semibold text-gray-700 uppercase tracking-wider">Efectivo</th>
                                    <th class="px-4 py-3 text-right text-xs font-semibold text-gray-700 uppercase tracking-wider">Transferencia</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Estado</th>
                                    <th class="px-4 py-3 text-center text-xs font-semibold text-gray-700 uppercase tracking-wider">Acciones</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($reportData['data'] as $sale)
                                    @php
                                        $hasInvoice = false;
                                        if ($sale->room_id && $sale->room) {
                                            $reservation = $sale->room->reservations()
                                                ->where('check_in_date', '<=', $sale->sale_date)
                                                ->where('check_out_date', '>=', $sale->sale_date)
                                                ->first();
                                            if ($reservation && $reservation->customer_id) {
                                                $hasInvoice = \App\Models\ElectronicInvoice::where('customer_id', $reservation->customer_id)
                                                    ->whereNotNull('payment_method_code')
                                                    ->exists();
                                            }
                                        }
                                    @endphp
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-4 py-3 text-sm text-gray-900">
                                            {{ \Carbon\Carbon::parse($sale->sale_date)->translatedFormat('d/m/Y') }}
                                            <span class="text-[10px] text-gray-400 block">{{ $sale->created_at->format('H:i') }}</span>
                                        </td>
                                        <td class="px-4 py-3 text-sm font-semibold text-gray-900">
                                            {{ $sale->user->name ?? 'N/A' }}
                                        </td>
                                        <td class="px-4 py-3 text-sm">
                                            @if($sale->room_id)
                                                <span class="px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">
                                                    Habitación
                                                </span>
                                            @else
                                                <span class="px-2 py-1 text-xs font-semibold rounded-full bg-emerald-100 text-emerald-800">
                                                    Venta Normal
                                                </span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3 text-sm text-gray-600">
                                            @if($sale->room_id && $sale->room)
                                                Habitación {{ $sale->room->room_number }}
                                            @else
                                                <span class="text-gray-400">-</span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3 text-sm">
                                            @if($sale->payment_method === 'efectivo')
                                                <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                                                    Efectivo
                                                </span>
                                            @elseif($sale->payment_method === 'transferencia')
                                                <span class="px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">
                                                    Transferencia
                                                </span>
                                            @elseif($sale->payment_method === 'ambos')
                                                <span class="px-2 py-1 text-xs font-semibold rounded-full bg-purple-100 text-purple-800">
                                                    Ambos
                                                </span>
                                            @else
                                                <span class="px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800">
                                                    Pendiente
                                                </span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3 text-sm text-gray-900 text-right font-semibold">
                                            ${{ number_format($sale->total, 2, ',', '.') }}
                                        </td>
                                        <td class="px-4 py-3 text-sm text-green-600 text-right">
                                            ${{ number_format($sale->cash_amount ?? 0, 2, ',', '.') }}
                                        </td>
                                        <td class="px-4 py-3 text-sm text-blue-600 text-right">
                                            ${{ number_format($sale->transfer_amount ?? 0, 2, ',', '.') }}
                                        </td>
                                        <td class="px-4 py-3 text-sm">
                                            @if($sale->debt_status === 'pagado')
                                                <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                                                    Pagado
                                                </span>
                                            @else
                                                <span class="px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">
                                                    Pendiente
                                                </span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3 text-sm text-center">
                                            <button type="button" wire:click="showDetails('sale', {{ $sale->id }})" class="text-violet-600 hover:text-violet-800 font-semibold text-xs">
                                                <i class="fas fa-eye mr-1"></i> Ver Detalles
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot class="bg-gray-50">
                                <tr>
                                    <td colspan="10" class="px-4 py-3 text-sm font-semibold text-gray-900 text-right">Total:</td>
                                    <td class="px-4 py-3 text-sm font-bold text-gray-900 text-right">
                                        ${{ number_format(collect($reportData['data'])->sum('total'), 2, ',', '.') }}
                                    </td>
                                    <td class="px-4 py-3 text-sm font-bold text-green-600 text-right">
                                        ${{ number_format(collect($reportData['data'])->sum('cash_amount'), 2, ',', '.') }}
                                    </td>
                                    <td class="px-4 py-3 text-sm font-bold text-blue-600 text-right">
                                        ${{ number_format(collect($reportData['data'])->sum('transfer_amount'), 2, ',', '.') }}
                                    </td>
                                    <td></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            @elseif($entity_type === 'sales' && (empty($reportData['data'] ?? []) || count($reportData['data']) === 0))
                <div class="mb-6 bg-white rounded-lg border-2 border-gray-300 p-8 text-center" wire:key="empty-sales">
                    <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-gray-100 mb-4">
                        <i class="fas fa-shopping-cart text-2xl text-gray-400"></i>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">No hay ventas</h3>
                    <p class="text-sm text-gray-600">No se encontraron ventas en el período seleccionado.</p>
                </div>
            @endif

            <!-- Tabla Detallada de Habitaciones -->
            @if($entity_type === 'rooms' && !empty($reportData['detailed_data'] ?? []) && count($reportData['detailed_data']) > 0)
                <div class="mb-6" wire:key="table-rooms">
                    <h3 class="text-md font-semibold text-gray-900 mb-3">Desglose Detallado de Habitaciones</h3>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Habitación</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Estado</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Cliente Actual</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Check-in</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Check-out</th>
                                    <th class="px-4 py-3 text-right text-xs font-semibold text-gray-700 uppercase tracking-wider">Total</th>
                                    <th class="px-4 py-3 text-right text-xs font-semibold text-gray-700 uppercase tracking-wider">Pagado</th>
                                    <th class="px-4 py-3 text-right text-xs font-semibold text-gray-700 uppercase tracking-wider">Pendiente</th>
                                    <th class="px-4 py-3 text-center text-xs font-semibold text-gray-700 uppercase tracking-wider">Acciones</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($reportData['detailed_data'] as $roomData)
                                    @php
                                        $currentReservation = collect($roomData['reservations_history'] ?? [])
                                            ->filter(fn($r) => $r['date'] === $endDate)
                                            ->first()['reservation'] ?? null;
                                        if (!$currentReservation && !empty($roomData['reservations_history'])) {
                                            $currentReservation = collect($roomData['reservations_history'])->last()['reservation'] ?? null;
                                        }
                                    @endphp
                                    <tr class="hover:bg-gray-50 cursor-pointer" 
                                        wire:click="showDetails('room', {{ $roomData['id'] }})">
                                        <td class="px-4 py-3 text-sm font-semibold text-gray-900">
                                            Habitación {{ $roomData['room_number'] }}
                                        </td>
                                        <td class="px-4 py-3 text-sm">
                                            <span class="px-2 py-1 text-xs font-semibold rounded-full bg-violet-100 text-violet-800">
                                                {{ $roomData['status_label'] }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-3 text-sm text-gray-900">
                                            {{ $currentReservation['customer_name'] ?? 'Disponible' }}
                                        </td>
                                        <td class="px-4 py-3 text-sm text-gray-500">
                                            {{ $currentReservation['check_in_date'] ?? '-' }}
                                        </td>
                                        <td class="px-4 py-3 text-sm text-gray-500">
                                            {{ $currentReservation['check_out_date'] ?? '-' }}
                                        </td>
                                        <td class="px-4 py-3 text-sm text-gray-900 text-right font-semibold">
                                            @if($currentReservation)
                                                ${{ number_format($currentReservation['total_amount'], 2, ',', '.') }}
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td class="px-4 py-3 text-sm text-green-600 text-right font-semibold">
                                            @if($currentReservation)
                                                ${{ number_format($currentReservation['deposit'], 2, ',', '.') }}
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td class="px-4 py-3 text-sm text-red-600 text-right font-semibold">
                                            @if($currentReservation)
                                                ${{ number_format($currentReservation['pending_amount'], 2, ',', '.') }}
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td class="px-4 py-3 text-sm text-center">
                                            <button type="button" wire:click="showDetails('room', {{ $roomData['id'] }})" class="text-violet-600 hover:text-violet-800 font-semibold text-xs">
                                                <i class="fas fa-eye mr-1"></i> Ver Detalles
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @elseif($entity_type === 'rooms' && (empty($reportData['detailed_data'] ?? []) || count($reportData['detailed_data']) === 0))
                <div class="mb-6 bg-white rounded-lg border-2 border-gray-300 p-8 text-center" wire:key="empty-rooms">
                    <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-gray-100 mb-4">
                        <i class="fas fa-door-open text-2xl text-gray-400"></i>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">No hay habitaciones</h3>
                    <p class="text-sm text-gray-600">No se encontraron habitaciones en el período seleccionado.</p>
                </div>
            @endif

            <!-- Tabla Detallada de Clientes -->
            @if($entity_type === 'customers' && !empty($reportData['data'] ?? []))
                <div class="mb-6" wire:key="table-customers">
                    <h3 class="text-md font-semibold text-gray-900 mb-3">Desglose Detallado de Clientes</h3>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Cliente</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Email</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Teléfono</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Estado</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Facturación Electrónica</th>
                                    <th class="px-4 py-3 text-right text-xs font-semibold text-gray-700 uppercase tracking-wider">Reservaciones</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Fecha Registro</th>
                                    <th class="px-4 py-3 text-center text-xs font-semibold text-gray-700 uppercase tracking-wider">Acciones</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($reportData['data'] as $customer)
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-4 py-3 text-sm font-semibold text-gray-900">{{ $customer->name }}</td>
                                        <td class="px-4 py-3 text-sm text-gray-600">{{ $customer->email ?? '-' }}</td>
                                        <td class="px-4 py-3 text-sm text-gray-600">{{ $customer->phone ?? '-' }}</td>
                                        <td class="px-4 py-3 text-sm">
                                            <span class="px-2 py-1 text-xs font-semibold rounded-full {{ $customer->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                                {{ $customer->is_active ? 'Activo' : 'Inactivo' }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-3 text-sm">
                                            @if($customer->requires_electronic_invoice)
                                                <span class="px-2 py-1 text-xs font-semibold rounded-full bg-violet-100 text-violet-800">Sí</span>
                                            @else
                                                <span class="px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800">No</span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3 text-sm text-gray-900 text-right font-semibold">{{ $customer->reservations_count ?? 0 }}</td>
                                        <td class="px-4 py-3 text-sm text-gray-500">{{ $customer->created_at->translatedFormat('d/m/Y') }}</td>
                                        <td class="px-4 py-3 text-sm text-center">
                                            <button type="button" wire:click="showDetails('customer', {{ $customer->id }})" class="text-violet-600 hover:text-violet-800 font-semibold text-xs">
                                                <i class="fas fa-eye mr-1"></i> Ver Detalles
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif

            <!-- Tabla Detallada de Productos -->
            @if($entity_type === 'products' && !empty($reportData['data'] ?? []))
                <div class="mb-6" wire:key="table-products">
                    <h3 class="text-md font-semibold text-gray-900 mb-3">Desglose Detallado de Productos</h3>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Producto</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">SKU</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Categoría</th>
                                    <th class="px-4 py-3 text-right text-xs font-semibold text-gray-700 uppercase tracking-wider">Cantidad</th>
                                    <th class="px-4 py-3 text-right text-xs font-semibold text-gray-700 uppercase tracking-wider">Precio</th>
                                    <th class="px-4 py-3 text-right text-xs font-semibold text-gray-700 uppercase tracking-wider">Valor Total</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Estado</th>
                                    <th class="px-4 py-3 text-center text-xs font-semibold text-gray-700 uppercase tracking-wider">Acciones</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($reportData['data'] as $product)
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-4 py-3 text-sm font-semibold text-gray-900">{{ $product->name }}</td>
                                        <td class="px-4 py-3 text-sm text-gray-600">{{ $product->sku ?? '-' }}</td>
                                        <td class="px-4 py-3 text-sm text-gray-600">{{ $product->category->name ?? 'Sin categoría' }}</td>
                                        <td class="px-4 py-3 text-sm text-gray-900 text-right font-semibold">
                                            <span class="{{ $product->hasLowStock() ? 'text-orange-600' : ($product->quantity == 0 ? 'text-red-600' : 'text-gray-900') }}">
                                                {{ $product->quantity }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-3 text-sm text-gray-900 text-right">${{ number_format($product->price, 2, ',', '.') }}</td>
                                        <td class="px-4 py-3 text-sm text-gray-900 text-right font-semibold">${{ number_format($product->quantity * $product->price, 2, ',', '.') }}</td>
                                        <td class="px-4 py-3 text-sm">
                                            <span class="px-2 py-1 text-xs font-semibold rounded-full {{ $product->status === 'active' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                                {{ ucfirst($product->status) }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-3 text-sm text-center">
                                            <button type="button" wire:click="showDetails('product', {{ $product->id }})" class="text-violet-600 hover:text-violet-800 font-semibold text-xs">
                                                <i class="fas fa-eye mr-1"></i> Ver Detalles
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif

            <!-- Tabla Detallada de Limpieza -->
            @if($entity_type === 'cleaning' && !empty($reportData['detailed_data'] ?? []))
                <div class="mb-6" wire:key="table-cleaning">
                    <h3 class="text-md font-semibold text-gray-900 mb-3">Desglose Detallado de Habitaciones en Limpieza</h3>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Habitación</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Estado</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Cliente Actual</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Check-in</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Check-out</th>
                                    <th class="px-4 py-3 text-right text-xs font-semibold text-gray-700 uppercase tracking-wider">Total</th>
                                    <th class="px-4 py-3 text-right text-xs font-semibold text-gray-700 uppercase tracking-wider">Pagado</th>
                                    <th class="px-4 py-3 text-right text-xs font-semibold text-gray-700 uppercase tracking-wider">Pendiente</th>
                                    <th class="px-4 py-3 text-center text-xs font-semibold text-gray-700 uppercase tracking-wider">Acciones</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($reportData['detailed_data'] as $roomData)
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-4 py-3 text-sm font-semibold text-gray-900">
                                            Habitación {{ $roomData['room_number'] }}
                                        </td>
                                        <td class="px-4 py-3 text-sm">
                                            <span class="px-2 py-1 text-xs font-semibold rounded-full {{ $roomData['status'] === 'limpieza' ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-800' }}">
                                                {{ $roomData['status_label'] }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-3 text-sm text-gray-900">
                                            {{ $roomData['current_customer'] }}
                                        </td>
                                        <td class="px-4 py-3 text-sm text-gray-500">
                                            {{ $roomData['check_in_date'] }}
                                        </td>
                                        <td class="px-4 py-3 text-sm text-gray-500">
                                            {{ $roomData['check_out_date'] }}
                                        </td>
                                        <td class="px-4 py-3 text-sm text-gray-900 text-right font-semibold">
                                            @if($roomData['total_amount'] > 0)
                                                ${{ number_format($roomData['total_amount'], 2, ',', '.') }}
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td class="px-4 py-3 text-sm text-green-600 text-right font-semibold">
                                            @if($roomData['deposit'] > 0)
                                                ${{ number_format($roomData['deposit'], 2, ',', '.') }}
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td class="px-4 py-3 text-sm text-red-600 text-right font-semibold">
                                            @if($roomData['pending_amount'] > 0)
                                                ${{ number_format($roomData['pending_amount'], 2, ',', '.') }}
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td class="px-4 py-3 text-sm text-center">
                                            <button type="button" wire:click="showDetails('room', {{ $roomData['id'] }})" class="text-violet-600 hover:text-violet-800 font-semibold text-xs">
                                                <i class="fas fa-eye mr-1"></i> Ver Detalles
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @elseif($entity_type === 'cleaning' && empty($reportData['detailed_data'] ?? []))
                <div class="mb-6 bg-white rounded-lg border-2 border-gray-300 p-8 text-center" wire:key="empty-cleaning">
                    <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-gray-100 mb-4">
                        <i class="fas fa-broom text-2xl text-gray-400"></i>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">No hay habitaciones en limpieza</h3>
                    <p class="text-sm text-gray-600">No se encontraron habitaciones que requieran limpieza en el período seleccionado.</p>
                </div>
            @endif

            <!-- Tabla Detallada de Reservaciones -->
            @if($entity_type === 'reservations' && !empty($reportData['detailed_data'] ?? []))
                <div class="mb-6" wire:key="table-reservations">
                    <h3 class="text-md font-semibold text-gray-900 mb-3">Desglose Detallado de Reservaciones</h3>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Cliente</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Teléfono</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Habitación</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Check-in</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Check-out</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Huéspedes</th>
                                    <th class="px-4 py-3 text-right text-xs font-semibold text-gray-700 uppercase tracking-wider">Total</th>
                                    <th class="px-4 py-3 text-right text-xs font-semibold text-gray-700 uppercase tracking-wider">Pagado</th>
                                    <th class="px-4 py-3 text-right text-xs font-semibold text-gray-700 uppercase tracking-wider">Pendiente</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Método Pago</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Estado</th>
                                    <th class="px-4 py-3 text-center text-xs font-semibold text-gray-700 uppercase tracking-wider">Acciones</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($reportData['detailed_data'] as $reservation)
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-4 py-3 text-sm font-semibold text-gray-900">{{ $reservation['customer_name'] }}</td>
                                        <td class="px-4 py-3 text-sm text-gray-600">{{ $reservation['customer_phone'] }}</td>
                                        <td class="px-4 py-3 text-sm text-gray-600">Habitación {{ $reservation['room_number'] }}</td>
                                        <td class="px-4 py-3 text-sm text-gray-500">{{ $reservation['check_in_date'] }}</td>
                                        <td class="px-4 py-3 text-sm text-gray-500">{{ $reservation['check_out_date'] }}</td>
                                        <td class="px-4 py-3 text-sm text-gray-600 text-center">{{ $reservation['guests_count'] }}</td>
                                        <td class="px-4 py-3 text-sm text-gray-900 text-right font-semibold">${{ number_format($reservation['total_amount'], 2, ',', '.') }}</td>
                                        <td class="px-4 py-3 text-sm text-green-600 text-right font-semibold">${{ number_format($reservation['deposit'], 2, ',', '.') }}</td>
                                        <td class="px-4 py-3 text-sm text-red-600 text-right font-semibold">${{ number_format($reservation['pending_amount'], 2, ',', '.') }}</td>
                                        <td class="px-4 py-3 text-sm text-gray-600">{{ ucfirst($reservation['payment_method']) }}</td>
                                        <td class="px-4 py-3 text-sm">
                                            @if($reservation['payment_status'] === 'paid')
                                                <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">Pagado</span>
                                            @elseif($reservation['payment_status'] === 'partially_paid')
                                                <span class="px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">Parcial</span>
                                            @else
                                                <span class="px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">Pendiente</span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3 text-sm text-center">
                                            <button type="button" wire:click="showDetails('reservation', {{ $reservation['id'] }})" class="text-violet-600 hover:text-violet-800 font-semibold text-xs">
                                                <i class="fas fa-eye mr-1"></i> Ver Detalles
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @elseif($entity_type === 'reservations' && empty($reportData['detailed_data'] ?? []))
                <div class="mb-6 bg-white rounded-lg border-2 border-gray-300 p-8 text-center" wire:key="empty-reservations">
                    <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-gray-100 mb-4">
                        <i class="fas fa-calendar-check text-2xl text-gray-400"></i>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">No hay reservaciones</h3>
                    <p class="text-sm text-gray-600">No se encontraron reservaciones en el período seleccionado.</p>
                </div>
            @endif

            <!-- Tabla Detallada de Recepcionistas -->
            @if($entity_type === 'receptionists' && !empty($reportData['detailed_data'] ?? []))
                <div class="mb-6" wire:key="table-receptionists">
                    <h3 class="text-md font-semibold text-gray-900 mb-3">Desglose Detallado de Recepcionistas</h3>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Recepcionista</th>
                                    <th class="px-4 py-3 text-right text-xs font-semibold text-gray-700 uppercase tracking-wider">Total Ventas</th>
                                    <th class="px-4 py-3 text-right text-xs font-semibold text-gray-700 uppercase tracking-wider">Cantidad Ventas</th>
                                    <th class="px-4 py-3 text-right text-xs font-semibold text-gray-700 uppercase tracking-wider">Efectivo</th>
                                    <th class="px-4 py-3 text-right text-xs font-semibold text-gray-700 uppercase tracking-wider">Transferencia</th>
                                    <th class="px-4 py-3 text-center text-xs font-semibold text-gray-700 uppercase tracking-wider">Acciones</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($reportData['detailed_data'] as $receptionist)
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-4 py-3 text-sm font-semibold text-gray-900">{{ $receptionist['name'] }}</td>
                                        <td class="px-4 py-3 text-sm text-gray-900 text-right font-semibold">${{ number_format($receptionist['total_sales'], 2, ',', '.') }}</td>
                                        <td class="px-4 py-3 text-sm text-gray-600 text-right">{{ $receptionist['sales_count'] }}</td>
                                        <td class="px-4 py-3 text-sm text-green-600 text-right">${{ number_format($receptionist['cash'], 2, ',', '.') }}</td>
                                        <td class="px-4 py-3 text-sm text-blue-600 text-right">${{ number_format($receptionist['transfer'], 2, ',', '.') }}</td>
                                        <td class="px-4 py-3 text-sm text-center">
                                            <button type="button" wire:click="showDetails('receptionist', {{ $receptionist['id'] }})" class="text-violet-600 hover:text-violet-800 font-semibold text-xs">
                                                <i class="fas fa-eye mr-1"></i> Ver Detalles
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @elseif($entity_type === 'receptionists' && empty($reportData['detailed_data'] ?? []))
                <div class="mb-6 bg-white rounded-lg border-2 border-gray-300 p-8 text-center" wire:key="empty-receptionists">
                    <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-gray-100 mb-4">
                        <i class="fas fa-user-tie text-2xl text-gray-400"></i>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">No hay datos de recepcionistas</h3>
                    <p class="text-sm text-gray-600">No se encontraron ventas realizadas por recepcionistas en el período seleccionado.</p>
                </div>
            @endif

            <!-- Tabla Detallada de Facturas Electrónicas -->
            @if($entity_type === 'electronic_invoices' && !empty($reportData['data'] ?? []))
                <div class="mb-6" wire:key="table-invoices">
                    <h3 class="text-md font-semibold text-gray-900 mb-3">Desglose Detallado de Facturas Electrónicas</h3>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Cliente</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Tipo Documento</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Estado</th>
                                    <th class="px-4 py-3 text-right text-xs font-semibold text-gray-700 uppercase tracking-wider">Total</th>
                                    <th class="px-4 py-3 text-right text-xs font-semibold text-gray-700 uppercase tracking-wider">Impuestos</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">CUFE</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Fecha</th>
                                    <th class="px-4 py-3 text-center text-xs font-semibold text-gray-700 uppercase tracking-wider">Acciones</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($reportData['data'] as $invoice)
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-4 py-3 text-sm font-semibold text-gray-900">{{ $invoice->customer->name ?? 'N/A' }}</td>
                                        <td class="px-4 py-3 text-sm text-gray-600">{{ $invoice->documentType->name ?? 'N/A' }}</td>
                                        <td class="px-4 py-3 text-sm">
                                            <span class="px-2 py-1 text-xs font-semibold rounded-full {{ $invoice->status === 'validated' ? 'bg-green-100 text-green-800' : ($invoice->status === 'pending' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800') }}">
                                                {{ ucfirst($invoice->status) }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-3 text-sm text-gray-900 text-right font-semibold">${{ number_format($invoice->total, 2, ',', '.') }}</td>
                                        <td class="px-4 py-3 text-sm text-gray-600 text-right">${{ number_format($invoice->tax_amount, 2, ',', '.') }}</td>
                                        <td class="px-4 py-3 text-sm text-gray-500 font-mono text-xs">{{ $invoice->cufe ?? '-' }}</td>
                                        <td class="px-4 py-3 text-sm text-gray-500">{{ $invoice->created_at->translatedFormat('d/m/Y') }}</td>
                                        <td class="px-4 py-3 text-sm text-center">
                                            @if($invoice->sale_id)
                                                <button type="button" wire:click="showDetails('sale', {{ $invoice->sale_id }})" class="text-violet-600 hover:text-violet-800 font-semibold text-xs">
                                                    <i class="fas fa-eye mr-1"></i> Ver Venta
                                                </button>
                                            @else
                                                <span class="text-gray-400 text-xs">Sin Venta Asociada</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif

            <!-- Grouped Data -->
            @if(!empty($reportData['grouped']) && !empty($groupBy))
                <div class="mb-6" wire:key="table-grouped">
                    <h3 class="text-md font-semibold text-gray-900 mb-3">Resumen Agrupado por {{ $groupByLabel }}</h3>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Nombre</th>
                                    <th class="px-4 py-3 text-right text-xs font-semibold text-gray-700 uppercase tracking-wider">Total</th>
                                    <th class="px-4 py-3 text-right text-xs font-semibold text-gray-700 uppercase tracking-wider">Cantidad</th>
                                    @if($entity_type === 'sales' && $groupBy === 'receptionist')
                                        <th class="px-4 py-3 text-right text-xs font-semibold text-gray-700 uppercase tracking-wider">Efectivo</th>
                                        <th class="px-4 py-3 text-right text-xs font-semibold text-gray-700 uppercase tracking-wider">Transferencia</th>
                                    @endif
                                    @if($entity_type === 'products' && isset($reportData['grouped'][0]['total_value']))
                                        <th class="px-4 py-3 text-right text-xs font-semibold text-gray-700 uppercase tracking-wider">Valor Total</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($reportData['grouped'] as $item)
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-4 py-3 text-sm text-gray-900">{{ $item['name'] ?? $item['id'] ?? 'N/D' }}</td>
                                        <td class="px-4 py-3 text-sm text-gray-900 text-right font-semibold">
                                            @if(isset($item['total']) || isset($item['total_amount']) || isset($item['total_sales']))
                                                ${{ number_format($item['total'] ?? $item['total_amount'] ?? $item['total_sales'] ?? 0, 2, ',', '.') }}
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td class="px-4 py-3 text-sm text-gray-500 text-right">{{ $item['count'] ?? $item['sales_count'] ?? '-' }}</td>
                                        @if($entity_type === 'sales' && $groupBy === 'receptionist')
                                            <td class="px-4 py-3 text-sm text-gray-500 text-right">${{ number_format($item['cash'] ?? 0, 2, ',', '.') }}</td>
                                            <td class="px-4 py-3 text-sm text-gray-500 text-right">${{ number_format($item['transfer'] ?? 0, 2, ',', '.') }}</td>
                                        @endif
                                        @if($entity_type === 'products' && isset($item['total_value']))
                                            <td class="px-4 py-3 text-sm text-gray-900 text-right font-semibold">${{ number_format($item['total_value'], 2, ',', '.') }}</td>
                                        @endif
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif
        </div>
    @else
        <div class="bg-white rounded-xl border border-gray-100 p-4 sm:p-6">
            <p class="text-gray-500 text-center py-8">Seleccione un tipo de reporte y las fechas para ver los datos.</p>
        </div>
    @endif
    </div>
    @endif

    <!-- Modal de Detalles Unificado -->
    @if($selectedDetailId && $selectedDetailType)
        @php
            $detail = $this->getDetailData();
        @endphp
        @if($detail)
            <div class="fixed inset-0 z-[100] overflow-y-auto" wire:key="detail-modal-{{ $selectedDetailType }}-{{ $selectedDetailId }}">
                <div class="fixed inset-0 bg-gray-500/75 backdrop-blur-sm transition-opacity" wire:click="closeDetails"></div>
                <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                    
                    <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                    <div class="inline-block align-bottom bg-white rounded-[2.5rem] text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-5xl sm:w-full">
                        <div class="bg-white px-6 pt-8 pb-4 sm:p-10">
                            <!-- Cabecera Forense -->
                            <div class="flex items-center justify-between mb-10">
                                <div class="flex items-center space-x-5">
                                    <div class="p-4 bg-violet-600 text-white rounded-[1.5rem] shadow-lg shadow-violet-200">
                                        <i class="fas fa-fingerprint text-2xl"></i>
                                    </div>
                                    <div>
                                        <h3 class="text-3xl font-black text-gray-900 tracking-tighter">{{ $detail['title'] }}</h3>
                                        <div class="flex items-center mt-1.5 space-x-3">
                                            <span class="text-[10px] bg-violet-100 text-violet-700 px-3 py-1 rounded-full font-black uppercase tracking-[0.2em]">{{ $detail['type'] }}</span>
                                            <span class="text-gray-300">/</span>
                                            <span class="text-xs text-gray-400 font-bold tracking-widest">ID: #{{ $selectedDetailId }}</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="flex items-center space-x-4">
                                    <button wire:click="downloadSinglePdf" 
                                            class="inline-flex items-center px-6 py-3 bg-rose-600 text-white text-xs font-black rounded-2xl hover:bg-rose-700 transition-all shadow-lg shadow-rose-100 hover:-translate-y-1 active:translate-y-0 uppercase tracking-widest">
                                        <i class="fas fa-file-pdf mr-2 text-lg"></i> Exportar PDF
                                    </button>
                                    <button wire:click="closeDetails" class="text-gray-300 hover:text-gray-600 p-2 hover:bg-gray-100 rounded-2xl transition-all">
                                        <i class="fas fa-times text-2xl"></i>
                                    </button>
                                </div>
                            </div>

                            <div class="space-y-10">
                                <!-- Grid de Información Estructurada -->
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                                    @foreach($detail as $sectionKey => $sectionData)
                                        @if(is_array($sectionData) && !in_array($sectionKey, ['items', 'recent_reservations', 'history', 'sales_history', 'movements', 'inventory_impact', 'recent_activity']))
                                            <div class="bg-gray-50/50 rounded-[2rem] p-8 border border-gray-100 h-full hover:border-violet-100 transition-colors">
                                                <h4 class="text-[10px] font-black text-violet-500 uppercase tracking-[0.3em] mb-6 flex items-center">
                                                    <span class="w-2 h-2 bg-violet-500 rounded-full mr-2"></span> {{ str_replace('_', ' ', $sectionKey) }}
                                                </h4>
                                                <div class="space-y-5">
                                                    @foreach($sectionData as $label => $value)
                                                        <div class="flex justify-between items-start border-b border-gray-100/50 pb-3 last:border-0 last:pb-0">
                                                            <span class="text-[10px] font-black text-gray-400 uppercase tracking-wider">{{ $label }}</span>
                                                            <span class="text-sm font-black text-gray-900 text-right ml-6 break-words max-w-[60%]">
                                                                @if(str_contains(strtolower($label), 'total') || str_contains(strtolower($label), 'monto') || str_contains(strtolower($label), 'efectivo') || str_contains(strtolower($label), 'transferencia') || str_contains(strtolower($label), 'saldo') || str_contains(strtolower($label), 'precio') || str_contains(strtolower($label), 'abono') || str_contains(strtolower($label), 'costo') || str_contains(strtolower($label), 'ingresos') || str_contains(strtolower($label), 'recaudado') || str_contains(strtolower($label), 'gasto'))
                                                                    <span class="text-violet-700 font-black">${{ is_numeric($value) ? number_format($value, 2, ',', '.') : $value }}</span>
                                                                @else
                                                                    {{ $value }}
                                                                @endif
                                                            </span>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </div>
                                        @endif
                                    @endforeach
                                </div>

                                <!-- Tablas Forenses -->
                                <div class="space-y-8">
                                    <!-- Items de Venta -->
                                    @if(isset($detail['items']) && count($detail['items']) > 0)
                                        <div class="bg-white rounded-[2rem] border border-gray-100 overflow-hidden shadow-sm">
                                            <div class="bg-gray-50 px-8 py-5 border-b border-gray-100 flex justify-between items-center">
                                                <h4 class="text-xs font-black text-gray-800 uppercase tracking-[0.2em] flex items-center">
                                                    <i class="fas fa-shopping-basket mr-3 text-violet-500"></i> Desglose de Productos / Servicios
                                                </h4>
                                            </div>
                                            <table class="min-w-full divide-y divide-gray-100">
                                                <thead class="bg-gray-50/50">
                                                    <tr>
                                                        <th class="px-8 py-4 text-left text-[10px] font-black text-gray-400 uppercase tracking-widest">Descripción</th>
                                                        <th class="px-8 py-4 text-center text-[10px] font-black text-gray-400 uppercase tracking-widest">SKU</th>
                                                        <th class="px-8 py-4 text-center text-[10px] font-black text-gray-400 uppercase tracking-widest">Cant.</th>
                                                        <th class="px-8 py-4 text-right text-[10px] font-black text-gray-400 uppercase tracking-widest">P. Unit.</th>
                                                        <th class="px-8 py-4 text-right text-[10px] font-black text-gray-400 uppercase tracking-widest">Subtotal</th>
                                                    </tr>
                                                </thead>
                                                <tbody class="divide-y divide-gray-100">
                                                    @foreach($detail['items'] as $item)
                                                        <tr class="hover:bg-violet-50/30 transition-colors">
                                                            <td class="px-8 py-5 text-sm font-bold text-gray-900">{{ $item['name'] }}</td>
                                                            <td class="px-8 py-5 text-xs text-gray-500 text-center font-mono tracking-tighter">{{ $item['sku'] ?? 'N/A' }}</td>
                                                            <td class="px-8 py-5 text-sm text-gray-900 text-center font-black">{{ $item['quantity'] }}</td>
                                                            <td class="px-8 py-5 text-sm text-gray-600 text-right font-medium">${{ number_format($item['price'], 2, ',', '.') }}</td>
                                                            <td class="px-8 py-5 text-sm text-violet-700 text-right font-black tracking-tight">${{ number_format($item['total'], 2, ',', '.') }}</td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                                <tfoot class="bg-gray-50/80">
                                                    <tr>
                                                        <td colspan="4" class="px-8 py-6 text-sm font-black text-gray-900 text-right uppercase tracking-[0.2em]">Liquidación Final:</td>
                                                        <td class="px-8 py-6 text-2xl font-black text-violet-700 text-right tracking-tighter">${{ number_format($detail['total'] ?? 0, 2, ',', '.') }}</td>
                                                    </tr>
                                                </tfoot>
                                            </table>
                                        </div>
                                    @endif

                                    <!-- Movimientos de Inventario -->
                                    @if(isset($detail['inventory_impact']) && count($detail['inventory_impact']) > 0)
                                        <div class="bg-white rounded-[2rem] border border-gray-100 overflow-hidden shadow-sm">
                                            <div class="bg-amber-50 px-8 py-5 border-b border-amber-100">
                                                <h4 class="text-xs font-black text-amber-800 uppercase tracking-[0.2em] flex items-center">
                                                    <i class="fas fa-boxes mr-3"></i> Trazabilidad de Inventario Forense
                                                </h4>
                                            </div>
                                            <table class="min-w-full divide-y divide-gray-100">
                                                <thead class="bg-amber-50/30">
                                                    <tr>
                                                        <th class="px-8 py-4 text-left text-[10px] font-black text-amber-600 uppercase">Producto Impactado</th>
                                                        <th class="px-8 py-4 text-center text-[10px] font-black text-amber-600 uppercase">Variación</th>
                                                        <th class="px-8 py-4 text-right text-[10px] font-black text-amber-600 uppercase">Stock Previo</th>
                                                        <th class="px-8 py-4 text-right text-[10px] font-black text-amber-600 uppercase">Stock Post-Operación</th>
                                                    </tr>
                                                </thead>
                                                <tbody class="divide-y divide-gray-100">
                                                    @foreach($detail['inventory_impact'] as $mv)
                                                        <tr>
                                                            <td class="px-8 py-5 text-sm font-bold text-gray-900">{{ $mv['product'] }}</td>
                                                            <td class="px-8 py-5 text-sm text-rose-600 text-center font-black">-{{ $mv['change'] }}</td>
                                                            <td class="px-8 py-5 text-sm text-gray-500 text-right font-medium">{{ $mv['stock_before'] }}</td>
                                                            <td class="px-8 py-5 text-sm text-gray-900 text-right font-black">{{ $mv['stock_after'] }}</td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    @endif

                                    <!-- Historial de Ventas (para Reserva) -->
                                    @if(isset($detail['sales_history']) && count($detail['sales_history']) > 0)
                                        <div class="bg-white rounded-[2rem] border border-gray-100 overflow-hidden shadow-sm">
                                            <div class="bg-blue-50 px-8 py-5 border-b border-blue-100 flex justify-between items-center">
                                                <h4 class="text-xs font-black text-blue-800 uppercase tracking-[0.2em] flex items-center">
                                                    <i class="fas fa-receipt mr-3"></i> Registro de Consumos en Estancia
                                                </h4>
                                                <span class="text-[10px] font-black text-blue-400 bg-blue-100/50 px-3 py-1 rounded-full uppercase tracking-widest">{{ count($detail['sales_history']) }} Registros</span>
                                            </div>
                                            <table class="min-w-full divide-y divide-gray-100">
                                                <thead class="bg-blue-50/30">
                                                    <tr>
                                                        <th class="px-8 py-4 text-left text-[10px] font-black text-blue-600 uppercase tracking-widest">ID / Fecha de Registro</th>
                                                        <th class="px-8 py-4 text-left text-[10px] font-black text-blue-600 uppercase tracking-widest">Items Liquidados</th>
                                                        <th class="px-8 py-4 text-center text-[10px] font-black text-blue-600 uppercase tracking-widest">Estado</th>
                                                        <th class="px-8 py-4 text-right text-[10px] font-black text-blue-600 uppercase tracking-widest">Monto Liquidez</th>
                                                    </tr>
                                                </thead>
                                                <tbody class="divide-y divide-gray-100">
                                                    @foreach($detail['sales_history'] as $sh)
                                                        <tr class="hover:bg-blue-50/20 cursor-pointer transition-all" wire:click="showDetails('sale', {{ $sh['id'] }})">
                                                            <td class="px-8 py-5">
                                                                <p class="text-xs font-black text-gray-900">FOLIO #{{ $sh['id'] }}</p>
                                                                <p class="text-[10px] text-gray-400 font-bold uppercase tracking-tight">{{ $sh['date'] }}</p>
                                                            </td>
                                                            <td class="px-8 py-5 text-xs text-gray-600 italic font-medium leading-relaxed max-w-xs">{{ $sh['items'] }}</td>
                                                            <td class="px-8 py-5 text-center">
                                                                <span class="px-3 py-1 rounded-full text-[9px] font-black uppercase tracking-widest {{ $sh['status'] === 'pagado' ? 'bg-green-100 text-green-700' : 'bg-rose-100 text-rose-700' }}">
                                                                    {{ $sh['status'] }}
                                                                </span>
                                                            </td>
                                                            <td class="px-8 py-5 text-right font-black text-blue-700 text-sm tracking-tight">${{ number_format($sh['total'], 2, ',', '.') }}</td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    @endif

                                    <!-- Historial de Movimientos (para Producto) -->
                                    @if(isset($detail['movements']) && count($detail['movements']) > 0)
                                        <div class="bg-white rounded-[2rem] border border-gray-100 overflow-hidden shadow-sm">
                                            <div class="bg-gray-50 px-8 py-5 border-b border-gray-100 flex justify-between items-center">
                                                <h4 class="text-xs font-black text-gray-800 uppercase tracking-[0.2em] flex items-center">
                                                    <i class="fas fa-history mr-3 text-violet-500"></i> Bitácora de Stock (Audit 50)
                                                </h4>
                                            </div>
                                            <div class="max-h-[400px] overflow-y-auto">
                                                <table class="min-w-full divide-y divide-gray-100">
                                                    <thead class="bg-gray-50/50 sticky top-0 backdrop-blur-md">
                                                        <tr>
                                                            <th class="px-8 py-4 text-left text-[10px] font-black text-gray-400 uppercase tracking-widest">Cronología</th>
                                                            <th class="px-8 py-4 text-center text-[10px] font-black text-gray-400 uppercase tracking-widest">Tipo</th>
                                                            <th class="px-8 py-4 text-center text-[10px] font-black text-gray-400 uppercase tracking-widest">Delta</th>
                                                            <th class="px-8 py-4 text-left text-[10px] font-black text-gray-400 uppercase tracking-widest">Firma Digital / Causa</th>
                                                            <th class="px-8 py-4 text-right text-[10px] font-black text-gray-400 uppercase tracking-widest">Balance</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody class="divide-y divide-gray-100">
                                                        @foreach($detail['movements'] as $mv)
                                                            <tr class="text-xs hover:bg-gray-50/50 transition-colors">
                                                                <td class="px-8 py-4 text-gray-500 font-bold tabular-nums">{{ $mv['date'] }}</td>
                                                                <td class="px-8 py-4 text-center">
                                                                    <span class="px-3 py-1 rounded-full font-black text-[9px] uppercase tracking-widest {{ $mv['type'] === 'ENTRADA' ? 'bg-green-100 text-green-700' : 'bg-rose-100 text-rose-700' }}">
                                                                        {{ $mv['type'] }}
                                                                    </span>
                                                                </td>
                                                                <td class="px-8 py-4 text-center font-black text-sm {{ $mv['qty'] > 0 ? 'text-green-600' : 'text-rose-600' }}">
                                                                    {{ $mv['qty'] > 0 ? '+'.$mv['qty'] : $mv['qty'] }}
                                                                </td>
                                                                <td class="px-8 py-4">
                                                                    <p class="text-gray-700 font-bold uppercase tracking-tighter truncate max-w-[180px]">{{ $mv['reason'] }}</p>
                                                                    <p class="text-[9px] text-gray-400 font-black uppercase mt-0.5 tracking-[0.1em]">Op: {{ $mv['user'] }}</p>
                                                                </td>
                                                                <td class="px-8 py-4 text-right font-black text-gray-900 tabular-nums">{{ $mv['balance'] }}</td>
                                                            </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    @endif

                                    <!-- Historial Reciente (para Cliente/Habitación/Staff) -->
                                    @if(isset($detail['recent_reservations']) || isset($detail['recent_activity']))
                                        @php
                                            $activities = $detail['recent_reservations'] ?? $detail['recent_activity'] ?? [];
                                        @endphp
                                        @if(count($activities) > 0)
                                            <div>
                                                <h4 class="text-xs font-black text-gray-800 uppercase tracking-[0.3em] mb-6 px-2 flex items-center">
                                                    <i class="fas fa-layer-group mr-3 text-violet-500"></i> Historial Reciente de Operaciones
                                                </h4>
                                                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                                                    @foreach($activities as $act)
                                                        <button type="button" 
                                                                @if(isset($act['id'])) wire:click="showDetails('{{ isset($act['customer']) ? 'reservation' : 'sale' }}', {{ $act['id'] }})" @endif
                                                                class="p-6 border border-gray-100 rounded-[2rem] bg-gray-50/50 hover:bg-white hover:shadow-2xl hover:shadow-violet-100 hover:border-violet-200 transition-all text-left group">
                                                            <div class="flex justify-between items-start mb-4">
                                                                <span class="text-[10px] font-black text-violet-600 bg-violet-50 px-3 py-1 rounded-full tracking-widest uppercase">ID #{{ $act['id'] ?? 'N/A' }}</span>
                                                                <span class="text-[10px] text-gray-400 font-black uppercase tracking-tight">{{ $act['date'] ?? $act['dates'] ?? '' }}</span>
                                                            </div>
                                                            <p class="text-xl font-black text-gray-900 group-hover:text-violet-700 transition-colors tracking-tighter">${{ number_format($act['total'] ?? 0, 2, ',', '.') }}</p>
                                                            @if(isset($act['customer']) || isset($act['hab']))
                                                                <div class="flex items-center mt-3 text-gray-500">
                                                                    <i class="fas {{ isset($act['customer']) ? 'fa-user' : 'fa-door-open' }} text-[10px] mr-2"></i>
                                                                    <span class="text-[10px] font-black uppercase tracking-widest truncate">{{ $act['customer'] ?? ("Habitación " . ($act['hab'] ?? 'N/A')) }}</span>
                                                                </div>
                                                            @endif
                                                        </button>
                                                    @endforeach
                                                </div>
                                            </div>
                                        @endif
                                    @endif

                                    <!-- Notas Especiales -->
                                    @if(!empty($detail['notes']))
                                        <div class="bg-amber-50 rounded-[2rem] p-8 border border-amber-100 relative overflow-hidden group">
                                            <div class="absolute -top-6 -right-6 text-amber-100/50 rotate-12 transition-transform group-hover:scale-110">
                                                <i class="fas fa-sticky-note text-[8rem]"></i>
                                            </div>
                                            <h4 class="text-[10px] font-black text-amber-700 uppercase tracking-[0.4em] mb-4 flex items-center relative z-10">
                                                <i class="fas fa-comment-alt mr-2 text-amber-500"></i> OBSERVACIONES CRÍTICAS
                                            </h4>
                                            <p class="text-sm text-amber-900 font-bold leading-relaxed italic relative z-10">
                                                "{{ $detail['notes'] }}"
                                            </p>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="bg-gray-50 px-8 py-8 sm:px-10 sm:flex sm:flex-row-reverse gap-4 border-t border-gray-100">
                            <button wire:click="closeDetails" class="w-full inline-flex justify-center items-center px-10 py-4 bg-gray-900 text-white text-xs font-black rounded-2xl hover:bg-black shadow-xl shadow-gray-200 transition-all hover:-translate-y-1 active:translate-y-0 uppercase tracking-[0.2em]">
                                CERRAR EXPEDIENTE
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    @endif
    
    {{-- Data container is rendered by the polling subcomponent to keep data consistent --}}
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/js/tom-select.complete.min.js"></script>
@vite(['resources/js/reports-manager.js'])
@endpush

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/css/tom-select.bootstrap5.css">
@vite(['resources/css/reports-manager.css'])
@endpush
