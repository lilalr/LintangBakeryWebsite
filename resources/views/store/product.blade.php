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
    {{-- Kolom Kiri: Galeri Foto & Poin Kualitas --}}
    <div class="detail-gallery">
        <div class="detail-image-box">
            <img src="{{ $product->image ?: '/images/products/kueTambang.jpg' }}" alt="{{ $product->name }}">
        </div>

        <div class="detail-feature-chips">
            <span class="feature-chip-item">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                Resep Tradisional Pilihan
            </span>
            <span class="feature-chip-item">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                Renyah Tahan 6 Bulan
            </span>
            <span class="feature-chip-item">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M21 8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16Z"/></svg>
                Packing Kardus Aman
            </span>
        </div>
    </div>

    {{-- Kolom Kanan: Informasi Produk & Form Pembelian --}}
    <div class="detail-info">
        <div class="detail-top-badges">
            <span class="category-tag">{{ $product->category }}</span>
            @if ($product->stock > 10)
                <span class="stock-tag in-stock">✓ Stok Tersedia ({{ $product->stock }} bal)</span>
            @elseif ($product->stock > 0)
                <span class="stock-tag low-stock">⚠ Sisa {{ $product->stock }} bal</span>
            @else
                <span class="stock-tag out-of-stock">✕ Stok Habis</span>
            @endif
        </div>

        <h1 class="detail-title">{{ $product->name }}</h1>

        <div class="detail-price-wrap">
            <span class="price-amount">Rp {{ number_format($product->price_per_bal, 0, ',', '.') }}</span>
            <span class="price-unit">/ bal (grosir reseller)</span>
        </div>

        <p class="detail-desc">{{ $product->description }}</p>

        {{-- Ringkasan Ketentuan Reseller --}}
        <div class="reseller-rule-card">
            <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>
            <div>
                <strong>Ketentuan Grosir Reseller:</strong>
                <span>Minimal total checkout toko <b>100 bal</b>. Bebas mix aneka produk dengan minimal <b>50 bal per varian</b>.</span>
            </div>
        </div>

        {{-- Form Pembelian Sederhana & Rapi --}}
        <form method="post" action="{{ route('cart.store', $product) }}" id="productPurchaseForm" class="buy-action-card">
            @csrf
            
            <div class="qty-section-head">
                <label for="detailQty">Jumlah Pembelian (bal):</label>
                <span class="qty-pill-note">Bisa Mix 50/50</span>
            </div>

            {{-- Stepper Control --}}
            <div style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
                <div class="stepper-unified">
                    <button type="button" class="step-btn" onclick="stepQty(-10)" title="Kurangi 10 bal">−10</button>
                    <button type="button" class="step-btn" onclick="stepQty(-1)" title="Kurangi 1 bal">−</button>
                    <input type="number" id="detailQty" name="quantity" value="50" min="50" max="{{ max(50, $product->stock) }}" oninput="updateSubtotal()" class="qty-num-input" aria-label="Jumlah bal">
                    <button type="button" class="step-btn" onclick="stepQty(1)" title="Tambah 1 bal">+</button>
                    <button type="button" class="step-btn" onclick="stepQty(10)" title="Tambah 10 bal">+10</button>
                </div>
            </div>

            {{-- Pilihan Preset Cepat --}}
            <div class="preset-chips-row">
                <span class="chips-label">Pilih cepat:</span>
                <button type="button" class="preset-chip active" onclick="setPresetQty(50)">50 bal</button>
                <button type="button" class="preset-chip" onclick="setPresetQty(100)">100 bal</button>
                <button type="button" class="preset-chip" onclick="setPresetQty(150)">150 bal</button>
                <button type="button" class="preset-chip" onclick="setPresetQty(200)">200 bal</button>
            </div>

            {{-- Subtotal & CTA Button --}}
            <div class="subtotal-bar">
                <span class="subtotal-label">Subtotal Produk:</span>
                <span class="subtotal-val" id="detailSubtotal">Rp {{ number_format($product->price_per_bal * 50, 0, ',', '.') }}</span>
            </div>

            <button type="submit" class="button buy-now-btn" {{ $product->stock <= 0 ? 'disabled' : '' }}>
                @if ($product->stock <= 0)
                    Stok Tidak Tersedia
                @else
                    <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3"><path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4Z"/><path d="M3 6h18"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>
                    Tambah ke Keranjang
                @endif
            </button>
        </form>
    </div>
