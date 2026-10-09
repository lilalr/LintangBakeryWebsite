@extends('layouts.admin')

@section('title', $product->exists ? 'Edit Produk' : 'Tambah Produk')
@section('section', 'Produk')

@section('content')
<div class="content-heading"><div><span class="eyebrow">KATALOG TOKO</span><h1>{{ $product->exists ? 'Edit produk' : 'Tambah produk' }}</h1><p>Isi informasi yang akan ditampilkan di katalog toko.</p></div><a class="button button-light" href="{{ route('admin.produk.index') }}">← Kembali</a></div>
<section class="form-card admin-form-card">
    <form method="post" action="{{ $product->exists ? route('admin.produk.update', $product) : route('admin.produk.store') }}" class="stack-form">
        @csrf
        @if ($product->exists) @method('PUT') @endif
        <div class="form-row"><label>Nama produk<input name="name" value="{{ old('name', $product->name) }}" required maxlength="160"></label><label>Kategori<input name="category" value="{{ old('category', $product->category ?: 'Kue Kering') }}" required></label></div>
        <label>Deskripsi<textarea name="description" rows="5" required>{{ old('description', $product->description) }}</textarea></label>
        <div class="form-row"><label>Harga per bal (Rp)<input type="number" name="price_per_bal" min="1" value="{{ old('price_per_bal', $product->price_per_bal) }}" required></label><label>Foto produk (URL)<input type="url" name="image" value="{{ old('image', $product->image) }}" placeholder="https://..."></label></div>
        <div class="form-row"><label>Kanal penjualan<select name="sales_channel" required>@foreach (['online' => 'Online (katalog)', 'offline' => 'Offline (toko fisik)', 'both' => 'Online dan offline'] as $value => $label)<option value="{{ $value }}" @selected(old('sales_channel', $product->sales_channel ?: 'online') === $value)>{{ $label }}</option>@endforeach</select></label><label>Stok offline (bal)<input type="number" name="stock" min="0" value="{{ old('stock', $product->stock ?? 0) }}" required></label></div>
        <button class="button" type="submit">{{ $product->exists ? 'Simpan perubahan' : 'Tambah produk' }}</button>
    </form>
</section>
@endsection
