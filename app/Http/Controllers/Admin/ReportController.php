<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\OfflineTransaction;
use App\Models\OfflineTransactionItem;
use App\Models\Order;
use App\Models\OrderItem;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    private const REVENUE_STATUSES = [
        'Pembayaran DP Disetujui',
        'Sedang Diproduksi',
        'Siap Dikirim',
        'Sedang Dikirim',
        'Sudah Sampai',
        'Selesai/Lunas',
    ];

    public function index(Request $request): View
    {
        return view('admin.reports.index', $this->reportData($request));
    }

    public function export(Request $request): StreamedResponse
    {
        $report = $this->reportData($request);
        $filename = 'laporan-penjualan-'.$report['startDate'].'-'.$report['endDate'].'.csv';

        return response()->streamDownload(function () use ($report): void {
            $output = fopen('php://output', 'w');
            if ($output === false) {
                throw new \RuntimeException('Tidak dapat membuka aliran ekspor laporan.');
            }

            fwrite($output, "\xEF\xBB\xBF");
            fputcsv($output, ['Laporan Penjualan Lintang Bakery'], ',', '"', '\\');
            fputcsv($output, ['Periode', $report['startDate'].' s.d. '.$report['endDate']], ',', '"', '\\');
            fputcsv($output, ['Total Penjualan (Kotor)', number_format($report['totalRevenue'], 2, '.', '')], ',', '"', '\\');
            fputcsv($output, [], ',', '"', '\\');
            fputcsv($output, ['Rincian Harian'], ',', '"', '\\');
            fputcsv($output, ['Tanggal', 'Penjualan Online', 'Penjualan Kasir', 'Total Penjualan', 'Tunai Kasir', 'QRIS Kasir', 'Transfer Online', 'QRIS Online', 'Online Lainnya'], ',', '"', '\\');

            foreach ($report['dailyBreakdown'] as $row) {
                fputcsv($output, [
                    $row['date'],
                    number_format($row['online'], 2, '.', ''),
                    number_format($row['offline'], 2, '.', ''),
                    number_format($row['online'] + $row['offline'], 2, '.', ''),
                    number_format($row['cash'], 2, '.', ''),
                    number_format($row['store_qris'], 2, '.', ''),
                    number_format($row['online_bank_transfer'], 2, '.', ''),
                    number_format($row['online_qris'], 2, '.', ''),
                    number_format($row['online_other'], 2, '.', ''),
                ], ',', '"', '\\');
            }

            fputcsv($output, [], ',', '"', '\\');
            fputcsv($output, ['Performa Produk'], ',', '"', '\\');
            fputcsv($output, ['Produk', 'Jumlah Terjual', 'Penjualan Produk'], ',', '"', '\\');

            foreach ($report['bestSellers'] as $product) {
                fputcsv($output, [
                    $product['name'],
                    $product['quantity'],
                    number_format($product['revenue'], 2, '.', ''),
                ], ',', '"', '\\');
            }

            fclose($output);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    /**
     * @return array<string, mixed>
     */
    private function reportData(Request $request): array
    {
        $filters = $request->validate([
            'period' => ['nullable', 'date_format:Y-m'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date'],
        ]);

        if (! empty($filters['start_date']) || ! empty($filters['end_date'])) {
            $startDate = $filters['start_date'] ?? $filters['end_date'];
            $endDate = $filters['end_date'] ?? $filters['start_date'];

            if ($startDate > $endDate) {
                abort(422, 'Tanggal mulai tidak boleh melewati tanggal akhir.');
            }
        } else {
            $period = Carbon::createFromFormat('!Y-m', $filters['period'] ?? now()->format('Y-m'));
            $startDate = $period->toDateString();
            $endDate = $period->endOfMonth()->toDateString();
        }

        $orders = Order::query()
            ->with('items')
            ->whereDate('created_at', '>=', $startDate)
            ->whereDate('created_at', '<=', $endDate)
            ->latest()
            ->get();
        $revenueOrders = $orders->whereIn('status', self::REVENUE_STATUSES);
        $transactions = OfflineTransaction::query()
            ->with('items')
            ->whereDate('date', '>=', $startDate)
            ->whereDate('date', '<=', $endDate)
            ->orderByDesc('date')
            ->get();

        $daily = [];
        $monthly = [];
        $salesByProduct = [];
        $onlineRevenue = 0.0;
        $offlineRevenue = (float) $transactions->sum('total');
        $totalHpp = 0.0;

        foreach ($revenueOrders as $order) {
            $date = $order->created_at->toDateString();
            $month = $order->created_at->format('Y-m');
            $this->initializeDailyRow($daily, $date);
            $this->initializeMonthlyRow($monthly, $month);
            $daily[$date]['transactions']++;
            $monthly[$month]['transactions']++;
            $revenue = (float) $order->total + (float) $order->shipping_cost;
            $onlineRevenue += $revenue;
            $daily[$date]['online'] += $revenue;
            $monthly[$month]['online'] += $revenue;

            $collected = $order->payment_type === 'dp'
                && $order->status !== 'Selesai/Lunas'
                ? (float) $order->dp
                : $revenue;
            $paymentColumn = match ($order->payment_method) {
                'bank_transfer' => 'online_bank_transfer',
                'qris' => 'online_qris',
                default => 'online_other',
            };
            $daily[$date][$paymentColumn] += $collected;

            foreach ($order->items as $item) {
                $this->addProductSale($salesByProduct, $item);
                $daily[$date]['items'] += (int) $item->quantity;
                $monthly[$month]['items'] += (int) $item->quantity;
                $totalHpp += (float) $item->harga_modal_at_purchase * $item->quantity;
            }
        }

        foreach ($transactions as $transaction) {
            $date = $transaction->date->toDateString();
            $month = Carbon::parse($transaction->date)->format('Y-m');
            $this->initializeDailyRow($daily, $date);
            $this->initializeMonthlyRow($monthly, $month);
            $daily[$date]['transactions']++;
            $monthly[$month]['transactions']++;
            $daily[$date]['offline'] += (float) $transaction->total;
            $monthly[$month]['offline'] += (float) $transaction->total;
            $paymentColumn = in_array($transaction->payment_method, ['qris', 'qris-tf'], true)
                ? 'store_qris'
                : 'cash';
            $daily[$date][$paymentColumn] += (float) $transaction->total;

            foreach ($transaction->items as $item) {
                $this->addProductSale($salesByProduct, $item);
                $daily[$date]['items'] += (int) $item->quantity;
                $monthly[$month]['items'] += (int) $item->quantity;
                $totalHpp += (float) $item->harga_modal_at_purchase * $item->quantity;
            }
        }

        $bestSellers = collect($salesByProduct)
            ->sortByDesc('quantity')
            ->values();
        $totalRevenue = $onlineRevenue + $offlineRevenue;
        $dailyBreakdown = collect($daily)->sortKeys()->values();
        $paymentBreakdown = [
            ['label' => 'Tunai kasir', 'amount' => (float) $dailyBreakdown->sum('cash')],
            ['label' => 'QRIS kasir', 'amount' => (float) $dailyBreakdown->sum('store_qris')],
            ['label' => 'Transfer online', 'amount' => (float) $dailyBreakdown->sum('online_bank_transfer')],
            ['label' => 'QRIS online', 'amount' => (float) $dailyBreakdown->sum('online_qris')],
            ['label' => 'Online lainnya', 'amount' => (float) $dailyBreakdown->sum('online_other')],
        ];

        // Daftar detail per transaksi (Kasir & Online)
        $transactionDetails = collect();

        foreach ($revenueOrders as $order) {
            $orderRevenue = (float) $order->total + (float) $order->shipping_cost;
            $orderHpp = (float) $order->items->sum(fn ($i) => (float) ($i->harga_modal_at_purchase ?? 0) * (int) $i->quantity);
            $transactionDetails->push([
                'id' => 'ord_'.$order->id,
                'code' => $order->code,
                'source' => 'Online',
                'buyer' => $order->buyer,
                'phone' => $order->phone,
                'date' => $order->created_at,
                'date_formatted' => $order->created_at->translatedFormat('d M Y, H:i'),
                'payment_method' => match ($order->payment_method) {
                    'bank_transfer' => 'Transfer Bank',
                    'qris' => 'QRIS Online',
                    default => 'Online Lainnya',
                },
                'payment_type' => strtoupper($order->payment_type),
                'status' => $order->status,
                'total_revenue' => $orderRevenue,
                'total_hpp' => $orderHpp,
                'gross_profit' => $orderRevenue - $orderHpp,
                'notes' => $order->city ? ($order->address.', '.$order->city) : null,
                'items' => $order->items->map(fn ($item) => [
                    'name' => $item->name,
                    'quantity' => (int) $item->quantity,
                    'unit_price' => (float) $item->unit_price,
                    'subtotal' => (float) $item->subtotal,
                    'cost_price' => (float) ($item->harga_modal_at_purchase ?? 0),
                ])->values(),
            ]);
        }

        foreach ($transactions as $tx) {
            $txHpp = (float) $tx->total_hpp;
            $txTotal = (float) $tx->total;
            $transactionDetails->push([
                'id' => 'pos_'.$tx->id,
                'code' => 'TRX-'.$tx->id,
                'source' => 'Kasir',
                'buyer' => $tx->buyer_name,
                'phone' => '-',
                'date' => $tx->created_at ?? $tx->date,
                'date_formatted' => Carbon::parse($tx->date)->translatedFormat('d M Y').($tx->created_at ? ', '.$tx->created_at->format('H:i') : ''),
                'payment_method' => in_array($tx->payment_method, ['qris', 'qris-tf'], true) ? 'QRIS Kasir' : 'Tunai Kasir',
                'payment_type' => 'LUNAS',
                'status' => 'Selesai',
                'total_revenue' => $txTotal,
                'total_hpp' => $txHpp,
                'gross_profit' => (float) ($tx->total_laba ?: ($txTotal - $txHpp)),
                'notes' => $tx->notes,
                'items' => $tx->items->map(fn ($item) => [
                    'name' => $item->name,
                    'quantity' => (int) $item->quantity,
                    'unit_price' => (float) $item->unit_price,
                    'subtotal' => (float) $item->subtotal,
                    'cost_price' => (float) ($item->harga_modal_at_purchase ?? 0),
                ])->values(),
            ]);
        }

        $transactionDetails = $transactionDetails->sortByDesc('date')->values()->map(function ($item, $idx) {
            $item['global_index'] = $idx;
            $item['date_day'] = \Carbon\Carbon::parse($item['date'])->toDateString();
            return $item;
        });

        $transactionsByDate = $transactionDetails->groupBy('date_day');

        // Hitung data grafik harian (timeline kontinu jika <= 62 hari)
        $startCarbon = Carbon::parse($startDate);
        $endCarbon = Carbon::parse($endDate);
        $dayDiff = $startCarbon->diffInDays($endCarbon);

        $dailyChartRows = collect($daily)->sortKeys();
        if ($dayDiff <= 62) {
            $periodDays = collect();
            $curr = $startCarbon->copy();
            while ($curr->lte($endCarbon)) {
                $dStr = $curr->toDateString();
                $periodDays[$dStr] = $daily[$dStr] ?? [
                    'date' => $dStr,
                    'transactions' => 0,
                    'items' => 0,
                    'online' => 0.0,
                    'offline' => 0.0,
                ];
                $curr->addDay();
            }
            $dailyChartRows = $periodDays;
        }

        $chartDaily = [
            'labels' => $dailyChartRows->map(fn ($r) => Carbon::parse($r['date'])->translatedFormat('d M'))->values(),
            'dates' => $dailyChartRows->pluck('date')->values(),
            'total' => $dailyChartRows->map(fn ($r) => (float) ($r['online'] + $r['offline']))->values(),
            'online' => $dailyChartRows->pluck('online')->values(),
            'offline' => $dailyChartRows->pluck('offline')->values(),
            'transactions' => $dailyChartRows->pluck('transactions')->values(),
        ];

        // Hitung data grafik bulanan
        $monthlyRows = collect($monthly)->sortKeys();
        $monthSpan = $startCarbon->diffInMonths($endCarbon);
        if ($monthSpan >= 1 && $monthSpan <= 24) {
            $periodMonths = collect();
            $currMonth = $startCarbon->copy()->startOfMonth();
            while ($currMonth->lte($endCarbon)) {
                $mStr = $currMonth->format('Y-m');
                $periodMonths[$mStr] = $monthly[$mStr] ?? [
                    'month' => $mStr,
                    'transactions' => 0,
                    'items' => 0,
                    'online' => 0.0,
                    'offline' => 0.0,
                ];
                $currMonth->addMonth();
            }
            $monthlyRows = $periodMonths;
        } elseif ($monthlyRows->isEmpty()) {
            $mStr = $startCarbon->format('Y-m');
            $monthlyRows = collect([
                $mStr => [
                    'month' => $mStr,
                    'transactions' => 0,
                    'items' => 0,
                    'online' => 0.0,
                    'offline' => 0.0,
                ],
            ]);
        }

        $chartMonthly = [
            'labels' => $monthlyRows->keys()->map(fn ($m) => Carbon::createFromFormat('!Y-m', $m)->translatedFormat('M Y'))->values(),
            'months' => $monthlyRows->keys()->values(),
            'total' => $monthlyRows->map(fn ($r) => (float) ($r['online'] + $r['offline']))->values(),
            'online' => $monthlyRows->pluck('online')->values(),
            'offline' => $monthlyRows->pluck('offline')->values(),
            'transactions' => $monthlyRows->pluck('transactions')->values(),
        ];

        $chartData = [
            'daily' => $chartDaily,
            'monthly' => $chartMonthly,
            'labels' => $chartDaily['labels'],
            'dates' => $chartDaily['dates'],
            'total' => $chartDaily['total'],
            'online' => $chartDaily['online'],
            'offline' => $chartDaily['offline'],
            'transactions' => $chartDaily['transactions'],
        ];

        return [
            'onlineRevenue' => $onlineRevenue,
            'offlineRevenue' => $offlineRevenue,
            'totalRevenue' => $totalRevenue,
            'totalHpp' => $totalHpp,
            'grossProfit' => $totalRevenue - $totalHpp,
            'totalTransactions' => $dailyBreakdown->sum('transactions'),
            'totalItemsSold' => $dailyBreakdown->sum('items'),
            'paymentBreakdown' => $paymentBreakdown,
            'dailyBreakdown' => $dailyBreakdown,
            'bestSellers' => $bestSellers,
            'transactionDetails' => $transactionDetails,
            'transactionsByDate' => $transactionsByDate,
            'chartData' => $chartData,
            'startDate' => $startDate,
            'endDate' => $endDate,
        ];
    }

    /**
     * @param  array<string, array<string, float|int|string>>  $daily
     */
    private function initializeDailyRow(array &$daily, string $date): void
    {
        $daily[$date] ??= [
            'date' => $date,
            'transactions' => 0,
            'items' => 0,
            'online' => 0.0,
            'offline' => 0.0,
            'cash' => 0.0,
            'store_qris' => 0.0,
            'online_bank_transfer' => 0.0,
            'online_qris' => 0.0,
            'online_other' => 0.0,
        ];
    }

    /**
     * @param  array<string, array<string, float|int|string>>  $monthly
     */
    private function initializeMonthlyRow(array &$monthly, string $month): void
    {
        $monthly[$month] ??= [
            'month' => $month,
            'transactions' => 0,
            'items' => 0,
            'online' => 0.0,
            'offline' => 0.0,
        ];
    }

    /**
     * @param  array<string, array{name: string, quantity: int, revenue: float, profit: float}>  $salesByProduct
     */
    private function addProductSale(array &$salesByProduct, OrderItem|OfflineTransactionItem $item): void
    {
        $quantity = (int) $item->quantity;
        $unitPrice = (float) $item->harga_jual_at_purchase ?: (float) $item->unit_price;
        $cost = (float) $item->harga_modal_at_purchase;
        $name = $item->name;
        $salesByProduct[$name] ??= [
            'name' => $name,
            'quantity' => 0,
            'revenue' => 0.0,
            'profit' => 0.0,
        ];
        $salesByProduct[$name]['quantity'] += $quantity;
        $salesByProduct[$name]['revenue'] += $unitPrice * $quantity;
        $salesByProduct[$name]['profit'] += ($unitPrice - $cost) * $quantity;
    }
}
