@extends('layouts.store')

@section('title', 'Checkout · Lintang Bakery')

@section('content')
<section class="page-heading"><span class="eyebrow">SATU LANGKAH LAGI</span><h1>Checkout pesanan</h1><p>Isi data penerima dan pilih metode pembayaran.</p></section>
<div class="checkout-grid">
    <form method="post" action="{{ route('checkout.store') }}" enctype="multipart/form-data" class="form-card stack-form">@csrf
        <h2>Data penerima</h2>
        <label>Nama lengkap<input name="buyer" value="{{ old('buyer', auth()->user()->name) }}" required></label>
        <label>No. telepon<input name="phone" value="{{ old('phone', auth()->user()->phone) }}" required></label>
        <label>Alamat lengkap<textarea name="address" rows="3" required>{{ old('address', auth()->user()->address) }}</textarea></label>
        <div class="form-row"><label>Kota / kabupaten<input name="city" value="{{ old('city') }}" required></label><label>Provinsi<input name="province" value="{{ old('province') }}" required></label></div>
        <h2>Pembayaran</h2>
        <label class="choice-card"><input type="radio" name="payment_type" value="full" @checked(old('payment_type', 'full') === 'full')> Lunas di muka</label>
        <label class="choice-card"><input type="radio" name="payment_type" value="dp" @checked(old('payment_type') === 'dp')> Uang muka 65%, pelunasan menyusul</label>
        <label>Metode pembayaran<select name="payment_method" required><option value="bank_transfer" @selected(old('payment_method', 'bank_transfer') === 'bank_transfer')>Transfer bank</option><option value="qris" @selected(old('payment_method') === 'qris')>QRIS</option></select></label>
        <label>Bukti pembayaran (opsional)<input type="file" name="payment_proof" accept="image/*"></label>
        <button class="button button-full" type="submit">Buat pesanan</button>
    </form>
    <aside class="summary-card"><h2>Ringkasan</h2>
        @foreach ($cart as $row)<div class="summary-line"><span>{{ $row['product']->name }} × {{ $row['quantity'] }}</span><strong>Rp {{ number_format($row['subtotal'], 0, ',', '.') }}</strong></div>@endforeach
        <div class="summary-line"><span>Ongkir (Rp 10.000 × jumlah bal)</span><strong>Rp {{ number_format(collect($cart)->sum('quantity') * 10000, 0, ',', '.') }}</strong></div>
        <div class="summary-total"><span>Total pesanan</span><strong>Rp {{ number_format(collect($cart)->sum('subtotal') + collect($cart)->sum('quantity') * 10000, 0, ',', '.') }}</strong></div>
    </aside>
</div>
@endsection
