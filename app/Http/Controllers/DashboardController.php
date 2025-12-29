<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Customer;
use App\Models\Reservation;
use App\Models\ShiftHandover;
use App\Enums\ShiftHandoverStatus;
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
        $activeShift = ShiftHandover::with('entregadoPor')
            ->where('status', ShiftHandoverStatus::ACTIVE)
            ->orderByDesc('started_at')
            ->first();

        if ($activeShift) {
            $activeShift->updateTotals();
        }

        $stats = [
            'total_products' => Product::count(),
            'low_stock_products' => Product::where('quantity', '<', 10)->count(),
            'total_customers' => Customer::count(),
            'total_reservations' => Reservation::count(),
            'total_revenue' => \App\Models\Reservation::sum('total_amount'), // Ejemplo simplificado
        ];

        $lowStockProducts = Product::where('quantity', '<=', DB::raw('low_stock_threshold'))
            ->where('quantity', '>', 0)
            ->orderBy('quantity', 'asc')
            ->limit(5)
            ->get();

        $cashbox = [
            'has_active_shift' => (bool) $activeShift,
            'shift_id' => $activeShift?->id,
            'shift_type' => $activeShift?->shift_type?->value,
            'started_at' => $activeShift?->started_at,
            'cash_available' => $activeShift ? (float) $activeShift->base_esperada : null,
            'cash_sales' => $activeShift ? (float) $activeShift->total_entradas_efectivo : null,
            'transfer_sales' => $activeShift ? (float) $activeShift->total_entradas_transferencia : null,
            'total_out' => $activeShift ? (float) $activeShift->total_salidas : null,
            'receptionist' => $activeShift?->entregadoPor?->name,
        ];

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
