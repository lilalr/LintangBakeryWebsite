<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Surat Jalan #{{ $order->code }} · Lintang Bakery</title>
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
            margin-bottom: 22px;
            padding: 14px 16px;
            background: #faf8f5;
            border: 1px solid #e8ded4;
            border-radius: 6px;
        }
        .meta-col div {
            margin-bottom: 6px;
        }
        .meta-col div:last-child { margin-bottom: 0; }
        .meta-label {
            font-size: 10.5px;
            text-transform: uppercase;
            color: #777;
            font-weight: bold;
            display: inline-block;
            width: 110px;
        }
        .table-items {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
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
        .notes-box {
            font-size: 11.5px;
            color: #555;
            border: 1px dashed #d5c8be;
            padding: 10px 14px;
            border-radius: 6px;
            margin-bottom: 28px;
            background: #fffdfb;
        }
        .signatures {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 16px;
            text-align: center;
            margin-top: 10px;
        }
        .sig-box {
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            height: 110px;
        }
        .sig-box span { font-size: 11.5px; color: #444; }
        .sig-box strong { font-size: 12px; border-top: 1px solid #666; padding-top: 4px; display: block; margin: 0 10px; }
        
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
    <button class="btn-act" onclick="window.print()">🖨️ Cetak Surat Jalan</button>
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
            <h2>SURAT JALAN</h2>
            <span>NO: SJ/{{ now()->format('Ymd') }}/{{ $order->code }}</span>
        </div>
    </div>

    <div class="meta-grid">
        <div class="meta-col">
            <div><span class="meta-label">Tanggal:</span> {{ now()->translatedFormat('d F Y') }}</div>
            <div><span class="meta-label">No. Pesanan:</span> <strong>#{{ $order->code }}</strong></div>
            <div><span class="meta-label">Metode Kirim:</span> <strong>Diantar Langsung oleh Toko</strong></div>
            <div><span class="meta-label">Sopir / Armada:</span> {{ $order->courier_name ?: 'Kurir / Armada Lintang Bakery' }}</div>
        </div>
        <div class="meta-col">
            <div><span class="meta-label">Penerima:</span> <strong>{{ $order->buyer }}</strong> ({{ $order->phone }})</div>
            <div><span class="meta-label">Alamat Kirim:</span> {{ $order->address }}</div>
            <div><span class="meta-label">Tujuan:</span> {{ $order->city }}, {{ $order->province }}</div>
            @if ($order->shipping_notes)
                <div><span class="meta-label">Catatan:</span> {{ $order->shipping_notes }}</div>
            @endif
        </div>
    </div>

    <table class="table-items">
        <thead>
            <tr>
                <th style="width: 35px;" class="text-center">No</th>
                <th>Nama Produk Camilan / Kue</th>
                <th>Kategori Kemasan</th>
                <th style="width: 100px;" class="text-center">Kuantitas</th>
                <th style="width: 100px;" class="text-center">Koli/Karton</th>
                <th style="width: 120px;">Kondisi Barang</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($order->items as $idx => $item)
                <tr>
                    <td class="text-center">{{ $idx + 1 }}</td>
                    <td><strong>{{ $item->name }}</strong></td>
                    <td>Bal Grosir (@ 50/100 bal)</td>
                    <td class="text-center"><strong>{{ $item->quantity }}</strong> bal</td>
                    <td class="text-center">{{ ceil($item->quantity / 2) }} dus karton</td>
                    <td>Baik & Tersegel</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr style="background:#faf8f5; font-weight:bold;">
                <td colspan="3" class="text-right">TOTAL PENGIRIMAN:</td>
                <td class="text-center">{{ $order->items->sum('quantity') }} bal</td>
                <td class="text-center">{{ ceil($order->items->sum('quantity') / 2) }} dus karton</td>
                <td>-</td>
            </tr>
        </tfoot>
    </table>

    <div class="notes-box">
        <strong>Ketentuan Serah Terima Barang:</strong><br>
        1. Barang yang tercantum pada surat jalan ini telah diperiksa bersama dalam keadaan baik, rapi, dan tersegel kardus tebal.<br>
        2. Mohon diperiksa kembali kuantitas koli dan bal saat serah terima barang di lokasi sebelum menandatangani bukti terima.<br>
        3. Klaim kekurangan atau kerusakan wajib disertakan video unboxing maksimal 1x24 jam sejak barang diterima.
    </div>

    <div class="signatures">
        <div class="sig-box">
            <span>Disiapkan Oleh (Gudang):</span>
            <strong>( Bagian Pengemasan )</strong>
        </div>
        <div class="sig-box">
            <span>Pengemudi / Kurir Toko:</span>
            <strong>( {{ $order->courier_name ?: 'Armada Toko' }} )</strong>
        </div>
        <div class="sig-box">
            <span>Diterima Oleh (Pembeli):</span>
            <strong>( {{ $order->buyer }} )</strong>
        </div>
    </div>
</div>

</body>
</html>
