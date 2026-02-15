<!-- SECCION 1: CLIENTE -->
<div>
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm">
        <div class="p-5 border-b border-gray-50 flex items-center justify-between bg-gray-50/50">
            <div class="flex items-center space-x-2">
                <i class="fas fa-user-circle text-blue-500"></i>
                <h2 class="font-bold text-gray-800">Informacion del Cliente</h2>
            </div>
            <button type="button"
                @unless($isCustomerLocked ?? false) wire:click="$dispatch('open-create-customer-modal')" @endunless
                @disabled($isCustomerLocked ?? false)
                class="text-xs font-bold text-blue-600 hover:text-blue-800 flex items-center disabled:opacity-50 disabled:cursor-not-allowed">
                <i class="fas fa-plus-circle mr-1"></i> NUEVO CLIENTE
            </button>
        </div>

        <div class="p-6">
            @if (!$datesCompleted && ($customerId || !empty($customerSearchTerm)))
                <div class="mb-4 p-3 bg-amber-50 border border-amber-200 rounded-xl">
                    <p class="text-xs text-amber-800 flex items-center">
                        <i class="fas fa-info-circle mr-2"></i>
                        <span>Por favor, completa las fechas de Check-In y Check-Out para continuar con el resto del formulario.</span>
                    </p>
                </div>
            @endif

            <div class="grid grid-cols-1 gap-4">
                <div class="relative" id="customer-selector-container" x-data @click.outside="$wire.closeCustomerDropdown()">
                    <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Seleccionar Huesped</label>

                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                            <i class="fas fa-search text-sm"></i>
                        </div>
                        <input type="text" wire:model.live.debounce.300ms="customerSearchTerm"
                            @unless($isCustomerLocked ?? false) wire:click="openCustomerDropdown" wire:focus="openCustomerDropdown" @endunless
                            wire:keydown.escape="closeCustomerDropdown"
                            @disabled($isCustomerLocked ?? false)
                            class="block w-full pl-10 pr-4 py-2.5 border border-gray-300 rounded-xl text-sm focus:ring-emerald-500 focus:border-emerald-500 disabled:bg-gray-100 disabled:text-gray-500 disabled:cursor-not-allowed"
                            placeholder="{{ ($isCustomerLocked ?? false) ? 'Cliente bloqueado durante la edicion' : 'Buscar por nombre, identificacion o telefono...' }}">

                        @if ($customerId && !($isCustomerLocked ?? false))
                            <button type="button" wire:click="clearCustomerSelection"
                                class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600">
                                <i class="fas fa-times"></i>
                            </button>
                        @endif
                    </div>

                    <input type="hidden" name="customer_id" value="{{ $customerId }}">
                    @error('reservation.customerId')
                        <p class="mt-2 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                    @error('customerId')
                        <p class="mt-2 text-xs text-red-600">{{ $message }}</p>
                    @enderror

                    @if (($isCustomerLocked ?? false))
                        <div class="mt-3 p-3 bg-slate-50 border border-slate-200 rounded-xl">
                            <p class="text-xs text-slate-700 flex items-center">
                                <i class="fas fa-lock mr-2"></i>
                                <span>En edicion, el huesped principal no se puede cambiar.</span>
                            </p>
                        </div>
                    @endif

                    @if ($showCustomerDropdown && !($isCustomerLocked ?? false))
                        <div class="absolute z-50 w-full mt-1 bg-white border border-gray-300 rounded-xl shadow-lg max-h-96 overflow-y-auto">
                            @if (count($filteredCustomers) > 0)
                                @foreach ($filteredCustomers as $customer)
                                    @php
                                        $customerIdValue = $customer['id'] ?? null;
                                        $customerName = $customer['name'] ?? '';
                                        $identification = $customer['taxProfile']['identification'] ?? 'S/N';
                                        $phone = $customer['phone'] ?? 'S/N';
                                        $isSelected = (string) $customerIdValue === (string) $customerId;
                                    @endphp
                                    @if ($customerIdValue)
                                        <button type="button" wire:click="selectCustomer({{ $customerIdValue }})"
                                            class="w-full text-left px-4 py-3 hover:bg-emerald-50 transition-colors {{ $isSelected ? 'bg-emerald-100' : '' }} border-b border-gray-100 last:border-b-0">
                                            <div class="flex items-center justify-between">
                                                <div class="flex-1">
                                                    <div class="font-medium text-gray-900 text-sm">
                                                        {{ $customerName }}
                                                    </div>
                                                    <div class="text-xs text-gray-500 mt-0.5">
                                                        <span class="mr-2"><i class="fas fa-id-card mr-1"></i>{{ $identification }}</span>
                                                        @if ($phone !== 'S/N')
                                                            <span><i class="fas fa-phone mr-1"></i>{{ $phone }}</span>
                                                        @endif
                                                    </div>
                                                </div>
                                                @if ($isSelected)
                                                    <i class="fas fa-check-circle text-emerald-600"></i>
                                                @endif
                                            </div>
                                        </button>
                                    @endif
                                @endforeach

                                @if (empty($customerSearchTerm) && count($customers ?? []) > 5)
                                    <div class="px-4 py-2 text-xs text-gray-500 text-center border-t border-gray-100 bg-gray-50">
                                        <i class="fas fa-info-circle mr-1"></i>
                                        Mostrando los 5 clientes mas recientes. Escribe para buscar mas.
                                    </div>
                                @endif
                            @else
                                <div class="px-4 py-6 text-center text-sm text-gray-500">
                                    @if (empty($customerSearchTerm))
                                        <i class="fas fa-users text-2xl mb-2 opacity-50"></i>
                                        <p>No hay clientes disponibles</p>
                                    @else
                                        <i class="fas fa-search text-2xl mb-2 opacity-50"></i>
                                        <p>No se encontraron clientes</p>
                                        <p class="text-xs mt-1">Intenta con otro termino de busqueda</p>
                                    @endif
                                </div>
                            @endif
                        </div>
                    @endif

                    @if ($customerId && !$showCustomerDropdown)
                        @php
                            $selectedCustomer = collect($customers)->first(function ($customer) use ($customerId) {
                                return (string) ($customer['id'] ?? '') === (string) $customerId;
                            });
                        @endphp
                        @if ($selectedCustomer)
                            <div class="mt-2 p-3 bg-emerald-50 rounded-xl border border-emerald-200 flex items-center justify-between">
                                <div class="flex items-center space-x-3">
                                    <i class="fas fa-check-circle text-emerald-600"></i>
                                    <div>
                                        <div class="font-medium text-gray-900 text-sm">
                                            {{ $selectedCustomer['name'] ?? '' }}
                                        </div>
                                        <div class="text-xs text-gray-600">
                                            {{ $selectedCustomer['taxProfile']['identification'] ?? 'S/N' }}
                                        </div>
                                    </div>
                                </div>
                                @if (!($isCustomerLocked ?? false))
                                    <button type="button" wire:click="clearCustomerSelection" class="text-gray-400 hover:text-gray-600">
                                        <i class="fas fa-times"></i>
                                    </button>
                                @endif
                            </div>
                        @endif
                    @endif
                </div>

                @if ($selectedCustomerInfo && !$showCustomerDropdown)
                    <div class="mt-2 p-3 bg-blue-50 rounded-xl flex items-center justify-between border border-blue-100 transition-all animate-fadeIn">
                        <div class="flex items-center space-x-4 text-sm text-blue-800">
                            <div class="flex items-center">
                                <i class="fas fa-id-card mr-2 opacity-60"></i>
                                <span>{{ $selectedCustomerInfo['id'] ?? 'S/N' }}</span>
                            </div>
                            <div class="flex items-center border-l border-blue-200 pl-4">
                                <i class="fas fa-phone mr-2 opacity-60"></i>
                                <span>{{ $selectedCustomerInfo['phone'] ?? 'S/N' }}</span>
                            </div>
                        </div>
                        <span class="text-[10px] font-bold bg-blue-200 text-blue-800 px-2 py-0.5 rounded-full uppercase">Verificado</span>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
