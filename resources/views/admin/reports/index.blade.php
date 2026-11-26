@extends('layouts.admin')

@section('title', 'Laporan Penjualan')
@section('section', 'Laporan')

@section('content')
@php
    $activeTab = in_array(request('tab'), ['detail', 'sales', 'harian', 'produk']) ? request('tab') : 'detail';
@endphp
<div class="content-heading">
    <div>
        <span class="eyebrow">RINGKASAN & RINCIAN PENJUALAN</span>
        <h1>Laporan Penjualan</h1>
        <p>Laporan transaksi detail harian, kanal penjualan (online & offline), dan metode pembayaran.</p>
    </div>
    <a class="button button-light" href="{{ route('admin.reports.export', request()->query()) }}" style="display:inline-flex; align-items:center; gap:8px;">
        <svg style="width:14px; height:14px; stroke:currentColor; fill:none; stroke-width:2;" viewBox="0 0 24 24"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" x2="12" y1="15" y2="3"/></svg>
        <span>Export Excel</span>
    </a>
</div>

{{-- Filter Tanggal, Kanal, Metode Bayar, & Cari Nama Pelanggan --}}
<section class="report-filter-bar">
    <form method="get" action="{{ route('admin.reports.index') }}" class="report-filter-inline" id="reportFilterForm" style="display:flex; flex-direction:column; gap:14px; width:100%;">
        <input type="hidden" name="tab" id="filterActiveTab" value="{{ $activeTab }}">
        <div style="display:flex; flex-wrap:wrap; gap:12px; align-items:flex-end;">
            {{-- Dari Tanggal --}}
            <label style="display:flex; flex-direction:column; gap:4px; font-size:11px; font-weight:700; color:var(--muted); min-width:140px; flex:1;">
                <span style="text-transform:uppercase; letter-spacing:.04em;">Dari Tanggal</span>
                <input type="date" name="start_date" id="filterStartDate" value="{{ $startDate }}" style="min-height:38px; border:1px solid var(--line); border-radius:8px; padding:6px 10px; font-family:inherit; font-size:13px;">
            </label>

            {{-- Sampai Tanggal --}}
            <label style="display:flex; flex-direction:column; gap:4px; font-size:11px; font-weight:700; color:var(--muted); min-width:140px; flex:1;">
                <span style="text-transform:uppercase; letter-spacing:.04em;">Sampai Tanggal</span>
                <input type="date" name="end_date" id="filterEndDate" value="{{ $endDate }}" style="min-height:38px; border:1px solid var(--line); border-radius:8px; padding:6px 10px; font-family:inherit; font-size:13px;">
            </label>

            {{-- Filter Kanal (Online & Offline) --}}
            <label style="display:flex; flex-direction:column; gap:4px; font-size:11px; font-weight:700; color:var(--muted); min-width:150px; flex:1;">
                <span style="text-transform:uppercase; letter-spacing:.04em;">Kanal Penjualan</span>
                <select name="channel" id="filterChannel" style="min-height:38px; border:1px solid var(--line); border-radius:8px; padding:6px 10px; font-family:inherit; font-size:13px; background:white;">
                    <option value="all" {{ ($currentChannel ?? 'all') === 'all' ? 'selected' : '' }}>Semua Kanal</option>
                    <option value="offline" {{ ($currentChannel ?? '') === 'offline' ? 'selected' : '' }}>Offline</option>
                    <option value="online" {{ ($currentChannel ?? '') === 'online' ? 'selected' : '' }}>Online</option>
                </select>
            </label>

            {{-- Filter Metode Pembayaran (Tunai & QRIS) --}}
            <label style="display:flex; flex-direction:column; gap:4px; font-size:11px; font-weight:700; color:var(--muted); min-width:150px; flex:1;">
                <span style="text-transform:uppercase; letter-spacing:.04em;">Metode Bayar</span>
                <select name="payment_method" id="filterPayment" style="min-height:38px; border:1px solid var(--line); border-radius:8px; padding:6px 10px; font-family:inherit; font-size:13px; background:white;">
                    <option value="all" {{ ($currentPaymentMethod ?? 'all') === 'all' ? 'selected' : '' }}>Semua Metode</option>
                    <option value="cash" {{ ($currentPaymentMethod ?? '') === 'cash' ? 'selected' : '' }}>Tunai</option>
                    <option value="qris" {{ ($currentPaymentMethod ?? '') === 'qris' ? 'selected' : '' }}>QRIS</option>
                    <option value="bank_transfer" {{ ($currentPaymentMethod ?? '') === 'bank_transfer' ? 'selected' : '' }}>Transfer</option>
                </select>
            </label>

            {{-- Pencarian Nama Pelanggan (Tanpa teks contoh) --}}
            <label style="display:flex; flex-direction:column; gap:4px; font-size:11px; font-weight:700; color:var(--muted); min-width:180px; flex:1.5;">
                <span style="text-transform:uppercase; letter-spacing:.04em;">Cari Nama Pelanggan</span>
                <input type="text" name="search_buyer" id="filterSearchBuyer" value="{{ $searchBuyer ?? '' }}" placeholder="" style="width:100%; min-height:38px; border:1px solid var(--line); border-radius:8px; padding:6px 10px; font-family:inherit; font-size:13px;">
            </label>

            {{-- Tombol Terapkan & Reset --}}
            <div style="display:flex; gap:6px; align-items:center; padding-bottom:1px;">
                <button class="button" type="submit" style="min-height:38px; padding:0 16px;">Terapkan Filter</button>
                @if (request()->hasAny(['start_date', 'end_date', 'channel', 'payment_method', 'search_buyer']))
                    <a href="{{ route('admin.reports.index', array_filter(['tab' => $activeTab !== 'detail' ? $activeTab : null])) }}" class="button button-light" style="min-height:38px; padding:0 12px;" title="Reset Semua Filter">Reset</a>
                @endif
            </div>
        </div>

        {{-- Filter Presets Cepat --}}
        <div class="filter-presets" style="margin-top:2px;">
            <span class="preset-label">Rentang Cepat:</span>
            <button type="button" class="preset-btn" onclick="applyPreset('today')">Hari Ini</button>
            <button type="button" class="preset-btn" onclick="applyPreset('7days')">7 Hari Terakhir</button>
            <button type="button" class="preset-btn" onclick="applyPreset('this_month')">Bulan Ini</button>
            <button type="button" class="preset-btn" onclick="applyPreset('last_month')">Bulan Lalu</button>
            <button type="button" class="preset-btn" onclick="applyPreset('this_year')">Tahun Ini</button>
            <button type="button" class="preset-btn" onclick="applyPreset('all')">Semua Waktu</button>
        </div>
    </form>
</section>

