@extends('layouts.admin')

@section('title', 'Kasir')
@section('section', 'Kasir')

@section('content')
<div class="content-heading">
    <div>
        <h1>Kasir toko</h1>
        <p>Aplikasi kasir penjualan langsung dengan perhitungan otomatis dan QRIS dinamis.</p>
    </div>
</div>

@php
    $activeTab = request('tab') === 'riwayat' || request()->has('page') ? 'riwayat' : 'kasir';
@endphp

{{-- Tab Navigasi Kasir --}}
<nav class="report-tabs" id="posTabs">
    <button class="report-tab {{ $activeTab === 'kasir' ? 'active' : '' }}" data-tab="kasir" type="button">Kasir</button>
    <button class="report-tab {{ $activeTab === 'riwayat' ? 'active' : '' }}" data-tab="riwayat" type="button">Riwayat Transaksi</button>
</nav>

{{-- TAB 1: KASIR --}}
<div class="pos-tab-content {{ $activeTab === 'kasir' ? 'active' : '' }}" id="tab-kasir">
    <div class="pos-layout">
        {{-- Sisi Kiri: Katalog Produk --}}
        <div class="pos-catalog-panel">
            <div class="pos-search-bar" style="position: relative; display: flex; align-items: center;">
                <svg style="position: absolute; left: 14px; width: 16px; height: 16px; stroke: #9c8e84; fill: none; stroke-width: 2; pointer-events: none;" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
                <input type="text" id="posSearch" class="pos-search-input" style="padding-left: 38px;" placeholder="Cari nama produk bakery...">
            </div>

            <div class="pos-products-grid" id="posProductsList">
                @forelse ($products as $p)
                    <div class="pos-product-card" 
                         data-id="{{ $p->id }}" 
                         data-name="{{ $p->name }}" 
                         data-price="{{ $p->price_per_bal }}" 
                         data-stock="{{ $p->stock }}" 
                         data-category="{{ $p->category }}">
                        <img src="{{ $p->image ?: 'https://images.unsplash.com/photo-1509440159596-0249088772ff?w=400&q=80' }}" 
                             alt="{{ $p->name }}" class="pos-product-img">
                        <div class="pos-product-info">
                            <span class="pos-product-name">{{ $p->name }}</span>
                            <span class="pos-product-stock {{ $p->stock <= 0 ? 'out' : '' }}">
                                {{ $p->stock > 0 ? 'Stok: '.$p->stock.' bal' : 'Stok Habis' }}
                            </span>
                            <span class="pos-product-price">Rp {{ number_format($p->price_per_bal, 0, ',', '.') }}</span>
                        </div>
                        <button type="button" class="pos-add-btn" {{ $p->stock <= 0 ? 'disabled' : '' }}>
                            {{ $p->stock > 0 ? '+ Tambah' : 'Habis' }}
                        </button>
                    </div>
                @empty
                    <div class="report-empty" style="grid-column: 1/-1;">Belum ada produk untuk penjualan kasir.</div>
                @endforelse
            </div>
        </div>

        {{-- Sisi Kanan: Keranjang & Pembayaran --}}
        <div class="pos-cart-panel">
            <div class="pos-cart-header">
                <h3>Keranjang Pesanan</h3>
                <button type="button" id="clearCartBtn" class="link-button" style="color:var(--muted); font-size:12px;">Hapus Semua</button>
            </div>

            <div style="margin-bottom: 12px;">
                <label style="font-size: 11px; font-weight: 700; color: var(--muted); display:block; margin-bottom: 4px;">Nama Pembeli</label>
                <input type="text" id="buyerNameInput" value="Pelanggan Toko" class="pos-search-input" style="min-height: 38px;" placeholder="Nama pembeli...">
            </div>

            {{-- List item keranjang --}}
            <div class="pos-cart-items" id="cartItemsContainer">
                <div class="report-empty" style="padding: 24px 0;" id="emptyCartMessage">
                    <svg style="width: 28px; height: 28px; stroke: #a4968d; fill: none; stroke-width: 1.8; display: block; margin: 0 auto 8px;" viewBox="0 0 24 24"><path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4Z"/><path d="M3 6h18"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>
                    Belum ada produk dipilih.<br><small class="muted">Klik produk di sebelah kiri untuk menambahkan.</small>
                </div>
            </div>

            {{-- Ringkasan --}}
            <div class="pos-cart-summary">
                <div class="pos-summary-row">
                    <span>Subtotal (<span id="cartCount">0</span> item)</span>
                    <strong id="cartSubtotal">Rp 0</strong>
                </div>
                <div class="pos-summary-total">
                    <span>Total Tagihan</span>
                    <strong id="cartTotal" class="text-brown">Rp 0</strong>
                </div>
            </div>

            {{-- Pilihan Metode Pembayaran --}}
            <div style="margin-bottom: 8px;">
                <label style="font-size: 11px; font-weight: 700; color: var(--muted); display:block; margin-bottom: 6px;">Metode Pembayaran</label>
                <div class="pos-payment-methods">
                    <div class="pos-pay-opt active" data-method="cash">
                        <svg class="pay-opt-icon" viewBox="0 0 24 24"><rect width="20" height="12" x="2" y="6" rx="2"/><circle cx="12" cy="12" r="2"/><path d="M6 12h.01M18 12h.01"/></svg>
                        <span>Tunai</span>
                    </div>
                    <div class="pos-pay-opt" data-method="qris">
                        <svg class="pay-opt-icon" viewBox="0 0 24 24"><rect width="5" height="5" x="3" y="3" rx="1"/><rect width="5" height="5" x="16" y="3" rx="1"/><rect width="5" height="5" x="3" y="16" rx="1"/><path d="M21 16h-3a2 2 0 0 0-2 2v3"/><path d="M12 7v3a2 2 0 0 1-2 2H7"/><path d="M3 12h.01"/><path d="M12 3h.01"/><path d="M12 16v.01"/><path d="M16 12h1"/><path d="M21 12v.01"/><path d="M12 21v-1"/></svg>
                        <span>QRIS Dinamis</span>
                    </div>
                    <div class="pos-pay-opt" data-method="edc">
                        <svg class="pay-opt-icon" viewBox="0 0 24 24"><rect width="20" height="14" x="2" y="5" rx="2"/><line x1="2" x2="22" y1="10" y2="10"/></svg>
                        <span>Transfer / EDC</span>
                    </div>
                </div>
            </div>

            {{-- Tombol Bayar Sekarang --}}
            <button type="button" id="payNowBtn" class="button button-full" disabled>
                Pilih Produk Dulu
            </button>
        </div>
    </div>
