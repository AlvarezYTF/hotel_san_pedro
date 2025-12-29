@extends('layouts.app')

@section('title', 'Salidas de Productos (No Ventas)')
@section('header', 'Salidas de Productos (No Ventas)')

@section('content')
<div class="space-y-6">
    <div class="bg-blue-50 border border-blue-100 rounded-xl p-4 text-sm text-blue-900">
        <span class="font-black">Nota:</span> este módulo es para registrar la salida de productos que <span class="font-bold">no son ventas</span> ni consumos asociados a habitaciones (ej. mermas, consumo interno, donaciones).
        El stock se descontará automáticamente.
    </div>
    
    <div class="flex justify-between items-center">
        <div>
            @if($activeShift)
                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                    <span class="w-2 h-2 mr-2 bg-green-400 rounded-full animate-pulse"></span>
                    Turno Activo: {{ $activeShift->shift_type->value }} ({{ $activeShift->shift_date->format('d/m/Y') }})
                </span>
            @else
                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                    Sin Turno Activo
                </span>
            @endif
        </div>
        @can('create_shift_cash_outs')
        <a href="{{ route('shift-product-outs.create') }}" class="bg-blue-600 text-white px-4 py-2 rounded-lg text-sm font-bold hover:bg-blue-700 transition-colors shadow-sm">
            <i class="fas fa-plus mr-2"></i> Registrar Salida de Producto
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
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Producto</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Motivo</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Cant.</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Usuario</th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-100">
                        @forelse($productOuts as $out)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ $out->created_at->format('d/m/Y H:i') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700 font-bold">
                                {{ $out->product->name }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                <span class="px-2 py-1 rounded text-xs font-medium bg-gray-100">
                                    {{ $out->reason->label() }}
                                </span>
                                @if($out->observations)
                                    <p class="text-xs text-gray-400 mt-1 italic">{{ Str::limit($out->observations, 30) }}</p>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 font-black">
                                {{ number_format($out->quantity, 0) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $out->user->name }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-medium">
                                @php
                                    $canDelete = false;
                                    if (auth()->user()->hasRole('Administrador')) {
                                        $canDelete = true;
                                    } elseif ($activeShift && $out->shift_handover_id === $activeShift->id && $out->user_id === auth()->id()) {
                                        $canDelete = true;
                                    }
                                @endphp

                                @if($canDelete)
                                    <form action="{{ route('shift-product-outs.destroy', $out->id) }}" method="POST" class="inline" onsubmit="return confirm('¿Está seguro de eliminar este registro? El stock será reintegrado.')">
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
                        @empty
                        <tr>
                            <td colspan="6" class="px-6 py-10 text-center text-gray-500 italic">
                                No se han registrado salidas de productos.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-4">
                {{ $productOuts->links() }}
            </div>
        </div>
    </div>
</div>
@endsection

