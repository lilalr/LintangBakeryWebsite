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

{{-- Lokasi Toko & Rumah Produksi --}}
<section class="store-location-card" style="margin: 40px 0 20px; padding: 32px; border: 1px solid var(--line); border-radius: 16px; background: white; box-shadow: 0 4px 20px rgba(92,58,33,0.03);">
    <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 20px; margin-bottom: 24px;">
        <div>
            <span class="eyebrow" style="color: var(--brown); font-weight: 750; font-size: 11px; letter-spacing: 0.08em; text-transform: uppercase;">LOKASI TOKO &amp; PABRIK PRODUKSI</span>
            <h2 style="font-size: 24px; font-weight: 750; color: var(--brown-dark); margin: 6px 0 8px;">Kunjungi Lintang Bakery</h2>
            <p style="margin: 0; color: #53463c; font-size: 14px; max-width: 680px; line-height: 1.6; display: flex; align-items: flex-start; gap: 8px;">
                <svg style="width: 18px; height: 18px; flex-shrink: 0; margin-top: 2px; stroke: var(--brown); fill: none; stroke-width: 2;" viewBox="0 0 24 24"><path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"/><circle cx="12" cy="10" r="3"/></svg>
                <span><strong>Jl. Leuwi Anyar No. 18 RT 001 RW 003 Babakan, Jl. Kalangsari, Sukamanah, Kec. Cipedes, Kota Tasikmalaya, Jawa Barat 46131</strong></span>
            </p>
        </div>
        <div>
            <a href="https://maps.google.com/?q=Jl.+Leuwi+Anyar+No.+18+RT+001+RW+003+Babakan,+Jl.+Kalangsari,+Sukamanah,+Kec.+Cipedes,+Kota+Tasikmalaya,+Jawa+Barat+46131" target="_blank" rel="noopener noreferrer" class="button" style="display: inline-flex; align-items: center; gap: 8px; padding: 11px 20px; font-size: 13px; text-decoration: none;">
                <svg style="width: 15px; height: 15px; stroke: currentColor; fill: none; stroke-width: 2;" viewBox="0 0 24 24"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" x2="21" y1="14" y2="3"/></svg>
                <span>Buka di Google Maps ↗</span>
            </a>
        </div>
    </div>

    <div style="border-radius: 12px; overflow: hidden; border: 1px solid var(--line); position: relative; min-height: 380px; background: #faf6f0;">
        <iframe 
            src="https://maps.google.com/maps?q=Jl.+Leuwi+Anyar+No.+18,+RT.001/RW.003,+Babakan,+Kalangsari,+Sukamanah,+Kec.+Cipedes,+Kota+Tasikmalaya,+Jawa+Barat+46131&t=&z=16&ie=UTF8&iwloc=&output=embed" 
            width="100%" 
            height="380" 
            style="border: 0; display: block;" 
            allowfullscreen="" 
            loading="lazy" 
            referrerpolicy="no-referrer-when-downgrade">
        </iframe>
    </div>
</section>
@endsection
