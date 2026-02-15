<?php

namespace App\Livewire\Reservations\Partials;

use Livewire\Component;
use App\Services\RoomAvailabilityService;
use Carbon\Carbon;

class RoomSelector extends Component
{
    // Props desde el padre
    public $checkIn = '';
    public $checkOut = '';
    public $datesCompleted = false;
    public $rooms = [];
    public $roomsData = [];
    public $roomId = '';
    public $selectedRoomIds = [];
    public $showMultiRoomSelector = false;
    public $excludeReservationId = null;

    // Service
    private RoomAvailabilityService $availabilityService;

    public function boot()
    {
        $this->availabilityService = new RoomAvailabilityService(null);
    }

    protected $listeners = [
        'datesUpdated' => 'handleDatesUpdated',
        'roomSelected' => 'handleRoomSelected',
    ];

    /**
     * Obtener habitaciones disponibles para el rango de fechas
     */
    public function getAvailableRoomsProperty(): array
    {
        // Validar fechas
        if (empty($this->checkIn) || empty($this->checkOut)) {
            return [];
        }

        try {
            if (!isset($this->availabilityService)) {
                $this->availabilityService = new RoomAvailabilityService(null);
            }

            $checkIn = Carbon::parse($this->checkIn);
            $checkOut = Carbon::parse($this->checkOut);

            // Validar rango
            if ($checkOut->lte($checkIn)) {
                return [];
            }

            // Usar el servicio para obtener disponibilidad
            $rooms = $this->rooms ?: $this->roomsData;
            return $this->availabilityService->getAvailableRooms(
                $checkIn,
                $checkOut,
                $rooms,
                !empty($this->excludeReservationId) ? (int)$this->excludeReservationId : null
            );

        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Obtener habitación seleccionada (modo simple)
     */
    public function getSelectedRoomProperty()
    {
        if (empty($this->roomId)) {
            return null;
        }

        try {
            $roomId = (int)$this->roomId;
            $allRooms = $this->roomsData ?: $this->rooms;

            if (empty($allRooms)) {
                return null;
            }

            foreach ($allRooms as $room) {
                if (!is_array($room)) {
                    continue;
                }
                if (((int)($room['id'] ?? 0)) === $roomId) {
                    return [
                        'id' => $room['id'] ?? $roomId,
                        'number' => $room['number'] ?? $room['room_number'] ?? null,
                        'capacity' => $room['capacity'] ?? $room['max_capacity'] ?? 0,
                    ];
                }
            }
        } catch (\Exception $e) {
            return null;
        }

        return null;
    }

    public function toggleMultiRoomMode()
    {
        $this->showMultiRoomSelector = !$this->showMultiRoomSelector;

        // Mantener una selección coherente al cambiar de modo
        if ($this->showMultiRoomSelector) {
            $this->selectedRoomIds = !empty($this->roomId) ? [(int)$this->roomId] : [];
        } else {
            $firstSelected = (int)($this->selectedRoomIds[0] ?? 0);
            $this->roomId = $firstSelected > 0 ? (string)$firstSelected : '';
            $this->selectedRoomIds = $firstSelected > 0 ? [$firstSelected] : [];
        }

        // Emitir eventos al padre
        $this->dispatch('multiRoomModeToggled', multiMode: $this->showMultiRoomSelector);
        $this->dispatch('roomsSelectionUpdated', selectedRoomIds: $this->selectedRoomIds);
    }

    public function selectRoom($roomId)
    {
        $roomIdInt = (int)$roomId;
        if ($roomIdInt <= 0) {
            return;
        }

        if ($this->showMultiRoomSelector) {
            $isSelected = $this->toggleSelectedRoomIds($roomIdInt);
            $this->dispatch('roomSelected', roomId: $roomIdInt, selected: $isSelected, checkIn: $this->checkIn, checkOut: $this->checkOut);
        } else {
            $this->roomId = (string)$roomIdInt;
            $this->selectedRoomIds = [$roomIdInt];
            $this->dispatch('roomSelected', roomId: $roomIdInt, selected: true, checkIn: $this->checkIn, checkOut: $this->checkOut);
        }

        $this->dispatch('roomsSelectionUpdated', selectedRoomIds: $this->selectedRoomIds);
    }

    /**
     * Toggle en modo multi-room
     */
    private function toggleSelectedRoomIds($roomIdInt): bool
    {
        if (!is_array($this->selectedRoomIds)) {
            $this->selectedRoomIds = [];
        }

        $currentIds = array_map('intval', $this->selectedRoomIds);
        $index = array_search($roomIdInt, $currentIds, true);

        if ($index !== false) {
            unset($currentIds[$index]);
            $this->selectedRoomIds = array_values($currentIds);
            return false;
        } else {
            $currentIds[] = $roomIdInt;
            $this->selectedRoomIds = array_values(array_unique($currentIds));
            return true;
        }
    }

    public function clearSelectedRooms()
    {
        $this->selectedRoomIds = [];
        $this->roomId = '';
        $this->dispatch('roomsCleared');
        $this->dispatch('roomsSelectionUpdated', selectedRoomIds: $this->selectedRoomIds);
    }

    public function updatedCheckIn($value): void
    {
        if (empty($value)) {
            $this->checkOut = '';
            return;
        }

        try {
            $checkIn = Carbon::parse((string)$value)->startOfDay();
            if (!empty($this->checkOut)) {
                $checkOut = Carbon::parse((string)$this->checkOut)->startOfDay();
                if ($checkOut->lte($checkIn)) {
                    $this->checkOut = '';
                }
            }
        } catch (\Throwable $e) {
            $this->checkOut = '';
        }
    }

    public function handleDatesUpdated($checkIn, $checkOut, $datesCompleted)
    {
        $this->checkIn = $checkIn;
        $this->checkOut = $checkOut;
        $this->datesCompleted = (bool)$datesCompleted && $this->hasValidDateRange();
    }

    public function handleRoomSelected($roomId)
    {
        // Puede usarse para sincronizar si es necesario
    }

    public function render()
    {
        $computedDatesCompleted = $this->hasValidDateRange();
        $this->datesCompleted = $computedDatesCompleted;

        return view('livewire.reservations.partials.room-selector', [
            'availableRooms' => $this->availableRooms,
            'selectedRoom' => $this->selectedRoom,
            'datesCompleted' => $computedDatesCompleted,
        ]);
    }

    private function hasValidDateRange(): bool
    {
        if (empty($this->checkIn) || empty($this->checkOut)) {
            return false;
        }

        try {
            return Carbon::parse($this->checkOut)->gt(Carbon::parse($this->checkIn));
        } catch (\Throwable $e) {
            return false;
        }
    }

    public function getMinCheckInDateProperty(): string
    {
        return Carbon::today()->format('Y-m-d');
    }

    public function getMinCheckOutDateProperty(): string
    {
        if (empty($this->checkIn)) {
            return '';
        }

        try {
            return Carbon::parse($this->checkIn)->addDay()->format('Y-m-d');
        } catch (\Throwable $e) {
            return '';
        }
    }

    public function getIsCheckOutDisabledProperty(): bool
    {
        return empty($this->checkIn);
    }
}
