@extends('layouts.store')

@section('title', 'Camilan & Kue Kering Grosir Reseller · Lintang Bakery')

@section('content')
<section class="hero">
    <div class="hero-copy">
        <span class="eyebrow">CAMILAN & KUE KERING GROSIR · SEJAK 1998</span>
        <h1>Rasa rumahan,<br><em>kualitas pilihan.</em></h1>
        <p>Grosir aneka camilan renyah dan kue kering gurih khas Lintang Bakery. Khusus mitra reseller, toko oleh-oleh, dan repack dengan minimal order 100 bal (bisa mix 50/50).</p>
        <a class="button" href="#produk">Jelajahi produk <span>↓</span></a>
    </div>
    <div class="hero-art">
        <img src="/images/products/kueTambang.jpg" alt="Aneka camilan dan kue kering Lintang Bakery" style="width: 100%; height: 100%; object-fit: cover;">
    </div>
</section>

<section class="section-head" id="produk">
    <div>
        <span class="eyebrow">PRODUK UNGGULAN RESELLER</span>
        <h2>Produk pilihan</h2>
    </div>
    <a class="text-link" href="{{ route('store.products') }}">Lihat semua produk →</a>
</section>

<div class="product-grid">
    @forelse ($products as $product)
        @include('store.partials.product-card', ['product' => $product])
    @empty
        <div class="empty-state">Produk sedang disiapkan. Silakan kembali lagi nanti.</div>
    @endforelse
</div>

<section class="feature-strip">
    <div>
        <strong>01</strong>
        <h3>Kualitas Terjamin</h3>
        <p>Bahan pilihan, renyah tahan lama, dan diproses secara higienis.</p>
    </div>
    <div>
        <strong>02</strong>
        <h3>Bisa Mix 50/50</h3>
        <p>Min. total 100 bal untuk reseller, bisa campur produk min. 50 bal per varian.</p>
    </div>
    <div>
        <strong>03</strong>
        <h3>Kirim se-Indonesia</h3>
        <p>Packing kardus tebal standar kargo dengan armada & ekspedisi terpercaya.</p>
    </div>
</section>
@endsection
