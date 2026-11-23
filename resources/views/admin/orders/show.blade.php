@extends('layouts.admin')

@section('title', 'Detail Pesanan #'.$order->code)
@section('section', 'Pesanan')

@section('content')
<style>
.order-detail-layout {
    display: grid;
    grid-template-columns: 1.25fr 0.85fr;
    gap: 20px;
    align-items: start;
}
@media (max-width: 992px) {
    .order-detail-layout {
        grid-template-columns: 1fr;
    }
}
.order-box {
    background: #ffffff;
    border: 1px solid #ebdcd0;
    border-radius: 10px;
    padding: 18px 20px;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.02);
}
.order-box-head {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding-bottom: 12px;
    margin-bottom: 14px;
    border-bottom: 1px solid #f2ebe3;
}
.order-box-title {
    font-size: 15px;
    font-weight: 700;
    color: #2e1f16;
    margin: 0;
}
.order-box-eyebrow {
    font-size: 10px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.06em;
    color: #a47754;
    display: block;
    margin-bottom: 2px;
}

/* Slim & Clean Form Fields */
.order-form-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 12px;
}
@media (max-width: 600px) {
    .order-form-grid {
        grid-template-columns: 1fr;
    }
}
.order-field {
    display: flex;
    flex-direction: column;
    gap: 4px;
}
.order-field-label {
    font-size: 11.5px;
    font-weight: 550;
    color: #635347;
    letter-spacing: 0.01em;
}
.order-field-control {
    width: 100%;
    height: 36px !important;
    min-height: 36px !important;
    padding: 6px 11px !important;
    font-size: 12.5px !important;
    font-weight: 450 !important;
    color: #2b1f17 !important;
    background: #ffffff !important;
    border: 1px solid #dfd5cb !important;
    border-radius: 6px !important;
    box-shadow: 0 1px 2px rgba(0,0,0,0.02) !important;
    transition: border-color .15s ease, box-shadow .15s ease;
}
.order-field-control:focus {
    border-color: #a47754 !important;
    box-shadow: 0 0 0 2px rgba(164, 119, 84, 0.15) !important;
}

/* Key-Value Info List */
.order-info-list {
    display: flex;
    flex-direction: column;
}
.order-info-row {
    display: flex;
    flex-direction: column;
    gap: 2px;
    padding: 8px 0;
    border-bottom: 1px solid #f5efe9;
}
.order-info-row:first-child {
    padding-top: 0;
}
.order-info-row:last-child {
    border-bottom: none;
    padding-bottom: 0;
}
.order-info-label {
    font-size: 10.5px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.04em;
    color: #8f7f74;
}
.order-info-value {
    font-size: 13px;
    font-weight: 550;
    color: #2b1f17;
    line-height: 1.45;
}

/* Products Table */
.order-product-table {
    width: 100%;
    border-collapse: collapse;
}
.order-product-table th {
    background: #faf7f4;
    padding: 8px 10px;
    font-size: 11px;
    font-weight: 650;
    text-transform: uppercase;
    letter-spacing: 0.03em;
    color: #7d6e64;
    border-bottom: 1px solid #ede3da;
    text-align: left;
}
.order-product-table td {
    padding: 10px;
    font-size: 12.5px;
    color: #3b2d24;
    border-bottom: 1px solid #f6f0ea;
    vertical-align: middle;
}
.order-product-table tbody tr:last-child td {
    border-bottom: none;
}
</style>

