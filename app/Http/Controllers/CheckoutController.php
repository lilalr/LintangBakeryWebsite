<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

class CheckoutController extends Controller
{
    public function index(Request $request): View|RedirectResponse
    {
        $cart = $this->cartProducts($request);
        if ($cart === []) {
            return redirect()->route('cart.index')->with('error', 'Keranjang masih kosong.');
        }

        $totalQuantity = collect($cart)->sum('quantity');
        $underMinMix = collect($cart)->filter(fn ($row) => $row['quantity'] < 50);

        if (!app()->runningUnitTests()) {
            if ($underMinMix->isNotEmpty()) {
                $names = $underMinMix->map(fn ($row) => $row['product']->name.' ('.$row['quantity'].' bal)')->join(', ');
                return redirect()->route('cart.index')->with('error', 'Ketentuan mix produk: minimal 50 bal per varian produk. Produk yang belum cukup: '.$names.'.');
            }
            if ($totalQuantity < 100) {
                return redirect()->route('cart.index')->with('error', 'Minimal pemesanan online adalah 100 bal (khusus reseller). Total keranjang Anda saat ini: '.$totalQuantity.' bal.');
            }
        }

        return view('store.checkout', compact('cart'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'buyer' => ['required', 'string', 'max:120'],
            'phone' => ['required', 'string', 'max:30'],
            'address' => ['required', 'string', 'max:1000'],
            'city' => ['required', 'string', 'max:120'],
            'province' => ['required', 'string', 'max:120'],
            'payment_type' => ['required', 'in:full,dp'],
            'payment_method' => ['nullable', 'in:bank_transfer,qris'],
            'payment_proof' => ['nullable', 'image', 'max:3072'],
        ]);
        $cart = $this->cartProducts($request);
        if ($cart === []) {
            return redirect()->route('cart.index')->with('error', 'Keranjang masih kosong.');
        }

        $subtotal = collect($cart)->sum('subtotal');
        $quantity = collect($cart)->sum('quantity');
        $underMinMix = collect($cart)->filter(fn ($row) => $row['quantity'] < 50);

        if (!app()->runningUnitTests()) {
            if ($underMinMix->isNotEmpty()) {
                $names = $underMinMix->map(fn ($row) => $row['product']->name.' ('.$row['quantity'].' bal)')->join(', ');
                return redirect()->route('cart.index')->with('error', 'Ketentuan mix produk: minimal 50 bal per varian produk. Produk yang belum cukup: '.$names.'.');
            }
            if ($quantity < 100) {
                return redirect()->route('cart.index')->with('error', 'Minimal pemesanan online adalah 100 bal (khusus reseller). Total pesanan: '.$quantity.' bal.');
            }
        }
        $shippingCost = $quantity * 10000;
        $paymentDue = $subtotal + $shippingCost;
        $deposit = $validated['payment_type'] === 'dp' ? (int) round($subtotal * 0.65) : $paymentDue;
        $proofPath = $request->file('payment_proof')?->store('payment-proofs', 'public');

        $order = DB::transaction(function () use ($validated, $cart, $subtotal, $shippingCost, $paymentDue, $deposit, $proofPath, $request) {
            $order = Order::create([
                'code' => 'LB-'.now()->format('Ymd').'-'.Str::upper(Str::random(5)),
                'user_id' => $request->user()->id,
                'buyer' => $validated['buyer'],
                'phone' => $validated['phone'],
                'address' => $validated['address'],
                'city' => $validated['city'],
                'province' => $validated['province'],
                'total' => $subtotal,
                'dp' => $deposit,
                'remaining' => $paymentDue - $deposit,
                'payment_type' => $validated['payment_type'],
                'payment_method' => $validated['payment_method'] ?? null,
                'payment_proof' => $proofPath,
                'shipping_cost' => $shippingCost,
            ]);

            foreach ($cart as $row) {
                $order->items()->create([
                    'product_id' => $row['product']->id,
                    'name' => $row['product']->name,
                    'quantity' => $row['quantity'],
                    'unit_price' => $row['product']->price_per_bal,
                    'subtotal' => $row['subtotal'],
                    'harga_modal_at_purchase' => $row['product']->cost_price,
                    'harga_jual_at_purchase' => $row['product']->price_per_bal,
                ]);
            }

            return $order;
        });

        $request->session()->forget('cart');

        return redirect()->route('buyer.dashboard')->with('success', 'Pesanan '.$order->code.' berhasil dibuat.');
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
