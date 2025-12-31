@extends('layouts.app')

@section('title', 'Detalle del Servicio')
@section('header', 'Detalle del Servicio')

@section('content')
<div class="max-w-4xl mx-auto space-y-4 sm:space-y-6">
    <div class="bg-white rounded-xl border border-gray-100 p-4 sm:p-6">
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-3 sm:space-x-4">
                <div class="p-2.5 sm:p-3 rounded-xl bg-blue-50 text-blue-600">
                    <i class="fas fa-concierge-bell text-lg sm:text-xl"></i>
                </div>
                <div>
                    <h1 class="text-xl sm:text-2xl font-bold text-gray-900">{{ $service->name }}</h1>
                    <p class="text-xs sm:text-sm text-gray-500 mt-1">Información detallada del servicio</p>
                </div>
            </div>
            <div class="flex items-center space-x-2">
                @can('edit_services')
                <a href="{{ route('services.edit', $service) }}"
                   class="px-4 py-2 rounded-xl border-2 border-indigo-600 bg-indigo-600 text-white text-sm font-semibold hover:bg-indigo-700">
                    <i class="fas fa-edit mr-2"></i>Editar
                </a>
                @endcan
                <a href="{{ route('services.index') }}"
                   class="px-4 py-2 rounded-xl border-2 border-gray-200 bg-white text-gray-700 text-sm font-semibold hover:bg-gray-50">
                    <i class="fas fa-arrow-left mr-2"></i>Volver
                </a>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 sm:gap-6">
        <div class="bg-white rounded-xl border border-gray-100 p-4 sm:p-6">
            <h2 class="text-base font-semibold text-gray-900 mb-4">Información General</h2>
            <dl class="space-y-3">
                <div>
                    <dt class="text-xs font-semibold text-gray-500 uppercase">Nombre</dt>
                    <dd class="text-sm text-gray-900 mt-1">{{ $service->name }}</dd>
                </div>
                @if($service->code_reference)
                <div>
                    <dt class="text-xs font-semibold text-gray-500 uppercase">Código de Referencia</dt>
                    <dd class="text-sm text-gray-900 mt-1">{{ $service->code_reference }}</dd>
                </div>
                @endif
                @if($service->description)
                <div>
                    <dt class="text-xs font-semibold text-gray-500 uppercase">Descripción</dt>
                    <dd class="text-sm text-gray-900 mt-1">{{ $service->description }}</dd>
                </div>
                @endif
                <div>
                    <dt class="text-xs font-semibold text-gray-500 uppercase">Estado</dt>
                    <dd class="mt-1">
                        @if($service->is_active)
                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700">
                                <i class="fas fa-check-circle mr-1.5"></i>Activo
                            </span>
                        @else
                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-gray-100 text-gray-700">
                                <i class="fas fa-times-circle mr-1.5"></i>Inactivo
                            </span>
                        @endif
                    </dd>
                </div>
            </dl>
        </div>

        <div class="bg-white rounded-xl border border-gray-100 p-4 sm:p-6">
            <h2 class="text-base font-semibold text-gray-900 mb-4">Información Fiscal</h2>
            <dl class="space-y-3">
                <div>
                    <dt class="text-xs font-semibold text-gray-500 uppercase">Unidad de Medida</dt>
                    <dd class="text-sm text-gray-900 mt-1">{{ $service->unitMeasure->name ?? '-' }} ({{ $service->unitMeasure->code ?? '-' }})</dd>
                </div>
                @if($service->standardCode)
                <div>
                    <dt class="text-xs font-semibold text-gray-500 uppercase">Código Estándar DIAN</dt>
                    <dd class="text-sm text-gray-900 mt-1">{{ $service->standardCode->name }}</dd>
                </div>
                @endif
                @if($service->tribute)
                <div>
                    <dt class="text-xs font-semibold text-gray-500 uppercase">Tributo DIAN</dt>
                    <dd class="text-sm text-gray-900 mt-1">{{ $service->tribute->name }} ({{ $service->tribute->code }})</dd>
                </div>
                @endif
                <div>
                    <dt class="text-xs font-semibold text-gray-500 uppercase">Precio Base</dt>
                    <dd class="text-lg font-semibold text-gray-900 mt-1">${{ number_format($service->price, 2) }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-semibold text-gray-500 uppercase">Tasa de Impuesto (IVA)</dt>
                    <dd class="text-sm text-gray-900 mt-1">{{ number_format($service->tax_rate, 2) }}%</dd>
                </div>
            </dl>
        </div>
    </div>

    <div class="bg-white rounded-xl border border-gray-100 p-4 sm:p-6">
        <h2 class="text-base font-semibold text-gray-900 mb-4">Información de Registro</h2>
        <dl class="grid grid-cols-1 md:grid-cols-2 gap-3">
            <div>
                <dt class="text-xs font-semibold text-gray-500 uppercase">Creado</dt>
                <dd class="text-sm text-gray-900 mt-1">{{ $service->created_at->format('d/m/Y H:i') }}</dd>
            </div>
            <div>
                <dt class="text-xs font-semibold text-gray-500 uppercase">Última Actualización</dt>
                <dd class="text-sm text-gray-900 mt-1">{{ $service->updated_at->format('d/m/Y H:i') }}</dd>
            </div>
        </dl>
    </div>
</div>
@endsection

