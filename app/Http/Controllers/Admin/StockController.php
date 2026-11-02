<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\StockBatch;
use App\Models\StockMovement;
use App\Services\StockService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
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
        $products = $this->offlineProducts()
            ->with(['batches' => fn ($query) => $query->where('quantity', '>', 0)->whereNotNull('expiry_date')->orderBy('expiry_date')])
            ->get();
        $alerts = $stock->alerts();
        $batches = StockBatch::with('product')
            ->where('quantity', '>', 0)
            ->whereNotNull('expiry_date')
            ->whereHas('product', fn ($query) => $query->whereIn('sales_channel', ['offline', 'both']))
            ->orderBy('expiry_date')
            ->get();

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

    /** Ekspor kartu stok ke CSV (dibuka di Excel) dengan filter yang sama seperti halaman Kartu Stok. */
    public function exportCard(Request $request): StreamedResponse
    {
        $filters = $this->cardFilters($request);
        $product = $filters['product_id'] ?? null
            ? Product::find($filters['product_id'])
            : $this->offlineProducts()->first();
        abort_unless($product, 404);

        $rows = $this->cardQuery($product->id, $filters)->get();
        $filename = 'kartu-stok-'.str($product->name)->slug().'-'.now()->format('Ymd').'.csv';

        return response()->streamDownload(function () use ($rows, $product): void {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF"); // BOM supaya Excel membaca UTF-8
            $write = fn (array $row) => fputcsv($out, $row, ';', '"', '');

            $write(['Tanggal', 'Produk', 'Jenis', 'Jumlah', 'Alasan', 'Catatan', 'Referensi', 'Admin', 'Saldo']);
            foreach ($rows as $movement) {
                $write([
                    $movement->date->format('d/m/Y'),
                    $this->csvSafe($product->name),
                    $movement->type,
                    ($movement->type === 'Masuk' ? '+' : '-').$movement->quantity,
                    $this->csvSafe($movement->reason),
                    $this->csvSafe((string) $movement->note),
                    $this->csvSafe((string) $movement->reference),
                    $this->csvSafe($movement->admin_name),
                    $movement->balance,
                ]);
            }
            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
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
        return view('admin.stock.opname', ['products' => $this->offlineProducts()->get()]);
    }

    /** Stok opname: selisih stok fisik dan sistem dicatat sebagai mutasi baru (Opname). */
    public function opname(Request $request, StockService $stock): RedirectResponse
    {
        $data = $request->validate([
            'date' => ['required', 'date'],
            'physical' => ['required', 'array'],
            'physical.*' => ['nullable', 'integer', 'min:0', 'max:100000'],
            'note' => ['nullable', 'string', 'max:500'],
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
        $note = trim('Stok opname. '.($data['note'] ?? ''));
        $adjusted = 0;

        DB::transaction(function () use ($counted, $products, $stock, $data, $reference, $note, &$adjusted): void {
            foreach ($counted as $productId => $physical) {
                $product = $products->get((int) $productId);

                if ($product && $stock->adjustTo($product, (int) $physical, ['date' => $data['date'], 'reference' => $reference, 'note' => $note], 'Opname')) {
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
