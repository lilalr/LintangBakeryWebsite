@extends('layouts.admin')

@section('title', 'Detail Pesanan '.$order->code)
@section('section', 'Pesanan')

@section('content')
<div class="content-heading" style="display:flex; justify-content:space-between; align-items:flex-start; flex-wrap:wrap; gap:16px;">
    <div>
        <a href="{{ route('admin.orders.index') }}" class="text-link" style="font-size:12px; margin-bottom:6px; display:inline-block;">← Kembali ke Daftar Pesanan</a>
        <h1 style="margin:0;">Pesanan #{{ $order->code }}</h1>
        <p>Dibuat: {{ $order->created_at->translatedFormat('d F Y, H:i') }} WIB · Pembeli: <strong>{{ $order->buyer }}</strong></p>
    </div>
    <div style="display:flex; gap:8px; flex-wrap:wrap;">
        <a href="https://wa.me/{{ preg_replace('/^0/', '62', preg_replace('/[^0-9]/', '', $order->phone)) }}?text={{ urlencode('Halo '.$order->buyer.', kami dari Lintang Bakery mengenai pesanan '.$order->code) }}" target="_blank" class="button" style="background:#25D366; border-color:#25D366; color:white; font-size:12px; display:inline-flex; align-items:center; gap:6px;">
            Hubungi WhatsApp
        </a>
        <a href="{{ route('admin.orders.delivery-order', $order) }}" target="_blank" class="button button-light" style="font-size:12px; display:inline-flex; align-items:center; gap:6px;">
            📄 Cetak Surat Jalan
        </a>
        <a href="{{ route('admin.orders.invoice', $order) }}" target="_blank" class="button button-light" style="font-size:12px; display:inline-flex; align-items:center; gap:6px;">
            🧾 Cetak Faktur
        </a>
    </div>
</div>

