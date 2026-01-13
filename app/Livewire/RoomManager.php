<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\On;
use App\Models\Room;
use App\Models\RoomType;
use App\Models\VentilationType;
use App\Models\ReservationRoom;
use App\Models\Reservation;
use App\Models\Payment;
use App\Enums\RoomDisplayStatus;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class RoomManager extends Component
{
    use WithPagination;

    // Propiedades de estado
    public string $activeTab = 'rooms';
    public $currentDate = null;
    public $date = null;
    public $search = '';
    public $statusFilter = null;
    public $ventilationTypeFilter = null;

    // Modales
    public bool $quickRentModal = false;
    public bool $roomDetailModal = false;
    public bool $roomEditModal = false;
    public bool $createRoomModal = false;
    public bool $releaseHistoryDetailModal = false;
    public bool $roomReleaseConfirmationModal = false;
    public bool $guestsModal = false;
    public bool $isReleasingRoom = false;

    // Datos de modales
    public ?array $detailData = null;
    public ?array $rentForm = null;
    
    // Computed properties para UX (no persistidos)
    public function getBalanceDueProperty()
    {
        if (!$this->rentForm) return 0;
        $total = (float)($this->rentForm['total'] ?? 0);
        $deposit = (float)($this->rentForm['deposit'] ?? 0);
        return max(0, $total - $deposit);
    }
    
    public function getPaymentStatusBadgeProperty()
    {
        if (!$this->rentForm) return ['text' => 'Sin datos', 'color' => 'gray'];
        
        $total = (float)($this->rentForm['total'] ?? 0);
        $deposit = (float)($this->rentForm['deposit'] ?? 0);
        
        if ($deposit >= $total && $total > 0) {
            return ['text' => 'Pagado', 'color' => 'emerald'];
        } elseif ($deposit > 0) {
            return ['text' => 'Pago parcial', 'color' => 'amber'];
        } else {
            return ['text' => 'Pendiente de pago', 'color' => 'red'];
        }
    }
    
    // Métodos para botones rápidos de pago
    public function setDepositFull()
    {
        if ($this->rentForm) {
            $this->rentForm['deposit'] = $this->rentForm['total'];
        }
    }
    
    public function setDepositHalf()
    {
        if ($this->rentForm) {
            $this->rentForm['deposit'] = round($this->rentForm['total'] / 2, 2);
        }
    }
    
    public function setDepositNone()
    {
        if ($this->rentForm) {
            $this->rentForm['deposit'] = 0;
        }
    }

    /**
     * Calcula el número total de huéspedes (principal + adicionales) con fallback a 1.
     */
    private function calculateGuestCount(): int
    {
        if (!$this->rentForm) {
            return 1;
        }

        $principal = !empty($this->rentForm['client_id']) ? 1 : 0;
        $additional = is_array($this->additionalGuests) ? count($this->additionalGuests) : 0;

        return max(1, $principal + $additional);
    }

    /**
     * Selecciona la tarifa adecuada según cantidad de huéspedes.
     * - Busca rango min/max que contenga el número de huéspedes.
     * - Si no hay coincidencia exacta, usa la tarifa con mayor max_guests (más cercana permitida).
     * - Fallback al base_price_per_night.
     */
    private function findRateForGuests(Room $room, int $guests): float
    {
        $rates = $room->rates;

        if ($rates && $rates->isNotEmpty()) {
            $sorted = $rates->sortBy('min_guests');

            $matching = $sorted->first(function ($rate) use ($guests) {
                $min = (int)($rate->min_guests ?? 0);
                $max = (int)($rate->max_guests ?? 0);
                return $guests >= $min && ($max === 0 || $guests <= $max);
            });

            if ($matching) {
                return (float)($matching->price_per_night ?? 0);
            }

            // Tarifa más cercana: la de mayor max_guests disponible
            $closest = $sorted->sortByDesc('max_guests')->first();
            if ($closest) {
                return (float)($closest->price_per_night ?? 0);
            }
        }

        return (float)($room->base_price_per_night ?? 0);
    }

    /**
     * Recalcula total, noches y guests_count cuando cambia personas o fechas.
     */
    private function recalculateQuickRentTotals(?Room $room = null): void
    {
        if (!$this->rentForm) {
            return;
        }

        $roomModel = $room ?? Room::with('rates')->find($this->rentForm['room_id'] ?? null);
        if (!$roomModel) {
            return;
        }

        $guests = $this->calculateGuestCount();

        $checkIn = Carbon::parse($this->rentForm['check_in_date'] ?? $this->date->toDateString());
        $checkOut = Carbon::parse($this->rentForm['check_out_date'] ?? $this->date->copy()->addDay()->toDateString());
        $nights = max(1, $checkIn->diffInDays($checkOut));

        $pricePerNight = $this->findRateForGuests($roomModel, $guests);
        $total = $pricePerNight * $nights;

        $this->rentForm['guests_count'] = $guests;
        $this->rentForm['total'] = $total;
    }

    /**
     * Obtiene el ID del método de pago por código en payments_methods.
     */
    private function getPaymentMethodId(string $code): ?int
    {
        return DB::table('payments_methods')
            ->whereRaw('LOWER(code) = ?', [strtolower($code)])
            ->value('id');
    }
    public ?array $additionalGuests = null;
    public ?array $releaseHistoryDetail = null;
    public ?array $roomEditData = null;
    public ?array $newSale = null;
    public ?array $newDeposit = null;
    public bool $showAddSale = false;
    public bool $showAddDeposit = false;

    // Propiedades derivadas
    public $daysInMonth = null;
    public ?array $statuses = null;
    public $ventilationTypes = null;
    public ?object $releaseHistory = null;

    protected $listeners = [
        'room-created' => '$refresh',
        'room-updated' => '$refresh',
        'refreshRooms' => 'loadRooms',
    ];

    public function mount($date = null, $search = null, $status = null)
    {
        $this->currentDate = $date ? Carbon::parse($date) : now();
        $this->date = $this->currentDate;
        $this->search = $search ?? '';
        $this->statusFilter = $status;
        
        // Generar array de días del mes
        $startOfMonth = $this->currentDate->copy()->startOfMonth();
        $daysCount = $this->currentDate->daysInMonth;
        $this->daysInMonth = collect(range(1, $daysCount))
            ->map(fn($day) => $startOfMonth->copy()->day($day))
            ->toArray();

        // Cargar catálogos
        $this->loadStatuses();
        $this->loadVentilationTypes();

        // Cargar datos iniciales
        $this->loadReleaseHistory();
    }

    public function loadStatuses()
    {
        $this->statuses = RoomDisplayStatus::cases();
    }

    public function loadVentilationTypes()
    {
        $this->ventilationTypes = VentilationType::all(['id', 'code', 'name']);
    }

    protected function getRoomsQuery()
    {
        $query = Room::query();

        if ($this->search) {
            $query->where('room_number', 'like', '%' . $this->search . '%');
        }

        if ($this->ventilationTypeFilter) {
            $query->where('ventilation_type_id', $this->ventilationTypeFilter);
        }

        $startOfMonth = $this->currentDate->copy()->startOfMonth();
        $endOfMonth = $this->currentDate->copy()->endOfMonth();

        return $query->with([
            'roomType',
            'ventilationType',
            'reservationRooms' => function($q) use ($startOfMonth, $endOfMonth) {
                $q->where('check_in_date', '<=', $endOfMonth->toDateString())
                  ->where('check_out_date', '>=', $startOfMonth->toDateString())
                  ->with(['reservation' => function($r) {
                      $r->with(['customer', 'sales', 'payments']);
                  }]);
            },
            'rates',
            'maintenanceBlocks' => function($q) {
                $q->where('status_id', function($subq) {
                    $subq->select('id')->from('room_maintenance_block_statuses')
                        ->where('code', 'active');
                });
            }
        ])
        ->orderBy('room_number');
    }

    public function loadReleaseHistory()
    {
        // Cargar historial de liberación de habitaciones
        // Se implementará cuando exista la tabla de historial
        $this->releaseHistory = collect([]);
    }

    /**
     * Carga huéspedes de la reserva activa de una habitación
     * Usa STAY (ocupación real con timestamps) en lugar de ReservationRoom (fechas).
     */
    public function loadRoomGuests($roomId)
    {
        try {
            // Cargar room sin eager loading de guests para evitar errores SQL
            $room = Room::with([
                'stays.reservation.customer',
                'stays.reservation.customer.taxProfile',
                // NO cargar guests aquí para evitar errores SQL - se cargará manualmente después
            ])
                ->find($roomId);

            if (!$room) {
                return ['guests' => [], 'customer' => null, 'room_number' => null, 'main_guest' => null];
            }

            // Obtener la Stay que intersecta con la fecha consultada
            $activeStay = $room->getAvailabilityService()->getStayForDate($this->date ?? Carbon::today());
            
            // GUARD: Si no hay stay activa, retornar vacío
            if (!$activeStay) {
                return [
                    'room_number' => $room->room_number,
                    'guests' => [],
                    'customer' => null,
                    'main_guest' => null,
                ];
            }
            
            $activeReservation = $activeStay?->reservation;
            $customer = $activeReservation?->customer;

            // Obtener ReservationRoom asociado para acceder a huéspedes adicionales
            $activeReservationRoom = null;
            if ($activeReservation) {
                $activeReservationRoom = $activeReservation->reservationRooms
                    ->where('room_id', $room->id)
                    ->first();
            }

            // Huésped principal (customer de la reserva)
            $mainGuest = null;
            if ($customer) {
                $mainGuest = [
                    'id' => $customer->id,
                    'name' => $customer->name,
                    'identification' => $customer->taxProfile?->identification ?? null,
                    'phone' => $customer->phone ?? null,
                    'email' => $customer->email ?? null,
                    'is_main' => true,
                ];
            }

            // Huéspedes adicionales (desde reservation_room_guests)
            $additionalGuests = collect();
            if ($activeReservationRoom) {
                try {
                    // Cargar guests usando el método helper que maneja errores
                    $guests = $activeReservationRoom->getGuests();
                    if ($guests && $guests->isNotEmpty()) {
                        $additionalGuests = $guests->map(function($guest) {
                            // Cargar taxProfile si no está cargado
                            if (!$guest->relationLoaded('taxProfile')) {
                                $guest->load('taxProfile');
                            }
                            
                            return [
                                'id' => $guest->id,
                                'name' => $guest->name,
                                'identification' => $guest->taxProfile?->identification ?? null,
                                'phone' => $guest->phone ?? null,
                                'email' => $guest->email ?? null,
                                'is_main' => false,
                            ];
                        });
                    }
                } catch (\Exception $e) {
                    // Si falla la carga de guests, simplemente retornar colección vacía
                    \Log::warning('Error loading additional guests', [
                        'room_id' => $room->id,
                        'reservation_room_id' => $activeReservationRoom->id ?? null,
                        'error' => $e->getMessage()
                    ]);
                    $additionalGuests = collect();
                }
            }

            // Combinar huésped principal y adicionales
            $guests = collect();
            if ($mainGuest) {
                $guests->push($mainGuest);
            }
            $guests = $guests->merge($additionalGuests);

            return [
                'room_number' => $room->room_number,
                'main_guest' => $mainGuest,
                'guests' => $guests->values()->toArray(),
            ];
        } catch (\Exception $e) {
            // Protección total: nunca lanzar excepciones
            \Log::error('Error in loadRoomGuests', [
                'room_id' => $roomId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return [
                'guests' => [],
                'customer' => null,
                'room_number' => null,
                'main_guest' => null,
            ];
        }
    }


    public function switchTab(string $tab): void
    {
        $this->activeTab = $tab;
    }

    public function refreshRoomsPolling()
    {
        if ($this->isReleasingRoom) {
            return; // NO refrescar mientras se libera una habitación
        }
        // Livewire automatically re-renders, no need to manually load
    }

    /**
     * Forzar recarga de habitaciones desde BD tras eventos.
     */
    public function loadRooms()
    {
        $this->resetPage();
    }

    /**
     * Marca una habitación como limpia actualizando last_cleaned_at.
     * Solo permitido cuando operational_status === 'pending_cleaning'.
     */
    public function markRoomAsClean($roomId)
    {
        try {
            $room = Room::find($roomId);
            if (!$room) {
                $this->dispatch('notify', type: 'error', message: 'Habitación no encontrada.');
                return;
            }

            // Validar que esté en pending_cleaning
            $operationalStatus = $room->getOperationalStatus($this->date ?? Carbon::today());
            if ($operationalStatus !== 'pending_cleaning') {
                $this->dispatch('notify', type: 'error', message: 'La habitación no requiere limpieza.');
                return;
            }

            $room->last_cleaned_at = now();
            $room->save();

            $this->dispatch('notify', type: 'success', message: 'Habitación marcada como limpia.');
            $this->dispatch('refreshRooms');
            
            // Notificar al frontend sobre el cambio de estado
            $this->dispatch('room-marked-clean', roomId: $room->id);
        } catch (\Exception $e) {
            $this->dispatch('notify', type: 'error', message: 'Error al marcar habitación: ' . $e->getMessage());
            \Log::error('Error marking room as clean: ' . $e->getMessage(), ['exception' => $e]);
        }
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedStatusFilter()
    {
        $this->resetPage();
    }

    public function updatedVentilationTypeFilter()
    {
        $this->resetPage();
    }

    public function goToDate($date)
    {
        $this->date = Carbon::parse($date);
        $this->currentDate = $this->date;
    }

    public function nextDay()
    {
        $this->date = $this->date->copy()->addDay();
    }

    public function previousDay()
    {
        $this->date = $this->date->copy()->subDay();
    }

    /**
     * Cambia la fecha actual y regenera el arreglo de días del mes para los filtros.
     */
    public function changeDate($newDate)
    {
        $this->date = Carbon::parse($newDate);
        $this->currentDate = $this->date;

        $startOfMonth = $this->currentDate->copy()->startOfMonth();
        $daysCount = $this->currentDate->daysInMonth;
        $this->daysInMonth = collect(range(1, $daysCount))
            ->map(fn($day) => $startOfMonth->copy()->day($day))
            ->toArray();

        $this->resetPage();
    }

    public function goToToday()
    {
        $this->date = now();
        $this->currentDate = $this->date;
    }

    public function openRoomDetail($roomId)
    {
        $room = Room::with([
            'reservationRooms' => function($q) {
                $q->where('check_in_date', '<=', $this->date->toDateString())
                  ->where('check_out_date', '>=', $this->date->toDateString());
            },
            'reservationRooms.reservation.customer',
            'reservationRooms.reservation.sales.product',
            'reservationRooms.reservation.payments',
            'rates',
            'maintenanceBlocks'
        ])->find($roomId);

        if (!$room) {
            return;
        }

        // Obtener información de acceso: si es fecha histórica, bloquear
        $availabilityService = $room->getAvailabilityService();
        $accessInfo = $availabilityService->getAccessInfo($this->date);

        if ($accessInfo['isHistoric']) {
            $this->dispatch('notify', type: 'warning', message: 'Información histórica: datos en solo lectura. No se permite modificar.');
        }

        $activeReservation = $room->getActiveReservation($this->date);
        $sales = collect();
        $payments = collect();
        $totalHospedaje = 0;
        $abonoRealizado = 0;
        $salesTotal = 0;
        $totalDebt = 0;
        $identification = null;
        $stayHistory = [];

        if ($activeReservation) {
            $sales = $activeReservation->sales ?? collect();
            $payments = $activeReservation->payments ?? collect();

            $reservationRoom = $room->reservationRooms->first();
            $nights = 0;
            $pricePerNight = 0;

            if ($reservationRoom) {
                $checkIn = Carbon::parse($reservationRoom->check_in_date);
                $checkOut = Carbon::parse($reservationRoom->check_out_date);
                $nights = $reservationRoom->nights ?? $checkIn->diffInDays($checkOut);
                if ($nights <= 0) {
                    $nights = 1; // mostrar al menos una noche
                }

                $pricePerNight = (float)($reservationRoom->price_per_night ?? 0);
                if ($pricePerNight == 0 && $activeReservation->total_amount && $nights > 0) {
                    $pricePerNight = (float)$activeReservation->total_amount / $nights;
                }
                if ($pricePerNight == 0 && $room->rates?->isNotEmpty()) {
                    $pricePerNight = (float)($room->rates->sortBy('min_guests')->first()->price_per_night ?? 0);
                }
                if ($pricePerNight == 0) {
                    $pricePerNight = (float)($room->base_price_per_night ?? 0);
                }

                for ($i = 0; $i < $nights; $i++) {
                    $stayHistory[] = [
                        'date' => $checkIn->copy()->addDays($i)->format('Y-m-d'),
                        'price' => $pricePerNight,
                        'is_paid' => false, // TODO: flag real por noche si existe
                    ];
                }

                $totalHospedaje = $pricePerNight * $nights;
            }

            if ($totalHospedaje == 0) {
                $totalHospedaje = (float)($activeReservation->total_amount ?? 0);
            }

            $abonoRealizado = (float)($payments->sum('amount') ?? 0);
            $salesTotal = (float)($sales->sum('total') ?? 0);
            $salesDebt = (float)($sales->where('is_paid', false)->sum('total') ?? 0);
            $totalDebt = ($totalHospedaje - $abonoRealizado) + $salesDebt;
            $identification = $activeReservation->customer->taxProfile->identification ?? null;
        }

        $this->detailData = [
            'room' => $room,
            'reservation' => $activeReservation,
            'display_status' => $room->getDisplayStatus($this->date),
            'sales' => $sales->map(function($sale) {
                return [
                    'id' => $sale->id,
                    'product' => [
                        'name' => $sale->product->name ?? null,
                    ],
                    'quantity' => $sale->quantity ?? 0,
                    'is_paid' => (bool)($sale->is_paid ?? false),
                    'payment_method' => $sale->payment_method ?? null,
                    'total' => (float)($sale->total ?? 0),
                ];
            })->values()->toArray(),
            'payments_history' => $payments->map(function($payment) {
                return [
                    'id' => $payment->id,
                    'amount' => (float)($payment->amount ?? 0),
                    'method' => $payment->paymentMethod->name ?? null,
                    'created_at' => $payment->created_at,
                ];
            })->values()->toArray(),
            'total_hospedaje' => $totalHospedaje,
            'abono_realizado' => $abonoRealizado,
            'sales_total' => $salesTotal,
            'total_debt' => $totalDebt,
            'identification' => $identification,
            'stay_history' => $stayHistory,
            'deposit_history' => $payments->map(function($payment) {
                return [
                    'id' => $payment->id,
                    'amount' => (float)($payment->amount ?? 0),
                    'payment_method' => $payment->paymentMethod->name ?? 'N/A',
                    'notes' => $payment->notes ?? null,
                    'created_at' => $payment->created_at ? $payment->created_at->format('Y-m-d H:i') : null,
                ];
            })->values()->toArray(),
            'refunds_history' => [],
            'is_past_date' => $this->date->lt(now()->startOfDay()),
            'isHistoric' => $accessInfo['isHistoric'],
            'canModify' => $accessInfo['canModify'],
        ];

        $this->roomDetailModal = true;
    }

    public function closeRoomDetail()
    {
        $this->roomDetailModal = false;
        $this->detailData = null;
    }

    public function toggleAddSale(): void
    {
        $this->showAddSale = !$this->showAddSale;
        if ($this->showAddSale) {
            $this->newSale = [
                'product_id' => null,
                'quantity' => 1,
                'payment_method' => 'efectivo',
            ];
        } else {
            $this->newSale = null;
        }
    }

    public function toggleAddDeposit(): void
    {
        $this->showAddDeposit = !$this->showAddDeposit;
        if ($this->showAddDeposit) {
            $this->newDeposit = [
                'amount' => null,
                'payment_method' => 'efectivo',
                'notes' => null,
            ];
        } else {
            $this->newDeposit = null;
        }
    }

    public function addSale(): void
    {
        // Placeholder: integrate with Sales logic if/when available
        $this->dispatch('notify', type: 'error', message: 'Registrar consumo no está habilitado todavía.');
    }

    public function addDeposit(): void
    {
        // Placeholder: integrate with payments when ready
        $this->dispatch('notify', type: 'error', message: 'Agregar abono no está habilitado todavía.');
    }


    /**
     * Registra un pago en la tabla payments (Single Source of Truth).
     * 
     * @param int $reservationId ID de la reserva
     * @param float $amount Monto del pago
     * @param string $paymentMethod Método de pago ('efectivo' o 'transferencia')
     * @param string|null $bankName Nombre del banco (solo si es transferencia)
     * @param string|null $reference Referencia de pago (solo si es transferencia)
     */
    /**
     * Obtiene el contexto financiero de una reserva para mostrar en el modal de pago
     */
    public function getFinancialContext($reservationId)
    {
        try {
            $reservation = Reservation::with(['payments', 'sales'])->find($reservationId);
            if (!$reservation) {
                return null;
            }

            $paymentsTotal = (float)($reservation->payments()->sum('amount') ?? 0);
            $salesDebt = (float)($reservation->sales?->where('is_paid', false)->sum('total') ?? 0);
            $totalAmount = (float)($reservation->total_amount ?? 0);
            $balanceDue = $totalAmount - $paymentsTotal + $salesDebt;

            return [
                'totalAmount' => $totalAmount,
                'paymentsTotal' => $paymentsTotal,
                'balanceDue' => max(0, $balanceDue),
            ];
        } catch (\Exception $e) {
            \Log::error('Error getting financial context', [
                'reservation_id' => $reservationId,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    #[On('register-payment')]
    public function handleRegisterPayment($reservationId, $amount, $paymentMethod, $bankName = null, $reference = null)
    {
        $this->registerPayment($reservationId, $amount, $paymentMethod, $bankName, $reference);
    }

    public function registerPayment($reservationId, $amount, $paymentMethod, $bankName = null, $reference = null)
    {
        try {
            $reservation = Reservation::find($reservationId);
            if (!$reservation) {
                $this->dispatch('notify', type: 'error', message: 'Reserva no encontrada.');
                return;
            }

            // Validar método de pago
            if (!in_array($paymentMethod, ['efectivo', 'transferencia'])) {
                $this->dispatch('notify', type: 'error', message: 'Método de pago inválido.');
                return;
            }

            // Validar monto
            $amount = (float)$amount;
            if ($amount <= 0) {
                $this->dispatch('notify', type: 'error', message: 'El monto debe ser mayor a 0.');
                return;
            }

            // Obtener balance antes del pago para determinar el mensaje
            $paymentsTotalBefore = (float)($reservation->payments()->sum('amount') ?? 0);
            $salesDebt = (float)($reservation->sales?->where('is_paid', false)->sum('total') ?? 0);
            $totalAmount = (float)($reservation->total_amount ?? 0);
            $balanceDueBefore = $totalAmount - $paymentsTotalBefore + $salesDebt;

            // Validar que el monto no exceda el saldo pendiente
            if ($amount > $balanceDueBefore) {
                $this->dispatch('notify', type: 'error', message: "El monto no puede ser mayor al saldo pendiente (\${$balanceDueBefore}).");
                return;
            }

            // Obtener ID del método de pago
            $paymentMethodId = $this->getPaymentMethodId($paymentMethod) ?? DB::table('payments_methods')
                ->where('name', 'Efectivo')
                ->orWhere('code', 'cash')
                ->value('id');

            if (!$paymentMethodId) {
                $this->dispatch('notify', type: 'error', message: 'Método de pago no encontrado en el sistema.');
                return;
            }

            // Crear el pago en la tabla payments (SSOT)
            Payment::create([
                'reservation_id' => $reservation->id,
                'amount' => $amount,
                'payment_method_id' => $paymentMethodId,
                'bank_name' => $paymentMethod === 'transferencia' ? ($bankName ?: null) : null,
                'reference' => $paymentMethod === 'transferencia' ? ($reference ?: null) : 'Pago registrado',
                'paid_at' => now(),
                'created_by' => auth()->id(),
            ]);

            // Recalcular balance_due de la reserva
            $paymentsTotal = (float)($reservation->payments()->sum('amount') ?? 0);
            $balanceDue = $totalAmount - $paymentsTotal + $salesDebt;

            // Actualizar estado de pago de la reserva
            $paymentStatusCode = $balanceDue <= 0 ? 'paid' : ($paymentsTotal > 0 ? 'partial' : 'pending');
            $paymentStatusId = DB::table('payment_statuses')->where('code', $paymentStatusCode)->value('id');

            $reservation->update([
                'balance_due' => max(0, $balanceDue),
                'payment_status_id' => $paymentStatusId,
            ]);

            // Mensaje específico según el tipo de pago
            if ($balanceDue <= 0) {
                $this->dispatch('notify', type: 'success', message: 'Pago registrado. Cuenta al día.');
            } else {
                $formattedBalance = number_format($balanceDue, 0, ',', '.');
                $this->dispatch('notify', type: 'success', message: "Abono registrado. Saldo pendiente: \${$formattedBalance}");
            }

            $this->dispatch('refreshRooms');
            
            // Cerrar el modal de pago si está abierto
            $this->dispatch('close-payment-modal');
            $this->dispatch('payment-registered');
            
            // Recargar datos del modal si está abierto
            if ($this->roomDetailModal && $this->detailData && isset($this->detailData['reservation']['id']) && $this->detailData['reservation']['id'] == $reservationId) {
                // Obtener el room_id desde reservation_rooms
                $reservationRoom = $reservation->reservationRooms()->first();
                if ($reservationRoom && $reservationRoom->room_id) {
                    $this->openRoomDetail($reservationRoom->room_id);
                }
            }
        } catch (\Exception $e) {
            $this->dispatch('notify', type: 'error', message: 'Error al registrar pago: ' . $e->getMessage());
            \Log::error('Error registering payment', [
                'reservation_id' => $reservationId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    public function openQuickRent($roomId)
    {
        $room = Room::with('rates')->find($roomId);
        if ($room) {
            // Calculate base price from rates or fallback to base_price_per_night
            $basePrice = 0;
            if ($room->rates && $room->rates->isNotEmpty()) {
                $firstRate = $room->rates->sortBy('min_guests')->first();
                $basePrice = $firstRate->price_per_night ?? 0;
            }
            if ($basePrice == 0 && $room->base_price_per_night) {
                $basePrice = $room->base_price_per_night;
            }

            $this->rentForm = [
                'room_id' => $roomId,
                'room_number' => $room->room_number,
                'check_in_date' => $this->date->toDateString(),
                'check_out_date' => $this->date->copy()->addDay()->toDateString(),
                'client_id' => null,
                'guests_count' => 1,
                'max_capacity' => $room->max_capacity,
                'total' => $basePrice,
                'deposit' => 0,
                'payment_method' => 'efectivo',
                    'bank_name' => '', // Opcional para transferencias
                    'reference' => '', // Opcional para transferencias
            ];
            $this->additionalGuests = [];
            $this->quickRentModal = true;
            $this->dispatch('quickRentOpened');
                $this->recalculateQuickRentTotals($room);
        }
    }

    public function closeQuickRent()
    {
        $this->quickRentModal = false;
        $this->rentForm = null;
        $this->additionalGuests = null;
    }

    public function updatedRentFormCheckOutDate($value): void
    {
        $this->rentForm['check_out_date'] = $value;
        $this->recalculateQuickRentTotals();
    }

    public function updatedRentFormClientId($value): void
    {
        $this->rentForm['client_id'] = $value;
        $this->recalculateQuickRentTotals();
    }

    public function addGuestFromCustomerId($customerId)
    {
        $customer = \App\Models\Customer::find($customerId);
        
        if (!$customer) {
            $this->dispatch('notify', type: 'error', message: 'Cliente no encontrado.');
            return;
        }

        $room = null;
        if (!empty($this->rentForm['room_id'])) {
            $room = Room::with('rates')->find($this->rentForm['room_id']);
        }

        // Check if already added
        if (is_array($this->additionalGuests)) {
            foreach ($this->additionalGuests as $guest) {
                if (isset($guest['customer_id']) && $guest['customer_id'] == $customerId) {
                    $this->dispatch('notify', type: 'error', message: 'Este cliente ya fue agregado como huésped adicional.');
                    return;
                }
            }
        } else {
            $this->additionalGuests = [];
        }

        // Add guest
        $this->additionalGuests[] = [
            'customer_id' => $customer->id,
            'name' => $customer->name,
            'identification' => $customer->taxProfile?->identification ?? 'N/A',
        ];

        $this->dispatch('guest-added');
        $this->dispatch('notify', type: 'success', message: 'Huésped adicional agregado.');

        // Recalcular total y contador de huéspedes
        $this->recalculateQuickRentTotals($room);
    }

    public function removeGuest($index)
    {
        if (isset($this->additionalGuests[$index])) {
            unset($this->additionalGuests[$index]);
            $this->additionalGuests = array_values($this->additionalGuests);
            $this->dispatch('notify', type: 'success', message: 'Huésped removido.');
            $this->recalculateQuickRentTotals();
        }
    }

    public function submitQuickRent()
    {
        if (!$this->rentForm) {
            return;
        }

        try {
            $paymentMethod = $this->rentForm['payment_method'] ?? 'efectivo';
            $bankName = $paymentMethod === 'transferencia' ? trim($this->rentForm['bank_name'] ?? '') : null;
            $reference = $paymentMethod === 'transferencia' ? trim($this->rentForm['reference'] ?? '') : null;

            // BLOQUEO: Verificar si es fecha histórica
            if (Carbon::parse($this->rentForm['check_in_date'])->lt(Carbon::today())) {
                throw new \RuntimeException('No se pueden crear reservas en fechas históricas.');
            }

            $validated = [
                'room_id' => $this->rentForm['room_id'],
                'check_in_date' => $this->rentForm['check_in_date'],
                'check_out_date' => $this->rentForm['check_out_date'],
                'client_id' => $this->rentForm['client_id'],
                'guests_count' => $this->rentForm['guests_count'],
            ];

            $room = Room::with('rates')->find($validated['room_id']);
            if (!$room) {
                throw new \RuntimeException('Habitación no encontrada');
            }

            $guests = $this->calculateGuestCount();
            $this->rentForm['guests_count'] = $guests;
            $validated['guests_count'] = $guests;

            $checkIn = Carbon::parse($validated['check_in_date']);
            $checkOut = Carbon::parse($validated['check_out_date']);
            $nights = max(1, $checkIn->diffInDays($checkOut));

            $pricePerNight = $this->findRateForGuests($room, $guests);
            $totalAmount = $pricePerNight * $nights;
            $depositAmount = (float)($this->rentForm['deposit'] ?? 0); // Del formulario
            $balanceDue = $totalAmount - $depositAmount;

            $paymentStatusCode = $balanceDue <= 0 ? 'paid' : ($depositAmount > 0 ? 'partial' : 'pending');
            $paymentStatusId = DB::table('payment_statuses')->where('code', $paymentStatusCode)->value('id');

            $reservationCode = sprintf('RSV-%s-%s', now()->format('YmdHis'), Str::upper(Str::random(4)));

            // ===== PASO 1: Crear reserva técnica para walk-in =====
            $reservation = Reservation::create([
                'reservation_code' => $reservationCode,
                'client_id' => $validated['client_id'],
                'status_id' => 1, // pending
                'total_guests' => $validated['guests_count'],
                'adults' => $validated['guests_count'],
                'children' => 0,
                'total_amount' => $totalAmount,
                'deposit_amount' => $depositAmount,
                'balance_due' => $balanceDue,
                'payment_status_id' => $paymentStatusId,
                'source_id' => 1, // reception / walk_in
                'created_by' => auth()->id(),
            ]);

            // Registrar trazabilidad de transferencia (informativa)
            if ($paymentMethod === 'transferencia') {
                $referencePayload = null;
                if ($reference && $bankName) {
                    $referencePayload = sprintf('%s | Banco: %s', $reference, $bankName);
                } elseif ($reference) {
                    $referencePayload = $reference;
                } elseif ($bankName) {
                    $referencePayload = sprintf('Banco: %s', $bankName);
                }

                if ($depositAmount > 0 || $referencePayload) {
                    DB::table('payments')->insert([
                        'reservation_id' => $reservation->id,
                        'amount' => $depositAmount > 0 ? $depositAmount : 0,
                        'payment_method_id' => $this->getPaymentMethodId('transferencia'),
                        'payment_type_id' => null,
                        'source_id' => null,
                        'reference' => $referencePayload,
                        'paid_at' => now(),
                        'created_by' => auth()->id(),
                        'created_at' => now(),
                    ]);
                }
            }

            // ===== PASO 2: Crear reservation_room =====
            ReservationRoom::create([
                'reservation_id' => $reservation->id,
                'room_id' => $validated['room_id'],
                'check_in_date' => $validated['check_in_date'],
                'check_out_date' => $validated['check_out_date'],
                'nights' => $nights,
                'price_per_night' => $pricePerNight,
            ]);

            // ===== PASO 3: CRÍTICO - Crear STAY activa AHORA (check-in inmediato) =====
            // Una stay activa es lo que marca que la habitación está OCUPADA
            \App\Models\Stay::create([
                'reservation_id' => $reservation->id,
                'room_id' => $validated['room_id'],
                'check_in_at' => now(), // Check-in INMEDIATO (timestamp)
                'check_out_at' => null, // Se completará al checkout
                'status' => 'active', // estados: active, pending_checkout, finished
            ]);

            // ÉXITO: Habitación ahora debe aparecer como OCUPADA
            $this->dispatch('notify', type: 'success', message: 'Arriendo registrado exitosamente. Habitación ocupada.');
            $this->closeQuickRent();
        } catch (\Exception $e) {
            $this->dispatch('notify', type: 'error', message: 'Error: ' . $e->getMessage());
        }
    }

    public function storeQuickRent()
    {
        return $this->submitQuickRent();
    }

    public function openRoomEdit($roomId)
    {
        $room = Room::with(['roomType', 'ventilationType', 'rates'])->find($roomId);
        if ($room) {
            $this->roomEditData = [
                'room' => $room,
                'ventilation_types' => $this->ventilationTypes,
                'statuses' => $this->statuses,
                'isOccupied' => $room->isOccupied(),
            ];
            $this->roomEditModal = true;
        }
    }

    public function closeRoomEdit()
    {
        $this->roomEditModal = false;
        $this->roomEditData = null;
    }

    public function openReleaseHistoryDetail($roomId)
    {
        $room = Room::find($roomId);
        if ($room) {
            $this->releaseHistoryDetail = [
                'room' => $room,
                'history' => collect([]), // Se implementará cuando exista la tabla
            ];
            $this->releaseHistoryDetailModal = true;
        }
    }

    public function closeReleaseHistoryDetail()
    {
        $this->releaseHistoryDetailModal = false;
        $this->releaseHistoryDetail = null;
    }

    public function openRoomReleaseConfirmation($roomId)
    {
        $room = Room::find($roomId);
        if ($room && $room->isOccupied()) {
            $this->detailData = [
                'room' => $room,
                'reservation' => $room->getActiveReservation($this->date),
            ];
            $this->roomReleaseConfirmationModal = true;
        }
    }

    public function closeRoomReleaseConfirmation()
    {
        $this->roomReleaseConfirmationModal = false;
        $this->detailData = null;
        $this->dispatch('close-room-release-modal');
    }

    public function loadRoomReleaseData($roomId, $isCancellation = false)
    {
        $room = Room::with([
            'reservationRooms.reservation' => function($q) {
                $q->with(['customer', 'sales.product', 'payments']);
            }
        ])->find($roomId);

        if (!$room) {
            return [
                'room_id' => $roomId,
                'room_number' => null,
                'reservation' => null,
                'sales' => [],
                'payments_history' => [],
                'refunds_history' => [],
                'total_hospedaje' => 0,
                'abono_realizado' => 0,
                'sales_total' => 0,
                'total_debt' => 0,
                'identification' => null,
                'is_cancellation' => $isCancellation,
            ];
        }

        $activeReservation = $room->getActiveReservation($this->date ?? now());
        $sales = collect();

        $totalHospedaje = 0;
        $abonoRealizado = 0;
        $salesTotal = 0;
        $totalDebt = 0;
        $payments = collect();
        $identification = null;

        if ($activeReservation) {
            $sales = $activeReservation->sales ?? collect();
            $payments = $activeReservation->payments ?? collect();

            $totalHospedaje = (float)($activeReservation->total_amount ?? 0);
            $abonoRealizado = (float)($activeReservation->deposit_amount ?? 0);
            $salesTotal = (float)($sales->sum('total') ?? 0);
            $salesDebt = (float)($sales->where('is_paid', false)->sum('total') ?? 0);
            $totalDebt = ($totalHospedaje - $abonoRealizado) + $salesDebt;
            $identification = $activeReservation->customer->taxProfile->identification ?? null;
        }

        return [
            'room_id' => $room->id,
            'room_number' => $room->room_number,
            'reservation' => $activeReservation ? $activeReservation->toArray() : null,
            'sales' => $sales->map(function($sale) {
                return [
                    'id' => $sale->id,
                    'product' => [
                        'name' => $sale->product->name ?? null,
                    ],
                    'quantity' => $sale->quantity ?? 0,
                    'is_paid' => (bool)($sale->is_paid ?? false),
                    'payment_method' => $sale->payment_method ?? null,
                    'total' => (float)($sale->total ?? 0),
                ];
            })->values()->toArray(),
            'payments_history' => $payments->map(function($payment) {
                return [
                    'id' => $payment->id,
                    'amount' => (float)($payment->amount ?? 0),
                    'method' => $payment->method ?? null,
                    'created_at' => $payment->created_at,
                ];
            })->values()->toArray(),
            'refunds_history' => [],
            'total_hospedaje' => $totalHospedaje,
            'abono_realizado' => $abonoRealizado,
            'sales_total' => $salesTotal,
            'total_debt' => $totalDebt,
            'identification' => $identification,
            'cancel_url' => null,
            'is_cancellation' => $isCancellation,
        ];
    }

    public function updateCleaningStatus($roomId, $status)
    {
        try {
            $room = Room::find($roomId);
            
            if (!$room) {
                $this->dispatch('notify', type: 'error', message: 'Habitación no encontrada.');
                return;
            }

            // Update cleaning status based on the status parameter
            if ($status === 'limpia') {
                $room->last_cleaned_at = now();
                $room->save();
                $this->dispatch('notify', type: 'success', message: 'Habitación marcada como limpia.');
            } elseif ($status === 'pendiente') {
                $room->last_cleaned_at = null;
                $room->save();
                $this->dispatch('notify', type: 'success', message: 'Habitación marcada como pendiente de limpieza.');
            }
        } catch (\Exception $e) {
            $this->dispatch('notify', type: 'error', message: 'Error al actualizar estado de limpieza: ' . $e->getMessage());
        }
    }

    public function confirmReleaseRoom($roomId)
    {
        // Implementar lógica de liberación de habitación
        try {
            $room = Room::find($roomId);
            if ($room && $room->isOccupied()) {
                // Realizar checkout y liberar habitación
                $this->dispatch('notify', type: 'success', message: 'Habitación liberada exitosamente.');
                $this->closeRoomReleaseConfirmation();
            }
        } catch (\Exception $e) {
            $this->dispatch('notify', type: 'error', message: 'Error: ' . $e->getMessage());
        }
    }

    /**
     * Libera la habitación (checkout).
     * 
     * Flujo:
     * 1. Si hay deuda pendiente:
     *    - Valida que el usuario haya confirmado (en frontend)
     *    - Registra un pago para saldarlo
     * 2. Cierra el stay usando getStayForDate(today)
     * 3. Actualiza el estado de la reserva
     * 
     * REGLA: Solo libera si el balance queda en 0
     * 
     * @param int $roomId
     * @param string|null $status Ej: 'libre'
     */
    public function releaseRoom($roomId, $status = null, $paymentMethod = null, $bankName = null, $reference = null)
    {
        $started = false;
        try {
            $this->isReleasingRoom = true;
            $room = Room::find($roomId);
            if (!$room) {
                $this->dispatch('notify', type: 'error', message: 'Habitación no encontrada.');
                $this->isReleasingRoom = false;
                return;
            }

            $this->dispatch('room-release-start', roomId: $roomId);
            $started = true;

            $availabilityService = $room->getAvailabilityService();
            $today = Carbon::today();
            
            // BLOQUEO: No se puede liberar ocupaciones históricas
            if ($availabilityService->isHistoricDate($today)) {
                $this->dispatch('notify', type: 'error', message: 'No se pueden hacer cambios en fechas históricas.');
                if ($started) {
                    $this->dispatch('room-release-finished', roomId: $roomId);
                }
                return;
            }

            // ===== PASO 1: Obtener el stay que intersecta HOY =====
            $activeStay = $availabilityService->getStayForDate($today);

            if (!$activeStay) {
                $this->dispatch('notify', type: 'info', message: 'No hay ocupación activa para liberar hoy.');
                if ($started) {
                    $this->dispatch('room-release-finished', roomId: $roomId);
                }
                $this->closeRoomReleaseConfirmation();
                return;
            }

            // ===== PASO 2: Obtener reserva y calcular deuda =====
            $reservation = $activeStay->reservation;
            if (!$reservation) {
                $this->dispatch('notify', type: 'error', message: 'La ocupación no tiene reserva asociada.');
                if ($started) {
                    $this->dispatch('room-release-finished', roomId: $roomId);
                }
                return;
            }

            // Calcular deuda pendiente
            $paymentsTotal = (float)($reservation->payments?->sum('amount') ?? 0);
            $salesDebt = (float)($reservation->sales?->where('is_paid', false)->sum('total') ?? 0);
            $balanceDue = (float)($reservation->total_amount ?? 0) - $paymentsTotal + $salesDebt;

            // ===== PASO 3: Si hay deuda, registrar un pago para saldarlo =====
            if ($balanceDue > 0) {
                // Requiere datos de pago desde frontend
                if (!$paymentMethod) {
                    $this->dispatch('notify', type: 'error', message: 'Debe seleccionar un método de pago.');
                    if ($started) {
                        $this->dispatch('room-release-finished', roomId: $roomId);
                    }
                    return;
                }

                $paymentMethodId = $this->getPaymentMethodId($paymentMethod) ?? DB::table('payments_methods')
                    ->where('name', 'Efectivo')
                    ->orWhere('code', 'cash')
                    ->value('id');

                Payment::create([
                    'reservation_id' => $reservation->id,
                    'amount' => $balanceDue,
                    'payment_method_id' => $paymentMethodId,
                    'bank_name' => $paymentMethod === 'transferencia' ? ($bankName ?: null) : null,
                    'reference' => $paymentMethod === 'transferencia' ? ($reference ?: null) : 'Pago confirmado en checkout',
                    'paid_at' => now(),
                    'created_by' => auth()->id(),
                ]);

                // Recalcular deuda después del pago
                $paymentsTotal += $balanceDue;
                $balanceDue = 0;
            }

            // ===== PASO 4: Validar que balance sea 0 antes de liberar =====
            if ($balanceDue != 0) {
                $this->dispatch('notify', type: 'error', message: "No se puede liberar. Deuda pendiente: \${$balanceDue}");
                if ($started) {
                    $this->dispatch('room-release-finished', roomId: $roomId);
                }
                return;
            }

            // ===== PASO 5: Cerrar la STAY =====
            $activeStay->update([
                'check_out_at' => now(),
                'status' => 'finished',
            ]);

            // ===== PASO 6: Actualizar estado de la reserva =====
            $reservation->balance_due = 0;
            $reservation->payment_status_id = DB::table('payment_statuses')
                ->where('code', 'paid')
                ->value('id');
            $reservation->save();

            $this->dispatch('notify', type: 'success', message: 'Habitación liberada correctamente.');
            if ($started) {
                $this->dispatch('room-release-finished', roomId: $roomId);
            }
            $this->isReleasingRoom = false;
            $this->closeRoomReleaseConfirmation();
            $this->dispatch('refreshRooms');
        } catch (\Exception $e) {
            if ($started) {
                $this->dispatch('room-release-finished', roomId: $roomId);
            }
            $this->isReleasingRoom = false;
            $this->dispatch('notify', type: 'error', message: 'Error al liberar habitación: ' . $e->getMessage());
            \Log::error('Error releasing room: ' . $e->getMessage(), ['exception' => $e]);
        }
    }

    public function render()
    {
        $rooms = $this->getRoomsQuery()->paginate(30);

        // Enriquecer rooms con estados y deudas
        $rooms->getCollection()->transform(function($room) {
            $room->display_status = $room->getDisplayStatus($this->date);
            $room->current_reservation = $room->getActiveReservation($this->date);
            if ($room->current_reservation) {
                $room->current_reservation->loadMissing(['customer']);
            }

            if ($room->current_reservation) {
                $reservationRoom = $room->reservationRooms?->first(function($rr) {
                    return $rr->check_in_date <= $this->date->toDateString()
                        && $rr->check_out_date >= $this->date->toDateString();
                });

                $checkIn = $reservationRoom?->check_in_date ? Carbon::parse($reservationRoom->check_in_date) : null;
                $checkOut = $reservationRoom?->check_out_date ? Carbon::parse($reservationRoom->check_out_date) : null;

                $nights = 0;
                if ($checkIn && $checkOut) {
                    $nights = max(1, $checkIn->diffInDays($checkOut));
                }

                $pricePerNight = (float)($reservationRoom->price_per_night ?? 0);
                if ($pricePerNight === 0 && $room->current_reservation->total_amount && $nights > 0) {
                    $pricePerNight = (float)$room->current_reservation->total_amount / $nights;
                }
                if ($pricePerNight === 0 && $room->rates && $room->rates->isNotEmpty()) {
                    $pricePerNight = (float)($room->rates->sortBy('min_guests')->first()->price_per_night ?? 0);
                }
                if ($pricePerNight === 0) {
                    $pricePerNight = (float)($room->base_price_per_night ?? 0);
                }

                $paymentsTotal = (float)($room->current_reservation->payments?->sum('amount') ?? 0);

                // Nights consumed up to current date (inclusive of current night if within range)
                $nightsConsumed = 0;
                if ($checkIn && $checkOut && $this->date) {
                    if ($this->date->lt($checkIn)) {
                        $nightsConsumed = 0;
                    } elseif ($this->date->gte($checkOut)) {
                        $nightsConsumed = $nights;
                    } else {
                        $nightsConsumed = max(1, $checkIn->diffInDays($this->date->copy()->addDay()));
                    }
                }

                $expectedPaidUntilToday = $pricePerNight * $nightsConsumed;
                $room->is_night_paid = $expectedPaidUntilToday > 0
                    ? $paymentsTotal >= $expectedPaidUntilToday
                    : false;

                $totalStay = $pricePerNight * $nights;
                if ($totalStay <= 0 && $room->current_reservation->total_amount) {
                    $totalStay = (float)$room->current_reservation->total_amount;
                }

                // Prefer stored balance_due (source of truth) when present
                $storedBalance = $room->current_reservation->balance_due;

                $sales_debt = 0;
                if ($room->current_reservation->sales) {
                    $sales_debt = (float)$room->current_reservation->sales->where('is_paid', false)->sum('total');
                }
                $computedDebt = ($totalStay - $paymentsTotal) + $sales_debt;
                $room->total_debt = $storedBalance !== null ? (float)$storedBalance + $sales_debt : $computedDebt;
            } else {
                $room->total_debt = 0;
                $room->is_night_paid = true;
            }
            
            return $room;
        });

        // Aplicar filtro de estado si existe (después de enriquecer)
        if ($this->statusFilter) {
            $rooms->setCollection(
                $rooms->getCollection()->filter(function($room) {
                    return $room->display_status === $this->statusFilter;
                })
            );
        }

        return view('livewire.room-manager', [
            'daysInMonth' => $this->daysInMonth,
            'currentDate' => $this->currentDate,
            'rooms' => $rooms,
        ]);
    }
}