</div>

{{-- TAB 2: RIWAYAT TRANSAKSI --}}
<div class="pos-tab-content {{ $activeTab === 'riwayat' ? 'active' : '' }}" id="tab-riwayat">
    <section class="panel">
        <div class="panel-heading">
            <div>
                <h2>Riwayat Penjualan Toko</h2>
                <p>{{ $transactions->total() }} transaksi tersimpan di sistem.</p>
            </div>
        </div>
        <div class="table-scroll">
            <table>
                <thead>
                    <tr>
                        <th>Tanggal</th>
                        <th>No. Transaksi</th>
                        <th>Pembeli</th>
                        <th>Item Terjual</th>
                        <th>Pembayaran</th>
                        <th>Total</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                @forelse ($transactions as $transaction)
                    <tr>
                        <td>
                            <strong>{{ $transaction->date->format('d/m/Y') }}</strong>
                            <small class="muted">{{ $transaction->created_at->format('H:i') }} WIB</small>
                        </td>
                        <td>
                            <code style="font-size: 11px; background:#f5ece1; padding:3px 6px; border-radius:4px;">TRX-{{ $transaction->id }}</code>
                        </td>
                        <td>
                            <strong>{{ $transaction->buyer_name }}</strong>
                            @if ($transaction->notes)
                                <small class="muted">{{ $transaction->notes }}</small>
                            @endif
                        </td>
                        <td>
                            @foreach ($transaction->items as $item)
                                <div>{{ $item->name }} &times; {{ $item->quantity }} bal</div>
                            @endforeach
                            <small class="muted">Total {{ $transaction->items->sum('quantity') }} bal</small>
                        </td>
                        <td>
                            @if ($transaction->payment_method === 'cash')
                                <span class="role-pill" style="background:#e6f4ea; color:#137333; display:inline-flex; align-items:center; gap:5px;">
                                    <svg style="width:13px; height:13px; stroke:currentColor; fill:none; stroke-width:2;" viewBox="0 0 24 24"><rect width="20" height="12" x="2" y="6" rx="2"/><circle cx="12" cy="12" r="2"/></svg>
                                    <span>Tunai</span>
                                </span>
                            @elseif (str_starts_with($transaction->payment_method, 'qris'))
                                <span class="role-pill" style="background:#fde8e8; color:#b71c1c; display:inline-flex; align-items:center; gap:5px;">
                                    <svg style="width:13px; height:13px; stroke:currentColor; fill:none; stroke-width:2;" viewBox="0 0 24 24"><rect width="5" height="5" x="3" y="3" rx="1"/><rect width="5" height="5" x="16" y="3" rx="1"/><rect width="5" height="5" x="3" y="16" rx="1"/><path d="M21 16h-3a2 2 0 0 0-2 2v3"/><path d="M12 7v3a2 2 0 0 1-2 2H7"/></svg>
                                    <span>QRIS</span>
                                </span>
                            @else
                                <span class="role-pill" style="background:#e8f0fe; color:#1a73e8; display:inline-flex; align-items:center; gap:5px;">
                                    <svg style="width:13px; height:13px; stroke:currentColor; fill:none; stroke-width:2;" viewBox="0 0 24 24"><rect width="20" height="14" x="2" y="5" rx="2"/><line x1="2" x2="22" y1="10" y2="10"/></svg>
                                    <span>{{ strtoupper($transaction->payment_method) }}</span>
                                </span>
                            @endif
                        </td>
                        <td>
                            <strong style="font-size: 14px;">Rp {{ number_format($transaction->total, 0, ',', '.') }}</strong>
                        </td>
                        <td>
                            <form method="post" action="{{ route('admin.transactions.destroy', $transaction) }}" onsubmit="return confirm('Batalkan (void) transaksi ini? Stok yang terjual akan otomatis dikembalikan ke inventori.')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="button button-small button-danger" style="padding: 4px 8px; font-size: 11px;">
                                    Void (Batal)
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="table-empty">Belum ada riwayat transaksi kasir.</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
        <div class="pagination-wrap">{{ $transactions->appends(['tab' => 'riwayat'])->links() }}</div>
    </section>
