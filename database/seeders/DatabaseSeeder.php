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
            ['Nastar Nanas Premium', 'Kue Lebaran', 'Nastar lembut dengan selai nanas asli pilihan.', 85000, 'https://images.unsplash.com/photo-1558961363-fa8fdf82db35?w=600&h=500&fit=crop&auto=format'],
            ['Kastengel Keju Edam', 'Kue Keju', 'Kastengel premium dengan keju Edam asli.', 95000, 'https://images.unsplash.com/photo-1499636136210-6f4ee915583e?w=600&h=500&fit=crop&auto=format'],
            ['Putri Salju Cokelat', 'Kue Salju', 'Putri salju klasik dengan balutan gula halus dan cokelat.', 80000, 'https://images.unsplash.com/photo-1481391319762-47dff72954d9?w=600&h=500&fit=crop&auto=format'],
            ['Lidah Kucing Vanilla', 'Kue Kering Klasik', 'Kue tipis dan renyah dengan aroma vanilla.', 75000, 'https://images.unsplash.com/photo-1517427677506-ade074eb1432?w=600&h=500&fit=crop&auto=format'],
            ['Semprit Meses Cokelat', 'Kue Kering Klasik', 'Semprit renyah bertabur meses cokelat.', 78000, 'https://images.unsplash.com/photo-1550617931-e17a7b70dce2?w=600&h=500&fit=crop&auto=format'],
        ];

        foreach ($products as [$name, $category, $description, $price, $image]) {
            Product::updateOrCreate(
                ['name' => $name],
                [
                    'category' => $category,
                    'description' => $description,
                    'price_per_bal' => $price,
                    'image' => $image,
                    'sales_channel' => 'both',
                    'stock' => 100,
                ],
            );
        }
    }
}
