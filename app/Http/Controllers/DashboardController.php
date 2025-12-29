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

        return view('dashboards.admin', compact('stats', 'lowStockProducts'));
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
