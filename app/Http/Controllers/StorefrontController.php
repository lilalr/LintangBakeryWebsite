<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\View\View;

class StorefrontController extends Controller
{
    public function index(): View
    {
        $products = Product::query()
            ->whereIn('sales_channel', ['online', 'both'])
            ->latest()
            ->get();

        return view('store.home', compact('products'));
    }

    public function catalog(): View
    {
        $products = Product::query()
            ->whereIn('sales_channel', ['online', 'both'])
            ->latest()
            ->get();

        return view('store.catalog', compact('products'));
    }

    public function show(Product $product): View
    {
        abort_unless(in_array($product->sales_channel, ['online', 'both'], true), 404);

        $relatedProducts = Product::query()
            ->whereIn('sales_channel', ['online', 'both'])
            ->where('id', '!=', $product->id)
            ->latest()
            ->take(3)
            ->get();

        return view('store.product', compact('product', 'relatedProducts'));
    }
}
