@extends('layouts.store')

@section('title', 'Keranjang · Lintang Bakery')

@section('content')
<section class="page-heading" style="display: flex; justify-content: space-between; align-items: flex-end; flex-wrap: wrap; gap: 16px;">
    <div>
        <span class="eyebrow">PESANAN ANDA</span>
        <h1>Keranjang belanja</h1>
        <p>Periksa jumlah produk sebelum melanjutkan checkout.</p>
    </div>
    @if (count($cart))
        <a class="button button-light" href="{{ route('store.products') }}">← Tambah Produk Lain</a>
    @endif
</section>
@if (count($cart))
    <div class="table-card">
        <table><thead><tr><th>Produk</th><th>Harga / bal</th><th>Jumlah</th><th>Subtotal</th><th></th></tr></thead><tbody>
        @foreach ($cart as $row)
            <tr>
                <td><div class="table-product"><img src="{{ $row['product']->image }}" alt=""><strong>{{ $row['product']->name }}</strong></div></td>
                <td>Rp {{ number_format($row['product']->price_per_bal, 0, ',', '.') }}</td>
                <td>
                    <div class="quantity-control" aria-label="Jumlah {{ $row['product']->name }}">
                        <form method="post" action="{{ route('cart.update', $row['product']) }}">
                            @csrf
                            @method('PATCH')
                            <input type="hidden" name="quantity" value="{{ max(1, $row['quantity'] - 1) }}">
                            <button class="quantity-button" type="submit" aria-label="Kurangi jumlah" @disabled($row['quantity'] <= 1)>−</button>
                        </form>
                        <span class="quantity-value" aria-live="polite">{{ $row['quantity'] }}</span>
                        <form method="post" action="{{ route('cart.update', $row['product']) }}">
                            @csrf
                            @method('PATCH')
                            <input type="hidden" name="quantity" value="{{ $row['quantity'] + 1 }}">
                            <button class="quantity-button" type="submit" aria-label="Tambah jumlah" @disabled($row['quantity'] >= 10000)>+</button>
                        </form>
                    </div>
                </td>
                <td><strong>Rp {{ number_format($row['subtotal'], 0, ',', '.') }}</strong></td>
                <td><form method="post" action="{{ route('cart.destroy', $row['product']) }}">@csrf @method('DELETE')<button class="text-danger" type="submit">Hapus</button></form></td>
            </tr>
        @endforeach
        </tbody></table>
    </div>
    <div class="cart-summary">
        <div><span>Total produk</span><strong>Rp {{ number_format(collect($cart)->sum('subtotal'), 0, ',', '.') }}</strong></div>
        <p>Ongkos kirim dihitung saat checkout.</p>
        <div style="display: flex; flex-direction: column; gap: 8px; margin-top: 12px;">
            <a class="button" href="{{ route('checkout.index') }}">Lanjut ke Checkout →</a>
            <a class="button button-light" href="{{ route('store.products') }}" style="margin-top: 0;">Tambah Produk Lain</a>
        </div>
    </div>
@else
    <div class="empty-state"><h2>Keranjang masih kosong</h2><p>Yuk, temukan kue favoritmu di katalog.</p><a class="button" href="{{ route('store.products') }}">Lihat produk</a></div>
@endif
@endsection
