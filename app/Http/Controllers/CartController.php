<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CartController extends Controller
{
    public function index(Request $request): View
    {
        $cart = $this->cartProducts($request);

        return view('store.cart', compact('cart'));
    }

    public function store(Request $request, Product $product): RedirectResponse
    {
        abort_unless(in_array($product->sales_channel, ['online', 'both'], true), 404);
        $validated = $request->validate(['quantity' => ['required', 'integer', 'min:1', 'max:10000']]);
        $cart = $request->session()->get('cart', []);
        $cart[$product->id] = ($cart[$product->id] ?? 0) + $validated['quantity'];
        $request->session()->put('cart', $cart);

        return back(fallback: route('cart.index'))
            ->with('open_cart', true)
            ->with('success', $product->name.' berhasil ditambahkan ke keranjang.');
    }

    public function update(Request $request, Product $product): RedirectResponse
    {
        $validated = $request->validate(['quantity' => ['required', 'integer', 'min:1', 'max:10000']]);
        $cart = $request->session()->get('cart', []);
        $cart[$product->id] = $validated['quantity'];
        $request->session()->put('cart', $cart);

        return back(fallback: route('cart.index'))
            ->with('open_cart', true)
            ->with('success', 'Jumlah produk diperbarui.');
    }

    public function destroy(Request $request, Product $product): RedirectResponse
    {
        $cart = $request->session()->get('cart', []);
        unset($cart[$product->id]);
        $request->session()->put('cart', $cart);

        return back(fallback: route('cart.index'))
            ->with('open_cart', true)
            ->with('success', 'Produk dihapus dari keranjang.');
    }

    private function cartProducts(Request $request): array
    {
        $quantities = $request->session()->get('cart', []);
        $products = Product::query()
            ->whereIn('id', array_keys($quantities))
            ->whereIn('sales_channel', ['online', 'both'])
            ->get()
            ->keyBy('id');

        return collect($quantities)->map(function (int $quantity, int|string $id) use ($products) {
            $product = $products->get($id);

            return $product ? ['product' => $product, 'quantity' => $quantity, 'subtotal' => $product->price_per_bal * $quantity] : null;
        })->filter()->values()->all();
    }
}
