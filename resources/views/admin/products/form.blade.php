@extends('layouts.admin')

@section('title', $product->exists ? 'Edit Produk' : 'Tambah Produk')
@section('section', 'Produk')

@section('content')
<div class="content-heading">
    <div>
        <span class="eyebrow">KATALOG TOKO</span>
        <h1>{{ $product->exists ? 'Edit produk' : 'Tambah produk' }}</h1>
        <p>Isi informasi produk yang akan ditampilkan di katalog toko.</p>
    </div>
    <a class="button button-light" href="{{ route('admin.produk.index') }}" style="display: inline-flex; align-items: center; gap: 6px;">
        <svg style="width: 15px; height: 15px; stroke: currentColor; fill: none; stroke-width: 2.2;" viewBox="0 0 24 24"><path d="m15 18-6-6 6-6"/></svg>
        <span>Kembali</span>
    </a>
</div>

<section class="form-card admin-form-card">
    <form method="post" action="{{ $product->exists ? route('admin.produk.update', $product) : route('admin.produk.store') }}" class="stack-form">
        @csrf
        @if ($product->exists) @method('PUT') @endif

        <input type="hidden" name="category" value="{{ old('category', $product->category ?: 'Kue Kering') }}">
        <input type="hidden" name="sales_channel" value="{{ old('sales_channel', $product->sales_channel ?: 'both') }}">
        <input type="hidden" name="cost_price" value="{{ old('cost_price', $product->cost_price ?? 0) }}">

        <label>
            Nama produk
            <input name="name" value="{{ old('name', $product->name) }}" placeholder="Contoh: Kastengel Keju Edam" required maxlength="160">
        </label>

        <label>
            Deskripsi produk
            <textarea name="description" rows="4" placeholder="Jelaskan keistimewaan kue..." required>{{ old('description', $product->description) }}</textarea>
        </label>

        <label>
            Harga jual per bal (Rp)
            <input type="number" name="price_per_bal" min="1" value="{{ old('price_per_bal', $product->price_per_bal) }}" placeholder="Contoh: 95000" required>
        </label>

        <label>
            Foto produk (URL)
            <input type="url" name="image" value="{{ old('image', $product->image) }}" placeholder="https://images.unsplash.com/...">
        </label>

        <div class="form-row">
            <label>
                Stok saat ini (bal)
                <input type="number" name="stock" min="0" value="{{ old('stock', $product->stock ?? 0) }}" required>
            </label>
            <label>
                Batas stok minimum (bal)
                <input type="number" name="min_stock" min="0" value="{{ old('min_stock', $product->min_stock ?? 10) }}" placeholder="Contoh: 10">
                <small class="muted">Status "Menipis" akan aktif jika stok &le; angka ini</small>
            </label>
        </div>

        <button class="button" type="submit">{{ $product->exists ? 'Simpan perubahan' : 'Tambah produk' }}</button>
    </form>
</section>
@endsection
