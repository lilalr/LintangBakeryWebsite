<?php

namespace App\Providers;

use App\Models\Product;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        View::composer('layouts.store', function ($view) {
            $rawCart = (array) session('cart', []);
            $cartDrawerItems = [];
            $cartDrawerTotal = 0;

            if (! empty($rawCart)) {
                $products = Product::query()
                    ->whereIn('id', array_keys($rawCart))
                    ->whereIn('sales_channel', ['online', 'both'])
                    ->get()
                    ->keyBy('id');

                foreach ($rawCart as $productId => $quantity) {
                    $product = $products->get($productId);
                    if ($product) {
                        $subtotal = $product->price_per_bal * $quantity;
                        $cartDrawerItems[] = [
                            'product' => $product,
                            'quantity' => $quantity,
                            'subtotal' => $subtotal,
                        ];
                        $cartDrawerTotal += $subtotal;
                    }
                }
            }

            $view->with([
                'cartDrawerItems' => $cartDrawerItems,
                'cartDrawerTotal' => $cartDrawerTotal,
                'cartDrawerCount' => array_sum($rawCart),
            ]);
        });
    }
}