{{-- Tab Navigation --}}
<nav class="report-tabs" id="reportTabs">
    <button class="report-tab {{ $activeTab === 'detail' ? 'active' : '' }}" data-tab="detail" type="button">Riwayat Transaksi</button>
    <button class="report-tab {{ $activeTab === 'sales' ? 'active' : '' }}" data-tab="sales" type="button">Ringkasan &amp; Grafik</button>
    <button class="report-tab {{ $activeTab === 'harian' ? 'active' : '' }}" data-tab="harian" type="button">Penjualan harian</button>
    <button class="report-tab {{ $activeTab === 'produk' ? 'active' : '' }}" data-tab="produk" type="button">Produk terlaris</button>
</nav>

{{-- TAB 1: RIWAYAT TRANSAKSI OFFLINE & ONLINE --}}
<div class="report-tab-content {{ $activeTab === 'detail' ? 'active' : '' }}" id="tab-detail">
    <div class="five-stats">
        <article class="stat-card report-stat-card">
            <span class="stat-label">Total Transaksi</span>
            <strong>{{ number_format($totalTransactions, 0, ',', '.') }}</strong>
            <small>transaksi terkonfirmasi</small>
        </article>
        <article class="stat-card report-stat-card">
            <span class="stat-label">Total Penjualan</span>
            <strong style="color:var(--brown);">Rp {{ number_format($totalRevenue, 0, ',', '.') }}</strong>
            <small>{{ match($currentChannel ?? 'all') { 'online' => 'pesanan online', 'offline' => 'penjualan offline', default => 'online + offline' } }}</small>
        </article>
        <article class="stat-card report-stat-card">
            <span class="stat-label">Item Terjual</span>
            <strong>{{ number_format($totalItemsSold, 0, ',', '.') }}</strong>
            <small>bal produk</small>
        </article>
        <article class="stat-card report-stat-card">
            <span class="stat-label">Rata-rata Transaksi</span>
            <strong>Rp {{ $totalTransactions > 0 ? number_format(round($totalRevenue / $totalTransactions), 0, ',', '.') : '0' }}</strong>
            <small>nilai belanja rata-rata</small>
        </article>
        <article class="stat-card report-stat-card" style="{{ ($totalLoss ?? 0) > 0 ? 'border-left: 3px solid #dc3545;' : '' }}">
            <span class="stat-label">Kerugian Stok</span>
            @if (($totalLoss ?? 0) > 0)
                <strong style="color:#c53030;">Rp {{ number_format($totalLoss, 0, ',', '.') }}</strong>
                <small>rusak &amp; kedaluwarsa</small>
            @else
                <strong>Rp 0</strong>
                <small>Belum ada kerugian stok.</small>
            @endif
        </article>
    </div>

    <section class="panel">
        <div class="panel-heading">
            <div>
                <h2>Riwayat Penjualan Offline &amp; Online</h2>
                <p>Seluruh transaksi penjualan offline dan pesanan online berdasarkan filter yang Anda pilih.</p>
            </div>
        </div>
        <div class="table-scroll">
            <table>
                <thead>
                    <tr>
                        <th style="width: 5%; text-align: center;">No</th>
                        <th style="width: 16%;">Tanggal &amp; Waktu</th>
                        <th style="width: 13%;">Kode / No. Nota</th>
                        <th style="width: 10%;">Kanal</th>
                        <th style="width: 19%;">Nama Pelanggan</th>
                        <th style="width: 13%;">Metode Bayar</th>
                        <th style="width: 13%;">Total Penjualan</th>
                        <th style="width: 11%; text-align: right;">Aksi</th>
                    </tr>
                </thead>
                <tbody id="trxTableBody">
                @forelse ($transactionDetails as $idx => $t)
                    <tr class="trx-row" style="vertical-align: middle;">
                        <td style="text-align: center; vertical-align: middle; padding: 10px 8px; color: var(--muted); font-size: 12px; font-weight: 600;">
                            {{ $idx + 1 }}
                        </td>
                        <td style="vertical-align: middle; padding: 10px 12px;">
                            <strong style="font-size: 12.5px;">{{ $t['date_formatted'] }}</strong>
                        </td>
                        <td style="vertical-align: middle; padding: 10px 12px;">
                            <code style="font-size: 11px; background: #f5ece1; padding: 3px 6px; border-radius: 4px; font-family: monospace;">{{ $t['code'] }}</code>
                        </td>
                        <td style="vertical-align: middle; padding: 10px 12px;">
                            <span class="badge {{ $t['source'] === 'Online' ? 'badge-success' : 'badge-info' }}" style="font-size: 11px; padding: 3px 8px;">
                                {{ $t['source'] }}
                            </span>
                        </td>
                        <td style="vertical-align: middle; padding: 10px 12px;">
                            @if ($t['buyer'] && $t['buyer'] !== '-')
                                <strong style="font-size: 13px; color: var(--brown-dark);">{{ $t['buyer'] }}</strong>
                            @else
                                <span style="color: var(--muted); font-size: 13px;">-</span>
                            @endif
                        </td>
                        <td style="vertical-align: middle; padding: 10px 12px;">
                            <span style="font-size: 12.5px; color: #443730;">{{ $t['payment_method'] }}</span>
                        </td>
                        <td style="vertical-align: middle; padding: 10px 12px;">
                            <strong style="color: var(--brown); font-size: 13.5px; display: block;">Rp {{ number_format($t['total_revenue'], 0, ',', '.') }}</strong>
                            <small class="muted" style="font-size: 11px;">{{ collect($t['items'])->sum('quantity') }} bal</small>
                        </td>
                        <td style="text-align: right; vertical-align: middle; padding: 10px 12px;">
                            <div style="display: inline-flex; gap: 4px; align-items: center; justify-content: flex-end;">
                                <button type="button" class="button button-small button-light view-trx-btn" data-index="{{ $idx }}" style="padding: 4px 8px; font-size: 11px;">
                                    Rincian
                                </button>
                                @if ($t['source'] === 'Offline')
                                    <a href="{{ route('admin.transactions.receipt', $t['raw_id']) }}" target="_blank" class="button button-small button-light" style="padding: 4px 8px; font-size: 11px; display: inline-flex; align-items: center; gap: 3px;" title="Cetak Struk Offline">
                                        <svg style="width: 12px; height: 12px; fill: none; stroke: currentColor; stroke-width: 2;" viewBox="0 0 24 24"><polyline points="6 9 6 2 18 2 18 9"></polyline><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path><rect x="6" y="14" width="12" height="8"></rect></svg>
                                        <span>Struk</span>
                                    </a>
                                @else
                                    <a href="{{ route('admin.orders.invoice', $t['raw_id']) }}" target="_blank" class="button button-small button-light" style="padding: 4px 8px; font-size: 11px; display: inline-flex; align-items: center; gap: 3px;" title="Cetak Faktur Pesanan">
                                        <span>Faktur</span>
                                    </a>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="table-empty">Belum ada transaksi yang sesuai dengan filter ini pada periode terpilih.</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>

        {{-- Kontrol Pagination Riwayat Transaksi (5 Transaksi per Halaman) --}}
        <div id="trxPaginationWrap" style="display: flex; justify-content: space-between; align-items: center; padding: 12px 18px; background: white; border-top: 1px solid var(--line); border-radius: 0 0 10px 10px; flex-wrap: wrap; gap: 12px;">
            <div id="trxPageSummary" style="font-size: 12.5px; color: var(--muted); font-weight: 600;">
                Menampilkan 1 - 5 dari {{ count($transactionDetails) }} transaksi
            </div>
            <div id="trxPaginationButtons" style="display: flex; gap: 6px; align-items: center;">
                <!-- Dibuat secara dinamis oleh JavaScript: <, 1, 2, 3, > -->
            </div>
        </div>
    </section>
