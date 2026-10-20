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
    <div class="alert alert-warning">
        <strong>Peringatan Stok Menipis:</strong>
        @foreach ($lowStock as $lp)
            <span>{{ $lp->name }} (sisa {{ $lp->stock }} bal &le; batas {{ $lp->min_stock }} bal){{ ! $loop->last ? ', ' : '' }}</span>
        @endforeach
        — <a href="{{ route('admin.stock.index') }}">Buka Manajemen Stok &rarr;</a>
    </div>
@endif

@if (isset($expiringSoon) && $expiringSoon->isNotEmpty())
    <div class="alert alert-error">
        <strong>Peringatan Kedaluwarsa:</strong>
        @foreach ($expiringSoon as $em)
            <span>{{ $em->product?->name }} (Batch {{ $em->reference_number }}, Exp: {{ $em->expiry_date?->format('d/m/Y') }}){{ ! $loop->last ? ', ' : '' }}</span>
        @endforeach
        — Prioritaskan penjualan atau pisahkan stok ini. <a href="{{ route('admin.stock.index') }}#kartu-stok">Lihat Mutasi &rarr;</a>
    </div>
@endif
<section class="panel">
    <div class="panel-heading"><div><h2>Pesanan terbaru</h2><p>Perbarui status untuk memberi kabar kepada user.</p></div><a class="text-link" href="{{ route('admin.orders.index') }}">Semua pesanan →</a></div>
    @include('admin.orders.table', ['compact' => true])
</section>
@endsection
