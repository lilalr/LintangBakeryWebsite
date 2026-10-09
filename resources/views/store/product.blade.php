@extends('layouts.store')

@section('title', $product->name.' · Lintang Bakery')

@section('content')
<div class="breadcrumbs"><a href="{{ route('store.home') }}">Beranda</a><span>/</span><a href="{{ route('store.products') }}">Produk</a><span>/</span><span>{{ $product->name }}</span></div>
<section class="product-detail">
    <div class="detail-image"><img src="{{ $product->image ?: 'https://images.unsplash.com/photo-1558961363-fa8fdf82db35?w=1000&h=700&fit=crop&auto=format' }}" alt="{{ $product->name }}"></div>
    <div class="detail-copy"><span class="eyebrow">{{ $product->category }}</span><h1>{{ $product->name }}</h1><p>{{ $product->description }}</p><div class="detail-price">Rp {{ number_format($product->price_per_bal, 0, ',', '.') }} <small>/ bal</small></div>
        <form method="post" action="{{ route('cart.store', $product) }}" class="add-form">@csrf<label>Jumlah bal<input type="number" name="quantity" value="1" min="1" required></label><button class="button" type="submit">Tambah ke keranjang</button></form>
        <a class="text-link" href="{{ route('store.products') }}">← Kembali ke katalog</a>
    </div>
</section>
@endsection
