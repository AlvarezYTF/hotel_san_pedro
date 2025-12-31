@extends('layouts.app')

@section('title', 'Editar Servicio')
@section('header', 'Editar Servicio')

@section('content')
<div class="max-w-4xl mx-auto space-y-4 sm:space-y-6">
    <div class="bg-white rounded-xl border border-gray-100 p-4 sm:p-6">
        <div class="flex items-center space-x-3 sm:space-x-4">
            <div class="p-2.5 sm:p-3 rounded-xl bg-blue-50 text-blue-600">
                <i class="fas fa-edit text-lg sm:text-xl"></i>
            </div>
            <div>
                <h1 class="text-xl sm:text-2xl font-bold text-gray-900">Editar Servicio</h1>
                <p class="text-xs sm:text-sm text-gray-500 mt-1">{{ $service->name }}</p>
            </div>
        </div>
    </div>

    <form method="POST" action="{{ route('services.update', $service) }}">
        @csrf
        @method('PUT')

        <div class="bg-white rounded-xl border border-gray-100 p-4 sm:p-6 space-y-5">
            <div>
                <label for="name" class="block text-xs sm:text-sm font-semibold text-gray-700 mb-2">
                    Nombre del Servicio <span class="text-red-500">*</span>
                </label>
                <input type="text" id="name" name="name" value="{{ old('name', $service->name) }}"
                       class="block w-full px-3 py-2.5 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 @error('name') border-red-300 @enderror"
                       required>
                @error('name')
                    <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="code_reference" class="block text-xs sm:text-sm font-semibold text-gray-700 mb-2">
                        Código de Referencia
                    </label>
                    <input type="text" id="code_reference" name="code_reference" value="{{ old('code_reference', $service->code_reference) }}"
                           class="block w-full px-3 py-2.5 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 @error('code_reference') border-red-300 @enderror"
                           maxlength="100">
                    @error('code_reference')
                        <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="unit_measure_id" class="block text-xs sm:text-sm font-semibold text-gray-700 mb-2">
                        Unidad de Medida <span class="text-red-500">*</span>
                    </label>
                    <select id="unit_measure_id" name="unit_measure_id"
                            class="block w-full px-3 py-2.5 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 @error('unit_measure_id') border-red-300 @enderror"
                            required>
                        <option value="">Seleccione...</option>
                        @foreach($unitMeasures as $unit)
                            <option value="{{ $unit->factus_id }}" {{ old('unit_measure_id', $service->unit_measure_id) == $unit->factus_id ? 'selected' : '' }}>
                                {{ $unit->name }} ({{ $unit->code }})
                            </option>
                        @endforeach
                    </select>
                    @error('unit_measure_id')
                        <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div>
                <label for="description" class="block text-xs sm:text-sm font-semibold text-gray-700 mb-2">
                    Descripción
                </label>
                <textarea id="description" name="description" rows="3"
                          class="block w-full px-3 py-2.5 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500">{{ old('description', $service->description) }}</textarea>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label for="standard_code_id" class="block text-xs sm:text-sm font-semibold text-gray-700 mb-2">
                        Código Estándar DIAN
                    </label>
                    <select id="standard_code_id" name="standard_code_id"
                            class="block w-full px-3 py-2.5 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500">
                        <option value="">Seleccione...</option>
                        @foreach($standardCodes as $code)
                            <option value="{{ $code->id }}" {{ old('standard_code_id', $service->standard_code_id) == $code->id ? 'selected' : '' }}>
                                {{ $code->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="tribute_id" class="block text-xs sm:text-sm font-semibold text-gray-700 mb-2">
                        Tributo DIAN
                    </label>
                    <select id="tribute_id" name="tribute_id"
                            class="block w-full px-3 py-2.5 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500">
                        <option value="">Seleccione...</option>
                        @foreach($tributes as $tribute)
                            <option value="{{ $tribute->id }}" {{ old('tribute_id', $service->tribute_id) == $tribute->id ? 'selected' : '' }}>
                                {{ $tribute->name }} ({{ $tribute->code }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="price" class="block text-xs sm:text-sm font-semibold text-gray-700 mb-2">
                        Precio <span class="text-red-500">*</span>
                    </label>
                    <input type="number" id="price" name="price" value="{{ old('price', $service->price) }}" step="0.01" min="0"
                           class="block w-full px-3 py-2.5 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 @error('price') border-red-300 @enderror"
                           required>
                    @error('price')
                        <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="tax_rate" class="block text-xs sm:text-sm font-semibold text-gray-700 mb-2">
                        Tasa de Impuesto (IVA) %
                    </label>
                    <input type="number" id="tax_rate" name="tax_rate" value="{{ old('tax_rate', $service->tax_rate) }}" step="0.01" min="0" max="100"
                           class="block w-full px-3 py-2.5 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 @error('tax_rate') border-red-300 @enderror">
                    @error('tax_rate')
                        <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex items-center">
                    <label class="flex items-center cursor-pointer">
                        <input type="checkbox" name="is_active" value="1" {{ old('is_active', $service->is_active) ? 'checked' : '' }}
                               class="w-4 h-4 text-emerald-600 border-gray-300 rounded focus:ring-emerald-500">
                        <span class="ml-2 text-sm text-gray-700">Servicio activo</span>
                    </label>
                </div>
            </div>
        </div>

        <div class="flex items-center justify-between pt-4 border-t border-gray-100">
            <a href="{{ route('services.show', $service) }}"
               class="px-4 py-2.5 rounded-xl border-2 border-gray-200 bg-white text-gray-700 text-sm font-semibold hover:bg-gray-50">
                <i class="fas fa-arrow-left mr-2"></i>Volver
            </a>
            <button type="submit"
                    class="px-4 py-2.5 rounded-xl border-2 border-emerald-600 bg-emerald-600 text-white text-sm font-semibold hover:bg-emerald-700">
                <i class="fas fa-save mr-2"></i>Actualizar Servicio
            </button>
        </div>
    </form>
</div>
@endsection

