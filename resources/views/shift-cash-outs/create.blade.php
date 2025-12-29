@extends('layouts.app')

@section('title', 'Nuevo Retiro de Caja (Turno)')
@section('header', 'Registrar Retiro de Caja (Turno)')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="mb-6">
        <a href="{{ route('shift-cash-outs.index') }}" class="text-sm font-bold text-gray-500 hover:text-gray-700">
            <i class="fas fa-arrow-left mr-2"></i> Volver al listado
        </a>
    </div>

    <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="p-6">
            <div class="mb-6 p-4 rounded-lg bg-indigo-50 border border-indigo-100 text-indigo-900 text-sm font-semibold">
                <i class="fas fa-info-circle mr-2"></i>
                Esto es un <span class="font-black">retiro/entrega/traslado</span> de efectivo desde la caja del turno (no es “gasto”).
                Para registrar <span class="font-black">gastos</span>, usa <span class="font-black">Gastos (Caja)</span>.
            </div>

            @if(!$activeShift)
            <div class="mb-6 p-4 rounded-lg bg-amber-50 border border-amber-100 text-amber-700 text-sm font-bold flex items-center gap-3">
                <i class="fas fa-exclamation-triangle text-lg"></i>
                <span>No tienes un turno activo. El retiro se registrará fuera de turno.</span>
            </div>
            @endif

            <form action="{{ route('shift-cash-outs.store') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-1">Monto ($)</label>
                    <input type="number" step="0.01" name="amount" class="w-full px-4 py-2 border-2 border-gray-100 rounded-xl focus:border-indigo-500 focus:ring-0 transition-all text-lg font-black" placeholder="0.00" required autofocus>
                </div>

                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-1">Concepto / Motivo</label>
                    <input type="text" name="concept" class="w-full px-4 py-2 border-2 border-gray-100 rounded-xl focus:border-indigo-500 focus:ring-0 transition-all" placeholder="Ej: Pago de proveedores, gastos menores..." required>
                </div>

                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-1">Observaciones Adicionales</label>
                    <textarea name="observations" rows="3" class="w-full px-4 py-2 border-2 border-gray-100 rounded-xl focus:border-indigo-500 focus:ring-0 transition-all" placeholder="Opcional..."></textarea>
                </div>

                <div class="pt-4">
                    <button type="submit" class="w-full bg-indigo-600 text-white py-3 rounded-xl text-sm font-black hover:bg-indigo-700 transition-all shadow-md">
                        <i class="fas fa-save mr-2"></i> REGISTRAR RETIRO
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

