@extends('layouts.admin')

@section('title', 'Manajemen Produk')
@section('section', 'Produk')

@section('content')
<div class="content-heading"><div><span class="eyebrow">KATALOG TOKO</span><h1>Manajemen produk</h1><p>Isi, harga, kanal penjualan, dan stok produk.</p></div><a class="button" href="{{ route('admin.produk.create') }}">+ Tambah produk</a></div>
<section class="panel">
    <div class="table-scroll"><table class="products-table"><thead><tr><th>Produk</th><th>Kategori</th><th>Harga / bal</th><th>Kanal</th><th>Stok</th><th>Aksi</th></tr></thead><tbody>
    @forelse ($products as $product)
        <tr><td><div class="table-product"><img src="{{ $product->image }}" alt=""><div><strong>{{ $product->name }}</strong><small>{{ \Illuminate\Support\Str::limit($product->description, 70) }}</small></div></div></td><td>{{ $product->category }}</td><td>Rp {{ number_format($product->price_per_bal, 0, ',', '.') }}</td><td><span class="status-pill">{{ ucfirst($product->sales_channel) }}</span></td><td>{{ $product->stock }} bal</td>
            <td><div class="action-row"><a class="button button-small button-light" href="{{ route('admin.produk.edit', $product) }}">Edit</a><form method="post" action="{{ route('admin.produk.destroy', $product) }}" onsubmit="return confirm('Hapus produk ini?')">@csrf @method('DELETE')<button class="button button-small button-danger">Hapus</button></form></div></td></tr>
    @empty<tr><td colspan="6" class="table-empty">Belum ada produk. Tambahkan produk pertama.</td></tr>@endforelse
    </tbody></table></div>
    <div class="pagination-wrap">{{ $products->links() }}</div>
</section>
@endsection
