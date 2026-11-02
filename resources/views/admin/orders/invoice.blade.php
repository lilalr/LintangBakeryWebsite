<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Faktur Penjualan #{{ $order->code }} · Lintang Bakery</title>
    <style>
        * { box-sizing: border-box; }
        body {
            font-family: Arial, Helvetica, sans-serif;
            color: #222;
            background: #f7f7f7;
            margin: 0;
            padding: 24px;
            font-size: 13px;
            line-height: 1.4;
        }
        .page-wrap {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            padding: 32px 36px;
            border-radius: 8px;
            box-shadow: 0 4px 16px rgba(0,0,0,0.06);
        }
        .kop {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            border-bottom: 2.5px solid #2b1d14;
            padding-bottom: 14px;
            margin-bottom: 18px;
        }
        .brand-title {
            font-size: 24px;
            font-weight: 800;
            color: #5c351f;
            letter-spacing: .02em;
            margin: 0 0 4px;
            font-family: Georgia, serif;
        }
        .brand-sub {
            font-size: 11px;
            color: #555;
            margin: 0;
            line-height: 1.4;
        }
        .doc-title {
            text-align: right;
        }
        .doc-title h2 {
            font-size: 18px;
            margin: 0 0 4px;
            color: #2b1d14;
            letter-spacing: .05em;
        }
        .doc-title span {
            font-size: 12px;
            color: #666;
            font-weight: bold;
        }
        .meta-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 20px;
            padding: 14px 16px;
            background: #faf8f5;
            border: 1px solid #e8ded4;
            border-radius: 6px;
        }
        .meta-col div { margin-bottom: 6px; }
        .meta-col div:last-child { margin-bottom: 0; }
        .meta-label {
            font-size: 10.5px;
            text-transform: uppercase;
            color: #777;
            font-weight: bold;
            display: inline-block;
            width: 120px;
        }
        .table-items {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 16px;
        }
        .table-items th, .table-items td {
            border: 1px solid #dcd4cc;
            padding: 8px 10px;
            font-size: 12px;
        }
        .table-items th {
            background: #f2ece6;
            color: #3b281c;
            text-align: left;
            font-weight: 700;
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .calc-box {
            display: flex;
            justify-content: flex-end;
            margin-bottom: 24px;
        }
        .calc-table {
            width: 320px;
            border-collapse: collapse;
        }
        .calc-table td {
            padding: 4px 8px;
            font-size: 12.5px;
        }
        .calc-table tr.total-row td {
            border-top: 1.5px solid #2b1d14;
            border-bottom: 1.5px solid #2b1d14;
            font-weight: bold;
            font-size: 14px;
            padding: 6px 8px;
            color: #5c351f;
        }
        .bank-info {
            background: #fbf9f6;
            border: 1px solid #e2d7cc;
            border-radius: 6px;
            padding: 10px 14px;
            font-size: 11.5px;
            color: #555;
            margin-bottom: 24px;
        }
        .signatures {
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
            margin-top: 16px;
        }
        .sig-box {
            text-align: center;
            width: 200px;
            height: 100px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }
        .sig-box span { font-size: 11.5px; color: #444; }
        .sig-box strong { font-size: 12px; border-top: 1px solid #666; padding-top: 4px; display: block; }
        
        .action-bar {
            position: fixed;
            bottom: 20px;
            left: 50%;
            transform: translateX(-50%);
            display: flex;
            gap: 10px;
            background: rgba(35, 23, 16, 0.9);
            padding: 10px 18px;
            border-radius: 99px;
            box-shadow: 0 8px 24px rgba(0,0,0,0.25);
            z-index: 999;
        }
        .btn-act {
            border: 0;
            background: #a47754;
            color: white;
            padding: 8px 16px;
            border-radius: 99px;
            cursor: pointer;
            font-weight: bold;
            font-size: 12px;
        }
        .btn-act:hover { background: #8b6243; }
        .btn-close { background: #555; }
        .btn-close:hover { background: #444; }

        @media print {
            body { background: white; padding: 0; }
            .page-wrap { box-shadow: none; padding: 0; }
            .action-bar { display: none !important; }
        }
    </style>
</head>
<body>

<div class="action-bar">
    <button class="btn-act" onclick="window.print()">🖨️ Cetak Faktur</button>
    <button class="btn-act btn-close" onclick="window.close()">Tutup</button>
</div>

<div class="page-wrap">
    <div class="kop">
        <div>
            <h1 class="brand-title">LINTANG BAKERY</h1>
            <p class="brand-sub">
                Sentra Aneka Camilan Tradisional & Kue Kering Bal Grosir<br>
                Jl. Raya Lintang No. 12, Sleman, D.I. Yogyakarta | Telp/WA: 0812-3456-7890
            </p>
        </div>
        <div class="doc-title">
            <h2>FAKTUR PENJUALAN</h2>
            <span>NO: INV/{{ now()->format('Ymd') }}/{{ $order->code }}</span>
        </div>
    </div>

    <div class="meta-grid">
        <div class="meta-col">
            <div><span class="meta-label">Tanggal Faktur:</span> {{ now()->translatedFormat('d F Y') }}</div>
            <div><span class="meta-label">No. Pesanan:</span> <strong>#{{ $order->code }}</strong></div>
            <div><span class="meta-label">Skema Bayar:</span> {{ $order->payment_type === 'dp' ? 'Uang Muka 65% (DP)' : 'Lunas Penuh (100%)' }}</div>
            <div><span class="meta-label">Metode Pembayaran:</span> {{ strtoupper($order->payment_method ?: 'Transfer Bank') }}</div>
            <div><span class="meta-label">Status Pesanan:</span> <strong>{{ $order->status }}</strong></div>
        </div>
        <div class="meta-col">
            <div><span class="meta-label">Ditagihkan Kepada:</span> <strong>{{ $order->buyer }}</strong></div>
            <div><span class="meta-label">No. Telepon / WA:</span> {{ $order->phone }}</div>
            <div><span class="meta-label">Alamat Lengkap:</span> {{ $order->address }}</div>
            <div><span class="meta-label">Kota / Provinsi:</span> {{ $order->city }}, {{ $order->province }}</div>
        </div>
    </div>

    <table class="table-items">
        <thead>
            <tr>
                <th style="width: 35px;" class="text-center">No</th>
                <th>Nama Produk Camilan</th>
                <th style="width: 90px;" class="text-center">Qty (bal)</th>
                <th style="width: 120px;" class="text-right">Harga Satuan</th>
                <th style="width: 140px;" class="text-right">Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($order->items as $idx => $item)
                <tr>
                    <td class="text-center">{{ $idx + 1 }}</td>
                    <td><strong>{{ $item->name }}</strong></td>
                    <td class="text-center">{{ $item->quantity }} bal</td>
                    <td class="text-right">Rp {{ number_format($item->unit_price, 0, ',', '.') }}</td>
                    <td class="text-right">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="calc-box">
        <table class="calc-table">
            <tr>
                <td>Subtotal Produk:</td>
                <td class="text-right">Rp {{ number_format($order->total, 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td>Ongkos Kirim Kargo:</td>
                <td class="text-right">Rp {{ number_format($order->shipping_cost, 0, ',', '.') }}</td>
            </tr>
            <tr class="total-row">
                <td>Total Tagihan:</td>
                <td class="text-right">Rp {{ number_format($order->total + $order->shipping_cost, 0, ',', '.') }}</td>
            </tr>
            @if ($order->payment_type === 'dp')
                <tr style="color: #15803d; font-weight:600;">
                    <td>Uang Muka (DP 65%):</td>
                    <td class="text-right">- Rp {{ number_format($order->dp, 0, ',', '.') }}</td>
                </tr>
                <tr style="color: #b45309; font-weight:bold;">
                    <td>Sisa Pelunasan (35%):</td>
                    <td class="text-right">Rp {{ number_format($order->remaining, 0, ',', '.') }}</td>
                </tr>
            @endif
        </table>
    </div>

    <div class="bank-info">
        <strong>Pembayaran Rekening Resmi Lintang Bakery:</strong><br>
        • BCA: <b>8830-123-456</b> a.n. Lintang Bakery<br>
        • BRI: <b>0123-01-000456-50-1</b> a.n. Lintang Bakery<br>
        • Mandiri: <b>137-00-1234567-8</b> a.n. Lintang Bakery
    </div>

    <div class="signatures">
        <div class="sig-box">
            <span>Penerima / Pembeli:</span>
            <strong>( {{ $order->buyer }} )</strong>
        </div>
        <div class="sig-box">
            <span>Bagian Keuangan:</span>
            <strong>( Kasir / Admin Toko )</strong>
        </div>
    </div>
</div>

</body>
</html>
