<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\OfflineTransaction;
use App\Models\Order;
use App\Models\Product;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $stats = [
            'products' => Product::count(),
            'orders' => Order::count(),
            'pending' => Order::where('status', 'Menunggu Konfirmasi')->count(),
            'sales' => Order::whereIn('status', ['Sudah Sampai', 'Selesai/Lunas'])->sum('total')
                + OfflineTransaction::sum('total'),
        ];
        $orders = Order::with('items')->latest()->take(6)->get();
        $lowStock = Product::where('stock', '<', 50)->orderBy('stock')->get();

        $statuses = OrderController::STATUSES;

        return view('admin.dashboard', compact('stats', 'orders', 'lowStock', 'statuses'));
    }
}
