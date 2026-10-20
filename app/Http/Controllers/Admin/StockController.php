<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\StockMutation;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

class StockController extends Controller
{
    public function index(Request $request): View
    {
        $products = Product::orderBy('name')->get();

        // Peringatan stok menipis (Proses 5)
        $lowStockProducts = Product::whereColumn('stock', '<=', 'min_stock')
            ->orderBy('stock')
            ->get();

        // Peringatan mendekati kedaluwarsa (14 hari ke depan)
        $today = Carbon::today();
        $soon = Carbon::today()->addDays(14);
        $expiringMutations = StockMutation::with('product')
            ->where('type', 'in')
            ->whereNotNull('expiry_date')
            ->whereBetween('expiry_date', [$today, $soon])
            ->orderBy('expiry_date')
            ->get();

        // Kartu Stok (Proses 4)
        $selectedProductId = $request->query('product_id');
        $startDate = $request->query('start_date');
        $endDate = $request->query('end_date');
        $selectedType = $request->query('type');

        $mutationsQuery = StockMutation::with('product', 'user')->latest('mutation_date')->latest('id');

        if ($selectedProductId) {
            $mutationsQuery->where('product_id', $selectedProductId);
        }

        if ($startDate) {
            $mutationsQuery->whereDate('mutation_date', '>=', $startDate);
        }

        if ($endDate) {
            $mutationsQuery->whereDate('mutation_date', '<=', $endDate);
        }

        if ($selectedType && in_array($selectedType, ['in', 'out'], true)) {
            $mutationsQuery->where('type', $selectedType);
        }

        $mutations = $mutationsQuery->paginate(15)->withQueryString();

        // Total kerugian dari barang rusak / kedaluwarsa
        $totalLoss = StockMutation::where('loss_amount', '>', 0)->sum('loss_amount');

        return view('admin.stock.index', compact(
            'products',
            'lowStockProducts',
            'expiringMutations',
            'mutations',
            'selectedProductId',
            'startDate',
            'endDate',
            'selectedType',
            'totalLoss'
        ));
    }

    /**
     * Proses 1: Barang Masuk (Hasil Produksi / Restock)
     */
    public function storeInbound(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'product_id' => ['required', 'exists:products,id'],
            'quantity' => ['required', 'integer', 'min:1', 'max:100000'],
            'mutation_date' => ['required', 'date'],
            'source' => ['nullable', 'string', 'max:150'],
            'expiry_date' => ['nullable', 'date'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        DB::transaction(function () use ($validated): void {
            $product = Product::query()->lockForUpdate()->findOrFail($validated['product_id']);
            $product->increment('stock', $validated['quantity']);

            StockMutation::create([
                'product_id' => $product->id,
                'user_id' => Auth::id(),
                'admin_name' => Auth::user()?->name ?? 'Admin',
                'type' => 'in',
                'quantity' => $validated['quantity'],
                'balance' => $product->fresh()->stock,
                'reason' => 'Barang Masuk (Produksi)',
                'source' => $validated['source'] ?: 'Dapur Produksi',
                'expiry_date' => $validated['expiry_date'] ?? null,
                'reference_number' => 'BM-'.date('Ymd').'-'.strtoupper(Str::random(4)),
                'loss_amount' => 0,
                'notes' => $validated['notes'] ?? null,
                'mutation_date' => $validated['mutation_date'],
            ]);
        });

        return back()->with('success', 'Barang masuk berhasil dicatat dan stok bertambah.');
    }

    /**
     * Proses 3: Barang Keluar Manual (Rusak, Kedaluwarsa, Sampel)
     */
    public function storeOutbound(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'product_id' => ['required', 'exists:products,id'],
            'quantity' => ['required', 'integer', 'min:1', 'max:100000'],
            'reason' => ['required', 'in:Rusak,Kedaluwarsa,Sampel/Tester,Koreksi Stok,Lainnya'],
            'mutation_date' => ['required', 'date'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $product = Product::findOrFail($validated['product_id']);
        if ($validated['quantity'] > $product->stock) {
            return back()->withInput()->withErrors([
                'quantity' => 'Jumlah melebihi stok yang tersedia (sisa stok: '.$product->stock.' bal).',
            ]);
        }

        DB::transaction(function () use ($validated, $product): void {
            $lockedProduct = Product::query()->lockForUpdate()->findOrFail($product->id);
            if ($validated['quantity'] > $lockedProduct->stock) {
                abort(422, 'Stok tidak mencukupi saat proses penyimpanan.');
            }

            $lockedProduct->decrement('stock', $validated['quantity']);

            $isLoss = in_array($validated['reason'], ['Rusak', 'Kedaluwarsa'], true);
            $lossAmount = $isLoss ? ($validated['quantity'] * (float) $lockedProduct->cost_price) : 0;

            StockMutation::create([
                'product_id' => $lockedProduct->id,
                'user_id' => Auth::id(),
                'admin_name' => Auth::user()?->name ?? 'Admin',
                'type' => 'out',
                'quantity' => $validated['quantity'],
                'balance' => $lockedProduct->fresh()->stock,
                'reason' => 'Barang Keluar ('.$validated['reason'].')',
                'source' => null,
                'expiry_date' => null,
                'reference_number' => 'BK-'.date('Ymd').'-'.strtoupper(Str::random(4)),
                'loss_amount' => $lossAmount,
                'notes' => $validated['notes'] ?? null,
                'mutation_date' => $validated['mutation_date'],
            ]);
        });

        return back()->with('success', 'Barang keluar manual berhasil dicatat.');
    }

    /**
     * Quick adjustment fallback
     */
    public function update(Request $request, Product $product): RedirectResponse
    {
        abort_unless(in_array($product->sales_channel, ['offline', 'both'], true), 404);
        $validated = $request->validate([
            'adjustment' => ['required', 'integer', 'min:1', 'max:100000'],
        ]);

        DB::transaction(function () use ($product, $validated): void {
            $product->increment('stock', $validated['adjustment']);
            StockMutation::create([
                'product_id' => $product->id,
                'user_id' => Auth::id(),
                'admin_name' => Auth::user()?->name ?? 'Admin',
                'type' => 'in',
                'quantity' => $validated['adjustment'],
                'balance' => $product->fresh()->stock,
                'reason' => 'Penambahan Stok Cepat',
                'reference_number' => 'ADJ-'.date('Ymd').'-'.strtoupper(Str::random(4)),
                'mutation_date' => now()->toDateString(),
            ]);
        });

        return back()->with('success', 'Stok '.$product->name.' berhasil ditambahkan.');
    }
}
