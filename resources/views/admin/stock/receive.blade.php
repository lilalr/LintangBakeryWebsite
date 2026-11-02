@extends('layouts.admin')

@section('title', 'Barang Masuk')
@section('section', 'Stok / Barang Masuk')

@section('content')
<div class="content-heading">
    <div>
        <span class="eyebrow">PERSEDIAAN OFFLINE</span>
        <h1>Barang masuk</h1>
        <p>Catat hasil produksi atau penerimaan barang dari sumber lain. Stok akan bertambah otomatis.</p>
    </div>
</div>
@include('admin.stock._nav', ['active' => 'masuk'])

@if ($errors->any())
    <div class="alert alert-error" style="margin-bottom:16px;">
        <ul style="margin:0; padding-left:18px;">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<form method="post" action="{{ route('admin.stock.receive') }}">
    @csrf
    <section class="panel">
        <div class="filter-bar" style="flex-wrap:wrap; gap:16px;">
            <label style="min-width:160px;">
                Tanggal masuk
                <input type="date" name="date" value="{{ old('date', now()->toDateString()) }}" required>
            </label>
            <label style="min-width:220px;">
                Produk
                <select name="product_id" required>
                    <option value="">— Pilih produk —</option>
                    @foreach ($products as $product)
                        <option value="{{ $product->id }}" {{ old('product_id') == $product->id ? 'selected' : '' }}>
                            {{ $product->name }} (stok: {{ $product->stock }} bal)
                        </option>
                    @endforeach
                </select>
            </label>
            <label style="min-width:130px;">
                Jumlah (bal)
                <input type="number" name="quantity" min="1" max="100000"
                       value="{{ old('quantity') }}" required placeholder="0">
            </label>
            <label style="min-width:180px;">
                Sumber
                <select name="source" required>
                    <option value="">— Pilih sumber —</option>
                    @foreach (\App\Services\StockService::BATCH_SOURCES as $src)
                        <option value="{{ $src }}" {{ old('source') === $src ? 'selected' : '' }}>{{ $src }}</option>
                    @endforeach
                </select>
            </label>
            <label style="min-width:160px;">
                Tanggal kedaluwarsa <span class="form-hint">(opsional)</span>
                <input type="date" name="expiry_date" value="{{ old('expiry_date') }}">
            </label>
            <label style="flex:1; min-width:220px;">
                Catatan <span class="form-hint">(opsional)</span>
                <input type="text" name="note" value="{{ old('note') }}" maxlength="1000"
                       placeholder="Misal: produksi hari ini">
            </label>
        </div>
    </section>
    <button class="button" type="submit" style="margin-top:16px">Simpan barang masuk</button>
</form>
<p class="form-hint" style="margin-top:12px">
    Barang masuk dari sumber <strong>Produksi</strong>, <strong>Retur</strong>, atau <strong>Lainnya</strong>
    akan membentuk batch yang bisa dilacak kedaluwarsanya.
</p>
@endsection
