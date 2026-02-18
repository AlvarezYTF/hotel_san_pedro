@php
    $isProductFormModalOpen = $createModalOpen || $editModalOpen;
    $isEditingProduct = $editModalOpen;
    $productModalCloseAction = $isEditingProduct ? 'closeEditModal' : 'closeCreateModal';
    $productModalSubmitAction = $isEditingProduct ? 'updateProduct' : 'storeProduct';
@endphp

<div class="space-y-4 sm:space-y-6">
    <x-product-list.header :productsCount="$products->total()" />

    <x-product-list.filters :categories="$categories" />

    <x-product-list.products-table :products="$products" />

    <x-confirm-delete-modal
        title="Eliminar Producto"
        message="Estas seguro de que deseas eliminar"
        confirmMethod="deleteProduct"
        itemNameAttribute="name" />

    @if($isProductFormModalOpen)
        <div class="fixed inset-0 z-[70] overflow-y-auto" role="dialog" aria-modal="true">
            <div class="flex min-h-screen items-center justify-center p-4">
                <button type="button"
                        wire:click="{{ $productModalCloseAction }}"
                        class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm"></button>

                <div class="relative w-full max-w-3xl rounded-2xl bg-white shadow-2xl">
                    <div class="flex items-center justify-between border-b border-gray-100 px-5 py-4 sm:px-6">
                        <div class="flex items-center gap-3">
                            <div class="flex h-10 w-10 items-center justify-center rounded-xl {{ $isEditingProduct ? 'bg-indigo-50 text-indigo-600' : 'bg-blue-50 text-blue-600' }}">
                                <i class="fas {{ $isEditingProduct ? 'fa-edit' : 'fa-plus' }}"></i>
                            </div>
                            <div>
                                <h3 class="text-base font-bold text-gray-900 sm:text-lg">
                                    {{ $isEditingProduct ? 'Editar Producto' : 'Nuevo Producto' }}
                                </h3>
                                <p class="text-xs text-gray-500">Gestion de inventario desde modal</p>
                            </div>
                        </div>
                        <button type="button" wire:click="{{ $productModalCloseAction }}" class="text-gray-400 hover:text-gray-700">
                            <i class="fas fa-times text-lg"></i>
                        </button>
                    </div>

                    <div class="max-h-[75vh] overflow-y-auto px-5 py-5 sm:px-6">
                        <div class="space-y-5">
                            <div>
                                <label class="mb-2 block text-xs font-semibold uppercase tracking-wider text-gray-700">Grupo de inventario</label>
                                <select wire:model.live="inventoryGroup"
                                        class="block w-full rounded-xl border border-gray-200 bg-gray-50 px-3 py-2.5 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    <option value="ventas">Productos de venta</option>
                                    <option value="aseo">Insumos de aseo</option>
                                </select>
                            </div>

                            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                <div class="sm:col-span-2">
                                    <label class="mb-2 block text-xs font-semibold uppercase tracking-wider text-gray-700">Nombre</label>
                                    <input type="text"
                                           wire:model.blur="form.name"
                                           class="block w-full rounded-xl border border-gray-200 px-3 py-2.5 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500"
                                           placeholder="Ej: Coca Cola 350ml">
                                    @error('name') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                                </div>

                                <div>
                                    <label class="mb-2 block text-xs font-semibold uppercase tracking-wider text-gray-700">SKU (opcional)</label>
                                    <input type="text"
                                           wire:model.blur="form.sku"
                                           class="block w-full rounded-xl border border-gray-200 px-3 py-2.5 text-sm uppercase focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500"
                                           placeholder="Ej: SKU-001">
                                    @error('sku') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                                </div>

                                @if($inventoryGroup === 'ventas')
                                    <div>
                                        <label class="mb-2 block text-xs font-semibold uppercase tracking-wider text-gray-700">Categoria</label>
                                        <select wire:model="form.category_id"
                                                class="block w-full rounded-xl border border-gray-200 bg-gray-50 px-3 py-2.5 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                            <option value="">Seleccionar categoria...</option>
                                            @foreach($saleCategories as $category)
                                                <option value="{{ $category->id }}">{{ $category->name }}</option>
                                            @endforeach
                                        </select>
                                        @error('category_id') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                                    </div>
                                @else
                                    <div>
                                        <label class="mb-2 block text-xs font-semibold uppercase tracking-wider text-gray-700">Categoria</label>
                                        <div class="rounded-xl border border-amber-200 bg-amber-50 px-3 py-2.5 text-xs text-amber-700">
                                            Se asignara automaticamente una categoria de aseo.
                                        </div>
                                    </div>
                                @endif

                                <div>
                                    <label class="mb-2 block text-xs font-semibold uppercase tracking-wider text-gray-700">Stock</label>
                                    <input type="number"
                                           wire:model.blur="form.quantity"
                                           min="0"
                                           step="1"
                                           class="block w-full rounded-xl border border-gray-200 px-3 py-2.5 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500"
                                           placeholder="0">
                                    @error('quantity') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                                </div>

                                <div>
                                    <label class="mb-2 block text-xs font-semibold uppercase tracking-wider text-gray-700">Umbral de stock bajo</label>
                                    <input type="number"
                                           wire:model.blur="form.low_stock_threshold"
                                           min="0"
                                           step="1"
                                           class="block w-full rounded-xl border border-gray-200 px-3 py-2.5 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500"
                                           placeholder="10">
                                    @error('low_stock_threshold') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                                </div>

                                <div>
                                    <label class="mb-2 block text-xs font-semibold uppercase tracking-wider text-gray-700">Precio</label>
                                    <input type="number"
                                           wire:model.blur="form.price"
                                           min="0"
                                           step="0.01"
                                           class="block w-full rounded-xl border border-gray-200 px-3 py-2.5 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500 {{ $inventoryGroup === 'aseo' ? 'bg-gray-100 text-gray-500' : '' }}"
                                           {{ $inventoryGroup === 'aseo' ? 'readonly' : '' }}>
                                    @if($inventoryGroup === 'aseo')
                                        <p class="mt-1 text-xs text-gray-500">Para insumos de aseo el precio se almacena en 0.</p>
                                    @endif
                                    @error('price') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                                </div>

                                <div>
                                    <label class="mb-2 block text-xs font-semibold uppercase tracking-wider text-gray-700">Estado</label>
                                    <select wire:model="form.status"
                                            class="block w-full rounded-xl border border-gray-200 bg-gray-50 px-3 py-2.5 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                        <option value="active">Activo</option>
                                        <option value="inactive">Inactivo</option>
                                        <option value="discontinued">Descontinuado</option>
                                    </select>
                                    @error('status') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="flex flex-col gap-3 border-t border-gray-100 bg-gray-50 px-5 py-4 sm:flex-row sm:items-center sm:justify-end sm:px-6">
                        <button type="button"
                                wire:click="{{ $productModalCloseAction }}"
                                class="inline-flex items-center justify-center rounded-xl border border-gray-300 bg-white px-4 py-2.5 text-sm font-semibold text-gray-700 hover:bg-gray-50">
                            Cancelar
                        </button>
                        <button type="button"
                                wire:click="{{ $productModalSubmitAction }}"
                                wire:loading.attr="disabled"
                                wire:target="{{ $productModalSubmitAction }}"
                                class="inline-flex items-center justify-center rounded-xl border border-blue-600 bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-blue-700 disabled:cursor-not-allowed disabled:opacity-60">
                            <span wire:loading.remove wire:target="{{ $productModalSubmitAction }}">
                                <i class="fas fa-save mr-2"></i>{{ $isEditingProduct ? 'Actualizar producto' : 'Guardar producto' }}
                            </span>
                            <span wire:loading wire:target="{{ $productModalSubmitAction }}">
                                <i class="fas fa-spinner fa-spin mr-2"></i>Procesando...
                            </span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    @if($showModalOpen && !empty($showProduct))
        <div class="fixed inset-0 z-[71] overflow-y-auto" role="dialog" aria-modal="true">
            <div class="flex min-h-screen items-center justify-center p-4">
                <button type="button" wire:click="closeShowModal" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm"></button>

                <div class="relative w-full max-w-5xl overflow-hidden rounded-2xl bg-white shadow-2xl">
                    <div class="flex items-center justify-between border-b border-gray-100 px-5 py-4 sm:px-6">
                        <div class="flex items-center gap-3">
                            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-indigo-50 text-indigo-600">
                                <i class="fas fa-box"></i>
                            </div>
                            <div>
                                <h3 class="text-base font-bold text-gray-900 sm:text-lg">Detalle del Producto</h3>
                                <p class="text-xs text-gray-500">ID #{{ $showProduct['id'] }}</p>
                            </div>
                        </div>
                        <button type="button" wire:click="closeShowModal" class="text-gray-400 hover:text-gray-700">
                            <i class="fas fa-times text-lg"></i>
                        </button>
                    </div>

                    <div class="max-h-[80vh] overflow-y-auto px-5 py-5 sm:px-6">
                        <div class="grid grid-cols-1 gap-5 lg:grid-cols-3">
                            <div class="space-y-4 lg:col-span-2">
                                <div class="rounded-xl border border-gray-100 p-4">
                                    <div class="flex flex-wrap items-center justify-between gap-3">
                                        <h4 class="text-lg font-bold text-gray-900">{{ $showProduct['name'] }}</h4>
                                        @php
                                            $statusClasses = match($showProduct['status']) {
                                                'active' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                                                'inactive' => 'bg-gray-100 text-gray-600 border-gray-200',
                                                default => 'bg-rose-50 text-rose-700 border-rose-200',
                                            };
                                            $statusLabel = match($showProduct['status']) {
                                                'active' => 'Activo',
                                                'inactive' => 'Inactivo',
                                                default => 'Descontinuado',
                                            };
                                        @endphp
                                        <span class="inline-flex items-center rounded-lg border px-2.5 py-1 text-[11px] font-bold uppercase tracking-wider {{ $statusClasses }}">
                                            {{ $statusLabel }}
                                        </span>
                                    </div>

                                    <div class="mt-4 grid grid-cols-1 gap-3 sm:grid-cols-2">
                                        <div>
                                            <p class="text-[11px] font-semibold uppercase tracking-wider text-gray-500">SKU</p>
                                            <p class="mt-1 text-sm font-semibold text-gray-900">{{ $showProduct['sku'] !== '' ? $showProduct['sku'] : 'Sin SKU' }}</p>
                                        </div>
                                        <div>
                                            <p class="text-[11px] font-semibold uppercase tracking-wider text-gray-500">Categoria</p>
                                            <p class="mt-1 text-sm font-semibold text-gray-900">{{ $showProduct['category_name'] }}</p>
                                        </div>
                                        <div>
                                            <p class="text-[11px] font-semibold uppercase tracking-wider text-gray-500">Creado</p>
                                            <p class="mt-1 text-sm font-semibold text-gray-900">{{ $showProduct['created_at'] }}</p>
                                        </div>
                                        <div>
                                            <p class="text-[11px] font-semibold uppercase tracking-wider text-gray-500">Actualizado</p>
                                            <p class="mt-1 text-sm font-semibold text-gray-900">{{ $showProduct['updated_at'] }}</p>
                                        </div>
                                    </div>
                                </div>

                                <div class="rounded-xl border border-gray-100 p-4">
                                    <h5 class="mb-3 text-sm font-bold text-gray-900">Ultimos movimientos</h5>
                                    @if(!empty($showMovements))
                                        <div class="space-y-2">
                                            @foreach($showMovements as $movement)
                                                <div class="flex items-start justify-between gap-3 rounded-lg border border-gray-100 bg-gray-50 px-3 py-2.5">
                                                    <div>
                                                        <div class="text-xs font-bold text-gray-900">
                                                            {{ $movement['translated_type'] }}
                                                            <span class="ml-2 {{ $movement['quantity'] > 0 ? 'text-emerald-600' : 'text-rose-600' }}">
                                                                {{ $movement['quantity'] > 0 ? '+' : '' }}{{ $movement['quantity'] }}
                                                            </span>
                                                        </div>
                                                        <div class="mt-0.5 text-[11px] text-gray-500">
                                                            {{ $movement['created_at'] }} | {{ $movement['user_name'] }}
                                                        </div>
                                                        @if($movement['reason'] !== '')
                                                            <div class="mt-0.5 text-[11px] italic text-gray-500">{{ $movement['reason'] }}</div>
                                                        @endif
                                                    </div>
                                                    <div class="text-right text-[11px] text-gray-500">
                                                        <div>Previo: <span class="font-semibold text-gray-700">{{ $movement['previous_stock'] }}</span></div>
                                                        <div>Actual: <span class="font-semibold text-gray-900">{{ $movement['current_stock'] }}</span></div>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    @else
                                        <p class="text-sm text-gray-500">No hay movimientos registrados para este producto.</p>
                                    @endif
                                </div>
                            </div>

                            <div class="space-y-4">
                                <div class="rounded-xl border border-gray-100 p-4">
                                    <p class="text-[11px] font-semibold uppercase tracking-wider text-gray-500">Stock actual</p>
                                    <p class="mt-1 text-3xl font-black {{ $showProduct['quantity'] <= $showProduct['low_stock_threshold'] ? 'text-rose-600' : 'text-gray-900' }}">
                                        {{ $showProduct['quantity'] }}
                                    </p>
                                    <p class="text-xs text-gray-500">Umbral: {{ $showProduct['low_stock_threshold'] }}</p>
                                </div>

                                <div class="rounded-xl border border-gray-100 p-4">
                                    <p class="text-[11px] font-semibold uppercase tracking-wider text-gray-500">Precio</p>
                                    <p class="mt-1 text-2xl font-bold text-emerald-600">
                                        @if(function_exists('formatCurrency'))
                                            {{ formatCurrency($showProduct['price']) }}
                                        @else
                                            ${{ number_format((float) $showProduct['price'], 0, ',', '.') }}
                                        @endif
                                    </p>
                                </div>

                                @can('edit_products')
                                    <div class="space-y-2">
                                        <button type="button"
                                                wire:click="openEditModal({{ $showProduct['id'] }})"
                                                class="inline-flex w-full items-center justify-center rounded-xl border border-indigo-600 bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-indigo-700">
                                            <i class="fas fa-edit mr-2"></i>Editar producto
                                        </button>
                                        <button type="button"
                                                wire:click="openStockModal({{ $showProduct['id'] }})"
                                                class="inline-flex w-full items-center justify-center rounded-xl border border-amber-300 bg-amber-50 px-4 py-2.5 text-sm font-semibold text-amber-700 hover:bg-amber-100">
                                            <i class="fas fa-sliders-h mr-2"></i>Ajustar stock
                                        </button>
                                    </div>
                                @endcan
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    @if($stockModalOpen && !empty($stockModalProduct))
        <div class="fixed inset-0 z-[72] overflow-y-auto" role="dialog" aria-modal="true">
            <div class="flex min-h-screen items-center justify-center p-4">
                <button type="button" wire:click="closeStockModal" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm"></button>

                <div class="relative w-full max-w-xl rounded-2xl bg-white shadow-2xl">
                    <div class="flex items-center justify-between border-b border-gray-100 px-5 py-4 sm:px-6">
                        <div>
                            <h3 class="text-base font-bold text-gray-900 sm:text-lg">Ajuste de Inventario</h3>
                            <p class="text-xs text-gray-500">{{ $stockModalProduct['name'] }} | Stock actual: {{ $stockModalProduct['quantity'] }}</p>
                        </div>
                        <button type="button" wire:click="closeStockModal" class="text-gray-400 hover:text-gray-700">
                            <i class="fas fa-times text-lg"></i>
                        </button>
                    </div>

                    <div class="space-y-4 px-5 py-5 sm:px-6">
                        <div>
                            <label class="mb-2 block text-xs font-semibold uppercase tracking-wider text-gray-700">Tipo de movimiento</label>
                            <select wire:model.live="stockForm.movement_type"
                                    class="block w-full rounded-xl border border-gray-200 bg-gray-50 px-3 py-2.5 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                <option value="adjustment">Ajuste</option>
                                <option value="input">Entrada</option>
                                <option value="output">Salida</option>
                            </select>
                            @error('stockForm.movement_type') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                        </div>

                        @php
                            $movementType = $stockForm['movement_type'] ?? 'adjustment';
                            $lockDirection = in_array($movementType, ['input', 'output'], true);
                        @endphp
                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                            <div>
                                <label class="mb-2 block text-xs font-semibold uppercase tracking-wider text-gray-700">Direccion</label>
                                <select wire:model="stockForm.direction"
                                        {{ $lockDirection ? 'disabled' : '' }}
                                        class="block w-full rounded-xl border border-gray-200 bg-gray-50 px-3 py-2.5 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 {{ $lockDirection ? 'cursor-not-allowed opacity-70' : '' }}">
                                    <option value="increase">Aumentar</option>
                                    <option value="decrease">Disminuir</option>
                                </select>
                                @error('stockForm.direction') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                            </div>

                            <div>
                                <label class="mb-2 block text-xs font-semibold uppercase tracking-wider text-gray-700">Cantidad</label>
                                <input type="number"
                                       wire:model.blur="stockForm.quantity"
                                       min="1"
                                       step="1"
                                       class="block w-full rounded-xl border border-gray-200 px-3 py-2.5 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                                       placeholder="1">
                                @error('stockForm.quantity') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                            </div>
                        </div>

                        <div>
                            <label class="mb-2 block text-xs font-semibold uppercase tracking-wider text-gray-700">Motivo (opcional)</label>
                            <textarea wire:model.blur="stockForm.reason"
                                      rows="3"
                                      class="block w-full rounded-xl border border-gray-200 px-3 py-2.5 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                                      placeholder="Describe el motivo del movimiento..."></textarea>
                            @error('stockForm.reason') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div class="flex flex-col gap-3 border-t border-gray-100 bg-gray-50 px-5 py-4 sm:flex-row sm:items-center sm:justify-end sm:px-6">
                        <button type="button"
                                wire:click="closeStockModal"
                                class="inline-flex items-center justify-center rounded-xl border border-gray-300 bg-white px-4 py-2.5 text-sm font-semibold text-gray-700 hover:bg-gray-50">
                            Cancelar
                        </button>
                        <button type="button"
                                wire:click="applyStockAdjustment"
                                wire:loading.attr="disabled"
                                wire:target="applyStockAdjustment"
                                class="inline-flex items-center justify-center rounded-xl border border-indigo-600 bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-indigo-700 disabled:cursor-not-allowed disabled:opacity-60">
                            <span wire:loading.remove wire:target="applyStockAdjustment">
                                <i class="fas fa-save mr-2"></i>Guardar movimiento
                            </span>
                            <span wire:loading wire:target="applyStockAdjustment">
                                <i class="fas fa-spinner fa-spin mr-2"></i>Guardando...
                            </span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
