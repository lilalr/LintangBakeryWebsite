@extends('layouts.admin')

@section('title', 'Stok Opname')
@section('section', 'Stok / Stok Opname')

@section('content')
<div class="content-heading"><div><span class="eyebrow">PERSEDIAAN OFFLINE</span><h1>Stok opname</h1><p>Hitung stok fisik di toko, lalu isi jumlahnya. Selisih dengan stok sistem dicatat otomatis sebagai mutasi baru di Kartu Stok. Kosongkan produk yang tidak dihitung.</p></div></div>
@include('admin.stock._nav', ['active' => 'opname'])

@if ($errors->any())
    <div class="alert alert-error" style="margin-bottom:16px;">
        <ul style="margin:0; padding-left:18px;">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<form method="post" action="{{ route('admin.stock.opname') }}">@csrf
<section class="panel">
    <div class="filter-bar">
        <label>Tanggal opname<input type="date" name="date" value="{{ old('date', now()->toDateString()) }}" required></label>
        <label>Catatan umum <span class="form-hint">(opsional)</span><input name="note" value="{{ old('note') }}" maxlength="500" placeholder="Misal: opname akhir bulan"></label>
    </div>
    <div class="table-scroll"><table><thead><tr><th>Produk</th><th>Stok sistem</th><th>Stok fisik</th><th>Selisih</th><th>Alasan selisih</th><th>Catatan tambahan</th></tr></thead><tbody>
    @forelse ($products as $product)
        <tr>
            <td><div class="table-product"><img src="{{ $product->image }}" alt=""><strong>{{ $product->name }}</strong></div></td>
            <td><strong>{{ $product->stock }} bal</strong></td>
            <td><input type="number" min="0" name="physical[{{ $product->id }}]" value="{{ old('physical.'.$product->id) }}" data-system="{{ $product->stock }}" class="opname-input" style="max-width:130px"></td>
            <td><span class="opname-diff muted">–</span></td>
            <td>
                <select name="reason[{{ $product->id }}]" class="opname-reason" style="max-width:200px">
                    <option value="">— Pilih alasan —</option>
                    <option value="Kesalahan Hitung" @selected(old('reason.'.$product->id) === 'Kesalahan Hitung')>Kesalahan Hitung</option>
                    <option value="Kesalahan Melayani" @selected(old('reason.'.$product->id) === 'Kesalahan Melayani')>Kesalahan Melayani</option>
                    <option value="Pencurian" @selected(old('reason.'.$product->id) === 'Pencurian')>Pencurian</option>
                    <option value="Lainnya" @selected(old('reason.'.$product->id) === 'Lainnya')>Lainnya</option>
                </select>
            </td>
            <td><input type="text" name="note_product[{{ $product->id }}]" value="{{ old('note_product.'.$product->id) }}" maxlength="500" placeholder="Catatan (opsional)" style="max-width:220px"></td>
        </tr>
    @empty<tr><td colspan="6" class="table-empty">Belum ada produk untuk penjualan offline.</td></tr>@endforelse
    </tbody></table></div>
</section>
<button class="button" type="submit" style="margin-top:16px">Simpan hasil opname</button>
</form>
<p class="form-hint" style="margin-top:12px">Selisih negatif (stok fisik lebih sedikit) dicatat sebagai Barang keluar, selisih positif sebagai Barang masuk, dengan alasan Opname. Alasan selisih disimpan sebagai catatan di Kartu Stok. Opname tidak dihitung sebagai kerugian rusak/kedaluwarsa.</p>

{{-- Rekap Kedaluwarsa Bulanan --}}
<section class="panel section-gap">
    <div class="panel-heading"><div><h2>Rekap kedaluwarsa bulanan</h2><p>Produk yang dikeluarkan karena kedaluwarsa, dikelompokkan per bulan. Data diambil dari riwayat Kartu Stok.</p></div></div>
    <div class="table-scroll"><table><thead><tr><th>Bulan</th><th>Produk</th><th>Total (bal)</th><th>Frekuensi</th></tr></thead><tbody>
    @php
        $namaBulan = ['', 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
    @endphp
    @forelse ($rekapKedaluwarsa as $rekap)
        <tr>
            <td>{{ $namaBulan[$rekap->bulan] }} {{ $rekap->tahun }}</td>
            <td>{{ $rekap->product_name }}</td>
            <td><strong>{{ $rekap->total_quantity }} bal</strong></td>
            <td>{{ $rekap->total_kejadian }}×</td>
        </tr>
    @empty
        <tr><td colspan="4" class="table-empty">Belum ada data kedaluwarsa. Data akan muncul setelah ada batch yang dikeluarkan karena kedaluwarsa.</td></tr>
    @endforelse
    </tbody></table></div>
</section>

<script>
document.querySelectorAll('.opname-input').forEach(function (input) {
    function update() {
        var diff = input.closest('tr').querySelector('.opname-diff');
        if (input.value === '') { diff.textContent = '–'; diff.className = 'opname-diff muted'; return; }
        var d = parseInt(input.value, 10) - parseInt(input.dataset.system, 10);
        diff.textContent = d === 0 ? 'Sesuai' : (d > 0 ? '+' + d : String(d));
        diff.className = 'opname-diff ' + (d === 0 ? 'num-in' : (d > 0 ? 'num-in' : 'num-out'));
    }
    input.addEventListener('input', update);
    update();
});
</script>
@endsection
