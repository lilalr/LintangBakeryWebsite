<?php

namespace Tests\Feature;

use App\Models\OfflineTransaction;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\StockMutation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StockManagementTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private Product $product;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'role' => 'admin',
            'name' => 'Admin Baker',
        ]);

        $this->product = Product::create([
            'name' => 'Nastar Spesial',
            'category' => 'Kue Kering',
            'description' => 'Nastar lembut nanas asli',
            'price_per_bal' => 90000,
            'cost_price' => 50000,
            'sales_channel' => 'both',
            'stock' => 50,
            'min_stock' => 15,
        ]);
    }

    public function test_process_1_inbound_stock_increases_stock_and_creates_mutation(): void
    {
        $this->actingAs($this->admin);

        $response = $this->post(route('admin.stock.inbound'), [
            'product_id' => $this->product->id,
            'quantity' => 20,
            'mutation_date' => now()->toDateString(),
            'source' => 'Dapur Produksi Utama',
            'expiry_date' => now()->addDays(30)->toDateString(),
            'notes' => 'Batch pagi oven 1',
        ]);

        $response->assertRedirect();
        $this->assertEquals(70, $this->product->fresh()->stock);

        $this->assertDatabaseHas('stock_mutations', [
            'product_id' => $this->product->id,
            'type' => 'in',
            'quantity' => 20,
            'balance' => 70,
            'reason' => 'Barang Masuk (Produksi)',
            'source' => 'Dapur Produksi Utama',
        ]);
    }

    public function test_process_2a_and_2c_pos_sale_and_void_updates_stock_and_mutations(): void
    {
        $this->actingAs($this->admin);

        // 2a: POS sale
        $response = $this->post(route('admin.transactions.store'), [
            'buyer_name' => 'Budi POS',
            'date' => now()->toDateString(),
            'product_id' => $this->product->id,
            'quantity' => 5,
            'payment_method' => 'cash',
        ]);

        $response->assertRedirect();
        $this->assertEquals(45, $this->product->fresh()->stock);

        $this->assertDatabaseHas('stock_mutations', [
            'product_id' => $this->product->id,
            'type' => 'out',
            'quantity' => 5,
            'balance' => 45,
            'reason' => 'Penjualan offline',
        ]);

        // 2c: Void POS transaction
        $transaction = OfflineTransaction::latest()->firstOrFail();
        $voidResponse = $this->delete(route('admin.transactions.destroy', $transaction));

        $voidResponse->assertRedirect();
        $this->assertEquals(50, $this->product->fresh()->stock);

        $this->assertDatabaseHas('stock_mutations', [
            'product_id' => $this->product->id,
            'type' => 'in',
            'quantity' => 5,
            'balance' => 50,
            'reason' => 'Pembatalan (Void) kasir',
        ]);
    }

    public function test_process_2b_online_order_shipped_deducts_stock_and_logs_mutation_once(): void
    {
        $this->actingAs($this->admin);

        $order = Order::create([
            'code' => 'LB-20261014-TEST',
            'buyer' => 'Siti Online',
            'phone' => '081234567890',
            'address' => 'Jl. Merdeka 45',
            'city' => 'Yogyakarta',
            'province' => 'DIY',
            'total' => 180000,
            'dp' => 180000,
            'remaining' => 0,
            'payment_type' => 'full',
            'payment_method' => 'bank_transfer',
            'status' => 'Sedang Diproduksi',
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $this->product->id,
            'name' => $this->product->name,
            'quantity' => 4,
            'unit_price' => 90000,
            'subtotal' => 360000,
        ]);

        // Change status to Sedang Dikirim
        $response = $this->patch(route('admin.orders.update', $order), [
            'status' => 'Sedang Dikirim',
        ]);

        $response->assertRedirect();
        $this->assertEquals(46, $this->product->fresh()->stock);
        $this->assertTrue($order->fresh()->stock_deducted);

        $this->assertDatabaseHas('stock_mutations', [
            'product_id' => $this->product->id,
            'type' => 'out',
            'quantity' => 4,
            'balance' => 46,
            'reason' => 'Penjualan online',
            'reference_number' => 'LB-20261014-TEST',
        ]);

        // Subsequent update to Selesai/Lunas must NOT deduct again
        $this->patch(route('admin.orders.update', $order), [
            'status' => 'Selesai/Lunas',
        ]);
        $this->assertEquals(46, $this->product->fresh()->stock);
    }

    public function test_process_3_manual_outbound_calculates_loss_for_damage(): void
    {
        $this->actingAs($this->admin);

        $response = $this->post(route('admin.stock.outbound'), [
            'product_id' => $this->product->id,
            'quantity' => 3,
            'reason' => 'Rusak',
            'mutation_date' => now()->toDateString(),
            'notes' => 'Toples retak saat disusun di rak',
        ]);

        $response->assertRedirect();
        $this->assertEquals(47, $this->product->fresh()->stock);

        // Kerugian: 3 * 50.000 = 150.000
        $this->assertDatabaseHas('stock_mutations', [
            'product_id' => $this->product->id,
            'type' => 'out',
            'quantity' => 3,
            'balance' => 47,
            'reason' => 'Barang Keluar (Rusak)',
            'loss_amount' => 150000.00,
        ]);
    }

    public function test_process_4_and_5_stock_page_and_dashboard_alerts(): void
    {
        $this->actingAs($this->admin);

        // Set stock below minimum (min_stock is 15, set stock to 10)
        $this->product->update(['stock' => 10]);

        // Create expiring inbound mutation
        StockMutation::create([
            'product_id' => $this->product->id,
            'type' => 'in',
            'quantity' => 10,
            'balance' => 10,
            'reason' => 'Barang Masuk (Produksi)',
            'expiry_date' => now()->addDays(5)->toDateString(),
            'mutation_date' => now()->toDateString(),
        ]);

        $stockPage = $this->get(route('admin.stock.index'));
        $stockPage->assertOk()
            ->assertSee('Nastar Spesial')
            ->assertSee('Menipis')
            ->assertSee('Peringatan Stok Menipis')
            ->assertSee('Peringatan Kedaluwarsa');

        $dashboard = $this->get(route('admin.dashboard'));
        $dashboard->assertOk()
            ->assertSee('Peringatan Stok Menipis')
            ->assertSee('Nastar Spesial');
    }
}
