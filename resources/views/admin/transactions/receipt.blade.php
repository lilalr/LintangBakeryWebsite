<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Struk #{{ $transaction->id }} - Lintang Bakery</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Courier+Prime:ital,wght@0,400;0,700;1,400&family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --receipt-width: 58mm;
            --font-family: 'Courier Prime', monospace;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            background-color: #eef2f6;
            color: #111;
            font-family: 'Plus Jakarta Sans', sans-serif;
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 24px 12px 48px;
            min-height: 100vh;
        }

        /* Action bar outside receipt */
        .action-bar {
            width: 100%;
            max-width: 440px;
            display: flex;
            gap: 10px;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            padding: 10px 16px;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 700;
            cursor: pointer;
            text-decoration: none;
            border: 1px solid transparent;
            transition: all 0.15s ease;
        }

        .btn-primary {
            background: #8b4513;
            color: #fff;
            border-color: #72380f;
            box-shadow: 0 2px 4px rgba(139, 69, 19, 0.2);
        }

        .btn-primary:hover {
            background: #72380f;
        }

        .btn-secondary {
            background: #fff;
            color: #4b5563;
            border-color: #d1d5db;
        }

        .btn-secondary:hover {
            background: #f3f4f6;
        }

        /* Receipt Canvas */
        .receipt-container {
            width: 320px;
            background: #fff;
            padding: 20px 18px 24px;
            box-shadow: 0 4px 14px rgba(0, 0, 0, 0.08), 0 1px 3px rgba(0,0,0,0.05);
            font-family: var(--font-family);
            font-size: 12px;
            line-height: 1.35;
            color: #111;
            border-radius: 4px;
            position: relative;
        }

        .receipt-header {
            text-align: center;
            margin-bottom: 12px;
        }

        .receipt-title {
            font-size: 16px;
            font-weight: 700;
            letter-spacing: 0.5px;
            text-transform: uppercase;
            margin-bottom: 2px;
        }

        .receipt-subtitle {
            font-size: 10.5px;
            color: #444;
            margin-bottom: 2px;
        }

        .receipt-contact {
            font-size: 10px;
            color: #555;
        }

        .divider {
            border-top: 1px dashed #444;
            margin: 10px 0;
        }

        .divider-double {
            border-top: 2px dashed #222;
            margin: 10px 0;
        }

        .meta-row {
            display: flex;
            justify-content: space-between;
            font-size: 11px;
            margin-bottom: 3px;
        }

        .item-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 11.5px;
            margin-top: 4px;
        }

        .item-name {
            font-weight: 700;
            padding-top: 6px;
            padding-bottom: 2px;
            word-break: break-word;
        }

        .item-calc {
            display: flex;
            justify-content: space-between;
            padding-bottom: 5px;
            font-size: 11px;
        }

        .summary-row {
            display: flex;
            justify-content: space-between;
            font-size: 12px;
            margin-bottom: 4px;
        }

        .summary-total {
            display: flex;
            justify-content: space-between;
            font-size: 14px;
            font-weight: 700;
            margin: 6px 0;
            padding: 6px 0;
            border-top: 1px solid #111;
            border-bottom: 1px solid #111;
        }

        .payment-notes {
            background: #fafafa;
            border: 1px dashed #bbb;
            padding: 6px;
            font-size: 10px;
            margin-top: 8px;
            border-radius: 4px;
            line-height: 1.3;
        }

        .receipt-footer {
            text-align: center;
            margin-top: 14px;
            font-size: 10px;
            color: #444;
            line-height: 1.4;
        }

        .receipt-footer strong {
            display: block;
            font-size: 11px;
            color: #111;
            margin-bottom: 3px;
        }

        /* PRINT STYLES */
        @media print {
            body {
                background: #fff;
                padding: 0;
                margin: 0;
                display: block;
            }

            .action-bar {
                display: none !important;
            }

            .receipt-container {
                width: 100% !important;
                max-width: 58mm;
                box-shadow: none !important;
                padding: 4mm 2mm;
                border-radius: 0;
                margin: 0 auto;
            }

            @page {
                size: 58mm auto;
                margin: 0;
            }
        }
    </style>
