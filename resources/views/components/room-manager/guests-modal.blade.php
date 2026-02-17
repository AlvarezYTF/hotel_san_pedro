@props(['guestsData'])

<div x-data="{ 
        show: false, 
        guestsData: null,
        init() {
            window.addEventListener('open-guests-modal', (e) => {
                this.guestsData = e.detail;
                this.show = true;
            });
        }
     }" 
     x-show="show" 
     x-cloak
     class="fixed inset-0 z-[100] overflow-y-auto" 
     aria-labelledby="modal-title" role="dialog" aria-modal="true">
    
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <!-- Overlay -->
        <div x-show="show" 
             x-transition:enter="ease-out duration-300" 
             x-transition:enter-start="opacity-0" 
             x-transition:enter-end="opacity-100" 
             x-transition:leave="ease-in duration-200" 
             x-transition:leave-start="opacity-100" 
             x-transition:leave-end="opacity-0" 
             class="fixed inset-0 bg-gray-500/75 backdrop-blur-sm transition-opacity" 
             @click="show = false"></div>

        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

        <!-- Modal Content -->
        <div x-show="show" 
             x-transition:enter="ease-out duration-300" 
             x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" 
             x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" 
             x-transition:leave="ease-in duration-200" 
             x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" 
             x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" 
             class="inline-block align-bottom bg-white rounded-3xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
            
            <template x-if="guestsData">
                <div>
                    <!-- Header -->
                    <div class="bg-white px-5 pt-5 pb-3 border-b border-gray-100">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center space-x-2.5">
                                <div class="w-10 h-10 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center">
                                    <i class="fas fa-users text-lg"></i>
                                </div>
                                <div>
                                    <h3 class="text-lg font-black text-gray-900" id="modal-title">
                                        Huéspedes - Hab. #<span x-text="guestsData.room_number"></span>
                                    </h3>
                                    <p class="text-xs text-gray-500 mt-0.5">
                                        Total: <span x-text="guestsData.guests.length" class="font-semibold"></span> 
                                        <span x-text="guestsData.guests.length === 1 ? 'huésped' : 'huéspedes'"></span>
                                    </p>
                                </div>
                            </div>
                            <button type="button" 
                                    @click="show = false"
                                    class="text-gray-400 hover:text-gray-900">
                                <i class="fas fa-times text-lg"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Content -->
                    <div class="bg-white px-5 pb-3 max-h-[calc(100vh-250px)] overflow-y-auto">
                        <template x-if="guestsData.guests && guestsData.guests.length > 0">
                            <div class="space-y-3">
                                <!-- Huésped Principal -->
                                <template x-if="guestsData.main_guest">
                                    <div class="bg-gradient-to-r from-blue-50 to-indigo-50 rounded-lg p-3 border border-blue-200">
                                        <div class="flex items-start justify-between">
                                            <div class="flex-1">
                                                <div class="flex items-center space-x-2 mb-2">
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[9px] font-black uppercase tracking-wider bg-blue-600 text-white">
                                                        Principal
                                                    </span>
                                                </div>
                                                <h4 class="text-sm font-black text-gray-900 mb-2 truncate" :title="guestsData.main_guest.name || ''" x-text="guestsData.main_guest.name"></h4>
                                                <div class="grid grid-cols-2 gap-2 mt-2">
                                                    <div>
                                                        <p class="text-[9px] font-bold text-gray-400 uppercase mb-0.5">Identificación</p>
                                                        <p class="text-xs font-bold text-gray-900" x-text="guestsData.main_guest.identification || 'N/A'"></p>
                                                    </div>
                                                    <div>
                                                        <p class="text-[9px] font-bold text-gray-400 uppercase mb-0.5">Teléfono</p>
                                                        <p class="text-xs font-bold text-gray-900" x-text="guestsData.main_guest.phone || 'N/A'"></p>
                                                    </div>
                                                    <div class="col-span-2">
                                                        <p class="text-[9px] font-bold text-gray-400 uppercase mb-0.5">Email</p>
                                                        <p class="text-xs font-bold text-gray-900" x-text="guestsData.main_guest.email || 'N/A'"></p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </template>

                                <!-- Huéspedes Adicionales -->
                                <template x-if="guestsData.guests.filter(g => !g.is_main).length > 0">
                                    <div>
                                        <h4 class="text-xs font-bold text-gray-900 uppercase tracking-wider mb-2">
                                            Adicionales (<span x-text="guestsData.guests.filter(g => !g.is_main).length"></span>)
                                        </h4>
                                        <div class="overflow-x-auto">
                                            <table class="min-w-[760px] w-full divide-y divide-gray-200">
                                                <thead class="bg-gray-50">
                                                    <tr>
                                                        <th scope="col" class="px-3 py-2 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Nombre</th>
                                                        <th scope="col" class="px-3 py-2 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Identificación</th>
                                                        <th scope="col" class="px-3 py-2 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Teléfono</th>
                                                        <th scope="col" class="px-3 py-2 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Email</th>
                                                    </tr>
                                                </thead>
                                                <tbody class="bg-white divide-y divide-gray-200">
                                                    <template x-for="(guest, index) in guestsData.guests.filter(g => !g.is_main)" :key="guest.id">
                                                        <tr class="hover:bg-gray-50">
                                                            <td class="px-3 py-2">
                                                                <div class="text-xs font-bold text-gray-900 max-w-[180px] truncate" :title="guest.name || ''" x-text="guest.name"></div>
                                                            </td>
                                                            <td class="px-3 py-2 whitespace-nowrap">
                                                                <div class="text-xs text-gray-700" x-text="guest.identification || 'N/A'"></div>
                                                            </td>
                                                            <td class="px-3 py-2 whitespace-nowrap">
                                                                <div class="text-xs text-gray-700" x-text="guest.phone || 'N/A'"></div>
                                                            </td>
                                                            <td class="px-3 py-2">
                                                                <div class="text-xs text-gray-700 max-w-[220px] truncate" :title="guest.email || 'N/A'" x-text="guest.email || 'N/A'"></div>
                                                            </td>
                                                        </tr>
                                                    </template>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </template>

                        <!-- Sin huéspedes -->
                        <template x-if="!guestsData.guests || guestsData.guests.length === 0">
                            <div class="text-center py-8">
                                <i class="fas fa-users text-3xl text-gray-300 mb-3"></i>
                                <p class="text-gray-500 text-sm font-medium">No hay huéspedes registrados</p>
                            </div>
                        </template>
                    </div>

                    <!-- Footer -->
                    <div class="bg-gray-50 px-5 py-3 flex flex-row-reverse gap-3 border-t border-gray-100">
                        <button type="button" 
                                @click="show = false"
                                class="inline-flex justify-center items-center px-5 py-2 rounded-lg border border-gray-200 shadow-sm text-xs font-bold text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 transition-all duration-200">
                            Cerrar
                        </button>
                    </div>
                </div>
            </template>
        </div>
    </div>
</div>
