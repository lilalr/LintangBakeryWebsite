@extends('layouts.admin')

@section('title', 'Laporan Penjualan')
@section('section', 'Laporan')

@section('content')
<div class="content-heading">
    <div><span class="eyebrow">RINGKASAN TOKO</span><h1>Laporan</h1></div>
    <a class="button button-light" href="{{ route('admin.reports.export', request()->query()) }}">Unduh CSV</a>
</div>

{{-- Filter Tanggal & Pilihan Rentang --}}
<section class="report-filter-bar">
    <form method="get" action="{{ route('admin.reports.index') }}" class="report-filter-inline" id="reportFilterForm">
        <div class="filter-dates">
            <label>
                <span>Dari</span>
                <input type="date" name="start_date" id="filterStartDate" value="{{ $startDate }}">
            </label>
            <span class="filter-separator">–</span>
            <label>
                <span>Sampai</span>
                <input type="date" name="end_date" id="filterEndDate" value="{{ $endDate }}">
            </label>
        </div>
        <button class="button" type="submit">Tampilkan</button>
    </form>

    <div class="filter-presets">
        <span class="preset-label">Rentang Cepat:</span>
        <button type="button" class="preset-btn" onclick="applyPreset('today')">Hari Ini</button>
        <button type="button" class="preset-btn" onclick="applyPreset('7days')">7 Hari Terakhir</button>
        <button type="button" class="preset-btn" onclick="applyPreset('this_month')">Bulan Ini</button>
        <button type="button" class="preset-btn" onclick="applyPreset('last_month')">Bulan Lalu</button>
        <button type="button" class="preset-btn" onclick="applyPreset('this_year')">Tahun Ini</button>
        <button type="button" class="preset-btn" onclick="applyPreset('all')">Semua Waktu</button>
    </div>
</section>

{{-- Tab Navigation --}}
<nav class="report-tabs" id="reportTabs">
    <button class="report-tab active" data-tab="sales" type="button">Sales</button>
    <button class="report-tab" data-tab="detail" type="button">Detail Transaksi ({{ $transactionDetails->count() }})</button>
    <button class="report-tab" data-tab="produk" type="button">Produk terlaris</button>
    <button class="report-tab" data-tab="harian" type="button">Penjualan harian</button>
</nav>

{{-- TAB 1: Sales (Ringkasan & Grafik) --}}
<div class="report-tab-content active" id="tab-sales">
    {{-- Ringkasan Penjualan Kotor --}}
    <section class="report-summary-card">
        <div class="summary-header">
            <span class="summary-period">Periode {{ \Illuminate\Support\Carbon::parse($startDate)->translatedFormat('d M Y') }} – {{ \Illuminate\Support\Carbon::parse($endDate)->translatedFormat('d M Y') }}</span>
            <strong class="summary-omzet">Rp {{ number_format($totalRevenue, 0, ',', '.') }}</strong>
            <span class="summary-label">Total Penjualan (Kotor)</span>
        </div>
    </section>

    {{-- Grafik Tren Penjualan --}}
    <section class="report-chart-card">
        <div class="chart-header">
            <div>
                <h3 id="chartTitle">Grafik Tren Penjualan</h3>
                <span class="muted" id="chartSubtitle" style="font-size:11px;">Tren penjualan harian (Rp)</span>
            </div>
            <div class="chart-controls">
                {{-- Toggle Pengelompokan: Per Hari / Per Bulan --}}
                <div class="chart-pill-group" title="Ubah tampilan pengelompokan waktu">
                    <button type="button" class="chart-pill active" id="btnGroupDaily" onclick="setChartGrouping('daily')">Per Hari</button>
                    <button type="button" class="chart-pill" id="btnGroupMonthly" onclick="setChartGrouping('monthly')">Per Bulan</button>
                </div>
                {{-- Toggle Visualisasi: Garis / Batang Ramping --}}
                <div class="chart-pill-group" title="Pilih tipe grafik">
                    <button type="button" class="chart-pill active" id="btnTypeLine" onclick="setChartType('line')">Garis</button>
                    <button type="button" class="chart-pill" id="btnTypeBar" onclick="setChartType('bar')">Batang</button>
                </div>
            </div>
        </div>
        <div class="chart-container">
            <canvas id="salesTrendChart"></canvas>
        </div>
    </section>

    {{-- Data Penjualan (gaya Starpos) --}}
    <section class="report-data-card">
        <h3>Data Penjualan</h3>
        <div class="data-list">
            <div class="data-row">
                <span>Jumlah transaksi</span>
                <strong>{{ number_format($totalTransactions, 0, ',', '.') }}</strong>
            </div>
            <div class="data-row">
                <span>Item Terjual</span>
                <strong>{{ number_format($totalItemsSold, 0, ',', '.') }}</strong>
            </div>
        </div>

        <div class="data-divider"></div>

        <div class="data-list">
            <div class="data-row">
                <span>Penjualan Kasir</span>
                <strong>Rp {{ number_format($offlineRevenue, 0, ',', '.') }}</strong>
            </div>
            <div class="data-row">
                <span>Penjualan Online</span>
                <strong>Rp {{ number_format($onlineRevenue, 0, ',', '.') }}</strong>
            </div>
        </div>

        <div class="data-divider"></div>

        <div class="data-section-title">Pembayaran</div>
        <div class="data-list">
            @foreach ($paymentBreakdown as $payment)
            <div class="data-row">
                <span>{{ $payment['label'] }}</span>
                <strong>Rp {{ number_format($payment['amount'], 0, ',', '.') }}</strong>
            </div>
            @endforeach
        </div>

        <div class="data-divider"></div>

        <div class="data-list">
            <div class="data-row omzet-row">
                <span>Total Penjualan</span>
                <strong class="text-brown">Rp {{ number_format($totalRevenue, 0, ',', '.') }}</strong>
            </div>
        </div>
    </section>
