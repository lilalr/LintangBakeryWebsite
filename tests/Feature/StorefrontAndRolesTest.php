<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class StorefrontAndRolesTest extends TestCase
{
    use RefreshDatabase;

    public function test_storefront_is_available(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('Lintang Bakery');
    }

    public function test_admin_area_is_restricted_to_admin_users(): void
    {
        $this->get('/admin')->assertRedirect('/masuk');

        $buyer = User::factory()->create(['role' => 'buyer']);
        $this->actingAs($buyer)->get('/admin')->assertForbidden();

        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin)->get('/admin')->assertOk();
    }

    public function test_public_registration_always_creates_a_buyer(): void
    {
        $this->post('/daftar', [
            'name' => 'Pengguna Baru',
            'email' => 'user@example.test',
            'phone' => '08123456789',
            'address' => 'Yogyakarta',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ])->assertRedirect('/akun');

        $this->assertDatabaseHas('users', [
            'email' => 'user@example.test',
            'role' => 'buyer',
        ]);
    }

    public function test_buyer_can_place_an_order_from_the_cart(): void
    {
        $buyer = User::factory()->create(['role' => 'buyer']);
        $product = Product::create([
            'name' => 'Nastar Demo',
            'category' => 'Kue Lebaran',
            'description' => 'Nastar dengan selai nanas.',
            'price_per_bal' => 80000,
            'cost_price' => 25000,
            'sales_channel' => 'both',
            'stock' => 100,
        ]);

        $this->actingAs($buyer)
            ->post(route('cart.store', $product), ['quantity' => 2])
            ->assertRedirect('/keranjang');

        Storage::fake('public');

        $this->post(route('checkout.store'), [
            'buyer' => 'Pembeli Demo',
            'phone' => '08123456789',
            'address' => 'Jalan Mawar 1',
            'city' => 'Kota Yogyakarta',
            'province' => 'D.I. Yogyakarta',
            'payment_type' => 'dp',
            'payment_method' => 'bank_transfer',
            'payment_proof' => UploadedFile::fake()->image('bukti_transfer.jpg'),
        ])->assertRedirect('/akun')->assertSessionMissing('cart');

        $this->assertDatabaseHas('orders', [
            'user_id' => $buyer->id,
            'total' => 160000,
            'shipping_cost' => 20000,
            'dp' => 104000,
            'remaining' => 76000,
        ]);
        $this->assertDatabaseHas('order_items', [
            'product_id' => $product->id,
            'quantity' => 2,
            'harga_modal_at_purchase' => 25000,
            'harga_jual_at_purchase' => 80000,
        ]);
    }

    public function test_admin_offline_sale_reduces_product_stock(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $product = Product::create([
            'name' => 'Kastengel Demo',
            'category' => 'Kue Keju',
            'description' => 'Kastengel keju.',
            'price_per_bal' => 90000,
            'cost_price' => 20000,
            'sales_channel' => 'both',
            'stock' => 10,
        ]);

        $this->actingAs($admin)
            ->post(route('admin.transactions.store'), [
                'buyer_name' => 'Toko Demo',
                'date' => now()->toDateString(),
                'product_id' => $product->id,
                'quantity' => 3,
                'payment_method' => 'cash',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('products', ['id' => $product->id, 'stock' => 7]);
        $this->assertDatabaseHas('offline_transactions', ['buyer_name' => 'Toko Demo', 'total' => 270000]);
        $this->assertDatabaseHas('offline_transactions', [
            'buyer_name' => 'Toko Demo',
            'total_hpp' => 60000,
            'total_laba' => 210000,
        ]);
        $this->assertDatabaseHas('offline_transaction_items', [
            'product_id' => $product->id,
            'harga_modal_at_purchase' => 20000,
            'harga_jual_at_purchase' => 90000,
        ]);
    }

    public function test_sales_report_summarizes_the_period_and_exports_a_csv(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $product = Product::create([
            'name' => 'Nastar Akuntansi',
            'category' => 'Kue Kering',
            'description' => 'Nastar untuk laporan.',
            'price_per_bal' => 90000,
            'cost_price' => 30000,
            'sales_channel' => 'both',
            'stock' => 10,
        ]);

        $this->actingAs($admin)->post(route('admin.transactions.store'), [
            'buyer_name' => 'Pembeli Laporan',
            'date' => now()->toDateString(),
            'product_id' => $product->id,
            'quantity' => 2,
            'payment_method' => 'qris',
        ])->assertRedirect();

        $buyer = User::factory()->create(['role' => 'buyer']);
        $this->actingAs($buyer)->post(route('cart.store', $product), ['quantity' => 1])
            ->assertRedirect('/keranjang');
        $this->post(route('checkout.store'), [
            'buyer' => 'Pembeli DP',
            'phone' => '08123456789',
            'address' => 'Jalan Mawar 1',
            'city' => 'Kota Yogyakarta',
            'province' => 'D.I. Yogyakarta',
            'payment_type' => 'dp',
            'payment_method' => 'bank_transfer',
            'payment_proof' => UploadedFile::fake()->image('bukti_dp.jpg'),
        ])->assertRedirect('/akun');
        Order::query()->where('buyer', 'Pembeli DP')
            ->firstOrFail()->update(['status' => 'Sedang Diproduksi']);

        $this->actingAs($admin);
        $report = $this->get(route('admin.reports.index', ['period' => now()->format('Y-m')]))
            ->assertOk()
            ->assertViewHas('totalTransactions', 2)
            ->assertViewHas('totalItemsSold', 3)
            ->assertSee('Pembayaran')
            ->assertSee('Jumlah transaksi')
            ->assertSee('Penjualan harian')
            ->assertSee('Produk terlaris')
            ->assertSee('Rp 180.000')
            ->assertSee('Rp 280.000')
            ->assertSee('Total Penjualan (Kotor)')
            ->assertSee('Nastar Akuntansi');
        $report->assertDontSee('Total modal (HPP)');
        $report->assertDontSee('Laba kotor');

        $export = $this->get(route('admin.reports.export', ['period' => now()->format('Y-m')]))
            ->assertOk()
            ->assertHeader('content-type', 'text/csv; charset=UTF-8');
        $this->assertStringContainsString('Nastar Akuntansi', $export->streamedContent());
        $this->assertStringContainsString('Total Penjualan (Kotor)', $export->streamedContent());
    }

    public function test_admin_can_view_customers_list_and_details(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $buyer = User::factory()->create([
            'role' => 'buyer',
            'name' => 'Budi Santoso',
            'email' => 'budi@example.com',
            'phone' => '081299887766',
            'address' => 'Jl. Malioboro No. 10, Yogyakarta',
        ]);

        $this->actingAs($admin);

        $this->get(route('admin.customers.index'))
            ->assertOk()
            ->assertSee('Data Pelanggan')
            ->assertSee('Budi Santoso')
            ->assertSee('budi@example.com')
            ->assertSee('081299887766');

        $this->get(route('admin.customers.show', $buyer))
            ->assertOk()
            ->assertSee('Budi Santoso')
            ->assertSee('budi@example.com')
            ->assertSee('Jl. Malioboro No. 10, Yogyakarta');
    }

    public function test_online_store_displays_reseller_100_bal_minimum_requirement(): void
    {
        $product1 = Product::create([
            'name' => 'Kue Tambang Reseller Test',
            'category' => 'Kue Kering Tradisional',
            'description' => 'Kue tambang renyah.',
            'price_per_bal' => 85000,
            'cost_price' => 55000,
            'sales_channel' => 'both',
            'stock' => 500,
        ]);
        $product2 = Product::create([
            'name' => 'Kue Keciput Reseller Test',
            'category' => 'Kue Kering Tradisional',
            'description' => 'Kue keciput renyah.',
            'price_per_bal' => 90000,
            'cost_price' => 60000,
            'sales_channel' => 'both',
            'stock' => 500,
        ]);

        $buyer = User::factory()->create(['role' => 'buyer']);

        // Case 1: When adding 30 bal of product1 (below min mix 50 bal)
        $this->actingAs($buyer)
            ->post(route('cart.store', $product1), ['quantity' => 30])
            ->assertRedirect('/keranjang');

        $response = $this->get(route('cart.index'));
        $response->assertOk()
            ->assertSee('Ketentuan Mix Produk: Minimal 50 Bal per Varian')
            ->assertSee('Min. mix 50 bal');

        // Case 2: Update product1 to 50 bal, but total is 50 bal (below total min 100 bal)
        $this->post(route('cart.store', $product1), ['quantity' => 20])
            ->assertRedirect('/keranjang');

        $response2 = $this->get(route('cart.index'));
        $response2->assertOk()
            ->assertSee('Total Pembelian Belum Mencapai 100 Bal')
            ->assertSee('50 / 100 bal');

        // Case 3: Add 50 bal of product2 -> 50/50 mix reaches 100 bal total!
        $this->post(route('cart.store', $product2), ['quantity' => 50])
            ->assertRedirect('/keranjang');

        $response3 = $this->get(route('cart.index'));
        $response3->assertOk()
            ->assertSee('Syarat Minimal Reseller Terpenuhi!')
            ->assertSee('Lanjut ke Checkout (100 Bal)');
    }

    public function test_buyer_can_view_order_detail_and_tracking(): void
    {
        $buyer = User::factory()->create(['role' => 'buyer']);
        $product = Product::create([
            'name' => 'Kue Kering Demo',
            'category' => 'Tradisional',
            'description' => 'Enak dan renyah.',
            'price_per_bal' => 80000,
            'cost_price' => 50000,
            'sales_channel' => 'both',
            'stock' => 100,
        ]);

        $order = Order::create([
            'code' => 'LB-TEST-12345',
            'user_id' => $buyer->id,
            'buyer' => $buyer->name,
            'phone' => '08123456789',
            'address' => 'Jl. Malioboro No. 10',
            'city' => 'Yogyakarta',
            'province' => 'D.I. Yogyakarta',
            'total' => 8000000,
            'dp' => 5200000,
            'remaining' => 3800000,
            'payment_type' => 'dp',
            'status' => 'Sedang Diproduksi',
            'shipping_cost' => 1000000,
        ]);

        $order->items()->create([
            'product_id' => $product->id,
            'name' => $product->name,
            'quantity' => 100,
            'unit_price' => 80000,
            'subtotal' => 8000000,
            'harga_modal_at_purchase' => 50000,
            'harga_jual_at_purchase' => 80000,
        ]);

        // Buyer can access their own order
        $this->actingAs($buyer)
            ->get(route('buyer.orders.show', $order))
            ->assertOk()
            ->assertSee('LB-TEST-12345')
            ->assertSee('Sedang Diproduksi')
            ->assertSee('Skema Uang Muka (DP 65%)');

        // Other buyer cannot access this order
        $otherBuyer = User::factory()->create(['role' => 'buyer']);
        $this->actingAs($otherBuyer)
            ->get(route('buyer.orders.show', $order))
            ->assertForbidden();
    }

    public function test_buyer_can_upload_settlement_proof_for_dp_order(): void
    {
        \Illuminate\Support\Facades\Storage::fake('public');

        $buyer = User::factory()->create(['role' => 'buyer']);
        $order = Order::create([
            'code' => 'LB-SETTLE-001',
            'user_id' => $buyer->id,
            'buyer' => $buyer->name,
            'phone' => '08123456789',
            'address' => 'Yogyakarta',
            'city' => 'Yogyakarta',
            'province' => 'D.I. Yogyakarta',
            'total' => 8000000,
            'dp' => 5200000,
            'remaining' => 3800000,
            'payment_type' => 'dp',
            'status' => 'Siap Dikirim',
            'shipping_cost' => 1000000,
        ]);

        $file = \Illuminate\Http\UploadedFile::fake()->image('bukti_pelunasan.jpg');

        $this->actingAs($buyer)
            ->post(route('buyer.orders.settlement', $order), [
                'payment_proof' => $file,
                'payment_method' => 'bank_transfer',
            ])
            ->assertRedirect();

        $order->refresh();
        $this->assertNotNull($order->settlement_proof);
        $this->assertSame('Menunggu Verifikasi Admin', $order->settlement_status);
    }

    public function test_admin_can_view_order_delivery_slip_and_invoice(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $product = Product::create([
            'name' => 'Kue Kering Admin Test',
            'category' => 'Tradisional',
            'description' => 'Deskripsi',
            'price_per_bal' => 85000,
            'cost_price' => 50000,
            'sales_channel' => 'both',
            'stock' => 100,
        ]);

        $order = Order::create([
            'code' => 'LB-ADM-999',
            'buyer' => 'Toko Barokah Reseller',
            'phone' => '081299998888',
            'address' => 'Jl. Solo Km 10',
            'city' => 'Sleman',
            'province' => 'D.I. Yogyakarta',
            'total' => 8500000,
            'dp' => 8500000,
            'remaining' => 0,
            'payment_type' => 'full',
            'status' => 'Sedang Dikirim',
            'courier_name' => 'Indah Logistik Kargo',
            'tracking_number' => 'IND-999888777',
            'shipping_cost' => 1000000,
        ]);

        $order->items()->create([
            'product_id' => $product->id,
            'name' => $product->name,
            'quantity' => 100,
            'unit_price' => 85000,
            'subtotal' => 8500000,
            'harga_modal_at_purchase' => 50000,
            'harga_jual_at_purchase' => 85000,
        ]);

        // Detail
        $this->actingAs($admin)
            ->get(route('admin.orders.show', $order))
            ->assertOk()
            ->assertSee('LB-ADM-999')
            ->assertSee('Toko Barokah Reseller')
            ->assertSee('Indah Logistik Kargo');

        // Surat Jalan
        $this->actingAs($admin)
            ->get(route('admin.orders.delivery-order', $order))
            ->assertOk()
            ->assertSee('SURAT JALAN')
            ->assertSee('Toko Barokah Reseller')
            ->assertSee('Diantar Langsung oleh Toko');

        // Faktur
        $this->actingAs($admin)
            ->get(route('admin.orders.invoice', $order))
            ->assertOk()
            ->assertSee('FAKTUR PENJUALAN')
            ->assertSee('Rp 9.500.000');
    }

    public function test_admin_can_view_offline_cashier_receipt(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $product = Product::create([
            'name' => 'Kue Tambang Kasir',
            'category' => 'Kue Kering Tradisional',
            'description' => 'Kue tambang.',
            'price_per_bal' => 85000,
            'cost_price' => 50000,
            'sales_channel' => 'offline',
            'stock' => 100,
        ]);

        $trx = \App\Models\OfflineTransaction::create([
            'buyer_name' => 'Bu Siti Toko Snack',
            'date' => now()->toDateString(),
            'total' => 170000,
            'total_hpp' => 100000,
            'total_laba' => 70000,
            'payment_method' => 'cash',
            'notes' => 'Tunai: Rp 200.000, Kembalian: Rp 30.000',
        ]);

        $trx->items()->create([
            'product_id' => $product->id,
            'name' => $product->name,
            'quantity' => 2,
            'unit_price' => 85000,
            'subtotal' => 170000,
            'harga_modal_at_purchase' => 50000,
            'harga_jual_at_purchase' => 85000,
        ]);

        $this->actingAs($admin)
            ->get(route('admin.transactions.receipt', $trx))
            ->assertOk()
            ->assertSee('LINTANG BAKERY')
            ->assertSee('Bu Siti Toko Snack')
            ->assertSee('Kue Tambang Kasir')
            ->assertSee('Rp 170.000')
            ->assertSee('TUNAI');
    }

    public function test_checkout_requires_payment_proof(): void
    {
        $product = Product::create([
            'name' => 'Kue Kering Bukti Bayar Test',
            'category' => 'Kue Kering Tradisional',
            'description' => 'Kue kering renyah.',
            'price_per_bal' => 85000,
            'cost_price' => 50000,
            'sales_channel' => 'online',
            'stock' => 200,
        ]);

        $buyer = User::factory()->create(['role' => 'buyer']);

        $this->actingAs($buyer)
            ->post(route('cart.store', $product), ['quantity' => 2]);

        // Attempt checkout without payment_proof
        $response = $this->post(route('checkout.store'), [
            'buyer' => 'Pembeli Tanpa Bukti',
            'phone' => '08123456789',
            'address' => 'Jalan Kaliurang KM 5',
            'province' => 'D.I. Yogyakarta',
            'city' => 'Kab. Sleman',
            'payment_type' => 'full',
            'payment_method' => 'bank_transfer',
            // no payment_proof
        ]);

        $response->assertSessionHasErrors(['payment_proof']);
    }

    public function test_checkout_page_provides_cascading_regions(): void
    {
        $product = Product::create([
            'name' => 'Kue Daerah Test',
            'category' => 'Kue Kering Tradisional',
            'description' => 'Kue daerah.',
            'price_per_bal' => 85000,
            'cost_price' => 50000,
            'sales_channel' => 'online',
            'stock' => 200,
        ]);

        $buyer = User::factory()->create(['role' => 'buyer']);

        $this->actingAs($buyer)
            ->post(route('cart.store', $product), ['quantity' => 2]);

        $response = $this->get(route('checkout.index'));
        $response->assertOk()
            ->assertSee('D.I. Yogyakarta')
            ->assertSee('Jawa Tengah')
            ->assertSee('Bukti pembayaran / transfer')
            ->assertSee('* (Wajib Diunggah)');
    }
}
