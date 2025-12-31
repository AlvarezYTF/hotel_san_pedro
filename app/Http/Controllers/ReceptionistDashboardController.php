<?php

namespace App\Http\Controllers;

use App\Enums\ShiftHandoverStatus;
use App\Enums\ShiftStatus;
use App\Enums\ShiftType;
use App\Models\Shift;
use App\Models\Room;
use App\Models\Sale;
use App\Models\ShiftHandover;
use App\Services\ShiftLifecycleService;
use App\Services\ShiftSnapshotBuilder;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use App\Models\AuditLog;
use App\Models\ShiftCashOut;
use App\Models\Reservation;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Barryvdh\DomPDF\Facade\Pdf;

class ReceptionistDashboardController extends Controller
{
    public function __construct(
        private readonly ShiftLifecycleService $shiftLifecycle,
        private readonly ShiftSnapshotBuilder $snapshotBuilder
    ) {
    }

    public function index()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        $query = ShiftHandover::with(['entregadoPor', 'recibidoPor'])->orderBy('created_at', 'desc');

        // Recepcionistas: solo sus propios turnos. Admin: todos.
        if (!$user->hasRole('Administrador')) {
            $query->where(function ($q) use ($user) {
                $q->where('entregado_por', $user->id)->orWhere('recibido_por', $user->id);
            });
        }

