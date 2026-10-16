@extends('layouts.admin')

@section('title', 'Stok Produk')
@section('section', 'Stok')

@section('content')
<div class="content-heading"><div><span class="eyebrow">PERSEDIAAN OFFLINE</span><h1>Stok produk</h1><p>Tambah stok yang tersedia untuk transaksi offline. Stok berkurang otomatis saat transaksi disimpan.</p></div></div>
<section class="panel"><div class="table-scroll"><table><thead><tr><th>Produk</th><th>Stok saat ini</th><th>Tambah stok</th></tr></thead><tbody>
@forelse ($products as $product)
    <tr><td><div class="table-product"><img src="{{ $product->image }}" alt=""><strong>{{ $product->name }}</strong></div></td><td><strong class="{{ $product->stock < 50 ? 'text-warning' : '' }}">{{ $product->stock }} bal</strong></td><td><form method="post" action="{{ route('admin.stock.update', $product) }}" class="quantity-form">@csrf @method('PATCH')<input type="number" name="adjustment" min="1" placeholder="Jumlah bal" required><button class="button button-small">Tambah</button></form></td></tr>
@empty<tr><td colspan="3" class="table-empty">Belum ada produk untuk penjualan offline. Ubah kanal produk di halaman Produk.</td></tr>@endforelse
</tbody></table></div></section>
@endsection
