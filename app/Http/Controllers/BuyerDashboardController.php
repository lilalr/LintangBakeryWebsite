<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BuyerDashboardController extends Controller
{
    public function index(Request $request): View
    {
        $orders = $request->user()->orders()->with('items')->latest()->get();

        return view('store.dashboard', compact('orders'));
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'phone' => ['required', 'string', 'max:30'],
            'address' => ['required', 'string', 'max:1000'],
        ]);
        $request->user()->update($validated);

        return back()->with('success', 'Profil berhasil diperbarui.');
    }

    public function showOrder(Request $request, \App\Models\Order $order): View
    {
        abort_unless($order->user_id === $request->user()->id, 403, 'Akses pesanan ditolak.');
        $order->load('items.product');

        return view('store.orders.show', compact('order'));
    }

    public function uploadSettlement(Request $request, \App\Models\Order $order): RedirectResponse
    {
        abort_unless($order->user_id === $request->user()->id, 403, 'Akses pesanan ditolak.');
        abort_unless($order->payment_type === 'dp' && $order->remaining > 0, 422, 'Pesanan ini tidak memerlukan pelunasan sisa.');

        $validated = $request->validate([
            'payment_proof' => ['required', 'image', 'max:4096'],
            'payment_method' => ['nullable', 'in:bank_transfer,qris'],
        ]);

        $proofPath = $request->file('payment_proof')->store('payment-proofs', 'public');

        $order->update([
            'settlement_proof' => $proofPath,
            'final_payment_proof' => $proofPath,
            'final_payment_method' => $validated['payment_method'] ?? 'bank_transfer',
            'settlement_status' => 'Menunggu Verifikasi Admin',
            'remaining_paid_at' => now(),
        ]);

        return back()->with('success', 'Bukti pelunasan berhasil diunggah. Tim Lintang Bakery akan segera memverifikasi pembayaran Anda.');
    }
}
