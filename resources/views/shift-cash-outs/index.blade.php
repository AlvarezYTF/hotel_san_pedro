@extends('layouts.app')

@section('title', 'Retiros de Caja (Turno)')
@section('header', 'Retiros de Caja (Turno)')

@section('content')
<div class="space-y-6">
    <div class="bg-indigo-50 border border-indigo-100 rounded-xl p-4 text-sm text-indigo-900">
        <span class="font-black">Nota:</span> este módulo es para <span class="font-bold">retiro/entrega/traslado</span> de efectivo desde la caja del turno (no es “gasto”).
        Para registrar <span class="font-bold">gastos</span> (almuerzo, compras, etc.), usa <span class="font-black">Gastos (Caja)</span>.
    </div>
    <div class="flex justify-end">
        @can('create_shift_cash_outs')
        <a href="{{ route('shift-cash-outs.create') }}" class="bg-indigo-600 text-white px-4 py-2 rounded-lg text-sm font-bold hover:bg-indigo-700 transition-colors shadow-sm">
            <i class="fas fa-plus mr-2"></i> Nuevo Retiro de Caja
        </a>
        @endcan
    </div>

    <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="p-6">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-100">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fecha/Hora</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Turno</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Registrado por</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Concepto</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Monto</th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-100">
                        @foreach($cashOuts as $cashOut)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ $cashOut->created_at->format('d/m/Y H:i') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 uppercase">
                                {{ $cashOut->shift_type->value }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                {{ $cashOut->user->name }}
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-700">
                                {{ $cashOut->concept }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-right font-black text-red-600">
                                ${{ number_format($cashOut->amount, 2) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-medium">
                                @php
                                    $canDelete = false;
                                    if (($isAdmin ?? false) === true) {
                                        $canDelete = true;
                                    } else {
                                        $window = (int) ($deleteWindowMinutes ?? 60);
                                        $canDelete = ($activeShiftId ?? null)
                                            && ($activeShiftStatus ?? '') === 'activo'
                                            && (int) $cashOut->user_id === (int) auth()->id()
                                            && (int) ($cashOut->shift_handover_id ?? 0) === (int) $activeShiftId
                                            && $cashOut->created_at
                                            && now()->diffInMinutes($cashOut->created_at) <= $window;
                                    }
                                @endphp

                                @if($canDelete)
                                    <form action="{{ route('shift-cash-outs.destroy', $cashOut->id) }}" method="POST" class="inline" onsubmit="return confirm('¿Está seguro de eliminar este registro?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-600 hover:text-red-900 bg-red-50 px-3 py-1 rounded-lg transition-colors">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                @else
                                    <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">No editable</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="mt-4">
                {{ $cashOuts->links() }}
            </div>
        </div>
    </div>
</div>
@endsection

