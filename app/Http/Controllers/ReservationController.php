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

        // Preparar datos de habitaciones para Alpine.js
        $roomsData = $rooms->map(function($room) {
            $occupancyPrices = $room->occupancy_prices ?? [];

            // Fallback to legacy prices if occupancy_prices is empty
            if (empty($occupancyPrices)) {
                $occupancyPrices = [
                    1 => (float)$room->price_1_person ?: (float)$room->price_per_night,
                    2 => (float)$room->price_2_persons ?: (float)$room->price_per_night,
                ];
                // Calculate additional person prices
                $additionalPrice = (float)$room->price_additional_person ?: 0;
                for ($i = 3; $i <= ($room->max_capacity ?? 2); $i++) {
                    $occupancyPrices[$i] = $occupancyPrices[2] + ($additionalPrice * ($i - 2));
                }
            } else {
                // Ensure keys are integers (JSON may return string keys)
                $normalizedPrices = [];
                foreach ($occupancyPrices as $key => $value) {
                    $normalizedPrices[(int)$key] = (float)$value;
                }
                $occupancyPrices = $normalizedPrices;
            }

            // Calculate additional person price
            // If price_additional_person is set, use it; otherwise calculate from price_2_persons - price_1_person
            $price1Person = (float)$room->price_1_person ?: (float)$room->price_per_night;
            $price2Persons = (float)$room->price_2_persons ?: (float)$room->price_per_night;
            $priceAdditionalPerson = (float)$room->price_additional_person;

            // If price_additional_person is 0 or not set, calculate it from the difference
            if ($priceAdditionalPerson == 0 && $price2Persons > $price1Person) {
                $priceAdditionalPerson = $price2Persons - $price1Person;
            }

            return [
                'id' => $room->id,
                'number' => $room->room_number,
                'beds' => $room->beds_count,
                'price' => (float)$room->price_per_night, // Keep for backward compatibility
                'occupancyPrices' => $occupancyPrices, // Prices by number of guests
                'price1Person' => $price1Person, // Base price for 1 person
                'price2Persons' => $price2Persons, // Price for 2 persons (for calculation fallback)
                'priceAdditionalPerson' => $priceAdditionalPerson, // Additional price per person
                'capacity' => $room->max_capacity ?? 2,
                'status' => $room->status->value
            ];
        });

        // Get DIAN catalogs for customer creation modal
        $identificationDocuments = DianIdentificationDocument::query()->orderBy('id')->get();
        $legalOrganizations = DianLegalOrganization::query()->orderBy('id')->get();
        $tributes = DianCustomerTribute::query()->orderBy('id')->get();
        $municipalities = DianMunicipality::query()
            ->orderBy('department')
            ->orderBy('name')
            ->get();

        return view('reservations.create', compact(
            'customers',
            'rooms',
            'roomsData',
            'identificationDocuments',
            'legalOrganizations',
            'tributes',
            'municipalities'
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

            // Validate availability for all rooms
            $checkInDate = Carbon::parse($request->check_in_date);
            $checkOutDate = Carbon::parse($request->check_out_date);

            foreach ($roomIds as $roomId) {
                $exists = Reservation::where('room_id', $roomId)
                    ->where(function ($query) use ($checkInDate, $checkOutDate) {
                        $query->where('check_in_date', '<', $checkOutDate)
                              ->where('check_out_date', '>', $checkInDate);
                    })
                    ->exists();

                if ($exists) {
                    $room = Room::find($roomId);
                    $roomNumber = $room ? $room->room_number : $roomId;
                    return back()->withInput()->withErrors(['room_ids' => "La habitación {$roomNumber} ya está reservada para las fechas seleccionadas."]);
                }

                // Also check in reservation_rooms table (for multi-room reservations)
                $existsInPivot = DB::table('reservation_rooms')
                    ->join('reservations', 'reservation_rooms.reservation_id', '=', 'reservations.id')
                    ->where('reservation_rooms.room_id', $roomId)
                    ->where(function ($query) use ($checkInDate, $checkOutDate) {
                        $query->where('reservations.check_in_date', '<', $checkOutDate)
                              ->where('reservations.check_out_date', '>', $checkInDate);
                    })
                    ->exists();

                if ($existsInPivot) {
                    $room = Room::find($roomId);
                    $roomNumber = $room ? $room->room_number : $roomId;
                    return back()->withInput()->withErrors(['room_ids' => "La habitación {$roomNumber} ya está reservada para las fechas seleccionadas."]);
                }
            }

            // Validate capacity for each room with assigned guests
            $roomGuests = $request->room_guests ?? [];
            $rooms = Room::whereIn('id', $roomIds)->get()->keyBy('id');

            foreach ($roomIds as $roomId) {
                $room = $rooms->get($roomId);
                if (!$room) {
                    return back()->withInput()->withErrors(['room_ids' => "La habitación con ID {$roomId} no existe."]);
                }

                $assignedGuestIds = $roomGuests[$roomId] ?? [];
                $guestCount = count(array_filter($assignedGuestIds, fn($id) => !empty($id) && is_numeric($id)));

                if ($guestCount > $room->max_capacity) {
                    return back()->withInput()->withErrors([
                        'room_guests' => "La habitación {$room->room_number} tiene una capacidad máxima de {$room->max_capacity} personas, pero se están intentando asignar {$guestCount}."
                    ]);
                }
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
                $assignedGuestIds = $roomGuests[$roomId] ?? [];
                if (!empty($assignedGuestIds)) {
                    $validGuestIds = array_filter($assignedGuestIds, function($id) {
                        return !empty($id) && is_numeric($id) && $id > 0;
                    });

                    if (!empty($validGuestIds)) {
                        $validGuestIds = Customer::withoutGlobalScopes()
                            ->whereIn('id', $validGuestIds)
                            ->pluck('id')
                            ->toArray();
                        if (!empty($validGuestIds)) {
                            $reservationRoom->guests()->attach($validGuestIds);
                        }
                    }
                }
            }

            // Backward compatibility: Assign guests to reservation if using old format
            if ($request->has('guest_ids') && is_array($request->guest_ids) && !$request->has('room_guests')) {
                $guestIds = array_filter($request->guest_ids, function($id) {
                    return !empty($id) && is_numeric($id) && $id > 0;
                });

                $guestIds = array_values($guestIds);

                if (!empty($guestIds)) {
                    $validGuestIds = Customer::withoutGlobalScopes()
                        ->whereIn('id', $guestIds)
                        ->pluck('id')
                        ->toArray();

                    if (count($validGuestIds) > 0) {
                        try {
                            $reservation->guests()->attach($validGuestIds);
                        } catch (\Exception $e) {
                            \Log::error('Error attaching guests to reservation: ' . $e->getMessage(), [
                                'reservation_id' => $reservation->id,
                                'guest_ids' => $validGuestIds,
                                'exception' => $e
                            ]);
                        }
                    }
                }
            }

            // Mark rooms as occupied if check-in is today
            if ($checkInDate->isToday()) {
                Room::whereIn('id', $roomIds)->update(['status' => \App\Enums\RoomStatus::OCUPADA]);
            }

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
        $exists = Reservation::where('room_id', $request->room_id)
            ->where(function ($query) use ($request) {
                $query->where('check_in_date', '<', $request->check_out_date)
                      ->where('check_out_date', '>', $request->check_in_date);
            })
            ->when($request->reservation_id, function($q) use ($request) {
                $q->where('id', '!=', $request->reservation_id);
            })
            ->exists();

        return response()->json(['available' => !$exists]);
    }
}
