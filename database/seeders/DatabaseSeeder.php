<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@lintangbakery.id'],
            [
                'name' => 'Admin Lintang Bakery',
                'role' => 'admin',
                'phone' => '081234567890',
                'password' => Hash::make('admin123'),
            ],
        );

        User::updateOrCreate(
            ['email' => 'buyer@test.com'],
            [
                'name' => 'Demo Reseller',
                'role' => 'buyer',
                'phone' => '081234567891',
                'address' => 'Yogyakarta',
                'password' => Hash::make('password'),
            ],
        );

        $products = [
            [
                'name' => 'Kue Tambang',
                'category' => 'Kue Kering Tradisional',
                'description' => 'Kue tambang (untir-untir) tradisional yang renyah, gurih, dan sedikit manis. Dibuat dari adonan pilihan yang dipilin rapi dan digoreng matang sempurna dengan minyak berkualitas. Sangat cocok untuk toples reseller dan kemasan repack.',
                'price' => 85000,
                'cost_price' => 55000,
                'image' => '/images/products/kueTambang.jpg',
            ],
            [
                'name' => 'Kue Keciput',
                'category' => 'Kue Kering Tradisional',
                'description' => 'Kue keciput renyah bertabur biji wijen pilihan dengan rasa gurih manis yang seimbang. Tekstur luar garing dan kopong renyah di dalam tahan lama tanpa bahan pengawet.',
                'price' => 90000,
                'cost_price' => 60000,
                'image' => '/images/products/kueKeciput.jpg',
            ],
            [
                'name' => 'Stik Ubi Ungu',
                'category' => 'Camilan & Keripik',
                'description' => 'Stik renyah berbahan olahan ubi ungu asli berkualitas dengan warna ungu alami tanpa pewarna sintetis. Rasa manis gurih yang unik dan renyah disukai semua kalangan.',
                'price' => 80000,
                'cost_price' => 50000,
                'image' => '/images/products/stikUbi.jpg',
            ],
            [
                'name' => 'Stik Asin',
                'category' => 'Camilan & Keripik',
                'description' => 'Stik gurih asin super renyah dengan aroma bumbu bawang dan rempah pilihan yang harum dan gurih nagih. Tekstur renyah garing sempurna untuk cemilan toples dan reseller.',
                'price' => 75000,
                'cost_price' => 45000,
                'image' => '/images/products/stikAsin.jpg',
            ],
            [
                'name' => 'Kerupuk Seblak Kering',
                'category' => 'Camilan Pedas',
                'description' => 'Kerupuk seblak kering pedas gurih beraroma kencur dan taburan daun jeruk yang otentik. Sensasi renyah bantat khas seblak kering dengan tingkat kepedasan nagih yang jadi best-seller reseller.',
                'price' => 85000,
                'cost_price' => 55000,
                'image' => '/images/products/kerupukSeblak.jpg',
            ],
        ];

        foreach ($products as $i => $data) {
            Product::updateOrCreate(
                ['id' => $i + 1],
                [
                    'name' => $data['name'],
                    'category' => $data['category'],
                    'description' => $data['description'],
                    'price_per_bal' => $data['price'],
                    'cost_price' => $data['cost_price'],
                    'image' => $data['image'],
                    'sales_channel' => 'both',
                    'stock' => 500,
                    'min_stock' => 50,
                ],
            );
        }
    }
}