</div>

{{-- MODAL 1: BAYAR TUNAI (CASH NUMPAD) --}}
<div class="pos-modal-overlay" id="cashModal">
    <div class="pos-modal-card">
        <div class="pos-modal-header">
            <h3>Bayar Tunai</h3>
            <button type="button" class="pos-close-btn" onclick="closeModals()">&times;</button>
        </div>

        <div class="numpad-display">
            <div class="numpad-box">
                <label>Total Tagihan</label>
                <strong id="modalCashTotal">Rp 0</strong>
            </div>
            <div class="numpad-box" style="background:#f1f8f3; border-color:#bce2c7;">
                <label style="color:#217346;">Kembalian</label>
                <strong id="modalCashChange" style="color:#1b633b;">Rp 0</strong>
            </div>
        </div>

        <div style="margin-bottom: 10px;">
            <label style="font-size: 11px; font-weight:700; color:var(--muted); display:block; margin-bottom:4px;">Uang Diterima (Rp)</label>
            <input type="text" id="receivedCashInput" class="pos-search-input" style="font-size: 20px; font-weight:800; text-align:right;" value="" placeholder="0">
        </div>

        <div class="quick-cash-row">
            <button type="button" class="quick-cash-btn" id="exactCashBtn">Uang Pas</button>
            <button type="button" class="quick-cash-btn quick-add-cash" data-val="50000">Rp 50.000</button>
            <button type="button" class="quick-cash-btn quick-add-cash" data-val="100000">Rp 100.000</button>
        </div>

        <div class="numpad-grid">
            <button type="button" class="numpad-btn num-key" data-key="1">1</button>
            <button type="button" class="numpad-btn num-key" data-key="2">2</button>
            <button type="button" class="numpad-btn num-key" data-key="3">3</button>
            <button type="button" class="numpad-btn num-key" data-key="4">4</button>
            <button type="button" class="numpad-btn num-key" data-key="5">5</button>
            <button type="button" class="numpad-btn num-key" data-key="6">6</button>
            <button type="button" class="numpad-btn num-key" data-key="7">7</button>
            <button type="button" class="numpad-btn num-key" data-key="8">8</button>
            <button type="button" class="numpad-btn num-key" data-key="9">9</button>
            <button type="button" class="numpad-btn num-key" data-key="0">0</button>
            <button type="button" class="numpad-btn num-key" data-key="000">000</button>
            <button type="button" class="numpad-btn" id="numpadBackspace" style="color:#c53030; display:flex; align-items:center; justify-content:center;">
                <svg style="width:20px; height:20px; stroke:currentColor; fill:none; stroke-width:2;" viewBox="0 0 24 24"><path d="m21 4H8l-7 8 7 8h13a2 2 0 0 0 2-2V6a2 2 0 0 0-2-2z"/><line x1="18" x2="12" y1="9" y2="15"/><line x1="12" x2="18" y1="9" y2="15"/></svg>
            </button>
        </div>

        <button type="button" id="confirmCashPaymentBtn" class="button button-full" style="margin-top: 10px;">
            Bayar & Cetak Transaksi
        </button>
    </div>
