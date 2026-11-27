
@extends('layouts.store')

@section('title', 'Camilan & Kue Kering Grosir Reseller · Lintang Bakery')

@section('content')
<<<<<<< Updated upstream
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
=======
>>>>>>> Stashed changes

<style>
    /* ==============================
       LINTANG BAKERY HOMEPAGE
    ============================== */

    .lb-home {
        --lb-brown: #70452d;
        --lb-dark: #39251b;
        --lb-cream: #fbf7f1;
        --lb-muted: #806f63;
        --lb-border: #eadfd4;
        color: var(--lb-dark);
    }

<<<<<<< Updated upstream
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
=======
    .lb-home * {
        box-sizing: border-box;
    }

    /* HERO */

    .lb-hero {
        display: grid;
        grid-template-columns: 1.05fr .95fr;
        align-items: center;
        gap: 48px;
        padding: 58px 54px;
        min-height: 440px;
        background: linear-gradient(120deg, #f8eee3, #fdfaf6);
        border: 1px solid #efe2d5;
        border-radius: 24px;
        overflow: hidden;
        position: relative;
    }

    .lb-hero-copy {
        position: relative;
        z-index: 1;
    }

    .lb-eyebrow {
        display: inline-flex;
        align-items: center;
        gap: 9px;
        color: var(--lb-brown);
        font-size: 11px;
        font-weight: 800;
        letter-spacing: 2px;
        text-transform: uppercase;
        margin-bottom: 20px;
    }

    .lb-eyebrow::before {
        content: "";
        width: 25px;
        height: 2px;
        background: #b77b4e;
    }

    .lb-hero h1 {
        color: var(--lb-dark);
        font-family: Georgia, serif;
        font-size: clamp(38px, 5vw, 61px);
        font-weight: 700;
        line-height: 1.13;
        letter-spacing: -1.8px;
        margin: 0 0 22px;
    }

    .lb-hero h1 em {
        color: #a66e46;
        font-weight: 500;
    }

    .lb-hero-description {
        max-width: 490px;
        color: #756457;
        font-size: 15px;
        line-height: 1.9;
        margin: 0 0 30px;
    }

    .lb-hero-actions {
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 14px;
    }

    .lb-primary-button {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 12px;
        padding: 14px 23px;
        border-radius: 9px;
        background: var(--lb-brown);
        color: white;
        font-size: 13px;
        font-weight: 700;
        text-decoration: none;
        transition: .25s ease;
    }

    .lb-primary-button:hover {
        background: var(--lb-dark);
        color: white;
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(72, 43, 26, .16);
    }

    .lb-hero-note {
        color: var(--lb-muted);
        font-size: 12px;
    }

    .lb-hero-art {
        position: relative;
        height: 335px;
    }

    .lb-hero-image {
        width: 100%;
        height: 100%;
        object-fit: cover;
        border-radius: 20px;
        box-shadow: 0 18px 40px rgba(65, 42, 26, .17);
    }

    .lb-image-label {
        position: absolute;
        left: -18px;
        bottom: 22px;
        background: white;
        padding: 14px 19px;
        border-radius: 12px;
        box-shadow: 0 8px 25px rgba(0,0,0,.1);
    }

    .lb-image-label strong {
        display: block;
        font-family: Georgia, serif;
        font-size: 17px;
        color: var(--lb-dark);
    }

    .lb-image-label span {
        color: var(--lb-muted);
        font-size: 11px;
    }

    /* SECTION HEADING */

    .lb-section-heading {
        display: flex;
        justify-content: space-between;
        align-items: end;
        gap: 20px;
        margin: 64px 0 26px;
    }

    .lb-section-heading .lb-eyebrow {
        margin-bottom: 9px;
    }

    .lb-section-heading h2 {
        font-family: Georgia, serif;
        font-size: 34px;
        color: var(--lb-dark);
        margin: 0;
        letter-spacing: -.7px;
    }

    .lb-text-link {
        color: var(--lb-brown);
        font-size: 13px;
        font-weight: 750;
        text-decoration: none;
        white-space: nowrap;
        transition: .2s;
    }

    .lb-text-link:hover {
        color: #b77b4e;
    }

    /* PRODUCT GRID */

    .lb-home .product-grid {
        margin-bottom: 20px;
    }

    /* FEATURE */

    .lb-features {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 18px;
        margin: 70px 0;
    }

    .lb-feature-card {
        padding: 27px 25px;
        background: #fff;
        border: 1px solid var(--lb-border);
        border-radius: 15px;
        transition: .25s ease;
    }

    .lb-feature-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 12px 28px rgba(72, 43, 26, .07);
        border-color: #d6b89e;
    }

    .lb-feature-number {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 43px;
        height: 43px;
        margin-bottom: 20px;
        background: #f6eee5;
        border-radius: 12px;
        color: var(--lb-brown);
        font-family: Georgia, serif;
        font-size: 17px;
        font-weight: 700;
    }

    .lb-feature-card h3 {
        font-family: Georgia, serif;
        font-size: 19px;
        margin: 0 0 10px;
        color: var(--lb-dark);
    }

    .lb-feature-card p {
        color: var(--lb-muted);
        font-size: 13px;
        line-height: 1.8;
        margin: 0;
    }

    /* LOCATION */

    .lb-location {
        margin: 65px 0 35px;
        padding: 32px;
        background: #fff;
        border: 1px solid var(--lb-border);
        border-radius: 20px;
        box-shadow: 0 8px 30px rgba(72, 43, 26, .045);
    }

    .lb-location-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 20px;
        margin-bottom: 25px;
    }

    .lb-location-header .lb-eyebrow {
        margin-bottom: 10px;
    }

    .lb-location-header h2 {
        font-family: Georgia, serif;
        font-size: 30px;
        color: var(--lb-dark);
        margin: 0 0 10px;
    }

    .lb-location-address {
        color: var(--lb-muted);
        font-size: 13px;
        line-height: 1.8;
        margin: 0;
    }

    .lb-map-button {
        display: inline-flex;
        align-items: center;
        gap: 9px;
        padding: 12px 18px;
        border: 1px solid var(--lb-border);
        border-radius: 9px;
        color: var(--lb-brown);
        background: #fff;
        text-decoration: none;
        font-size: 12px;
        font-weight: 750;
        transition: .2s;
    }

    .lb-map-button:hover {
        background: var(--lb-brown);
        color: white;
        border-color: var(--lb-brown);
    }

    .lb-map-frame {
        overflow: hidden;
        border-radius: 13px;
        border: 1px solid var(--lb-border);
        background: var(--lb-cream);
    }

    #storeLocationMap {
        width: 100%;
        height: 370px;
        z-index: 1;
    }

    .lb-map-footer {
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 10px;
        padding: 13px 17px;
        background: #fcfaf7;
        border-top: 1px solid var(--lb-border);
        color: var(--lb-muted);
        font-size: 11px;
    }

    /* EMPTY PRODUCT */

    .lb-home .empty-state {
        padding: 35px 20px;
        text-align: center;
        color: var(--lb-muted);
        background: var(--lb-cream);
        border: 1px dashed var(--lb-border);
        border-radius: 12px;
    }

    /* RESPONSIVE */

    @media (max-width: 900px) {
        .lb-hero {
            gap: 28px;
            padding: 40px 30px;
        }

        .lb-hero-art {
            height: 290px;
        }

        .lb-features {
            gap: 12px;
        }

        .lb-feature-card {
            padding: 22px 18px;
        }
    }

    @media (max-width: 680px) {
        .lb-hero {
            grid-template-columns: 1fr;
            padding: 32px 23px;
            gap: 30px;
            border-radius: 17px;
        }

        .lb-hero h1 {
            font-size: 42px;
        }

        .lb-hero-description {
            font-size: 13px;
        }

        .lb-hero-art {
            height: 245px;
        }

        .lb-image-label {
            left: 12px;
            bottom: 12px;
        }

        .lb-section-heading {
            align-items: flex-start;
            flex-direction: column;
            margin: 48px 0 22px;
        }

        .lb-section-heading h2 {
            font-size: 29px;
        }

        .lb-features {
            grid-template-columns: 1fr;
            margin: 48px 0;
        }

        .lb-feature-card {
            padding: 23px;
        }

        .lb-location {
            padding: 21px;
            margin-top: 45px;
        }

        .lb-location-header h2 {
            font-size: 25px;
        }

        #storeLocationMap {
            height: 290px;
        }

        .lb-map-button {
            width: 100%;
            justify-content: center;
        }
    }
