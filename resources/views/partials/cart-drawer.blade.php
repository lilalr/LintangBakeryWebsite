<div id="cartDrawerOverlay" class="cart-drawer-overlay {{ session('open_cart') ? 'active' : '' }}" aria-hidden="{{ session('open_cart') ? 'false' : 'true' }}"></div>

<aside id="cartDrawer" class="cart-drawer {{ session('open_cart') ? 'active' : '' }}" aria-label="Keranjang Belanja" aria-hidden="{{ session('open_cart') ? 'false' : 'true' }}">
    <div class="cart-drawer-header">
        <h2 class="cart-drawer-title">
            <span>KERANJANG RESELLER</span>
            @if (($cartDrawerCount ?? 0) > 0)
                <span class="badge">{{ $cartDrawerCount > 99 ? '99+' : $cartDrawerCount }} bal</span>
            @endif
        </h2>
        <button type="button" class="cart-drawer-close" id="closeCartDrawerBtn" aria-label="Tutup keranjang">✕</button>
    </div>

    <div class="cart-drawer-body">
        @if (!empty($cartDrawerItems) && count($cartDrawerItems) > 0)
            @foreach ($cartDrawerItems as $item)
                @php
                    $isItemUnder50 = $item['quantity'] < 50;
                @endphp
                <div class="cart-drawer-item" style="{{ $isItemUnder50 ? 'background: #fffdf5;' : '' }}">
                    <img src="{{ $item['product']->image }}" alt="{{ $item['product']->name }}" class="cart-drawer-item-img">
                    <div class="cart-drawer-item-info">
                        <div>
                            <h3 class="cart-drawer-item-name">{{ $item['product']->name }}</h3>
                            <div class="cart-drawer-item-price">
                                Rp {{ number_format($item['product']->price_per_bal, 0, ',', '.') }}
                                <span style="font-size: 11px; font-weight: normal; color: var(--muted);">/ bal</span>
                            </div>
                            @if ($isItemUnder50)
                                <div style="display: flex; align-items: center; gap: 6px; margin-top: 3px;">
                                    <span style="font-size: 10px; color: #b45309; font-weight: 700;">
                                        Min. mix 50 bal ({{ $item['quantity'] }}/50)
                                    </span>
                                    <form method="post" action="{{ route('cart.update', $item['product']) }}" style="margin: 0; display: inline;">
                                        @csrf
                                        @method('PATCH')
                                        <input type="hidden" name="quantity" value="50">
                                        <button type="submit" class="btn-quick-50" style="padding: 1px 6px; font-size: 9.5px;" title="Set 50 Bal">+50</button>
                                    </form>
                                </div>
                            @endif
                        </div>
                        <div class="cart-drawer-item-actions">
                            <div class="cart-drawer-stepper">
                                <form method="post" action="{{ route('cart.update', $item['product']) }}" style="display:inline; margin:0;">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="quantity" value="{{ $item['quantity'] > 10 ? $item['quantity'] - 10 : max(1, $item['quantity'] - 1) }}">
                                    <button type="submit" aria-label="Kurangi jumlah" @disabled($item['quantity'] <= 1)>−</button>
                                </form>
                                <span aria-live="polite" style="font-weight: 700; color: {{ $isItemUnder50 ? '#b45309' : 'inherit' }};">
                                    {{ $item['quantity'] }}
                                </span>
                                <form method="post" action="{{ route('cart.update', $item['product']) }}" style="display:inline; margin:0;">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="quantity" value="{{ $item['quantity'] < 10 ? 10 : $item['quantity'] + 10 }}">
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
                <p>Yuk, pilih camilan & kue kering grosir reseller di katalog!</p>
                <a href="{{ route('store.products') }}" class="button button-small" id="browseProductsBtn">Lihat Produk</a>
            </div>
        @endif
    </div>

    @if (!empty($cartDrawerItems) && count($cartDrawerItems) > 0)
        @php
            $minTotalRequired = 100;
            $minPerItemRequired = 50;

            $underMinCount = collect($cartDrawerItems)->filter(fn ($it) => $it['quantity'] < $minPerItemRequired)->count();
            $isDrawerTotalEligible = ($cartDrawerCount ?? 0) >= $minTotalRequired;
            $isDrawerEligible = $isDrawerTotalEligible && ($underMinCount === 0);
            $remainingDrawerBal = max(0, $minTotalRequired - ($cartDrawerCount ?? 0));
        @endphp
        <div class="cart-drawer-footer">
            <div class="cart-drawer-subtotal">
                <span>Total ({{ $cartDrawerCount }} bal):</span>
                <strong>Rp {{ number_format($cartDrawerTotal, 0, ',', '.') }}</strong>
            </div>

            @if ($underMinCount > 0)
                <div style="background:#fffbeb; border:1px solid #fde68a; border-radius:6px; padding:7px 10px; font-size:11px; color:#b45309; margin:6px 0; line-height:1.4;">
                    Setiap produk minimal <strong>50 bal</strong> jika di-mix (saat ini ada yang belum cukup).
                </div>
                <a href="{{ route('cart.index') }}" class="cart-drawer-checkout-btn" style="background:#b45309;">
                    <span>ATUR DI KERANJANG (MIN. 50/50)</span>
                </a>
            @elseif (!$isDrawerTotalEligible)
                <div style="background:#fff8f2; border:1px solid #fed7aa; border-radius:6px; padding:7px 10px; font-size:11px; color:#92400e; margin:6px 0; line-height:1.4;">
                    Min. reseller <strong>100 bal</strong> (saat ini: <strong>{{ $cartDrawerCount }} bal</strong>, kurang <strong>{{ $remainingDrawerBal }} bal</strong> lagi).
                </div>
                <a href="{{ route('cart.index') }}" class="cart-drawer-checkout-btn" style="background:#92400e;">
                    <span>LENGKAPI DI KERANJANG ({{ $cartDrawerCount }}/100)</span>
                </a>
            @else
                <p class="cart-drawer-note">✓ Min. order 100 bal (mix 50/50) terpenuhi.</p>
                <a href="{{ route('checkout.index') }}" class="cart-drawer-checkout-btn">
                    <span>CHECKOUT ({{ $cartDrawerCount }} BAL)</span>
                    <span>•</span>
                    <span>Rp {{ number_format($cartDrawerTotal, 0, ',', '.') }}</span>
                </a>
            @endif
        </div>
    @endif
</aside>
