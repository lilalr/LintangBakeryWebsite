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
            <article class="order-card" style="transition: transform .15s ease, box-shadow .15s ease;">
                <div class="order-card-head">
                    <div>
                        <strong>{{ $order->code }}</strong>
                        <span>{{ $order->created_at->format('d/m/Y H:i') }}</span>
                    </div>
                    <span class="status-pill">{{ $order->status }}</span>
                </div>
                <p style="margin: 8px 0; color: #4e3e34; font-size: 13px;">{{ $order->items->pluck('name')->join(', ') }}</p>
                <div class="order-card-foot" style="flex-wrap: wrap; gap: 8px; margin-top: 10px; padding-top: 10px; border-top: 1px dashed var(--line); display: flex; justify-content: space-between; align-items: center;">
                    <div>
                        <strong style="color: var(--brown-dark); font-size: 13.5px;">Total: Rp {{ number_format($order->total + $order->shipping_cost, 0, ',', '.') }}</strong>
                        @if ($order->payment_type === 'dp')
                            <small style="display:block; color:#92400e; font-size:11px; font-weight:600;">
                                DP: Rp {{ number_format($order->dp, 0, ',', '.') }} · Sisa: Rp {{ number_format($order->remaining, 0, ',', '.') }}
                                @if ($order->settlement_status === 'Menunggu Verifikasi Admin')
                                    <span style="color:#2563eb;">(Pelunasan Diunggah)</span>
                                @endif
                            </small>
                        @else
                            <small style="display:block; color:#15803d; font-size:11px; font-weight:600;">Pembayaran Lunas</small>
                        @endif
                    </div>
                    <a href="{{ route('buyer.orders.show', $order) }}" class="button button-small" style="font-size: 11.5px; padding: 6px 12px; text-decoration: none;">Lihat Detail & Tracking →</a>
                </div>
            </article>
        @empty
            <div class="empty-state small-empty"><p>Belum ada pesanan.</p><a class="text-link" href="{{ route('store.products') }}">Mulai belanja →</a></div>
        @endforelse
    </section>
</div>
@endsection
