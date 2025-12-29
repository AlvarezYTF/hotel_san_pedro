@extends('layouts.app')

@section('title', 'Recibir Caja de Turno')
@section('header', 'Recepción de Caja')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="p-6">
            @if(!$pendingReception)
            <div class="text-center py-8">
                <div class="mb-4 text-gray-300">
                    <i class="fas fa-check-circle text-6xl"></i>
                </div>
                <h3 class="text-xl font-bold text-gray-900 mb-2">No hay turnos pendientes</h3>
                <p class="text-gray-500 mb-6">No se encontraron turnos entregados pendientes de recibir por tu parte.</p>
                <a href="{{ route('dashboard') }}" class="bg-gray-800 text-white px-6 py-2 rounded-lg text-sm font-bold">
                    Volver al Dashboard
                </a>
            </div>
            @else
            <div class="mb-6 border-b pb-4">
                <h3 class="text-lg font-bold text-gray-900 mb-2">Turno entregado por {{ $pendingReception->entregadoPor->name }}</h3>
                <div class="grid grid-cols-2 gap-4 text-sm">
                    <div>
                        <span class="text-gray-500 uppercase text-[10px] font-black block">Tipo de Turno:</span>
                        <span class="font-bold text-gray-800 uppercase">{{ $pendingReception->shift_type->value }}</span>
                    </div>
                    <div>
                        <span class="text-gray-500 uppercase text-[10px] font-black block">Base Esperada:</span>
                        <span class="font-bold text-indigo-600">${{ number_format($pendingReception->base_esperada, 2) }}</span>
                    </div>
                </div>
            </div>

            <form action="{{ route('shift-handovers.store-reception') }}" method="POST" class="space-y-4">
                @csrf
                <input type="hidden" name="handover_id" value="{{ $pendingReception->id }}">
                
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-1">Monto Físico Recibido ($)</label>
                    <input type="text" name="base_recibida" oninput="formatNumberInput(this)" class="w-full px-4 py-2 border-2 border-gray-100 rounded-xl focus:border-blue-500 focus:ring-0 transition-all text-xl font-black" placeholder="0" required autofocus>
                    <p class="mt-1 text-xs text-gray-500">Cuenta el dinero físico que hay en caja actualmente.</p>
                </div>

                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-1">Observaciones de Recepción</label>
                    <textarea name="observaciones" rows="3" class="w-full px-4 py-2 border-2 border-gray-100 rounded-xl focus:border-blue-500 focus:ring-0 transition-all" placeholder="Escribe aquí cualquier novedad encontrada en la caja..."></textarea>
                </div>

                <div class="pt-4">
                    <button type="submit" class="w-full bg-blue-600 text-white py-3 rounded-xl text-sm font-black hover:bg-blue-700 transition-all shadow-md">
                        <i class="fas fa-check-double mr-2"></i> CONFIRMAR RECEPCIÓN E INICIAR MI TURNO
                    </button>
                </div>
            </form>
            @endif
        </div>
    </div>
</div>
@endsection

