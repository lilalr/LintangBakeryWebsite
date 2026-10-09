@extends('layouts.admin')

@section('title', 'Dashboard Admin')
@section('section', 'Dashboard')

@section('content')
<div class="content-heading"><div><span class="eyebrow">RINGKASAN TOKO</span><h1>Dashboard</h1><p>Ringkasan operasional Lintang Bakery.</p></div><a class="button button-light" href="{{ route('store.home') }}">Lihat toko user ↗</a></div>
<div class="stats-grid">
    <article class="stat-card"><span>Total produk</span><strong>{{ $stats['products'] }}</strong><small>produk terdaftar</small></article>
    <article class="stat-card"><span>Total pesanan</span><strong>{{ $stats['orders'] }}</strong><small>pesanan online</small></article>
    <article class="stat-card"><span>Perlu diproses</span><strong>{{ $stats['pending'] }}</strong><small>menunggu konfirmasi</small></article>
    <article class="stat-card"><span>Total penjualan</span><strong>Rp {{ number_format($stats['sales'], 0, ',', '.') }}</strong><small>pesanan selesai + offline</small></article>
</div>
@if ($lowStock->isNotEmpty())
    <div class="alert alert-warning"><strong>Stok rendah:</strong> {{ $lowStock->pluck('name')->join(', ') }} — kurang dari 50 bal. <a href="{{ route('admin.stock.index') }}">Kelola stok</a></div>
@endif
<section class="panel">
    <div class="panel-heading"><div><h2>Pesanan terbaru</h2><p>Perbarui status untuk memberi kabar kepada user.</p></div><a class="text-link" href="{{ route('admin.orders.index') }}">Semua pesanan →</a></div>
    @include('admin.orders.table', ['compact' => true])
</section>
@endsection
