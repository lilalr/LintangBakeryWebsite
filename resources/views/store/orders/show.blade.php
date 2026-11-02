@extends('layouts.store')

@section('title', 'Detail Pesanan '.$order->code.' · Lintang Bakery')

@section('content')
<div class="breadcrumbs">
    <a href="{{ route('store.home') }}">Beranda</a>
    <span>/</span>
    <a href="{{ route('buyer.dashboard') }}">Akun Saya</a>
    <span>/</span>
    <span>Pesanan {{ $order->code }}</span>
</div>

<div class="order-detail-page">
    {{-- Header Pesanan --}}
    <div class="order-page-head">
        <div>
            <span class="eyebrow">DETAIL & TRACKING PESANAN</span>
            <h1>Pesanan #{{ $order->code }}</h1>
            <p class="order-time-meta">Dibuat pada {{ $order->created_at->translatedFormat('d F Y, H:i') }} WIB</p>
        </div>
        <div class="order-head-actions">
            <button type="button" class="button button-light" onclick="window.print()" style="display:inline-flex; align-items:center; gap:6px;">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 6 2 18 2 18 9"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect x="6" y="14" width="12" height="8"/></svg>
                Cetak Invoice
            </button>
            <a href="https://wa.me/6281234567890?text={{ urlencode('Halo Admin Lintang Bakery, saya ingin menanyakan pesanan '.$order->code.' a.n. '.$order->buyer) }}" target="_blank" rel="noopener noreferrer" class="button" style="background:#25D366; border-color:#25D366; color:white; display:inline-flex; align-items:center; gap:6px;">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="currentColor"><path d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.582 2.128 2.182-.573c.978.58 1.911.928 3.145.929 3.178 0 5.767-2.587 5.768-5.766.001-3.187-2.575-5.771-5.764-5.771zm3.392 8.244c-.144.405-.837.774-1.17.824-.299.045-.677.063-1.092-.069-.252-.08-.575-.187-.988-.365-1.739-.751-2.874-2.502-2.961-2.617-.087-.116-.708-.94-.708-1.793s.448-1.273.607-1.446c.159-.173.346-.217.462-.217l.332.007c.106.005.249-.04.39.298.144.347.491 1.2.534 1.287.043.087.072.188.014.304-.058.116-.087.188-.173.289l-.26.304c-.087.086-.177.18-.076.354.101.174.449.741.964 1.201.662.591 1.221.774 1.394.86.173.086.275.072.376-.043.101-.116.433-.506.549-.68.116-.173.231-.145.39-.087s1.011.477 1.184.564.289.13.332.202c.043.073.043.419-.101.824z"/></svg>
                WhatsApp Admin
            </a>
        </div>
    </div>

    {{-- Stepper Status Pesanan --}}
    @php
        $statusList = [
            'Menunggu Konfirmasi',
            'Pembayaran DP Disetujui',
            'Sedang Diproduksi',
            'Siap Dikirim',
            'Sedang Dikirim',
            'Selesai/Lunas',
        ];
        $currentIdx = array_search($order->status, $statusList);
        if ($currentIdx === false) {
            $currentIdx = ($order->status === 'Sudah Sampai') ? 4 : 0;
        }
    @endphp

    <div class="tracking-stepper-card">
        <div class="stepper-header">
            <span class="stepper-title">Status Pesanan Saat Ini:</span>
            <span class="status-pill highlight">{{ $order->status }}</span>
        </div>
        
        <div class="stepper-track">
            @foreach ($statusList as $idx => $st)
                @php
                    $isDone = $idx < $currentIdx;
                    $isCurrent = $idx === $currentIdx;
                @endphp
                <div class="stepper-step {{ $isDone ? 'done' : '' }} {{ $isCurrent ? 'current' : '' }}">
                    <div class="step-dot">
                        @if ($isDone)
                            ✓
                        @else
                            {{ $idx + 1 }}
                        @endif
                    </div>
                    <span class="step-text">{{ $st }}</span>
                </div>
            @endforeach
        </div>

        {{-- Info Pengantaran Langsung oleh Toko --}}
        @if (in_array($order->status, ['Sedang Dikirim', 'Sudah Sampai', 'Selesai/Lunas']))
            <div class="shipping-info-banner">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 18V6a2 2 0 0 0-2-2H4a2 2 0 0 0-2 2v11a1 1 0 0 0 1 1h2"/><path d="M15 18H9"/><path d="M19 18h2a1 1 0 0 0 1-1v-3.65a1 1 0 0 0-.22-.624l-3.48-4.35A1 1 0 0 0 17.52 8H14"/><circle cx="17" cy="18.5" r="2.5"/><circle cx="7" cy="18.5" r="2.5"/></svg>
                <div>
                    <strong>Pengantaran Langsung oleh Toko:</strong>
                    <span>Pesanan diantar langsung oleh <b>{{ $order->courier_name ?: 'Armada / Kurir Toko Lintang Bakery' }}</b> ke alamat Anda.</span>
                    @if ($order->shipping_notes)
                        <small style="display:block; color:var(--muted); margin-top:2px;">Catatan Pengantaran: {{ $order->shipping_notes }}</small>
                    @endif
                </div>
            </div>
        @endif
    </div>

    {{-- Kotak Pelunasan DP (Jika menggunakan DP dan belum lunas) --}}
    @if ($order->payment_type === 'dp')
        <div class="settlement-card {{ $order->remaining <= 0 || $order->settlement_status === 'Lunas / Disetujui' ? 'settlement-done' : '' }}">
            <div class="settlement-head">
                <div>
                    <span class="eyebrow" style="color:#b45309;">STATUS PEMBAYARAN RESELLER</span>
                    <h3 style="margin:4px 0; color:var(--brown-dark);">Skema Uang Muka (DP 65%) & Pelunasan (35%)</h3>
                </div>
                <div>
                    @if ($order->remaining <= 0 || $order->settlement_status === 'Lunas / Disetujui')
                        <span class="status-pill" style="background:#dcfce7; color:#15803d; font-weight:700;">✓ Pembayaran Telah Lunas</span>
                    @elseif ($order->settlement_status === 'Menunggu Verifikasi Admin')
                        <span class="status-pill" style="background:#e0e7ff; color:#3730a3; font-weight:700;">⏳ Bukti Pelunasan Sedang Diverifikasi</span>
                    @else
                        <span class="status-pill" style="background:#fef3c7; color:#92400e; font-weight:700;">Sisa Tagihan: Rp {{ number_format($order->remaining, 0, ',', '.') }}</span>
                    @endif
                </div>
            </div>

            <div class="settlement-summary-grid">
                <div class="settlement-stat">
                    <span>Total Pembayaran (Termasuk Ongkir)</span>
                    <strong>Rp {{ number_format($order->total + $order->shipping_cost, 0, ',', '.') }}</strong>
                </div>
                <div class="settlement-stat">
                    <span>DP Awal (65%) Dibayarkan</span>
                    <strong style="color:#15803d;">Rp {{ number_format($order->dp, 0, ',', '.') }}</strong>
                    @if ($order->payment_proof)
                        <small><a href="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($order->payment_proof) }}" target="_blank" rel="noopener noreferrer" class="text-link">Lihat bukti DP</a></small>
                    @endif
                </div>
                <div class="settlement-stat">
                    <span>Sisa Pelunasan (35%)</span>
                    <strong style="color:var(--brown);">Rp {{ number_format($order->remaining, 0, ',', '.') }}</strong>
                </div>
            </div>

            {{-- Form Upload Bukti Pelunasan --}}
            @if ($order->remaining > 0 && $order->settlement_status !== 'Lunas / Disetujui')
                <div class="settlement-upload-box">
                    @if ($order->settlement_proof)
                        <div class="alert alert-success" style="margin: 0 0 16px;">
                            ✓ Bukti transfer pelunasan telah kami terima pada {{ $order->remaining_paid_at ? \Carbon\Carbon::parse($order->remaining_paid_at)->translatedFormat('d M Y, H:i') : 'hari ini' }}.
                            <a href="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($order->settlement_proof) }}" target="_blank" rel="noopener noreferrer" style="font-weight:700; text-decoration:underline; margin-left:6px;">Lihat Bukti Pelunasan Anda</a>.
                        </div>
                    @endif

                    <h4 style="margin:0 0 8px; color:var(--brown-dark);">Petunjuk Pembayaran Sisa Pelunasan</h4>
                    <p style="font-size:12.5px; color:#5c4b40; margin:0 0 14px; line-height:1.6;">
                        Silakan transfer sisa pelunasan sebesar <b>Rp {{ number_format($order->remaining, 0, ',', '.') }}</b> ke salah satu rekening resmi Lintang Bakery di bawah ini, lalu unggah struk/bukti transfer Anda:
                    </p>

                    <div class="bank-accounts-compact">
                        <div class="bank-pill"><strong>BCA:</strong> 8830-123-456 a.n. Lintang Bakery</div>
                        <div class="bank-pill"><strong>BRI:</strong> 0123-01-000456-50-1 a.n. Lintang Bakery</div>
                        <div class="bank-pill"><strong>Mandiri:</strong> 137-00-1234567-8 a.n. Lintang Bakery</div>
                    </div>

                    <form method="post" action="{{ route('buyer.orders.settlement', $order) }}" enctype="multipart/form-data" class="settlement-form">
                        @csrf
                        <div class="form-row-settle">
                            <label style="flex:1;">
                                <span>Pilih Bukti Transfer / Struk Pelunasan (Maks 4MB)</span>
                                <input type="file" name="payment_proof" accept="image/*" required>
                            </label>
                            <input type="hidden" name="payment_method" value="bank_transfer">
                            <button type="submit" class="button" style="align-self: flex-end;">
                                {{ $order->settlement_proof ? 'Perbarui Bukti Pelunasan' : 'Unggah Bukti Pelunasan' }}
                            </button>
                        </div>
                    </form>
                </div>
            @endif
        </div>
    @endif

    {{-- Grid 2 Kolom: Item Pesanan & Alamat Pengiriman --}}
    <div class="order-grid-details">
        {{-- Sisi Kiri: Daftar Produk Pesanan --}}
        <div class="panel" style="padding: 22px;">
            <div class="section-head compact" style="margin-bottom: 14px;">
                <div>
                    <span class="eyebrow">RINCIAN BAL</span>
                    <h2 style="font-size:18px;">Daftar Produk Pesanan</h2>
                </div>
                <span class="muted" style="font-size:12px;">Total: {{ $order->items->sum('quantity') }} bal</span>
            </div>

            <div class="table-scroll">
                <table>
                    <thead>
                        <tr>
                            <th>Produk</th>
                            <th>Harga Satuan</th>
                            <th style="text-align:center;">Qty (bal)</th>
                            <th style="text-align:right;">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($order->items as $item)
                            <tr>
                                <td>
                                    <strong>{{ $item->name }}</strong>
                                    @if ($item->product)
                                        <small class="muted" style="display:block;">{{ $item->product->category }}</small>
                                    @endif
                                </td>
                                <td>Rp {{ number_format($item->unit_price, 0, ',', '.') }}</td>
                                <td style="text-align:center;"><strong>{{ $item->quantity }}</strong> bal</td>
                                <td style="text-align:right; font-weight:700;">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Ringkasan Angka --}}
            <div class="order-summary-box">
                <div class="summary-line">
                    <span>Subtotal Produk:</span>
                    <span>Rp {{ number_format($order->total, 0, ',', '.') }}</span>
                </div>
                <div class="summary-line">
                    <span>Ongkos Kirim Kargo ({{ $order->items->sum('quantity') }} bal × Rp 10.000):</span>
                    <span>Rp {{ number_format($order->shipping_cost, 0, ',', '.') }}</span>
                </div>
                <div class="summary-line total-line">
                    <strong>Total Tagihan:</strong>
                    <strong style="color:var(--brown); font-size:18px;">Rp {{ number_format($order->total + $order->shipping_cost, 0, ',', '.') }}</strong>
                </div>
                @if ($order->payment_type === 'dp')
                    <div class="summary-line" style="color:#15803d; font-weight:600;">
                        <span>Uang Muka (DP 65%):</span>
                        <span>- Rp {{ number_format($order->dp, 0, ',', '.') }}</span>
                    </div>
                    <div class="summary-line" style="color:var(--brown); font-weight:700; border-top:1px dashed var(--line); padding-top:6px;">
                        <span>Sisa Pelunasan (35%):</span>
                        <span>Rp {{ number_format($order->remaining, 0, ',', '.') }}</span>
                    </div>
                @endif
            </div>
        </div>

        {{-- Sisi Kanan: Penerima & Metode Pembayaran --}}
        <div style="display:flex; flex-direction:column; gap:18px;">
            <div class="panel" style="padding: 22px;">
                <div class="section-head compact" style="margin-bottom: 12px;">
                    <div>
                        <span class="eyebrow">PENGIRIMAN</span>
                        <h2 style="font-size:18px;">Tujuan Pengiriman</h2>
                    </div>
                </div>
                <div class="meta-list">
                    <div>
                        <span class="muted" style="font-size:11px; text-transform:uppercase;">Nama Penerima</span>
                        <strong style="display:block; color:var(--brown-dark);">{{ $order->buyer }}</strong>
                    </div>
                    <div>
                        <span class="muted" style="font-size:11px; text-transform:uppercase;">No. Telepon / WhatsApp</span>
                        <strong style="display:block; color:var(--brown-dark);">{{ $order->phone }}</strong>
                    </div>
                    <div>
                        <span class="muted" style="font-size:11px; text-transform:uppercase;">Alamat Lengkap</span>
                        <p style="margin:2px 0 0; color:#4a3b31; font-size:13px; line-height:1.5;">{{ $order->address }}</p>
                    </div>
                    <div>
                        <span class="muted" style="font-size:11px; text-transform:uppercase;">Kota & Provinsi</span>
                        <strong style="display:block; color:var(--brown-dark);">{{ $order->city }}, {{ $order->province }}</strong>
                    </div>
                </div>
            </div>

            <div class="panel" style="padding: 22px;">
                <div class="section-head compact" style="margin-bottom: 12px;">
                    <div>
                        <span class="eyebrow">PEMBAYARAN</span>
                        <h2 style="font-size:18px;">Rincian Pembayaran</h2>
                    </div>
                </div>
                <div class="meta-list">
                    <div>
                        <span class="muted" style="font-size:11px; text-transform:uppercase;">Tipe Pembayaran</span>
                        <strong style="display:block; color:var(--brown-dark);">
                            {{ $order->payment_type === 'dp' ? 'Uang Muka 65% (DP)' : 'Pembayaran Lunas (100%)' }}
                        </strong>
                    </div>
                    <div>
                        <span class="muted" style="font-size:11px; text-transform:uppercase;">Metode</span>
                        <strong style="display:block; color:var(--brown-dark);">
                            {{ strtoupper($order->payment_method ?: 'Transfer Bank') }}
                        </strong>
                    </div>
                    @if ($order->payment_proof)
                        <div>
                            <span class="muted" style="font-size:11px; text-transform:uppercase;">Bukti Pembayaran Awal</span>
                            <a href="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($order->payment_proof) }}" target="_blank" rel="noopener noreferrer" class="text-link" style="display:block; margin-top:3px; font-weight:600;">
                                📄 Buka Bukti Transfer DP/Lunas →
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
