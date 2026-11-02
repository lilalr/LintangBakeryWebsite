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

{{-- Analytics: 7-Day Trend & Top Selling Products --}}
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 18px; margin-bottom: 24px;">
    {{-- Chart Tren Penjualan --}}
    <section class="panel" style="margin-bottom: 0; display: flex; flex-direction: column; justify-content: space-between;">
        <div>
            <div class="panel-heading" style="margin-bottom: 12px; padding-bottom: 10px; border-bottom: 1px solid #f0e6dd;">
                <div>
                    <h2 style="font-size: 16px; margin-bottom: 2px;">Tren Penjualan 7 Hari Terakhir</h2>
                    <p style="font-size: 12px; color: var(--muted); margin: 0;">Total omzet per hari (online &amp; offline kasir)</p>
                </div>
                <div style="display: flex; gap: 10px; font-size: 11.5px; font-weight: 700;">
                    <span style="display: inline-flex; align-items: center; gap: 5px; color: #8b4513;">
                        <span style="width: 9px; height: 9px; background: #8b4513; border-radius: 2px;"></span> Online
                    </span>
                    <span style="display: inline-flex; align-items: center; gap: 5px; color: #2e7d32;">
                        <span style="width: 9px; height: 9px; background: #2e7d32; border-radius: 2px;"></span> Offline
                    </span>
                </div>
            </div>

            {{-- SVG Chart Container --}}
            <div style="width: 100%; overflow-x: auto; padding-top: 6px;">
                <svg viewBox="0 0 560 170" style="width: 100%; height: auto; min-width: 320px; font-family: inherit;">
                    {{-- Grid Lines --}}
                    <line x1="20" y1="20" x2="540" y2="20" stroke="#f3ebe3" stroke-dasharray="4,4" stroke-width="1" />
                    <line x1="20" y1="75" x2="540" y2="75" stroke="#f3ebe3" stroke-dasharray="4,4" stroke-width="1" />
                    <line x1="20" y1="130" x2="540" y2="130" stroke="#ebdccb" stroke-width="1.5" />

                    @php
                        $chartHeight = 105;
                        $baselineY = 130;
                    @endphp

                    @foreach ($salesTrend as $idx => $day)
                        @php
                            $slotX = 35 + ($idx * 73);
                            $barW = 32;
                            $barX = $slotX + 4;
                            
                            $onlineH = $maxDailySales > 0 ? ($day['online'] / $maxDailySales) * $chartHeight : 0;
                            $offlineH = $maxDailySales > 0 ? ($day['offline'] / $maxDailySales) * $chartHeight : 0;
                            $totalH = $onlineH + $offlineH;

                            $offlineY = $baselineY - $offlineH;
                            $onlineY = $offlineY - $onlineH;
                        @endphp

                        @if ($day['total'] > 0)
                            {{-- Offline Bar (Bottom Segment) --}}
                            @if ($offlineH > 0)
                                <rect x="{{ $barX }}" y="{{ $offlineY }}" width="{{ $barW }}" height="{{ $offlineH }}" rx="{{ $onlineH > 0 ? 0 : 4 }}" fill="#2e7d32">
                                    <title>{{ $day['label'] }} ({{ $day['day_name'] }}): Kasir Offline Rp {{ number_format($day['offline'], 0, ',', '.') }}</title>
                                </rect>
                            @endif

                            {{-- Online Bar (Top Segment) --}}
                            @if ($onlineH > 0)
                                <rect x="{{ $barX }}" y="{{ $onlineY }}" width="{{ $barW }}" height="{{ $onlineH }}" rx="4" fill="#8b4513">
                                    <title>{{ $day['label'] }} ({{ $day['day_name'] }}): Online Rp {{ number_format($day['online'], 0, ',', '.') }}</title>
                                </rect>
                            @endif

                            {{-- Top Value Label --}}
                            <text x="{{ $barX + ($barW / 2) }}" y="{{ max(14, $onlineY - 6) }}" text-anchor="middle" font-size="9" font-weight="700" fill="#6d5d52">
                                {{ $day['total'] >= 1000000 ? round($day['total'] / 1000000, 1).'jt' : round($day['total'] / 1000).'k' }}
                            </text>
                        @else
                            {{-- Zero Placeholder Bar --}}
                            <rect x="{{ $barX }}" y="{{ $baselineY - 4 }}" width="{{ $barW }}" height="4" rx="2" fill="#e8dfd8">
                                <title>{{ $day['label'] }} ({{ $day['day_name'] }}): Rp 0</title>
                            </rect>
                        @endif

                        {{-- Day X-Axis Label --}}
                        <text x="{{ $barX + ($barW / 2) }}" y="148" text-anchor="middle" font-size="10.5" font-weight="600" fill="#7d6e64">
                            {{ $day['day_name'] }}
                        </text>
                        <text x="{{ $barX + ($barW / 2) }}" y="162" text-anchor="middle" font-size="9" fill="#9c8e84">
                            {{ $day['label'] }}
                        </text>
                    @endforeach
                </svg>
            </div>
        </div>

        {{-- Channel Proportion Strip --}}
        <div style="margin-top: 14px; padding-top: 12px; border-top: 1px solid #f0e6dd;">
            <div style="display: flex; justify-content: space-between; font-size: 11.5px; margin-bottom: 6px;">
                <span style="color: var(--muted);">Porsi Kanal Penjualan</span>
                <span style="font-weight: 700; color: var(--brown-dark);">Total: Rp {{ number_format($stats['sales'], 0, ',', '.') }}</span>
            </div>
            <div style="display: flex; height: 10px; border-radius: 999px; overflow: hidden; background: #e8dfd8;">
                <div style="width: {{ $stats['online_pct'] }}%; background: #8b4513;" title="Online: {{ $stats['online_pct'] }}%"></div>
                <div style="width: {{ $stats['offline_pct'] }}%; background: #2e7d32;" title="Offline: {{ $stats['offline_pct'] }}%"></div>
            </div>
            <div style="display: flex; justify-content: space-between; font-size: 11px; margin-top: 6px;">
                <span style="color: #8b4513; font-weight: 700;">Online: {{ $stats['online_pct'] }}% (Rp {{ number_format($stats['online_sales'], 0, ',', '.') }})</span>
                <span style="color: #2e7d32; font-weight: 700;">Kasir: {{ $stats['offline_pct'] }}% (Rp {{ number_format($stats['offline_sales'], 0, ',', '.') }})</span>
            </div>
        </div>
    </section>

    {{-- Panel Top Produk Terlaris --}}
    <section class="panel" style="margin-bottom: 0;">
        <div class="panel-heading" style="margin-bottom: 12px; padding-bottom: 10px; border-bottom: 1px solid #f0e6dd;">
            <div>
                <h2 style="font-size: 16px; margin-bottom: 2px;">Top Produk Terlaris</h2>
                <p style="font-size: 12px; color: var(--muted); margin: 0;">Paling banyak diminati pembeli</p>
            </div>
            <a class="text-link" href="{{ route('admin.produk.index') }}" style="font-size: 12px;">Katalog &rarr;</a>
        </div>

        @if ($topProducts->isNotEmpty())
            <div style="display: flex; flex-direction: column; gap: 10px;">
                @php $maxQty = $topProducts->max('qty') ?: 1; @endphp
                @foreach ($topProducts as $tIndex => $tp)
                    <div style="background: #faf6f1; border: 1px solid #f0e6dd; border-radius: 10px; padding: 10px 12px;">
                        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 6px;">
                            <div style="display: flex; align-items: center; gap: 8px;">
                                <span style="display: inline-flex; align-items: center; justify-content: center; width: 22px; height: 22px; border-radius: 6px; font-size: 11px; font-weight: 800; background: {{ $tIndex === 0 ? '#f59e0b; color: #fff;' : ($tIndex === 1 ? '#9ca3af; color: #fff;' : ($tIndex === 2 ? '#b45309; color: #fff;' : '#e8dfd8; color: #5a4b41;')) }}">
                                    #{{ $tIndex + 1 }}
                                </span>
                                <strong style="font-size: 13px; color: var(--brown-dark);">{{ $tp['name'] }}</strong>
                            </div>
                            <span style="font-size: 12.5px; font-weight: 700; color: #8b4513;">{{ $tp['qty'] }} bal</span>
                        </div>
                        <div style="display: flex; align-items: center; gap: 8px;">
                            <div style="flex: 1; height: 6px; background: #e8dfd8; border-radius: 999px; overflow: hidden;">
                                <div style="width: {{ round(($tp['qty'] / $maxQty) * 100) }}%; height: 100%; background: #8b4513; border-radius: 999px;"></div>
                            </div>
                            <span style="font-size: 11px; color: var(--muted); white-space: nowrap;">Rp {{ number_format($tp['revenue'], 0, ',', '.') }}</span>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="report-empty" style="padding: 36px 12px;">
                <svg style="width: 32px; height: 32px; stroke: #9c8e84; fill: none; stroke-width: 1.8; margin: 0 auto 8px; display: block;" viewBox="0 0 24 24"><circle cx="12" cy="8" r="6"/><path d="M15.477 12.89 17 22l-5-3-5 3 1.523-9.11"/></svg>
                Belum ada transaksi produk selesai tercatat.<br>
                <small class="muted">Data produk terlaris akan otomatis terisi saat transaksi online/kasir diselesaikan.</small>
            </div>
        @endif
    </section>
</div>

<section class="panel">
    <div class="panel-heading"><div><h2>Pesanan terbaru</h2><p>Perbarui status untuk memberi kabar kepada user.</p></div><a class="text-link" href="{{ route('admin.orders.index') }}">Semua pesanan →</a></div>
    @include('admin.orders.table', ['compact' => true])
</section>
@endsection
