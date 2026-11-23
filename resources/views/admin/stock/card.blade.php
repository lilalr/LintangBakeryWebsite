@extends('layouts.admin')

@section('title', 'Kartu Stok')
@section('section', 'Kartu Stok')

@section('content')
<div class="content-heading"><div><span class="eyebrow">RIWAYAT PERSEDIAAN</span><h1>Kartu stok</h1><p>Telusuri mutasi stok per produk untuk menemukan penyebab selisih. Riwayat tidak dapat diubah atau dihapus.</p></div></div>
@include('admin.stock._nav', ['active' => 'kartu'])
<section class="panel">
    <form method="get" action="{{ route('admin.stock.card') }}" class="filter-bar">
        <label>Produk<select name="product_id">@foreach ($products as $product)<option value="{{ $product->id }}" @selected($productId === $product->id)>{{ $product->name }}</option>@endforeach</select></label>
        <label>Dari tanggal<input type="date" name="start" value="{{ $start }}"></label>
        <label>Sampai tanggal<input type="date" name="end" value="{{ $end }}"></label>
        <button class="button button-small" type="submit">Tampilkan</button>
        @if ($start || $end)<a class="text-link" href="{{ route('admin.stock.card', ['product_id' => $productId]) }}">Reset tanggal</a>@endif
        <span class="spacer"></span>
        <a class="button button-small button-light" href="{{ route('admin.stock.card.export', array_filter(['product_id' => $productId, 'start' => $start, 'end' => $end])) }}">Ekspor Excel (CSV)</a>
        <button type="button" class="button button-small button-light" onclick="window.print()">Cetak</button>
    </form>
    <div class="panel-heading"><div><h2>{{ $products->firstWhere('id', $productId)?->name }}</h2><p>Periode: {{ $start ? \Illuminate\Support\Carbon::parse($start)->format('d/m/Y') : 'awal' }} sampai {{ $end ? \Illuminate\Support\Carbon::parse($end)->format('d/m/Y') : 'sekarang' }}</p></div></div>
    <div class="table-scroll"><table><thead><tr><th>Tanggal</th><th>Jenis</th><th>Jumlah</th><th>Alasan</th><th>Referensi</th><th>Admin</th><th>Saldo</th></tr></thead><tbody>
    @forelse ($movements as $movement)
        <tr>
            <td>{{ $movement->date->format('d/m/Y') }}</td>
            <td><span class="status-pill {{ $movement->type === 'Masuk' ? 'badge-in' : 'badge-out' }}">{{ $movement->type }}</span></td>
            <td class="{{ $movement->type === 'Masuk' ? 'num-in' : 'num-out' }}">{{ $movement->type === 'Masuk' ? '+' : '-' }}{{ $movement->quantity }}</td>
            <td>{{ $movement->reason }}@if ($movement->note)<small>{{ $movement->note }}</small>@endif</td>
            <td>{{ $movement->reference ?: '-' }}</td>
            <td>{{ $movement->admin_name }}</td>
            <td><strong>{{ $movement->balance }}</strong></td>
        </tr>
    @empty<tr><td colspan="7" class="table-empty">Belum ada mutasi untuk produk dan periode ini.</td></tr>@endforelse
    </tbody></table></div>
    <div class="pagination-wrap">{{ $movements->links() }}</div>
</section>
<p class="form-hint" style="margin-top:12px">Jika ada koreksi, catat dengan mutasi baru (Barang masuk atau Barang keluar), jangan mengubah baris lama.</p>
@endsection
