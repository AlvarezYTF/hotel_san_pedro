@extends('layouts.app')

@section('title', 'Facturas Electrónicas')
@section('header', 'Facturas Electrónicas')

@section('content')
<div class="space-y-4 sm:space-y-6">
    <!-- Componente Principal -->
    <livewire:electronic-invoices.electronic-invoices-table />
    
    <!-- Modal de Creación -->
    <livewire:electronic-invoices.create-electronic-invoice-modal />
</div>

<!-- Modal de Confirmación de Eliminación (Global) -->
<div x-data="{
    show: false,
    invoice: null,
    init() {
        // Escuchar eventos del componente de tabla
        this.showDeleteModalHandler = (event) => {
            // Livewire envía los datos directamente, no en event.detail
            const invoiceData = Array.isArray(event) ? event[0] : event;
            
            this.invoice = {
                id: invoiceData?.id || null,
                document: invoiceData?.document || '',
                customer_name: invoiceData?.customer_name || '',
                total: parseFloat(invoiceData?.total || 0),
                status_label: invoiceData?.status_label || '',
                reference_code: invoiceData?.reference_code || ''
            };
            
            this.show = true;
        };
        
        this.closeDeleteModalHandler = () => {
            this.show = false;
            this.invoice = null;
        };
        
        // Registrar listeners
        window.Livewire?.on('show-delete-modal', this.showDeleteModalHandler);
        window.Livewire?.on('close-delete-modal', this.closeDeleteModalHandler);
        
        // También cerrar con tecla ESC
        this.keydownHandler = (e) => {
            if (e.key === 'Escape' && this.show) {
                this.cancelDelete();
            }
        };
        document.addEventListener('keydown', this.keydownHandler);
    },
    destroy() {
        // Limpiar listeners para evitar acumulación
        window.Livewire?.off('show-delete-modal', this.showDeleteModalHandler);
        window.Livewire?.off('close-delete-modal', this.closeDeleteModalHandler);
        document.removeEventListener('keydown', this.keydownHandler);
    },
    confirmDelete() {
        if (this.invoice) {
            // Llamar al método del componente Livewire usando dispatch
            window.Livewire.dispatch('delete-confirmed-invoice');
        }
    },
    cancelDelete() {
        // Pequeño delay para asegurar que el estado se limpie correctamente
        setTimeout(() => {
            this.show = false;
            this.invoice = null;
        }, 10);
    }
}">

    <!-- Modal de Confirmación de Eliminación (dentro del mismo x-data) -->
    <div x-show="show" 
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-50 overflow-y-auto"
         style="display: none;">
        
        <!-- Backdrop -->
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 transition-opacity bg-gray-900 bg-opacity-75" aria-hidden="true"></div>

            <!-- Modal panel -->
            <div class="inline-block w-full max-w-md p-6 my-8 overflow-hidden text-left align-middle transition-all transform bg-white shadow-xl rounded-2xl sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                
                <!-- Header -->
                <div class="flex items-center justify-center w-12 h-12 mx-auto bg-red-100 rounded-full">
                    <i class="fas fa-exclamation-triangle text-red-600 text-xl"></i>
                </div>
                
                <!-- Content -->
                <div class="mt-4 text-center">
                    <h3 class="text-lg font-medium leading-6 text-gray-900" id="modal-title">
                        Eliminar Factura Electrónica
                    </h3>
                    
                    <div x-show="invoice" class="mt-4">
                        <div class="bg-gray-50 rounded-lg p-4 border border-gray-200">
                            <div class="space-y-2">
                                <div class="flex justify-between text-sm">
                                    <span class="text-gray-500">Número:</span>
                                    <span class="font-semibold text-gray-900" x-text="invoice?.document"></span>
                                </div>
                                <div class="flex justify-between text-sm">
                                    <span class="text-gray-500">Cliente:</span>
                                    <span class="font-semibold text-gray-900" x-text="invoice?.customer_name"></span>
                                </div>
                                <div class="flex justify-between text-sm">
                                    <span class="text-gray-500">Total:</span>
                                    <span class="font-semibold text-gray-900" x-text="'$' + (invoice?.total || 0).toFixed(2)"></span>
                                </div>
                                <div class="flex justify-between text-sm">
                                    <span class="text-gray-500">Estado:</span>
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-semibold bg-amber-50 text-amber-700 border-amber-200 border">
                                        <i class="fas fa-clock mr-1.5 text-xs"></i>
                                        <span x-text="invoice?.status_label"></span>
                                    </span>
                                </div>
                                <div x-show="invoice?.reference_code" class="flex justify-between text-sm">
                                    <span class="text-gray-500">Código Ref:</span>
                                    <span class="font-mono text-xs text-gray-600" x-text="invoice?.reference_code"></span>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mt-4">
                        <p class="text-sm text-gray-500">
                            Esta acción eliminará permanentemente la factura de la API de Factus y no se puede deshacer.
                        </p>
                        <p class="mt-2 text-sm font-medium text-red-600">
                            Solo se pueden eliminar facturas que no hayan sido validadas por la DIAN.
                        </p>
                    </div>
                </div>

                <!-- Actions -->
                <div class="mt-6 sm:flex sm:flex-row-reverse gap-3">
                    <button type="button" 
                            @click="confirmDelete()"
                            class="inline-flex justify-center w-full px-4 py-2 text-base font-medium text-white bg-red-600 border border-transparent rounded-xl shadow-sm hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm transition-colors duration-200">
                        <i class="fas fa-trash mr-2"></i>
                        Sí, Eliminar Factura
                    </button>
                    
                    <button type="button" 
                            @click="cancelDelete()"
                            class="inline-flex justify-center w-full px-4 py-2 mt-3 text-base font-medium text-gray-700 bg-white border border-gray-300 rounded-xl shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-emerald-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm transition-colors duration-200">
                        <i class="fas fa-times mr-2"></i>
                        Cancelar
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // Manejar tecla ESC para cerrar modales
    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape') {
            // Cerrar modales usando Livewire
            if (typeof Livewire !== 'undefined') {
                Livewire.dispatch('close-create-electronic-invoice-modal');
            }
        }
    });
</script>
@endsection
