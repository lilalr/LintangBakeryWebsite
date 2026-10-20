@extends('layouts.admin')

@section('title', 'Manajemen Stok')
@section('section', 'Stok')

@section('content')
<div class="content-heading">
    <div>
        <span class="eyebrow">INVENTORI & PERSEDIAAN</span>
        <h1>Manajemen stok</h1>
        <p>Catat barang masuk produksi, barang keluar manual, serta pantau kartu stok dan batas kedaluwarsa.</p>
    </div>
</div>

@if ($lowStockProducts->isNotEmpty())
    <div class="alert alert-warning">
        <strong>Peringatan Stok Menipis:</strong>
        {{ $lowStockProducts->count() }} produk berada pada atau di bawah batas minimum:
        {{ $lowStockProducts->pluck('name')->join(', ') }}. Segera rencanakan produksi.
    </div>
@endif

@if ($expiringMutations->isNotEmpty())
    <div class="alert alert-error">
        <strong>Peringatan Kedaluwarsa (&le; 14 Hari):</strong>
        @foreach ($expiringMutations as $em)
            <span>{{ $em->product?->name }} ({{ $em->quantity }} bal, Exp: {{ $em->expiry_date?->format('d/m/Y') }}){{ ! $loop->last ? ', ' : '' }}</span>
        @endforeach
    </div>
@endif

{{-- Tab Navigasi Stok --}}
<nav class="report-tabs" id="stockTabs">
    <button class="report-tab active" data-tab="status" type="button">Status Stok</button>
    <button class="report-tab" data-tab="masuk" type="button">Barang Masuk</button>
    <button class="report-tab" data-tab="keluar" type="button">Barang Keluar Manual</button>
    <button class="report-tab" data-tab="kartu" type="button">Riwayat Keluar Masuk</button>
</nav>

{{-- TAB 1: STATUS STOK PRODUK --}}
<div class="stock-tab-content active" id="tab-status">
    <div class="stats-grid">
        <article class="stat-card">
            <span>Total Produk</span>
            <strong>{{ $products->count() }}</strong>
            <small>varian terdaftar</small>
        </article>
        <article class="stat-card">
            <span>Stok Menipis</span>
            <strong class="{{ $lowStockProducts->count() > 0 ? 'text-warning' : '' }}">{{ $lowStockProducts->count() }}</strong>
            <small>&le; batas minimum</small>
        </article>
        <article class="stat-card">
            <span>Mendekati Expired</span>
            <strong>{{ $expiringMutations->count() }}</strong>
            <small>batch dalam 14 hari</small>
        </article>
        <article class="stat-card">
            <span>Keluar Non-Penjualan</span>
            <strong>{{ $mutations->where('type', 'out')->where('reason', '!=', 'Penjualan offline')->sum('quantity') }} bal</strong>
            <small>rusak, expired, tester</small>
        </article>
    </div>

    <section class="panel">
        <div class="panel-heading">
            <div>
                <h2>Persediaan produk saat ini</h2>
                <p>Status stok riil di toko fisik dan gudang.</p>
            </div>
        </div>
        <div class="table-scroll">
            <table>
                <thead>
                    <tr>
                        <th>Produk</th>
                        <th>Stok Saat Ini</th>
                        <th>Batas Min.</th>
                        <th>Status</th>
                        <th>Aksi Cepat</th>
                    </tr>
                </thead>
                <tbody>
                @forelse ($products as $product)
                    @php
                        $isOutOfStock = $product->stock <= 0;
                        $isLowStock = $product->stock <= $product->min_stock;
                    @endphp
                    <tr>
                        <td>
                            <div class="table-product">
                                @if ($product->image)
                                    <img src="{{ $product->image }}" alt="{{ $product->name }}">
                                @endif
                                <div>
                                    <strong>{{ $product->name }}</strong>
                                </div>
                            </div>
                        </td>
                        <td>
                            <strong style="font-size: 14px;" class="{{ $isOutOfStock ? 'text-danger' : ($isLowStock ? 'text-warning' : '') }}">
                                {{ $product->stock }} bal
                            </strong>
                        </td>
                        <td>{{ $product->min_stock }} bal</td>
                        <td>
                            @if ($isOutOfStock)
                                <span class="role-pill" style="background:#fde8e8; color:#9c1c1c;">Habis</span>
                            @elseif ($isLowStock)
                                <span class="role-pill" style="background:#fff3cd; color:#856404;">Menipis</span>
                            @else
                                <span class="role-pill" style="background:#e6f4ea; color:#137333;">Aman</span>
                            @endif
                        </td>
                        <td>
                            <div class="action-row">
                                <button type="button" class="button button-small button-light quick-in-btn" data-product-id="{{ $product->id }}" data-product-name="{{ $product->name }}">
                                    + Masuk
                                </button>
                                <button type="button" class="button button-small button-light quick-out-btn" data-product-id="{{ $product->id }}" data-product-name="{{ $product->name }}" data-stock="{{ $product->stock }}">
                                    - Keluar
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="table-empty">Belum ada produk terdaftar.</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </section>
</div>