</div>

{{-- TAB 2: RINGKASAN & GRAFIK (SALES) --}}
<div class="report-tab-content {{ $activeTab === 'sales' ? 'active' : '' }}" id="tab-sales">
    {{-- Ringkasan Penjualan Kotor --}}
    <section class="report-summary-card">
        <div class="summary-header">
            <span class="summary-period">Periode {{ \Illuminate\Support\Carbon::parse($startDate)->translatedFormat('d M Y') }} – {{ \Illuminate\Support\Carbon::parse($endDate)->translatedFormat('d M Y') }}</span>
            <strong class="summary-omzet">Rp {{ number_format($totalRevenue, 0, ',', '.') }}</strong>
            <span class="summary-label">Total Penjualan (Kotor)</span>
        </div>
    </section>

    {{-- Grafik Penjualan (Diskrit Batang Default - Bukan Kontinu) --}}
    <section class="report-chart-card">
        <div class="chart-header">
            <div>
                <h3 id="chartTitle">Grafik Penjualan</h3>
                <span class="muted" id="chartSubtitle" style="font-size:11px;">Diagram penjualan diskrit per hari (Rp)</span>
            </div>
            <div class="chart-controls">
                {{-- Toggle Pengelompokan: Per Hari / Per Bulan --}}
                <div class="chart-pill-group" title="Ubah tampilan pengelompokan waktu">
                    <button type="button" class="chart-pill active" id="btnGroupDaily" onclick="setChartGrouping('daily')">Per Hari</button>
                    <button type="button" class="chart-pill" id="btnGroupMonthly" onclick="setChartGrouping('monthly')">Per Bulan</button>
                </div>
            </div>
        </div>
        <div class="chart-container">
            <canvas id="salesTrendChart"></canvas>
        </div>
    </section>

    {{-- Analisis & Data Penjualan (Desain visual baru yang jelas & mudah dipahami) --}}
    <section class="panel" style="margin-top:20px;">
        <div class="panel-heading" style="margin-bottom:16px; border-bottom:1px solid #f0e6dd; padding-bottom:12px;">
            <div>
                <h2 style="font-size:17px; margin-bottom:3px; color:var(--brown-dark);">Ringkasan &amp; Analisis Penjualan</h2>
                <p style="font-size:12.5px; color:var(--muted); margin:0;">Visualisasi performa penjualan, perbandingan kanal, dan saluran pembayaran.</p>
            </div>
        </div>

        {{-- 1. Kartu Metrik Utama (4 Kartu Ringkas) --}}
        <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(200px, 1fr)); gap:14px; margin-bottom:20px;">
            <div style="background:#faf6f1; border:1px solid #ebdccb; border-radius:12px; padding:16px;">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:8px;">
                    <span style="font-size:11.5px; font-weight:700; color:var(--muted); text-transform:uppercase; letter-spacing:0.04em;">Total Penjualan</span>
                    <span style="width:28px; height:28px; border-radius:8px; background:#75462c; color:white; display:flex; align-items:center; justify-content:center;">
                        <svg style="width:15px; height:15px;" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
                    </span>
                </div>
                <strong style="font-size:20px; color:var(--brown); display:block; margin-bottom:2px;">Rp {{ number_format($totalRevenue, 0, ',', '.') }}</strong>
                <small style="color:var(--muted); font-size:11.5px;">Pendapatan kotor periode ini</small>
            </div>

            <div style="background:white; border:1px solid var(--line); border-radius:12px; padding:16px;">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:8px;">
                    <span style="font-size:11.5px; font-weight:700; color:var(--muted); text-transform:uppercase; letter-spacing:0.04em;">Jumlah transaksi</span>
                    <span style="width:28px; height:28px; border-radius:8px; background:#e8dfd8; color:var(--brown-dark); display:flex; align-items:center; justify-content:center;">
                        <svg style="width:15px; height:15px;" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"/><rect x="8" y="2" width="8" height="4" rx="1" ry="1"/></svg>
                    </span>
                </div>
                <strong style="font-size:20px; color:var(--brown-dark); display:block; margin-bottom:2px;">{{ number_format($totalTransactions, 0, ',', '.') }}</strong>
                <small style="color:var(--muted); font-size:11.5px;">Transaksi selesai / lunas</small>
            </div>

            <div style="background:white; border:1px solid var(--line); border-radius:12px; padding:16px;">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:8px;">
                    <span style="font-size:11.5px; font-weight:700; color:var(--muted); text-transform:uppercase; letter-spacing:0.04em;">Produk Terjual</span>
                    <span style="width:28px; height:28px; border-radius:8px; background:#e8dfd8; color:var(--brown-dark); display:flex; align-items:center; justify-content:center;">
                        <svg style="width:15px; height:15px;" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="m7.5 4.27 9 5.15"/><path d="M21 8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16Z"/><path d="m3.3 7 8.7 5 8.7-5"/><path d="M12 22V12"/></svg>
                    </span>
                </div>
                <strong style="font-size:20px; color:var(--brown-dark); display:block; margin-bottom:2px;">{{ number_format($totalItemsSold, 0, ',', '.') }} bal</strong>
                <small style="color:var(--muted); font-size:11.5px;">Volume roti/kue keluar</small>
            </div>

            <div style="background:white; border:1px solid var(--line); border-radius:12px; padding:16px;">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:8px;">
                    <span style="font-size:11.5px; font-weight:700; color:var(--muted); text-transform:uppercase; letter-spacing:0.04em;">Rata-rata Transaksi</span>
                    <span style="width:28px; height:28px; border-radius:8px; background:#e8dfd8; color:var(--brown-dark); display:flex; align-items:center; justify-content:center;">
                        <svg style="width:15px; height:15px;" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><path d="M8 14s1.5 2 4 2 4-2 4-2"/><line x1="9" y1="9" x2="9.01" y2="9"/><line x1="15" y1="9" x2="15.01" y2="9"/></svg>
                    </span>
                </div>
                <strong style="font-size:20px; color:var(--brown-dark); display:block; margin-bottom:2px;">Rp {{ number_format($averageOrderValue, 0, ',', '.') }}</strong>
                <small style="color:var(--muted); font-size:11.5px;">Rata-rata belanja per nota</small>
            </div>
        </div>

        {{-- 2. Perbandingan Kanal (Penjualan Offline vs Pesanan Online) --}}
        <div style="margin-bottom:22px; padding:16px; border:1px solid var(--line); border-radius:12px; background:#ffffff;">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:10px;">
                <strong style="font-size:14px; color:var(--brown-dark);">Kanal Penjualan (Offline vs Online)</strong>
                <span style="font-size:12px; color:var(--muted);">Total: Rp {{ number_format($totalRevenue, 0, ',', '.') }}</span>
            </div>

            {{-- Progress bar visual --}}
            <div style="height:12px; border-radius:999px; overflow:hidden; background:#e8dfd8; display:flex; margin-bottom:14px;">
                <div style="width:{{ $offlinePct }}%; background:#2e7d32;" title="Offline: {{ $offlinePct }}%"></div>
                <div style="width:{{ $onlinePct }}%; background:#8b4513;" title="Online: {{ $onlinePct }}%"></div>
            </div>

            {{-- Dua Kolom Komparasi --}}
            <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(260px, 1fr)); gap:14px;">
                <div style="border:1px solid #d4edd9; background:#f3f9f4; border-radius:10px; padding:14px;">
                    <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:6px;">
                        <span style="display:inline-flex; align-items:center; gap:6px; font-weight:700; font-size:13px; color:#1b5e20;">
                            <span style="width:10px; height:10px; background:#2e7d32; border-radius:3px;"></span>
                            Penjualan Offline
                        </span>
                        <span style="font-size:12px; font-weight:800; color:#2e7d32; background:#e2f3e5; padding:2px 8px; border-radius:6px;">{{ $offlinePct }}%</span>
                    </div>
                    <strong style="font-size:18px; color:#1b5e20; display:block; margin-bottom:4px;">Rp {{ number_format($offlineRevenue, 0, ',', '.') }}</strong>
                    <span style="font-size:11.5px; color:#406b47;">{{ $offlineCount }} transaksi penjualan offline</span>
                </div>

                <div style="border:1px solid #f2ded0; background:#fdf7f3; border-radius:10px; padding:14px;">
                    <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:6px;">
                        <span style="display:inline-flex; align-items:center; gap:6px; font-weight:700; font-size:13px; color:#8b4513;">
                            <span style="width:10px; height:10px; background:#8b4513; border-radius:3px;"></span>
                            Pesanan Online (Website)
                        </span>
                        <span style="font-size:12px; font-weight:800; color:#8b4513; background:#faede5; padding:2px 8px; border-radius:6px;">{{ $onlinePct }}%</span>
                    </div>
                    <strong style="font-size:18px; color:#8b4513; display:block; margin-bottom:4px;">Rp {{ number_format($onlineRevenue, 0, ',', '.') }}</strong>
                    <span style="font-size:11.5px; color:#7d5c48;">{{ $onlineCount }} transaksi pesanan online</span>
                </div>
            </div>
        </div>

        {{-- 3. Rincian Berdasarkan Metode Pembayaran (Visual Cards) --}}
        <div>
            <h3 style="font-size:14px; font-weight:700; color:var(--brown-dark); margin:0 0 10px;">Rincian Berdasarkan Metode Pembayaran</h3>
            <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(220px, 1fr)); gap:12px;">
                {{-- Tunai Offline --}}
                <div style="border:1px solid var(--line); border-radius:10px; padding:12px 14px; background:white; display:flex; flex-direction:column; justify-content:space-between;">
                    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:8px;">
                        <span class="role-pill" style="background:#e6f4ea; color:#137333; font-weight:700; display:inline-flex; align-items:center; gap:5px;">
                            <svg style="width:13px; height:13px; stroke:currentColor; fill:none; stroke-width:2;" viewBox="0 0 24 24"><rect width="20" height="12" x="2" y="6" rx="2"/><circle cx="12" cy="12" r="2"/></svg>
                            Tunai (Offline)
                        </span>
                    </div>
                    <strong style="font-size:17px; color:#137333; margin-bottom:3px; display:block;">Rp {{ number_format($totalCash, 0, ',', '.') }}</strong>
                    <small style="color:var(--muted); font-size:11px;">Pembayaran uang tunai transaksi offline</small>
                </div>

                {{-- QRIS --}}
                <div style="border:1px solid var(--line); border-radius:10px; padding:12px 14px; background:white; display:flex; flex-direction:column; justify-content:space-between;">
                    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:8px;">
                        <span class="role-pill" style="background:#fde8e8; color:#b71c1c; font-weight:700; display:inline-flex; align-items:center; gap:5px;">
                            <svg style="width:13px; height:13px; stroke:currentColor; fill:none; stroke-width:2;" viewBox="0 0 24 24"><rect width="5" height="5" x="3" y="3" rx="1"/><rect width="5" height="5" x="16" y="3" rx="1"/><rect width="5" height="5" x="3" y="16" rx="1"/><path d="M21 16h-3a2 2 0 0 0-2 2v3"/><path d="M12 7v3a2 2 0 0 1-2 2H7"/></svg>
                            QRIS (Offline &amp; Online)
                        </span>
                    </div>
                    <strong style="font-size:17px; color:#b71c1c; margin-bottom:3px; display:block;">Rp {{ number_format($totalQris, 0, ',', '.') }}</strong>
                    <small style="color:var(--muted); font-size:11px;">Offline: Rp {{ number_format($totalStoreQris, 0, ',', '.') }} &bull; Online: Rp {{ number_format($totalOnlineQris, 0, ',', '.') }}</small>
                </div>

                {{-- Transfer Bank Online --}}
                <div style="border:1px solid var(--line); border-radius:10px; padding:12px 14px; background:white; display:flex; flex-direction:column; justify-content:space-between;">
                    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:8px;">
                        <span class="role-pill" style="background:#e8f0fe; color:#1a73e8; font-weight:700; display:inline-flex; align-items:center; gap:5px;">
                            <svg style="width:13px; height:13px; stroke:currentColor; fill:none; stroke-width:2;" viewBox="0 0 24 24"><rect width="20" height="14" x="2" y="5" rx="2"/><line x1="2" x2="22" y1="10" y2="10"/></svg>
                            Transfer Bank (Online)
                        </span>
                    </div>
                    <strong style="font-size:17px; color:#1a73e8; margin-bottom:3px; display:block;">Rp {{ number_format($totalOnlineBankTransfer, 0, ',', '.') }}</strong>
                    <small style="color:var(--muted); font-size:11px;">Transfer rekening pesanan website</small>
                </div>
            </div>
        </div>
    </section>
