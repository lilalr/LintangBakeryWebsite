@extends('layouts.store')

@section('title', 'Keranjang · Lintang Bakery')

@section('content')
<section class="page-heading"><span class="eyebrow">PESANAN ANDA</span><h1>Keranjang belanja</h1><p>Periksa jumlah produk sebelum melanjutkan checkout.</p></section>
@if (count($cart))
    <div class="table-card">
        <table><thead><tr><th>Produk</th><th>Harga / bal</th><th>Jumlah</th><th>Subtotal</th><th></th></tr></thead><tbody>
        @foreach ($cart as $row)
            <tr><td><div class="table-product"><img src="{{ $row['product']->image }}" alt=""><strong>{{ $row['product']->name }}</strong></div></td><td>Rp {{ number_format($row['product']->price_per_bal, 0, ',', '.') }}</td>
                <td><form method="post" action="{{ route('cart.update', $row['product']) }}" class="quantity-form">@csrf @method('PATCH')<input type="number" name="quantity" value="{{ $row['quantity'] }}" min="1" required><button class="button button-small button-light">Ubah</button></form></td>
                <td><strong>Rp {{ number_format($row['subtotal'], 0, ',', '.') }}</strong></td><td><form method="post" action="{{ route('cart.destroy', $row['product']) }}">@csrf @method('DELETE')<button class="text-danger">Hapus</button></form></td></tr>
        @endforeach
        </tbody></table>
    </div>
    <div class="cart-summary"><div><span>Total produk</span><strong>Rp {{ number_format(collect($cart)->sum('subtotal'), 0, ',', '.') }}</strong></div><p>Ongkos kirim dihitung saat checkout.</p><a class="button" href="{{ auth()->check() ? route('checkout.index') : route('login') }}">Lanjut ke checkout</a></div>
@else
    <div class="empty-state"><h2>Keranjang masih kosong</h2><p>Yuk, temukan kue favoritmu di katalog.</p><a class="button" href="{{ route('store.products') }}">Lihat produk</a></div>
@endif
@endsection
