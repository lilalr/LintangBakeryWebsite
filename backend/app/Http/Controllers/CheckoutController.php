<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CheckoutController extends Controller
{
    public function index(Request $request): View
    {
        $user = auth()->user(); // Ambil data user yang sedang login
        $cart = session()->get('cart', []);

        return view('store.checkout', compact('user', 'cart'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'buyer' => ['required', 'string', 'max:120'],
            'phone' => ['required', 'string', 'max:30'],
            'address' => ['required', 'string', 'max:1000'],
            'province' => ['required', 'string'],
            'city' => ['required', 'string'],
            'payment_type' => ['required', 'in:full,dp'],
            'payment_proof' => ['nullable', 'image', 'max:3072'],
        ]);

        $cart = $this->cartProducts($request);
        if ($cart === []) {
            return redirect()->route('cart.index')->with('error', 'Keranjang masih kosong.');
        }

        $subtotal = collect($cart)->sum('subtotal');
        $quantity = collect($cart)->sum('quantity');
        
        // DIPERBAIKI: Operator perkalian (*) diperbaiki dari baris sebelumnya
        $shippingCost = $quantity * 10000;
        $paymentDue = $subtotal + $shippingCost;
        
        // DIPERBAIKI: Operator perkalian (*) dan pengurangan (-)
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
                ]);
            }

            return $order;
        });

        $request->session()->forget('cart');

        // BARU: Format pesan dan redirect otomatis ke WhatsApp Admin Toko
        $whatsappNumber = '6281234567890'; // Ganti dengan nomor WhatsApp Admin Lintang Bakery (awali 62)
        
        $message = "Halo Admin Lintang Bakery, saya ingin konfirmasi pesanan baru:%0A" .
                   "• Kode Pesanan: *{$order->code}*%0A" .
                   "• Nama Pembeli: {$order->buyer}%0A" .
                   "• No. Telepon: {$order->phone}%0A" .
                   "• Alamat: {$order->address}, {$order->city}, {$order->province}%0A" .
                   "• Total Pembayaran: Rp " . number_format($paymentDue, 0, ',', '.') . "%0A" .
                   "• Status: Menunggu Konfirmasi%0A%0AMohon segera diproses ya kak. Terima kasih!";

        $whatsappUrl = "https://wa.me/{$whatsappNumber}?text=" . $message;

        return redirect()->away($whatsappUrl);
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

            // DIPERBAIKI: Operator perkalian (*) subtotal diperbaiki
            return $product ? ['product' => $product, 'quantity' => $quantity, 'subtotal' => $product->price_per_bal * $quantity] : null;
        })->filter()->values()->all();
    }
}