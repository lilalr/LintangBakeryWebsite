@extends('layouts.admin')

@section('title', 'Laporan')
@section('section', 'Laporan')

@section('content')
<div class="content-heading"><div><span class="eyebrow">RINGKASAN PENJUALAN</span><h1>Laporan</h1><p>Rekap penjualan dari pesanan online dan transaksi toko offline.</p></div><button class="button button-light" onclick="window.print()">Cetak laporan</button></div>
<div class="stats-grid two-stats"><article class="stat-card"><span>Penjualan online selesai</span><strong>Rp {{ number_format($onlineRevenue, 0, ',', '.') }}</strong></article><article class="stat-card"><span>Penjualan offline</span><strong>Rp {{ number_format($offlineRevenue, 0, ',', '.') }}</strong></article></div>
<section class="panel"><div class="panel-heading"><div><h2>Pesanan online</h2><p>Hanya pesanan yang sudah sampai atau lunas dihitung sebagai penjualan.</p></div></div>
    <div class="table-scroll"><table><thead><tr><th>Kode</th><th>Tanggal</th><th>Pembeli</th><th>Status</th><th>Total</th></tr></thead><tbody>
    @forelse ($orders as $order)<tr><td>{{ $order->code }}</td><td>{{ $order->created_at->format('d/m/Y') }}</td><td>{{ $order->buyer }}</td><td><span class="status-pill">{{ $order->status }}</span></td><td>Rp {{ number_format($order->total + $order->shipping_cost, 0, ',', '.') }}</td></tr>
    @empty<tr><td colspan="5" class="table-empty">Belum ada pesanan online.</td></tr>@endforelse
    </tbody></table></div>
</section>
<section class="panel section-gap"><div class="panel-heading"><div><h2>Transaksi offline</h2></div></div>
    <div class="table-scroll"><table><thead><tr><th>Tanggal</th><th>Pembeli</th><th>Produk</th><th>Total</th></tr></thead><tbody>
    @forelse ($transactions as $transaction)<tr><td>{{ $transaction->date->format('d/m/Y') }}</td><td>{{ $transaction->buyer_name }}</td><td>{{ $transaction->items->pluck('name')->join(', ') }}</td><td>Rp {{ number_format($transaction->total, 0, ',', '.') }}</td></tr>
    @empty<tr><td colspan="4" class="table-empty">Belum ada transaksi offline.</td></tr>@endforelse
    </tbody></table></div>
</section>
@endsection
