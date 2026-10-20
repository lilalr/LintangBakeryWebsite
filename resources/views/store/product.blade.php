@extends('layouts.store')

@section('title', $product->name.' · Detail Produk Lintang Bakery')

@section('content')
<div class="breadcrumbs">
    <a href="{{ route('store.home') }}">Beranda</a>
    <span>/</span>
    <a href="{{ route('store.products') }}">Produk</a>
    <span>/</span>
    <span>{{ $product->name }}</span>
</div>

<section class="product-detail">
    {{-- Kolom Kiri: Galeri Foto & Trust Badges --}}
    <div class="detail-image-wrap">
        <div class="detail-image">
            <img src="{{ $product->image ?: 'https://images.unsplash.com/photo-1558961363-fa8fdf82db35?w=1000&h=700&fit=crop&auto=format' }}" alt="{{ $product->name }}">
        </div>
        
        <div class="trust-badges-grid">
            <div class="trust-badge-item">
                <span class="trust-badge-icon">
                    <svg style="width:18px;height:18px;stroke:currentColor;fill:none;stroke-width:2;" viewBox="0 0 24 24"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                </span>
                <div class="trust-badge-text">
                    <strong>Bahan Alami Pilihan</strong>
                    <span>Mentega & keju berkualitas</span>
                </div>
            </div>
            <div class="trust-badge-item">
                <span class="trust-badge-icon">
                    <svg style="width:18px;height:18px;stroke:currentColor;fill:none;stroke-width:2;" viewBox="0 0 24 24"><path d="M13 2 3 14h9l-1 8 10-12h-9l1-8z"/></svg>
                </span>
                <div class="trust-badge-text">
                    <strong>Produksi Baru</strong>
                    <span>Tahan hingga 6 bulan</span>
                </div>
            </div>
            <div class="trust-badge-item">
                <span class="trust-badge-icon">
                    <svg style="width:18px;height:18px;stroke:currentColor;fill:none;stroke-width:2;" viewBox="0 0 24 24"><path d="m7.5 4.27 9 5.15"/><path d="M21 8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16Z"/><path d="m3.3 7 8.7 5 8.7-5"/><path d="M12 22V12"/></svg>
                </span>
                <div class="trust-badge-text">
                    <strong>Packing Aman</strong>
                    <span>Bubble wrap tebal & karton</span>
                </div>
            </div>
            <div class="trust-badge-item">
                <span class="trust-badge-icon">
                    <svg style="width:18px;height:18px;stroke:currentColor;fill:none;stroke-width:2;" viewBox="0 0 24 24"><path d="M14 18V6a2 2 0 0 0-2-2H4a2 2 0 0 0-2 2v11a1 1 0 0 0 1 1h2"/><path d="M15 18H9"/><path d="M19 18h2a1 1 0 0 0 1-1v-3.65a1 1 0 0 0-.22-.624l-3.48-4.35A1 1 0 0 0 17.52 8H14"/><circle cx="17" cy="18.5" r="2.5"/><circle cx="7" cy="18.5" r="2.5"/></svg>
                </span>
                <div class="trust-badge-text">
                    <strong>Kirim Seluruh Kota</strong>
                    <span>Tersedia armada & ekspedisi</span>
                </div>
            </div>
        </div>
    </div>

    {{-- Kolom Kanan: Informasi Produk & Pembelian --}}
    <div class="detail-copy">
        <div class="detail-meta">
            @if ($product->stock > 10)
                <span class="badge-stock badge-stock-in" style="display:inline-flex; align-items:center; gap:5px;">
                    <svg style="width:13px;height:13px;stroke:currentColor;fill:none;stroke-width:2.5;" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
                    <span>Stok Tersedia ({{ $product->stock }} bal)</span>
                </span>
            @elseif ($product->stock > 0)
                <span class="badge-stock badge-stock-low" style="display:inline-flex; align-items:center; gap:5px;">
                    <svg style="width:13px;height:13px;stroke:currentColor;fill:none;stroke-width:2;" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="12" x2="12" y1="8" y2="12"/><line x1="12" x2="12.01" y1="16" y2="16"/></svg>
                    <span>Sisa {{ $product->stock }} bal lagi</span>
                </span>
            @else
                <span class="badge-stock badge-stock-out">✕ Stok Habis</span>
            @endif
            <span class="detail-rating">★★★★★ 5.0 · Pilihan Favorit Reseller</span>
        </div>

        <h1>{{ $product->name }}</h1>
        
        <div class="detail-price-box">
            <div class="detail-price">
                Rp {{ number_format($product->price_per_bal, 0, ',', '.') }}
                <small>/ bal</small>
            </div>
            <span class="detail-price-note">Harga grosir per bal (kemasan toples siap jual untuk reseller & toko)</span>
        </div>

        <p>{{ $product->description }}</p>

        {{-- Spesifikasi Ringkas --}}
        <div class="detail-specs">
            <div class="detail-spec-item">
                <span>Satuan Pembelian</span>
                <strong>1 Bal (Grosir)</strong>
            </div>
            <div class="detail-spec-item">
                <span>Minimal Pembelian</span>
                <strong>1 Bal</strong>
            </div>
            <div class="detail-spec-item">
                <span>Masa Simpan</span>
                <strong>Hingga 6 Bulan</strong>
            </div>
            <div class="detail-spec-item">
                <span>Kondisi Kemasan</span>
                <strong>Toples Kedap Udara</strong>
            </div>
        </div>

        {{-- Box Formulir Pembelian --}}
        <div class="detail-purchase-box">
            <form method="post" action="{{ route('cart.store', $product) }}" id="productPurchaseForm">
                @csrf
                <div class="stepper-wrap">
                    <span class="stepper-label">Jumlah Pembelian (bal):</span>
                    <div class="stepper-control">
                        <button type="button" class="stepper-btn" onclick="stepQty(-1)">−</button>
                        <input type="number" class="stepper-input" id="detailQty" name="quantity" value="1" min="1" max="{{ max(1, $product->stock) }}" oninput="updateSubtotal()">
                        <button type="button" class="stepper-btn" onclick="stepQty(1)">+</button>
                    </div>
                </div>

                <div class="subtotal-preview">
                    <span>Subtotal Produk:</span>
                    <strong id="detailSubtotal">Rp {{ number_format($product->price_per_bal, 0, ',', '.') }}</strong>
                </div>

                <div class="detail-actions">
                    <button class="button button-full" type="submit" {{ $product->stock <= 0 ? 'disabled' : '' }}>
                        {{ $product->stock <= 0 ? 'Stok Tidak Tersedia' : '+ Tambah ke Keranjang' }}
                    </button>
                </div>
            </form>

            <div class="detail-help-note">
                Pesanan partai besar atau reseller grosir? Hubungi kasir/admin di toko fisik Lintang Bakery.
            </div>
        </div>
    </div>
