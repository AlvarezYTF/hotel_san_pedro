<?php

namespace App\Livewire\Reservations;

use Livewire\Component;
use App\Models\Room;
use App\Models\Customer;
use App\Http\Controllers\ReservationController;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class ReservationCreate extends Component
{
    // Form data
    public $customerId = '';
    public $roomId = '';
    public $selectedRoomIds = [];
    public $checkIn = '';
    public $checkOut = '';
    public $checkInTime = '14:00';
    public $total = 0;
    public $deposit = 0;
    public $guestsCount = 0;
    public $showMultiRoomSelector = false;
    public $roomGuests = [];
    public $notes = '';

    // UI State
    public $formStep = 1;
    public $datesCompleted = false;
    public $loading = false;
    public $isChecking = false;
    public $availability = null;
    public $availabilityMessage = '';

    // Data
    public $rooms = [];
    public $roomsData = [];
    public $customers = [];
    public $identificationDocuments = [];
    public $legalOrganizations = [];
    public $tributes = [];
    public $municipalities = [];

    // Customer search
    public $customerSearchTerm = '';
    public $showCustomerDropdown = false;

    // Guest assignment
    public $assignedGuests = [];
    public $currentRoomForGuestAssignment = null;
    public $guestModalOpen = false;
    public $guestModalTab = 'search';
    public $selectedGuestForAdd = null;

    // New customer modal (for main customer)
    public $newCustomerModalOpen = false;
    public $newMainCustomer = [
        'name' => '',
        'identification' => '',
        'phone' => '',
        'email' => '',
        'address' => '',
        'requiresElectronicInvoice' => false,
        'identificationDocumentId' => '',
        'dv' => '',
        'company' => '',
        'tradeName' => '',
        'municipalityId' => '',
        'legalOrganizationId' => '',
        'tributeId' => ''
    ];
    public $creatingMainCustomer = false;
    public $newMainCustomerErrors = [];
    public $mainCustomerIdentificationMessage = '';
    public $mainCustomerIdentificationExists = false;
    public $mainCustomerRequiresDV = false;
    public $mainCustomerIsJuridicalPerson = false;

    // New customer for guest assignment
    public $newCustomer = [
        'name' => '',
        'identification' => '',
        'phone' => '',
        'email' => '',
        'address' => '',
        'requiresElectronicInvoice' => false,
        'identificationDocumentId' => '',
        'dv' => '',
        'company' => '',
        'tradeName' => '',
        'municipalityId' => '',
        'legalOrganizationId' => '',
        'tributeId' => ''
    ];
    public $creatingCustomer = false;
    public $newCustomerErrors = [];
    public $customerIdentificationMessage = '';
    public $customerIdentificationExists = false;
    public $customerRequiresDV = false;
    public $customerIsJuridicalPerson = false;

    protected $rules = [
        'customerId' => 'required|exists:customers,id',
        'checkIn' => 'required|date|after_or_equal:today',
        'checkOut' => 'required|date|after:checkIn',
        'checkInTime' => 'nullable|regex:/^([0-1]\d|2[0-3]):[0-5]\d$/',
        'total' => 'required|numeric|min:0',
        'deposit' => 'required|numeric|min:0',
        'guestsCount' => 'nullable|integer|min:0',
    ];

    protected $messages = [
        'customerId.required' => 'Debe seleccionar un cliente.',
        'customerId.exists' => 'El cliente seleccionado no existe.',
        'checkIn.required' => 'La fecha de entrada es obligatoria.',
        'checkIn.after_or_equal' => 'La fecha de entrada no puede ser anterior al día actual.',
        'checkOut.required' => 'La fecha de salida es obligatoria.',
        'checkOut.after' => 'La fecha de salida debe ser posterior a la fecha de entrada.',
        'checkInTime.regex' => 'El formato de hora debe ser HH:MM (24 horas).',
        'total.required' => 'El total es obligatorio.',
        'total.min' => 'El total debe ser mayor o igual a 0.',
        'deposit.required' => 'El abono es obligatorio.',
        'deposit.min' => 'El abono debe ser mayor o igual a 0.',
        'guestsCount.min' => 'El número de personas no puede ser negativo.',
    ];

    public function mount(
        $rooms = [],
        $roomsData = [],
        $customers = [],
        $identificationDocuments = [],
        $legalOrganizations = [],
        $tributes = [],
        $municipalities = []
    ) {
        $this->rooms = is_array($rooms) ? $rooms : [];
        $this->roomsData = is_array($roomsData) ? $roomsData : [];
        $this->customers = is_array($customers) ? $customers : [];
        $this->identificationDocuments = is_array($identificationDocuments) ? $identificationDocuments : [];
        $this->legalOrganizations = is_array($legalOrganizations) ? $legalOrganizations : [];
        $this->tributes = is_array($tributes) ? $tributes : [];
        $this->municipalities = is_array($municipalities) ? $municipalities : [];
        $this->checkIn = now()->format('Y-m-d');
        $this->checkOut = now()->addDay()->format('Y-m-d');

        // Validate initial dates
        $this->validateDates();
    }

    public function updatedCheckIn($value)
    {
        $this->clearDateErrors();
        $this->resetAvailabilityState();

        if (empty($value)) {
            $this->setDatesIncomplete();
            $this->total = 0;
            // Clear room selections when dates are incomplete
            $this->roomId = '';
            $this->selectedRoomIds = [];
            $this->roomGuests = [];
            return;
        }

        $this->validateCheckInDate($value);

        if (!empty($this->checkOut)) {
            $this->validateCheckOutAgainstCheckIn();
        }

        $this->validateDates();

        // Clear room selections if they're no longer available after date change
        $this->clearUnavailableRooms();

        $this->calculateTotal();
        $this->checkAvailabilityIfReady();
    }

    public function updatedCheckOut($value)
    {
        $this->clearDateErrors();
        $this->resetAvailabilityState();

        if (empty($value)) {
            $this->setDatesIncomplete();
            $this->total = 0;
            // Clear room selections when dates are incomplete
            $this->roomId = '';
            $this->selectedRoomIds = [];
            $this->roomGuests = [];
            return;
        }

        if (!empty($this->checkIn)) {
            $this->validateCheckOutDate($value);
        }

        $this->validateDates();

        // Clear room selections if they're no longer available after date change
        $this->clearUnavailableRooms();

        $this->calculateTotal();
        $this->checkAvailabilityIfReady();
    }

    public function updatedNewMainCustomer($value, $key)
    {
        // Recalculate DV when identification changes
        if ($key === 'identification' && $this->mainCustomerRequiresDV) {
            $this->newMainCustomer['dv'] = $this->calculateVerificationDigit($value ?? '');
        }

        // Update required fields when document type changes
        if ($key === 'identificationDocumentId') {
            $this->updateMainCustomerRequiredFields();
        }
    }

    public function updatedCheckInTime($value)
    {
        if (!empty($value) && !preg_match('/^([0-1]\d|2[0-3]):[0-5]\d$/', $value)) {
            $this->addError('checkInTime', 'Formato de hora inválido. Use formato HH:MM (24 horas).');
            $this->checkInTime = '14:00';
        }
    }

    public function updatedCustomerId($value)
    {
        // Clear search when customer is selected
        if (!empty($value)) {
            $this->customerSearchTerm = '';
            $this->showCustomerDropdown = false;
        }
        // The selectedCustomerInfo is computed automatically via getSelectedCustomerInfoProperty()
    }

    public function updatedRoomId($value)
    {
        if (empty($value)) {
            $this->total = 0;
            $this->resetAvailabilityState();
            return;
        }

        // Initialize empty roomGuests array for the selected room if not exists
        if (!isset($this->roomGuests[$value]) || !is_array($this->roomGuests[$value])) {
            $this->roomGuests[$value] = [];
        }

        $this->calculateTotal();
        $this->checkAvailabilityIfReady();
    }

    public function updatedSelectedRoomIds($value)
    {
        // Ensure we have an array (Livewire may pass null or empty string)
        if (!is_array($value)) {
            $this->selectedRoomIds = [];
            $this->calculateTotal();
            return;
        }

        // Filter and convert all values to integers for consistency
        $validIds = array_filter($value, function($id): bool {
            return !empty($id) && is_numeric($id) && $id > 0;
        });

        // Convert to integers and remove duplicates
        $this->selectedRoomIds = array_values(array_unique(array_map('intval', $validIds)));

        // Clean up roomGuests for rooms that are no longer selected
        if (is_array($this->roomGuests)) {
            foreach ($this->roomGuests as $roomId => $guests) {
                if (!in_array((int)$roomId, $this->selectedRoomIds, true)) {
                    unset($this->roomGuests[$roomId]);
                }
            }
        }

        $this->calculateTotal();
    }

    public function updatedGuestsCount($value)
    {
        // This method is kept for backward compatibility but guestsCount
        // is no longer used for price calculation in single room mode.
        // The total is now derived from roomGuests.
        $value = (int) $value;
        $this->guestsCount = $value;

        // Clear previous capacity errors
        $errorBag = $this->getErrorBag();
        $errorBag->forget('guestsCount');

        // Validate capacity only if room is selected (for UI feedback)
        if (!$this->showMultiRoomSelector && !empty($this->roomId)) {
            $selectedRoom = $this->selectedRoom;
            if ($selectedRoom && is_array($selectedRoom)) {
                $capacity = $selectedRoom['capacity'] ?? 0;

                if ($value > $capacity) {
                    $this->addError('guestsCount',
                        "Esta habitación admite máximo {$capacity} persona" . ($capacity > 1 ? 's' : '') . ".");
                }
            }
        }

        // Calculate total (will return 0 if capacity is exceeded or no guests assigned)
        $this->calculateTotal();
    }

    public function updatedShowMultiRoomSelector($value)
    {
        if ($value) {
            // Switching to multi-room mode: clear single room selection
            $this->roomId = '';
        } else {
            // Switching to single room mode: clear multi-room selections
            $this->selectedRoomIds = [];
            $this->roomGuests = [];
        }
        $this->calculateTotal();
    }

    public function toggleMultiRoomMode()
    {
        $this->showMultiRoomSelector = !$this->showMultiRoomSelector;

        if ($this->showMultiRoomSelector) {
            // Switching to multi-room mode: clear single room selection
            $this->roomId = '';
            // Initialize empty array if not already set
            if (!is_array($this->selectedRoomIds)) {
                $this->selectedRoomIds = [];
            }
        } else {
            // Switching to single room mode: clear multi-room selections
            $this->selectedRoomIds = [];
            $this->roomGuests = [];
        }

        $this->calculateTotal();
    }

    public function validateDates()
    {
        if (empty($this->checkIn) || empty($this->checkOut)) {
            $this->setDatesIncomplete();
            return;
        }

        // Sequential validation: parse first, then validate business rules
        try {
            $checkIn = Carbon::parse($this->checkIn)->startOfDay();
            $checkOut = Carbon::parse($this->checkOut)->startOfDay();
            $today = Carbon::today()->startOfDay();

            // Validation 1: Check-in must not be in the past (allows today)
            if ($checkIn->lt($today)) {
                $this->addError('checkIn', 'La fecha de entrada no puede ser anterior al día actual.');
                $this->setDatesIncomplete();
                return;
            }

            // Validation 2: Check-out must be after check-in (CRITICAL for business logic)
            if ($checkOut->lte($checkIn)) {
                $this->addError('checkOut', 'La fecha de salida debe ser posterior a la fecha de entrada.');
                $this->setDatesIncomplete();
                return;
            }

            // All date validations passed - mark as completed
            $this->datesCompleted = true;
            $this->formStep = 2;
        } catch (\Exception $e) {
            \Log::error('Error validating dates: ' . $e->getMessage(), [
                'checkIn' => $this->checkIn,
                'checkOut' => $this->checkOut,
                'trace' => $e->getTraceAsString()
            ]);
            $this->addError('checkIn', 'Fecha inválida. Por favor, selecciona fechas válidas.');
            $this->setDatesIncomplete();
        }
    }

    public function calculateTotal()
    {
        // Guard clause 1: dates must be present
        if (empty($this->checkIn) || empty($this->checkOut)) {
            $this->total = 0;
            return;
        }

        // Guard clause 2: dates must be valid (no errors)
        if ($this->hasDateValidationErrors()) {
            $this->total = 0;
            return;
        }

        try {
            $checkIn = Carbon::parse($this->checkIn);
            $checkOut = Carbon::parse($this->checkOut);
            $nights = $checkIn->diffInDays($checkOut);

            // Guard clause 3: nights must be > 0
            if ($nights <= 0) {
                $this->total = 0;
                return;
            }

            if ($this->showMultiRoomSelector) {
                if (!is_array($this->selectedRoomIds) || empty($this->selectedRoomIds)) {
                    $this->total = 0;
                    return;
                }

                $total = 0;
                foreach ($this->selectedRoomIds as $roomId) {
                    $room = $this->getRoomById($roomId);
                    if (!$room || !is_array($room)) {
                        continue;
                    }

                    $guestCount = $this->getRoomGuestsCount($roomId);
                    $capacity = $room['capacity'] ?? 0;

                    // Guard clause 4: respect room capacity
                    if ($guestCount > $capacity) {
                        $this->total = 0;
                        return;
                    }

                    // Guard clause 5: only calculate price if guests are assigned
                    if ($guestCount <= 0) {
                        continue;
                    }

                    $pricePerNight = $this->calculatePriceForRoom($room, $guestCount);
                    $total += $pricePerNight * $nights;
                }
                $this->total = $total;
            } else {
                // Single room mode: use roomId
                if (empty($this->roomId)) {
                    $this->total = 0;
                    return;
                }

                $selectedRoom = $this->selectedRoom;
                if (!$selectedRoom || !is_array($selectedRoom)) {
                    $this->total = 0;
                    return;
                }

                $guestCount = $this->getRoomGuestsCount($this->roomId);
                $capacity = $selectedRoom['capacity'] ?? 0;

                // Guard clause 4: respect room capacity (business rule)
                if ($guestCount > $capacity) {
                    $this->total = 0;
                    return;
                }

                // Guard clause 5: only calculate price if guests are assigned
                if ($guestCount <= 0) {
                    $this->total = 0;
                    return;
                }

                $pricePerNight = $this->calculatePriceForRoom($selectedRoom, $guestCount);
                $this->total = $pricePerNight * $nights;
            }
        } catch (\Exception $e) {
            \Log::error('Error calculating total: ' . $e->getMessage(), [
                'checkIn' => $this->checkIn,
                'checkOut' => $this->checkOut
            ]);
            $this->total = 0;
        }
    }

    public function checkAvailability()
    {
        if (!$this->canCheckAvailability()) {
            $this->resetAvailabilityState();
            return;
        }

        if (empty($this->roomId) || empty($this->checkIn) || empty($this->checkOut)) {
            $this->resetAvailabilityState();
            return;
        }

        $this->isChecking = true;
        $this->availability = null;

        try {
            $url = route('api.check-availability');
            if (empty($url)) {
                throw new \Exception('Route api.check-availability not found');
            }

            $response = \Http::timeout(5)->get($url, [
                'room_id' => $this->roomId,
                'check_in_date' => $this->checkIn,
                'check_out_date' => $this->checkOut,
            ]);

            if ($response->successful()) {
                $data = $response->json();
                $this->availability = $data['available'] ?? false;
                $this->availabilityMessage = $this->availability
                    ? 'HABITACIÓN DISPONIBLE'
                    : 'NO DISPONIBLE PARA ESTAS FECHAS';
            } else {
                $this->availability = false;
                $this->availabilityMessage = 'Error al verificar disponibilidad';
            }
        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            \Log::error('Connection error checking availability: ' . $e->getMessage());
            $this->availability = false;
            $this->availabilityMessage = 'Error de conexión al verificar disponibilidad';
        } catch (\Exception $e) {
            \Log::error('Error checking availability: ' . $e->getMessage(), [
                'roomId' => $this->roomId,
                'checkIn' => $this->checkIn,
                'checkOut' => $this->checkOut,
                'trace' => $e->getTraceAsString()
            ]);
            $this->availability = false;
            $this->availabilityMessage = 'Error al verificar disponibilidad';
        } finally {
            $this->isChecking = false;
        }
    }

    public function getNightsProperty()
    {
        if (empty($this->checkIn) || empty($this->checkOut)) {
            return 0;
        }

        try {
            $checkIn = Carbon::parse($this->checkIn);
            $checkOut = Carbon::parse($this->checkOut);
            $diff = $checkOut->diffInDays($checkIn);

            return $diff > 0 ? $diff : 0;
        } catch (\Exception $e) {
            \Log::error('Error calculating nights: ' . $e->getMessage());
            return 0;
        }
    }

    public function getBalanceProperty()
    {
        return $this->total - $this->deposit;
    }

    public function getCanProceedProperty()
    {
        if (empty($this->checkIn) || empty($this->checkOut)) {
            return false;
        }

        try {
            $checkIn = Carbon::parse($this->checkIn);
            $checkOut = Carbon::parse($this->checkOut);
            $today = Carbon::today();

            return $checkIn->isAfterOrEqualTo($today)
                && $checkOut->isAfter($checkIn);
        } catch (\Exception $e) {
            \Log::error('Error in getCanProceedProperty: ' . $e->getMessage(), [
                'checkIn' => $this->checkIn,
                'checkOut' => $this->checkOut
            ]);
            return false;
        }
    }

    public function getShowGuestAssignmentPanelProperty()
    {
        if ($this->showMultiRoomSelector) {
            if (!is_array($this->selectedRoomIds) || empty($this->selectedRoomIds)) {
                return false;
            }

            foreach ($this->selectedRoomIds as $roomId) {
                $room = $this->getRoomById($roomId);
                if (!$room || !is_array($room)) {
                    continue;
                }

                $assignedCount = $this->getRoomGuestsCount($roomId);
                $capacity = $room['capacity'] ?? 0;
                if ($assignedCount >= $capacity) {
                    return false;
                }
            }
            return true;
        }

        // Single room mode: check if room is selected and has available capacity
        if (empty($this->roomId)) {
            return false;
        }

        $selectedRoom = $this->selectedRoom;
        if (!$selectedRoom || !is_array($selectedRoom)) {
            return false;
        }

        $assignedCount = $this->getRoomGuestsCount($this->roomId);
        $capacity = $selectedRoom['capacity'] ?? 0;

        return $assignedCount < $capacity;
    }

    public function getExceedsCapacityProperty()
    {
        if ($this->showMultiRoomSelector) {
            if (!is_array($this->selectedRoomIds) || empty($this->selectedRoomIds)) {
                return false;
            }

            foreach ($this->selectedRoomIds as $roomId) {
                $room = $this->getRoomById($roomId);
                if (!$room || !is_array($room)) {
                    continue;
                }

                $assignedCount = $this->getRoomGuestsCount($roomId);
                $capacity = $room['capacity'] ?? 0;
                if ($assignedCount > $capacity) {
                    return true;
                }
            }
            return false;
        }

        // Single room mode: check if assigned guests exceed capacity
        if (empty($this->roomId)) {
            return false;
        }

        $selectedRoom = $this->selectedRoom;
        if (!$selectedRoom || !is_array($selectedRoom)) {
            return false;
        }

        $assignedCount = $this->getRoomGuestsCount($this->roomId);
        $capacity = $selectedRoom['capacity'] ?? 0;

        return $assignedCount > $capacity;
    }

    public function getIsValidProperty()
    {
        try {
            // Basic required fields
            if (empty($this->customerId) || empty($this->checkIn) || empty($this->checkOut)) {
                return false;
            }

            // Dates must be valid
            if (!$this->canProceed) {
                return false;
            }

            // Room selection
            if ($this->showMultiRoomSelector) {
                if (!is_array($this->selectedRoomIds) || empty($this->selectedRoomIds)) {
                    return false;
                }
            } else {
                if (empty($this->roomId)) {
                    return false;
                }

                // Business rule: capacity must be respected
                $selectedRoom = $this->selectedRoom;
                if ($selectedRoom && is_array($selectedRoom)) {
                    $assignedCount = $this->getRoomGuestsCount($this->roomId);
                    $capacity = $selectedRoom['capacity'] ?? 0;
                    if ($assignedCount > $capacity) {
                        return false;
                    }
                    // At least one guest must be assigned
                    if ($assignedCount <= 0) {
                        return false;
                    }
                }
            }

            // Financial validation
            if ($this->total <= 0 || $this->deposit < 0) {
                return false;
            }

            if ($this->balance < 0) {
                return false;
            }

            return true;
        } catch (\Exception $e) {
            \Log::error('Error in getIsValidProperty: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            return false;
        }
    }

    public function getRoomById($roomId)
    {
        if (empty($roomId)) {
            return null;
        }

        // Try roomsData first (has detailed pricing info with 'number' and 'capacity')
        if (!empty($this->roomsData) && is_array($this->roomsData)) {
            foreach ($this->roomsData as $room) {
                if (!is_array($room)) {
                    continue;
                }
                $roomIdValue = $room['id'] ?? null;
                if ($roomIdValue !== null && (int)$roomIdValue === (int)$roomId) {
                    return $room;
                }
            }
        }

        // Fallback to rooms array (has 'room_number' and 'max_capacity')
        // Convert to roomsData format for consistency
        if (!empty($this->rooms) && is_array($this->rooms)) {
            foreach ($this->rooms as $room) {
                if (!is_array($room)) {
                    continue;
                }
                $roomIdValue = $room['id'] ?? null;
                if ($roomIdValue !== null && (int)$roomIdValue === (int)$roomId) {
                    // Convert to roomsData format for consistency
                    return [
                        'id' => $room['id'] ?? null,
                        'number' => $room['room_number'] ?? null,
                        'capacity' => $room['max_capacity'] ?? 2,
                        'room_number' => $room['room_number'] ?? null, // Keep for compatibility
                        'max_capacity' => $room['max_capacity'] ?? 2, // Keep for compatibility
                    ];
                }
            }
        }

        return null;
    }

    public function getRoomGuestsCount($roomId): int
    {
        if (empty($roomId)) {
            return 0;
        }

        $assignedGuests = $this->roomGuests[$roomId] ?? [];

        if (!is_array($assignedGuests) || empty($assignedGuests)) {
            return 0;
        }

        $validGuests = array_filter($assignedGuests, function ($guest): bool {
            if (!is_array($guest)) {
                return false;
            }
            $guestId = $guest['id'] ?? null;
            return !empty($guestId) && is_numeric($guestId) && $guestId > 0;
        });

        return count($validGuests);
    }

    public function calculatePriceForRoom(array $room, int $guestsCount): float
    {
        if (!is_array($room) || empty($room)) {
            return 0.0;
        }

        // Defensive: do not calculate price if no guests assigned
        if ($guestsCount <= 0) {
            return 0.0;
        }

        $occupancyPrices = $room['occupancyPrices'] ?? [];
        $capacity = $room['capacity'] ?? 2;
        $actualGuests = min($guestsCount, $capacity);
        $exceededGuests = max(0, $guestsCount - $capacity);

        // Get base price for 1 person
        $basePrice = 0;
        if (isset($occupancyPrices[1]) && $occupancyPrices[1] > 0) {
            $basePrice = (float) $occupancyPrices[1];
        } elseif (isset($room['price1Person']) && $room['price1Person'] > 0) {
            $basePrice = (float) $room['price1Person'];
        } else {
            $basePrice = (float) ($room['price'] ?? 0);
        }

        // Add price for additional persons within capacity
        $additionalPersonsWithinCapacity = max(0, $actualGuests - 1);
        if ($additionalPersonsWithinCapacity > 0 && isset($room['priceAdditionalPerson'])) {
            $additionalPrice = (float) $room['priceAdditionalPerson'];
            $basePrice += $additionalPrice * $additionalPersonsWithinCapacity;
        }

        // Add price for exceeded guests
        if ($exceededGuests > 0 && isset($room['priceAdditionalPerson'])) {
            $additionalPrice = (float) $room['priceAdditionalPerson'];
            $basePrice += $additionalPrice * $exceededGuests;
        }

        return (float) $basePrice;
    }

    public function formatCurrency(float $value): string
    {
        return number_format($value, 0, ',', '.');
    }

    public function getPriceForGuestsProperty(): float
    {
        try {
            if (empty($this->roomId)) {
                return 0.0;
            }

            $selectedRoom = $this->selectedRoom;
            if (!$selectedRoom || !is_array($selectedRoom)) {
                return 0.0;
            }

            $guestCount = $this->getRoomGuestsCount($this->roomId);

            // Defensive: return 0 if no guests assigned
            if ($guestCount <= 0) {
                return 0.0;
            }

            return $this->calculatePriceForRoom($selectedRoom, $guestCount);
        } catch (\Exception $e) {
            \Log::error('Error calculating price for guests: ' . $e->getMessage());
            return 0.0;
        }
    }

    public function getRoomPriceForGuestsProperty(): array
    {
        if (!is_array($this->selectedRoomIds) || empty($this->selectedRoomIds)) {
            return [];
        }

        $prices = [];
        foreach ($this->selectedRoomIds as $roomId) {
            $room = $this->getRoomById($roomId);
            if (!$room || !is_array($room)) {
                continue;
            }
            $guestCount = $this->getRoomGuestsCount($roomId);
            // Only calculate price if guests are assigned
            if ($guestCount > 0) {
                $prices[$roomId] = $this->calculatePriceForRoom($room, $guestCount);
            } else {
                $prices[$roomId] = 0;
            }
        }
        return $prices;
    }

    public function getSelectedRoomProperty()
    {
        if (empty($this->roomId)) {
            return null;
        }

        if (empty($this->roomsData) || !is_array($this->roomsData)) {
            return null;
        }

        try {
            $room = $this->getRoomById($this->roomId);
            if (!$room || !is_array($room)) {
                return null;
            }
            return $room;
        } catch (\Exception $e) {
            \Log::error('Error getting selected room: ' . $e->getMessage(), [
                'roomId' => $this->roomId,
                'roomsData' => is_array($this->roomsData) ? 'array(' . count($this->roomsData) . ')' : gettype($this->roomsData),
                'exception' => $e
            ]);
            return null;
        }
    }

    public function getSelectedCustomerInfoProperty()
    {
        if (empty($this->customerId)) {
            return null;
        }

        try {
            if (empty($this->customers) || !is_array($this->customers)) {
                return null;
            }

            $customer = collect($this->customers)->first(function ($customer) {
                $customerId = $customer['id'] ?? null;
                return (string) $customerId === (string) $this->customerId;
            });

            if (!$customer || !is_array($customer)) {
                return null;
            }

            $phone = $customer['phone'] ?? 'S/N';
            $identification = 'S/N';

            if (isset($customer['taxProfile']) && is_array($customer['taxProfile'])) {
                $identification = $customer['taxProfile']['identification'] ?? 'S/N';
            }

            return [
                'id' => $identification,
                'phone' => $phone
            ];
        } catch (\Exception $e) {
            \Log::error('Error getting selected customer info: ' . $e->getMessage(), [
                'customerId' => $this->customerId,
                'exception' => $e
            ]);
            return null;
        }
    }

    public function getAutoCalculatedTotalProperty(): float
    {
        try {
            if ($this->showMultiRoomSelector) {
                if (!is_array($this->selectedRoomIds) || empty($this->selectedRoomIds)) {
                    return 0;
                }

                $nights = $this->nights;
                if ($nights <= 0) {
                    return 0;
                }

                $total = 0;
                foreach ($this->selectedRoomIds as $roomId) {
                    $guestCount = $this->getRoomGuestsCount($roomId);
                    // Only calculate price if guests are assigned
                    if ($guestCount <= 0) {
                        continue;
                    }
                    $room = $this->getRoomById($roomId);
                    if (!$room || !is_array($room)) {
                        continue;
                    }
                    $pricePerNight = $this->calculatePriceForRoom($room, $guestCount);
                    $total += $pricePerNight * $nights;
                }
                return $total;
            }

            $selectedRoom = $this->selectedRoom;
            if (!$selectedRoom || !is_array($selectedRoom)) {
                return 0;
            }

            $nights = $this->nights;
            if ($nights <= 0) {
                return 0;
            }

            $pricePerNight = $this->getPriceForGuests;
            return $pricePerNight * $nights;
        } catch (\Exception $e) {
            \Log::error('Error calculating auto total: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            return 0;
        }
    }

    public function calculateTotalGuestsCount(): int
    {
        if (!$this->showMultiRoomSelector) {
            // Single room mode: derive from roomGuests using roomId
            if (empty($this->roomId)) {
                return 0;
            }
            return $this->getRoomGuestsCount($this->roomId);
        }

        // Multiple rooms mode: sum all room guests
        if (!is_array($this->selectedRoomIds) || empty($this->selectedRoomIds)) {
            return 0;
        }

        $total = 0;
        foreach ($this->selectedRoomIds as $roomId) {
            $total += $this->getRoomGuestsCount($roomId);
        }
        return $total;
    }

    public function toggleSelectedRoomIds($roomId): void
    {
        $roomId = (int) $roomId;

        if (!is_array($this->selectedRoomIds)) {
            $this->selectedRoomIds = [];
        }

        $currentIds = array_map('intval', $this->selectedRoomIds);
        $index = array_search($roomId, $currentIds, true);

        if ($index !== false) {
            // Remove if already selected
            unset($currentIds[$index]);
            $this->selectedRoomIds = array_values($currentIds);

            // Clean up roomGuests for removed room
            if (isset($this->roomGuests[$roomId])) {
                unset($this->roomGuests[$roomId]);
            }
        } else {
            // Add if not selected
            $currentIds[] = $roomId;
            $this->selectedRoomIds = array_values(array_unique($currentIds));
        }

        $this->calculateTotal();
    }

    public function removeRoom(int $roomId): void
    {
        $this->selectedRoomIds = array_values(array_filter($this->selectedRoomIds, function ($id) use ($roomId): bool {
            return (int) $id !== $roomId;
        }));
        if (isset($this->roomGuests[$roomId])) {
            unset($this->roomGuests[$roomId]);
        }
        $this->calculateTotal();
    }

    public function openGuestModal(?int $roomId = null): void
    {
        $this->currentRoomForGuestAssignment = $roomId;
        $this->guestModalOpen = true;
        $this->guestModalTab = 'search';
        $this->selectedGuestForAdd = null;
    }

    public function addGuest($guestData): void
    {
        if (empty($guestData) || !is_array($guestData)) {
            return;
        }

        $guestId = $guestData['id'] ?? null;
        if (empty($guestId)) {
            return;
        }

        $roomId = $this->currentRoomForGuestAssignment;

        if ($roomId !== null) {
            // Multiple rooms mode: add to specific room
            $room = $this->getRoomById($roomId);
            if (!$room || !is_array($room)) {
                return;
            }

            // Check if guest is already assigned to this room
            $roomGuests = $this->getRoomGuests($roomId);
            $alreadyAssigned = false;
            foreach ($roomGuests as $guest) {
                if (isset($guest['id']) && (int)$guest['id'] === (int)$guestId) {
                    $alreadyAssigned = true;
                    break;
                }
            }

            if ($alreadyAssigned) {
                $this->addError('guestAssignment', 'Este cliente ya está asignado a esta habitación.');
                return;
            }

            // Check capacity
            $currentCount = $this->getRoomGuestsCount($roomId);
            $capacity = $room['capacity'] ?? 0;
            if ($currentCount >= $capacity) {
                $this->addError('guestAssignment', "No se pueden asignar más huéspedes. La habitación ha alcanzado su capacidad máxima de {$capacity} personas.");
                return;
            }

            // Initialize array if needed
            if (!isset($this->roomGuests[$roomId]) || !is_array($this->roomGuests[$roomId])) {
                $this->roomGuests[$roomId] = [];
            }

            // Add guest to room
            $this->roomGuests[$roomId][] = $guestData;

            // Close modal
            $this->guestModalOpen = false;
            $this->selectedGuestForAdd = null;
        } else {
            // Single room mode: add to roomGuests using roomId
            if (empty($this->roomId)) {
                return;
            }

            $selectedRoom = $this->selectedRoom;
            if (!$selectedRoom || !is_array($selectedRoom)) {
                return;
            }

            // Initialize array if needed
            if (!isset($this->roomGuests[$this->roomId]) || !is_array($this->roomGuests[$this->roomId])) {
                $this->roomGuests[$this->roomId] = [];
            }

            // Check if already assigned
            $roomGuests = $this->getRoomGuests($this->roomId);
            $alreadyAssigned = false;
            foreach ($roomGuests as $guest) {
                if (isset($guest['id']) && (int)$guest['id'] === (int)$guestId) {
                    $alreadyAssigned = true;
                    break;
                }
            }

            if ($alreadyAssigned) {
                $this->addError('guestAssignment', 'Este cliente ya está asignado a la habitación.');
                return;
            }

            // Check capacity
            $currentCount = $this->getRoomGuestsCount($this->roomId);
            $capacity = $selectedRoom['capacity'] ?? 0;
            if ($currentCount >= $capacity) {
                $this->addError('guestAssignment', "No se pueden asignar más huéspedes. La habitación ha alcanzado su capacidad máxima de {$capacity} personas.");
                return;
            }

            // Add guest to roomGuests
            $this->roomGuests[$this->roomId][] = $guestData;

            // Close modal
            $this->guestModalOpen = false;
            $this->selectedGuestForAdd = null;
        }

        $this->calculateTotal();
    }

    public function confirmAddGuest(): void
    {
        if (empty($this->selectedGuestForAdd) || !is_array($this->selectedGuestForAdd)) {
            return;
        }

        $this->addGuest($this->selectedGuestForAdd);
    }

    public function removeGuest(?int $roomId, int $index): void
    {
        if ($roomId !== null) {
            // Multiple rooms mode: remove from specific room
            if (isset($this->roomGuests[$roomId][$index])) {
                unset($this->roomGuests[$roomId][$index]);
                $this->roomGuests[$roomId] = array_values($this->roomGuests[$roomId]);
            }
        } else {
            // Single room mode: remove from roomGuests using roomId
            if (empty($this->roomId)) {
                return;
            }
            if (isset($this->roomGuests[$this->roomId][$index])) {
                unset($this->roomGuests[$this->roomId][$index]);
                $this->roomGuests[$this->roomId] = array_values($this->roomGuests[$this->roomId]);
            }
        }
        $this->calculateTotal();
    }

    public function canAssignMoreGuestsToRoom(int $roomId): bool
    {
        $room = $this->getRoomById($roomId);
        if (!$room) {
            return false;
        }
        $currentCount = $this->getRoomGuestsCount($roomId);
        return $currentCount < ($room['capacity'] ?? 0);
    }

    public function getRoomGuests(int $roomId): array
    {
        return $this->roomGuests[$roomId] ?? [];
    }

    public function getAssignedGuestsProperty(): array
    {
        // In single room mode, derive from roomGuests for compatibility with view
        if (!$this->showMultiRoomSelector && !empty($this->roomId)) {
            return $this->getRoomGuests($this->roomId);
        }
        // In multi-room mode, return empty array (not used)
        return [];
    }

    public function getAvailableSlotsProperty(): int
    {
        if (empty($this->roomId)) {
            return 0;
        }

        $selectedRoom = $this->selectedRoom;
        if (!$selectedRoom || !is_array($selectedRoom)) {
            return 0;
        }

        $assignedCount = $this->getRoomGuestsCount($this->roomId);
        $capacity = $selectedRoom['capacity'] ?? 0;

        return max(0, $capacity - $assignedCount);
    }

    public function getCanAssignMoreGuestsProperty(): bool
    {
        if (empty($this->roomId)) {
            return false;
        }

        $selectedRoom = $this->selectedRoom;
        if (!$selectedRoom || !is_array($selectedRoom)) {
            return false;
        }

        $assignedCount = $this->getRoomGuestsCount($this->roomId);
        $capacity = $selectedRoom['capacity'] ?? 0;

        return $assignedCount < $capacity;
    }

    private function clearDateErrors(): void
    {
        $errorBag = $this->getErrorBag();
        $errorBag->forget('checkIn');
        $errorBag->forget('checkOut');
    }

    private function resetAvailabilityState(): void
    {
        $this->availability = null;
        $this->availabilityMessage = '';
        $this->isChecking = false;
    }

    private function setDatesIncomplete(): void
    {
        $this->datesCompleted = false;
        $this->formStep = 1;
    }

    private function validateCheckInDate(string $value): void
    {
        try {
            $checkIn = Carbon::parse($value)->startOfDay();
            $today = Carbon::today()->startOfDay();

            if ($checkIn->lt($today)) {
                $this->addError('checkIn', 'La fecha de entrada no puede ser anterior al día actual.');
            }
        } catch (\Exception $e) {
            \Log::error('Error parsing check-in date: ' . $e->getMessage(), ['value' => $value]);
            if (strlen($value) > 0) {
                $this->addError('checkIn', 'Fecha inválida. Por favor, selecciona una fecha válida.');
            }
        }
    }

    private function validateCheckOutDate(string $value): void
    {
        try {
            if (empty($this->checkIn)) {
                return;
            }

            $checkIn = Carbon::parse($this->checkIn)->startOfDay();
            $checkOut = Carbon::parse($value)->startOfDay();

            if ($checkOut->lte($checkIn)) {
                $this->addError('checkOut', 'La fecha de salida debe ser posterior a la fecha de entrada.');
            }
        } catch (\Exception $e) {
            \Log::error('Error parsing check-out date: ' . $e->getMessage(), [
                'value' => $value,
                'checkIn' => $this->checkIn
            ]);
            if (strlen($value) > 0) {
                $this->addError('checkOut', 'Fecha inválida. Por favor, selecciona una fecha válida.');
            }
        }
    }

    private function validateCheckOutAgainstCheckIn(): void
    {
        if (empty($this->checkOut) || empty($this->checkIn)) {
            return;
        }

        try {
            $checkIn = Carbon::parse($this->checkIn)->startOfDay();
            $checkOut = Carbon::parse($this->checkOut)->startOfDay();

            if ($checkOut->lte($checkIn)) {
                $this->addError('checkOut', 'La fecha de salida debe ser posterior a la fecha de entrada.');
            }
        } catch (\Exception $e) {
            \Log::error('Error validating check-out against check-in: ' . $e->getMessage(), [
                'checkIn' => $this->checkIn,
                'checkOut' => $this->checkOut
            ]);
        }
    }

    private function hasDateValidationErrors(): bool
    {
        $errors = $this->getErrorBag();
        return $errors->has('checkIn') || $errors->has('checkOut');
    }

    private function canCheckAvailability(): bool
    {
        if ($this->showMultiRoomSelector) {
            return false;
        }

        if (empty($this->roomId) || empty($this->checkIn) || empty($this->checkOut)) {
            return false;
        }

        if (!$this->datesCompleted) {
            return false;
        }

        if ($this->hasDateValidationErrors()) {
            return false;
        }

        return true;
    }

    private function checkAvailabilityIfReady(): void
    {
        // Guard clause 1: dates must be present
        if (empty($this->checkIn) || empty($this->checkOut)) {
            $this->resetAvailabilityState();
            return;
        }

        // Guard clause 2: dates must be valid (no parsing or business rule errors)
        if ($this->hasDateValidationErrors()) {
            $this->resetAvailabilityState();
            return;
        }

        // Guard clause 3: dates must be marked as completed (passed all validations)
        if (!$this->datesCompleted) {
            $this->resetAvailabilityState();
            return;
        }

        // Guard clause 4: verify checkOut > checkIn before checking availability
        try {
            $checkIn = Carbon::parse($this->checkIn)->startOfDay();
            $checkOut = Carbon::parse($this->checkOut)->startOfDay();

            if ($checkOut->lte($checkIn)) {
                $this->resetAvailabilityState();
                return;
            }
        } catch (\Exception $e) {
            $this->resetAvailabilityState();
            return;
        }

        // All guard clauses passed - check availability
        if ($this->canCheckAvailability()) {
            $this->checkAvailability();
        } else {
            $this->resetAvailabilityState();
        }
    }

    public function getAvailableRoomsProperty(): array
    {
        // If dates are not set or not valid, return empty array (no rooms available)
        if (empty($this->checkIn) || empty($this->checkOut)) {
            return [];
        }

        // If dates have validation errors, return empty array
        if ($this->hasDateValidationErrors()) {
            return [];
        }

        // Only filter if dates are completed and valid
        if (!$this->datesCompleted) {
            return [];
        }

        try {
            $checkIn = Carbon::parse($this->checkIn)->startOfDay();
            $checkOut = Carbon::parse($this->checkOut)->startOfDay();

            // Validate date range
            if ($checkOut->lte($checkIn)) {
                return [];
            }

            $availableRooms = [];
            $allRooms = $this->rooms ?? [];

            foreach ($allRooms as $room) {
                if (!is_array($room) || empty($room['id'])) {
                    continue;
                }

                $roomId = (int) $room['id'];

                // Check if room is available using the same logic as ReservationController
                if ($this->isRoomAvailableForDates($roomId, $checkIn, $checkOut)) {
                    $availableRooms[] = $room;
                }
            }

            return $availableRooms;
        } catch (\Exception $e) {
            \Log::error('Error filtering available rooms: ' . $e->getMessage(), [
                'checkIn' => $this->checkIn,
                'checkOut' => $this->checkOut,
                'trace' => $e->getTraceAsString()
            ]);
            return [];
        }
    }

    private function clearUnavailableRooms(): void
    {
        if (!$this->datesCompleted || $this->hasDateValidationErrors()) {
            return;
        }

        if (empty($this->checkIn) || empty($this->checkOut)) {
            return;
        }

        try {
            $checkIn = Carbon::parse($this->checkIn)->startOfDay();
            $checkOut = Carbon::parse($this->checkOut)->startOfDay();

            if ($checkOut->lte($checkIn)) {
                return;
            }

            $availableRooms = $this->availableRooms;
            $availableRoomIds = array_map(function($room) {
                return (int) ($room['id'] ?? 0);
            }, $availableRooms);

            // Clear single room selection if not available
            if (!empty($this->roomId)) {
                $roomId = (int) $this->roomId;
                if (!in_array($roomId, $availableRoomIds, true)) {
                    $this->roomId = '';
                    if (isset($this->roomGuests[$roomId])) {
                        unset($this->roomGuests[$roomId]);
                    }
                }
            }

            // Clear multiple room selections if not available
            if (is_array($this->selectedRoomIds) && !empty($this->selectedRoomIds)) {
                $validRoomIds = [];
                foreach ($this->selectedRoomIds as $roomId) {
                    $roomId = (int) $roomId;
                    if (in_array($roomId, $availableRoomIds, true)) {
                        $validRoomIds[] = $roomId;
                    } else {
                        // Remove guests for unavailable room
                        if (isset($this->roomGuests[$roomId])) {
                            unset($this->roomGuests[$roomId]);
                        }
                    }
                }
                $this->selectedRoomIds = $validRoomIds;
            }
        } catch (\Exception $e) {
            \Log::error('Error clearing unavailable rooms: ' . $e->getMessage());
        }
    }

    private function isRoomAvailableForDates(int $roomId, Carbon $checkIn, Carbon $checkOut): bool
    {
        // Check in main reservations table (single room reservations)
        $existsInReservations = \App\Models\Reservation::where('room_id', $roomId)
            ->where(function ($query) use ($checkIn, $checkOut) {
                $query->where('check_in_date', '<', $checkOut)
                      ->where('check_out_date', '>', $checkIn);
            })
            ->exists();

        if ($existsInReservations) {
            return false;
        }

        // Check in reservation_rooms table (multi-room reservations)
        $existsInPivot = \Illuminate\Support\Facades\DB::table('reservation_rooms')
            ->join('reservations', 'reservation_rooms.reservation_id', '=', 'reservations.id')
            ->where('reservation_rooms.room_id', $roomId)
            ->where(function ($query) use ($checkIn, $checkOut) {
                $query->where('reservations.check_in_date', '<', $checkOut)
                      ->where('reservations.check_out_date', '>', $checkIn);
            })
            ->exists();

        return !$existsInPivot;
    }

    public function updateMainCustomerRequiredFields(): void
    {
        $documentId = $this->newMainCustomer['identificationDocumentId'] ?? '';

        if (empty($documentId)) {
            $this->mainCustomerRequiresDV = false;
            $this->mainCustomerIsJuridicalPerson = false;
            $this->newMainCustomer['dv'] = '';
            return;
        }

        // Find document in identificationDocuments array
        $document = null;
        if (is_array($this->identificationDocuments)) {
            foreach ($this->identificationDocuments as $doc) {
                if (isset($doc['id']) && (string)$doc['id'] === (string)$documentId) {
                    $document = $doc;
                    break;
                }
            }
        }

        if ($document) {
            $this->mainCustomerRequiresDV = (bool)($document['requires_dv'] ?? false);
            $this->mainCustomerIsJuridicalPerson = in_array($document['code'] ?? '', ['NI', 'NIT'], true);

            // Calculate DV if required
            if ($this->mainCustomerRequiresDV && !empty($this->newMainCustomer['identification'])) {
                $this->newMainCustomer['dv'] = $this->calculateVerificationDigit($this->newMainCustomer['identification']);
            } else {
                $this->newMainCustomer['dv'] = '';
            }
        } else {
            $this->mainCustomerRequiresDV = false;
            $this->mainCustomerIsJuridicalPerson = false;
            $this->newMainCustomer['dv'] = '';
        }
    }

    private function calculateVerificationDigit(string $nit): string
    {
        $nit = preg_replace('/\D/', '', $nit);
        $weights = [71, 67, 59, 53, 47, 43, 41, 37, 29, 23, 19, 17, 13, 7, 3];
        $sum = 0;
        $nitLength = strlen($nit);

        for ($i = 0; $i < $nitLength; $i++) {
            $sum += (int)$nit[$nitLength - 1 - $i] * $weights[$i];
        }

        $remainder = $sum % 11;
        if ($remainder < 2) {
            return (string)$remainder;
        }

        return (string)(11 - $remainder);
    }

    public function checkMainCustomerIdentification(): void
    {
        $identification = $this->newMainCustomer['identification'] ?? '';

        if (empty($identification)) {
            $this->mainCustomerIdentificationMessage = '';
            $this->mainCustomerIdentificationExists = false;
            return;
        }

        // Check if identification already exists
        $exists = Customer::withoutGlobalScopes()
            ->whereHas('taxProfile', function ($query) use ($identification) {
                $query->where('identification', $identification);
            })
            ->exists();

        if ($exists) {
            $this->mainCustomerIdentificationExists = true;
            $this->mainCustomerIdentificationMessage = 'Esta identificación ya está registrada.';
        } else {
            $this->mainCustomerIdentificationExists = false;
            $this->mainCustomerIdentificationMessage = 'Identificación disponible.';
        }

        // Recalculate DV if required
        if ($this->mainCustomerRequiresDV && !empty($identification)) {
            $this->newMainCustomer['dv'] = $this->calculateVerificationDigit($identification);
        }
    }

    public function createMainCustomer(): void
    {
        $requiresElectronicInvoice = $this->newMainCustomer['requiresElectronicInvoice'] ?? false;

        $rules = [
            'newMainCustomer.name' => 'required|string|max:255',
            'newMainCustomer.identification' => 'required|string|max:10',
            'newMainCustomer.phone' => 'required|string|max:20',
            'newMainCustomer.email' => 'nullable|email|max:255',
            'newMainCustomer.address' => 'nullable|string|max:500',
        ];

        $messages = [
            'newMainCustomer.name.required' => 'El nombre es obligatorio.',
            'newMainCustomer.name.max' => 'El nombre no puede exceder 255 caracteres.',
            'newMainCustomer.identification.required' => 'La identificación es obligatoria.',
            'newMainCustomer.identification.max' => 'La identificación no puede exceder 10 dígitos.',
            'newMainCustomer.phone.required' => 'El teléfono es obligatorio.',
            'newMainCustomer.phone.max' => 'El teléfono no puede exceder 20 caracteres. Por favor, ingrese un número válido.',
            'newMainCustomer.email.email' => 'El email debe tener un formato válido (ejemplo: correo@dominio.com).',
            'newMainCustomer.email.max' => 'El email no puede exceder 255 caracteres.',
            'newMainCustomer.address.max' => 'La dirección no puede exceder 500 caracteres.',
        ];

        // Add DIAN validation if electronic invoice is required
        if ($requiresElectronicInvoice) {
            $rules['newMainCustomer.identificationDocumentId'] = 'required|exists:dian_identification_documents,id';
            $rules['newMainCustomer.municipalityId'] = 'required|exists:dian_municipalities,factus_id';

            $messages['newMainCustomer.identificationDocumentId.required'] = 'El tipo de documento es obligatorio para facturación electrónica.';
            $messages['newMainCustomer.identificationDocumentId.exists'] = 'El tipo de documento seleccionado no es válido. Por favor, seleccione una opción de la lista.';
            $messages['newMainCustomer.municipalityId.required'] = 'El municipio es obligatorio para facturación electrónica.';
            $messages['newMainCustomer.municipalityId.exists'] = 'El municipio seleccionado no es válido. Por favor, seleccione una opción de la lista.';

            // If juridical person, company is required
            if ($this->mainCustomerIsJuridicalPerson) {
                $rules['newMainCustomer.company'] = 'required|string|max:255';
                $messages['newMainCustomer.company.required'] = 'La razón social es obligatoria para personas jurídicas (NIT).';
                $messages['newMainCustomer.company.max'] = 'La razón social no puede exceder 255 caracteres.';
            }
        }

        $this->validate($rules, $messages);

        // Check if identification already exists
        $this->checkMainCustomerIdentification();
        if ($this->mainCustomerIdentificationExists) {
            $this->addError('newMainCustomer.identification', 'Esta identificación ya está registrada.');
            return;
        }

        $this->creatingMainCustomer = true;

        try {
            // Create customer
            $customer = Customer::create([
                'name' => mb_strtoupper($this->newMainCustomer['name']),
                'phone' => $this->newMainCustomer['phone'],
                'email' => $this->newMainCustomer['email'] ?? null,
                'address' => $this->newMainCustomer['address'] ?? null,
                'is_active' => true,
                'requires_electronic_invoice' => $requiresElectronicInvoice,
            ]);

            // Create tax profile
            $taxProfileData = [
                'identification' => $this->newMainCustomer['identification'],
                'dv' => $this->newMainCustomer['dv'] ?? null,
                'identification_document_id' => $requiresElectronicInvoice ? ($this->newMainCustomer['identificationDocumentId'] ?? null) : null,
                'legal_organization_id' => $requiresElectronicInvoice ? ($this->newMainCustomer['legalOrganizationId'] ?? null) : null,
                'tribute_id' => $requiresElectronicInvoice ? ($this->newMainCustomer['tributeId'] ?? null) : null,
                'municipality_id' => $requiresElectronicInvoice ? ($this->newMainCustomer['municipalityId'] ?? null) : null,
                'company' => $requiresElectronicInvoice && $this->mainCustomerIsJuridicalPerson ? ($this->newMainCustomer['company'] ?? null) : null,
                'trade_name' => $requiresElectronicInvoice ? ($this->newMainCustomer['tradeName'] ?? null) : null,
            ];

            $customer->taxProfile()->create($taxProfileData);

            // Add customer to the list
            $this->customers[] = [
                'id' => $customer->id,
                'name' => $customer->name,
                'phone' => $customer->phone ?? 'S/N',
                'email' => $customer->email ?? null,
                'taxProfile' => $customer->taxProfile ? [
                    'identification' => $customer->taxProfile->identification ?? 'S/N',
                    'dv' => $customer->taxProfile->dv ?? null,
                ] : null,
            ];

            // Select the newly created customer
            $this->customerId = (string) $customer->id;

            // Reset form and close modal
            $this->newMainCustomer = [
                'name' => '',
                'identification' => '',
                'phone' => '',
                'email' => '',
                'address' => '',
                'requiresElectronicInvoice' => false,
                'identificationDocumentId' => '',
                'dv' => '',
                'company' => '',
                'tradeName' => '',
                'municipalityId' => '',
                'legalOrganizationId' => '',
                'tributeId' => ''
            ];
            $this->newMainCustomerErrors = [];
            $this->mainCustomerIdentificationMessage = '';
            $this->mainCustomerIdentificationExists = false;
            $this->newCustomerModalOpen = false;

            // Show success message
            session()->flash('message', 'Cliente creado exitosamente.');
        } catch (\Exception $e) {
            \Log::error('Error creating customer: ' . $e->getMessage(), [
                'exception' => $e,
                'data' => $this->newMainCustomer
            ]);
            $this->addError('newMainCustomer.name', 'Error al crear el cliente. Por favor intente nuevamente.');
        } finally {
            $this->creatingMainCustomer = false;
        }
    }

    public function getFilteredCustomersProperty(): array
    {
        $allCustomers = $this->customers ?? [];

        // If no search term, return first 5 customers
        if (empty($this->customerSearchTerm)) {
            return array_slice($allCustomers, 0, 5);
        }

        $searchTerm = mb_strtolower(trim($this->customerSearchTerm));
        $filtered = [];

        foreach ($allCustomers as $customer) {
            $name = mb_strtolower($customer['name'] ?? '');
            $identification = $customer['taxProfile']['identification'] ?? '';
            $phone = mb_strtolower($customer['phone'] ?? '');

            // Search in name, identification, or phone
            if (str_contains($name, $searchTerm) ||
                str_contains($identification, $searchTerm) ||
                str_contains($phone, $searchTerm)) {
                $filtered[] = $customer;
            }

            // Limit to 20 results for performance
            if (count($filtered) >= 20) {
                break;
            }
        }

        return $filtered;
    }

    public function updatedCustomerSearchTerm($value)
    {
        // Keep dropdown open when typing
        if ($this->datesCompleted) {
            $this->showCustomerDropdown = true;
        }
    }

    public function openCustomerDropdown()
    {
        if ($this->datesCompleted) {
            // Always show dropdown when clicked, even if search term is empty
            // This will display the default 5 customers
            $this->showCustomerDropdown = true;
        }
    }

    public function selectCustomer($customerId)
    {
        $this->customerId = (string) $customerId;
        $this->customerSearchTerm = '';
        $this->showCustomerDropdown = false;
    }

    public function render()
    {
        return view('livewire.reservations.reservation-create');
    }
}
