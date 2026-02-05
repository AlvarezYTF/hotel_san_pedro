<?php

namespace App\Livewire\ElectronicInvoices;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\ElectronicInvoice;
use Livewire\Attributes\On;
use Illuminate\Support\Facades\Log;

class ElectronicInvoicesTable extends Component
{
    use WithPagination;

    public $search = '';
    public $status = '';
    public $perPage = 15;
    public $invoiceIdToDelete = null;
    public $goToPage = null;

    protected $queryString = [
        'search' => ['except' => ''],
        'status' => ['except' => ''],
        'perPage' => ['except' => 15],
    ];

    protected $listeners = [
        'invoice-created' => '$refresh',
        'invoice-updated' => '$refresh',
        'invoice-deleted' => '$refresh',
        'delete-confirmed-invoice' => 'deleteConfirmedInvoice',
        'close-delete-modal' => 'closeDeleteModal',
    ];

    public function mount()
    {
        $this->perPage = 15;
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedStatus()
    {
        $this->resetPage();
    }

    public function updatedPerPage()
    {
        $this->resetPage();
    }

    public function updatedGoToPage()
    {
        if (is_numeric($this->goToPage) && $this->goToPage >= 1) {
            // Validar que la página no exceda el máximo
            $maxPage = $this->invoices->lastPage() ?? 1;
            $this->goToPage = min($this->goToPage, $maxPage);
            
            // Usar el método nativo de Livewire
            $this->setPage($this->goToPage);
        }
    }

    public function render()
    {
        $query = ElectronicInvoice::with(['customer.taxProfile', 'customer.taxProfile.identificationDocument'])
            ->orderBy('created_at', 'desc');

        // Filtro por búsqueda
        if (!empty($this->search)) {
            $query->where(function ($q) {
                $q->where('document', 'like', '%' . $this->search . '%')
                  ->orWhere('reference_code', 'like', '%' . $this->search . '%')
                  ->orWhereHas('customer', function ($subQ) {
                      $subQ->where('name', 'like', '%' . $this->search . '%');
                  });
            });
        }

        // Filtro por estado
        if (!empty($this->status)) {
            $query->where('status', $this->status);
        }

        $invoices = $query->paginate($this->perPage);

        return view('livewire.electronic-invoices.electronic-invoices-table', [
            'invoices' => $invoices
        ]);
    }

    /**
     * Refrescar/Rehidratar la tabla
     */
    public function refreshTable()
    {
        // Resetear paginación para volver a la primera página
        $this->resetPage();
        
        // Forzar recarga de datos
        $this->dispatch('notify', [
            'type' => 'info',
            'message' => 'Tabla actualizada correctamente'
        ]);
    }

    /**
     * Escuchar eventos de actualización
     */
    #[On('invoice-created')]
    #[On('invoice-updated')]
    #[On('invoice-deleted')]
    public function onInvoiceUpdated()
    {
        // Refrescar automáticamente cuando hay cambios
        $this->refreshTable();
    }

    public function getStatusBadge($status)
    {
        $badges = [
            'accepted' => [
                'class' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                'icon' => 'fa-check-circle',
                'text' => 'Aceptada'
            ],
            'rejected' => [
                'class' => 'bg-red-50 text-red-700 border-red-200',
                'icon' => 'fa-times-circle',
                'text' => 'Rechazada'
            ],
            'sent' => [
                'class' => 'bg-blue-50 text-blue-700 border-blue-200',
                'icon' => 'fa-paper-plane',
                'text' => 'Enviada'
            ],
            'pending' => [
                'class' => 'bg-amber-50 text-amber-700 border-amber-200',
                'icon' => 'fa-clock',
                'text' => 'Pendiente'
            ],
            'cancelled' => [
                'class' => 'bg-gray-50 text-gray-700 border-gray-200',
                'icon' => 'fa-ban',
                'text' => 'Cancelada'
            ],
            'deleted' => [
                'class' => 'bg-gray-50 text-gray-600 border-gray-200',
                'icon' => 'fa-trash',
                'text' => 'Eliminada'
            ],
        ];

        return $badges[$status] ?? $badges['pending'];
    }

    public function deleteInvoice($invoiceId)
    {
        try {
            $invoice = ElectronicInvoice::findOrFail($invoiceId);
            
            // Solo permitir eliminar facturas que se puedan eliminar (pendientes/rechazadas y no validadas)
            if (!$invoice->canBeDeleted()) {
                $this->dispatch('notify', [
                    'type' => 'error',
                    'message' => 'Esta factura no se puede eliminar. Solo se pueden eliminar facturas pendientes o rechazadas que no hayan sido validadas por la DIAN.'
                ]);
                return;
            }

            // Usar el servicio para eliminar de Factus API y localmente
            $invoiceService = app(\App\Services\ElectronicInvoiceService::class);
            $invoiceService->deleteInvoice($invoice);
            
            $this->dispatch('notify', [
                'type' => 'success',
                'message' => 'Factura eliminada exitosamente de Factus.'
            ]);
            
            // Refrescar la tabla
            $this->dispatch('invoice-deleted');
        } catch (\Exception $e) {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'Error al eliminar la factura: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Mostrar modal de confirmación para eliminar factura
     */
    public function confirmDelete($invoiceId)
    {
        $invoice = ElectronicInvoice::findOrFail($invoiceId);
        $this->invoiceIdToDelete = $invoiceId;
        
        // Enviar datos de la factura al frontend
        $this->dispatch('show-delete-modal', [
            'id' => $invoice->id,
            'document' => $invoice->document,
            'customer_name' => $invoice->customer->name,
            'total' => (float) $invoice->total,
            'status_label' => $invoice->getStatusLabel(),
            'reference_code' => $invoice->reference_code,
        ]);
    }

    /**
     * Cerrar modal de eliminación
     */
    public function closeDeleteModal()
    {
        $this->invoiceIdToDelete = null;
        $this->dispatch('close-delete-modal');
    }

    /**
     * Eliminar factura confirmada
     */
    public function deleteConfirmedInvoice()
    {
        if (!$this->invoiceIdToDelete) {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'No se encontró la factura a eliminar.'
            ]);
            return;
        }

        try {
            $invoice = ElectronicInvoice::findOrFail($this->invoiceIdToDelete);
            
            // Solo permitir eliminar facturas que se puedan eliminar (pendientes/rechazadas y no validadas)
            if (!$invoice->canBeDeleted()) {
                $this->dispatch('notify', [
                    'type' => 'error',
                    'message' => 'Esta factura no se puede eliminar. Solo se pueden eliminar facturas pendientes o rechazadas que no hayan sido validadas por la DIAN.'
                ]);
                $this->closeDeleteModal();
                return;
            }

            // Usar el servicio para eliminar de Factus API y localmente
            $invoiceService = app(\App\Services\ElectronicInvoiceService::class);
            $invoiceService->deleteInvoice($invoice);
            
            $this->closeDeleteModal();
            
            // Verificar si la factura fue eliminada exitosamente
            if ($invoice->fresh()->isDeleted()) {
                $this->dispatch('notify', [
                    'type' => 'success',
                    'message' => 'Factura eliminada exitosamente.'
                ]);
            } else {
                $this->dispatch('notify', [
                    'type' => 'warning',
                    'message' => 'La factura fue marcada como eliminada localmente, pero no se encontró en Factus API.'
                ]);
            }
            
            // Refrescar la tabla
            $this->dispatch('invoice-deleted');
        } catch (\Exception $e) {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'Error al eliminar la factura: ' . $e->getMessage()
            ]);
        }
    }
}
