@extends('layouts.admin')

@section('title', 'Dashboard Admin')
@section('section', 'Dashboard')

@section('content')
<div class="content-heading">
    <div>
        <span class="eyebrow">RINGKASAN TOKO</span>
        <h1>Dashboard</h1>
        <p>Ringkasan operasional Lintang Bakery.</p>
    </div>
</div>

{{-- 3 Kartu Utama: Fokus ke operasional penting --}}
<div class="stats-grid three-stats">
    <article class="stat-card {{ $stats['pending'] > 0 ? 'stat-card-attention' : '' }}">
        <div class="stat-card-top">
            <span>Perlu Diproses</span>
            @if ($stats['pending'] > 0)
                <span class="stat-badge-pulse">
                    <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
                    {{ $stats['pending'] }} Baru
                </span>
            @endif
        </div>
        <strong>{{ $stats['pending'] }}</strong>
        <div class="stat-card-bottom">
            <small>pesanan menunggu konfirmasi</small>
            @if ($stats['pending'] > 0)
                <a href="{{ route('admin.orders.index') }}" class="stat-link-action">Periksa &rarr;</a>
            @endif
        </div>
    </article>

    <article class="stat-card">
        <div class="stat-card-top">
            <span>Penjualan Bulan Ini</span>
            <span style="font-size: 10px; font-weight: 700; color: #8c786c; background: #f5ede6; padding: 2px 7px; border-radius: 999px;">
                {{ $stats['month_label'] }}
            </span>
        </div>
        <strong>Rp {{ number_format($stats['month_sales'], 0, ',', '.') }}</strong>
        <div class="stat-card-bottom">
            <small>omzet online &amp; kasir bulan ini</small>
        </div>
    </article>

    <article class="stat-card">
        <div class="stat-card-top">
            <span>Total Pesanan</span>
        </div>
        <strong>{{ $stats['orders'] }}</strong>
        <div class="stat-card-bottom">
            <small>total seluruh pesanan masuk</small>
        </div>
    </article>
</div>

{{-- Peringatan Terpadu (Hanya muncul jika ada stok menipis atau kedaluwarsa) --}}
@if ($lowStock->isNotEmpty() || (isset($expiringSoon) && $expiringSoon->isNotEmpty()))
    <div class="dashboard-alert-strip">
        @if ($lowStock->isNotEmpty())
            <div class="dash-alert-row dash-alert-warning">
                <span class="dash-alert-tag">Stok</span>
                <div class="dash-alert-content">
                    <strong>Peringatan Stok Menipis:</strong>
                    @foreach ($lowStock as $lp)
                        <span>{{ $lp->name }} (sisa {{ $lp->stock }} bal &le; batas {{ $lp->min_stock }} bal){{ ! $loop->last ? ', ' : '' }}</span>
                    @endforeach
                </div>
                <a href="{{ route('admin.stock.index') }}" class="dash-alert-link">Kelola Stok &rarr;</a>
            </div>
        @endif

        @if (isset($expiringSoon) && $expiringSoon->isNotEmpty())
            <div class="dash-alert-row dash-alert-danger">
                <span class="dash-alert-tag">Batch</span>
                <div class="dash-alert-content">
                    <strong>Peringatan Kedaluwarsa:</strong>
                    @foreach ($expiringSoon as $em)
                        <span>{{ $em->product?->name }} (Batch {{ $em->reference_number }}, Exp: {{ $em->expiry_date?->format('d/m/Y') }}){{ ! $loop->last ? ', ' : '' }}</span>
                    @endforeach
                </div>
                <a href="{{ route('admin.stock.index') }}#kartu-stok" class="dash-alert-link">Lihat Mutasi &rarr;</a>
            </div>
        @endif
    </div>
@endif