<div class="dashboard-grid" style="grid-template-columns: 1.2fr 0.8fr; align-items:start; gap:24px;">
    {{-- Sisi Kiri: Form Update Status, Ekspedisi & Verifikasi Pelunasan --}}
    <div style="display:flex; flex-direction:column; gap:20px;">
        {{-- Card Status & Logistik --}}
        <section class="panel" style="padding:22px;">
            <div class="section-head compact" style="margin-bottom:16px;">
                <div>
                    <span class="eyebrow">OPERASIONAL & PENGIRIMAN</span>
                    <h2 style="font-size:18px;">Status Pesanan & Logistik</h2>
                </div>
                <span class="status-pill highlight">{{ $order->status }}</span>
            </div>

            <form method="post" action="{{ route('admin.orders.update', $order) }}" class="stack-form">
                @csrf @method('PATCH')

                <div class="form-row">
                    <label>
                        <span>Status Alur Pesanan</span>
                        <select name="status" required>
                            @foreach ($statuses as $st)
                                <option value="{{ $st }}" @selected($order->status === $st)>{{ $st }}</option>
                            @endforeach
                        </select>
                    </label>

                    <label>
                        <span>Sopir / Armada Pengantaran Toko (Opsional)</span>
                        <input type="text" name="courier_name" value="{{ old('courier_name', $order->courier_name) }}" placeholder="Contoh: Pak Joko / Armada Pick-up Lintang Bakery">
                    </label>
                </div>

                <div class="form-row">
                    <label style="grid-column: 1 / -1;">
                        <span>Catatan Pengantaran Toko (Opsional)</span>
                        <input type="text" name="shipping_notes" value="{{ old('shipping_notes', $order->shipping_notes) }}" placeholder="Contoh: Diantar langsung pagi jam 09:00 WIB, armada mobil toko">
                    </label>
                </div>

                {{-- Bagian Pelunasan DP jika ada --}}
                @if ($order->payment_type === 'dp')
                    <div style="background:#fffcf8; border:1px solid #eadcd0; border-radius:10px; padding:16px; margin:8px 0;">
                        <span class="eyebrow" style="color:#b45309;">VERIFIKASI PELUNASAN DP (35%)</span>
                        <div style="display:flex; justify-content:space-between; margin:8px 0 12px; font-size:12.5px;">
                            <span>Sisa Tagihan: <strong style="color:var(--brown);">Rp {{ number_format($order->remaining, 0, ',', '.') }}</strong></span>
                            <span>Status Pelunasan: <strong>{{ $order->settlement_status ?: 'Belum Ada Bukti' }}</strong></span>
                        </div>

                        @if ($order->settlement_proof)
                            <div style="margin-bottom:12px; font-size:12px;">
                                <a href="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($order->settlement_proof) }}" target="_blank" rel="noopener noreferrer" class="text-link" style="font-weight:700;">
                                    🔍 Klik Di Sini untuk Membuka Bukti Transfer Pelunasan Pembeli →
                                </a>
                            </div>

                            <label>
                                <span>Verifikasi Status Pelunasan</span>
                                <select name="settlement_status">
                                    <option value="Menunggu Verifikasi Admin" @selected($order->settlement_status === 'Menunggu Verifikasi Admin')>Menunggu Verifikasi Admin</option>
                                    <option value="Lunas / Disetujui" @selected($order->settlement_status === 'Lunas / Disetujui')>Lunas / Disetujui (Sisa Menjadi Rp 0)</option>
                                    <option value="Ditolak" @selected($order->settlement_status === 'Ditolak')>Ditolak (Bukti Tidak Valid)</option>
                                </select>
                            </label>
                        @else
                            <p class="muted" style="font-size:11.5px; margin:0;">Pembeli belum mengunggah bukti pelunasan sisa 35%.</p>
                        @endif
                    </div>
                @endif

                <div style="display:flex; justify-content:flex-end;">
                    <button class="button" type="submit">Simpan Perubahan Pesanan</button>
                </div>
            </form>
        </section>

        {{-- Card Rincian Barang --}}
        <section class="panel" style="padding:22px;">
            <div class="section-head compact" style="margin-bottom:14px;">
                <div>
                    <span class="eyebrow">RINCIAN PRODUK</span>
                    <h2 style="font-size:18px;">Barang Dipesan</h2>
                </div>
                <span class="muted" style="font-size:12px;">Total: {{ $order->items->sum('quantity') }} bal</span>
            </div>

            <div class="table-scroll">
                <table>
                    <thead>
                        <tr>
                            <th>Produk</th>
                            <th>Qty (bal)</th>
                            <th>Harga Jual</th>
                            <th>Harga Modal (HPP)</th>
                            <th style="text-align:right;">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($order->items as $item)
                            <tr>
                                <td><strong>{{ $item->name }}</strong></td>
                                <td>{{ $item->quantity }} bal</td>
                                <td>Rp {{ number_format($item->unit_price, 0, ',', '.') }}</td>
                                <td class="muted">Rp {{ number_format($item->harga_modal_at_purchase, 0, ',', '.') }}</td>
                                <td style="text-align:right; font-weight:700;">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div style="margin-top:14px; padding-top:14px; border-top:1px dashed var(--line); display:flex; flex-direction:column; gap:6px; font-size:13px;">
                <div style="display:flex; justify-content:space-between;">
                    <span class="muted">Subtotal Produk:</span>
                    <span>Rp {{ number_format($order->total, 0, ',', '.') }}</span>
                </div>
                <div style="display:flex; justify-content:space-between;">
                    <span class="muted">Ongkir Kargo ({{ $order->items->sum('quantity') }} bal × Rp 10.000):</span>
                    <span>Rp {{ number_format($order->shipping_cost, 0, ',', '.') }}</span>
                </div>
                <div style="display:flex; justify-content:space-between; font-weight:800; font-size:16px; color:var(--brown-dark); border-top:1px solid var(--line); padding-top:8px;">
                    <span>Total Tagihan:</span>
                    <span>Rp {{ number_format($order->total + $order->shipping_cost, 0, ',', '.') }}</span>
                </div>
            </div>
        </section>
    </div>

    {{-- Sisi Kanan: Info Pembeli & Bukti Pembayaran --}}
    <div style="display:flex; flex-direction:column; gap:20px;">
        {{-- Info Pembeli & Alamat --}}
        <section class="panel" style="padding:22px;">
            <div class="section-head compact" style="margin-bottom:12px;">
                <div>
                    <span class="eyebrow">DATA PEMESAN</span>
                    <h2 style="font-size:18px;">Penerima & Alamat</h2>
                </div>
            </div>

            <div class="meta-list" style="display:flex; flex-direction:column; gap:12px; font-size:13px;">
                <div>
                    <span class="muted" style="font-size:11px; text-transform:uppercase;">Nama Pemesan</span>
                    <strong style="display:block; color:var(--brown-dark);">{{ $order->buyer }}</strong>
                </div>
                <div>
                    <span class="muted" style="font-size:11px; text-transform:uppercase;">No. Telepon</span>
                    <strong style="display:block; color:var(--brown-dark);">{{ $order->phone }}</strong>
                </div>
                <div>
                    <span class="muted" style="font-size:11px; text-transform:uppercase;">Alamat Pengiriman</span>
                    <p style="margin:2px 0 0; color:#4a3b31; line-height:1.5;">{{ $order->address }}</p>
                </div>
                <div>
                    <span class="muted" style="font-size:11px; text-transform:uppercase;">Kota & Provinsi</span>
                    <strong style="display:block; color:var(--brown-dark);">{{ $order->city }}, {{ $order->province }}</strong>
                </div>
            </div>
        </section>

        {{-- Bukti Pembayaran Awal (DP / Full) --}}
        <section class="panel" style="padding:22px;">
            <div class="section-head compact" style="margin-bottom:12px;">
                <div>
                    <span class="eyebrow">PEMBAYARAN AWAL</span>
                    <h2 style="font-size:18px;">Bukti Transfer / QRIS</h2>
                </div>
                <span class="muted" style="font-size:11px;">{{ strtoupper($order->payment_type) }}</span>
            </div>

            <div style="font-size:13px; margin-bottom:12px;">
                <span>Total Awal: <strong>Rp {{ number_format($order->dp ?: ($order->total + $order->shipping_cost), 0, ',', '.') }}</strong></span>
                <span style="display:block; color:var(--muted); font-size:11px; margin-top:2px;">Metode: {{ strtoupper($order->payment_method ?: 'Transfer') }}</span>
            </div>

            @if ($order->payment_proof)
                <div style="border:1px solid var(--line); border-radius:10px; overflow:hidden; background:#faf5ee; text-align:center;">
                    <a href="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($order->payment_proof) }}" target="_blank" rel="noopener noreferrer">
                        <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($order->payment_proof) }}" alt="Bukti Pembayaran" style="max-height:220px; width:100%; object-fit:contain; padding:8px;">
                    </a>
                </div>
                <small class="muted" style="display:block; text-align:center; margin-top:6px;">Klik gambar untuk memperbesar</small>
            @else
                <div class="empty-state small-empty"><p>Tidak ada lampiran bukti transfer.</p></div>
            @endif
        </section>
    </div>
</div>
@endsection
