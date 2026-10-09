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
        $order->update($validated);

        return back()->with('success', 'Status pesanan diperbarui.');
    }
}
