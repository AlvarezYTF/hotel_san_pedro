<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Sale;

class ShowSale extends Component
{
    public bool $isModal = false;
    public Sale $sale;

    public function mount(Sale $sale, bool $isModal = false)
    {
        $this->isModal = $isModal;
        $this->sale = $sale->load(['user', 'room.reservations.customer', 'items.product.category']);
    }

    public function render()
    {
        return view('livewire.show-sale');
    }
}
