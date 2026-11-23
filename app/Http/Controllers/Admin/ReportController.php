<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\OfflineTransaction;
use App\Models\OfflineTransactionItem;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\StockMovement;
use App\Models\StockMutation;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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
        $filename = 'laporan-penjualan-detail-'.$report['startDate'].'-'.$report['endDate'].'.csv';

        return response()->streamDownload(function () use ($report): void {
            $output = fopen('php://output', 'w');
            if ($output === false) {
                throw new \RuntimeException('Tidak dapat membuka aliran ekspor laporan.');
            }

            fwrite($output, "\xEF\xBB\xBF");
            fputcsv($output, ['Laporan Detail Transaksi Penjualan - Lintang Bakery'], ',', '"', '\\');
            fputcsv($output, ['Periode', $report['startDate'].' s.d. '.$report['endDate']], ',', '"', '\\');
            fputcsv($output, ['Kanal Penjualan', match ($report['currentChannel']) {
                'online' => 'Online',
                'offline' => 'Offline',
                default => 'Semua Kanal (Online & Offline)',
            }], ',', '"', '\\');
            fputcsv($output, ['Metode Pembayaran', match ($report['currentPaymentMethod']) {
                'cash' => 'Tunai',
                'qris' => 'QRIS',
                'bank_transfer' => 'Transfer',
                default => 'Semua Metode',
            }], ',', '"', '\\');
            if (! empty($report['searchBuyer'])) {
                fputcsv($output, ['Filter Nama Pelanggan', $report['searchBuyer']], ',', '"', '\\');
            }
            fputcsv($output, ['Total Transaksi', (string) count($report['transactionDetails'])], ',', '"', '\\');
            fputcsv($output, ['Total Penjualan (Kotor)', number_format($report['totalRevenue'], 2, '.', '')], ',', '"', '\\');
            fputcsv($output, [], ',', '"', '\\');

            fputcsv($output, [
                'No',
                'Tanggal & Waktu',
                'No. Transaksi / Kode',
                'Kanal',
                'Nama Pelanggan',
                'Kontak / Telp',
                'Daftar Item Kue',
                'Total Qty (bal)',
                'Metode Pembayaran',
                'Status',
                'Total Penjualan (Rp)',
            ], ',', '"', '\\');

            foreach ($report['transactionDetails'] as $idx => $row) {
                $itemStrings = [];
                $totalQty = 0;
                foreach ($row['items'] as $item) {
                    $qty = (int) $item['quantity'];
                    $totalQty += $qty;
                    $itemStrings[] = $item['name'].' ('.$qty.' bal @ Rp '.number_format($item['unit_price'], 0, ',', '.').')';
                }
                $itemListStr = implode('; ', $itemStrings);

                fputcsv($output, [
                    $idx + 1,
                    $row['date_formatted'],
                    $row['code'],
                    $row['source'],
                    $row['buyer'],
                    $row['phone'] ?? '-',
                    $itemListStr,
                    $totalQty,
                    $row['payment_method'],
                    $row['status'],
                    number_format($row['total_revenue'], 2, '.', ''),
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
            'channel' => ['nullable', 'string', 'in:all,offline,online'],
            'search_buyer' => ['nullable', 'string', 'max:160'],
            'payment_method' => ['nullable', 'string', 'in:all,cash,qris,bank_transfer'],
        ]);

        $channel = $filters['channel'] ?? 'all';
        $searchBuyer = trim($filters['search_buyer'] ?? '');
        $paymentMethod = $filters['payment_method'] ?? 'all';

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

        // 1. Query Orders (Online)
        $revenueOrders = collect();
        if ($channel !== 'offline' && $paymentMethod !== 'cash') {
            $orderQuery = Order::query()
                ->with('items')
                ->whereDate('created_at', '>=', $startDate)
                ->whereDate('created_at', '<=', $endDate)
                ->whereIn('status', self::REVENUE_STATUSES);

            if ($paymentMethod === 'qris') {
                $orderQuery->where('payment_method', 'qris');
            } elseif ($paymentMethod === 'bank_transfer') {
                $orderQuery->where('payment_method', 'bank_transfer');
            }

            if ($searchBuyer !== '') {
                $orderQuery->where(function ($q) use ($searchBuyer) {
                    $q->where(DB::raw('LOWER(buyer)'), 'like', '%'.mb_strtolower($searchBuyer).'%')
                        ->orWhere('phone', 'like', "%{$searchBuyer}%")
                        ->orWhere('code', 'like', "%{$searchBuyer}%");
                });
            }

            $revenueOrders = $orderQuery->latest()->get();
        }

        // 2. Query Offline Transactions
        $transactions = collect();
        if ($channel !== 'online' && $paymentMethod !== 'bank_transfer') {
            $txQuery = OfflineTransaction::query()
                ->with('items')
                ->whereDate('date', '>=', $startDate)
                ->whereDate('date', '<=', $endDate);

            if ($paymentMethod === 'cash') {
                $txQuery->where('payment_method', 'cash');
            } elseif ($paymentMethod === 'qris') {
                $txQuery->whereIn('payment_method', ['qris', 'qris-tf']);
            }

            if ($searchBuyer !== '') {
                $txQuery->where(function ($q) use ($searchBuyer) {
                    $q->where(DB::raw('LOWER(buyer_name)'), 'like', '%'.mb_strtolower($searchBuyer).'%')
                        ->orWhere('id', 'like', "%{$searchBuyer}%");
                });
            }

            $transactions = $txQuery->orderByDesc('date')->get();
        }

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

        // Daftar detail per transaksi (Offline & Online)
        $transactionDetails = collect();

        foreach ($revenueOrders as $order) {
            $orderRevenue = (float) $order->total + (float) $order->shipping_cost;
            $orderHpp = (float) $order->items->sum(fn ($i) => (float) ($i->harga_modal_at_purchase ?? 0) * (int) $i->quantity);
            $transactionDetails->push([
                'id' => 'ord_'.$order->id,
                'raw_id' => $order->id,
                'code' => $order->code,
                'source' => 'Online',
                'buyer' => $order->buyer,
                'phone' => $order->phone,
                'date' => $order->created_at,
                'date_formatted' => $order->created_at->translatedFormat('d M Y, H:i'),
                'payment_method' => match ($order->payment_method) {
                    'bank_transfer' => 'Transfer',
                    'qris' => 'QRIS',
                    default => 'Lainnya',
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
                'raw_id' => $tx->id,
                'code' => 'TRX-'.str_pad($tx->id, 4, '0', STR_PAD_LEFT),
                'source' => 'Offline',
                'buyer' => ($tx->buyer_name && ! in_array(trim($tx->buyer_name), ['Pelanggan Toko', '-', 'Walk-in', 'Umum'], true)) ? $tx->buyer_name : '-',
                'phone' => '-',
                'date' => $tx->created_at ?? $tx->date,
                'date_formatted' => Carbon::parse($tx->created_at ?: $tx->date)->translatedFormat('d M Y, H:i'),
                'payment_method' => in_array($tx->payment_method, ['qris', 'qris-tf'], true) ? 'QRIS' : 'Tunai',
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

        $transactionDetails = $transactionDetails->sort(function ($a, $b) {
            $timeA = Carbon::parse($a['date'])->timestamp;
            $timeB = Carbon::parse($b['date'])->timestamp;
            if ($timeA === $timeB) {
                return strcmp($b['id'], $a['id']);
            }

            return $timeB <=> $timeA;
        })->values()->map(function ($item, $idx) {
            $item['global_index'] = $idx;
            $item['date_day'] = Carbon::parse($item['date'])->toDateString();

            return $item;
        });

        $transactionsByDate = $transactionDetails->groupBy('date_day');

        // Hitung data grafik harian (grafik diskrit, tanpa timeline kontinu buatan berangka 0)
        $dailyChartRows = collect($daily)->sortKeys();

        $chartDaily = [
            'labels' => $dailyChartRows->map(fn ($r) => Carbon::parse($r['date'])->translatedFormat('d M'))->values(),
            'dates' => $dailyChartRows->pluck('date')->values(),
            'total' => $dailyChartRows->map(fn ($r) => (float) ($r['online'] + $r['offline']))->values(),
            'online' => $dailyChartRows->pluck('online')->values(),
            'offline' => $dailyChartRows->pluck('offline')->values(),
            'transactions' => $dailyChartRows->pluck('transactions')->values(),
        ];

        // Hitung data grafik bulanan (grafik diskrit)
        $monthlyRows = collect($monthly)->sortKeys();

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

        $totalTransactions = (int) $dailyBreakdown->sum('transactions');
        $totalItemsSold = (int) $dailyBreakdown->sum('items');
        $onlineCount = $revenueOrders->count();
        $offlineCount = $transactions->count();
        $onlinePct = $totalRevenue > 0 ? (int) round(($onlineRevenue / $totalRevenue) * 100) : 0;
        $offlinePct = $totalRevenue > 0 ? (int) round(($offlineRevenue / $totalRevenue) * 100) : 0;
        $averageOrderValue = $totalTransactions > 0 ? (float) round($totalRevenue / $totalTransactions) : 0.0;

        $totalCash = (float) $dailyBreakdown->sum('cash');
        $totalStoreQris = (float) $dailyBreakdown->sum('store_qris');
        $totalOnlineQris = (float) $dailyBreakdown->sum('online_qris');
        $totalQris = $totalStoreQris + $totalOnlineQris;
        $totalOnlineBankTransfer = (float) $dailyBreakdown->sum('online_bank_transfer');

        $lossMovements = StockMovement::whereIn('reason', ['Rusak', 'Kedaluwarsa'])
            ->whereDate('date', '>=', $startDate)
            ->whereDate('date', '<=', $endDate)
            ->with('product')
            ->get();

        $totalLoss = (float) $lossMovements->sum(function ($m) {
            $unitPrice = (float) ($m->unit_price > 0
                ? $m->unit_price
                : (((float) ($m->product?->cost_price ?? 0) > 0)
                    ? $m->product->cost_price
                    : ($m->product?->price_per_bal ?? 0)));

            return (int) $m->quantity * $unitPrice;
        }) + (float) StockMutation::where('type', 'out')
            ->where('loss_amount', '>', 0)
            ->whereDate('date', '>=', $startDate)
            ->whereDate('date', '<=', $endDate)
            ->sum('loss_amount');

        return [
            'onlineRevenue' => $onlineRevenue,
            'offlineRevenue' => $offlineRevenue,
            'totalRevenue' => $totalRevenue,
            'totalHpp' => $totalHpp,
            'grossProfit' => $totalRevenue - $totalHpp,
            'totalLoss' => $totalLoss,
            'totalTransactions' => $totalTransactions,
            'totalItemsSold' => $totalItemsSold,
            'onlineCount' => $onlineCount,
            'offlineCount' => $offlineCount,
            'onlinePct' => $onlinePct,
            'offlinePct' => $offlinePct,
            'averageOrderValue' => $averageOrderValue,
            'totalCash' => $totalCash,
            'totalQris' => $totalQris,
            'totalStoreQris' => $totalStoreQris,
            'totalOnlineQris' => $totalOnlineQris,
            'totalOnlineBankTransfer' => $totalOnlineBankTransfer,
            'paymentBreakdown' => $paymentBreakdown,
            'dailyBreakdown' => $dailyBreakdown,
            'bestSellers' => $bestSellers,
            'transactionDetails' => $transactionDetails,
            'transactionsByDate' => $transactionsByDate,
            'chartData' => $chartData,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'currentChannel' => $channel,
            'currentPaymentMethod' => $paymentMethod,
            'searchBuyer' => $searchBuyer,
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
