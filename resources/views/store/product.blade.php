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
            <img src="{{ $product->image ?: '/images/products/kueTambang.jpg' }}" alt="{{ $product->name }}">
        </div>
        
        <div class="trust-badges-grid">
            <div class="trust-badge-item">
                <span class="trust-badge-icon">
                    <svg style="width:18px;height:18px;stroke:currentColor;fill:none;stroke-width:2;" viewBox="0 0 24 24"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                </span>
                <div class="trust-badge-text">
                    <strong>Bahan Alami Pilihan</strong>
                    <span>Resep tradisional & higienis</span>
                </div>
            </div>
            <div class="trust-badge-item">
                <span class="trust-badge-icon">
                    <svg style="width:18px;height:18px;stroke:currentColor;fill:none;stroke-width:2;" viewBox="0 0 24 24"><path d="M13 2 3 14h9l-1 8 10-12h-9l1-8z"/></svg>
                </span>
                <div class="trust-badge-text">
                    <strong>Produksi Baru</strong>
                    <span>Renyah tahan hingga 6 bulan</span>
                </div>
            </div>
            <div class="trust-badge-item">
                <span class="trust-badge-icon">
                    <svg style="width:18px;height:18px;stroke:currentColor;fill:none;stroke-width:2;" viewBox="0 0 24 24"><path d="m7.5 4.27 9 5.15"/><path d="M21 8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16Z"/><path d="m3.3 7 8.7 5 8.7-5"/><path d="M12 22V12"/></svg>
                </span>
                <div class="trust-badge-text">
                    <strong>Packing Kardus Tebal</strong>
                    <span>Aman ekspedisi kargo & luar kota</span>
                </div>
            </div>
            <div class="trust-badge-item">
                <span class="trust-badge-icon">
                    <svg style="width:18px;height:18px;stroke:currentColor;fill:none;stroke-width:2;" viewBox="0 0 24 24"><path d="M14 18V6a2 2 0 0 0-2-2H4a2 2 0 0 0-2 2v11a1 1 0 0 0 1 1h2"/><path d="M15 18H9"/><path d="M19 18h2a1 1 0 0 0 1-1v-3.65a1 1 0 0 0-.22-.624l-3.48-4.35A1 1 0 0 0 17.52 8H14"/><circle cx="17" cy="18.5" r="2.5"/><circle cx="7" cy="18.5" r="2.5"/></svg>
                </span>
                <div class="trust-badge-text">
                    <strong>Kirim se-Indonesia</strong>
                    <span>Tersedia kargo darat & laut</span>
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
                    <svg style="width:13px;height:13px;stroke:currentColor;fill:none;stroke-width:2;" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
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
            <span class="detail-price-note">Harga grosir per bal (khusus reseller untuk toko & repack)</span>
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
                <strong style="color: #92400e;">Min. 50 Bal (Bisa Mix 50/50, Total 100 Bal)</strong>
            </div>
            <div class="detail-spec-item">
                <span>Masa Simpan</span>
                <strong>Hingga 6 Bulan</strong>
            </div>
            <div class="detail-spec-item">
                <span>Kondisi Kemasan</span>
                <strong>Kardus / Bal Rapi</strong>
            </div>
        </div>

        {{-- Box Formulir Pembelian --}}
        <div class="detail-purchase-box">
            <form method="post" action="{{ route('cart.store', $product) }}" id="productPurchaseForm">
                @csrf
                <div class="stepper-wrap">
                    <div style="display:flex; justify-content:space-between; align-items:center; width:100%; margin-bottom:8px;">
                        <span class="stepper-label" style="margin:0;">Jumlah Pembelian (bal):</span>
                        <span class="reseller-pill">Bisa Mix 50/50</span>
                    </div>

                    <div class="stepper-control" style="width: 100%;">
                        <button type="button" class="stepper-btn" onclick="stepQty(-10)" title="Kurangi 10 bal">−10</button>
                        <button type="button" class="stepper-btn" onclick="stepQty(-1)" title="Kurangi 1 bal">−</button>
                        <input type="number" class="stepper-input" id="detailQty" name="quantity" value="50" min="50" max="{{ max(50, $product->stock) }}" oninput="updateSubtotal()" style="font-size:16px; font-weight:700;">
                        <button type="button" class="stepper-btn" onclick="stepQty(1)" title="Tambah 1 bal">+</button>
                        <button type="button" class="stepper-btn" onclick="stepQty(10)" title="Tambah 10 bal">+10</button>
                    </div>

                    {{-- Preset cepat --}}
                    <div style="display: flex; gap: 6px; margin-top: 10px; width: 100%; flex-wrap: wrap;">
                        <span style="font-size: 11px; color: var(--muted); align-self: center; margin-right: 4px;">Pilih cepat:</span>
                        <button type="button" class="qty-preset-btn active" onclick="setPresetQty(50)">50 bal (Mix 50/50)</button>
                        <button type="button" class="qty-preset-btn" onclick="setPresetQty(100)">100 bal (1 Produk)</button>
                        <button type="button" class="qty-preset-btn" onclick="setPresetQty(150)">150 bal</button>
                        <button type="button" class="qty-preset-btn" onclick="setPresetQty(200)">200 bal</button>
                    </div>
                </div>

                <div class="subtotal-preview" style="margin-top: 14px;">
                    <span>Subtotal Produk:</span>
                    <strong id="detailSubtotal">Rp {{ number_format($product->price_per_bal * 50, 0, ',', '.') }}</strong>
                </div>

                <div class="detail-actions">
                    <button class="button button-full" type="submit" {{ $product->stock <= 0 ? 'disabled' : '' }}>
                        {{ $product->stock <= 0 ? 'Stok Tidak Tersedia' : '+ Tambah ke Keranjang' }}
                    </button>
                </div>
            </form>

            <div class="detail-help-note" style="margin-top: 12px; font-size: 11.5px; color: #855333; line-height: 1.45;">
                <svg style="width:14px;height:14px;display:inline-block;vertical-align:-2px;margin-right:4px;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>
                Minimal order online total <strong>100 bal</strong>. Bisa di-mix minimal <strong>50 bal per varian</strong> (contoh: 50 bal produk ini + 50 bal produk lain = 100 bal).
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
            Setiap produk <strong>{{ $product->name }}</strong> diolah menggunakan resep tradisional pilihan dengan bahan-bahan bermutu tinggi dan proses penggorengan yang higienis. Menghasilkan tekstur renyah garing, gurih, dan awet tanpa bahan pengawet berbahaya.
        </p>
        <p>
            Sangat cocok dijadikan stok jualan bagi reseller toko kelontong, pusat oleh-oleh, warung camilan, toples lebaran, maupun dikemas ulang (repack) menjadi kemasan retail siap saji.
        </p>
    </div>

    <div class="detail-tab-pane" id="tab-pane-reseller">
        <h3 style="margin-top:0; color:var(--brown-dark);">Ketentuan Khusus Pembelian Reseller & Mix Produk</h3>
        <ul style="padding-left: 20px; margin: 10px 0; line-height: 1.8;">
            <li><strong>Minimal Pembelian & Mix 50/50:</strong> Pemesanan online dikhususkan untuk reseller dengan <strong>minimal total checkout 100 bal</strong>. Pembeli bebas me-mix aneka produk camilan & kue dengan ketentuan <strong>minimal 50 bal per varian produk</strong> (misal 50 bal Kue Tambang + 50 bal Kue Keciput).</li>
            <li><strong>Sistem Pembayaran Fleksibel:</strong> Mendukung pembayaran <strong>Lunas di Muka</strong> maupun <strong>Uang Muka 65% (DP)</strong> dengan pelunasan saat pesanan siap kirim. Tersedia QRIS Dinamis dan Transfer Bank resmi (BCA, BRI, Mandiri).</li>
            <li><strong>Pengemasan Standar Kargo:</strong> Setiap pesanan bal dilindungi kardus ekstra tebal dan lapisan pelindung untuk menjaga kerenyahan dan meminimalisir kerusakan saat perjalanan kargo.</li>
            <li><strong>Jangkauan Kirim:</strong> Melayani pengiriman ke seluruh kota dan kabupaten di Indonesia melalui ekspedisi kargo rekanan terpercaya.</li>
        </ul>
    </div>

    <div class="detail-tab-pane" id="tab-pane-storage">
        <h3 style="margin-top:0; color:var(--brown-dark);">Petunjuk Menjaga Kerenyahan Produk</h3>
        <p>
            Untuk mempertahankan kerenyahan dan cita rasa terbaik produk camilan Lintang Bakery:
        </p>
        <ol style="padding-left: 20px; margin: 10px 0; line-height: 1.8;">
            <li>Simpan di tempat yang sejuk, kering, dan tidak terkena sinar matahari langsung.</li>
            <li>Pastikan wadah atau plastik bal tertutup rapat kedap udara setelah dibuka.</li>
            <li>Masa simpan produk dapat mencapai hingga 6 bulan dalam kondisi kemasan tertutup rapat.</li>
        </ol>
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
    updateSubtotal();
}

function setPresetQty(qty) {
    const input = document.getElementById('detailQty');
    input.value = qty;
    document.querySelectorAll('.qty-preset-btn').forEach(b => {
        b.classList.toggle('active', parseInt(b.textContent, 10) === qty);
    });
    updateSubtotal();
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