</div>

{{-- TAB 3: PENJUALAN HARIAN (DENGAN PAGINATION PER 5 HARI) --}}
<div class="report-tab-content {{ $activeTab === 'harian' ? 'active' : '' }}" id="tab-harian">
    @if ($dailyBreakdown->isEmpty())
        <section class="report-data-card">
            <div class="report-empty">Belum ada penjualan pada periode ini.</div>
        </section>
    @else
        <div id="dailyCardsContainer">
            @foreach ($dailyBreakdown as $rowIdx => $row)
            @php
                $dayTrx = $transactionsByDate[$row['date']] ?? collect();
            @endphp
            <div class="daily-card-wrap daily-paged-card" data-card-index="{{ $rowIdx }}" style="margin-bottom:12px; border:1px solid var(--line); border-radius:12px; background:white; overflow:hidden;">
                <div class="daily-sale-item" style="cursor:pointer; margin-bottom:0; border:none; border-radius:0;" onclick="toggleDayDetails('{{ $row['date'] }}')">
                    <div class="daily-date">
                        <strong>{{ \Illuminate\Support\Carbon::parse($row['date'])->translatedFormat('d') }}</strong>
                        <span>{{ \Illuminate\Support\Carbon::parse($row['date'])->translatedFormat('M') }}</span>
                    </div>
                    <div class="daily-info">
                        <div style="display:flex; align-items:center; gap:8px;">
                            <strong style="color:var(--brown-dark); font-size:13.5px;">{{ $row['transactions'] }} transaksi</strong>
                            <span class="badge" style="font-size:10.5px; padding:2px 8px; font-weight:600; background:#f5eee6; color:var(--brown);">{{ $row['items'] }} item terjual</span>
                        </div>
                    </div>
                    <div style="display:flex; align-items:center; gap:16px;">
                        <div class="daily-total" style="font-size:15px; color:var(--brown);">Rp {{ number_format($row['online'] + $row['offline'], 0, ',', '.') }}</div>
                        <button type="button" class="button button-small button-light" style="padding:5px 11px; font-size:11px; display:inline-flex; align-items:center; gap:6px;">
                            <span>Lihat Transaksi ({{ $row['transactions'] }})</span>
                            <svg id="chevron-{{ $row['date'] }}" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="transition:transform 0.2s;"><polyline points="6 9 12 15 18 9"></polyline></svg>
                        </button>
                    </div>
                </div>

                <div id="day-details-{{ $row['date'] }}" style="display:none; border-top:1px solid var(--line); background:#fcfaf8; padding:14px 16px;">
                    @if ($dayTrx->isNotEmpty())
                        <div style="overflow-x:auto;">
                            <table class="data-table" style="font-size:12px; background:white; border-radius:8px; border:1px solid var(--line); width:100%;">
                                <thead>
                                    <tr style="background:#f5eee6; color:var(--brown-dark);">
                                        <th style="padding:9px 8px; width:5%; text-align:center;">No</th>
                                        <th style="padding:9px 12px; width:17%;">Waktu &amp; Ref</th>
                                        <th style="padding:9px 12px; width:12%;">Kanal</th>
                                        <th style="padding:9px 12px; width:24%;">Pembeli</th>
                                        <th style="padding:9px 12px; width:17%;">Metode Bayar</th>
                                        <th style="padding:9px 12px; text-align:right; width:15%;">Total</th>
                                        <th style="padding:9px 12px; text-align:center; width:10%;">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($dayTrx as $tx)
                                    <tr style="border-bottom:1px solid #f2e9df;">
                                        <td style="padding:9px 8px; text-align:center; color:var(--muted); font-size:11.5px; font-weight:600;">
                                            {{ $loop->iteration }}
                                        </td>
                                        <td style="padding:9px 12px; white-space:nowrap;">
                                            <strong>{{ \Carbon\Carbon::parse($tx['date'])->format('H:i') }} WIB</strong>
                                            <span class="badge" style="display:block; font-size:10px; padding:2px 6px; width:fit-content; margin-top:2px; background:#f0e6dc; color:var(--brown-dark);">{{ $tx['code'] }}</span>
                                        </td>
                                        <td style="padding:9px 12px;">
                                            @if ($tx['source'] === 'Online')
                                                <span class="badge badge-success" style="font-size:10px;">Online</span>
                                            @else
                                                <span class="badge badge-info" style="font-size:10px;">Offline</span>
                                            @endif
                                        </td>
                                        <td style="padding:9px 12px;">
                                            @if ($tx['buyer'] && $tx['buyer'] !== '-')
                                                <strong>{{ $tx['buyer'] }}</strong>
                                            @else
                                                <span style="color:var(--muted);">-</span>
                                            @endif
                                            @if(!empty($tx['phone']) && $tx['phone'] !== '-')
                                                <div style="font-size:10.5px; color:var(--muted);">{{ $tx['phone'] }}</div>
                                            @endif
                                        </td>
                                        <td style="padding:9px 12px; white-space:nowrap;">
                                            <span style="font-size:11.5px; color:#555;">{{ $tx['payment_method'] }}</span>
                                        </td>
                                        <td style="padding:9px 12px; text-align:right; white-space:nowrap;">
                                            <strong style="color:var(--brown); font-size:13px; display:block;">Rp {{ number_format($tx['total_revenue'], 0, ',', '.') }}</strong>
                                            <small class="muted" style="font-size:10.5px;">{{ collect($tx['items'])->sum('quantity') }} bal</small>
                                        </td>
                                        <td style="padding:9px 12px; text-align:center; white-space:nowrap;">
                                            <button type="button" class="button button-small button-light view-trx-btn" data-index="{{ $tx['global_index'] }}" style="padding:4px 9px; font-size:11px;">
                                                Rincian
                                            </button>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div style="padding:14px; text-align:center; color:var(--muted); font-size:12px;">Tidak ada transaksi tersimpan untuk hari ini.</div>
                    @endif
                </div>
            </div>
            @endforeach
        </div>

        {{-- Kontrol Pagination Harian (5 Hari per Halaman) --}}
        <div id="dailyPaginationWrap" style="display:flex; justify-content:space-between; align-items:center; margin-top:18px; padding:12px 18px; background:white; border:1px solid var(--line); border-radius:10px; flex-wrap:wrap; gap:12px;">
            <div id="dailyPageSummary" style="font-size:12.5px; color:var(--muted); font-weight:600;">
                Menampilkan 1 - 5 dari {{ $dailyBreakdown->count() }} hari
            </div>
            <div id="dailyPaginationButtons" style="display:flex; gap:6px; align-items:center;">
                <!-- Dibuat secara dinamis oleh JavaScript: <, 1, 2, 3, > -->
            </div>
        </div>
    @endif
