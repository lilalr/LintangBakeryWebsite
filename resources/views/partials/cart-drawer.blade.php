<div id="cartDrawerOverlay" class="cart-drawer-overlay {{ session('open_cart') ? 'active' : '' }}" aria-hidden="{{ session('open_cart') ? 'false' : 'true' }}"></div>

<aside id="cartDrawer" class="cart-drawer {{ session('open_cart') ? 'active' : '' }}" aria-label="Keranjang Belanja" aria-hidden="{{ session('open_cart') ? 'false' : 'true' }}">
    <div class="cart-drawer-header">
        <h2 class="cart-drawer-title">
            <span>KERANJANG</span>
            @if (($cartDrawerCount ?? 0) > 0)
                <span class="badge">{{ $cartDrawerCount > 99 ? '99+' : $cartDrawerCount }}</span>
            @endif
        </h2>
        <button type="button" class="cart-drawer-close" id="closeCartDrawerBtn" aria-label="Tutup keranjang">✕</button>
    </div>

    <div class="cart-drawer-body">
        @if (!empty($cartDrawerItems) && count($cartDrawerItems) > 0)
            @foreach ($cartDrawerItems as $item)
                <div class="cart-drawer-item">
                    <img src="{{ $item['product']->image }}" alt="{{ $item['product']->name }}" class="cart-drawer-item-img">
                    <div class="cart-drawer-item-info">
                        <div>
                            <h3 class="cart-drawer-item-name">{{ $item['product']->name }}</h3>
                            <div class="cart-drawer-item-price">
                                Rp {{ number_format($item['product']->price_per_bal, 0, ',', '.') }}
                                <span style="font-size: 11px; font-weight: normal; color: var(--muted);">/ bal</span>
                            </div>
                        </div>
                        <div class="cart-drawer-item-actions">
                            <div class="cart-drawer-stepper">
                                <form method="post" action="{{ route('cart.update', $item['product']) }}" style="display:inline; margin:0;">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="quantity" value="{{ max(1, $item['quantity'] - 1) }}">
                                    <button type="submit" aria-label="Kurangi jumlah" @disabled($item['quantity'] <= 1)>−</button>
                                </form>
                                <span aria-live="polite">{{ $item['quantity'] }}</span>
                                <form method="post" action="{{ route('cart.update', $item['product']) }}" style="display:inline; margin:0;">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="quantity" value="{{ $item['quantity'] + 1 }}">
                                    <button type="submit" aria-label="Tambah jumlah" @disabled($item['quantity'] >= 10000)>+</button>
                                </form>
                            </div>
                            <form method="post" action="{{ route('cart.destroy', $item['product']) }}" style="display:inline; margin:0;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="cart-drawer-remove">HAPUS</button>
                            </form>
                        </div>
                    </div>
                </div>
            @endforeach
        @else
            <div class="cart-drawer-empty">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="9" cy="21" r="1"></circle>
                    <circle cx="20" cy="21" r="1"></circle>
                    <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path>
                </svg>
                <h3>Keranjang Anda Kosong</h3>
                <p>Yuk, pilih kue kering favoritmu sekarang!</p>
                <a href="{{ route('store.products') }}" class="button button-small" id="browseProductsBtn">Lihat Produk</a>
            </div>
        @endif
    </div>

    @if (!empty($cartDrawerItems) && count($cartDrawerItems) > 0)
        <div class="cart-drawer-footer">
            <div class="cart-drawer-subtotal">
                <span>Subtotal ({{ $cartDrawerCount }} bal):</span>
                <strong>Rp {{ number_format($cartDrawerTotal, 0, ',', '.') }}</strong>
            </div>
            <p class="cart-drawer-note">Ongkos kirim & pembayaran dihitung saat checkout.</p>
            <a href="{{ route('checkout.index') }}" class="cart-drawer-checkout-btn">
                <span>CHECKOUT</span>
                <span>•</span>
                <span>Rp {{ number_format($cartDrawerTotal, 0, ',', '.') }}</span>
            </a>
        </div>
    @endif
</aside>