</div>

{{-- MODAL 2: QRIS DINAMIS OTOMATIS --}}
<div class="pos-modal-overlay" id="qrisModal">
    <div class="pos-modal-card">
        <div class="pos-modal-header">
            <h3>QRIS Dinamis</h3>
            <button type="button" class="pos-close-btn" onclick="closeModals()">&times;</button>
        </div>

        <div class="qris-box">
            <div class="qris-header-badge">
                <span class="qris-logo">QRIS</span>
                <span class="qris-nmid">NMID: ID1020261014001</span>
            </div>

            <div class="qris-merchant-name">LINTANG BAKERY</div>
            <div class="qris-amount-display" id="modalQrisAmount">Rp 0</div>
            <p class="muted" style="font-size: 11px; margin: 0 0 10px;">Nominal otomatis terkunci sesuai transaksi</p>

            <div class="qris-img-wrap">
                <img id="dynamicQrisImage" src="" alt="QRIS Dinamis Lintang Bakery">
            </div>

            <p style="font-size: 11px; color: var(--muted); margin: 6px 0 0;">
                Buka BCA Mobile, Livin, GoPay, OVO, ShopeePay, atau Dana, lalu scan QR di atas.
            </p>
        </div>

        <button type="button" id="confirmQrisPaymentBtn" class="button button-full" style="background:#218838; border-color:#1e7e34; display:inline-flex; align-items:center; justify-content:center; gap:8px;">
            <svg style="width:16px; height:16px; stroke:currentColor; fill:none; stroke-width:2.5;" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
            <span>Konfirmasi Pembayaran QRIS Berhasil</span>
        </button>
    </div>
</div>

