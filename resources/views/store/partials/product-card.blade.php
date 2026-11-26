<article class="product-card" onclick="if (!event.target.closest('form') && !event.target.closest('button') && !event.target.closest('a')) window.location='{{ route('store.product', $product) }}';" style="cursor: pointer;">
    <a href="{{ route('store.product', $product) }}" class="product-image">
        <img src="{{ $product->image ?: '/images/products/kueTambang.jpg' }}" alt="{{ $product->name }}" loading="lazy">
        <span class="product-tag">{{ $product->category }}</span>
    </a>
    <div class="product-card-body">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:6px;">
            <span style="font-size:10px; color:#92400e; font-weight:750; background:#fdf2e9; border:1px solid #fed7aa; padding:2px 7px; border-radius:4px;">Min. Mix 50 Bal</span>
            <span style="font-size:11px; color:var(--muted);">Grosir Reseller</span>
        </div>
        <h3><a href="{{ route('store.product', $product) }}">{{ $product->name }}</a></h3>
        <p>{{ \Illuminate\Support\Str::limit($product->description, 90) }}</p>
        <div class="product-card-foot">
            <strong>Rp {{ number_format($product->price_per_bal, 0, ',', '.') }} <small>/ bal</small></strong>
            <form method="post" action="{{ route('cart.store', $product) }}" onclick="event.stopPropagation();">
                @csrf
                <input type="hidden" name="quantity" value="50">
                <button class="icon-button" aria-label="Tambah 50 bal ke keranjang" title="Tambah 50 bal ke keranjang">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <line x1="12" y1="5" x2="12" y2="19"></line>
                        <line x1="5" y1="12" x2="19" y2="12"></line>
                    </svg>
                </button>
            </form>
        </div>
    </div>
</article>
