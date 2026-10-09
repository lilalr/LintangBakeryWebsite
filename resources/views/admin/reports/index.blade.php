@extends('layouts.admin')

@section('title', 'Laporan Akuntansi')
@section('section', 'Laporan')

@section('content')
<div class="content-heading">
    <div><span class="eyebrow">AKUNTANSI & KEUANGAN</span><h1>Laporan akuntansi</h1><p>Rekap omzet, HPP, laba kotor, dan performa penjualan produk.</p></div>
    <a class="button button-light" href="{{ route('admin.reports.export', request()->query()) }}">Unduh Excel (CSV)</a>
</div>

<section class="panel report-filter">
    <form method="get" action="{{ route('admin.reports.index') }}" class="report-filter-form">
        <label>Bulan &amp; tahun<input type="month" name="period" value="{{ $filters['period'] ?? now()->format('Y-m') }}"></label>
        <span class="filter-divider">atau rentang tanggal</span>
        <label>Dari tanggal<input type="date" name="start_date" value="{{ $filters['start_date'] ?? '' }}"></label>
        <label>Sampai tanggal<input type="date" name="end_date" value="{{ $filters['end_date'] ?? '' }}"></label>
        <button class="button" type="submit">Tampilkan</button>
    </form>
    <p class="report-period">Periode laporan: {{ \Illuminate\Support\Carbon::parse($startDate)->format('d/m/Y') }} – {{ \Illuminate\Support\Carbon::parse($endDate)->format('d/m/Y') }}</p>
</section>

<div class="stats-grid accounting-stats section-gap">
    <article class="stat-card"><span>Total pendapatan / omzet</span><strong>Rp {{ number_format($totalRevenue, 2, ',', '.') }}</strong><small>Online Rp {{ number_format($onlineRevenue, 2, ',', '.') }} · Offline Rp {{ number_format($offlineRevenue, 2, ',', '.') }}</small></article>
    <article class="stat-card"><span>Total HPP</span><strong>Rp {{ number_format($totalHpp, 2, ',', '.') }}</strong><small>HPP item terjual pada periode ini</small></article>
    <article class="stat-card"><span>Laba kotor</span><strong>Rp {{ number_format($grossProfit, 2, ',', '.') }}</strong><small>Pendapatan dikurangi HPP</small></article>
</div>

<section class="panel">
    <div class="panel-heading"><div><h2>Penjualan &amp; penerimaan harian</h2><p>Pendapatan dikelompokkan per tanggal; rincian metode pembayaran menunjukkan nilai yang diterima.</p></div></div>
    <div class="table-scroll"><table><thead><tr><th>Tanggal</th><th>Omzet online</th><th>Omzet offline</th><th>Total omzet</th><th>Kas tunai</th><th>QRIS toko</th><th>Transfer online</th><th>QRIS online</th><th>Online lainnya</th></tr></thead><tbody>
    @forelse ($dailyBreakdown as $row)
        <tr><td>{{ \Illuminate\Support\Carbon::parse($row['date'])->format('d/m/Y') }}</td><td>Rp {{ number_format($row['online'], 2, ',', '.') }}</td><td>Rp {{ number_format($row['offline'], 2, ',', '.') }}</td><td><strong>Rp {{ number_format($row['online'] + $row['offline'], 2, ',', '.') }}</strong></td><td>Rp {{ number_format($row['cash'], 2, ',', '.') }}</td><td>Rp {{ number_format($row['store_qris'], 2, ',', '.') }}</td><td>Rp {{ number_format($row['online_bank_transfer'], 2, ',', '.') }}</td><td>Rp {{ number_format($row['online_qris'], 2, ',', '.') }}</td><td>Rp {{ number_format($row['online_other'], 2, ',', '.') }}</td></tr>
    @empty<tr><td colspan="9" class="table-empty">Belum ada penjualan terkonfirmasi pada periode ini.</td></tr>@endforelse
    </tbody></table></div>
</section>

<section class="panel section-gap">
    <div class="panel-heading"><div><h2>Produk terlaris</h2><p>Volume terjual beserta kontribusi omzet dan laba kotor per produk.</p></div></div>
    <div class="product-chart" role="img" aria-label="Grafik volume penjualan produk">
        @forelse ($bestSellers->take(8) as $product)
            <div class="chart-row"><span class="chart-label">{{ $product['name'] }}</span><span class="chart-track"><span class="chart-bar" style="width: {{ round($product['quantity'] / $maxProductQuantity * 100, 1) }}%"></span></span><strong>{{ $product['quantity'] }} bal</strong></div>
        @empty<p class="table-empty">Belum ada produk terjual pada periode ini.</p>@endforelse
    </div>
    <div class="table-scroll"><table><thead><tr><th>Produk</th><th>Volume</th><th>Kontribusi omzet</th><th>Laba kotor produk</th></tr></thead><tbody>
    @forelse ($bestSellers as $product)
        <tr><td>{{ $product['name'] }}</td><td>{{ $product['quantity'] }} bal</td><td>Rp {{ number_format($product['revenue'], 2, ',', '.') }}</td><td>Rp {{ number_format($product['profit'], 2, ',', '.') }}</td></tr>
    @empty<tr><td colspan="4" class="table-empty">Belum ada data produk terjual.</td></tr>@endforelse
    </tbody></table></div>
</section>

<section class="panel section-gap">
    <div class="panel-heading"><div><h2>Rincian pesanan online</h2><p>Semua pesanan pada periode laporan; omzet hanya menghitung DP yang disetujui dan pesanan yang lunas.</p></div></div>
    <div class="table-scroll"><table><thead><tr><th>Kode</th><th>Tanggal</th><th>Pembeli</th><th>Status</th><th>Total pesanan</th></tr></thead><tbody>
    @forelse ($orders as $order)
        <tr><td>{{ $order->code }}</td><td>{{ $order->created_at->format('d/m/Y') }}</td><td>{{ $order->buyer }}</td><td><span class="status-pill">{{ $order->status }}</span></td><td>Rp {{ number_format($order->total + $order->shipping_cost, 2, ',', '.') }}</td></tr>
    @empty<tr><td colspan="5" class="table-empty">Belum ada pesanan online pada periode ini.</td></tr>@endforelse
    </tbody></table></div>
</section>

<section class="panel section-gap">
    <div class="panel-heading"><div><h2>Rincian transaksi offline</h2></div></div>
    <div class="table-scroll"><table><thead><tr><th>Tanggal</th><th>Pembeli</th><th>Produk</th><th>Pembayaran</th><th>Total</th><th>HPP</th><th>Laba</th></tr></thead><tbody>
    @forelse ($transactions as $transaction)
        <tr><td>{{ $transaction->date->format('d/m/Y') }}</td><td>{{ $transaction->buyer_name }}</td><td>{{ $transaction->items->pluck('name')->join(', ') }}</td><td>{{ $transaction->payment_method === 'cash' ? 'Tunai' : 'QRIS' }}</td><td>Rp {{ number_format($transaction->total, 2, ',', '.') }}</td><td>Rp {{ number_format($transaction->total_hpp, 2, ',', '.') }}</td><td>Rp {{ number_format($transaction->total_laba, 2, ',', '.') }}</td></tr>
    @empty<tr><td colspan="7" class="table-empty">Belum ada transaksi offline pada periode ini.</td></tr>@endforelse
    </tbody></table></div>
</section>
@endsection
