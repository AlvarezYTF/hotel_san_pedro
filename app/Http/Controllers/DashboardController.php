<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Customer;
use App\Models\Reservation;
use App\Models\ReservationRoom;
use App\Services\ShiftControlService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * Show the application dashboard.
     */
    public function index()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $shiftOperationsEnabled = app(ShiftControlService::class)->isOperationalEnabled();

        if ($user->hasRole('Administrador')) {
            return $this->adminDashboard();
        }

        if ($user->hasRole('Recepcionista Día')) {
            if (!$shiftOperationsEnabled) {
                return $this->receptionistDashboard();
            }
            return redirect()->route('dashboard.receptionist.day');
        }

        if ($user->hasRole('Recepcionista Noche')) {
            if (!$shiftOperationsEnabled) {
                return $this->receptionistDashboard();
            }
            return redirect()->route('dashboard.receptionist.night');
        }

        return $this->receptionistDashboard();
    }

    private function adminDashboard()
    {
        /** @var ShiftControlService $shiftControl */
        $shiftControl = app(ShiftControlService::class);
        $shiftControlState = $shiftControl->current()->loadMissing("updatedBy:id,name");
        $operationalShift = \App\Models\Shift::openOperational()
            ->with("openedBy:id,name")
            ->first();

        // Calcular Ingresos Totales (Ventas + Reservas)
        $totalSales = \App\Models\Sale::sum('total');
        $totalReservationRevenue = \App\Models\Reservation::sum('total_amount');
        
        $stats = [
            'total_products' => Product::count(),
            'low_stock_products' => Product::where('quantity', '<', 10)->count(),
            'total_customers' => Customer::count(),
            'total_reservations' => Reservation::count(),
            'total_revenue' => $totalSales + $totalReservationRevenue,
        ];

        // Obtener informaciÃ³n de caja para el administrador (dinero fÃ­sico esperado en gaveta)
        $activeShift = \App\Models\ShiftHandover::activo()->first();
        
        $cashbox = [
            'has_active_shift' => (bool)$activeShift,
            'cash_available' => $activeShift ? $activeShift->getEfectivoDisponible() : 0,
            'shift_id' => $activeShift?->id,
            'shift_type' => $activeShift?->shift_type?->value,
            'receptionist' => $activeShift?->entregadoPor?->name,
        ];

        // Si no hay turno activo, mostrar el saldo del Ãºltimo turno cerrado como referencia del dinero que deberÃ­a haber
        if (!$activeShift) {
            $lastShift = \App\Models\ShiftHandover::whereIn('status', [
                \App\Enums\ShiftHandoverStatus::RECEIVED, 
                \App\Enums\ShiftHandoverStatus::DELIVERED,
                \App\Enums\ShiftHandoverStatus::CLOSED
            ])->latest()->first();
            
            if ($lastShift) {
                $cashbox['cash_available'] = $lastShift->base_recibida > 0 ? $lastShift->base_recibida : $lastShift->base_final;
                $cashbox['last_shift_date'] = $lastShift->shift_date->format('d/m/Y');
            }
        }

        $lowStockProducts = Product::where('quantity', '<=', DB::raw('low_stock_threshold'))
            ->where('quantity', '>', 0)
            ->orderBy('quantity', 'asc')
            ->limit(5)
            ->get();

        return view("dashboards.admin", [
            "stats" => $stats,
            "lowStockProducts" => $lowStockProducts,
            "cashbox" => $cashbox,
            "shiftOperationsEnabled" => (bool) $shiftControlState->operational_enabled,
            "shiftOperationsUpdatedAt" => $shiftControlState->updated_at,
            "shiftOperationsNote" => $shiftControlState->note,
            "shiftOperationsUpdatedBy" => $shiftControlState->updatedBy?->name,
            "operationalShift" => $operationalShift,
        ]);
    }

    private function receptionistDashboard()
    {
        $today = now()->toDateString();

        $stats = [
            'arrivals_today' => ReservationRoom::query()
                ->whereDate('check_in_date', $today)
                ->whereHas('reservation', function ($query) {
                    $query->whereNull('deleted_at');
                })
                ->distinct('reservation_id')
                ->count('reservation_id'),
            'departures_today' => ReservationRoom::query()
                ->whereDate('check_out_date', $today)
                ->whereHas('reservation', function ($query) {
                    $query->whereNull('deleted_at');
                })
                ->distinct('reservation_id')
                ->count('reservation_id'),
            'active_reservations' => ReservationRoom::query()
                ->whereDate('check_in_date', '<=', $today)
                ->whereDate('check_out_date', '>=', $today)
                ->whereHas('reservation', function ($query) {
                    $query->whereNull('deleted_at');
                })
                ->distinct('reservation_id')
                ->count('reservation_id'),
        ];

        $upcomingArrivals = ReservationRoom::query()
            ->with(['reservation.customer', 'room'])
            ->whereDate('check_in_date', '>=', $today)
            ->whereHas('reservation', function ($query) {
                $query->whereNull('deleted_at');
            })
            ->orderBy('check_in_date', 'asc')
            ->limit(5)
            ->get()
            ->map(function (ReservationRoom $reservationRoom) {
                return (object) [
                    'customer' => $reservationRoom->reservation?->customer,
                    'room' => $reservationRoom->room,
                    'check_in_date' => $reservationRoom->check_in_date
                        ? \Carbon\Carbon::parse($reservationRoom->check_in_date)
                        : null,
                ];
            });

        return view('dashboards.receptionist', compact('stats', 'upcomingArrivals'));
    }
}

