@extends('layouts.admin')

@section('title', 'Stok Produk')
@section('section', 'Stok')

@section('content')
<div class="content-heading"><div><span class="eyebrow">PERSEDIAAN OFFLINE</span><h1>Stok produk</h1><p>Stok barang jadi untuk penjualan toko. Pesanan online dibuat sesuai pesanan (made by order), sehingga tidak memengaruhi stok.</p></div></div>
@include('admin.stock._nav', ['active' => 'ringkasan'])
@if ($low->isNotEmpty())
    <div class="alert alert-warning"><strong>Peringatan stok:</strong> {{ $low->map(fn ($p) => $p->name.' ('.strtolower($p->stockStatus()).', '.$p->stock.' bal)')->join(', ') }}. Rencanakan produksi berikutnya.</div>
@endif
@if ($expiring->isNotEmpty())
    <div class="alert alert-error"><strong>Mendekati kedaluwarsa:</strong> {{ $expiring->map(fn ($b) => $b->product->name.' ('.($b->daysLeft() < 0 ? 'sudah kedaluwarsa' : $b->daysLeft().' hari lagi').')')->join(', ') }}. Jual lebih dulu barang yang dekat kedaluwarsa.</div>
@endif
<section class="panel"><div class="table-scroll"><table><thead><tr><th>Produk</th><th>Stok saat ini</th><th>Stok minimum</th><th>Status</th><th>Kedaluwarsa terdekat</th></tr></thead><tbody>
@forelse ($products as $product)
    @php
        $status = $product->stockStatus();
        $statusClass = ['Aman' => 'status-ok', 'Menipis' => 'status-low', 'Habis' => 'status-out'][$status];
        $batch = $product->batches->first();
    @endphp
    <tr>
        <td><div class="table-product"><img src="{{ $product->image }}" alt=""><strong>{{ $product->name }}</strong></div></td>
        <td><strong>{{ $product->stock }} bal</strong></td>
        <td>{{ $product->min_stock }} bal</td>
        <td><span class="status-pill {{ $statusClass }}">{{ $status }}</span></td>
        <td>@if ($batch){{ $batch->expiry_date->format('d/m/Y') }}<small class="{{ $batch->daysLeft() <= \App\Services\StockService::EXPIRY_ALERT_DAYS ? 'text-warning' : '' }}">{{ $batch->daysLeft() < 0 ? 'sudah lewat' : 'H-'.$batch->daysLeft() }}</small>@else<span class="muted">—</span>@endif</td>
    </tr>
@empty<tr><td colspan="5" class="table-empty">Belum ada produk untuk penjualan offline. Ubah kanal produk di halaman Produk.</td></tr>@endforelse
</tbody></table></div></section>
<section class="panel section-gap"><div class="panel-heading"><div><h2>Batch dengan tanggal kedaluwarsa</h2><p>Barang yang paling dekat kedaluwarsa dikeluarkan lebih dulu (FEFO). Batch yang sudah lewat tanggalnya bisa langsung dikeluarkan dan dicatat sebagai kerugian.</p></div></div>
    <div class="table-scroll"><table><thead><tr><th>Produk</th><th>Jumlah</th><th>Tanggal masuk</th><th>Kedaluwarsa</th><th>Status</th><th>Aksi</th></tr></thead><tbody>
    @forelse ($batches as $batch)
        @php
            $days = $batch->daysLeft();
            [$label, $class] = $days < 0 ? ['Kedaluwarsa', 'status-out'] : ($days <= \App\Services\StockService::EXPIRY_ALERT_DAYS ? ['H-'.$days, 'status-low'] : ['Aman', 'status-ok']);
        @endphp
        <tr>
            <td><strong>{{ $batch->product->name }}</strong></td>
            <td>{{ $batch->quantity }} bal</td>
            <td>{{ $batch->received_date->format('d/m/Y') }}</td>
            <td>{{ $batch->expiry_date->format('d/m/Y') }}</td>
            <td><span class="status-pill {{ $class }}">{{ $label }}</span></td>
            <td>@if ($days < 0)<form method="post" action="{{ route('admin.stock.batch.expire', $batch) }}" onsubmit="return confirm('Keluarkan {{ $batch->quantity }} bal {{ $batch->product->name }} yang kedaluwarsa? Dicatat sebagai kerugian.')">@csrf<button class="text-danger">Keluarkan</button></form>@else<span class="muted">—</span>@endif</td>
        </tr>
    @empty<tr><td colspan="6" class="table-empty">Belum ada batch dengan tanggal kedaluwarsa. Isi tanggal kedaluwarsa saat mencatat Barang masuk.</td></tr>@endforelse
    </tbody></table></div>
</section>
<p class="form-hint" style="margin-top:12px">Stok minimum diatur per produk di halaman Produk. Barang masuk dan keluar dicatat lewat tombol di atas.</p>
@endsection
