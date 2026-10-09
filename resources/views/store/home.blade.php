@extends('layouts.store')

@section('title', 'Kue Kering Premium · Lintang Bakery')

@section('content')
<section class="hero">
    <div class="hero-copy">
        <span class="eyebrow">KUE KERING PREMIUM · SEJAK 1998</span>
        <h1>Rasa rumahan,<br><em>kualitas pilihan.</em></h1>
        <p>Temukan kue kering premium Lintang Bakery untuk toko, reseller, dan momen spesial keluarga.</p>
        <a class="button" href="{{ route('store.products') }}">Jelajahi produk <span>→</span></a>
    </div>
    <div class="hero-art"><img src="https://images.unsplash.com/photo-1558961363-fa8fdf82db35?w=1000&h=700&fit=crop&auto=format" alt="Aneka kue kering Lintang Bakery"></div>
</section>
<section class="section-head"><div><span class="eyebrow">DIBUAT DENGAN SEPENUH HATI</span><h2>Produk pilihan</h2></div><a class="text-link" href="{{ route('store.products') }}">Lihat semua produk →</a></section>
<div class="product-grid">
    @forelse ($products as $product)
        @include('store.partials.product-card', ['product' => $product])
    @empty
        <div class="empty-state">Produk sedang disiapkan. Silakan kembali lagi nanti.</div>
    @endforelse
</div>
<section class="feature-strip"><div><strong>01</strong><h3>Bahan berkualitas</h3><p>Dibuat dari bahan pilihan dengan proses yang higienis.</p></div><div><strong>02</strong><h3>Untuk reseller</h3><p>Harga per bal yang cocok untuk usaha dan distribusi.</p></div><div><strong>03</strong><h3>Pantau pesanan</h3><p>Lihat perkembangan pesanan langsung dari akun Anda.</p></div></section>
@endsection
