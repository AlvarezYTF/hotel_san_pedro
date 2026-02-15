<?php

namespace App\Livewire\Reservations\Partials;

use Livewire\Component;

class GuestAssignmentPanel extends Component
{
    // Props desde el padre
    public $roomId = '';
    public $roomGuests = [];
    public $showMultiRoomSelector = false;
    public $selectedRoomIds = [];
    public $customers = [];
    public $guestModalOpen = false;
    public $currentRoomForGuestAssignment = null;
    public $guestSearchTerm = '';
    public $guestModalTab = 'search';
    public $selectedGuestForAdd = null;

    protected $listeners = [
        'guestAdded' => 'handleGuestAdded',
        'guestRemoved' => 'handleGuestRemoved',
    ];

    /**
     * Obtener asignaciones de huéspedes (modo simple)
     */
    public function getAssignedGuestsProperty(): array
    {
        // Solo para modo simple
        if ($this->showMultiRoomSelector) {
            return [];
        }

        if (empty($this->roomId)) {
            return [];
        }

        $roomIdInt = is_numeric($this->roomId) ? (int)$this->roomId : 0;
        if ($roomIdInt <= 0) {
            return [];
        }

        $guests = $this->roomGuests[$roomIdInt] ?? [];

        if (!is_array($guests) || empty($guests)) {
            return [];
        }

        // Validar y retornar
        return array_values(array_filter($guests, function ($guest): bool {
            return is_array($guest)
                && !empty($guest['id'])
                && is_numeric($guest['id'])
                && isset($guest['name']);
        }));
    }

    /**
     * Obtener huéspedes filtrados por término de búsqueda
     */
    public function getFilteredGuestsProperty(): array
    {
        $allCustomers = $this->customers ?? [];

        // Determinar sala objetivo
        $targetRoomId = null;
        if ($this->currentRoomForGuestAssignment !== null) {
            $targetRoomId = (int)$this->currentRoomForGuestAssignment;
        } elseif (!$this->showMultiRoomSelector && !empty($this->roomId)) {
            $targetRoomId = is_numeric($this->roomId) ? (int)$this->roomId : 0;
        }

        // Obtener IDs ya asignados
        $alreadyAssignedIds = [];
        if (is_array($this->roomGuests) && !empty($this->roomGuests)) {
            foreach ($this->roomGuests as $guests) {
                if (!is_array($guests)) {
                    continue;
                }
                foreach ($guests as $guest) {
                    if (is_array($guest) && !empty($guest['id'])) {
                        $alreadyAssignedIds[] = (int)$guest['id'];
                    }
                }
            }
        }

        $alreadyAssignedIds = array_unique($alreadyAssignedIds);
        $searchTerm = mb_strtolower(trim($this->guestSearchTerm));

        $filtered = [];

        foreach ($allCustomers as $customer) {
            if (!is_array($customer)) {
                continue;
            }

            $customerId = (int)($customer['id'] ?? 0);
            if ($customerId <= 0) {
                continue;
            }

            // No mostrar si ya está asignado
            if (in_array($customerId, $alreadyAssignedIds, true)) {
                continue;
            }

            // Si no hay búsqueda, retornar primeros 5
            if (empty($searchTerm)) {
                $filtered[] = $customer;
                if (count($filtered) >= 5) {
                    break;
                }
                continue;
            }

            // Aplicar búsqueda
            $name = mb_strtolower($customer['name'] ?? '');
            $phone = mb_strtolower($customer['phone'] ?? '');
            $identification = mb_strtolower($customer['taxProfile']['identification'] ?? '');

            if (str_contains($name, $searchTerm) ||
                str_contains($phone, $searchTerm) ||
                str_contains($identification, $searchTerm)) {
                $filtered[] = $customer;
                if (count($filtered) >= 20) {
                    break;
                }
            }
        }

        return $filtered;
    }

    /**
     * Contar huéspedes asignados a una sala
     */
    public function getRoomGuestsCount($roomId): int
    {
        $roomIdInt = is_numeric($roomId) ? (int)$roomId : 0;
        if ($roomIdInt <= 0) {
            return 0;
        }

        $guests = $this->roomGuests[$roomIdInt] ?? [];

        if (!is_array($guests)) {
            return 0;
        }

        return count(array_filter($guests, function ($guest): bool {
            return is_array($guest) && !empty($guest['id']);
        }));
    }

    public function openGuestModal(?int $roomId = null)
    {
        if ($roomId === null && !$this->showMultiRoomSelector && !empty($this->roomId)) {
            $roomId = is_numeric($this->roomId) ? (int)$this->roomId : 0;
        }

        $this->currentRoomForGuestAssignment = $roomId;
        $this->guestModalOpen = true;
        $this->guestModalTab = 'search';
        $this->selectedGuestForAdd = null;
        $this->guestSearchTerm = '';
    }

    public function closeGuestModal()
    {
        $this->guestModalOpen = false;
        $this->currentRoomForGuestAssignment = null;
    }

    public function setGuestModalTab(string $tab)
    {
        $this->guestModalTab = $tab;
    }

    public function selectGuestForAssignment($customerId)
    {
        $customer = collect($this->customers)->firstWhere('id', $customerId);

        if (!$customer) {
            return;
        }

        // Emitir evento al padre para agregar huésped
        $this->dispatch('addGuestRequested', customerId: $customerId, roomId: $this->currentRoomForGuestAssignment);
    }

    public function removeGuest(?int $roomId, int $index)
    {
        if ($roomId === null && !$this->showMultiRoomSelector && !empty($this->roomId)) {
            $roomId = is_numeric($this->roomId) ? (int)$this->roomId : 0;
        }

        if (empty($roomId)) {
            return;
        }

        if (!isset($this->roomGuests[$roomId]) || !is_array($this->roomGuests[$roomId])) {
            return;
        }

        if (isset($this->roomGuests[$roomId][$index])) {
            unset($this->roomGuests[$roomId][$index]);
            $this->roomGuests[$roomId] = array_values($this->roomGuests[$roomId]);
            $this->roomGuests = $this->roomGuests;

            $this->dispatch('guestRemoved', roomId: $roomId);
        }
    }

    public function handleGuestAdded($roomId, $customerId)
    {
        // Sincronizar estado si es necesario
    }

    public function handleGuestRemoved($roomId)
    {
        // Sincronizar estado si es necesario
    }

    public function render()
    {
        return view('livewire.reservations.partials.guest-assignment-panel', [
            'assignedGuests' => $this->assignedGuests,
            'filteredGuests' => $this->filteredGuests,
        ]);
    }
}
