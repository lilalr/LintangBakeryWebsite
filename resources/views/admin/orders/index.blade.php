@extends('layouts.admin')

@section('title', 'Pesanan')
@section('section', 'Pesanan')

@section('content')
<div class="content-heading">
    <div>
        <span class="eyebrow">PESANAN ONLINE</span>
        <h1>Kelola pesanan</h1>
        <p>Perbarui status produksi, pembayaran, dan pengiriman.</p>
    </div>
</div>

{{-- Filter Status Dropdown & Filter Tanggal (Ringkas & Horizontal) --}}
<section class="report-filter-bar" style="background:#ffffff; border:1px solid #ebdcd0; border-radius:10px; padding:14px 18px; margin-bottom:18px;">
    <form method="get" action="{{ route('admin.orders.index') }}" style="display:flex; flex-wrap:wrap; gap:12px; align-items:flex-end; margin:0;">
        {{-- Filter Status Pesanan (Dropdown) --}}
        <label style="display:flex; flex-direction:column; gap:4px; font-size:11px; font-weight:700; color:var(--muted); min-width:210px;">
            <span style="text-transform:uppercase; letter-spacing:.04em;">Status Pesanan</span>
            <select name="status" onchange="this.form.submit()" style="height:36px; min-height:36px; border:1px solid #dfd5cb; border-radius:6px; padding:6px 10px; font-size:12.5px; background:white; color:#2d1e15; cursor:pointer;">
                <option value="active" {{ ($currentStatus ?? 'active') === 'active' ? 'selected' : '' }}>Pesanan Aktif (Perlu Diproses)</option>
                <option value="all" {{ ($currentStatus ?? '') === 'all' ? 'selected' : '' }}>Semua Status</option>
                @foreach ($statuses as $st)
                    <option value="{{ $st }}" {{ ($currentStatus ?? '') === $st ? 'selected' : '' }}>{{ $st }}</option>
                @endforeach
            </select>
        </label>

        {{-- Urutkan Berdasarkan --}}
        <label style="display:flex; flex-direction:column; gap:4px; font-size:11px; font-weight:700; color:var(--muted); min-width:190px;">
            <span style="text-transform:uppercase; letter-spacing:.04em;">Urutkan</span>
            <select name="sort" onchange="this.form.submit()" style="height:36px; min-height:36px; border:1px solid #dfd5cb; border-radius:6px; padding:6px 10px; font-size:12.5px; background:white; color:#2d1e15; cursor:pointer;">
                <option value="priority" {{ ($currentSort ?? 'priority') === 'priority' ? 'selected' : '' }}>Paling Penting untuk Diupdate</option>
                <option value="latest" {{ ($currentSort ?? '') === 'latest' ? 'selected' : '' }}>Tanggal Terbaru</option>
                <option value="updated" {{ ($currentSort ?? '') === 'updated' ? 'selected' : '' }}>Terakhir Diupdate</option>
                <option value="oldest" {{ ($currentSort ?? '') === 'oldest' ? 'selected' : '' }}>Tanggal Terlama</option>
            </select>
        </label>

        {{-- Dari Tanggal --}}
        <label style="display:flex; flex-direction:column; gap:4px; font-size:11px; font-weight:700; color:var(--muted); min-width:140px;">
            <span style="text-transform:uppercase; letter-spacing:.04em;">Dari Tanggal</span>
            <input type="date" name="start_date" value="{{ $startDate ?? '' }}" style="height:36px; min-height:36px; border:1px solid #dfd5cb; border-radius:6px; padding:6px 10px; font-size:12.5px; background:white; color:#2d1e15;">
        </label>

        {{-- Sampai Tanggal --}}
        <label style="display:flex; flex-direction:column; gap:4px; font-size:11px; font-weight:700; color:var(--muted); min-width:140px;">
            <span style="text-transform:uppercase; letter-spacing:.04em;">Sampai Tanggal</span>
            <input type="date" name="end_date" value="{{ $endDate ?? '' }}" style="height:36px; min-height:36px; border:1px solid #dfd5cb; border-radius:6px; padding:6px 10px; font-size:12.5px; background:white; color:#2d1e15;">
        </label>

        {{-- Tombol Terapkan & Reset --}}
        <div style="display:flex; gap:6px; align-items:center; padding-bottom:1px;">
            <button class="button" type="submit" style="height:36px; min-height:36px; padding:0 16px; border-radius:6px; font-size:12px; font-weight:600; background:#62432b; color:white; white-space:nowrap;">
                Terapkan Filter
            </button>
            @if (request()->hasAny(['status', 'sort', 'start_date', 'end_date']) && (request('status') !== 'all' || (request('sort') && request('sort') !== 'priority') || request('start_date') || request('end_date')))
                <a href="{{ route('admin.orders.index') }}" class="button button-light" style="height:36px; min-height:36px; padding:0 12px; border-radius:6px; font-size:12px; white-space:nowrap; display:inline-flex; align-items:center;" title="Reset Semua Filter">
                    Reset
                </a>
            @endif
        </div>
    </form>
</section>

<section class="panel">
    @include('admin.orders.table', ['compact' => false])
    <div class="pagination-wrap">{{ $orders->links() }}</div>
</section>
@endsection