        $handovers = $query->paginate(15);
        return view('shift-handovers.index', compact('handovers'));
    }

    public function show($id)
    {
        $handover = ShiftHandover::with(['entregadoPor', 'recibidoPor', 'sales', 'cashOutflows', 'cashOuts'])->findOrFail($id);

        /** @var \App\Models\User $user */
        $user = Auth::user();
        if (!$user->hasRole('Administrador') && !in_array($user->id, [(int) $handover->entregado_por, (int) $handover->recibido_por], true)) {
            abort(403);
        }

        return view('shift-handovers.show', compact('handover'));
    }

    public function downloadHandoverPdf($id)
    {
        $handover = ShiftHandover::with(['entregadoPor', 'recibidoPor', 'sales', 'cashOutflows', 'cashOuts'])->findOrFail($id);

        /** @var \App\Models\User $user */
        $user = Auth::user();
        if (!$user->hasRole('Administrador') && !in_array($user->id, [(int) $handover->entregado_por, (int) $handover->recibido_por], true)) {
            abort(403);
        }

        $fileName = "Turno_{$handover->id}_{$handover->shift_date->format('Y-m-d')}.pdf";

        $pdf = Pdf::loadView('shift-handovers.pdf', [
            'handover' => $handover,
            'tolerance' => (float) config('shifts.difference_tolerance', 0),
        ])->setPaper('a4', 'portrait');

        return $pdf->download($fileName);
    }

    public function day()
    {
        return $this->renderDashboard(ShiftType::DAY);
    }

    public function night()
    {
        return $this->renderDashboard(ShiftType::NIGHT);
    }

    private function renderDashboard(ShiftType $type)
    {
        $user = Auth::user();
        $today = Carbon::today();

        // 1. Detect active or pending shift
        $activeShift = ShiftHandover::where('entregado_por', $user->id)
            ->where('status', ShiftHandoverStatus::ACTIVE)
            ->first();

        $pendingReception = ShiftHandover::where('recibido_por', $user->id)
            ->where('status', ShiftHandoverStatus::DELIVERED)
            ->first();

        if (!$pendingReception) {
            $pendingReception = ShiftHandover::whereNull('recibido_por')
                ->where('status', ShiftHandoverStatus::DELIVERED)
                ->first();
        }

        // 2. Summary of sales for the active shift
        $salesSummary = [
            'total' => 0,
            'cash' => 0,
            'transfer' => 0,
        ];

        if ($activeShift) {
            $activeShift->updateTotals();

            $salesSummary['total'] = $activeShift->sales->sum('total');
            $salesSummary['cash'] = $activeShift->total_entradas_efectivo;
            $salesSummary['transfer'] = $activeShift->total_entradas_transferencia;
        }

        // 3. Room status summary
        $roomsSummary = [
            'occupied' => Room::where('status', 'ocupada')->count(),
            'available' => Room::where('status', 'disponible')->count(),
            'dirty' => Room::where('status', 'sucia')->count(),
            'cleaning' => Room::where('status', 'limpieza')->count(),
        ];

        // 4. Alerts
        $alerts = [];

        // Pending reception alert
        if ($pendingReception) {
            $alerts[] = [
                'type' => 'warning',
                'title' => 'Turno pendiente de recibir',
                'message' => 'Tienes un turno entregado que aún no has recibido formalmente.',
                'link' => route('shift-handovers.receive'),
                'link_text' => 'Recibir ahora'
            ];
        }

        // Dirty rooms alert
        $dirtyRoomsCount = Room::whereIn('status', [\App\Enums\RoomStatus::SUCIA, \App\Enums\RoomStatus::PENDIENTE_ASEO])->count();
        if ($dirtyRoomsCount > 0) {
            $alerts[] = [
                'type' => 'danger',
                'title' => 'Habitaciones sucias',
                'message' => "Hay {$dirtyRoomsCount} habitaciones que requieren limpieza.",
                'link' => route('rooms.index', ['status' => 'sucia']),
                'link_text' => 'Ver habitaciones'
            ];
        }

        // Pending check-ins today
        $pendingCheckIns = Reservation::whereDate('check_in_date', $today)->count();
        if ($pendingCheckIns > 0) {
            $alerts[] = [
                'type' => 'info',
                'title' => 'Check-ins para hoy',
                'message' => "Hay {$pendingCheckIns} ingresos programados para el día de hoy.",
                'link' => route('reservations.index', ['date' => $today->toDateString()]),
                'link_text' => 'Ver reservas'
            ];
        }

        // Pending check-outs today
        $pendingCheckOuts = Reservation::whereDate('check_out_date', $today)->count();
        if ($pendingCheckOuts > 0) {
            $alerts[] = [
                'type' => 'info',
                'title' => 'Check-outs para hoy',
                'message' => "Hay {$pendingCheckOuts} salidas programadas para el día de hoy.",
                'link' => route('reservations.index', ['date' => $today->toDateString()]),
                'link_text' => 'Ver reservas'
            ];
        }

        // 5. Last 5 cash outflows
        $lastOutflows = \App\Models\CashOutflow::where('user_id', $user->id)
            ->orderBy('date', 'desc')
            ->take(5)
            ->get();

        $operationalShift = Shift::openOperational()
            ->with('openedBy')
            ->first();

        $view = $type === ShiftType::DAY ? 'dashboards.receptionist-day' : 'dashboards.receptionist-night';

        return view($view, compact(
            'user',
            'activeShift',
            'pendingReception',
            'salesSummary',
            'roomsSummary',
            'lastOutflows',
            'alerts',
            'operationalShift'
        ));
    }

    public function startShift(Request $request)
    {
        $user = Auth::user();
        $type = ShiftType::from($request->input('shift_type'));

        // Bloquear si ya hay un turno operativo abierto (aunque sea de otro usuario)
        if (Shift::openOperational()->exists()) {
            return back()->with('error', 'Ya existe un turno operativo abierto. Recibe el turno pendiente o pide a un administrador forzarlo.');
        }

        // Base inicial por defecto (configurable)
        $defaultBase = (float) config('shifts.default_initial_base', 0);

        // Limpiar el formato de miles (puntos) antes de guardar
        $baseInicialRaw = $request->input('base_inicial', (string) $defaultBase);
        $baseInicial = str_replace('.', '', (string) $baseInicialRaw);
        $baseInicial = (float) str_replace(',', '.', $baseInicial);

        // Crear shift operativo garantizando unicidad global
        $baseSnapshot = [
            'shift' => [
                'type' => $type->value,
                'date' => Carbon::today()->toDateString(),
            ],
            'cash' => [
                'base_inicial' => $baseInicial,
                'entradas_efectivo' => 0.0,
                'entradas_transferencia' => 0.0,
                'salidas' => 0.0,
                'base_esperada' => $baseInicial,
            ],
            'meta' => [
                'captured_at' => Carbon::now()->toIso8601String(),
            ],
        ];

        try {
            $shift = $this->shiftLifecycle->openFresh($user, $type, $baseSnapshot);
        } catch (\Throwable $e) {
            $message = $e instanceof \App\Exceptions\ShiftRuleViolation ? $e->getMessage() : 'No se pudo iniciar el turno.';
            return back()->with('error', $message)->withInput();
        }

        ShiftHandover::create([
            'from_shift_id' => $shift->id,
            'entregado_por' => $user->id,
            'shift_type' => $type,
            'shift_date' => Carbon::today(),
            'started_at' => Carbon::now(),
            'base_inicial' => $baseInicial,
            'status' => ShiftHandoverStatus::ACTIVE,
        ]);

        $this->auditLog('shift_start', "Usuario {$user->username} inició turno {$type}", [
            'shift_type' => $type,
            'base_inicial' => $baseInicial
        ]);

        return back()->with('success', 'Turno iniciado correctamente.');
    }

    public function createHandover()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $activeShift = $user->turnoActivo()->first();
        $operationalShift = Shift::openOperational()->first();
        if (!$activeShift || !$operationalShift || (int) ($activeShift->from_shift_id ?? $activeShift->id) !== (int) $operationalShift->id) {
            return back()->with('error', 'Debe haber un turno operativo abierto para registrar una salida de caja.');
        }

        if (!$activeShift) {
            return redirect()->route('shift-handovers.index')->with('error', 'No tienes un turno activo para entregar.');
        }

        $receivers = User::query()
            ->whereHas('roles', function ($q) {
                $q->whereIn('name', ['Recepcionista Día', 'Recepcionista Noche']);
            })
            ->where('id', '!=', $user->id)
            ->orderBy('name')
            ->get();

        return view('shift-handovers.create', compact('activeShift', 'receivers'));
    }

    public function storeHandover(Request $request)
    {
        // Reusar la lógica de entrega del turno (endShift) pero con más campos.
        return $this->endShift($request);
    }

    public function endShift(Request $request)
    {
        $user = Auth::user();
        $activeShift = $user->turnoActivo()->first();

        if (!$activeShift) {
            return back()->with('error', 'No tienes un turno activo.');
        }

        $request->validate([
            'base_final' => 'nullable',
            'observaciones' => 'nullable|string|max:2000',
            'recibido_por' => 'nullable|integer|exists:users,id',
        ]);

        // Sanitize base_final
        $baseFinal = $request->input('base_final');
        if (is_string($baseFinal)) {
            $baseFinal = str_replace('.', '', $baseFinal);
            $baseFinal = (float) str_replace(',', '.', $baseFinal);
        }

        $activeShift->updateTotals();
        $activeShift->status = ShiftHandoverStatus::DELIVERED;
        $activeShift->ended_at = Carbon::now();
        $activeShift->observaciones_entrega = $request->input('observaciones');
        $activeShift->base_final = $baseFinal ?? $activeShift->base_esperada;

        // Opcional: asignar el receptor desde la entrega
        $receiverId = $request->input('recibido_por');
        if ($receiverId) {
            $receiver = User::find((int) $receiverId);
            if ($receiver && ($receiver->hasRole('Recepcionista Día') || $receiver->hasRole('Recepcionista Noche'))) {
                $activeShift->recibido_por = $receiver->id;
            }
        }

        // Vincular shift operativo y cerrar con snapshot inmutable
        $shift = $activeShift->from_shift_id ? Shift::find($activeShift->from_shift_id) : Shift::openOperational()->first();
        if (!$shift) {
            return back()->with('error', 'No se encontró el turno operativo asociado.');
        }

        $closingSnapshot = $this->snapshotBuilder->closingFromHandover($activeShift);
        try {
            $this->shiftLifecycle->closeWithSnapshot($shift, $user, $closingSnapshot);
        } catch (\Throwable $e) {
            $message = $e instanceof \App\Exceptions\ShiftRuleViolation ? $e->getMessage() : 'No se pudo cerrar el turno.';
            return back()->with('error', $message);
        }

        // Persistir summary y vínculo
        $activeShift->from_shift_id = $activeShift->from_shift_id ?: $shift->id;
        $activeShift->summary = $closingSnapshot;
        $activeShift->save();

        $this->auditLog('shift_end', "Usuario {$user->username} entregó turno {$activeShift->shift_type->value} (handover #{$activeShift->id})", [
            'shift_id' => $activeShift->id,
            'base_final' => $activeShift->base_final,
            'total_efectivo' => $activeShift->total_entradas_efectivo
        ]);

        return back()->with('success', 'Turno entregado correctamente. Pendiente de recepción por el siguiente turno.');
    }

    public function receiveShift()
    {
        $user = Auth::user();
        $pendingReception = ShiftHandover::where('recibido_por', $user->id)
            ->where('status', ShiftHandoverStatus::DELIVERED)
            ->first();

        if (!$pendingReception) {
            // If not assigned, look for any delivered shift that is not yet received
            $pendingReception = ShiftHandover::whereNull('recibido_por')
                ->where('status', ShiftHandoverStatus::DELIVERED)
                ->first();
        }

        return view('shift-handovers.receive', compact('pendingReception'));
    }

    public function storeReception(Request $request)
    {
        $user = Auth::user();
        $handoverId = $request->input('handover_id');
        $handover = ShiftHandover::findOrFail($handoverId);

        if ($handover->status !== ShiftHandoverStatus::DELIVERED) {
            return back()->with('error', 'Este turno no está en estado de entrega.');
        }

        // Validar que el turno sea para este usuario o que esté sin asignar (admin puede todo)
        if (!$user->hasRole('Administrador')) {
            if ($handover->recibido_por !== null && (int) $handover->recibido_por !== (int) $user->id) {
                abort(403);
            }
        }

        // Sanitize base_recibida
        $baseRecibida = $request->input('base_recibida');
        if (is_string($baseRecibida)) {
            $baseRecibida = str_replace('.', '', $baseRecibida);
            $baseRecibida = (float) str_replace(',', '.', $baseRecibida);
        }

        $handover->recibido_por = $user->id;
        $handover->received_at = Carbon::now();
        $handover->base_recibida = $baseRecibida;
        $handover->observaciones_recepcion = $request->input('observaciones');
        $handover->diferencia = $handover->base_recibida - $handover->base_esperada;

        $tolerance = (float) config('shifts.difference_tolerance', 0);
        if ($tolerance > 0 && abs((float) $handover->diferencia) > $tolerance && trim((string) $handover->observaciones_recepcion) === '') {
            return back()->with('error', "La diferencia de base supera la tolerancia permitida (" . number_format($tolerance, 2, ',', '.') . "). Debes registrar observaciones.");
        }

        $handover->status = ShiftHandoverStatus::RECEIVED;

        // Abrir siguiente turno desde snapshot de cierre previo
        $previousShift = $handover->from_shift_id ? Shift::find($handover->from_shift_id) : null;
        if (!$previousShift || !$previousShift->closing_snapshot) {
            return back()->with('error', 'No hay snapshot de cierre del turno anterior para iniciar el siguiente.');
        }

        $nextType = $previousShift->type;
        try {
            $nextShift = $this->shiftLifecycle->openFresh($user, $nextType, $previousShift->closing_snapshot);
        } catch (\Throwable $e) {
            $message = $e instanceof \App\Exceptions\ShiftRuleViolation ? $e->getMessage() : 'No se pudo iniciar el turno siguiente.';
            return back()->with('error', $message);
        }

        $handover->to_shift_id = $nextShift->id;
        $handover->save();

        // Crear el nuevo handover activo para el receptor, enlazado al shift recién abierto
        ShiftHandover::create([
            'from_shift_id' => $nextShift->id,
            'entregado_por' => $user->id,
            'shift_type' => $nextType,
            'shift_date' => Carbon::today(),
            'started_at' => Carbon::now(),
            'base_inicial' => $handover->base_recibida ?? 0,
            'status' => ShiftHandoverStatus::ACTIVE,
        ]);

        $this->auditLog('shift_receive', "Usuario {$user->username} recibió turno #{$handover->id}", [
            'shift_id' => $handover->id,
            'base_recibida' => $handover->base_recibida,
            'diferencia' => $handover->diferencia,
            'next_shift_id' => $nextShift->id
        ]);

        return back()->with('success', 'Turno recibido y nuevo turno iniciado desde snapshot previo.');
    }

    // Solo Admin: forzar cierre de turnos operativos abiertos
    public function forceCloseOperational(Request $request)
    {
        $user = Auth::user();
        if (!$user->hasRole('Administrador')) {
            abort(403);
        }

        $reason = $request->input('reason', 'Cierre forzado por administrador');
        $closed = $this->shiftLifecycle->forceCloseOperational($user->id, $reason);

        return back()->with('success', "Se cerraron {$closed} turno(s) operativo(s). Razón: {$reason}");
    }

    // Cash Out Methods
    public function cashOutsIndex()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        $query = ShiftCashOut::with(['user', 'shiftHandover'])->orderBy('created_at', 'desc');
        if (!$user->hasRole('Administrador')) {
            $query->where('user_id', $user->id);
        }

        $cashOuts = $query->paginate(15);

        $activeShift = $user->turnoActivo()->first();
        $deleteWindowMinutes = (int) config('shifts.cash_delete_window_minutes', 60);

        return view('shift-cash-outs.index', [
            'cashOuts' => $cashOuts,
            'activeShiftId' => $activeShift ? $activeShift->id : null,
            'activeShiftStatus' => $activeShift ? $activeShift->status->value : null,
            'deleteWindowMinutes' => $deleteWindowMinutes,
            'isAdmin' => $user->hasRole('Administrador'),
        ]);
    }

    public function createCashOut()
    {
        $user = Auth::user();
        $activeShift = $user->turnoActivo()->first();
        return view('shift-cash-outs.create', compact('activeShift'));
    }

    public function storeCashOut(Request $request)
    {
        $user = Auth::user();
        $activeShift = $user->turnoActivo()->first();

        if (!$activeShift && !$user->hasRole('Administrador')) {
            return back()->with('error', 'Debes tener un turno activo para registrar una salida de caja.');
        }

        $request->validate([
            'amount' => 'required',
            'concept' => 'required|string|max:255',
        ]);

        // Sanitize amount (remove thousand separators)
        $amount = $request->amount;
        if (is_string($amount)) {
            $amount = str_replace('.', '', $amount);
            $amount = (float) str_replace(',', '.', $amount);
        }

        // Determinar el tipo de turno desde el turno operativo
        $shiftType = $activeShift->shift_type;

        // VALIDACIÓN DE SALDO DISPONIBLE
        if ($activeShift) {
            $disponible = $activeShift->getEfectivoDisponible();
            if ($amount > $disponible) {
                return back()->with('error', "Saldo insuficiente en caja del turno. Disponible: $" . number_format($disponible, 0, ',', '.'))->withInput();
            }
        }

        $cashOut = ShiftCashOut::create([
            'shift_handover_id' => $activeShift->id,
            'user_id' => $user->id,
            'amount' => $amount,
            'concept' => $request->concept,
            'observations' => $request->observations,
            'shift_type' => $shiftType,
            'shift_date' => Carbon::today(),
        ]);

        // Actualizar totales del turno
        $activeShift->updateTotals();

        $this->auditLog('cash_out', "Retiro de caja por {$amount} - Concepto: {$request->concept}", [
            'cash_out_id' => $cashOut->id,
            'amount' => $amount,
            'concept' => $request->concept
        ]);

        return redirect()->route('shift-cash-outs.index')->with('success', 'Retiro de caja registrado.');
    }

    public function destroyCashOut($id)
    {
        $cashOut = ShiftCashOut::findOrFail($id);

        /** @var \App\Models\User $user */
        $user = Auth::user();
        if (!$user->hasRole('Administrador')) {
            // Solo puede eliminar el suyo, dentro del turno activo y ventana de tiempo
            $activeShift = $user->turnoActivo()->first();
            $window = (int) config('shifts.cash_delete_window_minutes', 60);
            $createdAt = $cashOut->created_at;

            $canDelete = $activeShift
                && $activeShift->status === ShiftHandoverStatus::ACTIVE
                && (int) $cashOut->user_id === (int) $user->id
                && (int) ($cashOut->shift_handover_id ?? 0) === (int) $activeShift->id
                && $createdAt
                && now()->diffInMinutes($createdAt) <= $window;

            if (!$canDelete) {
                abort(403);
            }
        }

        $cashOut->delete();
        $this->auditLog('cash_out_delete', "Eliminó retiro de caja #{$id}");

        // Recalcular turno activo si aplica
        $activeShift = $user->turnoActivo()->first();
        if ($activeShift) {
            $activeShift->updateTotals();
        }

        return back()->with('success', 'Retiro de caja eliminado.');
    }

    private function auditLog($event, $description, $metadata = [])
    {
        AuditLog::create([
            'user_id' => Auth::id(),
            'event' => $event,
            'description' => $description,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'metadata' => $metadata,
        ]);
    }
}