</div>

{{-- TAB 2: Detail Transaksi --}}
<div class="report-tab-content" id="tab-detail">
    <section class="panel">
        <div class="panel-heading">
            <div>
                <h2>Detail per transaksi</h2>
                <p>Klik "Lihat Rincian" untuk memeriksa barang yang dibeli dan total pembayaran pada transaksi tersebut.</p>
            </div>
        </div>
        <div class="table-scroll">
            <table>
                <thead>
                    <tr>
                        <th>Tanggal</th>
                        <th>Kode / No. Nota</th>
                        <th>Sumber</th>
                        <th>Pembeli</th>
                        <th>Metode Bayar</th>
                        <th>Total Penjualan</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                @forelse ($transactionDetails as $idx => $t)
                    <tr>
                        <td>
                            <strong>{{ $t['date_formatted'] }}</strong>
                        </td>
                        <td>
                            <code style="font-size:11px; background:#f5ece1; padding:3px 6px; border-radius:4px;">{{ $t['code'] }}</code>
                        </td>
                        <td>
                            @if ($t['source'] === 'Kasir')
                                <span class="trx-badge-kasir">Kasir</span>
                            @else
                                <span class="trx-badge-online">Online</span>
                            @endif
                        </td>
                        <td>
                            <strong>{{ $t['buyer'] }}</strong>
                        </td>
                        <td>{{ $t['payment_method'] }}</td>
                        <td>
                            <strong style="color:var(--brown); font-size:13px;">Rp {{ number_format($t['total_revenue'], 0, ',', '.') }}</strong>
                        </td>
                        <td>
                            <button type="button" class="button button-small button-light view-trx-btn" data-index="{{ $idx }}">
                                Lihat Rincian
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="table-empty">Belum ada transaksi terkonfirmasi pada periode ini.</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </section>
</div>

{{-- TAB 3: Produk Terlaris --}}
<div class="report-tab-content" id="tab-produk">
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
        <div class="report-empty">Belum ada data produk terjual.</div>
        @endforelse
    </section>
</div>

{{-- TAB 4: Penjualan Harian --}}
<div class="report-tab-content" id="tab-harian">
    @forelse ($dailyBreakdown as $row)
    @php
        $dayTrx = $transactionsByDate[$row['date']] ?? collect();
    @endphp
    <div class="daily-card-wrap" style="margin-bottom:12px; border:1px solid var(--line); border-radius:12px; background:white; overflow:hidden;">
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
                <span style="font-size:11.5px; color:var(--muted); display:block; margin-top:3px;">
                    Kasir Toko: Rp {{ number_format($row['offline'], 0, ',', '.') }} &bull; Pesanan Online: Rp {{ number_format($row['online'], 0, ',', '.') }}
                </span>
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
                                <th style="padding:9px 12px;">Waktu & Ref</th>
                                <th style="padding:9px 12px;">Tipe</th>
                                <th style="padding:9px 12px;">Pembeli</th>
                                <th style="padding:9px 12px;">Item Pembelian</th>
                                <th style="padding:9px 12px;">Metode Bayar</th>
                                <th style="padding:9px 12px; text-align:right;">Total</th>
                                <th style="padding:9px 12px; text-align:center;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($dayTrx as $tx)
                            <tr style="border-bottom:1px solid #f2e9df;">
                                <td style="padding:9px 12px; white-space:nowrap;">
                                    <strong>{{ \Carbon\Carbon::parse($tx['date'])->format('H:i') }} WIB</strong>
                                    <span class="badge" style="display:block; font-size:10px; padding:2px 6px; width:fit-content; margin-top:2px; background:#f0e6dc; color:var(--brown-dark);">{{ $tx['code'] }}</span>
                                </td>
                                <td style="padding:9px 12px;">
                                    @if ($tx['source'] === 'Online')
                                        <span class="badge badge-success" style="font-size:10px;">Online</span>
                                    @else
                                        <span class="badge badge-info" style="font-size:10px;">Kasir</span>
                                    @endif
                                </td>
                                <td style="padding:9px 12px;">
                                    <strong>{{ $tx['buyer'] }}</strong>
                                    @if(!empty($tx['phone']) && $tx['phone'] !== '-')
                                        <div style="font-size:10.5px; color:var(--muted);">{{ $tx['phone'] }}</div>
                                    @endif
                                </td>
                                <td style="padding:9px 12px; max-width:240px;">
                                    <div style="display:flex; flex-direction:column; gap:2px;">
                                        @foreach(collect($tx['items'])->take(3) as $it)
                                            <span style="font-size:11px; color:#444;">&bull; {{ $it['quantity'] }}x {{ $it['name'] }}</span>
                                        @endforeach
                                        @if(count($tx['items']) > 3)
                                            <span style="font-size:10px; color:var(--muted);">+{{ count($tx['items']) - 3 }} item lainnya</span>
                                        @endif
                                    </div>
                                </td>
                                <td style="padding:9px 12px; white-space:nowrap;">
                                    <span style="font-size:11.5px; color:#555;">{{ $tx['payment_method'] }}</span>
                                </td>
                                <td style="padding:9px 12px; text-align:right; white-space:nowrap;">
                                    <strong style="color:var(--brown); font-size:13px;">Rp {{ number_format($tx['total_revenue'], 0, ',', '.') }}</strong>
                                </td>
                                <td style="padding:9px 12px; text-align:center; white-space:nowrap;">
                                    <button type="button" class="button button-small button-light view-trx-btn" data-index="{{ $tx['global_index'] }}" style="padding:4px 9px; font-size:11px;">
                                        Lihat Rincian
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
    @empty
    <section class="report-data-card">
        <div class="report-empty">Belum ada penjualan pada periode ini.</div>
    </section>
    @endforelse
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
                <span class="muted">Tipe Transaksi:</span>
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

        <button type="button" class="button button-full button-light" style="margin-top:14px;" onclick="closeTrxModal()">
            Tutup
        </button>
    </div>