</div>

{{-- TAB 4: PRODUK TERLARIS --}}
<div class="report-tab-content {{ $activeTab === 'produk' ? 'active' : '' }}" id="tab-produk">
    <section class="report-data-card">
        <h3>Produk terlaris</h3>
        <p class="data-subtitle">Lima produk dengan penjualan terbanyak.</p>

        @forelse ($bestSellers->take(5) as $i => $product)
        <div class="product-rank-item">
            <div class="rank-number">#{{ $i + 1 }}</div>
            <div class="rank-info">
                <strong>{{ $product['name'] }}</strong>
                <span>{{ $product['quantity'] }} bal terjual</span>
            </div>
            <div class="rank-revenue">Rp {{ number_format($product['revenue'], 0, ',', '.') }}</div>
        </div>
        @empty
        <div class="report-empty">Belum ada data produk terjual pada periode ini.</div>
        @endforelse
    </section>
</div>

{{-- Modal Detail Transaksi --}}
<div class="pos-modal-overlay" id="trxDetailModal">
    <div class="pos-modal-card" style="width:min(540px, 100%);">
        <div class="pos-modal-header">
            <div>
                <h3 id="modalTrxCode" style="margin:0;">Detail Transaksi</h3>
                <small id="modalTrxDate" class="muted"></small>
            </div>
            <button type="button" class="pos-close-btn" onclick="closeTrxModal()">&times;</button>
        </div>

        <div style="background:#faf7f3; padding:12px; border-radius:8px; margin-bottom:12px; font-size:12px;">
            <div style="display:flex; justify-content:space-between; margin-bottom:4px;">
                <span class="muted">Pembeli:</span>
                <strong id="modalTrxBuyer">-</strong>
            </div>
            <div style="display:flex; justify-content:space-between; margin-bottom:4px;">
                <span class="muted">Kanal:</span>
                <span id="modalTrxSource">-</span>
            </div>
            <div style="display:flex; justify-content:space-between;">
                <span class="muted">Metode Pembayaran:</span>
                <strong id="modalTrxPayment">-</strong>
            </div>
        </div>

        <h4 style="font-size:13px; margin:12px 0 6px; color:var(--brown-dark);">Daftar Item Kue:</h4>
        <div style="max-height:200px; overflow-y:auto;">
            <table class="modal-details-table">
                <thead>
                    <tr>
                        <th>Item</th>
                        <th style="text-align:center;">Qty</th>
                        <th style="text-align:right;">Harga</th>
                        <th style="text-align:right;">Subtotal</th>
                    </tr>
                </thead>
                <tbody id="modalTrxItemsBody">
                </tbody>
            </table>
        </div>

        <div class="pos-cart-summary" style="margin-top:14px; font-size:13px;">
            <div class="pos-summary-total">
                <span>Total Penjualan</span>
                <strong id="modalTrxTotal" style="color:var(--brown-dark); font-size:17px;">Rp 0</strong>
            </div>
        </div>

        <div id="modalTrxActions" style="display:flex; gap:8px; margin-top:16px; flex-wrap:wrap; justify-content:flex-end; align-items:center;">
            <button type="button" class="button button-small button-light" onclick="closeTrxModal()">
                Tutup
            </button>
        </div>
    </div>
