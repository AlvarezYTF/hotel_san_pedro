<?php

namespace App\Http\Controllers\Dashboards;

use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\Quotation;

class DashboardController extends Controller
{
    public function index()
    {
        $orders = Order::count();
        $vendidoOrders = Order::where('order_status', OrderStatus::VENDIDO)
            ->count();

        $products = Product::count();

        $categories = Category::count();

        return view('dashboard', [
            'products' => $products,
            'orders' => $orders,
            'vendidoOrders' => $vendidoOrders,
            'categories' => $categories
        ]);
    }
}
