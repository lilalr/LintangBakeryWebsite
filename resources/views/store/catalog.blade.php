@extends('layouts.store')

@section('title', 'Katalog Camilan & Kue Kering Grosir · Lintang Bakery')

@section('content')
<div class="breadcrumbs">
    <a href="{{ route('store.home') }}">Beranda</a>
    <span>/</span>
    <span>Produk</span>
</div>

<div class="page-heading" style="padding-top:10px; padding-bottom:20px;">
    <div style="display:flex; justify-content:space-between; align-items:flex-end; flex-wrap:wrap; gap:20px;">
        <div>
            <span class="eyebrow">KATALOG GROSIR RESELLER</span>
            <h1 style="margin:6px 0 10px;">Semua Produk Camilan & Kue Kering</h1>
            <p style="color:var(--muted); font-size:14px; max-width:580px;">
                Camilan renyah dan kue kering khas Lintang Bakery dalam kemasan bal grosir. Khusus pemesanan online mitra reseller dengan minimal total <strong>100 bal</strong> (bisa di-mix minimal <strong>50 bal per varian</strong>, contoh 50/50).
            </p>
        </div>
        <div style="min-width:280px; flex:1; max-width:360px;">
            <input type="text" id="catalogSearch" placeholder="Cari nama camilan / kue..." style="width:100%; border-radius:99px; padding:10px 18px; border:1px solid var(--line); font-size:13px;" onkeyup="filterCatalog()">
        </div>
    </div>
</div>

<div class="product-grid" id="catalogGrid" style="margin-top:20px;">
    @forelse ($products as $product)
        @include('store.partials.product-card', ['product' => $product])
    @empty
        <div class="empty-state">Belum ada produk yang tersedia saat ini.</div>
    @endforelse
</div>

<div id="noSearchMatch" class="empty-state" style="display:none; margin-top:30px;">
    <h3>Tidak ada produk yang cocok</h3>
    <p>Coba gunakan kata kunci pencarian lainnya.</p>
</div>

<section class="feature-strip" style="margin-top:60px;">
    <div><strong>01</strong><h3>Bahan Berkualitas</h3><p>Renyah gurih tahan lama, dibuat higienis dengan resep tradisional pilihan.</p></div>
    <div><strong>02</strong><h3>Bisa Mix 50/50</h3><p>Minimal order total 100 bal, bisa campur minimal 50 bal per varian produk.</p></div>
    <div><strong>03</strong><h3>Kemasan Ekstra Aman</h3><p>Dilengkapi kardus tebal dan standar packing kargo agar produk tetap utuh saat sampai.</p></div>
</section>

<script>
function filterCatalog() {
    const q = document.getElementById('catalogSearch').value.toLowerCase().trim();
    const cards = document.querySelectorAll('#catalogGrid .product-card');
    let visibleCount = 0;

    cards.forEach(card => {
        const title = card.querySelector('h3').textContent.toLowerCase();
        const desc = card.querySelector('p').textContent.toLowerCase();
        if (title.includes(q) || desc.includes(q)) {
            card.style.display = '';
            visibleCount++;
        } else {
            card.style.display = 'none';
        }
    });

    const noMatch = document.getElementById('noSearchMatch');
    if (noMatch) {
        noMatch.style.display = (visibleCount === 0) ? 'block' : 'none';
    }
}
</script>
@endsection
