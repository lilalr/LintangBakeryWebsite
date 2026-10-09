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

    public function test_accounting_report_shows_profit_and_can_be_exported_to_excel_compatible_csv(): void
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
        $this->get(route('admin.reports.index', ['period' => now()->format('Y-m')]))
            ->assertOk()
            ->assertSee('Rp 180.000,00')
            ->assertSee('Rp 280.000,00')
            ->assertSee('Rp 90.000,00')
            ->assertSee('Rp 190.000,00')
            ->assertSee('Rp 58.500,00')
            ->assertSee('Nastar Akuntansi');

        $export = $this->get(route('admin.reports.export', ['period' => now()->format('Y-m')]))
            ->assertOk()
            ->assertHeader('content-type', 'text/csv; charset=UTF-8');
        $this->assertStringContainsString('Nastar Akuntansi', $export->streamedContent());
    }
}
