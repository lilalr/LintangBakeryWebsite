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
        <img src="/images/products/tambang-besar.jpg" alt="Aneka camilan dan kue kering Lintang Bakery" style="width: 100%; height: 100%; object-fit: cover;">
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
        <h3>Pengiriman Wilayah Jawa</h3>
        <p>Diantar langsung oleh armada Lintang Bakery khusus area Pulau Jawa, aman sampai ke lokasi Anda.</p>
    </div>
</section>

{{-- Lokasi Toko & Rumah Produksi --}}
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/leaflet@1.9.4/dist/leaflet.css" />

<section class="store-location-card" style="margin: 40px 0 20px; padding: 32px; border: 1px solid var(--line); border-radius: 16px; background: white; box-shadow: 0 4px 20px rgba(92,58,33,0.03);">
    <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 20px; margin-bottom: 24px;">
        <div>
            <span class="eyebrow" style="color: var(--brown); font-weight: 750; font-size: 11px; letter-spacing: 0.08em; text-transform: uppercase;">LOKASI TOKO &amp; PABRIK PRODUKSI</span>
            <h2 style="font-size: 24px; font-weight: 750; color: var(--brown-dark); margin: 6px 0 8px;">Kunjungi Lintang Bakery</h2>
            <p style="margin: 0; color: #66564c; font-size: 14px; max-width: 680px; line-height: 1.6; font-weight: 400;">
                Jl. Jend. Sudirman No.38, Ciamis, Kec. Ciamis, Kabupaten Ciamis, Jawa Barat 46211
            </p>
        </div>
        <div>
            <a href="https://maps.google.com/?q=Jl.+Jend.+Sudirman+No.38,+Ciamis,+Kec.+Ciamis,+Kabupaten+Ciamis,+Jawa+Barat+46211" target="_blank" rel="noopener noreferrer" class="button" style="display: inline-flex; align-items: center; gap: 8px; padding: 11px 20px; font-size: 13px; text-decoration: none;">
                <svg style="width: 15px; height: 15px; stroke: currentColor; fill: none; stroke-width: 2;" viewBox="0 0 24 24"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" x2="21" y1="14" y2="3"/></svg>
                <span>Buka di Google Maps ↗</span>
            </a>
        </div>
    </div>

    {{-- Map Container with Leaflet & OpenStreetMap --}}
    <div style="border-radius: 12px; overflow: hidden; border: 1px solid var(--line); position: relative; box-shadow: 0 2px 10px rgba(0,0,0,0.04); background: #fbf7f2;">
        <div id="storeLocationMap" style="width: 100%; height: 380px; z-index: 1;">
            {{-- Fallback iframe jika JavaScript Leaflet terhalang --}}
            <iframe 
                src="https://www.openstreetmap.org/export/embed.html?bbox=108.3440%2C-7.3320%2C108.3600%2C-7.3200&layer=mapnik&marker=-7.326221%2C108.351795" 
                width="100%" 
                height="380" 
                style="border: 0; display: block;" 
                loading="lazy">
            </iframe>
        </div>
        <div style="background: white; padding: 10px 16px; border-top: 1px solid #ebd9c8; display: flex; justify-content: space-between; align-items: center; font-size: 12px; color: #786b62; flex-wrap: wrap; gap: 8px;">
            <span>Jl. Jend. Sudirman No.38, Ciamis, Jawa Barat 46211</span>
            <span>&copy; OpenStreetMap contributors</span>
        </div>
    </div>
</section>

<script src="https://cdn.jsdelivr.net/npm/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    var lat = -7.326221;
    var lng = 108.351795;
    var mapContainer = document.getElementById('storeLocationMap');

    if (typeof L !== 'undefined' && mapContainer) {
        // Clear fallback iframe inside container
        mapContainer.innerHTML = '';

        var map = L.map('storeLocationMap', { 
            scrollWheelZoom: false 
        }).setView([lat, lng], 16);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '&copy; OpenStreetMap'
        }).addTo(map);

        // Custom marker LK (Lintang Kue / Bakery) exactly matching reference
        var lkIcon = L.divIcon({
            className: 'lk-custom-pin',
            html: '<div style="position:relative; width:44px; height:50px;">' +
                  '<div style="background:white; border:2.5px solid #5c3a21; border-radius:50%; width:40px; height:40px; display:flex; align-items:center; justify-content:center; font-weight:800; font-size:14px; color:#5c3a21; box-shadow:0 4px 12px rgba(92,58,33,0.3);">LK</div>' +
                  '<div style="position:absolute; bottom:2px; left:16px; width:0; height:0; border-left:6px solid transparent; border-right:6px solid transparent; border-top:8px solid #5c3a21;"></div>' +
                  '</div>',
            iconSize: [44, 50],
            iconAnchor: [22, 50],
            popupAnchor: [0, -45]
        });

        var marker = L.marker([lat, lng], { icon: lkIcon }).addTo(map);
        marker.bindPopup(
            '<div style="font-family: inherit; font-size: 13px; line-height: 1.4; padding: 2px;">' +
            '<strong style="color: #5c3a21; font-size: 14px;">Lintang Bakery</strong><br>' +
            'Pabrik &amp; Toko Aneka Kue Lintang<br>' +
            '<span style="color: #666; font-size: 11.5px;">Jl. Jend. Sudirman No.38, Ciamis</span>' +
            '</div>'
        ).openPopup();

        setTimeout(function() {
            map.invalidateSize();
        }, 300);
    }
});
</script>
@endsection
