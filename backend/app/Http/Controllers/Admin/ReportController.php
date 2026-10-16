<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\OfflineTransaction;
use App\Models\Order;
use Illuminate\View\View;

class ReportController extends Controller
{
    public function index(): View
    {
        $orders = Order::with('items')->latest()->get();
        $transactions = OfflineTransaction::with('items')->latest()->get();
        $onlineRevenue = $orders->whereIn('status', ['Sudah Sampai', 'Selesai/Lunas'])->sum('total');
        $offlineRevenue = $transactions->sum('total');

        return view('admin.reports.index', compact('orders', 'transactions', 'onlineRevenue', 'offlineRevenue'));
    }
}
