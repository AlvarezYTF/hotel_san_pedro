<?php

namespace App\Http\Controllers;

use App\Models\Reservation;
use App\Models\ReservationRoom;
use App\Models\Customer;
use App\Models\Room;
use App\Models\DianIdentificationDocument;
use App\Models\DianLegalOrganization;
use App\Models\DianCustomerTribute;
use App\Models\DianMunicipality;
use App\Http\Requests\StoreReservationRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Collection;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

class ReservationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        Carbon::setLocale('es');
        $view = $request->get('view', 'calendar');
        $dateStr = $request->get('month', now()->format('Y-m'));
        $date = Carbon::createFromFormat('Y-m', $dateStr);

        $startOfMonth = $date->copy()->startOfMonth();
        $endOfMonth = $date->copy()->endOfMonth();

        $daysInMonth = [];
        $tempDate = $startOfMonth->copy();
        while ($tempDate <= $endOfMonth) {
            $daysInMonth[] = $tempDate->copy();
            $tempDate->addDay();
        }

        $rooms = Room::with(['reservations' => function($query) use ($startOfMonth, $endOfMonth) {
            $query->where(function($q) use ($startOfMonth, $endOfMonth) {
                $q->where('check_in_date', '<=', $endOfMonth)
                  ->where('check_out_date', '>=', $startOfMonth);
            });
        }, 'reservations.customer'])->orderBy('room_number')->get();

        // Asegurarse de que el status se maneje como string para la vista si es necesario,
        // aunque Blade puede manejar el enum.

        $reservations = Reservation::with(['customer', 'room'])->latest()->paginate(10);

        return view('reservations.index', compact(
            'reservations',
            'rooms',
            'daysInMonth',
            'view',
            'date'
        ));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $customers = Customer::withoutGlobalScopes()
            ->with('taxProfile')
            ->orderBy('name')
            ->get();
        $rooms = Room::where('status', '!=', \App\Enums\RoomStatus::MANTENIMIENTO)->get();

        // Prepare rooms data for frontend
        $roomsData = $this->prepareRoomsData($rooms);

        // Get DIAN catalogs for customer creation modal
        $dianCatalogs = $this->getDianCatalogs();

        // Prepare customers as simple array for Livewire
        $customersArray = $customers->map(function ($customer) {
            return [
                'id' => $customer->id,
                'name' => $customer->name,
                'phone' => $customer->phone ?? 'S/N',
                'email' => $customer->email ?? null,
                'taxProfile' => $customer->taxProfile ? [
                    'identification' => $customer->taxProfile->identification ?? 'S/N',
                    'dv' => $customer->taxProfile->dv ?? null,
                ] : null,
            ];
        })->toArray();

        // Prepare rooms as simple array for Livewire
        $roomsArray = $rooms->map(function ($room) {
            return [
                'id' => $room->id,
                'room_number' => $room->room_number,
                'beds_count' => $room->beds_count,
                'max_capacity' => $room->max_capacity,
            ];
        })->toArray();

        // Prepare DIAN catalogs as arrays
        $dianCatalogsArray = [
            'identificationDocuments' => $dianCatalogs['identificationDocuments']->toArray(),
            'legalOrganizations' => $dianCatalogs['legalOrganizations']->toArray(),
            'tributes' => $dianCatalogs['tributes']->toArray(),
            'municipalities' => $dianCatalogs['municipalities']->toArray(),
        ];

        return view('reservations.create', array_merge(
            [
                'customers' => $customersArray,
                'rooms' => $roomsArray,
                'roomsData' => $roomsData,
            ],
            $dianCatalogsArray
        ));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreReservationRequest $request)
    {
        try {
            $data = $request->validated();

            // Determine if using multiple rooms or single room (backward compatibility)
            $roomIds = $request->has('room_ids') && is_array($request->room_ids)
                ? $request->room_ids
                : ($request->room_id ? [$request->room_id] : []);

            if (empty($roomIds)) {
                return back()->withInput()->withErrors(['room_id' => 'Debe seleccionar al menos una habitación.']);
            }

            // Validate dates and availability
            $checkInDate = Carbon::parse($request->check_in_date);
            $checkOutDate = Carbon::parse($request->check_out_date);

            $dateValidation = $this->validateDates($checkInDate, $checkOutDate);
            if (!$dateValidation['valid']) {
                return back()->withInput()->withErrors($dateValidation['errors']);
            }

            // Validate availability for all rooms
            $availabilityErrors = $this->validateRoomsAvailability($roomIds, $checkInDate, $checkOutDate);
            if (!empty($availabilityErrors)) {
                return back()->withInput()->withErrors($availabilityErrors);
            }

            // Validate guest assignment
            $roomGuests = $request->room_guests ?? [];
            $rooms = Room::whereIn('id', $roomIds)->get()->keyBy('id');

            $guestValidationErrors = $this->validateGuestAssignment($roomGuests, $rooms);
            if (!empty($guestValidationErrors)) {
                return back()->withInput()->withErrors($guestValidationErrors);
            }

            // Remove payment_method from data if not provided (it's optional)
            if (!isset($data['payment_method']) || empty($data['payment_method'])) {
                unset($data['payment_method']);
            }

            // For backward compatibility, use first room_id for the room_id field
            $data['room_id'] = $roomIds[0];

            $reservation = Reservation::create($data);

            // Attach all rooms to reservation via pivot table
            foreach ($roomIds as $roomId) {
                $reservationRoom = ReservationRoom::create([
                    'reservation_id' => $reservation->id,
                    'room_id' => $roomId,
                ]);

                // Assign guests to this specific room if provided
                $this->assignGuestsToRoom($reservationRoom, $roomGuests[$roomId] ?? []);
            }

            // Backward compatibility: Assign guests to reservation if using old format
            if ($request->has('guest_ids') && is_array($request->guest_ids) && !$request->has('room_guests')) {
                $this->assignGuestsToReservationLegacy($reservation, $request->guest_ids);
            }

            // Mark rooms as occupied if check-in is today
            if ($checkInDate->isToday()) {
                Room::whereIn('id', $roomIds)->update(['status' => \App\Enums\RoomStatus::OCUPADA]);
            }

            // Dispatch Livewire event for stats update
            \Livewire\Livewire::dispatch('reservation-created');

            // Redirect to reservations index with calendar view for the check-in month
            $month = $checkInDate->format('Y-m');
            return redirect()->route('reservations.index', ['view' => 'calendar', 'month' => $month])
                ->with('success', 'Reserva registrada exitosamente.');
        } catch (\Exception $e) {
            \Log::error('Error creating reservation: ' . $e->getMessage(), [
                'request_data' => $request->all(),
                'exception' => $e
            ]);

            return back()->withInput()->withErrors(['error' => 'Error al crear la reserva: ' . $e->getMessage()]);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Reservation $reservation)
    {
        return view('reservations.show', compact('reservation'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Reservation $reservation)
    {
        $customers = Customer::withoutGlobalScopes()
            ->with('taxProfile')
            ->orderBy('name')
            ->get();
        $rooms = Room::all(); // Show all rooms for edit
        return view('reservations.edit', compact('reservation', 'customers', 'rooms'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(StoreReservationRequest $request, Reservation $reservation)
    {
        $exists = Reservation::where('room_id', $request->room_id)
            ->where('id', '!=', $reservation->id)
            ->where(function ($query) use ($request) {
                $query->where('check_in_date', '<', $request->check_out_date)
                      ->where('check_out_date', '>', $request->check_in_date);
            })
            ->exists();

        if ($exists) {
            return back()->withInput()->withErrors(['room_id' => 'La habitación ya está reservada para las fechas seleccionadas.']);
        }

        $reservation->update($request->validated());

        // Dispatch Livewire event for stats update
        \Livewire\Livewire::dispatch('reservation-updated');

        return redirect()->route('reservations.index')->with('success', 'Reserva actualizada correctamente.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Reservation $reservation)
    {
        $reservationId = $reservation->id;
        $customerName = $reservation->customer->name;

        $reservation->delete();

        \App\Models\AuditLog::create([
            'user_id' => auth()->id(),
            'event' => 'reservation_deleted',
            'description' => "Eliminó la reserva #{$reservationId} del cliente {$customerName}",
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);

        // Dispatch Livewire event for stats update
        \Livewire\Livewire::dispatch('reservation-deleted');

        return redirect()->route('reservations.index')->with('success', 'Reserva eliminada correctamente.');
    }

    /**
     * Download the reservation support in PDF format.
     */
    public function download(Reservation $reservation)
    {
        $reservation->load(['customer', 'room']);
        $pdf = Pdf::loadView('reservations.pdf', compact('reservation'));
        return $pdf->download("Soporte_Reserva_{$reservation->id}.pdf");
    }

    /**
     * Check if a room is available for a given date range.
     */
    public function checkAvailability(Request $request)
    {
        $checkIn = Carbon::parse($request->check_in_date);
        $checkOut = Carbon::parse($request->check_out_date);
        $excludeReservationId = $request->reservation_id ? (int) $request->reservation_id : null;

        $isAvailable = $this->isRoomAvailable(
            (int) $request->room_id,
            $checkIn,
            $checkOut,
            $excludeReservationId
        );

        return response()->json(['available' => $isAvailable]);
    }

    /**
     * Prepare rooms data for frontend consumption.
     * Similar to CustomerController::getTaxCatalogs() pattern.
     */
    private function prepareRoomsData(Collection $rooms): array
    {
        return $rooms->map(function (Room $room): array {
            $occupancyPrices = $room->occupancy_prices ?? [];

            // Fallback to legacy prices if occupancy_prices is empty
            if (empty($occupancyPrices)) {
                $occupancyPrices = [
                    1 => (float) ($room->price_1_person ?: $room->price_per_night),
                    2 => (float) ($room->price_2_persons ?: $room->price_per_night),
                ];
                // Calculate additional person prices
                $additionalPrice = (float) ($room->price_additional_person ?: 0);
                for ($i = 3; $i <= ($room->max_capacity ?? 2); $i++) {
                    $occupancyPrices[$i] = $occupancyPrices[2] + ($additionalPrice * ($i - 2));
                }
            } else {
                // Ensure keys are integers (JSON may return string keys)
                $normalizedPrices = [];
                foreach ($occupancyPrices as $key => $value) {
                    $normalizedPrices[(int) $key] = (float) $value;
                }
                $occupancyPrices = $normalizedPrices;
            }

            // Calculate additional person price
            // If price_additional_person is set, use it; otherwise calculate from price_2_persons - price_1_person
            $price1Person = (float) ($room->price_1_person ?: $room->price_per_night);
            $price2Persons = (float) ($room->price_2_persons ?: $room->price_per_night);
            $priceAdditionalPerson = (float) $room->price_additional_person;

            // If price_additional_person is 0 or not set, calculate it from the difference
            if ($priceAdditionalPerson == 0 && $price2Persons > $price1Person) {
                $priceAdditionalPerson = $price2Persons - $price1Person;
            }

            return [
                'id' => $room->id,
                'number' => $room->room_number,
                'beds' => $room->beds_count,
                'price' => (float) $room->price_per_night, // Keep for backward compatibility
                'occupancyPrices' => $occupancyPrices, // Prices by number of guests
                'price1Person' => $price1Person, // Base price for 1 person
                'price2Persons' => $price2Persons, // Price for 2 persons (for calculation fallback)
                'priceAdditionalPerson' => $priceAdditionalPerson, // Additional price per person
                'capacity' => $room->max_capacity ?? 2,
                'status' => $room->status->value,
            ];
        })->toArray();
    }

    /**
     * Get DIAN catalogs for customer creation modal.
     * Similar to CustomerController::getTaxCatalogs() pattern.
     */
    private function getDianCatalogs(): array
    {
        return [
            'identificationDocuments' => DianIdentificationDocument::query()->orderBy('id')->get(),
            'legalOrganizations' => DianLegalOrganization::query()->orderBy('id')->get(),
            'tributes' => DianCustomerTribute::query()->orderBy('id')->get(),
            'municipalities' => DianMunicipality::query()
                ->orderBy('department')
                ->orderBy('name')
                ->get(),
        ];
    }

    /**
     * Validate dates for a reservation.
     * Ensures check-in is not before today and check-out is after check-in.
     */
    private function validateDates(Carbon $checkIn, Carbon $checkOut): array
    {
        $errors = [];
        $today = Carbon::today();

        // Check if check-in is before today
        if ($checkIn->isBefore($today)) {
            $errors['check_in_date'] = 'La fecha de entrada no puede ser anterior al día actual.';
        }

        // Check if check-out is before or equal to check-in
        if ($checkOut->isBeforeOrEqualTo($checkIn)) {
            $errors['check_out_date'] = 'La fecha de salida debe ser posterior a la fecha de entrada.';
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors,
        ];
    }

    /**
     * Validate availability for multiple rooms.
     */
    private function validateRoomsAvailability(array $roomIds, Carbon $checkIn, Carbon $checkOut): array
    {
        $errors = [];

        foreach ($roomIds as $roomId) {
            if (!$this->isRoomAvailable($roomId, $checkIn, $checkOut)) {
                $room = Room::find($roomId);
                $roomNumber = $room ? $room->room_number : $roomId;
                $errors['room_ids'][] = "La habitación {$roomNumber} ya está reservada para las fechas seleccionadas.";
            }
        }

        return $errors;
    }

    /**
     * Check if a room is available for a given date range.
     */
    private function isRoomAvailable(int $roomId, Carbon $checkIn, Carbon $checkOut, ?int $excludeReservationId = null): bool
    {
        // Check in main reservations table (single room reservations)
        $existsInReservations = Reservation::where('room_id', $roomId)
            ->where(function ($query) use ($checkIn, $checkOut) {
                $query->where('check_in_date', '<', $checkOut)
                      ->where('check_out_date', '>', $checkIn);
            })
            ->when($excludeReservationId, function ($q) use ($excludeReservationId) {
                $q->where('id', '!=', $excludeReservationId);
            })
            ->exists();

        if ($existsInReservations) {
            return false;
        }

        // Check in reservation_rooms table (multi-room reservations)
        $existsInPivot = DB::table('reservation_rooms')
            ->join('reservations', 'reservation_rooms.reservation_id', '=', 'reservations.id')
            ->where('reservation_rooms.room_id', $roomId)
            ->where(function ($query) use ($checkIn, $checkOut) {
                $query->where('reservations.check_in_date', '<', $checkOut)
                      ->where('reservations.check_out_date', '>', $checkIn);
            })
            ->when($excludeReservationId, function ($q) use ($excludeReservationId) {
                $q->where('reservations.id', '!=', $excludeReservationId);
            })
            ->exists();

        return !$existsInPivot;
    }

    /**
     * Validate guest assignment for multiple rooms.
     */
    private function validateGuestAssignment(array $roomGuests, Collection $rooms): array
    {
        $errors = [];

        foreach ($roomGuests as $roomId => $assignedGuestIds) {
            $room = $rooms->get($roomId);

            if (!$room) {
                $errors['room_guests'][] = "La habitación con ID {$roomId} no existe.";
                continue;
            }

            // Filter valid guest IDs
            $validGuestIds = array_filter($assignedGuestIds, function ($id): bool {
                return !empty($id) && is_numeric($id) && $id > 0;
            });

            $guestCount = count($validGuestIds);

            if ($guestCount > $room->max_capacity) {
                $errors['room_guests'][] = "La habitación {$room->room_number} tiene una capacidad máxima de {$room->max_capacity} personas, pero se están intentando asignar {$guestCount}.";
            }
        }

        return $errors;
    }

    /**
     * Assign guests to a specific reservation room.
     */
    private function assignGuestsToRoom(ReservationRoom $reservationRoom, array $assignedGuestIds): void
    {
        if (empty($assignedGuestIds)) {
            return;
        }

        // Filter valid guest IDs
        $validGuestIds = array_filter($assignedGuestIds, function ($id): bool {
            return !empty($id) && is_numeric($id) && $id > 0;
        });

        if (empty($validGuestIds)) {
            return;
        }

        // Verify guests exist in database
        $validGuestIds = Customer::withoutGlobalScopes()
            ->whereIn('id', $validGuestIds)
            ->pluck('id')
            ->toArray();

        if (!empty($validGuestIds)) {
            $reservationRoom->guests()->attach($validGuestIds);
        }
    }

    /**
     * Assign guests to reservation (legacy format for single-room reservations).
     */
    private function assignGuestsToReservationLegacy(Reservation $reservation, array $guestIds): void
    {
        // Filter valid guest IDs
        $validGuestIds = array_filter($guestIds, function ($id): bool {
            return !empty($id) && is_numeric($id) && $id > 0;
        });

        if (empty($validGuestIds)) {
            return;
        }

        // Verify guests exist in database
        $validGuestIds = Customer::withoutGlobalScopes()
            ->whereIn('id', $validGuestIds)
            ->pluck('id')
            ->toArray();

        if (empty($validGuestIds)) {
            return;
        }

        try {
            $reservation->guests()->attach($validGuestIds);
        } catch (\Exception $e) {
            \Log::error('Error attaching guests to reservation: ' . $e->getMessage(), [
                'reservation_id' => $reservation->id,
                'guest_ids' => $validGuestIds,
                'exception' => $e,
            ]);
        }
    }
}
