@extends('layouts.store')

@section('title', 'Akun User · Lintang Bakery')

@section('content')
<section class="page-heading"><span class="eyebrow">AREA USER</span><h1>Halo, {{ auth()->user()->name }}</h1><p>Pantau pesanan dan perbarui informasi akun Anda.</p></section>
<div class="dashboard-grid">
    <section class="form-card">
        <div class="section-head compact"><div><span class="eyebrow">INFORMASI AKUN</span><h2>Profil saya</h2></div></div>
        <form method="post" action="{{ route('buyer.profile.update') }}" class="stack-form">@csrf @method('PUT')
            <label>Nama lengkap<input name="name" value="{{ old('name', auth()->user()->name) }}" required></label>
            <label>Email<input value="{{ auth()->user()->email }}" disabled></label>
            <label>No. telepon<input name="phone" value="{{ old('phone', auth()->user()->phone) }}" required></label>
            <label>Alamat<textarea name="address" rows="3" required>{{ old('address', auth()->user()->address) }}</textarea></label>
            <button class="button button-light" type="submit">Simpan profil</button>
        </form>
    </section>
    <section>
        <div class="section-head compact"><div><span class="eyebrow">RIWAYAT</span><h2>Pesanan saya</h2></div></div>
        @forelse ($orders as $order)
            <article class="order-card">
                <div class="order-card-head"><div><strong>{{ $order->code }}</strong><span>{{ $order->created_at->format('d/m/Y') }}</span></div><span class="status-pill">{{ $order->status }}</span></div>
                <p>{{ $order->items->pluck('name')->join(', ') }}</p>
                <div class="order-card-foot"><span>Total Rp {{ number_format($order->total + $order->shipping_cost, 0, ',', '.') }}</span><span>DP: Rp {{ number_format($order->dp, 0, ',', '.') }}</span></div>
            </article>
        @empty
            <div class="empty-state small-empty"><p>Belum ada pesanan.</p><a class="text-link" href="{{ route('store.products') }}">Mulai belanja →</a></div>
        @endforelse
    </section>
</div>
@endsection
