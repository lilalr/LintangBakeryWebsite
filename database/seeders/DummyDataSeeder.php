<?php

namespace Database\Seeders;

use App\Models\OfflineTransaction;
use App\Models\OfflineTransactionItem;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\StockBatch;
use App\Models\StockMutation;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DummyDataSeeder extends Seeder
{
    public function run(): void
    {
        // 1. DUMMY BUYERS (PELANGGAN)
        $customersData = [
            [
                'name' => 'Sutopo Hadi',
                'email' => 'sutopo@gmail.com',
                'phone' => '08122334455',
                'address' => 'Jl. Kaliurang KM 9, Sleman',
                'city' => 'Sleman',
                'province' => 'DI Yogyakarta',
            ],
            [
                'name' => 'Dewi Anggraini',
                'email' => 'dewi.ang@gmail.com',
                'phone' => '081398765432',
                'address' => 'Jl. Gejayan No. 45, Condongcatur',
                'city' => 'Sleman',
                'province' => 'DI Yogyakarta',
            ],
            [
                'name' => 'Budi Santoso',
                'email' => 'budi.santoso@yahoo.com',
                'phone' => '081567890123',
                'address' => 'Jl. Solo KM 10, Kalasan',
                'city' => 'Sleman',
                'province' => 'DI Yogyakarta',
            ],
            [
                'name' => 'Sri Wahyuni',
                'email' => 'sri.wahyuni@gmail.com',
                'phone' => '081745612389',
                'address' => 'Jl. Bantul KM 5, Sewon',
                'city' => 'Bantul',
                'province' => 'DI Yogyakarta',
            ],
            [
                'name' => 'Hendra Pratama',
                'email' => 'hendra.pratama@outlook.com',
                'phone' => '082134567812',
                'address' => 'Jl. Malioboro No. 88, Sosromenduran',
                'city' => 'Kota Yogyakarta',
                'province' => 'DI Yogyakarta',
            ],
            [
                'name' => 'Rina Marlina',
                'email' => 'rina.marlina@gmail.com',
                'phone' => '085612347890',
                'address' => 'Jl. Magelang KM 6, Mlati',
                'city' => 'Sleman',
                'province' => 'DI Yogyakarta',
            ],
            [
                'name' => 'Toko Berkah Snack (H. Anshori)',
                'email' => 'berkahsnack@gmail.com',
                'phone' => '081299887766',
                'address' => 'Pasar Beringharjo Lt. 1 No. 24',
                'city' => 'Kota Yogyakarta',
                'province' => 'DI Yogyakarta',
            ],
            [
                'name' => 'Grosir Jaya Abadi (Ibu Ratna)',
                'email' => 'jayagrosir@gmail.com',
                'phone' => '081322114455',
                'address' => 'Jl. Kusumanegara No. 120, Umbulharjo',
                'city' => 'Kota Yogyakarta',
                'province' => 'DI Yogyakarta',
            ],
        ];

        $users = [];
        foreach ($customersData as $c) {
            $users[$c['name']] = User::updateOrCreate(
                ['email' => $c['email']],
                [
                    'name' => $c['name'],
                    'role' => 'buyer',
                    'phone' => $c['phone'],
                    'address' => $c['address'],
                    'password' => Hash::make('password'),
                ]
            );
        }

        $admin = User::where('role', 'admin')->first() ?: User::first();

        // 2. PRODUK
        $products = Product::all()->keyBy('name');
        if ($products->isEmpty()) {
            $this->call(DatabaseSeeder::class);
            $products = Product::all()->keyBy('name');
        }

        $pTambangBesar = $products->get('Tambang Besar') ?: $products->get('Kue Tambang') ?: $products->first();
        $pCiput = $products->get('Ciput') ?: $products->get('Kue Keciput') ?: $products->skip(1)->first();
        $pTambangSpesial = $products->get('Tambang Spesial') ?: $products->last();

        $pTambang = $pTambangBesar;
        $pKeciput = $pCiput;
        $pStikUbi = $pTambangSpesial;
        $pStikAsin = $pTambangBesar;
        $pSeblak = $pCiput;

        // 3. PESANAN ONLINE (ORDERS)
        $ordersData = [
            [
                'code' => 'ONL-0002',
                'user' => $users['Dewi Anggraini'],
                'date' => '2026-11-03 10:15:00',
                'status' => 'Selesai/Lunas',
                'payment_method' => 'bank_transfer',
                'payment_type' => 'full',
                'shipping_cost' => 150000,
                'items' => [
                    ['product' => $pTambang, 'qty' => 50],
                    ['product' => $pKeciput, 'qty' => 50],
                ],
            ],
            [
                'code' => 'ONL-0003',
                'user' => $users['Sutopo Hadi'],
                'date' => '2026-11-06 14:20:00',
                'status' => 'Selesai/Lunas',
                'payment_method' => 'qris',
                'payment_type' => 'full',
                'shipping_cost' => 100000,
                'items' => [
                    ['product' => $pSeblak, 'qty' => 60],
                    ['product' => $pStikAsin, 'qty' => 50],
                ],
            ],
            [
                'code' => 'ONL-0004',
                'user' => $users['Toko Berkah Snack (H. Anshori)'],
                'date' => '2026-11-09 09:30:00',
                'status' => 'Selesai/Lunas',
                'payment_method' => 'bank_transfer',
                'payment_type' => 'dp',
                'shipping_cost' => 200000,
                'items' => [
                    ['product' => $pTambang, 'qty' => 100],
                    ['product' => $pStikUbi, 'qty' => 100],
                ],
            ],
            [
                'code' => 'ONL-0005',
                'user' => $users['Grosir Jaya Abadi (Ibu Ratna)'],
                'date' => '2026-11-12 11:45:00',
                'status' => 'Sedang Dikirim',
                'payment_method' => 'bank_transfer',
                'payment_type' => 'full',
                'shipping_cost' => 150000,
                'courier_name' => 'J&T Cargo',
                'tracking_number' => 'JT99823145612',
                'items' => [
                    ['product' => $pKeciput, 'qty' => 50],
                    ['product' => $pStikAsin, 'qty' => 50],
                ],
            ],
            [
                'code' => 'ONL-0006',
                'user' => $users['Budi Santoso'],
                'date' => '2026-11-14 16:10:00',
                'status' => 'Sedang Diproduksi',
                'payment_method' => 'qris',
                'payment_type' => 'dp',
                'shipping_cost' => 120000,
                'items' => [
                    ['product' => $pSeblak, 'qty' => 50],
                    ['product' => $pTambang, 'qty' => 50],
                ],
            ],
            [
                'code' => 'ONL-0007',
                'user' => $users['Rina Marlina'],
                'date' => '2026-11-16 13:00:00',
                'status' => 'Siap Dikirim',
                'payment_method' => 'bank_transfer',
                'payment_type' => 'full',
                'shipping_cost' => 100000,
                'items' => [
                    ['product' => $pStikUbi, 'qty' => 100],
                ],
            ],
            [
                'code' => 'ONL-0008',
                'user' => $users['Sri Wahyuni'],
                'date' => '2026-11-17 10:00:00',
                'status' => 'Menunggu Konfirmasi',
                'payment_method' => 'bank_transfer',
                'payment_type' => 'full',
                'shipping_cost' => 100000,
                'items' => [
                    ['product' => $pTambang, 'qty' => 100],
                ],
            ],
        ];

        foreach ($ordersData as $o) {
            $totalItems = 0;
            foreach ($o['items'] as $item) {
                $totalItems += ($item['product']->price_per_bal * $item['qty']);
            }

            $dp = $o['payment_type'] === 'dp' ? (int) ($totalItems / 2) : 0;
            $remaining = $o['payment_type'] === 'dp' ? ($totalItems - $dp) : 0;

            $order = Order::updateOrCreate(
                ['code' => $o['code']],
                [
                    'user_id' => $o['user']->id,
                    'buyer' => $o['user']->name,
                    'phone' => $o['user']->phone,
                    'address' => $o['user']->address,
                    'city' => 'Sleman',
                    'province' => 'DI Yogyakarta',
                    'total' => $totalItems,
                    'dp' => $dp,
                    'remaining' => $remaining,
                    'payment_type' => $o['payment_type'],
                    'payment_method' => $o['payment_method'],
                    'status' => $o['status'],
                    'shipping_cost' => $o['shipping_cost'],
                    'courier_name' => $o['courier_name'] ?? null,
                    'tracking_number' => $o['tracking_number'] ?? null,
                    'stock_deducted' => true,
                    'created_at' => $o['date'],
                    'updated_at' => $o['date'],
                ]
            );

            // Re-create items cleanly
            $order->items()->delete();
            foreach ($o['items'] as $item) {
                $p = $item['product'];
                $subtotal = $p->price_per_bal * $item['qty'];
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $p->id,
                    'name' => $p->name,
                    'quantity' => $item['qty'],
                    'unit_price' => $p->price_per_bal,
                    'subtotal' => $subtotal,
                    'harga_modal_at_purchase' => $p->cost_price ?: ($p->price_per_bal * 0.65),
                    'harga_jual_at_purchase' => $p->price_per_bal,
                    'created_at' => $o['date'],
                    'updated_at' => $o['date'],
                ]);
            }
        }

        // 4. TRANSAKSI OFFLINE (KASIR TOKO)
        $offlineData = [
            [
                'date' => '2026-11-01',
                'buyer' => '-',
                'payment' => 'cash',
                'notes' => 'Pembelian toples toko kelontong',
                'items' => [
                    ['product' => $pTambang, 'qty' => 3],
                ],
            ],
            [
                'date' => '2026-11-02',
                'buyer' => '-',
                'payment' => 'qris',
                'notes' => 'Langganan warung kopi',
                'items' => [
                    ['product' => $pKeciput, 'qty' => 2],
                    ['product' => $pSeblak, 'qty' => 2],
                ],
            ],
            [
                'date' => '2026-11-04',
                'buyer' => '-',
                'payment' => 'cash',
                'notes' => 'Cemilan hajatan arisan',
                'items' => [
                    ['product' => $pStikUbi, 'qty' => 5],
                ],
            ],
            [
                'date' => '2026-11-05',
                'buyer' => '-',
                'payment' => 'qris',
                'notes' => 'Restock warung Sleman',
                'items' => [
                    ['product' => $pStikAsin, 'qty' => 4],
                    ['product' => $pTambang, 'qty' => 2],
                ],
            ],
            [
                'date' => '2026-11-07',
                'buyer' => '-',
                'payment' => 'cash',
                'notes' => 'Grosir eceran pasar',
                'items' => [
                    ['product' => $pSeblak, 'qty' => 10],
                ],
            ],
            [
                'date' => '2026-11-08',
                'buyer' => '-',
                'payment' => 'qris',
                'notes' => 'Take away langsung',
                'items' => [
                    ['product' => $pKeciput, 'qty' => 3],
                ],
            ],
            [
                'date' => '2026-11-10',
                'buyer' => '-',
                'payment' => 'cash',
                'notes' => 'Paket cemilan keluarga',
                'items' => [
                    ['product' => $pStikAsin, 'qty' => 3],
                ],
            ],
            [
                'date' => '2026-11-11',
                'buyer' => '-',
                'payment' => 'cash',
                'notes' => 'Repeat order mingguan',
                'items' => [
                    ['product' => $pTambang, 'qty' => 5],
                ],
            ],
            [
                'date' => '2026-11-13',
                'buyer' => '-',
                'payment' => 'qris',
                'notes' => 'Cemilan toples kantin',
                'items' => [
                    ['product' => $pStikUbi, 'qty' => 6],
                    ['product' => $pSeblak, 'qty' => 4],
                ],
            ],
            [
                'date' => '2026-11-15',
                'buyer' => '-',
                'payment' => 'cash',
                'notes' => 'Pesanan kue toples',
                'items' => [
                    ['product' => $pKeciput, 'qty' => 4],
                ],
            ],
            [
                'date' => '2026-11-16',
                'buyer' => '-',
                'payment' => 'qris',
                'notes' => 'Pesanan tambahan kerupuk',
                'items' => [
                    ['product' => $pSeblak, 'qty' => 5],
                ],
            ],
            [
                'date' => '2026-11-17',
                'buyer' => '-',
                'payment' => 'cash',
                'notes' => 'Cemilan santai sore',
                'items' => [
                    ['product' => $pStikAsin, 'qty' => 2],
                    ['product' => $pTambang, 'qty' => 2],
                ],
            ],
            [
                'date' => '2026-11-18',
                'buyer' => '-',
                'payment' => 'qris',
                'notes' => 'Bawa ke kantor',
                'items' => [
                    ['product' => $pKeciput, 'qty' => 3],
                    ['product' => $pStikUbi, 'qty' => 2],
                ],
            ],
            [
                'date' => '2026-11-18',
                'buyer' => '-',
                'payment' => 'cash',
                'notes' => 'Restock harian warung',
                'items' => [
                    ['product' => $pStikAsin, 'qty' => 3],
                ],
            ],
        ];

        // Clean existing offline transactions to avoid duplicate double-seeding
        OfflineTransaction::query()->delete();

        foreach ($offlineData as $t) {
            $totalSales = 0;
            $totalHpp = 0;

            foreach ($t['items'] as $item) {
                $p = $item['product'];
                $subtotal = $p->price_per_bal * $item['qty'];
                $hpp = ($p->cost_price ?: ($p->price_per_bal * 0.65)) * $item['qty'];
                $totalSales += $subtotal;
                $totalHpp += $hpp;
            }

            $transaction = OfflineTransaction::create([
                'date' => $t['date'],
                'buyer_name' => $t['buyer'],
                'total' => $totalSales,
                'total_hpp' => $totalHpp,
                'total_laba' => $totalSales - $totalHpp,
                'payment_method' => $t['payment'],
                'notes' => $t['notes'],
                'created_at' => Carbon::parse($t['date'].' 11:30:00'),
                'updated_at' => Carbon::parse($t['date'].' 11:30:00'),
            ]);

            foreach ($t['items'] as $item) {
                $p = $item['product'];
                $subtotal = $p->price_per_bal * $item['qty'];
                $hpp = $p->cost_price ?: ($p->price_per_bal * 0.65);

                OfflineTransactionItem::create([
                    'offline_transaction_id' => $transaction->id,
                    'product_id' => $p->id,
                    'name' => $p->name,
                    'quantity' => $item['qty'],
                    'unit_price' => $p->price_per_bal,
                    'subtotal' => $subtotal,
                    'harga_modal_at_purchase' => $hpp,
                    'harga_jual_at_purchase' => $p->price_per_bal,
                    'created_at' => Carbon::parse($t['date'].' 11:30:00'),
                    'updated_at' => Carbon::parse($t['date'].' 11:30:00'),
                ]);
            }
        }

        // 5. MUTASI STOK (MANAJEMEN STOK)
        StockMutation::query()->delete();
        StockBatch::query()->delete();

        $mutations = [
            [
                'product' => $pTambang,
                'type' => 'in',
                'qty' => 150,
                'date' => '2026-11-01',
                'source' => 'Dapur Produksi Utama',
                'reason' => 'Barang Masuk (Produksi)',
                'expiry' => '2026-12-25',
                'loss' => 0,
                'notes' => 'Batch produksi reguler oven 1-3',
            ],
            [
                'product' => $pKeciput,
                'type' => 'in',
                'qty' => 120,
                'date' => '2026-11-02',
                'source' => 'Dapur Produksi Utama',
                'reason' => 'Barang Masuk (Produksi)',
                'expiry' => '2026-11-28', // < 14 hari dari hari ini -> memicu alert kedaluwarsa!
                'loss' => 0,
                'notes' => 'Batch goreng wijen super',
            ],
            [
                'product' => $pStikUbi,
                'type' => 'in',
                'qty' => 100,
                'date' => '2026-11-04',
                'source' => 'Dapur Produksi Utama',
                'reason' => 'Barang Masuk (Produksi)',
                'expiry' => '2027-01-15',
                'loss' => 0,
                'notes' => 'Olahan ubi ungu segar Kulon Progo',
            ],
            [
                'product' => $pStikAsin,
                'type' => 'in',
                'qty' => 150,
                'date' => '2026-11-08',
                'source' => 'Dapur Produksi Utama',
                'reason' => 'Barang Masuk (Produksi)',
                'expiry' => '2027-01-20',
                'loss' => 0,
                'notes' => 'Batch gurih renyah bawang',
            ],
            [
                'product' => $pSeblak,
                'type' => 'in',
                'qty' => 180,
                'date' => '2026-11-10',
                'source' => 'Dapur Produksi Utama',
                'reason' => 'Barang Masuk (Produksi)',
                'expiry' => '2027-02-01',
                'loss' => 0,
                'notes' => 'Bumbu kencur pedas daun jeruk',
            ],
            [
                'product' => $pStikUbi,
                'type' => 'out',
                'qty' => 2,
                'date' => '2026-11-12',
                'source' => null,
                'reason' => 'Barang Rusak / Kemasan Sobek',
                'expiry' => null,
                'loss' => 100000,
                'notes' => 'Kemasan luar sobek saat pemindahan rak gudang',
            ],
            [
                'product' => $pKeciput,
                'type' => 'out',
                'qty' => 1,
                'date' => '2026-11-14',
                'source' => null,
                'reason' => 'Tester Promosi Reseller',
                'expiry' => null,
                'loss' => 60000,
                'notes' => 'Sampel tester untuk kunjungan calon reseller Klaten',
            ],
        ];

        foreach ($mutations as $m) {
            $p = $m['product'];
            StockMutation::create([
                'product_id' => $p->id,
                'user_id' => $admin?->id,
                'admin_name' => $admin?->name ?? 'Admin Lintang Bakery',
                'type' => $m['type'],
                'quantity' => $m['qty'],
                'balance' => $p->stock,
                'reason' => $m['reason'],
                'source' => $m['source'],
                'expiry_date' => $m['expiry'],
                'reference_number' => ($m['type'] === 'in' ? 'IN-' : 'OUT-').str_replace('-', '', $m['date']).'-'.strtoupper(Str::random(4)),
                'loss_amount' => $m['loss'],
                'notes' => $m['notes'],
                'mutation_date' => $m['date'],
                'created_at' => Carbon::parse($m['date'].' 08:30:00'),
                'updated_at' => Carbon::parse($m['date'].' 08:30:00'),
            ]);

            if ($m['type'] === 'in' && ! empty($m['expiry'])) {
                StockBatch::create([
                    'product_id' => $p->id,
                    'quantity' => $m['qty'],
                    'received_date' => $m['date'],
                    'expiry_date' => $m['expiry'],
                ]);
            }
        }

        // Set realistic current stocks
        $pTambangBesar?->update(['stock' => 185, 'min_stock' => 50]);
        $pCiput?->update(['stock' => 142, 'min_stock' => 50]);
        $pTambangSpesial?->update(['stock' => 45, 'min_stock' => 50]); // Sedikit di bawah min_stock untuk trigger alert stok menipis!
    }
}
