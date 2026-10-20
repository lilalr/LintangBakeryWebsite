<article class="product-card" onclick="if (!event.target.closest('form') && !event.target.closest('button') && !event.target.closest('a')) window.location='{{ route('store.product', $product) }}';" style="cursor: pointer;">
    <a href="{{ route('store.product', $product) }}" class="product-image">
        <img src="{{ $product->image ?: 'https://images.unsplash.com/photo-1558961363-fa8fdf82db35?w=600&h=500&fit=crop&auto=format' }}" alt="{{ $product->name }}" loading="lazy">
    </a>
    <div class="product-card-body">
        <h3><a href="{{ route('store.product', $product) }}">{{ $product->name }}</a></h3>
        <p>{{ \Illuminate\Support\Str::limit($product->description, 92) }}</p>
        <div class="product-card-foot">
            <strong>Rp {{ number_format($product->price_per_bal, 0, ',', '.') }} <small>/ bal</small></strong>
            <form method="post" action="{{ route('cart.store', $product) }}" onclick="event.stopPropagation();">
                @csrf
                <input type="hidden" name="quantity" value="1">
                <button class="icon-button" aria-label="Tambah ke keranjang" title="Tambah ke keranjang">+</button>
            </form>
        </div>
    </div>
</article>
