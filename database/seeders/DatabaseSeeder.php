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
                'name' => 'Tambang Besar',
                'category' => 'Kue Kering Tradisional',
                'description' => 'Kue tambang besar tradisional khas Lintang Bakery bertekstur renyah, gurih, dan manis pas. Dibuat dari adonan pilihan yang dipilin rapi dan digoreng higienis dengan minyak berkualitas. Kemasan bal (3 kg/bal), pilihan utama untuk reseller, toko oleh-oleh, dan repackaging.',
                'price' => 75000,
                'cost_price' => 50000,
                'image' => '/images/products/tambang-besar.jpg',
            ],
            [
                'name' => 'Ciput',
                'category' => 'Kue Kering Tradisional',
                'description' => 'Kue ciput (keciput) renyah bertabur biji wijen pilihan dengan rasa gurih manis yang seimbang khas Lintang Bakery. Tekstur luar garing dan kopong renyah di dalam, tahan lama tanpa bahan pengawet. Kemasan bal (2 kg/bal) cocok untuk reseller dan kemasan repack.',
                'price' => 100000,
                'cost_price' => 70000,
                'image' => '/images/products/kue-ciput.jpg',
            ],
            [
                'name' => 'Tambang Spesial',
                'category' => 'Kue Kering Tradisional',
                'description' => 'Kue tambang spesial kualitas premium dari Lintang Bakery dengan adonan renyah empuk istimewa dan cita rasa gurih yang khas. Kemasan bal (3 kg/bal) bertanda kemasan Aneka Kue Lintang asli untuk agen dan reseller.',
                'price' => 95000,
                'cost_price' => 65000,
                'image' => '/images/products/tambang-spesial.jpg',
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

        $this->call(DummyDataSeeder::class);
    }
}
