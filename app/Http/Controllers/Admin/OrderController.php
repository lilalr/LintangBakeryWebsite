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
        $query = Order::with('items')->latest();

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

        $orders = $query->paginate(15)->withQueryString();

        return view('admin.orders.index', [
            'orders' => $orders,
            'statuses' => self::STATUSES,
            'currentStatus' => $selectedStatus,
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
