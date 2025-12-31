@extends('layouts.app')

@section('title', 'Servicios')
@section('header', 'Gestión de Servicios')

@section('content')
<div class="space-y-4 sm:space-y-6">
    <!-- Header -->
    <div class="bg-white rounded-xl border border-gray-100 p-4 sm:p-6">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div class="flex items-center space-x-3 sm:space-x-4">
                <div class="p-2.5 sm:p-3 rounded-xl bg-blue-50 text-blue-600">
                    <i class="fas fa-concierge-bell text-lg sm:text-xl"></i>
                </div>
                <div>
                    <h1 class="text-xl sm:text-2xl font-bold text-gray-900">Gestión de Servicios</h1>
                    <div class="flex items-center space-x-2 mt-1">
                        <span class="text-xs sm:text-sm text-gray-500">
                            <span class="font-semibold text-gray-900">{{ $services->total() }}</span> servicios registrados
                        </span>
                    </div>
                </div>
            </div>
            
            @can('create_services')
            <a href="{{ route('services.create') }}"
               class="inline-flex items-center justify-center px-4 sm:px-5 py-2.5 rounded-xl border-2 border-emerald-600 bg-emerald-600 text-white text-sm font-semibold hover:bg-emerald-700 hover:border-emerald-700 transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-emerald-500 shadow-sm hover:shadow-md">
                <i class="fas fa-plus mr-2"></i>
                <span>Nuevo Servicio</span>
            </a>
            @endcan
        </div>
    </div>
    
    <!-- Filtros -->
    <div class="bg-white rounded-xl border border-gray-100 p-4 sm:p-6">
        <form method="GET" action="{{ route('services.index') }}" class="space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <label for="search" class="block text-xs font-semibold text-gray-700 mb-2">Buscar</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fas fa-search text-gray-400 text-sm"></i>
                        </div>
                        <input type="text" 
                               id="search" 
                               name="search" 
                               value="{{ request('search') }}" 
                               class="block w-full pl-10 pr-3 py-2.5 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500"
                               placeholder="Nombre, código...">
                    </div>
                </div>
                
                <div>
                    <label for="status" class="block text-xs font-semibold text-gray-700 mb-2">Estado</label>
                    <select id="status" name="status" class="block w-full px-3 py-2.5 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500">
                        <option value="">Todos</option>
                        <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Activo</option>
                        <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactivo</option>
                    </select>
                </div>
                
                <div>
                    <label for="standard_code_id" class="block text-xs font-semibold text-gray-700 mb-2">Código Estándar</label>
                    <select id="standard_code_id" name="standard_code_id" class="block w-full px-3 py-2.5 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500">
                        <option value="">Todos</option>
                        @foreach($standardCodes as $code)
                            <option value="{{ $code->id }}" {{ request('standard_code_id') == $code->id ? 'selected' : '' }}>{{ $code->name }}</option>
                        @endforeach
                    </select>
                </div>
                
                <div class="flex items-end">
                    <button type="submit" class="w-full px-4 py-2.5 rounded-xl border-2 border-gray-200 bg-white text-gray-700 text-sm font-semibold hover:bg-gray-50">
                        <i class="fas fa-filter mr-2"></i>Filtrar
                    </button>
                </div>
            </div>
        </form>
    </div>
    
    <!-- Tabla -->
    <div class="bg-white rounded-xl border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-100">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase">Servicio</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase">Código</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase">Unidad</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase">Precio</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase">IVA</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase">Estado</th>
                        <th class="px-6 py-4 text-right text-xs font-semibold text-gray-500 uppercase">Acciones</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-100">
                    @forelse($services as $service)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4">
                            <div class="text-sm font-semibold text-gray-900">{{ $service->name }}</div>
                            @if($service->description)
                                <div class="text-xs text-gray-500 mt-1">{{ Str::limit($service->description, 50) }}</div>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-700">{{ $service->code_reference ?? '-' }}</td>
                        <td class="px-6 py-4 text-sm text-gray-700">{{ $service->unitMeasure->name ?? '-' }}</td>
                        <td class="px-6 py-4 text-sm font-semibold text-gray-900">${{ number_format($service->price, 2) }}</td>
                        <td class="px-6 py-4 text-sm text-gray-700">{{ number_format($service->tax_rate, 2) }}%</td>
                        <td class="px-6 py-4">
                            @if($service->is_active)
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700">
                                    <i class="fas fa-check-circle mr-1.5"></i>Activo
                                </span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-gray-100 text-gray-700">
                                    <i class="fas fa-times-circle mr-1.5"></i>Inactivo
                                </span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-right text-sm font-medium">
                            <div class="flex items-center justify-end space-x-2">
                                <a href="{{ route('services.show', $service) }}" class="p-2 text-blue-600 hover:bg-blue-50 rounded-lg" title="Ver">
                                    <i class="fas fa-eye"></i>
                                </a>
                                @can('edit_services')
                                <a href="{{ route('services.edit', $service) }}" class="p-2 text-indigo-600 hover:bg-indigo-50 rounded-lg" title="Editar">
                                    <i class="fas fa-edit"></i>
                                </a>
                                @endcan
                                @can('delete_services')
                                <form action="{{ route('services.destroy', $service) }}" method="POST" class="inline" onsubmit="return confirm('¿Está seguro de eliminar este servicio?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="p-2 text-red-600 hover:bg-red-50 rounded-lg" title="Eliminar">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                                @endcan
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-6 py-16 text-center">
                            <div class="flex flex-col items-center">
                                <div class="p-4 rounded-full bg-gray-50 text-gray-400 mb-4">
                                    <i class="fas fa-concierge-bell text-3xl"></i>
                                </div>
                                <p class="text-lg font-semibold text-gray-900 mb-2">No hay servicios registrados</p>
                                <p class="text-sm text-gray-500 mb-4">Comienza agregando tu primer servicio</p>
                                @can('create_services')
                                <a href="{{ route('services.create') }}" class="inline-flex items-center px-4 py-2 rounded-xl border-2 border-emerald-600 bg-emerald-600 text-white text-sm font-semibold hover:bg-emerald-700">
                                    <i class="fas fa-plus mr-2"></i>Crear Primer Servicio
                                </a>
                                @endcan
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        @if($services->hasPages())
        <div class="bg-white px-6 py-4 border-t border-gray-100">
            {{ $services->appends(request()->query())->links() }}
        </div>
        @endif
    </div>
</div>
@endsection