{{-- MODAL 3: POPUP NOTIFIKASI TRANSAKSI BERHASIL --}}
@if (session('success'))
<div class="pos-modal-overlay active" id="successModal">
    <div class="pos-modal-card" style="max-width: 430px; text-align: center; padding: 32px 26px; border-radius: 20px;">
        <div style="width: 72px; height: 72px; background: #e6f4ea; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 16px; color: #1e7e34; box-shadow: 0 0 0 8px #f1f8f3;">
            <svg style="width: 38px; height: 38px; stroke: currentColor; fill: none; stroke-width: 2.8;" viewBox="0 0 24 24">
                <polyline points="20 6 9 17 4 12"></polyline>
            </svg>
        </div>

        <h2 style="font-size: 22px; font-weight: 800; color: var(--brown-dark); margin: 0 0 6px;">
            {{ session('trx_success') ? 'Transaksi Berhasil!' : 'Berhasil!' }}
        </h2>
        <p style="font-size: 13.5px; color: var(--muted); margin: 0 0 20px;">
            {{ session('success') }}
        </p>

        @if (session('trx_success'))
            @php $stx = session('trx_success'); @endphp
            <div style="background: #faf6f1; border: 1px solid #ebdccb; border-radius: 12px; padding: 14px 16px; text-align: left; margin-bottom: 22px; font-size: 13px;">
                <div style="display: flex; justify-content: space-between; margin-bottom: 8px;">
                    <span style="color: var(--muted);">No. Transaksi</span>
                    <strong style="color: var(--brown); font-family: monospace; font-size: 14px;">{{ $stx['code'] }}</strong>
                </div>
                <div style="display: flex; justify-content: space-between; margin-bottom: 8px;">
                    <span style="color: var(--muted);">Pembeli</span>
                    <strong>{{ $stx['buyer'] }}</strong>
                </div>
                <div style="display: flex; justify-content: space-between; margin-bottom: 8px;">
                    <span style="color: var(--muted);">Metode Pembayaran</span>
                    <span style="text-transform: uppercase; font-weight: 700; color: var(--brown-dark);">{{ $stx['payment_method'] }}</span>
                </div>
                @if (!empty($stx['notes']))
                    <div style="background: #fff; border-radius: 6px; padding: 8px 10px; margin-bottom: 8px; font-size: 12px; color: #444; border: 1px dashed #d7c1ad; line-height: 1.4;">
                        {{ $stx['notes'] }}
                    </div>
                @endif
                <div style="display: flex; justify-content: space-between; padding-top: 8px; border-top: 1px solid #ebdccb; font-size: 15px;">
                    <span>Total Pembayaran</span>
                    <strong style="color: var(--brown-dark); font-size: 17px;">Rp {{ number_format($stx['total'], 0, ',', '.') }}</strong>
                </div>
            </div>
        @endif

        <button type="button" id="closeSuccessModalBtn" class="button button-full" onclick="closeModals()" style="font-size: 14px; font-weight: 700; padding: 12px; border-radius: 10px;">
            {{ session('trx_success') ? 'OK, Transaksi Baru' : 'Tutup' }}
        </button>
    </div>
</div>
@endif

{{-- Hidden Form for Submitting POS Transaction --}}
<form id="posSubmitForm" method="post" action="{{ route('admin.transactions.store') }}" style="display:none;">
    @csrf
    <input type="hidden" name="buyer_name" id="submitBuyerName">
    <input type="hidden" name="date" id="submitDate" value="{{ date('Y-m-d') }}">
    <input type="hidden" name="payment_method" id="submitPaymentMethod">
    <input type="hidden" name="notes" id="submitNotes">
    <div id="submitItemsInputs"></div>