</div>

{{-- Chart.js CDN --}}
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
// Data grafik dari server (Diskrit, bukan kontinu)
const chartData = @json($chartData);
let currentGrouping = 'daily';
let currentType = 'bar'; // Batang diskrit sebagai default
let salesChartInstance = null;

function renderSalesChart() {
    const ctx = document.getElementById('salesTrendChart');
    if (!ctx) return;

    if (salesChartInstance) {
        salesChartInstance.destroy();
    }

    const activeData = (currentGrouping === 'monthly') ? chartData.monthly : chartData.daily;
    if (!activeData || !activeData.labels || activeData.labels.length === 0) {
        return;
    }

    let datasets = [];

    if (currentType === 'line') {
        // Garis diskrit tanpa smoothing continuous curve
        datasets = [
            {
                label: 'Total Penjualan (Rp)',
                data: activeData.total,
                borderColor: '#75462c',
                backgroundColor: 'rgba(117, 70, 44, 0.08)',
                borderWidth: 2.5,
                fill: false,
                tension: 0, // Garis lurus antar titik diskrit (bukan continuous spline)
                spanGaps: false,
                pointBackgroundColor: '#75462c',
                pointBorderColor: '#ffffff',
                pointBorderWidth: 2,
                pointRadius: 4,
                pointHoverRadius: 6,
            },
            {
                label: 'Offline (Rp)',
                data: activeData.offline,
                borderColor: '#2e7d32',
                backgroundColor: 'transparent',
                borderWidth: 1.8,
                borderDash: [4, 4],
                tension: 0,
                pointBackgroundColor: '#2e7d32',
                pointRadius: 3,
                fill: false,
            },
            {
                label: 'Pesanan Online (Rp)',
                data: activeData.online,
                borderColor: '#d4a373',
                backgroundColor: 'transparent',
                borderWidth: 1.8,
                borderDash: [2, 2],
                tension: 0,
                pointBackgroundColor: '#d4a373',
                pointRadius: 3,
                fill: false,
            }
        ];
    } else {
        // Tipe Batang Diskrit (Default)
        datasets = [
            {
                label: 'Offline (Rp)',
                data: activeData.offline,
                backgroundColor: '#2e7d32',
                borderRadius: 4,
                maxBarThickness: 32,
            },
            {
                label: 'Pesanan Online (Rp)',
                data: activeData.online,
                backgroundColor: '#8b4513',
                borderRadius: 4,
                maxBarThickness: 32,
            }
        ];
    }

    salesChartInstance = new Chart(ctx, {
        type: currentType,
        data: {
            labels: activeData.labels,
            datasets: datasets,
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: {
                mode: 'index',
                intersect: false,
            },
            plugins: {
                legend: {
                    position: 'top',
                    labels: {
                        usePointStyle: true,
                        boxWidth: 8,
                        font: { family: "'Plus Jakarta Sans', sans-serif", size: 11 },
                        color: '#555'
                    }
                },
                tooltip: {
                    backgroundColor: '#3b2214',
                    padding: 10,
                    cornerRadius: 8,
                    titleFont: { family: "'Plus Jakarta Sans', sans-serif", size: 12 },
                    bodyFont: { family: "'Plus Jakarta Sans', sans-serif", size: 11 },
                    callbacks: {
                        label: function(context) {
                            return context.dataset.label + ': Rp ' + context.parsed.y.toLocaleString('id-ID');
                        }
                    }
                }
            },
            scales: {
                x: {
                    grid: { display: false },
                    ticks: {
                        font: { family: "'Plus Jakarta Sans', sans-serif", size: 11 },
                        color: '#777',
                        maxRotation: 45,
                        autoSkip: true,
                        maxTicksLimit: 15,
                    }
                },
                y: {
                    beginAtZero: true,
                    grid: { color: '#f5eee6' },
                    ticks: {
                        font: { family: "'Plus Jakarta Sans', sans-serif", size: 10 },
                        color: '#777',
                        callback: function(val) {
                            return 'Rp ' + val.toLocaleString('id-ID');
                        }
                    }
                }
            }
        }
    });
}

window.setChartGrouping = function(grouping) {
    currentGrouping = grouping;
    document.getElementById('btnGroupDaily').classList.toggle('active', grouping === 'daily');
    document.getElementById('btnGroupMonthly').classList.toggle('active', grouping === 'monthly');
    document.getElementById('chartSubtitle').textContent = grouping === 'monthly'
        ? 'Diagram omzet penjualan diskrit per bulan (Rp)'
        : 'Diagram omzet penjualan diskrit per hari (Rp)';
    renderSalesChart();
};

