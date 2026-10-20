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
            'payment_method' => ['required', 'in:cash,qris,qris-tf,edc'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'items' => ['nullable', 'array', 'min:1'],
            'items.*.product_id' => ['required_with:items', 'exists:products,id'],
            'items.*.quantity' => ['required_with:items', 'integer', 'min:1', 'max:10000'],
            'product_id' => ['required_without:items', 'nullable', 'exists:products,id'],
            'quantity' => ['required_without:items', 'nullable', 'integer', 'min:1', 'max:10000'],
        ]);

        $rawItems = $validated['items'] ?? [
            [
                'product_id' => $validated['product_id'],
                'quantity' => $validated['quantity'],
            ],
        ];

        $transaction = DB::transaction(function () use ($validated, $rawItems): OfflineTransaction {
            $total = 0;
            $totalHpp = 0.0;
            $itemRows = [];

            foreach ($rawItems as $raw) {
                $product = Product::query()->lockForUpdate()->findOrFail($raw['product_id']);
                abort_unless(in_array($product->sales_channel, ['offline', 'both'], true), 422, 'Produk '.$product->name.' tidak tersedia untuk transaksi offline.');
                abort_if($product->stock < $raw['quantity'], 422, 'Stok produk '.$product->name.' tidak mencukupi (sisa: '.$product->stock.').');

                $qty = (int) $raw['quantity'];
                $subtotal = $product->price_per_bal * $qty;
                $hpp = (float) $product->cost_price * $qty;

                $total += $subtotal;
                $totalHpp += $hpp;

                $itemRows[] = [
                    'product' => $product,
                    'quantity' => $qty,
                    'unit_price' => $product->price_per_bal,
                    'subtotal' => $subtotal,
                    'cost_price' => $product->cost_price,
                ];
            }

            $transaction = OfflineTransaction::create([
                'buyer_name' => $validated['buyer_name'],
                'date' => $validated['date'],
                'total' => $total,
                'total_hpp' => $totalHpp,
                'total_laba' => $total - $totalHpp,
                'payment_method' => $validated['payment_method'],
                'notes' => $validated['notes'] ?? null,
            ]);

            foreach ($itemRows as $row) {
                $product = $row['product'];
                $transaction->items()->create([
                    'product_id' => $product->id,
                    'name' => $product->name,
                    'quantity' => $row['quantity'],
                    'unit_price' => $row['unit_price'],
                    'subtotal' => $row['subtotal'],
                    'harga_modal_at_purchase' => $row['cost_price'],
                    'harga_jual_at_purchase' => $row['unit_price'],
                ]);

                $product->decrement('stock', $row['quantity']);

                \App\Models\StockMutation::create([
                    'product_id' => $product->id,
                    'user_id' => auth()->id(),
                    'admin_name' => auth()->user()?->name ?? 'Kasir',
                    'type' => 'out',
                    'quantity' => $row['quantity'],
                    'balance' => $product->fresh()->stock,
                    'reason' => 'Penjualan offline',
                    'reference_number' => 'TRX-'.$transaction->id,
                    'mutation_date' => $validated['date'],
                    'notes' => 'Pembeli: '.$validated['buyer_name'].(! empty($validated['notes']) ? ' ('.$validated['notes'].')' : ''),
                ]);
            }

            return $transaction;
        });

        return back()
            ->with('success', 'Transaksi kasir berhasil disimpan.')
            ->with('trx_success', [
                'id' => $transaction->id,
                'code' => 'TRX-'.$transaction->id,
                'total' => $transaction->total,
                'buyer' => $validated['buyer_name'],
                'payment_method' => $validated['payment_method'],
                'notes' => $validated['notes'] ?? '',
            ]);
    }

    public function destroy(OfflineTransaction $transaction): RedirectResponse
    {
        DB::transaction(function () use ($transaction): void {
            foreach ($transaction->items as $item) {
                if ($item->product_id) {
                    $product = Product::find($item->product_id);
                    if ($product) {
                        $product->increment('stock', $item->quantity);
                        \App\Models\StockMutation::create([
                            'product_id' => $product->id,
                            'user_id' => auth()->id(),
                            'admin_name' => auth()->user()?->name ?? 'Admin',
                            'type' => 'in',
                            'quantity' => $item->quantity,
                            'balance' => $product->fresh()->stock,
                            'reason' => 'Pembatalan (Void) kasir',
                            'reference_number' => 'VOID-TRX-'.$transaction->id,
                            'mutation_date' => now()->toDateString(),
                            'notes' => 'Pembatalan transaksi #'.$transaction->id.' ('.$transaction->buyer_name.')',
                        ]);
                    }
                }
            }
            $transaction->delete();
        });

        return back()->with('success', 'Transaksi dibatalkan (void) dan stok telah dikembalikan.');
    }
}