</div>

{{-- Chart.js CDN --}}
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
// Data grafik dari server
const chartData = @json($chartData);
let currentGrouping = 'daily';
let currentType = 'line';
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
        datasets = [
            {
                label: 'Total Penjualan (Rp)',
                data: activeData.total,
                borderColor: '#75462c',
                backgroundColor: 'rgba(117, 70, 44, 0.08)',
                borderWidth: 2.5,
                fill: true,
                tension: 0.35,
                pointBackgroundColor: '#75462c',
                pointBorderColor: '#ffffff',
                pointBorderWidth: 2,
                pointRadius: 4,
                pointHoverRadius: 6,
            },
            {
                label: 'Kasir Toko (Rp)',
                data: activeData.offline,
                borderColor: '#a3623b',
                backgroundColor: 'transparent',
                borderWidth: 1.8,
                borderDash: [4, 4],
                tension: 0.35,
                pointBackgroundColor: '#a3623b',
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
                tension: 0.35,
                pointBackgroundColor: '#d4a373',
                pointRadius: 3,
                fill: false,
            }
        ];
    } else {
        // Tipe Batang Ramping (maxBarThickness mencegah batang terlalu gemuk/lebar)
        datasets = [
            {
                label: 'Kasir Toko (Rp)',
                data: activeData.offline,
                backgroundColor: '#75462c',
                borderRadius: 6,
                maxBarThickness: 28,
            },
            {
                label: 'Pesanan Online (Rp)',
                data: activeData.online,
                backgroundColor: '#c49a6c',
                borderRadius: 6,
                maxBarThickness: 28,
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
                        font: { family: "'Instrument Sans', sans-serif", size: 11 },
                        color: '#555'
                    }
                },
                tooltip: {
                    backgroundColor: '#3b2214',
                    padding: 10,
                    cornerRadius: 8,
                    titleFont: { family: "'Instrument Sans', sans-serif", size: 12 },
                    bodyFont: { family: "'Instrument Sans', sans-serif", size: 11 },
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
                        font: { family: "'Instrument Sans', sans-serif", size: 11 },
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
                        font: { family: "'Instrument Sans', sans-serif", size: 10 },
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
        ? 'Tren omzet penjualan per bulan (Rp)'
        : 'Tren omzet penjualan harian (Rp)';
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

document.addEventListener('DOMContentLoaded', function() {
    // Tab switching
    const tabs = document.querySelectorAll('#reportTabs .report-tab');
    const contents = document.querySelectorAll('.report-tab-content');

    tabs.forEach(function(tab) {
        tab.addEventListener('click', function() {
            tabs.forEach(function(t) { t.classList.remove('active'); });
            contents.forEach(function(c) { c.classList.remove('active'); });

            tab.classList.add('active');
            document.getElementById('tab-' + tab.dataset.tab).classList.add('active');
        });
    });

    // Inisialisasi Grafik
    renderSalesChart();

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
        document.getElementById('modalTrxBuyer').textContent = trx.buyer + (trx.phone !== '-' ? ' (' + trx.phone + ')' : '');
        document.getElementById('modalTrxSource').textContent = trx.source + ' (' + trx.status + ')';
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

        document.getElementById('trxDetailModal').classList.add('active');
    });
});
</script>
@endsection
