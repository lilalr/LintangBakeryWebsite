@extends('layouts.store')

@section('title', 'Checkout · Lintang Bakery')

@section('content')
<section class="page-heading">
    <span class="eyebrow">SATU LANGKAH LAGI</span>
    <h1>Checkout pesanan</h1>
    <p>Periksa data penerima dan pilih metode pembayaran.</p>
</section>

<div class="checkout-grid">
    <form method="post" action="{{ route('checkout.store') }}" enctype="multipart/form-data" class="form-card stack-form">
        @csrf
        <h2>Data penerima</h2>
        
        <label>Nama lengkap
            <input name="buyer" value="{{ old('buyer', $user->name) }}" required>
        </label>
        
        <label>No. telepon
            <input name="phone" value="{{ old('phone', $user->phone) }}" required>
        </label>
        
        <label>Alamat lengkap
            <textarea name="address" rows="3" required>{{ old('address', $user->address) }}</textarea>
        </label>

        {{-- Provinsi & Kota otomatis diambil dari profil user dan dikirim via hidden input --}}
        <div class="profile-region-info" style="background: #f9f9f9; padding: 12px; border-radius: 6px; margin-bottom: 16px;">
            <p style="margin: 0; font-size: 0.95rem;"><strong>Provinsi:</strong> {{ $user->province ?? '-' }}</p>
            <p style="margin: 4px 0 0 0; font-size: 0.95rem;"><strong>Kota / Kabupaten:</strong> {{ $user->city ?? '-' }}</p>
            <small style="color: #666;">(Data wilayah dikirim otomatis sesuai pengaturan profil akun Anda).</small>
        </div>

        <input type="hidden" name="province" value="{{ $user->province }}">
        <input type="hidden" name="city" value="{{ $user->city }}">

        <h2>Pembayaran</h2>
        <label class="choice-card">
            <input type="radio" name="payment_type" value="full" @checked(old('payment_type', 'full') === 'full')> Lunas di muka
        </label>
        <label class="choice-card">
            <input type="radio" name="payment_type" value="dp" @checked(old('payment_type') === 'dp')> Uang muka 65%, pelunasan menyusul
        </label>
        
        <label>Bukti pembayaran (opsional)
            <input type="file" name="payment_proof" accept="image/*">
        </label>
        
        <button class="button button-full" type="submit">Buat pesanan</button>
    </form>

    <aside class="summary-card">
        <h2>Ringkasan</h2>
        @foreach ($cart as $row)
            @if (isset($row['product']))
                <div class="summary-line">
                    <span>{{ $row['product']->name }} × {{ $row['quantity'] }}</span>
                    <strong>Rp {{ number_format($row['subtotal'], 0, ',', '.') }}</strong>
                </div>
            @endif
        @endforeach
        <div class="summary-line">
            <span>Ongkir (Rp 10.000 × jumlah bal)</span>
            <strong>Rp {{ number_format(collect($cart)->sum('quantity') * 10000, 0, ',', '.') }}</strong>
        </div>
        <div class="summary-total">
            <span>Total pesanan</span>
            <strong>Rp {{ number_format(collect($cart)->sum('subtotal') + collect($cart)->sum('quantity') * 10000, 0, ',', '.') }}</strong>
        </div>
    </aside>
</div>
@endsection