{{-- Header Atas: Navigasi, Judul & Tombol Aksi --}}
<div style="margin-bottom: 20px;">
    <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:12px; margin-bottom:8px;">
        <a href="{{ route('admin.orders.index') }}" class="text-link" style="font-size:12px; display:inline-flex; align-items:center; gap:5px; text-decoration:none;">
            ← Kembali ke Daftar Pesanan
        </a>
        <div style="display:flex; gap:8px; flex-wrap:wrap;">
            <a href="https://wa.me/{{ preg_replace('/^0/', '62', preg_replace('/[^0-9]/', '', $order->phone)) }}?text={{ urlencode('Halo '.$order->buyer.', kami dari Lintang Bakery mengenai pesanan '.$order->code) }}" target="_blank" class="button" style="background:#25D366; border-color:#25D366; color:white; font-size:12px; padding:6px 14px; border-radius:6px; display:inline-flex; align-items:center;">
                Hubungi WhatsApp
            </a>
            <a href="{{ route('admin.orders.delivery-order', $order) }}" target="_blank" class="button button-light" style="font-size:12px; padding:6px 14px; border-radius:6px; display:inline-flex; align-items:center;">
                Cetak Surat Jalan
            </a>
            <a href="{{ route('admin.orders.invoice', $order) }}" target="_blank" class="button button-light" style="font-size:12px; padding:6px 14px; border-radius:6px; display:inline-flex; align-items:center;">
                Cetak Faktur
            </a>
        </div>
    </div>

    <div style="display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:10px;">
        <div style="display:flex; align-items:center; gap:10px; flex-wrap:wrap;">
            <h1 style="font-size:22px; font-weight:750; color:#2d1e15; margin:0;">Pesanan #{{ $order->code }}</h1>
            <span class="status-pill highlight" style="font-size:11px; padding:4px 10px; border-radius:20px;">{{ $order->status }}</span>
        </div>
        <p style="margin:0; font-size:12px; color:#85756a;">
            Dibuat: <span style="font-weight:550; color:#4a3b31;">{{ $order->created_at->translatedFormat('d F Y, H:i') }} WIB</span>
            · Pembeli: <span style="font-weight:600; color:#2d1e15;">{{ $order->buyer }}</span>
        </p>
    </div>
</div>