</style>

<div class="lb-home">

    {{-- HERO SECTION --}}
    <section class="lb-hero">
        <div class="lb-hero-copy">
            <span class="lb-eyebrow">
                Camilan & Kue Kering Grosir · Sejak 1998
            </span>

            <h1>
                Rasa rumahan,<br>
                <em>kualitas pilihan.</em>
            </h1>

            <p class="lb-hero-description">
                Grosir aneka camilan renyah dan kue kering gurih khas
                Lintang Bakery. Pilihan tepat untuk mitra reseller,
                toko oleh-oleh, dan usaha repack dengan minimal order
                100 bal, bisa mix 50/50.
            </p>

            <div class="lb-hero-actions">
                <a class="lb-primary-button" href="#produk">
                    Jelajahi Produk
                    <span>→</span>
                </a>

                <span class="lb-hero-note">
                    Melayani mitra reseller sejak 1998
                </span>
            </div>
        </div>

        <div class="lb-hero-art">
            <img
                src="{{ asset('images/products/tambang-besar.jpg') }}"
                alt="Aneka camilan dan kue kering Lintang Bakery"
                class="lb-hero-image"
            >

            <div class="lb-image-label">
                <strong>Lintang Bakery</strong>
                <span>Kualitas dan cita rasa pilihan</span>
            </div>
        </div>
    </section>


    {{-- PRODUCT SECTION --}}
    <section class="lb-section-heading" id="produk">
        <div>
            <span class="lb-eyebrow">Produk Unggulan Reseller</span>
            <h2>Temukan produk pilihan</h2>
        </div>

        <a class="lb-text-link" href="{{ route('store.products') }}">
            Lihat semua produk →
        </a>
    </section>

    <div class="product-grid">
        @forelse ($products as $product)
            @include('store.partials.product-card', ['product' => $product])
        @empty
            <div class="empty-state">
                Produk sedang disiapkan. Silakan kembali lagi nanti.
            </div>
        @endforelse
    </div>


    {{-- ADVANTAGES SECTION --}}
    <section class="lb-features">

        <div class="lb-feature-card">
            <div class="lb-feature-number">01</div>
            <h3>Kualitas Terjamin</h3>
            <p>
                Menggunakan bahan pilihan, menghasilkan camilan
                yang renyah, gurih, dan diproses secara higienis.
            </p>
        </div>

        <div class="lb-feature-card">
            <div class="lb-feature-number">02</div>
            <h3>Fleksibel untuk Reseller</h3>
            <p>
                Minimal pemesanan total 100 bal. Anda dapat
                mengombinasikan produk dengan sistem mix 50/50.
            </p>
        </div>

        <div class="lb-feature-card">
            <div class="lb-feature-number">03</div>
            <h3>Pengiriman Wilayah Jawa</h3>
            <p>
                Pengiriman langsung menggunakan armada Lintang Bakery
                untuk wilayah Pulau Jawa.
            </p>
        </div>

    </section>


    {{-- STORE LOCATION SECTION --}}
    <section class="lb-location">

        <div class="lb-location-header">
            <div>
                <span class="lb-eyebrow">Lokasi Toko & Pabrik Produksi</span>

                <h2>Kunjungi Lintang Bakery</h2>

                <p class="lb-location-address">
                    Jl. Jend. Sudirman No.38, Ciamis, Kec. Ciamis,
                    Kabupaten Ciamis, Jawa Barat 46211
                </p>
            </div>

            <a
                href="https://maps.google.com/?q=Jl.+Jend.+Sudirman+No.38,+Ciamis,+Kec.+Ciamis,+Kabupaten+Ciamis,+Jawa+Barat+46211"
                target="_blank"
                rel="noopener noreferrer"
                class="lb-map-button"
            >
                <svg
                    width="16"
                    height="16"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="2"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    aria-hidden="true"
                >
                    <path d="M15 3h6v6"/>
                    <path d="M10 14 21 3"/>
                    <path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/>
                </svg>
                Buka Google Maps
            </a>
        </div>

        <div class="lb-map-frame">
            <div id="storeLocationMap">
                <iframe
                    src="https://www.openstreetmap.org/export/embed.html?bbox=108.3440%2C-7.3320%2C108.3600%2C-7.3200&layer=mapnik&marker=-7.326221%2C108.351795"
                    width="100%"
                    height="370"
                    style="border:0; display:block;"
                    loading="lazy"
                    title="Peta lokasi Lintang Bakery"
                ></iframe>
            </div>

            <div class="lb-map-footer">
                <span>Jl. Jend. Sudirman No.38, Ciamis, Jawa Barat</span>
                <span>© OpenStreetMap contributors</span>
            </div>
        </div>

    </section>

