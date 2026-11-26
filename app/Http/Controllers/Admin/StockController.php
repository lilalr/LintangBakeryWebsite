<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\StockBatch;
use App\Models\StockMovement;
use App\Models\StockMutation;
use App\Services\StockService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class StockController extends Controller
{
    public const ISSUE_REASONS = ['Rusak', 'Kedaluwarsa', 'Sampel', 'Lainnya'];

    /** Ringkasan stok: status, stok minimum, kedaluwarsa terdekat, dan peringatan. */
    public function index(StockService $stock): View
    {
        // Sinkronisasi batch kedaluwarsa dari mutasi masuk jika batch belum tercatat
        $inboundMutations = StockMutation::where('type', 'in')->whereNotNull('expiry_date')->get();
        foreach ($inboundMutations as $m) {
            $exists = StockBatch::where('product_id', $m->product_id)
                ->where('expiry_date', $m->expiry_date)
                ->exists();
            if (! $exists) {
                StockBatch::create([
                    'product_id' => $m->product_id,
                    'quantity' => $m->quantity,
                    'received_date' => $m->mutation_date ?? $m->created_at?->toDateString() ?? now()->toDateString(),
                    'expiry_date' => $m->expiry_date,
                ]);
            }
        }

        $alerts = $stock->alerts();
        $batches = StockBatch::with('product')
            ->where('quantity', '>', 0)
            ->whereNotNull('expiry_date')
            ->whereHas('product', fn ($query) => $query->whereIn('sales_channel', ['offline', 'both']))
            ->orderBy('expiry_date')
            ->get();

        $products = $this->offlineProducts()
            ->with(['batches' => fn ($query) => $query->where('quantity', '>', 0)->whereNotNull('expiry_date')->orderBy('expiry_date')])
            ->get()
            ->sortBy(function ($product) {
                $stockRank = match ($product->stockStatus()) {
                    'Habis' => 0,
                    'Menipis' => 1,
                    default => 2,
                };
                $earliestBatch = $product->batches->first();
                $expiryRank = ($earliestBatch && $earliestBatch->daysLeft() <= StockService::EXPIRY_ALERT_DAYS) ? 0 : 1;

                return [$stockRank, $expiryRank, $product->name];
            })
            ->values();

        return view('admin.stock.index', [
            'batches' => $batches,
            'products' => $products,
            'low' => $alerts['low'],
            'expiring' => $alerts['expiring'],
        ]);
    }

    /** Kartu stok: mutasi berurutan per produk dan rentang tanggal. */
    public function card(Request $request): View
    {
        $filters = $this->cardFilters($request);
        $products = $this->offlineProducts()->get();
        $productId = $filters['product_id'] ?? $products->first()?->id;

        $movements = $this->cardQuery($productId, $filters)->paginate(30)->withQueryString();

        return view('admin.stock.card', [
            'products' => $products,
            'movements' => $movements,
            'productId' => (int) $productId,
            'start' => $filters['start'] ?? null,
            'end' => $filters['end'] ?? null,
        ]);
    }

    /** Ekspor kartu stok ke Excel (.xls) dengan filter yang sama seperti halaman Kartu Stok. */
    public function exportCard(Request $request): StreamedResponse
    {
        $filters = $this->cardFilters($request);
        $product = $filters['product_id'] ?? null
            ? Product::find($filters['product_id'])
            : $this->offlineProducts()->first();
        abort_unless($product, 404);

        $rows = $this->cardQuery($product->id, $filters)->get();
        $filename = 'kartu-stok-'.str($product->name)->slug().'-'.now()->format('Ymd').'.xls';

        return response()->streamDownload(function () use ($rows, $product): void {
            $headers = ['Tanggal', 'Produk', 'Jenis', 'Jumlah', 'Alasan', 'Catatan', 'Referensi', 'Admin', 'Saldo'];

            echo "<!DOCTYPE html>\n<html><head><meta charset=\"UTF-8\"></head><body>\n";
            echo "<table border=\"1\">\n<thead><tr>";
            foreach ($headers as $h) {
                echo '<th>'.htmlspecialchars($h).'</th>';
            }
            echo "</tr></thead>\n<tbody>\n";

            foreach ($rows as $movement) {
                $qty = ($movement->type === 'Masuk' ? '+' : '-').$movement->quantity;
                echo '<tr>';
                echo '<td>'.htmlspecialchars($movement->date->format('d/m/Y')).'</td>';
                echo '<td>'.htmlspecialchars($product->name).'</td>';
                echo '<td>'.htmlspecialchars($movement->type).'</td>';
                echo '<td>'.htmlspecialchars($qty).'</td>';
                echo '<td>'.htmlspecialchars($movement->reason).'</td>';
                echo '<td>'.htmlspecialchars((string) $movement->note).'</td>';
                echo '<td>'.htmlspecialchars((string) $movement->reference).'</td>';
                echo '<td>'.htmlspecialchars($movement->admin_name).'</td>';
                echo '<td>'.htmlspecialchars((string) $movement->balance).'</td>';
                echo "</tr>\n";
            }

            echo "</tbody>\n</table>\n</body></html>";
        }, $filename, ['Content-Type' => 'application/vnd.ms-excel']);
    }

    /** Membuang batch yang sudah kedaluwarsa: stok berkurang dan tercatat sebagai kerugian. */
    public function expireBatch(StockBatch $batch, StockService $stock): RedirectResponse
    {
        abort_if($batch->quantity <= 0 || ! $batch->expiry_date, 404);

        if ($batch->daysLeft() >= 0) {
            return back()->withErrors(['batch' => 'Batch ini belum kedaluwarsa.']);
        }

        $quantity = $batch->quantity;
        $product = $batch->product;
        $stock->issue($product, $quantity, 'Kedaluwarsa', [
            'batch_id' => $batch->id,
            'note' => 'Batch kedaluwarsa '.$batch->expiry_date->format('d/m/Y'),
        ]);

        return redirect()->route('admin.stock.index')
            ->with('success', "{$quantity} bal {$product->name} yang kedaluwarsa dikeluarkan dan dicatat sebagai kerugian.");
    }

    public function opnameForm(): View
    {
        $driver = DB::connection()->getDriverName();
        $yearExpr = match ($driver) {
            'pgsql' => 'CAST(EXTRACT(YEAR FROM date) AS INTEGER)',
            'sqlite' => "CAST(strftime('%Y', date) AS INTEGER)",
            default => 'YEAR(date)',
        };
        $monthExpr = match ($driver) {
            'pgsql' => 'CAST(EXTRACT(MONTH FROM date) AS INTEGER)',
            'sqlite' => "CAST(strftime('%m', date) AS INTEGER)",
            default => 'MONTH(date)',
        };

        $rekapKedaluwarsa = StockMovement::query()
            ->where('type', 'Keluar')
            ->where('reason', 'Kedaluwarsa')
            ->selectRaw("{$yearExpr} as tahun, {$monthExpr} as bulan, product_name, SUM(quantity) as total_quantity, COUNT(*) as total_kejadian")
            ->groupByRaw("{$yearExpr}, {$monthExpr}, product_name")
            ->orderByRaw("{$yearExpr} DESC, {$monthExpr} DESC, product_name ASC")
            ->get();

        return view('admin.stock.opname', [
            'products' => $this->offlineProducts()->get(),
            'rekapKedaluwarsa' => $rekapKedaluwarsa,
        ]);
    }

    /** Stok opname: selisih stok fisik dan sistem dicatat sebagai mutasi baru (Opname). */
    public function opname(Request $request, StockService $stock): RedirectResponse
    {
        $data = $request->validate([
            'date' => ['required', 'date'],
            'physical' => ['required', 'array'],
            'physical.*' => ['nullable', 'integer', 'min:0', 'max:100000'],
            'note' => ['nullable', 'string', 'max:500'],
            'reason' => ['nullable', 'array'],
            'reason.*' => ['nullable', 'string', 'max:100'],
            'note_product' => ['nullable', 'array'],
            'note_product.*' => ['nullable', 'string', 'max:500'],
        ], [
            'date.required' => 'Tanggal opname wajib diisi.',
            'physical.*.integer' => 'Stok fisik harus berupa angka bulat.',
            'physical.*.min' => 'Stok fisik tidak boleh negatif.',
        ]);

        $counted = collect($data['physical'])->filter(fn ($value) => $value !== null);

        if ($counted->isEmpty()) {
            throw ValidationException::withMessages(['physical' => 'Isi stok fisik minimal satu produk.']);
        }

        $products = $this->offlineProducts()->get()->keyBy('id');
        $reference = 'OPNAME-'.Carbon::parse($data['date'])->format('Ymd');
        $globalNote = trim($data['note'] ?? '');
        $adjusted = 0;

        DB::transaction(function () use ($counted, $products, $stock, $data, $reference, $globalNote, &$adjusted): void {
            foreach ($counted as $productId => $physical) {
                $product = $products->get((int) $productId);
                if (! $product) {
                    continue;
                }

                $productReason = $data['reason'][$productId] ?? null;
                $productNote = $data['note_product'][$productId] ?? null;

                $noteParts = array_filter([
                    'Stok opname',
                    $productReason ? 'Alasan: '.$productReason : null,
                    $productNote,
                    $globalNote,
                ]);
                $note = implode('. ', $noteParts);

                if ($stock->adjustTo($product, (int) $physical, ['date' => $data['date'], 'reference' => $reference, 'note' => $note], 'Opname')) {
                    $adjusted++;
                }
            }
        });

        return redirect()->route('admin.stock.index')
            ->with('success', "Stok opname selesai: {$counted->count()} produk dihitung, {$adjusted} produk disesuaikan.");
    }

    public function receiveForm(): View
    {
        return view('admin.stock.receive', ['products' => $this->offlineProducts()->get()]);
    }

    public function receive(Request $request, StockService $stock): RedirectResponse
    {
        $data = $request->validate([
            'date' => ['required', 'date'],
            'product_id' => ['required', $this->offlineProductRule()],
            'quantity' => ['required', 'integer', 'min:1', 'max:100000'],
            'source' => ['required', Rule::in(StockService::BATCH_SOURCES)],
            'expiry_date' => ['nullable', 'date', 'after_or_equal:date'],
            'note' => ['nullable', 'string', 'max:1000'],
        ], $this->messages());

        $product = Product::findOrFail($data['product_id']);
        $stock->receive($product, (int) $data['quantity'], $data['source'], [
            'date' => $data['date'],
            'expiry_date' => $data['expiry_date'] ?? null,
            'note' => $data['note'] ?? null,
        ]);

        return redirect()->route('admin.stock.index')
            ->with('success', "Barang masuk {$data['quantity']} bal {$product->name} berhasil dicatat.");
    }

    public function issueForm(): View
    {
        return view('admin.stock.issue', [
            'products' => $this->offlineProducts()->get(),
            'reasons' => self::ISSUE_REASONS,
        ]);
    }

    public function issue(Request $request, StockService $stock): RedirectResponse
    {
        $data = $request->validate([
            'date' => ['required', 'date'],
            'product_id' => ['required', $this->offlineProductRule()],
            'quantity' => ['required', 'integer', 'min:1', 'max:100000'],
            'reason' => ['required', Rule::in(self::ISSUE_REASONS)],
            'note' => ['nullable', 'string', 'max:1000'],
        ], $this->messages());

        $product = Product::findOrFail($data['product_id']);
        $stock->issue($product, (int) $data['quantity'], $data['reason'], [
            'date' => $data['date'],
            'note' => $data['note'] ?? null,
        ]);

        $suffix = in_array($data['reason'], StockService::LOSS_REASONS, true) ? ' dan dicatat sebagai kerugian' : '';

        return redirect()->route('admin.stock.index')
            ->with('success', "Barang keluar {$data['quantity']} bal {$product->name} berhasil dicatat{$suffix}.");
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

    private function cardFilters(Request $request): array
    {
        return $request->validate([
            'product_id' => ['nullable', 'integer'],
            'start' => ['nullable', 'date'],
            'end' => ['nullable', 'date', 'after_or_equal:start'],
        ], ['end.after_or_equal' => 'Tanggal akhir tidak boleh sebelum tanggal awal.']);
    }

    private function cardQuery(?int $productId, array $filters)
    {
        return StockMovement::query()
            ->where('product_id', $productId)
            ->when($filters['start'] ?? null, fn ($query, $start) => $query->whereDate('date', '>=', $start))
            ->when($filters['end'] ?? null, fn ($query, $end) => $query->whereDate('date', '<=', $end))
            ->orderBy('date')
            ->orderBy('id');
    }

    /** Mencegah isi sel diperlakukan sebagai rumus di Excel. */
    private function csvSafe(string $value): string
    {
        return preg_match('/^[=+\-@\t\r]/', $value) ? "'".$value : $value;
    }

    private function offlineProducts()
    {
        return Product::whereIn('sales_channel', ['offline', 'both'])->orderBy('name');
    }

    private function offlineProductRule()
    {
        return Rule::exists('products', 'id')->whereIn('sales_channel', ['offline', 'both']);
    }

    private function messages(): array
    {
        return [
            'date.required' => 'Tanggal wajib diisi.',
            'product_id.required' => 'Pilih produk terlebih dahulu.',
            'product_id.exists' => 'Pilih produk terlebih dahulu.',
            'quantity.required' => 'Jumlah wajib diisi.',
            'quantity.integer' => 'Jumlah harus berupa angka bulat.',
            'quantity.min' => 'Jumlah harus lebih dari 0.',
            'source.required' => 'Sumber wajib dipilih.',
            'reason.required' => 'Alasan wajib diisi.',
            'reason.in' => 'Alasan wajib diisi.',
            'expiry_date.after_or_equal' => 'Tanggal kedaluwarsa tidak boleh sebelum tanggal masuk.',
        ];
    }
}
