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
        <label>Alamat lengkap
            <textarea name="address" rows="3" required>{{ old('address', auth()->user()->address) }}</textarea>
        </label>
        <div class="form-row">
            <label>Kota / kabupaten
                <input name="city" value="{{ old('city') }}" required>
            </label>
            <label>Provinsi
                <input name="province" value="{{ old('province') }}" required>
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

        {{-- BOX 1: TRANSFER BANK (NOMOR REKENING RESMI) --}}
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
                    {{-- Rekening BCA --}}
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

                    {{-- Rekening BRI --}}
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

                    {{-- Rekening Mandiri --}}
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

        {{-- Bukti Pembayaran --}}
        <div class="upload-proof-box">
            <label for="payment_proof">Bukti pembayaran (opsional)
                <input type="file" id="payment_proof" name="payment_proof" accept="image/*" onchange="handleProofPreview(event)">
            </label>
            <div id="fileUploadPreview" class="file-upload-preview">
                <svg style="width:18px;height:18px;stroke:var(--brown);fill:none;stroke-width:2;" viewBox="0 0 24 24"><path d="M21.44 11.05l-9.19 9.19a6 6 0 0 1-8.49-8.49l9.19-9.19a4 4 0 0 1 5.66 5.66l-9.2 9.19a2 2 0 0 1-2.83-2.83l8.49-8.48"/></svg>
                <span id="fileUploadName" style="flex:1; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; font-weight:600;"></span>
                <span style="color:#28a745; font-size:11px; font-weight:700;">File dipilih</span>
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

        {{-- Highlight Tagihan saat ini --}}
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

    // CRC16-CCITT implementation for EMVCo standard QRIS
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

    function updateDynamicQris(amount) {
        const amtStr = amount.toString();
        const amtTag = '54' + ('0' + amtStr.length).slice(-2) + amtStr;
        const payloadPrefix = '00020101021226600016ID.LINKAJA.WWW0118936009110020261014520454995303360' + amtTag + '5802ID5914LINTANG BAKERY6010YOGYAKARTA6304';
        const finalPayload = payloadPrefix + calculateCrc16(payloadPrefix);
        const qrUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=250x250&margin=8&data=' + encodeURIComponent(finalPayload);

        const img = document.getElementById('qrisCheckoutImage');
        if (img) img.src = qrUrl;

        const btnDownload = document.getElementById('btnDownloadQris');
        if (btnDownload) btnDownload.href = qrUrl;
    }

    function refreshPaymentUI() {
        const type = getActivePaymentType();
        const amount = getActiveAmount();
        const method = document.getElementById('paymentMethodSelect').value;
        const formattedAmount = 'Rp ' + amount.toLocaleString('id-ID');

        // Update Transfer Bank Box
        const bankAmtEl = document.getElementById('bankTransferAmount');
        if (bankAmtEl) bankAmtEl.textContent = formattedAmount;

        // Update QRIS Box
        const qrisAmtEl = document.getElementById('qrisAmountDisplay');
        if (qrisAmtEl) qrisAmtEl.textContent = formattedAmount;

        const qrisTypeEl = document.getElementById('qrisTypeDisplay');
        if (qrisTypeEl) {
            qrisTypeEl.textContent = type === 'dp' ? 'Pembayaran Uang Muka 65% (DP)' : 'Pembayaran Lunas';
        }

        updateDynamicQris(amount);

        // Toggle sections based on selected payment method
        const bankBox = document.getElementById('bankTransferSection');
        const qrisBox = document.getElementById('qrisSection');
        if (method === 'qris') {
            if (bankBox) bankBox.style.display = 'none';
            if (qrisBox) qrisBox.style.display = 'block';
        } else {
            if (bankBox) bankBox.style.display = 'block';
            if (qrisBox) qrisBox.style.display = 'none';
        }

        // Toggle summary highlight
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

    // Initialize UI on page load
    document.addEventListener('DOMContentLoaded', function() {
        refreshPaymentUI();
    });
</script>
@endsection