{{-- TAB 2: BARANG MASUK (PROSES 1) --}}
<div class="stock-tab-content" id="tab-masuk">
    <section class="form-card admin-form-card">
        <h2>Catat Barang Masuk</h2>
        <p class="muted" style="margin-top:-10px; margin-bottom:18px;">Gunakan formulir ini setelah produksi dapur selesai atau saat menerima pasokan barang.</p>
        <form method="post" action="{{ route('admin.stock.inbound') }}" class="stack-form">
            @csrf
            <div class="form-row">
                <label>
                    Tanggal Masuk
                    <input type="date" name="mutation_date" value="{{ old('mutation_date', date('Y-m-d')) }}" required>
                </label>
                <label>
                    Produk
                    <select name="product_id" id="inbound_product_id" required>
                        <option value="">-- Pilih Produk --</option>
                        @foreach ($products as $p)
                            <option value="{{ $p->id }}" @selected(old('product_id') == $p->id)>{{ $p->name }} (Stok saat ini: {{ $p->stock }} bal)</option>
                        @endforeach
                    </select>
                </label>
            </div>

            <div class="form-row">
                <label>
                    Jumlah Masuk (bal)
                    <input type="number" name="quantity" min="1" max="100000" value="{{ old('quantity') }}" placeholder="Contoh: 50" required>
                </label>
                <label>
                    Sumber Barang
                    <input type="text" name="source" value="{{ old('source', 'Dapur Produksi') }}" placeholder="Contoh: Dapur Produksi, Supplier A" required>
                </label>
            </div>

            <div class="form-row">
                <label>
                    Tanggal Kedaluwarsa (opsional)
                    <input type="date" name="expiry_date" value="{{ old('expiry_date') }}">
                    <small class="muted">Jika diisi, sistem memberi peringatan 14 hari sebelum tanggal ini.</small>
                </label>
                <label>
                    Catatan / Nomor Batch (opsional)
                    <input type="text" name="notes" value="{{ old('notes') }}" placeholder="Contoh: Batch pagi oven 1, resep baru">
                </label>
            </div>

            <button class="button" type="submit">Simpan Barang Masuk</button>
        </form>
    </section>
</div>