</form>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Tab switching
    const posTabs = document.querySelectorAll('#posTabs .report-tab');
    const posContents = document.querySelectorAll('.pos-tab-content');

    function switchTab(tabName) {
        posTabs.forEach(t => t.classList.toggle('active', t.dataset.tab === tabName));
        posContents.forEach(c => c.classList.toggle('active', c.id === 'tab-' + tabName));
    }

    posTabs.forEach(tab => {
        tab.addEventListener('click', function() {
            const targetTab = this.dataset.tab;
            switchTab(targetTab);
            const url = new URL(window.location.href);
            url.searchParams.set('tab', targetTab);
            window.history.replaceState({}, '', url);
        });
    });

    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.get('tab') === 'riwayat' || urlParams.has('page')) {
        switchTab('riwayat');
    }

    // POS Cart State
    let cart = [];
    let selectedPayment = 'cash';
    let receivedCash = 0;

    // Search Filter
    const searchInput = document.getElementById('posSearch');
    const productCards = document.querySelectorAll('.pos-product-card');

    function filterProducts() {
        const query = searchInput.value.toLowerCase().trim();

        productCards.forEach(card => {
            const name = card.dataset.name.toLowerCase();
            card.style.display = name.includes(query) ? 'flex' : 'none';
        });
    }

    searchInput.addEventListener('input', filterProducts);

    // Add to Cart
    productCards.forEach(card => {
        card.addEventListener('click', function(e) {
            const stock = parseInt(this.dataset.stock, 10);
            if (stock <= 0) return;

            const id = parseInt(this.dataset.id, 10);
            const name = this.dataset.name;
            const price = parseInt(this.dataset.price, 10);

            addToCart(id, name, price, stock);
        });
    });

    function addToCart(id, name, price, stock) {
        const existing = cart.find(item => item.id === id);
        if (existing) {
            if (existing.qty < stock) {
                existing.qty++;
            } else {
                alert('Stok tidak mencukupi! Maksimal ' + stock + ' bal.');
            }
        } else {
            cart.push({ id, name, price, stock, qty: 1 });
        }
        renderCart();
    }

    function renderCart() {
        const container = document.getElementById('cartItemsContainer');
        const emptyMsg = document.getElementById('emptyCartMessage');
        const countSpan = document.getElementById('cartCount');
        const subtotalEl = document.getElementById('cartSubtotal');
        const totalEl = document.getElementById('cartTotal');
        const payBtn = document.getElementById('payNowBtn');

        if (cart.length === 0) {
            container.innerHTML = '<div class="report-empty" style="padding: 24px 0;"><svg style="width: 28px; height: 28px; stroke: #a4968d; fill: none; stroke-width: 1.8; display: block; margin: 0 auto 8px;" viewBox="0 0 24 24"><path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4Z"/><path d="M3 6h18"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>Belum ada produk dipilih.<br><small class="muted">Klik produk di sebelah kiri untuk menambahkan.</small></div>';
            countSpan.textContent = '0';
            subtotalEl.textContent = 'Rp 0';
            totalEl.textContent = 'Rp 0';
            payBtn.disabled = true;
            payBtn.textContent = 'Pilih Produk Dulu';
            return;
        }

        let total = 0;
        let totalItems = 0;
        let html = '';

        cart.forEach((item, index) => {
            const itemSubtotal = item.price * item.qty;
            total += itemSubtotal;
            totalItems += item.qty;

            html += `
                <div class="pos-cart-item">
                    <button type="button" class="pos-del-btn" onclick="removeItem(${index})" title="Hapus item" style="display: flex; align-items: center; justify-content: center;">
                        <svg style="width: 15px; height: 15px; stroke: currentColor; fill: none; stroke-width: 2;" viewBox="0 0 24 24"><path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/><line x1="10" x2="10" y1="11" y2="17"/><line x1="14" x2="14" y1="11" y2="17"/></svg>
                    </button>
                    <div class="pos-item-details">
                        <span class="pos-item-name">${item.name}</span>
                        <span class="pos-item-sub">Rp ${item.price.toLocaleString('id-ID')} &times; ${item.qty} = <strong>Rp ${itemSubtotal.toLocaleString('id-ID')}</strong></span>
                    </div>
                    <div class="pos-stepper">
                        <button type="button" class="pos-step-btn" onclick="changeQty(${index}, -1)">-</button>
                        <span class="pos-step-qty">${item.qty}</span>
                        <button type="button" class="pos-step-btn" onclick="changeQty(${index}, 1)">+</button>
                    </div>
                </div>
            `;
        });

        container.innerHTML = html;
        countSpan.textContent = totalItems;
        subtotalEl.textContent = 'Rp ' + total.toLocaleString('id-ID');
        totalEl.textContent = 'Rp ' + total.toLocaleString('id-ID');
        payBtn.disabled = false;
        payBtn.textContent = 'Bayar Sekarang (Rp ' + total.toLocaleString('id-ID') + ')';
    }

    window.removeItem = function(index) {
        cart.splice(index, 1);
        renderCart();
    };

    window.changeQty = function(index, delta) {
        const item = cart[index];
        const newQty = item.qty + delta;
        if (newQty <= 0) {
            cart.splice(index, 1);
        } else if (newQty > item.stock) {
            alert('Maksimal stok tersedia hanya ' + item.stock + ' bal.');
        } else {
            item.qty = newQty;
        }
        renderCart();
    };

    document.getElementById('clearCartBtn').addEventListener('click', function() {
        cart = [];
        renderCart();
    });

    // Payment Method Selection
    const payOptions = document.querySelectorAll('.pos-pay-opt');
    payOptions.forEach(opt => {
        opt.addEventListener('click', function() {
            payOptions.forEach(o => o.classList.remove('active'));
            opt.classList.add('active');
            selectedPayment = opt.dataset.method;
        });
    });

    function getCartTotal() {
        return cart.reduce((sum, item) => sum + (item.price * item.qty), 0);
    }

    // Pay Now Click Handler
    const payNowBtn = document.getElementById('payNowBtn');
    payNowBtn.addEventListener('click', function() {
        if (cart.length === 0) return;
        const total = getCartTotal();

        if (selectedPayment === 'cash') {
            openCashModal(total);
        } else if (selectedPayment === 'qris') {
            openQrisModal(total);
        } else {
            // EDC / Transfer
            if (confirm(`Konfirmasi pembayaran EDC / Transfer sebesar Rp ${total.toLocaleString('id-ID')}?`)) {
                submitPosTransaction('edc', 'Pembayaran via EDC / Transfer Bank');
            }
        }
    });

    // CASH MODAL LOGIC
    function openCashModal(total) {
        document.getElementById('modalCashTotal').textContent = 'Rp ' + total.toLocaleString('id-ID');
        receivedCash = 0; // kosongkan, tidak di-default
        updateCashDisplay(total);
        document.getElementById('cashModal').classList.add('active');
        setTimeout(() => {
            const input = document.getElementById('receivedCashInput');
            if (input) input.focus();
        }, 100);
    }

    function updateCashDisplay(total, updateInput = true) {
        const totalAmount = total || getCartTotal();
        const input = document.getElementById('receivedCashInput');
        if (updateInput) {
            input.value = receivedCash > 0 ? receivedCash.toLocaleString('id-ID') : '';
        }
        const changeEl = document.getElementById('modalCashChange');
        if (receivedCash === 0) {
            changeEl.textContent = 'Rp 0';
            changeEl.style.color = '#1b633b';
        } else {
            const change = receivedCash - totalAmount;
            if (change >= 0) {
                changeEl.textContent = 'Rp ' + change.toLocaleString('id-ID');
                changeEl.style.color = '#1b633b';
            } else {
                changeEl.textContent = 'Kurang Rp ' + Math.abs(change).toLocaleString('id-ID');
                changeEl.style.color = '#dc3545';
            }
        }
    }

    const cashInputEl = document.getElementById('receivedCashInput');
    cashInputEl.addEventListener('input', function() {
        const raw = this.value.replace(/\D/g, '');
        receivedCash = parseInt(raw, 10) || 0;
        updateCashDisplay(null, false);
    });

    cashInputEl.addEventListener('blur', function() {
        if (receivedCash > 0) {
            this.value = receivedCash.toLocaleString('id-ID');
        } else {
            this.value = '';
        }
    });

    document.getElementById('exactCashBtn').addEventListener('click', function() {
        receivedCash = getCartTotal();
        updateCashDisplay();
    });

    document.querySelectorAll('.quick-add-cash').forEach(btn => {
        btn.addEventListener('click', function() {
            receivedCash = parseInt(this.dataset.val, 10);
            updateCashDisplay();
        });
    });

    document.querySelectorAll('.num-key').forEach(btn => {
        btn.addEventListener('click', function() {
            let str = receivedCash > 0 ? receivedCash.toString() : '';
            str += this.dataset.key;
            receivedCash = parseInt(str, 10) || 0;
            updateCashDisplay();
        });
    });

    document.getElementById('numpadBackspace').addEventListener('click', function() {
        let str = receivedCash > 0 ? receivedCash.toString() : '';
        str = str.slice(0, -1);
        receivedCash = parseInt(str, 10) || 0;
        updateCashDisplay();
    });

    document.getElementById('confirmCashPaymentBtn').addEventListener('click', function() {
        const total = getCartTotal();
        if (receivedCash <= 0 || receivedCash < total) {
            alert('Uang yang diterima masih kurang atau belum dimasukkan!');
            return;
        }
        const change = receivedCash - total;
        const note = `Tunai diterima: Rp ${receivedCash.toLocaleString('id-ID')}, Kembalian: Rp ${change.toLocaleString('id-ID')}`;
        submitPosTransaction('cash', note);
    });

    // QRIS MODAL LOGIC (OTOMATIS SESUAI JUMLAH TRANSAKSI)
    function openQrisModal(total) {
        document.getElementById('modalQrisAmount').textContent = 'Rp ' + total.toLocaleString('id-ID');

        // Generate dynamic QRIS string with exact transaction amount
        // Standar QRIS EMVCo Tag 54 (Transaction Amount)
        const amtStr = total.toString();
        const amtTag = '54' + ('0' + amtStr.length).slice(-2) + amtStr;
        const qrisPayload = '00020101021226600016ID.LINKAJA.WWW0118936009110020261014520454995303360' + amtTag + '5802ID5914LINTANG BAKERY6010YOGYAKARTA6304ABCD';

        // Load dynamic QR Code via API
        const qrUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=250x250&data=' + encodeURIComponent(qrisPayload);
        document.getElementById('dynamicQrisImage').src = qrUrl;

        document.getElementById('qrisModal').classList.add('active');
    }

    document.getElementById('confirmQrisPaymentBtn').addEventListener('click', function() {
        const total = getCartTotal();
        const note = `QRIS Dinamis Terverifikasi: Rp ${total.toLocaleString('id-ID')}`;
        submitPosTransaction('qris', note);
    });

    window.closeModals = function() {
        document.querySelectorAll('.pos-modal-overlay').forEach(m => m.classList.remove('active'));
    };

    // Close modals on clicking overlay backdrop
    document.querySelectorAll('.pos-modal-overlay').forEach(overlay => {
        overlay.addEventListener('click', function(e) {
            if (e.target === this) {
                closeModals();
            }
        });
    });

    // Close modal on Escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeModals();
        }
    });

    // Auto focus button when success modal is present
    const closeSuccessBtn = document.getElementById('closeSuccessModalBtn');
    if (closeSuccessBtn) {
        setTimeout(() => closeSuccessBtn.focus(), 100);
    }

    // Submit POS Transaction to Backend
    function submitPosTransaction(method, notes) {
        const form = document.getElementById('posSubmitForm');
        const buyerName = document.getElementById('buyerNameInput').value.trim() || 'Pelanggan Toko';

        document.getElementById('submitBuyerName').value = buyerName;
        document.getElementById('submitPaymentMethod').value = method;
        document.getElementById('submitNotes').value = notes;

        const inputsContainer = document.getElementById('submitItemsInputs');
        inputsContainer.innerHTML = '';

        cart.forEach((item, index) => {
            inputsContainer.innerHTML += `
                <input type="hidden" name="items[${index}][product_id]" value="${item.id}">
                <input type="hidden" name="items[${index}][quantity]" value="${item.qty}">
            `;
        });

        // Single item fallback for full legacy compatibility
        if (cart.length === 1) {
            inputsContainer.innerHTML += `
                <input type="hidden" name="product_id" value="${cart[0].id}">
                <input type="hidden" name="quantity" value="${cart[0].qty}">
            `;
        }

        form.submit();
    }
});
</script>
@endsection