{{-- Analytics: Tren 7 Hari & Top Produk (Versi Simpel) --}}
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 18px; margin-bottom: 24px;">
    {{-- Chart Tren Penjualan --}}
    <section class="panel" style="margin-top: 0; margin-bottom: 0;">
        <div class="panel-heading" style="margin-bottom: 12px; padding-bottom: 10px; border-bottom: 1px solid #f0e6dd;">
            <div>
                <h2 style="font-size: 15px; margin-bottom: 2px;">Tren Penjualan 7 Hari</h2>
                <p style="font-size: 11.5px; color: var(--muted); margin: 0;">Omzet harian online &amp; kasir</p>
            </div>
            <div style="display: flex; gap: 10px; font-size: 11px; font-weight: 700;">
                <span style="display: inline-flex; align-items: center; gap: 5px; color: #8b4513;">
                    <span style="width: 8px; height: 8px; background: #8b4513; border-radius: 2px;"></span> Online
                </span>
                <span style="display: inline-flex; align-items: center; gap: 5px; color: #2e7d32;">
                    <span style="width: 8px; height: 8px; background: #2e7d32; border-radius: 2px;"></span> Kasir
                </span>
            </div>
        </div>

        {{-- SVG Chart Container --}}
        <div style="width: 100%; overflow-x: auto; padding-top: 4px;">
            <svg viewBox="0 0 560 170" style="width: 100%; height: auto; min-width: 300px; font-family: inherit;">
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
                                <title>{{ $day['label'] }} ({{ $day['day_name'] }}): Kasir Rp {{ number_format($day['offline'], 0, ',', '.') }}</title>
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
    </section>

    {{-- Panel Top Produk Terlaris (Diagram Donut) --}}
    <section class="panel" style="margin-top: 0; margin-bottom: 0;">
        <div class="panel-heading" style="margin-bottom: 12px; padding-bottom: 10px; border-bottom: 1px solid #f0e6dd;">
            <div>
                <h2 style="font-size: 15px; margin-bottom: 2px;">Top Produk Terlaris</h2>
                <p style="font-size: 11.5px; color: var(--muted); margin: 0;">Proporsi penjualan produk terlaris</p>
            </div>
            <a class="text-link" href="{{ route('admin.produk.index') }}" style="font-size: 12px;">Katalog &rarr;</a>
        </div>

        @if ($topProducts->isNotEmpty())
            @php
                $topTotalQty = $topProducts->sum('qty') ?: 1;
                $donutRadius = 52;
                $donutCircumference = 2 * pi() * $donutRadius;
                $donutColors = ['#d97706', '#8b4513', '#059669', '#6366f1', '#ec4899'];
                $donutOffset = 0;
            @endphp
            <div style="display: flex; align-items: center; justify-content: center; gap: 20px; flex-wrap: wrap; padding: 4px 0 6px;">
                {{-- Donut SVG --}}
                <div style="position: relative; width: 160px; height: 160px; flex-shrink: 0;">
                    <svg viewBox="0 0 160 160" width="160" height="160" style="display: block; transform: rotate(-90deg);">
                        {{-- Background Track --}}
                        <circle
                            cx="80"
                            cy="80"
                            r="{{ $donutRadius }}"
                            fill="transparent"
                            stroke="#f3ece6"
                            stroke-width="22"
                        />
                        @foreach ($topProducts as $idx => $tp)
                            @php
                                $pct = ($tp['qty'] / $topTotalQty);
                                $sliceLength = $pct * $donutCircumference;
                                $color = $donutColors[$idx % count($donutColors)];
                                $currentOffset = $donutOffset;
                                $donutOffset += $sliceLength;
                            @endphp
                            <circle
                                cx="80"
                                cy="80"
                                r="{{ $donutRadius }}"
                                fill="transparent"
                                stroke="{{ $color }}"
                                stroke-width="22"
                                stroke-dasharray="{{ sprintf('%.2f', max(0, $sliceLength - ($topProducts->count() > 1 ? 1.5 : 0))) }} {{ sprintf('%.2f', $donutCircumference) }}"
                                stroke-dashoffset="{{ sprintf('%.2f', -$currentOffset) }}"
                                stroke-linecap="butt"
                            >
                                <title>{{ $tp['name'] }}: {{ $tp['qty'] }} bal ({{ round($pct * 100) }}%)</title>
                            </circle>
                        @endforeach
                    </svg>

                    {{-- Donut Center Text --}}
                    <div style="position: absolute; inset: 0; display: flex; flex-direction: column; align-items: center; justify-content: center; text-align: center; pointer-events: none;">
                        <span style="font-size: 19px; font-weight: 800; color: #451a03; line-height: 1.1;">{{ $topTotalQty }}</span>
                        <span style="font-size: 9px; font-weight: 700; color: #9c8e84; text-transform: uppercase; letter-spacing: 0.5px;">Bal Terjual</span>
                    </div>
                </div>

                {{-- Legend / Breakdown List --}}
                <div style="flex: 1; min-width: 200px; display: flex; flex-direction: column; gap: 6px;">
                    @foreach ($topProducts as $idx => $tp)
                        @php
                            $pct = round(($tp['qty'] / $topTotalQty) * 100);
                            $color = $donutColors[$idx % count($donutColors)];
                        @endphp
                        <div style="display: flex; align-items: center; justify-content: space-between; font-size: 12px; padding: 5px 8px; border-radius: 6px; background: #faf6f1; border: 1px solid #f3ece6;">
                            <div style="display: flex; align-items: center; gap: 7px; overflow: hidden; white-space: nowrap; max-width: 62%;">
                                <span style="width: 9px; height: 9px; border-radius: 50%; background: {{ $color }}; flex-shrink: 0;"></span>
                                <span style="font-weight: 650; color: var(--brown-dark); overflow: hidden; text-overflow: ellipsis;" title="{{ $tp['name'] }}">{{ $tp['name'] }}</span>
                            </div>
                            <div style="text-align: right; font-size: 11.5px; white-space: nowrap;">
                                <strong style="color: #451a03;">{{ $tp['qty'] }} bal</strong>
                                <span style="color: var(--muted); font-size: 10.5px; margin-left: 2px;">({{ $pct }}%)</span>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @else
            <div class="report-empty" style="padding: 28px 12px;">
                <svg style="width: 28px; height: 28px; stroke: #9c8e84; fill: none; stroke-width: 1.8; margin: 0 auto 6px; display: block;" viewBox="0 0 24 24"><circle cx="12" cy="8" r="6"/><path d="M15.477 12.89 17 22l-5-3-5 3 1.523-9.11"/></svg>
                Belum ada transaksi produk tercatat.<br>
                <small class="muted">Data produk terlaris otomatis terisi saat transaksi diselesaikan.</small>
            </div>
        @endif
    </section>
</div>

{{-- Pesanan Terbaru --}}
<section class="panel">
    <div class="panel-heading">
        <div>
            <h2>Pesanan terbaru</h2>
            <p>Perbarui status untuk memberi kabar kepada user.</p>
        </div>
        <a class="text-link" href="{{ route('admin.orders.index') }}">Semua pesanan &rarr;</a>
    </div>
    @include('admin.orders.table', ['compact' => true])
</section>
@endsection