{{-- TAB 3: BARANG KELUAR MANUAL (PROSES 3) --}}
<div class="stock-tab-content" id="tab-keluar">
    <section class="form-card admin-form-card">
        <h2>Catat Barang Keluar Manual</h2>
        <p class="muted" style="margin-top:-10px; margin-bottom:18px;">Gunakan formulir ini untuk mencatat stok yang berkurang di luar penjualan (seperti toples pecah, kue remuk, kedaluwarsa, atau sampel tester).</p>

        <form method="post" action="{{ route('admin.stock.outbound') }}" class="stack-form">
            @csrf
            <div class="form-row">
                <label>
                    Tanggal Keluar
                    <input type="date" name="mutation_date" value="{{ old('mutation_date', date('Y-m-d')) }}" required>
                </label>
                <label>
                    Produk
                    <select name="product_id" id="outbound_product_id" required>
                        <option value="">-- Pilih Produk --</option>
                        @foreach ($products as $p)
                            <option value="{{ $p->id }}" data-stock="{{ $p->stock }}" @selected(old('product_id') == $p->id)>
                                {{ $p->name }} (Tersedia: {{ $p->stock }} bal)
                            </option>
                        @endforeach
                    </select>
                </label>
            </div>

            <div class="form-row">
                <label>
                    Jumlah Keluar (bal)
                    <input type="number" name="quantity" id="outbound_quantity" min="1" max="100000" value="{{ old('quantity') }}" placeholder="Contoh: 2" required>
                    <small class="muted" id="outbound_stock_hint">Pastikan tidak melebihi stok yang ada.</small>
                </label>
                <label>
                    Alasan Pengeluaran
                    <select name="reason" required>
                        <option value="Rusak" @selected(old('reason') === 'Rusak')>Rusak (Pecah toples, remuk, cacat oven)</option>
                        <option value="Kedaluwarsa" @selected(old('reason') === 'Kedaluwarsa')>Kedaluwarsa / Basi</option>
                        <option value="Sampel/Tester" @selected(old('reason') === 'Sampel/Tester')>Sampel / Tester Pembeli</option>
                        <option value="Koreksi Stok" @selected(old('reason') === 'Koreksi Stok')>Koreksi Selisih Stok Opname</option>
                        <option value="Lainnya" @selected(old('reason') === 'Lainnya')>Lainnya</option>
                    </select>
                </label>
            </div>

            <label>
                Keterangan / Catatan Tambahan
                <textarea name="notes" rows="3" placeholder="Tuliskan keterangan detail mengapa barang ini dikeluarkan...">{{ old('notes') }}</textarea>
            </label>

            <button class="button button-danger" type="submit">Simpan Barang Keluar</button>
        </form>
    </section>
</div>

