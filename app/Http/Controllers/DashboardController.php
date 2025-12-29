<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Customer;
use App\Models\Reservation;
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

        if ($user->hasRole('Administrador')) {
            return $this->adminDashboard();
        }

        if ($user->hasRole('Recepcionista Día')) {
            return redirect()->route('dashboard.receptionist.day');
        }

        if ($user->hasRole('Recepcionista Noche')) {
            return redirect()->route('dashboard.receptionist.night');
        }

        return $this->receptionistDashboard();
    }

    private function adminDashboard()
    {
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

        // Obtener información de caja para el administrador (dinero físico esperado en gaveta)
        $activeShift = \App\Models\ShiftHandover::activo()->first();
        
        $cashbox = [
            'has_active_shift' => (bool)$activeShift,
            'cash_available' => $activeShift ? $activeShift->getEfectivoDisponible() : 0,
            'shift_id' => $activeShift?->id,
            'shift_type' => $activeShift?->shift_type?->value,
            'receptionist' => $activeShift?->entregadoPor?->name,
        ];

        // Si no hay turno activo, mostrar el saldo del último turno cerrado como referencia del dinero que debería haber
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

        return view('dashboards.admin', compact('stats', 'lowStockProducts', 'cashbox'));
    }

    private function receptionistDashboard()
    {
        $today = now()->toDateString();
        
        $stats = [
            'arrivals_today' => Reservation::whereDate('check_in_date', $today)->count(),
            'departures_today' => Reservation::whereDate('check_out_date', $today)->count(),
            'active_reservations' => Reservation::whereDate('check_in_date', '<=', $today)
                                                ->whereDate('check_out_date', '>=', $today)
                                                ->count(),
        ];

        $upcomingArrivals = Reservation::with(['customer', 'room'])
            ->whereDate('check_in_date', '>=', $today)
            ->orderBy('check_in_date', 'asc')
            ->limit(5)
            ->get();

        return view('dashboards.receptionist', compact('stats', 'upcomingArrivals'));
    }
}
