<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\StockBatch;
use App\Models\StockMovement;
use App\Models\User;
use App\Services\StockService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StockExtrasTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create(['role' => 'admin', 'name' => 'Admin Lintang']);
    }

    private function product(int $stock = 100, string $name = 'Nastar Demo'): Product
    {
        return Product::create([
            'name' => $name,
            'category' => 'Kue Lebaran',
            'description' => 'Demo.',
            'price_per_bal' => 80000,
            'sales_channel' => 'both',
            'stock' => $stock,
            'min_stock' => 20,
        ]);
    }

    // ---------- Ekspor kartu stok ----------

    public function test_stock_card_exports_csv_with_filters(): void
    {
        $product = $this->product(0);
        $service = app(StockService::class);
        $service->receive($product, 100, 'Produksi', ['date' => '2026-10-01']);
        $service->issue($product, 3, 'Penjualan offline', ['date' => '2026-10-02', 'reference' => 'TRX-00001']);
        $service->issue($product, 2, 'Rusak', ['date' => '2026-10-10', 'note' => '=HYPERLINK("x")']);

        $response = $this->actingAs($this->admin)
            ->get(route('admin.stock.card.export', ['product_id' => $product->id, 'start' => '2026-10-05', 'end' => '2026-10-31']))
            ->assertOk()
            ->assertHeader('content-type', 'text/csv; charset=UTF-8');
        $csv = $response->streamedContent();

        $this->assertStringStartsWith("\xEF\xBB\xBF", $csv);
        $this->assertStringContainsString('Tanggal;Produk;Jenis;Jumlah;Alasan;Catatan;Referensi;Admin;Saldo', $csv);
        $this->assertStringContainsString('10/10/2026', $csv);
        $this->assertStringContainsString('Rusak', $csv);
        $this->assertStringNotContainsString('TRX-00001', $csv);          // di luar rentang tanggal
        $this->assertStringContainsString("'=HYPERLINK", $csv);           // rumus dinetralkan
    }

    public function test_stock_card_page_has_export_and_print_buttons(): void
    {
        $this->product(10);

        $this->actingAs($this->admin)->get(route('admin.stock.card'))
            ->assertOk()
            ->assertSee('Ekspor Excel (CSV)')
            ->assertSee('Cetak');
    }

    // ---------- Daftar batch + keluarkan ----------

    public function test_expired_batch_can_be_discarded_and_counts_as_loss(): void
    {
        $product = $this->product(0);
        $service = app(StockService::class);
        $service->receive($product, 5, 'Produksi', ['expiry_date' => now()->subDay()->toDateString()]);
        $service->receive($product, 3, 'Produksi', ['expiry_date' => now()->subDays(2)->toDateString()]);
        $service->receive($product, 20, 'Produksi', ['expiry_date' => now()->addDays(30)->toDateString()]);
        $batch = StockBatch::where('quantity', 5)->firstOrFail();

        $this->actingAs($this->admin)->post(route('admin.stock.batch.expire', $batch))
            ->assertRedirect(route('admin.stock.index'));

        // Hanya batch yang dipilih yang hilang (bukan batch lain yang lebih dulu kedaluwarsa).
        $this->assertDatabaseMissing('stock_batches', ['id' => $batch->id]);
        $this->assertDatabaseHas('stock_batches', ['product_id' => $product->id, 'quantity' => 3]);
        $this->assertDatabaseHas('stock_batches', ['product_id' => $product->id, 'quantity' => 20]);
        $this->assertDatabaseHas('products', ['id' => $product->id, 'stock' => 23]);
        $this->assertDatabaseHas('stock_movements', ['type' => 'Keluar', 'reason' => 'Kedaluwarsa', 'quantity' => 5, 'balance' => 23]);

        $this->get(route('admin.reports.index'))->assertOk()->assertSee('400.000'); // 5 bal x Rp80.000
    }

    public function test_batch_that_is_not_expired_cannot_be_discarded(): void
    {
        $product = $this->product(0);
        app(StockService::class)->receive($product, 5, 'Produksi', ['expiry_date' => now()->addDays(3)->toDateString()]);
        $batch = StockBatch::firstOrFail();

        $this->actingAs($this->admin)->post(route('admin.stock.batch.expire', $batch))
            ->assertSessionHasErrors('batch');

        $this->assertDatabaseHas('products', ['id' => $product->id, 'stock' => 5]);
        $this->assertSame(0, StockMovement::where('reason', 'Kedaluwarsa')->count());
    }

    public function test_stock_page_lists_batches_with_status_and_discard_button(): void
    {
        $product = $this->product(0, 'Putri Salju Demo');
        $service = app(StockService::class);
        $service->receive($product, 5, 'Produksi', ['expiry_date' => now()->subDay()->toDateString()]);
        $service->receive($product, 7, 'Produksi', ['expiry_date' => now()->addDays(5)->toDateString()]);

        $this->actingAs($this->admin)->get(route('admin.stock.index'))
            ->assertOk()
            ->assertSee('Batch dengan tanggal kedaluwarsa')
            ->assertSee('Kedaluwarsa')
            ->assertSee('H-5')
            ->assertSee('Keluarkan');
    }

    // ---------- Stok opname ----------

    public function test_opname_records_differences_as_new_movements(): void
    {
        $short = $this->product(100, 'Nastar Demo');
        $extra = $this->product(50, 'Kastengel Demo');
        $same = $this->product(30, 'Sagu Demo');
        $skipped = $this->product(40, 'Lidah Kucing Demo');

        $this->actingAs($this->admin)->post(route('admin.stock.opname'), [
            'date' => '2026-10-20',
            'note' => 'Opname akhir bulan',
            'physical' => [
                $short->id => '95',
                $extra->id => '53',
                $same->id => '30',
                $skipped->id => '',
            ],
        ])->assertRedirect(route('admin.stock.index'))
            ->assertSessionHas('success', 'Stok opname selesai: 3 produk dihitung, 2 produk disesuaikan.');

        $this->assertDatabaseHas('products', ['id' => $short->id, 'stock' => 95]);
        $this->assertDatabaseHas('products', ['id' => $extra->id, 'stock' => 53]);
        $this->assertDatabaseHas('products', ['id' => $same->id, 'stock' => 30]);
        $this->assertDatabaseHas('products', ['id' => $skipped->id, 'stock' => 40]);

        $this->assertDatabaseHas('stock_movements', ['product_id' => $short->id, 'type' => 'Keluar', 'reason' => 'Opname', 'quantity' => 5, 'balance' => 95, 'reference' => 'OPNAME-20261020']);
        $this->assertDatabaseHas('stock_movements', ['product_id' => $extra->id, 'type' => 'Masuk', 'reason' => 'Opname', 'quantity' => 3, 'balance' => 53]);
        $this->assertSame(0, StockMovement::where('product_id', $same->id)->count());
        $this->assertSame(0, StockMovement::where('product_id', $skipped->id)->count());

        // Opname bukan kerugian rusak/kedaluwarsa.
        $this->get(route('admin.reports.index'))->assertOk()->assertSee('Belum ada kerugian stok.');
    }

    public function test_opname_requires_at_least_one_physical_count_and_rejects_negative(): void
    {
        $product = $this->product(10);

        $this->actingAs($this->admin)->post(route('admin.stock.opname'), [
            'date' => '2026-10-20',
            'physical' => [$product->id => ''],
        ])->assertSessionHasErrors(['physical' => 'Isi stok fisik minimal satu produk.']);

        $this->post(route('admin.stock.opname'), [
            'date' => '2026-10-20',
            'physical' => [$product->id => '-3'],
        ])->assertSessionHasErrors('physical.'.$product->id);

        $this->assertDatabaseHas('products', ['id' => $product->id, 'stock' => 10]);
        $this->assertSame(0, StockMovement::count());
    }

    public function test_opname_page_renders_products_with_system_stock(): void
    {
        $this->product(77, 'Nastar Demo');

        $this->actingAs($this->admin)->get(route('admin.stock.opname.form'))
            ->assertOk()
            ->assertSee('Stok opname')
            ->assertSee('Nastar Demo')
            ->assertSee('77 bal');
    }

    public function test_new_stock_routes_are_admin_only(): void
    {
        $buyer = User::factory()->create(['role' => 'buyer']);
        $product = $this->product(5);
        app(StockService::class)->receive($product, 1, 'Produksi', ['expiry_date' => now()->subDay()->toDateString()]);
        $batch = StockBatch::firstOrFail();

        foreach ([['get', route('admin.stock.card.export')], ['get', route('admin.stock.opname.form')], ['post', route('admin.stock.opname')], ['post', route('admin.stock.batch.expire', $batch)]] as [$method, $url]) {
            $this->$method($url)->assertRedirect('/masuk');
            $this->actingAs($buyer)->$method($url)->assertForbidden();
            auth()->logout();
        }
    }
}
