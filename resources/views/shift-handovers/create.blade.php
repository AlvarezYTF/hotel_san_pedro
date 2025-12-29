@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto px-4 py-6 space-y-6">
    <div class="bg-white rounded-xl border border-gray-100 p-6 shadow-sm">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-black text-gray-900 tracking-tight">Entregar Turno</h1>
                <p class="text-sm text-gray-500 mt-1">Genera el acta de entrega del turno activo.</p>
            </div>
            <a href="{{ route('shift-handovers.index') }}" class="text-sm font-semibold text-violet-700 hover:text-violet-900">
                <i class="fas fa-arrow-left mr-2"></i>Volver
            </a>
        </div>
    </div>

    <div class="bg-white rounded-xl border border-gray-100 p-6 shadow-sm">
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
            <div class="bg-gray-50 rounded-xl p-4 border border-gray-100">
                <div class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Turno</div>
                <div class="text-sm font-black text-gray-900 mt-1">{{ ucfirst($activeShift->shift_type->value) }}</div>
            </div>
            <div class="bg-gray-50 rounded-xl p-4 border border-gray-100">
                <div class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Base Inicial</div>
                <div class="text-sm font-black text-gray-900 mt-1">${{ number_format($activeShift->base_inicial ?? 0, 2, ',', '.') }}</div>
            </div>
            <div class="bg-gray-50 rounded-xl p-4 border border-gray-100">
                <div class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Iniciado</div>
                <div class="text-sm font-black text-gray-900 mt-1">{{ optional($activeShift->started_at)->format('d/m/Y H:i') }}</div>
            </div>
        </div>

        <form method="POST" action="{{ route('shift-handovers.store') }}" class="space-y-5">
            @csrf

            <div>
                <label class="block text-xs font-black text-gray-700 uppercase tracking-widest mb-2">Base final (opcional)</label>
                <input name="base_final" value="{{ old('base_final') }}"
                       class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:ring-2 focus:ring-violet-500 focus:border-transparent"
                       placeholder="Ej: 150000">
                @error('base_final') <p class="text-xs text-rose-600 mt-2 font-semibold">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-xs font-black text-gray-700 uppercase tracking-widest mb-2">Asignar receptor (opcional)</label>
                <select name="recibido_por" class="w-full px-4 py-3 rounded-xl border border-gray-200 bg-white focus:ring-2 focus:ring-violet-500 focus:border-transparent">
                    <option value="">Sin asignar (cualquiera puede recibir)</option>
                    @foreach($receivers as $r)
                        <option value="{{ $r->id }}" @selected(old('recibido_por') == $r->id)>{{ $r->name }} ({{ $r->email }})</option>
                    @endforeach
                </select>
                @error('recibido_por') <p class="text-xs text-rose-600 mt-2 font-semibold">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-xs font-black text-gray-700 uppercase tracking-widest mb-2">Observaciones (opcional)</label>
                <textarea name="observaciones" rows="4"
                          class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:ring-2 focus:ring-violet-500 focus:border-transparent"
                          placeholder="Notas de entrega...">{{ old('observaciones') }}</textarea>
                @error('observaciones') <p class="text-xs text-rose-600 mt-2 font-semibold">{{ $message }}</p> @enderror
            </div>

            <div class="pt-2 flex flex-col sm:flex-row gap-3 sm:justify-end">
                <a href="{{ route('dashboard') }}"
                   class="inline-flex items-center justify-center px-6 py-3 rounded-xl border border-gray-200 bg-white text-gray-700 text-sm font-black hover:bg-gray-50 transition-all">
                    Cancelar
                </a>
                <button type="submit"
                        class="inline-flex items-center justify-center px-6 py-3 rounded-xl bg-violet-600 text-white text-sm font-black hover:bg-violet-700 transition-all shadow-sm">
                    <i class="fas fa-handshake mr-2"></i> Entregar Turno
                </button>
            </div>
        </form>
    </div>
</div>
@endsection


