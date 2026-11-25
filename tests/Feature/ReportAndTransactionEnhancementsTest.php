<?php

namespace Tests\Feature;

use App\Models\OfflineTransaction;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportAndTransactionEnhancementsTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create(['role' => 'admin']);
    }

    private function createProduct(array $attributes = []): Product
    {
        static $counter = 1;

        return Product::create(array_merge([
            'name' => 'Produk Test '.$counter++,
            'category' => 'Kue Kering',
            'description' => 'Deskripsi produk test',
            'price_per_bal' => 50000,
            'cost_price' => 30000,
            'sales_channel' => 'both',
            'stock' => 100,
            'min_stock' => 10,
        ], $attributes));
    }

    public function test_sidebar_has_updated_order_with_pelanggan_at_bottom(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.dashboard'));
        $response->assertOk();

        $content = $response->getContent();

        // Urutan: Dashboard, Kasir, Pesanan, Pelanggan, Laporan
        $posDashboard = strpos($content, '<span>Dashboard</span>');
        $posKasir = strpos($content, '<span>Kasir</span>');
        $posPesanan = strpos($content, '<span>Pesanan</span>');
        $posPelanggan = strpos($content, '<span>Pelanggan</span>');
        $posLaporan = strpos($content, '<span>Laporan</span>');

        $this->assertNotFalse($posDashboard);
        $this->assertNotFalse($posKasir);
        $this->assertNotFalse($posPesanan);
        $this->assertNotFalse($posPelanggan);
        $this->assertNotFalse($posLaporan);

        $this->assertTrue($posDashboard < $posKasir);
        $this->assertTrue($posKasir < $posPesanan);
        $this->assertTrue($posPesanan < $posPelanggan);
        $this->assertTrue($posPelanggan < $posLaporan);
    }

    public function test_kasir_menu_does_not_have_riwayat_tab_and_focuses_on_pos(): void
    {
        $this->createProduct();

        $res = $this->actingAs($this->admin)->get(route('admin.transactions.index'));
        $res->assertOk();
        $res->assertDontSee('data-tab="riwayat"', false);
        $res->assertSee('Keranjang Pesanan');
        $res->assertSee('Bayar Sekarang', false);
    }

    public function test_admin_report_supports_filters_and_has_riwayat_transactions(): void
    {
        $product = $this->createProduct(['price_per_bal' => 45000]);

        $offline = OfflineTransaction::create([
            'buyer_name' => 'Sutopo Pelanggan Setia',
            'date' => now()->toDateString(),
            'total' => 90000,
            'total_hpp' => 50000,
            'total_laba' => 40000,
            'payment_method' => 'cash',
        ]);
        $offline->items()->create([
            'product_id' => $product->id,
            'name' => $product->name,
            'quantity' => 2,
            'unit_price' => 45000,
            'subtotal' => 90000,
            'harga_modal_at_purchase' => 25000,
            'harga_jual_at_purchase' => 45000,
        ]);

        $order = Order::create([
            'user_id' => $this->admin->id,
            'code' => 'LB-TEST-002',
            'buyer' => 'Ahmad Pembeli',
            'phone' => '08987654321',
            'address' => 'Jl. Gejayan',
            'city' => 'Yogyakarta',
            'province' => 'DI Yogyakarta',
            'postal_code' => '55281',
            'delivery_method' => 'pickup',
            'payment_method' => 'qris',
            'payment_type' => 'full',
            'status' => 'Selesai/Lunas',
            'subtotal' => 45000,
            'shipping_cost' => 0,
            'total' => 45000,
        ]);
        $order->items()->create([
            'product_id' => $product->id,
            'name' => $product->name,
            'quantity' => 1,
            'unit_price' => 45000,
            'subtotal' => 45000,
            'harga_modal_at_purchase' => 25000,
        ]);

        // Default view opens Riwayat Transaksi tab and shows both
        $resDefault = $this->actingAs($this->admin)->get(route('admin.reports.index'));
        $resDefault->assertOk();
        $resDefault->assertSee('Riwayat Penjualan Offline &amp; Online', false);
        $resDefault->assertSee('Kanal');
        $resDefault->assertDontSee('>Tipe</th>', false);
        $resDefault->assertDontSee('Kasir Toko: Rp');
        $resDefault->assertSee('Sutopo Pelanggan Setia');
        $resDefault->assertSee('Ahmad Pembeli');
        $resDefault->assertSee('Struk');
        $resDefault->assertSee('Void');
        $resDefault->assertSee('Pesanan');
        $resDefault->assertSee('Faktur');

        // Search Sutopo
        $resSearch = $this->actingAs($this->admin)->get(route('admin.reports.index', ['search_buyer' => 'Sutopo']));
        $resSearch->assertOk();
        $resSearch->assertSee('Sutopo Pelanggan Setia');
        $resSearch->assertDontSee('Ahmad Pembeli');

        // Filter channel offline
        $resOffline = $this->actingAs($this->admin)->get(route('admin.reports.index', ['channel' => 'offline']));
        $resOffline->assertOk();
        $resOffline->assertSee('Sutopo Pelanggan Setia');
        $resOffline->assertDontSee('Ahmad Pembeli');

        // Filter channel online
        $resOnline = $this->actingAs($this->admin)->get(route('admin.reports.index', ['channel' => 'online']));
        $resOnline->assertOk();
        $resOnline->assertDontSee('Sutopo Pelanggan Setia');
        $resOnline->assertSee('Ahmad Pembeli');

        // Filter payment cash
        $resCash = $this->actingAs($this->admin)->get(route('admin.reports.index', ['payment_method' => 'cash']));
        $resCash->assertOk();
        $resCash->assertSee('Sutopo Pelanggan Setia');
        $resCash->assertDontSee('Ahmad Pembeli');

        // Filter payment qris
        $resQris = $this->actingAs($this->admin)->get(route('admin.reports.index', ['payment_method' => 'qris']));
        $resQris->assertOk();
        $resQris->assertDontSee('Sutopo Pelanggan Setia');
        $resQris->assertSee('Ahmad Pembeli');
    }

    public function test_admin_report_export_csv_respects_filters(): void
    {
        $product = $this->createProduct(['name' => 'Roti Manis Cokelat', 'price_per_bal' => 30000]);

        $offline = OfflineTransaction::create([
            'buyer_name' => 'Sutopo Ekspor',
            'date' => now()->toDateString(),
            'total' => 60000,
            'total_hpp' => 40000,
            'total_laba' => 20000,
            'payment_method' => 'cash',
        ]);
        $offline->items()->create([
            'product_id' => $product->id,
            'name' => $product->name,
            'quantity' => 2,
            'unit_price' => 30000,
            'subtotal' => 60000,
            'harga_modal_at_purchase' => 20000,
            'harga_jual_at_purchase' => 30000,
        ]);

        $response = $this->actingAs($this->admin)->get(route('admin.reports.export', [
            'search_buyer' => 'Sutopo',
            'channel' => 'offline',
            'payment_method' => 'cash',
        ]));

        $response->assertOk();
        $this->assertEquals('text/csv; charset=UTF-8', $response->headers->get('content-type'));

        $csvContent = $response->streamedContent();
        $this->assertStringContainsString('Sutopo Ekspor', $csvContent);
        $this->assertStringContainsString('Roti Manis Cokelat', $csvContent);
        $this->assertStringContainsString('Tunai', $csvContent);
    }

    public function test_product_management_does_not_display_stock_column(): void
    {
        $this->createProduct(['name' => 'Kue Kering Nastar', 'stock' => 500]);

        $response = $this->actingAs($this->admin)->get(route('admin.produk.index'));
        $response->assertOk();
        $response->assertSee('Kue Kering Nastar');
        $response->assertDontSee('<th>Stok</th>', false);
        $response->assertDontSee('500 bal');
    }

    public function test_admin_can_create_and_update_product_without_stock_input(): void
    {
        $response = $this->actingAs($this->admin)->post(route('admin.produk.store'), [
            'name' => 'Biskuit Susu Cokelat',
            'description' => 'Biskuit susu manis dan renyah.',
            'price_per_bal' => 75000,
        ]);

        $response->assertRedirect(route('admin.produk.index'));
        $product = Product::where('name', 'Biskuit Susu Cokelat')->firstOrFail();
        $this->assertEquals(0, $product->stock);

        $updateResponse = $this->actingAs($this->admin)->put(route('admin.produk.update', $product), [
            'name' => 'Biskuit Susu Vanilla',
            'description' => 'Biskuit susu vanilla harum dan renyah.',
            'price_per_bal' => 80000,
        ]);

        $updateResponse->assertRedirect(route('admin.produk.index'));
        $this->assertEquals('Biskuit Susu Vanilla', $product->fresh()->name);
        $this->assertEquals(0, $product->fresh()->stock);
    }

    public function test_orders_table_has_streamlined_columns(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.orders.index'));
        $response->assertOk();
        $response->assertSee('>No</th>', false);
        $response->assertSee('Kode Pesanan');
        $response->assertSee('Total Tagihan');
        $response->assertDontSee('<th>Produk</th>', false);
        $response->assertDontSee('<th>Total & Pembayaran</th>', false);
    }

    public function test_orders_index_supports_status_filter_and_formatted_dates(): void
    {
        $product = $this->createProduct(['price_per_bal' => 50000]);

        $orderPending = Order::create([
            'user_id' => $this->admin->id,
            'code' => 'LB-FILTER-01',
            'buyer' => 'Pembeli Pending',
            'phone' => '081234567890',
            'address' => 'Alamat A',
            'city' => 'Sleman',
            'province' => 'DI Yogyakarta',
            'postal_code' => '55281',
            'delivery_method' => 'pickup',
            'payment_method' => 'bank_transfer',
            'payment_type' => 'full',
            'status' => 'Menunggu Konfirmasi',
            'subtotal' => 50000,
            'shipping_cost' => 0,
            'total' => 50000,
        ]);

        $orderShipped = Order::create([
            'user_id' => $this->admin->id,
            'code' => 'LB-FILTER-02',
            'buyer' => 'Pembeli Dikirim',
            'phone' => '081234567891',
            'address' => 'Alamat B',
            'city' => 'Bantul',
            'province' => 'DI Yogyakarta',
            'postal_code' => '55282',
            'delivery_method' => 'delivery',
            'payment_method' => 'qris',
            'payment_type' => 'full',
            'status' => 'Sedang Dikirim',
            'subtotal' => 50000,
            'shipping_cost' => 10000,
            'total' => 50000,
        ]);

        $orderCompleted = Order::create([
            'user_id' => $this->admin->id,
            'code' => 'LB-FILTER-03',
            'buyer' => 'Pembeli Selesai',
            'phone' => '081234567892',
            'address' => 'Alamat C',
            'city' => 'Yogyakarta',
            'province' => 'DI Yogyakarta',
            'postal_code' => '55283',
            'delivery_method' => 'delivery',
            'payment_method' => 'qris',
            'payment_type' => 'full',
            'status' => 'Selesai/Lunas',
            'subtotal' => 50000,
            'shipping_cost' => 0,
            'total' => 50000,
        ]);

        // Default view: shows active orders only (Menunggu Konfirmasi & Sedang Dikirim), hides Selesai/Lunas
        $resDefault = $this->actingAs($this->admin)->get(route('admin.orders.index'));
        $resDefault->assertOk();
        $resDefault->assertSee('LB-FILTER-01');
        $resDefault->assertSee('LB-FILTER-02');
        $resDefault->assertDontSee('LB-FILTER-03');
        $resDefault->assertSee('onchange="this.form.submit()"', false);

        // Filter status: Menunggu Konfirmasi
        $resFilter = $this->actingAs($this->admin)->get(route('admin.orders.index', ['status' => 'Menunggu Konfirmasi']));
        $resFilter->assertOk();
        $resFilter->assertSee('LB-FILTER-01');
        $resFilter->assertDontSee('LB-FILTER-02');
        $resFilter->assertDontSee('LB-FILTER-03');
        $resFilter->assertSee($orderPending->created_at->format('d/m/Y'));
        $resFilter->assertSee($orderPending->created_at->format('H:i'));

        // Filter status: Selesai/Lunas
        $resCompleted = $this->actingAs($this->admin)->get(route('admin.orders.index', ['status' => 'Selesai/Lunas']));
        $resCompleted->assertOk();
        $resCompleted->assertSee('LB-FILTER-03');
        $resCompleted->assertDontSee('LB-FILTER-01');
        $resCompleted->assertDontSee('LB-FILTER-02');

        // Filter date
        $resDate = $this->actingAs($this->admin)->get(route('admin.orders.index', [
            'status' => 'all',
            'start_date' => now()->toDateString(),
            'end_date' => now()->toDateString(),
        ]));
        $resDate->assertOk();
        $resDate->assertSee('LB-FILTER-01');
        $resDate->assertSee('LB-FILTER-02');
        $resDate->assertSee('LB-FILTER-03');

        // All status
        $resAll = $this->actingAs($this->admin)->get(route('admin.orders.index', ['status' => 'all']));
        $resAll->assertOk();
        $resAll->assertSee('LB-FILTER-01');
        $resAll->assertSee('LB-FILTER-02');
        $resAll->assertSee('LB-FILTER-03');
    }

    public function test_reports_table_has_streamlined_columns(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.reports.index'));
        $response->assertOk();
        $response->assertSee('Kode / No. Nota');
        $response->assertSee('Total Penjualan');
        $response->assertDontSee('<th>Item Pembelian</th>', false);
        $response->assertDontSee('Unduh CSV Sesuai Filter Ini');
        $response->assertSee('Export Excel');
        $response->assertSee('id="trxPaginationWrap"', false);
    }
}
