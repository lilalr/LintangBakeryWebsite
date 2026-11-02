@extends('layouts.store')

@section('title', 'Checkout · Lintang Bakery')

@section('content')
@php
    $subtotal = collect($cart)->sum('subtotal');
    $quantity = collect($cart)->sum('quantity');
    $shipping = $quantity * 10000;
    $total = $subtotal + $shipping;
    $dp = (int) round($subtotal * 0.65);
    $remaining = $total - $dp;
    $defaultType = old('payment_type', 'full');
    $defaultMethod = old('payment_method', 'bank_transfer');
@endphp

<style>
    /* Styling Checkout Grid & Form Card */
    .checkout-grid {
        display: grid;
        grid-template-columns: 1fr 340px;
        gap: 24px;
        margin-top: 20px;
    }
    @media (max-width: 900px) {
        .checkout-grid { grid-template-columns: 1fr; }
    }
    .form-card {
        background: #ffffff;
        border: 1px solid #ebd9c7;
        border-radius: 12px;
        padding: 24px;
    }
    .stack-form label {
        display: block;
        font-weight: 600;
        font-size: 13px;
        margin-bottom: 14px;
        color: #3e2723;
    }
    .stack-form input[type="text"],
    .stack-form textarea,
    .stack-form select {
        width: 100%;
        padding: 10px 12px;
        border: 1px solid #d7ccc8;
        border-radius: 8px;
        margin-top: 6px;
        font-size: 14px;
        outline: none;
        box-sizing: border-box;
    }

    /* Pilihan Payment Type (Full vs DP) */
    .payment-type-group {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 12px;
        margin-bottom: 16px;
    }
    .choice-card {
        border: 1.5px solid #e0d0c1;
        border-radius: 10px;
        padding: 12px 14px;
        display: flex;
        align-items: flex-start;
        gap: 10px;
        cursor: pointer;
        background: #faf7f5;
        transition: all 0.2s;
    }
    .choice-card:has(input:checked) {
        border-color: #8d6e63;
        background: #fff8f5;
        box-shadow: 0 2px 8px rgba(141, 110, 99, 0.15);
    }
    .choice-title { font-weight: 700; font-size: 13.5px; color: #4e342e; }
    .choice-desc { font-size: 11.5px; color: #6d4c41; margin-top: 2px; line-height: 1.4; }

    /* Box Transfer Bank & Rekening */
    .checkout-payment-box {
        background: #fffbf7;
        border: 1px solid #efe0d3;
        border-radius: 12px;
        padding: 18px;
        margin-top: 14px;
    }
    .bank-instruction-head {
        display: flex;
        justify-content: space-between;
        align-items: center;
        border-bottom: 1px dashed #e0d0c1;
        padding-bottom: 12px;
    }
    .nominal-highlight {
        font-size: 16px;
        font-weight: 800;
        color: #8d2b2b;
    }
    .bank-accounts-list {
        display: flex;
        flex-direction: column;
        gap: 10px;
        margin: 12px 0;
    }
    .bank-account-item {
        display: flex;
        align-items: center;
        justify-content: space-between;
        background: #ffffff;
        border: 1px solid #ebd9c7;
        border-radius: 8px;
        padding: 10px 14px;
    }
    .bank-badge {
        font-weight: 800;
        font-size: 11px;
        padding: 4px 8px;
        border-radius: 4px;
        color: #fff;
    }
    .bank-bca { background: #0056b3; }
    .bank-bri { background: #003366; }
    .bank-mandiri { background: #e65100; }
    .bank-info { display: flex; flex-direction: column; flex: 1; margin-left: 12px; }
    .bank-acc-num { font-weight: 700; font-size: 14px; letter-spacing: 0.5px; color: #3e2723; }
    .bank-acc-name { font-size: 11px; color: #795548; }

    /* Tombol Salin Mini */
    .btn-copy-account, .btn-copy-mini {
        background: #efebe9;
        border: 1px solid #d7ccc8;
        border-radius: 6px;
        padding: 5px 10px;
        font-size: 11px;
        font-weight: 600;
        color: #4e342e;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 5px;
    }
    .btn-copy-account:hover { background: #d7ccc8; }

    /* Membatasi Ukuran SVG Icon agar Tidak Meledak/Raksasa */
    .payment-hint {
        display: flex;
        align-items: center;
        gap: 8px;
        background: #fff3e0;
        border-radius: 6px;
        padding: 8px 12px;
        font-size: 11.5px;
        color: #e65100;
        margin-top: 10px;
    }
    .payment-hint svg {
        width: 18px !important;
        height: 18px !important;
        min-width: 18px;
        stroke: #e65100;
    }

    /* Box Tampilan QRIS */
    .qris-checkout-card {
        text-align: center;
        background: #ffffff;
        border: 1px solid #e0e0e0;
        border-radius: 12px;
        padding: 20px;
    }
    .qris-header-badge {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 12px;
    }
    .qris-logo { font-weight: 900; font-size: 18px; color: #c62828; }
    .qris-tag-national { font-size: 9px; font-weight: 700; background: #eee; padding: 2px 6px; border-radius: 4px; }
    .qris-merchant-title { font-weight: 800; font-size: 16px; color: #212121; margin-top: 6px; }
    .qris-merchant-city { font-size: 11px; color: #757575; letter-spacing: 1px; }
    .qris-amount-display { font-size: 22px; font-weight: 800; color: #2e7d32; margin: 8px 0 2px; }
    .qris-qr-container img {
        width: 220px;
        height: 220px;
        object-fit: contain;
        border: 1px solid #e0e0e0;
        border-radius: 8px;
        padding: 6px;
        margin-top: 8px;
    }
    .qris-steps {
        text-align: left;
        font-size: 11.5px;
        color: #616161;
        margin-top: 14px;
        padding-left: 18px;
        line-height: 1.6;
    }

    /* Ringkasan Pesanan (Aside Sidebar) */
    .summary-card {
        background: #ffffff;
        border: 1px solid #ebd9c7;
        border-radius: 12px;
        padding: 20px;
        height: fit-content;
    }
    .summary-line {
        display: flex;
        justify-content: space-between;
        font-size: 13px;
        margin-bottom: 10px;
        color: #4e342e;
    }
</style>

<section class="page-heading">
    <span class="eyebrow">SATU LANGKAH LAGI</span>
    <h1>Checkout pesanan</h1>
    <p>Isi data penerima dan pilih metode pembayaran resmi Lintang Bakery.</p>
</section>

<div class="checkout-grid">
    <form method="post" action="{{ route('checkout.store') }}" enctype="multipart/form-data" class="form-card stack-form">
        @csrf
        <h2>Data penerima</h2>
        <label>Nama lengkap
            <input name="buyer" value="{{ old('buyer', auth()->user()->name) }}" required>
        </label>
        <label>No. telepon
            <input name="phone" value="{{ old('phone', auth()->user()->phone) }}" required>
        </label>
        <label>Alamat lengkap (Nama jalan, RT/RW, No. Toko/Rumah, Patokan)
            <textarea name="address" rows="3" placeholder="Contoh: Jl. Slamet Riyadi No. 120, RT 02 / RW 04, Toko Roti Berkah (depan apotek)" required>{{ old('address', auth()->user()->address) }}</textarea>
        </label>
        <div class="form-row">
            <label>Provinsi
                <select name="province" id="provinceSelect" required onchange="onProvinceChanged()">
                    <option value="">-- Pilih Provinsi Tujuan --</option>
                    @foreach (array_keys($regions) as $prov)
                        <option value="{{ $prov }}" @selected(old('province', 'D.I. Yogyakarta') === $prov)>{{ $prov }}</option>
                    @endforeach
                </select>
            </label>
            <label>Kota / Kabupaten
                <select name="city" id="citySelect" required>
                    <option value="">-- Pilih Kota / Kabupaten --</option>
                </select>
            </label>
        </div>

        <h2 style="margin-top: 10px;">Pembayaran</h2>

        {{-- Pilihan Jenis Pembayaran (Lunas vs DP) --}}
        <div class="payment-type-group">
            <label class="choice-card" for="payment_type_full">
                <input type="radio" id="payment_type_full" name="payment_type" value="full" @checked($defaultType === 'full') onchange="refreshPaymentUI()">
                <div>
                    <div class="choice-title">Lunas di muka</div>
                    <div class="choice-desc">Bayar penuh seluruh pesanan: <strong>Rp {{ number_format($total, 0, ',', '.') }}</strong> (sudah termasuk ongkir).</div>
                </div>
            </label>
            <label class="choice-card" for="payment_type_dp">
                <input type="radio" id="payment_type_dp" name="payment_type" value="dp" @checked($defaultType === 'dp') onchange="refreshPaymentUI()">
                <div>
                    <div class="choice-title">Uang muka 65% (DP)</div>
                    <div class="choice-desc">Bayar DP <strong>Rp {{ number_format($dp, 0, ',', '.') }}</strong> sekarang, sisa pelunasan <strong>Rp {{ number_format($remaining, 0, ',', '.') }}</strong> menyusul saat siap kirim.</div>
                </div>
            </label>
        </div>

        {{-- Dropdown Pilihan Metode Pembayaran --}}
        <label for="paymentMethodSelect">Metode pembayaran
            <select name="payment_method" id="paymentMethodSelect" required onchange="refreshPaymentUI()">
                <option value="bank_transfer" @selected($defaultMethod === 'bank_transfer')>Transfer Bank (BCA, BRI, Mandiri)</option>
                <option value="qris" @selected($defaultMethod === 'qris')>QRIS (Semua Bank & E-Wallet - Otomatis Dinamis)</option>
            </select>
        </label>

        {{-- BOX 1: TRANSFER BANK --}}
        <div id="bankTransferSection" class="checkout-payment-box">
            <div class="bank-instruction-card">
                <div class="bank-instruction-head">
                    <div>
                        <span class="eyebrow" style="font-size: 9px;">TRANSFER REKENING</span>
                        <h4 style="margin: 4px 0 0; font-size: 15px; color: var(--brown-dark);">Rekening Resmi Lintang Bakery</h4>
                    </div>
                    <div class="nominal-tag-wrap">
                        <span class="muted" style="font-size: 10px; display: block;">Jumlah Transfer</span>
                        <span class="nominal-highlight" id="bankTransferAmount">Rp {{ number_format($total, 0, ',', '.') }}</span>
                        <button type="button" id="btnCopyBankNominal" class="btn-copy-mini" onclick="copyAmount(this)">
                            <svg style="width:11px; height:11px; stroke:currentColor; fill:none; stroke-width:2.5;" viewBox="0 0 24 24"><rect x="9" y="9" width="13" height="13" rx="2" ry="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/></svg>
                            <span>Salin Nominal</span>
                        </button>
                    </div>
                </div>

                <p style="font-size: 12px; color: var(--muted); margin: 10px 0 12px; line-height: 1.5;">
                    Silakan transfer tepat sesuai nominal ke salah satu rekening resmi di bawah ini:
                </p>

                <div class="bank-accounts-list">
                    <div class="bank-account-item">
                        <div class="bank-badge bank-bca">BCA</div>
                        <div class="bank-info">
                            <span class="bank-acc-num">8765-4321-0987-6543</span>
                            <span class="bank-acc-name">a.n. PT Lintang Bakery Nusantara</span>
                        </div>
                        <button type="button" class="btn-copy-account" onclick="copyText('8765432109876543', this)">
                            <svg style="width:13px; height:13px; stroke:currentColor; fill:none; stroke-width:2.5;" viewBox="0 0 24 24"><rect x="9" y="9" width="13" height="13" rx="2" ry="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/></svg>
                            <span>Salin No. Rek</span>
                        </button>
                    </div>

                    <div class="bank-account-item">
                        <div class="bank-badge bank-bri">BRI</div>
                        <div class="bank-info">
                            <span class="bank-acc-num">1234-5678-9012-3456</span>
                            <span class="bank-acc-name">a.n. PT Lintang Bakery Nusantara</span>
                        </div>
                        <button type="button" class="btn-copy-account" onclick="copyText('1234567890123456', this)">
                            <svg style="width:13px; height:13px; stroke:currentColor; fill:none; stroke-width:2.5;" viewBox="0 0 24 24"><rect x="9" y="9" width="13" height="13" rx="2" ry="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/></svg>
                            <span>Salin No. Rek</span>
                        </button>
                    </div>

                    <div class="bank-account-item">
                        <div class="bank-badge bank-mandiri">MANDIRI</div>
                        <div class="bank-info">
                            <span class="bank-acc-num">137-00-1234567-8</span>
                            <span class="bank-acc-name">a.n. PT Lintang Bakery Nusantara</span>
                        </div>
                        <button type="button" class="btn-copy-account" onclick="copyText('1370012345678', this)">
                            <svg style="width:13px; height:13px; stroke:currentColor; fill:none; stroke-width:2.5;" viewBox="0 0 24 24"><rect x="9" y="9" width="13" height="13" rx="2" ry="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/></svg>
                            <span>Salin No. Rek</span>
                        </button>
                    </div>
                </div>

                <div class="payment-hint">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>
                    <span>Setelah transfer selesai, silakan upload bukti struk/screenshot pembayaran pada form di bawah.</span>
                </div>
            </div>
        </div>

        {{-- BOX 2: QRIS DINAMIS OTOMATIS --}}
        <div id="qrisSection" class="checkout-payment-box" style="display: none;">
            <div class="qris-checkout-card">
                <div class="qris-header-badge">
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <span class="qris-logo">QRIS</span>
                        <span class="qris-tag-national">STANDAR NASIONAL</span>
                    </div>
                    <span class="qris-nmid">NMID: ID1020261014001</span>
                </div>

                <div class="qris-merchant-title">LINTANG BAKERY</div>
                <div class="qris-merchant-city">YOGYAKARTA</div>

                <div class="qris-amount-wrap">
                    <span class="qris-amount-label">Nominal QRIS Otomatis:</span>
                    <div class="qris-amount-display" id="qrisAmountDisplay">Rp {{ number_format($total, 0, ',', '.') }}</div>
                    <span class="qris-amount-type" id="qrisTypeDisplay">Pembayaran Lunas</span>
                </div>

                <div class="qris-qr-container">
                    <div class="qris-img-wrap">
                        <img id="qrisCheckoutImage" src="" alt="QRIS Dinamis Lintang Bakery">
                    </div>
                    <div style="margin-top: 10px;">
                        <a id="btnDownloadQris" href="#" download="QRIS-Lintang-Bakery.png" target="_blank" class="button button-small button-light" style="display:inline-flex; align-items:center; gap:6px;">
                            <svg style="width:14px;height:14px;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                            <span>Unduh Kode QR</span>
                        </a>
                    </div>
                </div>

                <div class="qris-support-logos">
                    Bisa scan pakai <strong>BCA Mobile, Livin' Mandiri, BRImo, BNI, GoPay, OVO, ShopeePay, DANA</strong>, dll.
                </div>

                <ol class="qris-steps">
                    <li>Buka aplikasi m-Banking atau e-Wallet di smartphone Anda.</li>
                    <li>Pilih menu <strong>Scan / Bayar QRIS</strong> dan scan QR di atas.</li>
                    <li>Nominal pembayaran akan otomatis terkunci sesuai tagihan Anda.</li>
                    <li>Selesaikan pembayaran dan simpan tangkapan layar bukti bayar.</li>
                </ol>
            </div>
        </div>

        {{-- Upload Bukti Pembayaran --}}
        <div class="upload-proof-box">
            <label for="payment_proof" style="display: block; font-weight: 700; color: var(--brown-dark); margin-bottom: 6px;">
                Bukti pembayaran / transfer <span style="color: #c53030; font-weight: 800;">* (Wajib Diunggah)</span>
                <span style="display: block; font-weight: 400; font-size: 12px; color: var(--muted); margin-top: 2px;">
                    Silakan upload screenshot bukti transfer bank atau resi scan QRIS sebelum pesanan dibuat.
                </span>
                <input type="file" id="payment_proof" name="payment_proof" accept="image/*" required onchange="handleProofPreview(event)" style="margin-top: 8px;">
            </label>
            <div id="fileUploadPreview" class="file-upload-preview">
                <svg style="width:18px;height:18px;stroke:var(--brown);fill:none;stroke-width:2;" viewBox="0 0 24 24"><path d="M21.44 11.05l-9.19 9.19a6 6 0 0 1-8.49-8.49l9.19-9.19a4 4 0 0 1 5.66 5.66l-9.2 9.19a2 2 0 0 1-2.83-2.83l8.49-8.48"/></svg>
                <span id="fileUploadName" style="flex:1; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; font-weight:600;"></span>
                <span style="color:#28a745; font-size:11px; font-weight:700;">File siap diunggah ✓</span>
            </div>
        </div>

        <button class="button button-full" type="submit" style="margin-top: 10px;">Buat pesanan</button>
    </form>

    {{-- ASIDE: RINGKASAN PESANAN --}}
    <aside class="summary-card">
        <h2>Ringkasan</h2>
        @foreach ($cart as $row)
            <div class="summary-line">
                <span>{{ $row['product']->name }} × {{ $row['quantity'] }}</span>
                <strong>Rp {{ number_format($row['subtotal'], 0, ',', '.') }}</strong>
            </div>
        @endforeach
        <div class="summary-line">
            <span>Ongkir (Rp 10.000 × jumlah bal)</span>
            <strong>Rp {{ number_format($shipping, 0, ',', '.') }}</strong>
        </div>
        <div class="summary-line" style="border-top: 1.5px solid #ebd9c7; margin-top: 8px; padding-top: 12px;">
            <span>Total keseluruhan</span>
            <strong style="font-size: 14px;">Rp {{ number_format($total, 0, ',', '.') }}</strong>
        </div>

        <div id="summaryPayNowWrap" class="summary-highlight-row" style="{{ $defaultType === 'dp' ? '' : 'display:none;' }}">
            <div class="summary-line" style="padding: 4px 0; border: none; font-size: 12px; font-weight: 700; color: var(--brown-dark);">
                <span>Tagihan Sekarang (DP 65%)</span>
                <strong id="summaryPayNowVal" style="color: #9c3932; font-size: 15px;">Rp {{ number_format($dp, 0, ',', '.') }}</strong>
            </div>
            <div class="summary-line" style="padding: 4px 0; border: none; font-size: 11px; color: var(--muted);">
                <span>Sisa Pelunasan Nanti</span>
                <strong id="summaryRemainingVal">Rp {{ number_format($remaining, 0, ',', '.') }}</strong>
            </div>
        </div>

        <div id="summaryFullWrap" class="summary-highlight-row" style="{{ $defaultType === 'full' ? '' : 'display:none;' }}">
            <div class="summary-line" style="padding: 4px 0; border: none; font-size: 12px; font-weight: 700; color: var(--brown-dark);">
                <span>Total yang Harus Dibayar</span>
                <strong style="color: var(--brown); font-size: 16px;">Rp {{ number_format($total, 0, ',', '.') }}</strong>
            </div>
        </div>
    </aside>
</div>

<script>
    const TOTAL_AMOUNT = {{ (int) $total }};
    const DP_AMOUNT = {{ (int) $dp }};
    const REMAINING_AMOUNT = {{ (int) $remaining }};

    function getActivePaymentType() {
        const checked = document.querySelector('input[name="payment_type"]:checked');
        return checked ? checked.value : 'full';
    }

    function getActiveAmount() {
        return getActivePaymentType() === 'dp' ? DP_AMOUNT : TOTAL_AMOUNT;
    }

    // CRC16-CCITT implementation untuk EMVCo Standard QRIS Dinamis
    function calculateCrc16(data) {
        let crc = 0xFFFF;
        for (let i = 0; i < data.length; i++) {
            crc ^= (data.charCodeAt(i) << 8);
            for (let j = 0; j < 8; j++) {
                if ((crc & 0x8000) !== 0) {
                    crc = ((crc << 1) ^ 0x1021) & 0xFFFF;
                } else {
                    crc = (crc << 1) & 0xFFFF;
                }
            }
        }
        return ('0000' + crc.toString(16).toUpperCase()).slice(-4);
    }

    // Fungsi memanggil API QRIS Midtrans Kasir (server.js)
        async function updateDynamicQris(amount) {
            const img = document.getElementById('qrisCheckoutImage');
            const btnDownload = document.getElementById('btnDownloadQris');
            
            // Tampilkan indikator loading sementara
            if (img) img.style.opacity = '0.3';

            try {
                // Panggil endpoint kasir yang berjalan di port 3000
                const response = await fetch('http://localhost:3000/api/generate_qris', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        order_id: 'LB-ONLINE-' + Date.now(),
                        gross_amount: amount
                    })
                });

                const data = await response.json();

                if (data.qr_url) {
                    // Pasang gambar QRIS dari API Kasir Midtrans
                    if (img) {
                        img.src = data.qr_url;
                        img.style.opacity = '1';
                    }
                    if (btnDownload) btnDownload.href = data.qr_url;
                } else if (data.qr_string) {
                    // Fallback jika API mengembalikan QR string
                    const generatedUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=300x300&margin=10&data=' + encodeURIComponent(data.qr_string);
                    if (img) {
                        img.src = generatedUrl;
                        img.style.opacity = '1';
                    }
                    if (btnDownload) btnDownload.href = generatedUrl;
                }
            } catch (error) {
                console.error('Gagal mengambil QRIS dari server kasir:', error);
                // Fallback jika server.js belum dinyalakan
                if (img) img.style.opacity = '1';
            }
        }

        function refreshPaymentUI() {
            const type = getActivePaymentType();
            const amount = getActiveAmount();
            const method = document.getElementById('paymentMethodSelect').value;
            const formattedAmount = 'Rp ' + amount.toLocaleString('id-ID');

            const bankAmtEl = document.getElementById('bankTransferAmount');
            if (bankAmtEl) bankAmtEl.textContent = formattedAmount;

            const qrisAmtEl = document.getElementById('qrisAmountDisplay');
            if (qrisAmtEl) qrisAmtEl.textContent = formattedAmount;

            const qrisTypeEl = document.getElementById('qrisTypeDisplay');
            if (qrisTypeEl) {
                qrisTypeEl.textContent = type === 'dp' ? 'Pembayaran Uang Muka 65% (DP)' : 'Pembayaran Lunas';
            }

            // Panggil API QRIS Kasir saat metode QRIS dipilih
            if (method === 'qris') {
                updateDynamicQris(amount);
            }

            const bankBox = document.getElementById('bankTransferSection');
            const qrisBox = document.getElementById('qrisSection');
            if (method === 'qris') {
                if (bankBox) bankBox.style.display = 'none';
                if (qrisBox) qrisBox.style.display = 'block';
            } else {
                if (bankBox) bankBox.style.display = 'block';
                if (qrisBox) qrisBox.style.display = 'none';
            }

            const summaryPayNowWrap = document.getElementById('summaryPayNowWrap');
            const summaryFullWrap = document.getElementById('summaryFullWrap');
            if (summaryPayNowWrap && summaryFullWrap) {
                if (type === 'dp') {
                    summaryPayNowWrap.style.display = 'block';
                    summaryFullWrap.style.display = 'none';
                    document.getElementById('summaryPayNowVal').textContent = 'Rp ' + DP_AMOUNT.toLocaleString('id-ID');
                    document.getElementById('summaryRemainingVal').textContent = 'Rp ' + REMAINING_AMOUNT.toLocaleString('id-ID');
                } else {
                    summaryPayNowWrap.style.display = 'none';
                    summaryFullWrap.style.display = 'block';
                }
            }
        }

    function copyText(text, btnEl, successMsg = 'Tersalin!') {
        const setFeedback = () => {
            const originalContent = btnEl.innerHTML;
            btnEl.classList.add('copied');
            btnEl.innerHTML = `<svg style="width:13px;height:13px;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg> <span>${successMsg}</span>`;
            setTimeout(() => {
                btnEl.classList.remove('copied');
                btnEl.innerHTML = originalContent;
            }, 2000);
        };

        if (navigator.clipboard && window.isSecureContext) {
            navigator.clipboard.writeText(text).then(setFeedback).catch(() => fallbackCopy(text, setFeedback));
        } else {
            fallbackCopy(text, setFeedback);
        }
    }

    function fallbackCopy(text, callback) {
        const textarea = document.createElement('textarea');
        textarea.value = text;
        textarea.style.position = 'fixed';
        textarea.style.opacity = '0';
        document.body.appendChild(textarea);
        textarea.select();
        try {
            document.execCommand('copy');
            callback();
        } catch (e) {
            console.error(e);
        }
        document.body.removeChild(textarea);
    }

    function copyAmount(btnEl) {
        const amount = getActiveAmount();
        copyText(amount.toString(), btnEl, 'Tersalin!');
    }

    function handleProofPreview(event) {
        const file = event.target.files[0];
        const preview = document.getElementById('fileUploadPreview');
        const nameEl = document.getElementById('fileUploadName');
        if (file) {
            nameEl.textContent = file.name;
            preview.style.display = 'flex';
        } else {
            preview.style.display = 'none';
        }
    }

    const REGIONS_DATA = @json($regions);
    const OLD_PROVINCE = @json(old('province', 'D.I. Yogyakarta'));
    const OLD_CITY = @json(old('city', 'Kota Yogyakarta'));

    function onProvinceChanged(preserveOldCity = false) {
        const provSelect = document.getElementById('provinceSelect');
        const citySelect = document.getElementById('citySelect');
        const selectedProv = provSelect.value;

        citySelect.innerHTML = '<option value="">-- Pilih Kota / Kabupaten --</option>';

        if (selectedProv && REGIONS_DATA[selectedProv]) {
            citySelect.disabled = false;
            REGIONS_DATA[selectedProv].forEach(city => {
                const opt = document.createElement('option');
                opt.value = city;
                opt.textContent = city;
                citySelect.appendChild(opt);
            });

            if (preserveOldCity && OLD_CITY && REGIONS_DATA[selectedProv].includes(OLD_CITY)) {
                citySelect.value = OLD_CITY;
            }
        } else {
            citySelect.disabled = true;
        }
    }

    function initRegionDropdown() {
        const provSelect = document.getElementById('provinceSelect');
        if (provSelect) {
            if (OLD_PROVINCE && REGIONS_DATA[OLD_PROVINCE]) {
                provSelect.value = OLD_PROVINCE;
            }
            onProvinceChanged(true);
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        initRegionDropdown();
        refreshPaymentUI();
    });
</script>
@endsection