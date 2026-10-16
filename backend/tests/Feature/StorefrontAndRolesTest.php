<?php

namespace Tests\Feature;

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
            ->assertSee('Lintang Bakery')
            ->assertSee('id="produk"', false)
            ->assertSee('href="#produk"', false);
    }

    public function test_product_navigation_scrolls_to_the_catalog(): void
    {
        $this->get('/produk')
            ->assertRedirect(route('store.home').'#produk');
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
            'city' => 'Sleman',
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
        ]);
    }

    public function test_checkout_rejects_a_city_that_is_not_in_the_selected_province(): void
    {
        $buyer = User::factory()->create(['role' => 'buyer']);
        $product = Product::create([
            'name' => 'Nastar Demo',
            'category' => 'Kue Lebaran',
            'description' => 'Nastar dengan selai nanas.',
            'price_per_bal' => 80000,
            'sales_channel' => 'both',
            'stock' => 100,
        ]);

        $this->actingAs($buyer)
            ->withSession(['cart' => [$product->id => 1]])
            ->post(route('checkout.store'), [
                'buyer' => 'Pembeli Demo',
                'phone' => '08123456789',
                'address' => 'Jalan Mawar 1',
                'city' => 'Sleman',
                'province' => 'Jawa Barat',
                'payment_type' => 'full',
            ])
            ->assertSessionHasErrors('city');

        $this->assertDatabaseCount('orders', 0);
    }

    public function test_admin_offline_sale_reduces_product_stock(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $product = Product::create([
            'name' => 'Kastengel Demo',
            'category' => 'Kue Keju',
            'description' => 'Kastengel keju.',
            'price_per_bal' => 90000,
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
    }
}