</head>
<body>

    <div class="action-bar">
        <a href="{{ route('admin.transactions.index', ['tab' => 'riwayat']) }}" class="btn btn-secondary">
            &larr; Kembali ke Kasir
        </a>
        <div style="display: flex; gap: 8px;">
            <button onclick="window.print()" class="btn btn-primary">
                <svg style="width:16px; height:16px; fill:none; stroke:currentColor; stroke-width:2;" viewBox="0 0 24 24"><polyline points="6 9 6 2 18 2 18 9"></polyline><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path><rect x="6" y="14" width="12" height="8"></rect></svg>
                Cetak Struk
            </button>
        </div>
    </div>

    <div class="receipt-container">
        {{-- Header Toko --}}
        <div class="receipt-header">
            <h1 class="receipt-title">LINTANG BAKERY</h1>
            <div class="receipt-subtitle">Pabrik & Toko Roti Grosir</div>
            <div class="receipt-contact">
                Jl. Raya Solo - Yogya, Karanganyar<br>
                Telp / WA: 0812-3456-7890
            </div>
        </div>

        <div class="divider"></div>

        {{-- Meta Data Transaksi --}}
        <div class="meta-row">
            <span>No. Struk</span>
            <strong>#TRX-{{ str_pad($transaction->id, 5, '0', STR_PAD_LEFT) }}</strong>
        </div>
        <div class="meta-row">
            <span>Tanggal</span>
            <span>{{ \Carbon\Carbon::parse($transaction->date)->format('d/m/Y') }} {{ $transaction->created_at ? $transaction->created_at->format('H:i') : '' }}</span>
        </div>
        <div class="meta-row">
            <span>Kasir</span>
            <span>{{ auth()->user()?->name ?? 'Kasir Utama' }}</span>
        </div>
        <div class="meta-row">
            <span>Pelanggan</span>
            <span>{{ $transaction->buyer_name }}</span>
        </div>

        <div class="divider"></div>

        {{-- Daftar Barang --}}
        <div class="item-list">
            @php $totalQty = 0; @endphp
            @foreach ($transaction->items as $item)
                @php $totalQty += $item->quantity; @endphp
                <div class="item-name">{{ $item->name }}</div>
                <div class="item-calc">
                    <span>{{ $item->quantity }} bal &times; {{ number_format($item->unit_price, 0, ',', '.') }}</span>
                    <strong>Rp {{ number_format($item->subtotal, 0, ',', '.') }}</strong>
                </div>
            @endforeach
        </div>

        <div class="divider"></div>

        {{-- Perhitungan & Pembayaran --}}
        <div class="summary-row">
            <span>Total Item</span>
            <span>{{ $totalQty }} bal</span>
        </div>
        <div class="summary-total">
            <span>TOTAL TAGIHAN</span>
            <span>Rp {{ number_format($transaction->total, 0, ',', '.') }}</span>
        </div>
        <div class="summary-row">
            <span>Metode Bayar</span>
            <strong style="text-transform: uppercase;">
                {{ $transaction->payment_method === 'cash' ? 'TUNAI' : strtoupper($transaction->payment_method) }}
            </strong>
        </div>

        @if (!empty($transaction->notes))
            <div class="payment-notes">
                {{ $transaction->notes }}
            </div>
        @endif

        <div class="divider-double"></div>

        {{-- Footer --}}
        <div class="receipt-footer">
            <strong>Terima Kasih Atas Kunjungan Anda</strong>
            Selamat Berjualan Kembali!<br>
            Barang yang dibeli telah diperiksa dengan baik.<br>
            <em>Simpan struk ini sebagai bukti pembayaran.</em>
        </div>
    </div>

    <script>
        // Auto trigger print when loaded if parameter print=1 is present
        if (new URLSearchParams(window.location.search).get('print') === '1') {
            window.addEventListener('load', () => {
                setTimeout(() => window.print(), 300);
            });
        }
    </script>
</body>
</html>