</section>

{{-- Section Tab Informasi Detail Produk --}}
<section class="detail-tabs-section">
    <div class="detail-tabs-nav">
        <button type="button" class="detail-tab-btn active" onclick="switchDetailTab('desc', this)">Deskripsi & Kualitas</button>
        <button type="button" class="detail-tab-btn" onclick="switchDetailTab('reseller', this)">Ketentuan Reseller & Pengiriman</button>
        <button type="button" class="detail-tab-btn" onclick="switchDetailTab('storage', this)">Cara Penyimpanan</button>
    </div>

    <div class="detail-tab-pane active" id="tab-pane-desc">
        <h3 style="margin-top:0; color:var(--brown-dark);">Kelezatan Khas Lintang Bakery Sejak 1998</h3>
        <p>
            Setiap kue kering <strong>{{ $product->name }}</strong> dipanggang menggunakan resep tradisional turun-temurun dengan bahan mentega, tepung, dan keju terbaik. Teksturnya renyah namun lembut saat digigit, dengan aroma panggangan yang menggugah selera.
        </p>
        <p>
            Produk kami sangat diminati sebagai sajian hari raya, hidangan keluarga, maupun stok jualan toko dan reseller oleh karena rasanya yang disukai semua kalangan.
        </p>
    </div>

    <div class="detail-tab-pane" id="tab-pane-reseller">
        <h3 style="margin-top:0; color:var(--brown-dark);">Informasi Reseller, Grosir & Ekspedisi</h3>
        <ul style="padding-left: 20px; margin: 10px 0;">
            <li><strong>Sistem Pembayaran:</strong> Mendukung pembayaran Tunai/Transfer Lunas maupun pembayaran DP (Uang Muka) untuk pesanan terencana.</li>
            <li><strong>Pengemasan Khusus:</strong> Setiap pesanan bal dilindungi kardus ekstra tebal dan lapisan bubble wrap berlapis untuk meminimalisir risiko kue hancur dalam perjalanan.</li>
            <li><strong>Pengiriman:</strong> Melayani pengiriman dalam kota via kurir instan dan luar kota/antar provinsi via ekspedisi kargo terpercaya.</li>
        </ul>
    </div>

    <div class="detail-tab-pane" id="tab-pane-storage">
        <h3 style="margin-top:0; color:var(--brown-dark);">Petunjuk Menjaga Kerenyahan Kue</h3>
        <p>
            Untuk mempertahankan kerenyahan dan kualitas rasa terbaik kue kering Lintang Bakery:
        </p>
        <ol style="padding-left: 20px; margin: 10px 0;">
            <li>Simpan di tempat yang sejuk, kering, dan terhindar dari paparan sinar matahari langsung.</li>
            <li>Pastikan toples tertutup rapat setiap kali selesai dikonsumsi agar udara lembap tidak masuk.</li>
            <li>Tidak disarankan menyimpan kue di dalam kulkas kecuali telah ditutup kedap udara secara sempurna.</li>
        </ol>
    </div>