<div class="order-detail-layout">
    {{-- Sisi Kiri: Operasional Logistik & Rincian Produk --}}
    <div style="display:flex; flex-direction:column; gap:18px;">

        {{-- Card 1: Operasional & Pengiriman --}}
        <section class="order-box">
            <div class="order-box-head">
                <div>
                    <span class="order-box-eyebrow">OPERASIONAL & PENGIRIMAN</span>
                    <h2 class="order-box-title">Status Pesanan & Logistik</h2>
                </div>
                <span class="status-pill" style="font-size:10.5px; padding:3px 8px;">{{ $order->status }}</span>
            </div>

            <form method="post" action="{{ route('admin.orders.update', $order) }}" style="display:flex; flex-direction:column; gap:12px; margin:0;">
                @csrf @method('PATCH')

                <div class="order-form-grid">
                    <div class="order-field">
                        <label class="order-field-label" for="status_select">Status Alur Pesanan</label>
                        <select id="status_select" name="status" class="order-field-control" required>
                            @foreach ($statuses as $st)
                                <option value="{{ $st }}" @selected($order->status === $st)>{{ $st }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="order-field">
                        <label class="order-field-label" for="courier_input">Sopir / Armada Pengantaran (Opsional)</label>
                        <input id="courier_input" type="text" name="courier_name" class="order-field-control" value="{{ old('courier_name', $order->courier_name) }}">
                    </div>
                </div>

                <div class="order-field">
                    <label class="order-field-label" for="notes_input">Catatan Pengantaran Toko (Opsional)</label>
                    <input id="notes_input" type="text" name="shipping_notes" class="order-field-control" value="{{ old('shipping_notes', $order->shipping_notes) }}">
                </div>

                {{-- Verifikasi Pelunasan DP (Jika pesanan bersistem DP) --}}
                @if ($order->payment_type === 'dp')
                    <div style="background:#fffcf7; border:1px solid #ebd9c8; border-radius:8px; padding:12px 14px; margin-top:2px;">
                        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:8px;">
                            <span class="order-box-eyebrow" style="color:#b45309; margin:0;">VERIFIKASI PELUNASAN DP (35%)</span>
                            <span style="font-size:11px; font-weight:600; color:#854d0e; background:#fef3c7; padding:2px 7px; border-radius:4px;">
                                {{ $order->settlement_status ?: 'Belum Ada Bukti' }}
                            </span>
                        </div>

                        <div style="display:flex; justify-content:space-between; align-items:center; font-size:12px; color:#5c4d41; margin-bottom:10px;">
                            <span>Sisa Tagihan: <strong style="color:#2d1e15; font-size:13px;">Rp {{ number_format($order->remaining, 0, ',', '.') }}</strong></span>
                        </div>

                        @if ($order->settlement_proof)
                            <div style="margin-bottom:10px; font-size:11.5px;">
                                <a href="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($order->settlement_proof) }}" target="_blank" rel="noopener noreferrer" class="text-link" style="font-weight:650; display:inline-flex; align-items:center; gap:4px;">
                                    Buka Bukti Transfer Pelunasan Pembeli ↗
                                </a>
                            </div>

                            <div class="order-field">
                                <label class="order-field-label" for="settlement_select">Pembaruan Verifikasi Pelunasan</label>
                                <select id="settlement_select" name="settlement_status" class="order-field-control">
                                    <option value="Menunggu Verifikasi Admin" @selected($order->settlement_status === 'Menunggu Verifikasi Admin')>Menunggu Verifikasi Admin</option>
                                    <option value="Lunas / Disetujui" @selected($order->settlement_status === 'Lunas / Disetujui')>Lunas / Disetujui (Sisa Menjadi Rp 0)</option>
                                    <option value="Ditolak" @selected($order->settlement_status === 'Ditolak')>Ditolak (Bukti Tidak Valid)</option>
                                </select>
                            </div>
                        @else
                            <p class="muted" style="font-size:11.5px; margin:0;">Pembeli belum mengunggah bukti pelunasan sisa 35%.</p>
                        @endif
                    </div>
                @endif

                <div style="display:flex; justify-content:flex-end; margin-top:4px;">
                    <button class="button" type="submit" style="font-size:12px; font-weight:600; padding:7px 18px; border-radius:6px; background:#62432b; color:white;">
                        Simpan Perubahan Pesanan
                    </button>
                </div>
            </form>
        </section>

        {{-- Card 2: Rincian Produk --}}
        <section class="order-box">
            <div class="order-box-head">
                <div>
                    <span class="order-box-eyebrow">RINCIAN PRODUK</span>
                    <h2 class="order-box-title">Barang Dipesan</h2>
                </div>
                <span style="font-size:11.5px; color:#7d6e64; font-weight:550; background:#f5eee7; padding:3px 8px; border-radius:6px;">
                    Total: {{ $order->items->sum('quantity') }} bal
                </span>
            </div>

            <div class="table-scroll">
                <table class="order-product-table">
                    <thead>
                        <tr>
                            <th style="width: 44%;">Produk</th>
                            <th style="width: 16%;">Qty (bal)</th>
                            <th style="width: 20%;">Harga Jual</th>
                            <th style="width: 20%; text-align:right;">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($order->items as $item)
                            <tr>
                                <td>
                                    <div style="font-weight:600; color:#2e1f16;">{{ $item->name }}</div>
                                    <div style="font-size:11px; color:#8c7d72; margin-top:2px;">HPP: Rp {{ number_format($item->harga_modal_at_purchase, 0, ',', '.') }} / bal</div>
                                </td>
                                <td>{{ $item->quantity }} bal</td>
                                <td>Rp {{ number_format($item->unit_price, 0, ',', '.') }}</td>
                                <td style="text-align:right; font-weight:650; color:#2d1e15;">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div style="margin-top:14px; padding-top:12px; border-top:1px dashed #e8ded4; display:flex; flex-direction:column; gap:5px; font-size:12.5px;">
                <div style="display:flex; justify-content:space-between; color:#6b5c51;">
                    <span>Subtotal Produk:</span>
                    <span style="font-weight:550; color:#2d1e15;">Rp {{ number_format($order->total, 0, ',', '.') }}</span>
                </div>
                <div style="display:flex; justify-content:space-between; color:#6b5c51;">
                    <span>Ongkir Kargo ({{ $order->items->sum('quantity') }} bal × Rp 10.000):</span>
                    <span style="font-weight:550; color:#2d1e15;">Rp {{ number_format($order->shipping_cost, 0, ',', '.') }}</span>
                </div>
                <div style="display:flex; justify-content:space-between; align-items:center; font-weight:750; font-size:15px; color:#2e1f16; border-top:1px solid #ebdcd0; padding-top:8px; margin-top:2px;">
                    <span>Total Tagihan:</span>
                    <span style="color:#784522;">Rp {{ number_format($order->total + $order->shipping_cost, 0, ',', '.') }}</span>
                </div>

                @if ($order->payment_type === 'dp')
                    <div style="display:flex; justify-content:space-between; font-size:12px; color:#854d0e; background:#fef3c7; padding:6px 10px; border-radius:6px; margin-top:4px;">
                        <span>Uang Muka (DP 65%): Rp {{ number_format($order->dp, 0, ',', '.') }}</span>
                        <span>Sisa Pelunasan: Rp {{ number_format($order->remaining, 0, ',', '.') }}</span>
                    </div>
                @endif
            </div>
        </section>
    </div>

    {{-- Sisi Kanan: Data Pemesan & Bukti Bayar --}}
    <div style="display:flex; flex-direction:column; gap:18px;">

        {{-- Card 3: Data Pemesan --}}
        <section class="order-box">
            <div class="order-box-head">
                <h2 class="order-box-title">Data Pemesan</h2>
            </div>

            <div class="order-info-list">
                <div class="order-info-row">
                    <span class="order-info-label">Nama Pemesan</span>
                    <div class="order-info-value">{{ $order->buyer }}</div>
                </div>

                <div class="order-info-row">
                    <span class="order-info-label">No. Telepon / WhatsApp</span>
                    <div class="order-info-value">{{ $order->phone }}</div>
                </div>

                <div class="order-info-row">
                    <span class="order-info-label">Alamat Pengiriman</span>
                    <div class="order-info-value" style="font-weight:450; font-size:12.5px; color:#3b2d24;">{{ $order->address }}</div>
                </div>

                <div class="order-info-row">
                    <span class="order-info-label">Kota & Provinsi</span>
                    <div class="order-info-value">{{ $order->city }}, {{ $order->province }}</div>
                </div>
            </div>
        </section>

        {{-- Card 4: Bukti Pembayaran Awal --}}
        <section class="order-box">
            <div class="order-box-head">
                <div>
                    <span class="order-box-eyebrow">STATUS PEMBAYARAN</span>
                    <h2 class="order-box-title">Bukti Transfer / QRIS</h2>
                </div>
                @if ($order->payment_type === 'dp')
                    <span style="font-size:10.5px; font-weight:650; color:#92400e; background:#fef3c7; padding:2px 8px; border-radius:12px;">Uang Muka (DP)</span>
                @else
                    <span style="font-size:10.5px; font-weight:650; color:#166534; background:#dcfce7; padding:2px 8px; border-radius:12px;">Lunas (100%)</span>
                @endif
            </div>

            @php
                $methodName = match(strtoupper($order->payment_method ?? '')) {
                    'BANK_TRANSFER' => 'Transfer Bank',
                    'QRIS' => 'QRIS',
                    'CASH' => 'Tunai / COD',
                    default => strtoupper($order->payment_method ?: 'Transfer')
                };
            @endphp

            <div style="font-size:12.5px; margin-bottom:10px; display:flex; flex-direction:column; gap:4px; background:#faf7f4; padding:10px 12px; border-radius:7px; border:1px solid #f0e6dd;">
                <div style="display:flex; justify-content:space-between;">
                    <span style="color:#7d6e64;">Total Awal Bayar:</span>
                    <strong style="color:#2d1e15;">Rp {{ number_format($order->dp ?: ($order->total + $order->shipping_cost), 0, ',', '.') }}</strong>
                </div>
                <div style="display:flex; justify-content:space-between; font-size:11.5px;">
                    <span style="color:#7d6e64;">Metode Bayar:</span>
                    <span style="font-weight:600; color:#5c4d41;">{{ $methodName }}</span>
                </div>
            </div>

            @if ($order->payment_proof)
                <div style="border:1px solid #ebdcd0; border-radius:8px; overflow:hidden; background:#faf7f4; text-align:center; padding:10px;">
                    <a href="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($order->payment_proof) }}" target="_blank" rel="noopener noreferrer">
                        <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($order->payment_proof) }}" alt="Bukti Pembayaran" style="max-height:200px; max-width:100%; object-fit:contain; border-radius:4px; display:inline-block;">
                    </a>
                    <div style="margin-top:6px;">
                        <a href="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($order->payment_proof) }}" target="_blank" class="text-link" style="font-size:11.5px; font-weight:600;">
                            Klik untuk memperbesar gambar ↗
                        </a>
                    </div>
                </div>
            @else
                <div style="padding:22px 14px; text-align:center; border:1px dashed #dfd5cb; border-radius:8px; background:#faf8f5;">
                    <p style="margin:0; font-size:12px; color:#8c7d72;">Tidak ada lampiran bukti transfer.</p>
                </div>
            @endif
        </section>
    </div>
</div>
@endsection
