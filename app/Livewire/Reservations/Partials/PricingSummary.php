<?php

namespace App\Livewire\Reservations\Partials;

use Barryvdh\DomPDF\Facade\Pdf;
use Livewire\Component;

class PricingSummary extends Component
{
    // Props desde el padre
    public $total = 0;
    public $deposit = 0;
    public $nights = 0;
    public $autoCalculatedTotal = 0;
    public $roomsData = [];
    public $selectedRoomIds = [];
    public $showMultiRoomSelector = false;

    public function getIsReceiptReadyProperty(): bool
    {
        $total = is_numeric($this->total) ? (float)$this->total : 0.0;
        $deposit = is_numeric($this->deposit) ? (float)$this->deposit : 0.0;

        return $total > 0 && $deposit >= 0 && $deposit <= $total;
    }

    public function getStatusProperty(): string
    {
        return $this->balance <= 0 && $this->isReceiptReady ? 'Liquidado' : 'Pendiente';
    }

    /**
     * Obtener el saldo pendiente
     */
    public function getBalanceProperty(): int
    {
        try {
            $total = is_numeric($this->total) ? (float)$this->total : 0.0;
            $deposit = is_numeric($this->deposit) ? (float)$this->deposit : 0.0;

            $balance = $total - $deposit;
            return (int)max(0, $balance);
        } catch (\Exception $e) {
            return 0;
        }
    }

    /**
     * Obtener el total automático calculado
     */
    public function getAutoCalculatedTotalProperty(): float
    {
        if ($this->showMultiRoomSelector) {
            if (!is_array($this->selectedRoomIds) || empty($this->selectedRoomIds)) {
                return 0;
            }
            if ($this->nights <= 0) {
                return 0;
            }
            // Implementaría lógica de múltiples habitaciones aquí
            return 0;
        }

        // Modo simple: no implementar por ahora
        return (float)$this->total;
    }

    public function restoreSuggestedTotal()
    {
        $this->total = $this->autoCalculatedTotal;
        $this->dispatch('totalRestored', total: $this->autoCalculatedTotal);
    }

    public function downloadReceipt()
    {
        if (!$this->isReceiptReady) {
            return null;
        }

        $total = is_numeric($this->total) ? (float)$this->total : 0.0;
        $deposit = is_numeric($this->deposit) ? (float)$this->deposit : 0.0;
        $balance = max(0, $total - $deposit);
        $status = $balance <= 0 ? 'Liquidado' : 'Pendiente';
        $issuedAt = now();

        $pdf = Pdf::loadView('reservations.receipt-preview-pdf', [
            'issuedAt' => $issuedAt,
            'customerName' => 'Cliente no disponible',
            'customerIdentification' => 'S/N',
            'customerPhone' => 'S/N',
            'checkInDate' => null,
            'checkOutDate' => null,
            'checkInTime' => null,
            'nights' => (int) ($this->nights ?? 0),
            'roomSummaries' => [],
            'totalAmount' => $total,
            'depositAmount' => $deposit,
            'balanceDue' => $balance,
            'status' => $status,
            'notes' => '',
        ])->setPaper('a4', 'portrait');

        $pdfContent = $pdf->output();

        return response()->streamDownload(function () use ($pdfContent): void {
            echo $pdfContent;
        }, 'Comprobante_Reserva_' . $issuedAt->format('Ymd-His') . '.pdf', [
            'Content-Type' => 'application/pdf',
        ]);
    }

    public function render()
    {
        return view('livewire.reservations.partials.pricing-summary', [
            'balance' => $this->balance,
            'autoCalculatedTotal' => $this->autoCalculatedTotal,
            'isReceiptReady' => $this->isReceiptReady,
            'status' => $this->status,
        ]);
    }
}
