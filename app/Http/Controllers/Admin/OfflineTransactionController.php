<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\OfflineTransaction;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class OfflineTransactionController extends Controller
{
    public function index(): View
    {
        $transactions = OfflineTransaction::with('items')->latest()->paginate(12);
        $products = Product::whereIn('sales_channel', ['offline', 'both'])->orderBy('name')->get();

        return view('admin.transactions.index', compact('transactions', 'products'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'buyer_name' => ['required', 'string', 'max:160'],
            'date' => ['required', 'date'],
            'product_id' => ['required', 'exists:products,id'],
            'quantity' => ['required', 'integer', 'min:1', 'max:10000'],
            'payment_method' => ['required', 'in:cash,qris,qris-tf'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        DB::transaction(function () use ($validated): void {
            $product = Product::query()->lockForUpdate()->findOrFail($validated['product_id']);
            abort_unless(in_array($product->sales_channel, ['offline', 'both'], true), 422, 'Produk tidak tersedia untuk transaksi offline.');
            abort_if($product->stock < $validated['quantity'], 422, 'Stok produk tidak mencukupi.');

            $quantity = $validated['quantity'];
            $subtotal = $product->price_per_bal * $quantity;
            $totalHpp = (float) $product->cost_price * $quantity;
            $transaction = OfflineTransaction::create([
                'buyer_name' => $validated['buyer_name'],
                'date' => $validated['date'],
                'total' => $subtotal,
                'total_hpp' => $totalHpp,
                'total_laba' => $subtotal - $totalHpp,
                'payment_method' => $validated['payment_method'],
                'notes' => $validated['notes'] ?? null,
            ]);
            $transaction->items()->create([
                'product_id' => $product->id,
                'name' => $product->name,
                'quantity' => $validated['quantity'],
                'unit_price' => $product->price_per_bal,
                'subtotal' => $subtotal,
                'harga_modal_at_purchase' => $product->cost_price,
                'harga_jual_at_purchase' => $product->price_per_bal,
            ]);
            $product->decrement('stock', $validated['quantity']);
        });

        return back()->with('success', 'Transaksi offline berhasil disimpan.');
    }

    public function destroy(OfflineTransaction $transaction): RedirectResponse
    {
        $transaction->delete();

        return back()->with('success', 'Transaksi dihapus.');
    }
}
