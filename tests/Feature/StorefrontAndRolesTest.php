<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
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

        $this->post(route('checkout.store'), [
            'buyer' => 'Pembeli Demo',
            'phone' => '08123456789',
            'address' => 'Jalan Mawar 1',
            'city' => 'Yogyakarta',
            'province' => 'DI Yogyakarta',
            'payment_type' => 'dp',
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
            'city' => 'Yogyakarta',
            'province' => 'DI Yogyakarta',
            'payment_type' => 'dp',
            'payment_method' => 'bank_transfer',
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
}
