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
        $filename = 'laporan-akuntansi-'.$report['startDate'].'-'.$report['endDate'].'.csv';

        return response()->streamDownload(function () use ($report): void {
            $output = fopen('php://output', 'w');
            if ($output === false) {
                throw new \RuntimeException('Tidak dapat membuka aliran ekspor laporan.');
            }

            fwrite($output, "\xEF\xBB\xBF");
            fputcsv($output, ['Laporan Akuntansi Lintang Bakery'], ',', '"', '\\');
            fputcsv($output, ['Periode', $report['startDate'].' s.d. '.$report['endDate']], ',', '"', '\\');
            fputcsv($output, ['Total Pendapatan', number_format($report['totalRevenue'], 2, '.', '')], ',', '"', '\\');
            fputcsv($output, ['Total HPP', number_format($report['totalHpp'], 2, '.', '')], ',', '"', '\\');
            fputcsv($output, ['Laba Kotor', number_format($report['grossProfit'], 2, '.', '')], ',', '"', '\\');
            fputcsv($output, [], ',', '"', '\\');
            fputcsv($output, ['Rincian Harian'], ',', '"', '\\');
            fputcsv($output, ['Tanggal', 'Omzet Online', 'Omzet Offline', 'Total Omzet', 'Tunai Offline', 'QRIS Offline', 'Transfer Online', 'QRIS Online', 'Online Lainnya'], ',', '"', '\\');

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
            fputcsv($output, ['Produk', 'Jumlah Terjual', 'Omzet Produk', 'Laba Kotor Produk'], ',', '"', '\\');

            foreach ($report['bestSellers'] as $product) {
                fputcsv($output, [
                    $product['name'],
                    $product['quantity'],
                    number_format($product['revenue'], 2, '.', ''),
                    number_format($product['profit'], 2, '.', ''),
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
        $salesByProduct = [];
        $onlineRevenue = 0.0;
        $offlineRevenue = (float) $transactions->sum('total');
        $totalHpp = 0.0;

        foreach ($revenueOrders as $order) {
            $date = $order->created_at->toDateString();
            $this->initializeDailyRow($daily, $date);
            $revenue = (float) $order->total + (float) $order->shipping_cost;
            $onlineRevenue += $revenue;
            $daily[$date]['online'] += $revenue;

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
                $totalHpp += (float) $item->harga_modal_at_purchase * $item->quantity;
            }
        }

        foreach ($transactions as $transaction) {
            $date = $transaction->date->toDateString();
            $this->initializeDailyRow($daily, $date);
            $daily[$date]['offline'] += (float) $transaction->total;
            $paymentColumn = in_array($transaction->payment_method, ['qris', 'qris-tf'], true)
                ? 'store_qris'
                : 'cash';
            $daily[$date][$paymentColumn] += (float) $transaction->total;

            foreach ($transaction->items as $item) {
                $this->addProductSale($salesByProduct, $item);
                $totalHpp += (float) $item->harga_modal_at_purchase * $item->quantity;
            }
        }

        $bestSellers = collect($salesByProduct)
            ->sortByDesc('quantity')
            ->values();
        $totalRevenue = $onlineRevenue + $offlineRevenue;

        return [
            'orders' => $orders,
            'transactions' => $transactions,
            'onlineRevenue' => $onlineRevenue,
            'offlineRevenue' => $offlineRevenue,
            'totalRevenue' => $totalRevenue,
            'totalHpp' => $totalHpp,
            'grossProfit' => $totalRevenue - $totalHpp,
            'dailyBreakdown' => collect($daily)->sortKeys()->values(),
            'bestSellers' => $bestSellers,
            'maxProductQuantity' => max(1, (int) $bestSellers->max('quantity')),
            'filters' => $filters,
            'startDate' => $startDate,
            'endDate' => $endDate,
        ];
    }

    /**
     * @param  array<string, array<string, float|string>>  $daily
     */
    private function initializeDailyRow(array &$daily, string $date): void
    {
        $daily[$date] ??= [
            'date' => $date,
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
