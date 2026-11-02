<?php

namespace App\Services;

use App\Models\Product;
use App\Models\StockBatch;
use App\Models\StockMovement;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Satu-satunya pintu untuk mengubah stok barang jadi (offline).
 * Setiap perubahan menulis satu baris kartu stok (tidak pernah diedit/dihapus).
 */
class StockService
{
    public const EXPIRY_ALERT_DAYS = 14;

    /** Alasan barang keluar yang dihitung sebagai kerugian. */
    public const LOSS_REASONS = ['Rusak', 'Kedaluwarsa'];

    /** Sumber barang masuk yang membentuk batch (bisa punya tanggal kedaluwarsa). */
    public const BATCH_SOURCES = ['Produksi', 'Retur', 'Lainnya'];

    /**
     * @param  array{date?: string, reference?: string, note?: string, expiry_date?: string|null, admin_name?: string}  $meta
     */
    public function receive(Product $product, int $quantity, string $reason, array $meta = []): StockMovement
    {
        return DB::transaction(function () use ($product, $quantity, $reason, $meta): StockMovement {
            $locked = Product::query()->lockForUpdate()->findOrFail($product->id);
            $locked->increment('stock', $quantity);

            if (in_array($reason, self::BATCH_SOURCES, true)) {
                StockBatch::create([
                    'product_id' => $locked->id,
                    'quantity' => $quantity,
                    'received_date' => $meta['date'] ?? now()->toDateString(),
                    'expiry_date' => $meta['expiry_date'] ?? null,
                ]);
            }

            return $this->record($locked, 'Masuk', $quantity, $reason, $meta);
        });
    }

    /**
     * @param  array{date?: string, reference?: string, note?: string, admin_name?: string, batch_id?: int}  $meta
     *                                                                                                       batch_id: ambil dari batch tertentu dulu (misalnya membuang batch kedaluwarsa), sisanya FEFO.
     */
    public function issue(Product $product, int $quantity, string $reason, array $meta = []): StockMovement
    {
        return DB::transaction(function () use ($product, $quantity, $reason, $meta): StockMovement {
            $locked = Product::query()->lockForUpdate()->findOrFail($product->id);

            if ($quantity > $locked->stock) {
                throw ValidationException::withMessages([
                    'quantity' => "Jumlah melebihi stok. Stok tersisa {$locked->stock} bal.",
                ]);
            }

            $locked->decrement('stock', $quantity);
            $this->consumeBatches($locked, $quantity, $meta['batch_id'] ?? null);

            return $this->record($locked, 'Keluar', $quantity, $reason, $meta);
        });
    }

    /** Koreksi stok lewat mutasi baru (bukan mengubah atau menghapus baris lama). */
    public function adjustTo(Product $product, int $newStock, array $meta = [], string $reason = 'Koreksi'): ?StockMovement
    {
        $difference = $newStock - $product->fresh()->stock;

        if ($difference === 0) {
            return null;
        }

        return $difference > 0
            ? $this->receive($product, $difference, $reason, $meta)
            : $this->issue($product, abs($difference), $reason, $meta);
    }

    /** Mencatat saldo awal untuk produk yang sudah punya stok tetapi belum punya riwayat. */
    public function ensureOpeningBalance(Product $product): void
    {
        if ($product->stock > 0 && ! $product->stockMovements()->exists()) {
            $this->record($product, 'Masuk', $product->stock, 'Saldo awal', ['reference' => 'SALDO-AWAL']);
        }
    }

    /** Produk menipis dan batch yang mendekati kedaluwarsa (satu batch terdekat per produk). */
    public function alerts(): array
    {
        $low = Product::whereIn('sales_channel', ['offline', 'both'])
            ->whereColumn('stock', '<=', 'min_stock')
            ->orderBy('stock')
            ->get();

        $expiring = StockBatch::with('product')
            ->where('quantity', '>', 0)
            ->whereNotNull('expiry_date')
            ->whereDate('expiry_date', '<=', now()->addDays(self::EXPIRY_ALERT_DAYS)->toDateString())
            ->orderBy('expiry_date')
            ->get()
            ->unique('product_id')
            ->values();

        return ['low' => $low, 'expiring' => $expiring];
    }

    /** FEFO: batch yang paling dekat kedaluwarsa dipakai lebih dulu. */
    private function consumeBatches(Product $product, int $quantity, ?int $batchId = null): void
    {
        $left = $quantity;

        $batches = StockBatch::where('product_id', $product->id)
            ->where('quantity', '>', 0)
            ->when($batchId, fn ($query) => $query->orderByRaw('id = ? desc', [$batchId]))
            ->orderByRaw('expiry_date is null')
            ->orderBy('expiry_date')
            ->orderBy('id')
            ->lockForUpdate()
            ->get();

        foreach ($batches as $batch) {
            if ($left <= 0) {
                break;
            }

            $take = min($batch->quantity, $left);
            $left -= $take;

            if ($take >= $batch->quantity) {
                $batch->delete();
            } else {
                $batch->update(['quantity' => $batch->quantity - $take]);
            }
        }
    }

    private function record(Product $product, string $type, int $quantity, string $reason, array $meta): StockMovement
    {
        return StockMovement::create([
            'product_id' => $product->id,
            'product_name' => $product->name,
            'date' => $meta['date'] ?? now()->toDateString(),
            'type' => $type,
            'quantity' => $quantity,
            'reason' => $reason,
            'reference' => $meta['reference'] ?? null,
            'note' => $meta['note'] ?? null,
            'admin_name' => $meta['admin_name'] ?? (auth()->user()?->name ?? 'Sistem'),
            'balance' => $product->stock,
            'unit_price' => $product->price_per_bal,
        ]);
    }
}
