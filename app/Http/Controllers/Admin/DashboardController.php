<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\OfflineTransaction;
use App\Models\OfflineTransactionItem;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\StockMutation;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $onlineSalesTotal = (float) Order::whereIn('status', ['Sudah Sampai', 'Selesai/Lunas'])->sum('total');
        $offlineSalesTotal = (float) OfflineTransaction::sum('total');
        $totalSales = $onlineSalesTotal + $offlineSalesTotal;

        $stats = [
            'products' => Product::count(),
            'orders' => Order::count(),
            'pending' => Order::where('status', 'Menunggu Konfirmasi')->count(),
            'sales' => $totalSales,
            'online_sales' => $onlineSalesTotal,
            'offline_sales' => $offlineSalesTotal,
            'online_pct' => $totalSales > 0 ? round(($onlineSalesTotal / $totalSales) * 100) : 0,
            'offline_pct' => $totalSales > 0 ? round(($offlineSalesTotal / $totalSales) * 100) : 0,
        ];

        // 7-day sales trend (Online completed + Offline)
        $salesTrend = collect();
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i);
            $dateStr = $date->toDateString();

            $onlineDay = (float) Order::whereIn('status', ['Sudah Sampai', 'Selesai/Lunas'])
                ->whereDate('created_at', $dateStr)
                ->sum('total');

            $offlineDay = (float) OfflineTransaction::whereDate('date', $dateStr)
                ->sum('total');

            $salesTrend->push([
                'date' => $dateStr,
                'label' => $date->format('d/m'),
                'day_name' => $date->format('D'),
                'online' => $onlineDay,
                'offline' => $offlineDay,
                'total' => $onlineDay + $offlineDay,
            ]);
        }

        $maxDailySales = $salesTrend->max('total') ?: 1;

        // Top 5 Best-Selling Products (Combined Online + Offline)
        // Merge by product_id so renamed products are grouped correctly,
        // then show the current name from the products table.
        $currentProductNames = Product::pluck('name', 'id');

        $onlineTop = OrderItem::whereHas('order', function ($q) {
                $q->whereIn('status', ['Sudah Sampai', 'Selesai/Lunas']);
            })
            ->select('product_id', 'name', DB::raw('SUM(quantity) as qty'), DB::raw('SUM(subtotal) as revenue'))
            ->groupBy('product_id', 'name')
            ->get();

        $offlineTop = OfflineTransactionItem::select('product_id', 'name', DB::raw('SUM(quantity) as qty'), DB::raw('SUM(subtotal) as revenue'))
            ->groupBy('product_id', 'name')
            ->get();

        $mergedProducts = [];
        foreach ($onlineTop as $item) {
            // Use product_id as key when available; fall back to stored name
            $key = $item->product_id ?? 'name__' . $item->name;
            $displayName = ($item->product_id && $currentProductNames->has($item->product_id))
                ? $currentProductNames[$item->product_id]
                : $item->name;

            if (isset($mergedProducts[$key])) {
                $mergedProducts[$key]['qty']     += (int) $item->qty;
                $mergedProducts[$key]['revenue'] += (float) $item->revenue;
            } else {
                $mergedProducts[$key] = [
                    'name'    => $displayName,
                    'qty'     => (int) $item->qty,
                    'revenue' => (float) $item->revenue,
                ];
            }
        }

        foreach ($offlineTop as $item) {
            $key = $item->product_id ?? 'name__' . $item->name;
            $displayName = ($item->product_id && $currentProductNames->has($item->product_id))
                ? $currentProductNames[$item->product_id]
                : $item->name;

            if (isset($mergedProducts[$key])) {
                $mergedProducts[$key]['qty']     += (int) $item->qty;
                $mergedProducts[$key]['revenue'] += (float) $item->revenue;
                // Always use the latest name from the products table
                $mergedProducts[$key]['name'] = $displayName;
            } else {
                $mergedProducts[$key] = [
                    'name'    => $displayName,
                    'qty'     => (int) $item->qty,
                    'revenue' => (float) $item->revenue,
                ];
            }
        }

        $topProducts = collect($mergedProducts)->sortByDesc('qty')->take(5)->values();

        $orders = Order::with('items')->latest()->take(6)->get();
        $lowStock = Product::whereColumn('stock', '<=', 'min_stock')->orderBy('stock')->get();
        $expiringSoon = StockMutation::with('product')
            ->where('type', 'in')
            ->whereNotNull('expiry_date')
            ->whereBetween('expiry_date', [Carbon::today(), Carbon::today()->addDays(14)])
            ->orderBy('expiry_date')
            ->get();

        $statuses = OrderController::STATUSES;

        return view('admin.dashboard', compact(
            'stats',
            'orders',
            'lowStock',
            'expiringSoon',
            'statuses',
            'salesTrend',
            'maxDailySales',
            'topProducts'
        ));
    }
}