</section>

{{-- Rekomendasi Produk Pilihan Lainnya --}}
@if (isset($relatedProducts) && $relatedProducts->count() > 0)
<section class="related-products-section">
    <div class="section-head">
        <div>
            <span class="eyebrow">REKOMENDASI KELUARGA</span>
            <h2>Produk pilihan lainnya</h2>
        </div>
        <a class="text-link" href="{{ route('store.products') }}">Lihat semua produk →</a>
    </div>

    <div class="product-grid">
        @foreach ($relatedProducts as $related)
            @include('store.partials.product-card', ['product' => $related])
        @endforeach
    </div>
</section>
@endif

<script>
const unitPrice = {{ (float) $product->price_per_bal }};
const maxStock = {{ (int) $product->stock }};

function stepQty(delta) {
    const input = document.getElementById('detailQty');
    let current = parseInt(input.value, 10) || 1;
    let next = current + delta;
    if (next < 1) next = 1;
    if (maxStock > 0 && next > maxStock) next = maxStock;
    input.value = next;
    updateSubtotal();
}

function updateSubtotal() {
    const input = document.getElementById('detailQty');
    let qty = parseInt(input.value, 10) || 1;
    if (qty < 1) qty = 1;
    if (maxStock > 0 && qty > maxStock) {
        qty = maxStock;
        input.value = maxStock;
    }
    const total = qty * unitPrice;
    document.getElementById('detailSubtotal').textContent = 'Rp ' + total.toLocaleString('id-ID');
}

function switchDetailTab(tabName, btn) {
    document.querySelectorAll('.detail-tab-btn').forEach(b => b.classList.remove('active'));
    document.querySelectorAll('.detail-tab-pane').forEach(p => p.classList.remove('active'));

    btn.classList.add('active');
    const pane = document.getElementById('tab-pane-' + tabName);
    if (pane) pane.classList.add('active');
}
</script>
@endsection
