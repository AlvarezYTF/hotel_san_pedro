<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Customer;
use App\Models\Reservation;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * Show the application dashboard.
     */
    public function index()
    {
        $stats = [
            'total_products' => Product::count(),
            'low_stock_products' => Product::where('quantity', '<', 10)->count(),
            'total_customers' => Customer::count(),
            'total_reservations' => Reservation::count(),
        ];

        // Productos con bajo stock
        $lowStockProducts = Product::where('quantity', '<=', DB::raw('low_stock_threshold'))
            ->where('quantity', '>', 0)
            ->orderBy('quantity', 'asc')
            ->limit(5)
            ->get();

        return view('dashboard', compact('stats', 'lowStockProducts'));
    }
}
