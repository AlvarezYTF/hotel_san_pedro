<div x-show="createRoomModal" 
     x-cloak
     class="fixed inset-0 z-[100]" 
     aria-labelledby="modal-title" role="dialog" aria-modal="true"
     @click="$wire.set('createRoomModal', false)"
     x-transition:enter="ease-out duration-300"
     x-transition:enter-start="opacity-0"
     x-transition:enter-end="opacity-100"
     x-transition:leave="ease-in duration-200"
     x-transition:leave-start="opacity-100"
     x-transition:leave-end="opacity-0">
    
    <div class="flex items-center justify-center min-h-screen p-4" @click.stop>
        <!-- Overlay -->
        <div class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm"></div>

        <!-- Modal Content -->
        <div x-show="createRoomModal" 
             x-transition:enter="ease-out duration-300" 
             x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" 
             x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" 
             x-transition:leave="ease-in duration-200" 
             x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" 
             x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" 
             @click.stop
             x-effect="if (createRoomModal) { $nextTick(() => { document.querySelector('[data-create-room-scroll]')?.scrollTo(0, 0); }); }"
             class="relative bg-white rounded-2xl shadow-2xl w-full max-w-4xl overflow-hidden transform transition-all max-h-[90vh] flex flex-col">
            
            <!-- Header -->
            <div class="bg-white px-6 pt-6 pb-4 border-b border-gray-100 flex-shrink-0">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-3">
                        <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
                            <i class="fas fa-bed text-xl"></i>
                        </div>
                        <div>
                            <h3 class="text-2xl font-black text-gray-900" id="modal-title">Nueva Habitación</h3>
                            <p class="text-sm text-gray-500 mt-1">Registra una nueva habitación con su configuración y precios</p>
                        </div>
                    </div>
                    <button type="button" 
                            @click="$wire.set('createRoomModal', false)"
                            class="text-gray-400 hover:text-gray-900">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>
            </div>

            <!-- Content -->
            <div class="overflow-y-auto flex-1" data-create-room-scroll>
                <livewire:create-room />
            </div>

        </div>
    </div>
</div>

