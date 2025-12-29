@extends('layouts.app')

@section('title', 'Detalle de Turno #' . $handover->id)
@section('header', 'Detalle de Turno')

@section('content')
<div class="space-y-6">
    <div class="flex justify-between items-center">
        <a href="{{ route('shift-handovers.index') }}" class="text-sm font-bold text-gray-500 hover:text-gray-700">
            <i class="fas fa-arrow-left mr-2"></i> Volver al listado
        </a>
        <div class="flex gap-2">
            <a href="{{ route('shift-handovers.pdf', $handover->id) }}"
               class="bg-rose-600 text-white px-4 py-2 rounded-lg text-sm font-bold hover:bg-rose-700 transition-colors">
                <i class="fas fa-file-pdf mr-2"></i> Descargar PDF
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Información General -->
        <div class="lg:col-span-2 space-y-6">
            <div class="bg-white rounded-xl border border-gray-100 p-6 shadow-sm">
                <h3 class="font-bold text-gray-900 mb-4 uppercase text-xs tracking-wider border-b pb-2">Resumen Operativo</h3>
                <div class="grid grid-cols-2 md:grid-cols-3 gap-6">
                    <div>
                        <p class="text-xs text-gray-500 font-medium uppercase mb-1">Base Inicial</p>
                        <p class="text-xl font-bold text-gray-900">${{ number_format($handover->base_inicial, 2) }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 font-medium uppercase mb-1">Ventas Efectivo</p>
                        <p class="text-xl font-bold text-emerald-600">${{ number_format($handover->total_entradas_efectivo, 2) }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 font-medium uppercase mb-1">Ventas Transf.</p>
                        <p class="text-xl font-bold text-blue-600">${{ number_format($handover->total_entradas_transferencia, 2) }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 font-medium uppercase mb-1">Total Salidas</p>
                        <p class="text-xl font-bold text-red-600">${{ number_format($handover->total_salidas, 2) }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 font-medium uppercase mb-1">Base Esperada</p>
                        <p class="text-xl font-bold text-indigo-600">${{ number_format($handover->base_esperada, 2) }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 font-medium uppercase mb-1">Base Recibida</p>
                        <p class="text-xl font-bold text-{{ abs($handover->diferencia) > config('shifts.difference_tolerance') ? 'red' : 'emerald' }}-600">
                            ${{ number_format($handover->base_recibida, 2) }}
                        </p>
                    </div>
                </div>

                @if(abs($handover->diferencia) > 0)
                <div class="mt-6 p-4 rounded-lg bg-{{ abs($handover->diferencia) > config('shifts.difference_tolerance') ? 'red' : 'amber' }}-50 border border-{{ abs($handover->diferencia) > config('shifts.difference_tolerance') ? 'red' : 'amber' }}-100 flex items-center justify-between">
                    <div class="flex items-center gap-3 text-{{ abs($handover->diferencia) > config('shifts.difference_tolerance') ? 'red' : 'amber' }}-700 font-bold">
                        <i class="fas fa-exclamation-triangle"></i>
                        <span>Diferencia en caja:</span>
                    </div>
                    <span class="text-lg font-black text-{{ abs($handover->diferencia) > config('shifts.difference_tolerance') ? 'red' : 'amber' }}-700">
                        ${{ number_format($handover->diferencia, 2) }}
                    </span>
                </div>
                @endif
            </div>

            <div class="bg-white rounded-xl border border-gray-100 p-6 shadow-sm">
                <h3 class="font-bold text-gray-900 mb-4 uppercase text-xs tracking-wider border-b pb-2">Observaciones</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <p class="text-xs text-gray-500 font-medium uppercase mb-1">Entrega ({{ $handover->entregadoPor->name }})</p>
                        <p class="text-sm text-gray-700 italic">{{ $handover->observaciones_entrega ?: 'Sin observaciones' }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 font-medium uppercase mb-1">Recepción ({{ $handover->recibidoPor->name ?? 'N/A' }})</p>
                        <p class="text-sm text-gray-700 italic">{{ $handover->observaciones_recepcion ?: 'Sin observaciones' }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sidebar Detalles -->
        <div class="space-y-6">
            <div class="bg-white rounded-xl border border-gray-100 p-6 shadow-sm">
                <h3 class="font-bold text-gray-900 mb-4 uppercase text-xs tracking-wider border-b pb-2">Información del Turno</h3>
                <ul class="space-y-4">
                    <li class="flex justify-between items-center text-sm">
                        <span class="text-gray-500">Fecha:</span>
                        <span class="font-bold text-gray-900">{{ $handover->shift_date->format('d/m/Y') }}</span>
                    </li>
                    <li class="flex justify-between items-center text-sm">
                        <span class="text-gray-500">Tipo:</span>
                        <span class="font-bold text-gray-900 uppercase">{{ $handover->shift_type->value }}</span>
                    </li>
                    <li class="flex justify-between items-center text-sm">
                        <span class="text-gray-500">Estado:</span>
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-black uppercase bg-blue-100 text-blue-700">
                            {{ $handover->status->value }}
                        </span>
                    </li>
                    <li class="flex justify-between items-center text-sm border-t pt-4">
                        <span class="text-gray-500">Inicio:</span>
                        <span class="font-bold text-gray-900">{{ $handover->started_at->format('H:i') }}</span>
                    </li>
                    <li class="flex justify-between items-center text-sm">
                        <span class="text-gray-500">Fin:</span>
                        <span class="font-bold text-gray-900">{{ $handover->ended_at ? $handover->ended_at->format('H:i') : 'N/A' }}</span>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection

