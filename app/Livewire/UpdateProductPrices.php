<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class UpdateProductPrices extends Component
{
    public $exchangeRate = '';
    public $showModal = false;
    public $previewMode = false;
    public $affectedProducts = [];
    public $totalProducts = 0;

    protected $rules = [
        'exchangeRate' => 'required|numeric|min:0.01',
    ];

    protected $messages = [
        'exchangeRate.required' => 'La tasa de cambio es obligatoria.',
        'exchangeRate.numeric' => 'La tasa de cambio debe ser un número válido.',
        'exchangeRate.min' => 'La tasa de cambio debe ser mayor a 0.',
    ];

    public function mount()
    {
        $this->totalProducts = Product::where('status', 'active')->count();
    }

    public function openModal()
    {
        $this->showModal = true;
        $this->previewMode = false;
        $this->exchangeRate = '';
        $this->affectedProducts = [];
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->previewMode = false;
        $this->exchangeRate = '';
        $this->affectedProducts = [];
    }

    public function preview()
    {
        $this->validate();

        $rate = (float) $this->exchangeRate;
        
        // Obtener todos los productos activos
        $products = Product::where('status', 'active')->get();
        
        $this->affectedProducts = $products->map(function ($product) use ($rate) {
            $oldPrice = (float) $product->price;
            $newPrice = $oldPrice * $rate;
            
            return [
                'id' => $product->id,
                'name' => $product->name,
                'old_price' => $oldPrice,
                'new_price' => round($newPrice, 2),
            ];
        })->toArray();

        $this->previewMode = true;
    }

    public function updatePrices()
    {
        $this->validate();

        if (empty($this->affectedProducts)) {
            $this->preview();
        }

        $rate = (float) $this->exchangeRate;
        
        DB::beginTransaction();
        
        try {
            $updated = 0;
            
            foreach ($this->affectedProducts as $productData) {
                $product = Product::find($productData['id']);
                if ($product) {
                    $product->update([
                        'price' => $productData['new_price']
                    ]);
                    $updated++;
                }
            }

            DB::commit();

            // Registrar en audit log
            \App\Models\AuditLog::create([
                'user_id' => Auth::id(),
                'event' => 'bulk_price_update',
                'description' => "Actualización masiva de precios: {$updated} productos actualizados con tasa de cambio {$rate}",
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'metadata' => [
                    'exchange_rate' => $rate,
                    'products_updated' => $updated,
                ]
            ]);

            $this->dispatch('notify', type: 'success', message: "Se actualizaron {$updated} productos exitosamente.");
            $this->closeModal();
            
        } catch (\Exception $e) {
            DB::rollBack();
            $this->dispatch('notify', type: 'error', message: 'Error al actualizar los precios: ' . $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.update-product-prices');
    }
}