window.setChartType = function(type) {
    currentType = type;
    document.getElementById('btnTypeLine').classList.toggle('active', type === 'line');
    document.getElementById('btnTypeBar').classList.toggle('active', type === 'bar');
    renderSalesChart();
};

window.applyPreset = function(preset) {
    const startInput = document.getElementById('filterStartDate');
    const endInput = document.getElementById('filterEndDate');
    const today = new Date();

    const formatDate = (d) => {
        const year = d.getFullYear();
        const month = String(d.getMonth() + 1).padStart(2, '0');
        const day = String(d.getDate()).padStart(2, '0');
        return `${year}-${month}-${day}`;
    };

    if (preset === 'today') {
        startInput.value = formatDate(today);
        endInput.value = formatDate(today);
    } else if (preset === '7days') {
        const start = new Date();
        start.setDate(today.getDate() - 6);
        startInput.value = formatDate(start);
        endInput.value = formatDate(today);
    } else if (preset === 'this_month') {
        const start = new Date(today.getFullYear(), today.getMonth(), 1);
        const end = new Date(today.getFullYear(), today.getMonth() + 1, 0);
        startInput.value = formatDate(start);
        endInput.value = formatDate(end);
    } else if (preset === 'last_month') {
        const start = new Date(today.getFullYear(), today.getMonth() - 1, 1);
        const end = new Date(today.getFullYear(), today.getMonth(), 0);
        startInput.value = formatDate(start);
        endInput.value = formatDate(end);
    } else if (preset === 'this_year') {
        const start = new Date(today.getFullYear(), 0, 1);
        const end = new Date(today.getFullYear(), 11, 31);
        startInput.value = formatDate(start);
        endInput.value = formatDate(end);
    } else if (preset === 'all') {
        startInput.value = '2024-01-01';
        endInput.value = formatDate(today);
    }

    document.getElementById('reportFilterForm').submit();
};

// Pagination Harian (5 Item Per Halaman)
function initDailyPagination() {
    const cards = document.querySelectorAll('.daily-paged-card');
    const totalCards = cards.length;
    if (totalCards === 0) return;

    const perPage = 5;
    const totalPages = Math.ceil(totalCards / perPage);
    let currentPage = 1;

    function renderPage(page) {
        currentPage = page;
        const start = (page - 1) * perPage;
        const end = page * perPage;

        cards.forEach((card, idx) => {
            if (idx >= start && idx < end) {
                card.style.display = 'block';
            } else {
                card.style.display = 'none';
            }
        });

        // Summary text
        const currentEnd = Math.min(end, totalCards);
        const summaryElem = document.getElementById('dailyPageSummary');
        if (summaryElem) {
            summaryElem.textContent = `Menampilkan ${start + 1} - ${currentEnd} dari ${totalCards} hari`;
        }

        // Render button navigation: < 1 2 3 >
        const btnBox = document.getElementById('dailyPaginationButtons');
        if (!btnBox) return;
        btnBox.innerHTML = '';

        // Tombol < (Sebelumnya)
        const prevBtn = document.createElement('button');
        prevBtn.type = 'button';
        prevBtn.className = 'button button-small button-light';
        prevBtn.innerHTML = '&lt;';
        prevBtn.title = 'Halaman Sebelumnya';
        prevBtn.style.padding = '4px 10px';
        prevBtn.style.fontWeight = 'bold';
        if (page <= 1) {
            prevBtn.disabled = true;
            prevBtn.style.opacity = '0.4';
            prevBtn.style.cursor = 'not-allowed';
        } else {
            prevBtn.onclick = () => renderPage(page - 1);
        }
        btnBox.appendChild(prevBtn);

        // Angka Halaman: 1, 2, 3...
        for (let p = 1; p <= totalPages; p++) {
            const numBtn = document.createElement('button');
            numBtn.type = 'button';
            numBtn.className = (p === page) ? 'button button-small' : 'button button-small button-light';
            numBtn.textContent = p;
            numBtn.style.padding = '4px 10px';
            numBtn.style.minWidth = '32px';
            numBtn.style.textAlign = 'center';
            if (p === page) {
                numBtn.style.background = 'var(--brown)';
                numBtn.style.color = '#ffffff';
                numBtn.style.fontWeight = '700';
            }
            numBtn.onclick = () => renderPage(p);
            btnBox.appendChild(numBtn);
        }

        // Tombol > (Selanjutnya)
        const nextBtn = document.createElement('button');
        nextBtn.type = 'button';
        nextBtn.className = 'button button-small button-light';
        nextBtn.innerHTML = '&gt;';
        nextBtn.title = 'Halaman Selanjutnya';
        nextBtn.style.padding = '4px 10px';
        nextBtn.style.fontWeight = 'bold';
        if (page >= totalPages) {
            nextBtn.disabled = true;
            nextBtn.style.opacity = '0.4';
            nextBtn.style.cursor = 'not-allowed';
        } else {
            nextBtn.onclick = () => renderPage(page + 1);
        }
        btnBox.appendChild(nextBtn);
    }

    renderPage(1);
}

function initTrxPagination() {
    const rows = document.querySelectorAll('#trxTableBody .trx-row');
    const totalRows = rows.length;
    const paginationWrap = document.getElementById('trxPaginationWrap');
    if (totalRows === 0) {
        if (paginationWrap) paginationWrap.style.display = 'none';
        return;
    }

    const perPage = 5;
    const totalPages = Math.ceil(totalRows / perPage);
    let currentPage = 1;

    function renderPage(page) {
        currentPage = page;
        const start = (page - 1) * perPage;
        const end = page * perPage;

        rows.forEach((row, idx) => {
            if (idx >= start && idx < end) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });

        // Summary text
        const currentEnd = Math.min(end, totalRows);
        const summaryElem = document.getElementById('trxPageSummary');
        if (summaryElem) {
            summaryElem.textContent = `Menampilkan ${start + 1} - ${currentEnd} dari ${totalRows} transaksi`;
        }

        // Render button navigation: < 1 2 3 >
        const btnBox = document.getElementById('trxPaginationButtons');
        if (!btnBox) return;
        btnBox.innerHTML = '';

        if (totalPages <= 1) {
            return;
        }

        // Tombol < (Sebelumnya)
        const prevBtn = document.createElement('button');
        prevBtn.type = 'button';
        prevBtn.className = 'button button-small button-light';
        prevBtn.innerHTML = '&lt;';
        prevBtn.title = 'Halaman Sebelumnya';
        prevBtn.style.padding = '4px 10px';
        prevBtn.style.fontWeight = 'bold';
        if (page <= 1) {
            prevBtn.disabled = true;
            prevBtn.style.opacity = '0.4';
            prevBtn.style.cursor = 'not-allowed';
        } else {
            prevBtn.onclick = () => renderPage(page - 1);
        }
        btnBox.appendChild(prevBtn);

        // Angka Halaman: 1, 2, 3...
        for (let p = 1; p <= totalPages; p++) {
            const numBtn = document.createElement('button');
            numBtn.type = 'button';
            numBtn.className = (p === page) ? 'button button-small' : 'button button-small button-light';
            numBtn.textContent = p;
            numBtn.style.padding = '4px 10px';
            numBtn.style.minWidth = '32px';
            numBtn.style.textAlign = 'center';
            if (p === page) {
                numBtn.style.background = 'var(--brown)';
                numBtn.style.color = '#ffffff';
                numBtn.style.fontWeight = '700';
            }
            numBtn.onclick = () => renderPage(p);
            btnBox.appendChild(numBtn);
        }

        // Tombol > (Selanjutnya)
        const nextBtn = document.createElement('button');
        nextBtn.type = 'button';
        nextBtn.className = 'button button-small button-light';
        nextBtn.innerHTML = '&gt;';
        nextBtn.title = 'Halaman Selanjutnya';
        nextBtn.style.padding = '4px 10px';
        nextBtn.style.fontWeight = 'bold';
        if (page >= totalPages) {
            nextBtn.disabled = true;
            nextBtn.style.opacity = '0.4';
            nextBtn.style.cursor = 'not-allowed';
        } else {
            nextBtn.onclick = () => renderPage(page + 1);
        }
        btnBox.appendChild(nextBtn);
    }

    renderPage(1);
}

