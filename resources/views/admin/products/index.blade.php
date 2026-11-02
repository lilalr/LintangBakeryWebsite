@extends('layouts.admin')

@section('title', 'Manajemen Produk')
@section('section', 'Produk')

@section('content')
<div class="content-heading">
    <div>
        <span class="eyebrow">KATALOG TOKO</span>
        <h1>Manajemen produk</h1>
        <p>Katalog master produk, harga jual, dan ketersediaan stok.</p>
    </div>
    <a class="button" href="{{ route('admin.produk.create') }}" style="display: inline-flex; align-items: center; gap: 6px;">
        <svg style="width: 15px; height: 15px; stroke: currentColor; fill: none; stroke-width: 2.2;" viewBox="0 0 24 24"><path d="M12 5v14"/><path d="M5 12h14"/></svg>
        <span>Tambah produk</span>
    </a>
</div>

<section class="panel">
    <div class="table-scroll">
        <table class="products-table">
            <thead>
                <tr>
                    <th style="width: 45%;">Produk</th>
                    <th style="width: 25%;">Harga Jual / bal</th>
                    <th style="width: 15%;">Stok</th>
                    <th style="width: 15%;">Aksi</th>
                </tr>
            </thead>
            <tbody>
            @forelse ($products as $product)
                <tr>
                    <td>
                        <div class="table-product">
                            <img src="{{ $product->image ?: 'https://images.unsplash.com/photo-1509440159596-0249088772ff?w=400&q=80' }}" alt="{{ $product->name }}">
                            <div>
                                <strong>{{ $product->name }}</strong>
                                <small>{{ \Illuminate\Support\Str::limit($product->description, 70) }}</small>
                            </div>
                        </div>
                    </td>
                    <td>
                        <strong style="color: var(--brown); font-size: 14px;">Rp {{ number_format($product->price_per_bal, 0, ',', '.') }}</strong>
                    </td>
                    <td>
                        <strong class="{{ $product->stock <= $product->min_stock ? 'text-warning' : '' }}">{{ $product->stock }} bal</strong>
                    </td>
                    <td>
                        <div class="action-row">
                            <a class="button button-small button-light" href="{{ route('admin.produk.edit', $product) }}">Edit</a>
                            <form method="post" action="{{ route('admin.produk.destroy', $product) }}" onsubmit="return confirm('Hapus produk ini?')">
                                @csrf
                                @method('DELETE')
                                <button class="button button-small button-danger">Hapus</button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" class="table-empty">Belum ada produk. Tambahkan produk pertama.</td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>
    <div class="pagination-wrap">{{ $products->links() }}</div>
</section>
@endsection