</div>


{{-- LEAFLET MAP --}}
<link
    rel="stylesheet"
    href="https://cdn.jsdelivr.net/npm/leaflet@1.9.4/dist/leaflet.css"
/>

<script src="https://cdn.jsdelivr.net/npm/leaflet@1.9.4/dist/leaflet.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const lat = -7.326221;
    const lng = 108.351795;
    const mapContainer = document.getElementById('storeLocationMap');

    if (!mapContainer || typeof L === 'undefined') {
        return;
    }

    // Menghapus iframe fallback ketika Leaflet tersedia.
    mapContainer.innerHTML = '';

    const map = L.map('storeLocationMap', {
        scrollWheelZoom: false
    }).setView([lat, lng], 16);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '&copy; OpenStreetMap contributors'
    }).addTo(map);

    const lkIcon = L.divIcon({
        className: 'lk-custom-pin',
        html: `
            <div style="
                width:44px;
                height:50px;
                position:relative;
            ">
                <div style="
                    width:40px;
                    height:40px;
                    display:flex;
                    align-items:center;
                    justify-content:center;
                    background:#fff;
                    border:2.5px solid #70452d;
                    border-radius:50%;
                    color:#70452d;
                    font-size:14px;
                    font-weight:800;
                    box-shadow:0 4px 12px rgba(92,58,33,.25);
                ">LK</div>

                <div style="
                    position:absolute;
                    bottom:2px;
                    left:16px;
                    width:0;
                    height:0;
                    border-left:6px solid transparent;
                    border-right:6px solid transparent;
                    border-top:8px solid #70452d;
                "></div>
            </div>
        `,
        iconSize: [44, 50],
        iconAnchor: [22, 50],
        popupAnchor: [0, -45]
    });

    const marker = L.marker([lat, lng], {
        icon: lkIcon
    }).addTo(map);

    marker.bindPopup(`
        <div style="font-size:13px; line-height:1.6;">
            <strong style="color:#70452d; font-size:15px;">
                Lintang Bakery
            </strong>
            <br>
            Pabrik & Toko Aneka Kue Lintang
            <br>
            <span style="color:#777; font-size:11px;">
                Jl. Jend. Sudirman No.38, Ciamis
            </span>
        </div>
    `).openPopup();

    setTimeout(function () {
        map.invalidateSize();
    }, 300);
});
</script>

>>>>>>> Stashed changes
@endsection
