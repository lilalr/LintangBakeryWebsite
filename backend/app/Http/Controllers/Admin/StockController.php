<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StockController extends Controller
{
    public function index(): View
    {
        $products = Product::whereIn('sales_channel', ['offline', 'both'])->orderBy('name')->get();

        return view('admin.stock.index', compact('products'));
    }

    public function update(Request $request, Product $product): RedirectResponse
    {
        abort_unless(in_array($product->sales_channel, ['offline', 'both'], true), 404);
        $validated = $request->validate([
            'adjustment' => ['required', 'integer', 'min:1', 'max:100000'],
        ]);
        $product->increment('stock', $validated['adjustment']);

        return back()->with('success', 'Stok '.$product->name.' berhasil ditambahkan.');
    }
}