{{-- TAB 4: KARTU STOK / RIWAYAT MUTASI (PROSES 4) --}}
<div class="stock-tab-content" id="tab-kartu">
    {{-- Filter Kartu Stok --}}
    <section class="report-filter-bar">
        <form method="get" action="{{ route('admin.stock.index') }}" class="report-filter-inline">
            <input type="hidden" name="tab" value="kartu">
            <div class="filter-dates" style="flex-wrap: wrap;">
                <label style="min-width: 180px;">
                    <span>Produk</span>
                    <select name="product_id">
                        <option value="">Semua Produk</option>
                        @foreach ($products as $p)
                            <option value="{{ $p->id }}" @selected($selectedProductId == $p->id)>{{ $p->name }}</option>
                        @endforeach
                    </select>
                </label>
                <label style="min-width: 130px;">
                    <span>Jenis Mutasi</span>
                    <select name="type">
                        <option value="">Semua</option>
                        <option value="in" @selected($selectedType === 'in')>Masuk (+)</option>
                        <option value="out" @selected($selectedType === 'out')>Keluar (-)</option>
                    </select>
                </label>
                <label style="min-width: 130px;">
                    <span>Dari Tanggal</span>
                    <input type="date" name="start_date" value="{{ $startDate }}">
                </label>
                <label style="min-width: 130px;">
                    <span>Sampai Tanggal</span>
                    <input type="date" name="end_date" value="{{ $endDate }}">
                </label>
            </div>
            <button class="button" type="submit">Terapkan Filter</button>
            @if ($selectedProductId || $selectedType || $startDate || $endDate)
                <a href="{{ route('admin.stock.index', ['tab' => 'kartu']) }}" class="button button-light">Reset</a>
            @endif
        </form>
    </section>

    <section class="panel">
        <div class="panel-heading">
            <div>
                <h2>Riwayat Keluar Masuk Barang</h2>
                <p>Catatan alur barang saat masuk dari produksi atau keluar saat ada penjualan.</p>
            </div>
        </div>
        <div class="table-scroll">
            <table>
                <thead>
                    <tr>
                        <th style="width: 20%;">Waktu & Referensi</th>
                        <th style="width: 26%;">Produk</th>
                        <th style="width: 18%;">Perubahan Stok</th>
                        <th style="width: 14%;">Sisa Stok</th>
                        <th style="width: 22%;">Keterangan & Sumber</th>
                    </tr>
                </thead>
                <tbody>
                @forelse ($mutations as $m)
                    <tr>
                        <td>
                            <strong>{{ $m->mutation_date ? $m->mutation_date->format('d/m/Y') : $m->created_at->format('d/m/Y') }}</strong>
                            <small class="muted" style="display:block;">{{ $m->created_at->format('H:i') }} WIB</small>
                            @if ($m->reference_number)
                                <code style="font-size: 10.5px; background: #f5ece1; padding: 2px 5px; border-radius: 4px; display: inline-block; margin-top: 3px;">{{ $m->reference_number }}</code>
                            @endif
                        </td>
                        <td>
                            <strong style="color: #2b1d16; font-size: 13.5px;">{{ $m->product?->name ?? 'Produk dihapus' }}</strong>
                        </td>
                        <td>
                            @if ($m->type === 'in')
                                <span class="role-pill" style="background: #e6f4ea; color: #137333; font-weight: 700; font-size: 12px; display: inline-flex; align-items: center; gap: 4px;">
                                    <span>+ Masuk {{ $m->quantity }} bal</span>
                                </span>
                            @else
                                <span class="role-pill" style="background: #fde8e8; color: #9c1c1c; font-weight: 700; font-size: 12px; display: inline-flex; align-items: center; gap: 4px;">
                                    <span>- Keluar {{ $m->quantity }} bal</span>
                                </span>
                            @endif
                        </td>
                        <td>
                            <strong style="font-size: 14px; color: #2b1d16;">{{ $m->balance }} bal</strong>
                        </td>
                        <td>
                            <strong style="display:block; font-size: 12px; color: #3b2d23;">{{ $m->reason }}</strong>
                            @if ($m->source)
                                <small class="muted" style="display:block;">Sumber: {{ $m->source }}</small>
                            @endif
                            @if ($m->notes)
                                <small class="muted" style="display:block;">Catatan: {{ $m->notes }}</small>
                            @endif
                            @if ($m->expiry_date)
                                <small style="display:block; color: #b35900; font-weight: 600;">Exp: {{ $m->expiry_date->format('d/m/Y') }}</small>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="table-empty">Belum ada riwayat keluar masuk barang.</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
        @if ($mutations->hasPages())
            <div class="pagination-wrap">
                {{ $mutations->links() }}
            </div>
        @endif
    </section>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const tabs = document.querySelectorAll('#stockTabs .report-tab');
    const contents = document.querySelectorAll('.stock-tab-content');

    function activateTab(tabKey) {
        tabs.forEach(t => t.classList.toggle('active', t.dataset.tab === tabKey));
        contents.forEach(c => c.classList.toggle('active', c.id === 'tab-' + tabKey));
    }

    tabs.forEach(tab => {
        tab.addEventListener('click', function() {
            activateTab(this.dataset.tab);
        });
    });

    // Cek query parameter ?tab=kartu
    const urlParams = new URLSearchParams(window.location.search);
    const initialTab = urlParams.get('tab');
    if (initialTab && document.getElementById('tab-' + initialTab)) {
        activateTab(initialTab);
    }

    // Quick buttons: + Masuk & - Keluar
    document.querySelectorAll('.quick-in-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const pid = this.dataset.productId;
            const select = document.getElementById('inbound_product_id');
            if (select) select.value = pid;
            activateTab('masuk');
        });
    });

    document.querySelectorAll('.quick-out-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const pid = this.dataset.productId;
            const select = document.getElementById('outbound_product_id');
            if (select) {
                select.value = pid;
                select.dispatchEvent(new Event('change'));
            }
            activateTab('keluar');
        });
    });

    // Outbound max limit hint
    const outboundSelect = document.getElementById('outbound_product_id');
    const outboundQty = document.getElementById('outbound_quantity');
    const stockHint = document.getElementById('outbound_stock_hint');

    if (outboundSelect && outboundQty) {
        outboundSelect.addEventListener('change', function() {
            const opt = this.options[this.selectedIndex];
            const stock = opt.getAttribute('data-stock');
            if (stock !== null) {
                outboundQty.max = stock;
                if (stockHint) stockHint.textContent = 'Maksimal: ' + stock + ' bal.';
            }
        });
    }
});
</script>
@endsection