document.addEventListener('DOMContentLoaded', function() {
    // Tab switching with URL & form synchronization
    const tabs = document.querySelectorAll('#reportTabs .report-tab');
    const contents = document.querySelectorAll('.report-tab-content');
    const activeTabInput = document.getElementById('filterActiveTab');

    function switchReportTab(targetTabName) {
        tabs.forEach(function(t) {
            t.classList.toggle('active', t.dataset.tab === targetTabName);
        });
        contents.forEach(function(c) {
            c.classList.toggle('active', c.id === 'tab-' + targetTabName);
        });

        if (activeTabInput) {
            activeTabInput.value = targetTabName;
        }

        // Update URL query parameter without page reload
        try {
            const url = new URL(window.location);
            url.searchParams.set('tab', targetTabName);
            window.history.replaceState({}, '', url);

            const resetBtn = document.querySelector('a[title="Reset Semua Filter"]');
            if (resetBtn) {
                const resetUrl = new URL(resetBtn.href);
                resetUrl.searchParams.set('tab', targetTabName);
                resetBtn.href = resetUrl.toString();
            }
        } catch (_) {}

        if (targetTabName === 'sales') {
            setTimeout(renderSalesChart, 50);
        }
    }

    tabs.forEach(function(tab) {
        tab.addEventListener('click', function() {
            switchReportTab(tab.dataset.tab);
        });
    });

    // Inisialisasi Grafik
    if (activeTabInput && activeTabInput.value === 'sales') {
        setTimeout(renderSalesChart, 60);
    } else {
        renderSalesChart();
    }

    // Inisialisasi Pagination Penjualan Harian
    initDailyPagination();

    // Inisialisasi Pagination Riwayat Transaksi (5 per halaman)
    initTrxPagination();

    // Accordion Penjualan Harian
    window.toggleDayDetails = function(dateStr) {
        const detailPanel = document.getElementById('day-details-' + dateStr);
        const chevron = document.getElementById('chevron-' + dateStr);
        if (!detailPanel) return;

        const isHidden = (detailPanel.style.display === 'none' || detailPanel.style.display === '');
        if (isHidden) {
            detailPanel.style.display = 'block';
            if (chevron) chevron.style.transform = 'rotate(180deg)';
        } else {
            detailPanel.style.display = 'none';
            if (chevron) chevron.style.transform = 'rotate(0deg)';
        }
    };

    // Modal Detail Transaksi (Event Delegation)
    const transactions = @json($transactionDetails);
    window.closeTrxModal = function() {
        document.getElementById('trxDetailModal').classList.remove('active');
    };

    document.addEventListener('click', function(e) {
        const btn = e.target.closest('.view-trx-btn');
        if (!btn) return;
        const idx = parseInt(btn.dataset.index, 10);
        const trx = transactions[idx];
        if (!trx) return;

        document.getElementById('modalTrxCode').textContent = trx.code;
        document.getElementById('modalTrxDate').textContent = trx.date_formatted;
        document.getElementById('modalTrxBuyer').textContent = (trx.buyer && trx.buyer !== '-' ? trx.buyer : '-') + (trx.phone && trx.phone !== '-' ? ' (' + trx.phone + ')' : '');
        document.getElementById('modalTrxSource').textContent = trx.source;
        document.getElementById('modalTrxPayment').textContent = trx.payment_method;

        const tbody = document.getElementById('modalTrxItemsBody');
        tbody.innerHTML = '';

        trx.items.forEach(item => {
            tbody.innerHTML += `
                <tr>
                    <td>
                        <strong>${item.name}</strong>
                    </td>
                    <td style="text-align:center;">${item.quantity} bal</td>
                    <td style="text-align:right;">Rp ${item.unit_price.toLocaleString('id-ID')}</td>
                    <td style="text-align:right; font-weight:700;">Rp ${item.subtotal.toLocaleString('id-ID')}</td>
                </tr>
            `;
        });

        document.getElementById('modalTrxTotal').textContent = 'Rp ' + trx.total_revenue.toLocaleString('id-ID');

        const actionsDiv = document.getElementById('modalTrxActions');
        if (actionsDiv) {
            let actionsHtml = '';
            if (trx.source === 'Offline') {
                actionsHtml += `
                    <a href="/admin/transaksi-offline/${trx.raw_id}/struk" target="_blank" class="button button-small button-light" style="display:inline-flex; align-items:center; gap:4px;">
                        <svg style="width:12px; height:12px; fill:none; stroke:currentColor; stroke-width:2;" viewBox="0 0 24 24"><polyline points="6 9 6 2 18 2 18 9"></polyline><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path><rect x="6" y="14" width="12" height="8"></rect></svg>
                        <span>Cetak Struk</span>
                    </a>
                    <form method="post" action="/admin/transaksi-offline/${trx.raw_id}" onsubmit="return confirm('Batalkan (void) transaksi ini? Stok yang terjual akan otomatis dikembalikan ke inventori.')" style="margin:0;">
                        <input type="hidden" name="_token" value="{{ csrf_token() }}">
                        <input type="hidden" name="_method" value="DELETE">
                        <button type="submit" class="button button-small button-danger">Void Transaksi</button>
                    </form>
                `;
            } else {
                actionsHtml += `
                    <a href="/admin/pesanan/${trx.raw_id}" class="button button-small button-light">
                        Lihat Pesanan
                    </a>
                    <a href="/admin/pesanan/${trx.raw_id}/faktur" target="_blank" class="button button-small button-light">
                        Cetak Faktur
                    </a>
                `;
            }
            actionsHtml += `<button type="button" class="button button-small button-light" onclick="closeTrxModal()">Tutup</button>`;
            actionsDiv.innerHTML = actionsHtml;
        }

        document.getElementById('trxDetailModal').classList.add('active');
    });
});
</script>
@endsection
