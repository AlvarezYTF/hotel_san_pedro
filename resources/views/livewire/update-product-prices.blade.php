<div>
    <!-- Botón para abrir el modal -->
    <button type="button" 
            wire:click="openModal"
            class="inline-flex items-center justify-center px-4 py-2 rounded-xl border border-gray-300 text-sm font-semibold text-gray-700 bg-white hover:bg-gray-50 transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500">
        <i class="fas fa-exchange-alt mr-2"></i>
        Actualizar Precios (USD → COP)
    </button>

    <!-- Modal -->
    @if($showModal)
    <div class="fixed inset-0 z-50 overflow-y-auto" x-data="{ show: @entangle('showModal') }" x-show="show" x-cloak>
        <div class="flex items-center justify-center min-h-screen p-4">
            <!-- Overlay -->
            <div class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm transition-opacity" 
                 wire:click="closeModal"></div>
            
            <!-- Modal Content -->
            <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-4xl max-h-[90vh] overflow-hidden transform transition-all"
                 @click.stop>
                <!-- Header -->
                <div class="px-6 py-4 border-b border-gray-200 bg-gradient-to-r from-green-500 to-emerald-600">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-3">
                            <div class="w-10 h-10 rounded-xl bg-white/20 backdrop-blur-md flex items-center justify-center">
                                <i class="fas fa-exchange-alt text-white text-lg"></i>
                            </div>
                            <div>
                                <h3 class="text-xl font-bold text-white">Actualizar Precios de Productos</h3>
                                <p class="text-sm text-green-100">Conversión de USD a Pesos Colombianos (COP)</p>
                            </div>
                        </div>
                        <button wire:click="closeModal" class="text-white hover:text-gray-200 transition-colors">
                            <i class="fas fa-times text-xl"></i>
                        </button>
                    </div>
                </div>

                <!-- Body -->
                <div class="p-6 overflow-y-auto max-h-[calc(90vh-200px)]">
                    @if(!$previewMode)
                        <!-- Formulario de tasa de cambio -->
                        <div class="space-y-6">
                            <div class="bg-blue-50 border border-blue-200 rounded-xl p-4">
                                <div class="flex items-start space-x-3">
                                    <i class="fas fa-info-circle text-blue-600 mt-1"></i>
                                    <div class="text-sm text-blue-800">
                                        <p class="font-semibold mb-1">Instrucciones:</p>
                                        <ul class="list-disc list-inside space-y-1 text-xs">
                                            <li>Ingrese la tasa de cambio actual (1 USD = X COP)</li>
                                            <li>Se actualizarán todos los productos activos</li>
                                            <li>Puede previsualizar los cambios antes de aplicar</li>
                                            <li>Total de productos a actualizar: <strong>{{ $totalProducts }}</strong></li>
                                        </ul>
                                    </div>
                                </div>
                            </div>

                            <div>
                                <label for="exchangeRate" class="block text-sm font-semibold text-gray-700 mb-2">
                                    Tasa de Cambio (1 USD = X COP) <span class="text-red-500">*</span>
                                </label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                        <span class="text-gray-500 text-sm font-semibold">$</span>
                                    </div>
                                    <input type="number" 
                                           id="exchangeRate"
                                           wire:model="exchangeRate"
                                           step="0.01"
                                           min="0.01"
                                           class="block w-full pl-10 pr-4 py-3 border border-gray-300 rounded-xl text-lg text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent transition-all @error('exchangeRate') border-red-300 focus:ring-red-500 @enderror"
                                           placeholder="Ej: 4200.00">
                                </div>
                                @error('exchangeRate')
                                    <p class="mt-1.5 text-xs text-red-600 flex items-center">
                                        <i class="fas fa-exclamation-circle mr-1.5"></i>
                                        {{ $message }}
                                    </p>
                                @enderror
                            </div>
                        </div>
                    @else
                        <!-- Vista previa de cambios -->
                        <div class="space-y-4">
                            <div class="bg-amber-50 border border-amber-200 rounded-xl p-4">
                                <div class="flex items-start space-x-3">
                                    <i class="fas fa-exclamation-triangle text-amber-600 mt-1"></i>
                                    <div class="text-sm text-amber-800">
                                        <p class="font-semibold mb-1">Vista Previa de Cambios</p>
                                        <p class="text-xs">Se actualizarán <strong>{{ count($affectedProducts) }}</strong> productos con la tasa de cambio: <strong>{{ formatCurrency($exchangeRate, true) }}</strong></p>
                                    </div>
                                </div>
                            </div>

                            <div class="border border-gray-200 rounded-xl overflow-hidden">
                                <div class="bg-gray-50 px-4 py-3 border-b border-gray-200">
                                    <h4 class="text-sm font-semibold text-gray-900">Productos a Actualizar</h4>
                                </div>
                                <div class="max-h-96 overflow-y-auto">
                                    <table class="min-w-full divide-y divide-gray-200">
                                        <thead class="bg-gray-50 sticky top-0">
                                            <tr>
                                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Producto</th>
                                                <th class="px-4 py-3 text-right text-xs font-semibold text-gray-600 uppercase tracking-wider">Precio Actual (USD)</th>
                                                <th class="px-4 py-3 text-right text-xs font-semibold text-gray-600 uppercase tracking-wider">Nuevo Precio (COP)</th>
                                            </tr>
                                        </thead>
                                        <tbody class="bg-white divide-y divide-gray-200">
                                            @foreach($affectedProducts as $product)
                                            <tr class="hover:bg-gray-50">
                                                <td class="px-4 py-3 text-sm text-gray-900">{{ $product['name'] }}</td>
                                                <td class="px-4 py-3 text-sm text-gray-600 text-right">{{ formatCurrency($product['old_price']) }}</td>
                                                <td class="px-4 py-3 text-sm font-semibold text-green-600 text-right">{{ formatCurrency($product['new_price']) }}</td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>

                <!-- Footer -->
                <div class="px-6 py-4 border-t border-gray-200 bg-gray-50 flex items-center justify-between">
                    <button type="button"
                            wire:click="closeModal"
                            class="px-4 py-2 rounded-xl border border-gray-300 text-sm font-semibold text-gray-700 bg-white hover:bg-gray-50 transition-colors">
                        Cancelar
                    </button>
                    
                    <div class="flex items-center space-x-3">
                        @if($previewMode)
                            <button type="button"
                                    wire:click="previewMode = false"
                                    class="px-4 py-2 rounded-xl border border-gray-300 text-sm font-semibold text-gray-700 bg-white hover:bg-gray-50 transition-colors">
                                <i class="fas fa-arrow-left mr-2"></i>
                                Volver
                            </button>
                            <button type="button"
                                    wire:click="updatePrices"
                                    wire:loading.attr="disabled"
                                    class="px-6 py-2 rounded-xl border-2 border-green-600 bg-green-600 text-white text-sm font-semibold hover:bg-green-700 hover:border-green-700 transition-all shadow-sm hover:shadow-md disabled:opacity-50 disabled:cursor-not-allowed">
                                <i class="fas fa-check mr-2" wire:loading.remove wire:target="updatePrices"></i>
                                <i class="fas fa-spinner fa-spin mr-2" wire:loading wire:target="updatePrices"></i>
                                <span wire:loading.remove wire:target="updatePrices">Confirmar Actualización</span>
                                <span wire:loading wire:target="updatePrices">Actualizando...</span>
                            </button>
                        @else
                            <button type="button"
                                    wire:click="preview"
                                    wire:loading.attr="disabled"
                                    class="px-6 py-2 rounded-xl border-2 border-blue-600 bg-blue-600 text-white text-sm font-semibold hover:bg-blue-700 hover:border-blue-700 transition-all shadow-sm hover:shadow-md disabled:opacity-50 disabled:cursor-not-allowed">
                                <i class="fas fa-eye mr-2" wire:loading.remove wire:target="preview"></i>
                                <i class="fas fa-spinner fa-spin mr-2" wire:loading wire:target="preview"></i>
                                <span wire:loading.remove wire:target="preview">Previsualizar Cambios</span>
                                <span wire:loading wire:target="preview">Calculando...</span>
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>

