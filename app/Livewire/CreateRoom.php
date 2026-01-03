<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Room;
use App\Enums\RoomStatus;
use App\Enums\VentilationType;
use Illuminate\Validation\ValidationException;

class CreateRoom extends Component
{
    public string $room_number = '';
    public int $beds_count = 1;
    public int $max_capacity = 2;
    public bool $auto_calculate = true;
    public string $ventilation_type = '';
    public array $occupancy_prices = [];

    protected function rules(): array
    {
        return [
            'room_number' => 'required|string|unique:rooms,room_number',
            'beds_count' => 'required|integer|min:1',
            'max_capacity' => 'required|integer|min:1',
            'ventilation_type' => 'required|string|in:' . implode(',', array_column(VentilationType::cases(), 'value')),
            'occupancy_prices' => 'required|array',
            'occupancy_prices.*' => 'nullable|integer|min:1',
        ];
    }

    protected function messages(): array
    {
        return [
            'room_number.required' => 'El número de habitación es obligatorio.',
            'room_number.unique' => 'Este número de habitación ya existe.',
            'beds_count.required' => 'El número de camas es obligatorio.',
            'beds_count.min' => 'Debe haber al menos 1 cama.',
            'max_capacity.required' => 'La capacidad máxima es obligatoria.',
            'max_capacity.min' => 'La capacidad máxima debe ser al menos 1.',
            'ventilation_type.required' => 'El tipo de ventilación es obligatorio.',
            'occupancy_prices.required' => 'Debe definir precios para al menos una ocupación.',
            'occupancy_prices.*.min' => 'Los precios deben ser mayores a 0.',
        ];
    }

    public function mount(): void
    {
        $this->updateCapacity();
    }

    public function updatedBedsCount(): void
    {
        if ($this->auto_calculate) {
            $this->updateCapacity();
        }
    }

    public function updatedAutoCalculate(): void
    {
        if ($this->auto_calculate) {
            $this->updateCapacity();
        }
    }

    public function updatedMaxCapacity(): void
    {
        $this->initializePrices();
    }

    public function updatedOccupancyPrices($value, $key): void
    {
        // Convert 0, '0', empty string, or null to null to treat it as empty/placeholder
        if ($value === 0 || $value === '0' || $value === '' || $value === null) {
            $this->occupancy_prices[$key] = null;
        } else {
            $intValue = (int)$value;
            if ($intValue > 0) {
                $this->occupancy_prices[$key] = $intValue;
            } else {
                $this->occupancy_prices[$key] = null;
            }
        }
    }

    private function updateCapacity(): void
    {
        if (!isset($this->beds_count) || $this->beds_count < 1) {
            $this->beds_count = 1;
        }
        
        if (!isset($this->max_capacity) || $this->max_capacity < 1) {
            $this->max_capacity = 2;
        }
        
        if ($this->auto_calculate) {
            $this->max_capacity = $this->beds_count * 2;
        }
        
        $this->initializePrices();
    }

    private function initializePrices(): void
    {
        if (!isset($this->max_capacity) || $this->max_capacity < 1) {
            $this->max_capacity = 2;
        }
        
        $newPrices = [];
        for ($i = 1; $i <= $this->max_capacity; $i++) {
            // Preserve existing non-zero values, otherwise set to null (will show as placeholder)
            $existingValue = $this->occupancy_prices[$i] ?? null;
            $previousValue = $this->occupancy_prices[$i - 1] ?? null;
            
            if ($existingValue !== null && $existingValue > 0) {
                $newPrices[$i] = $existingValue;
            } elseif ($previousValue !== null && $previousValue > 0) {
                $newPrices[$i] = $previousValue;
            } else {
                $newPrices[$i] = null;
            }
        }
        $this->occupancy_prices = $newPrices;
    }

    public function store(): void
    {
        // Validate that at least one price is set
        $hasAtLeastOnePrice = false;
        foreach ($this->occupancy_prices as $value) {
            if ($value !== null && $value > 0) {
                $hasAtLeastOnePrice = true;
                break;
            }
        }
        
        if (!$hasAtLeastOnePrice) {
            throw ValidationException::withMessages([
                'occupancy_prices' => 'Debe definir al menos un precio de ocupación.',
            ]);
        }
        
        // Convert null values to 0 for validation
        $pricesForValidation = [];
        foreach ($this->occupancy_prices as $key => $value) {
            $pricesForValidation[$key] = $value !== null ? (int)$value : null;
        }
        
        // Temporarily set occupancy_prices for validation
        $originalPrices = $this->occupancy_prices;
        $this->occupancy_prices = $pricesForValidation;
        
        $this->validate();
        
        // Restore original prices
        $this->occupancy_prices = $originalPrices;

        // Filter out null values and convert to integers for storage
        $validatedPrices = [];
        foreach ($this->occupancy_prices as $key => $value) {
            if ($value !== null && $value > 0) {
                $validatedPrices[$key] = (int)$value;
            } else {
                $validatedPrices[$key] = 0;
            }
        }

        $validated = [
            'room_number' => $this->room_number,
            'beds_count' => $this->beds_count,
            'max_capacity' => $this->max_capacity,
            'ventilation_type' => $this->ventilation_type,
            'occupancy_prices' => $validatedPrices,
            'status' => RoomStatus::LIBRE->value,
            'last_cleaned_at' => now(),
        ];

        $validated['price_1_person'] = $validated['occupancy_prices'][1] ?? 0;
        $validated['price_2_persons'] = $validated['occupancy_prices'][2] ?? 0;
        $validated['price_per_night'] = $validated['price_2_persons'];

        $room = Room::create($validated);

        // Reset form with specific values to avoid property not found errors
        $this->room_number = '';
        $this->beds_count = 1;
        $this->max_capacity = 2;
        $this->auto_calculate = true;
        $this->ventilation_type = '';
        $this->occupancy_prices = [];
        
        // Re-initialize after reset
        $this->updateCapacity();

        // Dispatch events
        $this->dispatch('room-created', roomId: $room->id);
        $this->dispatch('notify', type: 'success', message: 'Habitación creada exitosamente.');
    }

    public function render()
    {
        return view('livewire.create-room', [
            'ventilationTypes' => VentilationType::cases(),
        ]);
    }
}

