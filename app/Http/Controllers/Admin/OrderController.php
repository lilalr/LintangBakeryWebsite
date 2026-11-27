<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Models\StockMutation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class OrderController extends Controller
{
    public const STATUSES = [
        'Menunggu Konfirmasi',
        'Sedang Diproduksi',
        'Siap Dikirim',
        'Sedang Dikirim',
        'Selesai/Lunas',
        'Dibatalkan',
    ];

    public function index(Request $request): View
    {
        $query = Order::with('items');

        $selectedStatus = $request->query('status');
        if (empty($selectedStatus) || $selectedStatus === 'active') {
            $query->whereIn('status', [
                'Menunggu Konfirmasi',
                'Sedang Diproduksi',
                'Siap Dikirim',
                'Sedang Dikirim',
            ]);
            $selectedStatus = 'active';
        } elseif ($selectedStatus !== 'all' && in_array($selectedStatus, self::STATUSES, true)) {
            $query->where('status', $selectedStatus);
        }

        $startDate = $request->query('start_date');
        $endDate = $request->query('end_date');

        if (! empty($startDate)) {
            $query->whereDate('created_at', '>=', $startDate);
        }
        if (! empty($endDate)) {
            $query->whereDate('created_at', '<=', $endDate);
        }

        $selectedSort = $request->query('sort', 'priority');
        if ($selectedSort === 'latest') {
            $query->latest();
        } elseif ($selectedSort === 'updated') {
            $query->latest('updated_at');
        } elseif ($selectedSort === 'oldest') {
            $query->oldest();
        } else {
            // Default: 'priority' -> Urutkan berdasarkan status yang paling penting untuk ditindaklanjuti/diupdate admin:
            // 1. Menunggu Konfirmasi (pesanan baru masuk)
            // 2. Menunggu Verifikasi Admin (bukti pelunasan DP)
            // 3. Siap Dikirim (siap kirim / perlu surat jalan & resi)
            // 4. Sedang Diproduksi
            // 5. Sedang Dikirim
            // 6. Selesai/Lunas
            // 7. Dibatalkan
            // Untuk status yang sama, urutkan tanggal terbaru (created_at desc)
            $query->orderByRaw("
                CASE
                    WHEN status = 'Menunggu Konfirmasi' THEN 1
                    WHEN settlement_status = 'Menunggu Verifikasi Admin' THEN 2
                    WHEN status = 'Siap Dikirim' THEN 3
                    WHEN status = 'Sedang Diproduksi' THEN 4
                    WHEN status = 'Sedang Dikirim' THEN 5
                    WHEN status = 'Selesai/Lunas' THEN 6
                    WHEN status = 'Dibatalkan' THEN 7
                    ELSE 8
                END ASC
            ")->latest();
            $selectedSort = 'priority';
        }

        $orders = $query->paginate(15)->withQueryString();

        return view('admin.orders.index', [
            'orders' => $orders,
            'statuses' => self::STATUSES,
            'currentStatus' => $selectedStatus,
            'currentSort' => $selectedSort,
            'startDate' => $startDate,
            'endDate' => $endDate,
        ]);
    }

    public function show(Order $order): View
    {
        $order->load('items.product', 'user');

        return view('admin.orders.show', [
            'order' => $order,
            'statuses' => self::STATUSES,
        ]);
    }

    public function deliveryOrder(Order $order): View
    {
        $order->load('items.product');

        return view('admin.orders.delivery-order', compact('order'));
    }

    public function invoice(Order $order): View
    {
        $order->load('items.product');

        return view('admin.orders.invoice', compact('order'));
    }

    public function update(Request $request, Order $order): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'in:'.implode(',', self::STATUSES)],
            'courier_name' => ['nullable', 'string', 'max:120'],
            'tracking_number' => ['nullable', 'string', 'max:120'],
            'shipping_notes' => ['nullable', 'string', 'max:500'],
            'settlement_status' => ['nullable', 'string', 'in:Menunggu Verifikasi Admin,Lunas / Disetujui,Ditolak'],
        ]);

        DB::transaction(function () use ($order, $validated): void {
            $shippedStatuses = ['Sedang Dikirim', 'Sudah Sampai', 'Selesai/Lunas'];
            if (in_array($validated['status'], $shippedStatuses, true) && ! $order->stock_deducted) {
                foreach ($order->items as $item) {
                    if ($item->product_id) {
                        $product = Product::find($item->product_id);
                        if ($product) {
                            $product->decrement('stock', $item->quantity);
                            StockMutation::create([
                                'product_id' => $product->id,
                                'user_id' => auth()->id(),
                                'admin_name' => auth()->user()?->name ?? 'Admin',
                                'type' => 'out',
                                'quantity' => $item->quantity,
                                'balance' => $product->fresh()->stock,
                                'reason' => 'Penjualan online',
                                'reference_number' => $order->code,
                                'mutation_date' => now()->toDateString(),
                                'notes' => 'Pesanan online dikirim ke '.$order->buyer.' ('.$order->city.') via '.($validated['courier_name'] ?? 'Ekspedisi'),
                            ]);
                        }
                    }
                }
                $order->stock_deducted = true;
            }

            if (isset($validated['courier_name'])) {
                $order->courier_name = $validated['courier_name'];
            }
            if (isset($validated['tracking_number'])) {
                $order->tracking_number = $validated['tracking_number'];
            }
            if (isset($validated['shipping_notes'])) {
                $order->shipping_notes = $validated['shipping_notes'];
            }

            if (! empty($validated['settlement_status'])) {
                $order->settlement_status = $validated['settlement_status'];
                if ($validated['settlement_status'] === 'Lunas / Disetujui') {
                    $order->remaining = 0;
                }
            }

            $order->status = $validated['status'];
            $order->save();
        });

        return back()->with('success', 'Data & status pesanan '.$order->code.' berhasil diperbarui.');
    }
}