</section>

{{-- Section Tab Informasi Tambahan (Sederhana & Rapi) --}}
<section class="detail-tabs-section">
    <div class="detail-tabs-nav">
        <button type="button" class="detail-tab-btn active" onclick="switchDetailTab('desc', this)">Deskripsi & Kualitas</button>
        <button type="button" class="detail-tab-btn" onclick="switchDetailTab('reseller', this)">Ketentuan Reseller & Pengiriman</button>
    </div>

    <div class="detail-tab-pane active" id="tab-pane-desc">
        <h3 style="margin-top:0; color:var(--brown-dark); font-size:16px;">Kelezatan Khas Lintang Bakery Sejak 1998</h3>
        <p>
            Setiap produk <strong>{{ $product->name }}</strong> diolah menggunakan resep tradisional pilihan dengan bahan bermutu tinggi dan higienis. Menghasilkan tekstur renyah garing, gurih, dan awet hingga 6 bulan tanpa bahan pengawet berbahaya.
        </p>
        <p style="margin-bottom:0;">
            Sangat cocok dijadikan stok jualan bagi reseller toko kelontong, pusat oleh-oleh, warung camilan, toples lebaran, maupun dikemas ulang (repack) menjadi kemasan retail siap saji.
        </p>
    </div>

    <div class="detail-tab-pane" id="tab-pane-reseller">
        <h3 style="margin-top:0; color:var(--brown-dark); font-size:16px;">Ketentuan Khusus Pembelian Reseller & Pengiriman</h3>
        <ul style="padding-left: 20px; margin: 8px 0 0; line-height: 1.8;">
            <li><strong>Minimal Pembelian & Mix 50/50:</strong> Pemesanan online khusus reseller dengan <strong>minimal total checkout 100 bal</strong>. Pembeli bebas me-mix aneka varian produk dengan ketentuan <strong>minimal 50 bal per varian</strong> (contoh: 50 bal {{ $product->name }} + 50 bal produk lain = 100 bal).</li>
            <li><strong>Sistem Pembayaran Fleksibel:</strong> Mendukung pembayaran <strong>Lunas di Muka</strong> maupun <strong>Uang Muka 65% (DP)</strong> dengan pelunasan saat pesanan siap kirim. Tersedia QRIS Dinamis dan Transfer Bank resmi (BCA, BRI, Mandiri).</li>
            <li><strong>Pengemasan Standar Kargo:</strong> Setiap pesanan bal dilindungi kardus ekstra tebal untuk menjaga kerenyahan dan meminimalisir risiko benturan di ekspedisi kargo.</li>
        </ul>
    </div>
</section>

{{-- Rekomendasi Produk Pilihan Lainnya --}}
@if (isset($relatedProducts) && $relatedProducts->count() > 0)
<section class="related-products-section">
    <div class="section-head">
        <div>
            <span class="eyebrow">REKOMENDASI LAINNYA</span>
            <h2>Produk camilan pilihan lainnya</h2>
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
    let current = parseInt(input.value, 10) || 50;
    let next = current + delta;
    if (next < 50) next = 50;
    if (maxStock > 0 && next > maxStock) next = maxStock;
    input.value = next;
    highlightPreset(next);
    updateSubtotal();
}

function setPresetQty(qty) {
    const input = document.getElementById('detailQty');
    input.value = qty;
    highlightPreset(qty);
    updateSubtotal();
}

function highlightPreset(qty) {
    document.querySelectorAll('.preset-chip').forEach(b => {
        b.classList.toggle('active', parseInt(b.textContent, 10) === qty);
    });
}

function updateSubtotal() {
    const input = document.getElementById('detailQty');
    let qty = parseInt(input.value, 10) || 50;
    if (qty < 50) qty = 50;
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

document.addEventListener('DOMContentLoaded', function() {
    updateSubtotal();
});
</script>
@endsection
