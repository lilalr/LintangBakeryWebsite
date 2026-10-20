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
        $lowStock = Product::whereColumn('stock', '<=', 'min_stock')->orderBy('stock')->get();
        $expiringSoon = \App\Models\StockMutation::with('product')
            ->where('type', 'in')
            ->whereNotNull('expiry_date')
            ->whereBetween('expiry_date', [\Carbon\Carbon::today(), \Carbon\Carbon::today()->addDays(14)])
            ->orderBy('expiry_date')
            ->get();

        $statuses = OrderController::STATUSES;

        return view('admin.dashboard', compact('stats', 'orders', 'lowStock', 'expiringSoon', 'statuses'));
    }
}
