<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OrderController extends Controller
{
    public const STATUSES = [
        'Menunggu Konfirmasi',
        'Pembayaran DP Disetujui',
        'Sedang Diproduksi',
        'Siap Dikirim',
        'Sedang Dikirim',
        'Sudah Sampai',
        'Selesai/Lunas',
    ];

    public function index(): View
    {
        $orders = Order::with('items')->latest()->paginate(15);

        return view('admin.orders.index', ['orders' => $orders, 'statuses' => self::STATUSES]);
    }

    public function update(Request $request, Order $order): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'in:'.implode(',', self::STATUSES)],
        ]);

        \Illuminate\Support\Facades\DB::transaction(function () use ($order, $validated): void {
            $shippedStatuses = ['Sedang Dikirim', 'Sudah Sampai', 'Selesai/Lunas'];
            if (in_array($validated['status'], $shippedStatuses, true) && ! $order->stock_deducted) {
                foreach ($order->items as $item) {
                    if ($item->product_id) {
                        $product = \App\Models\Product::find($item->product_id);
                        if ($product) {
                            $product->decrement('stock', $item->quantity);
                            \App\Models\StockMutation::create([
                                'product_id' => $product->id,
                                'user_id' => auth()->id(),
                                'admin_name' => auth()->user()?->name ?? 'Admin',
                                'type' => 'out',
                                'quantity' => $item->quantity,
                                'balance' => $product->fresh()->stock,
                                'reason' => 'Penjualan online',
                                'reference_number' => $order->code,
                                'mutation_date' => now()->toDateString(),
                                'notes' => 'Pesanan online dikirim ke '.$order->buyer.' ('.$order->city.')',
                            ]);
                        }
                    }
                }
                $order->stock_deducted = true;
            }

            $order->status = $validated['status'];
            $order->save();
        });

        return back()->with('success', 'Status pesanan diperbarui.');
    }
}
