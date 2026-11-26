<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Lintang Bakery')</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    @vite('resources/css/app.css')
</head>
<body class="@yield('body_class')">
    @php($cartItemCount = array_sum(session('cart', [])))
    <header class="store-header">
        <a class="brand" href="{{ route('store.home') }}"><span class="brand-mark"><svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m2 7 4.41-4.41A2 2 0 0 1 7.83 2h8.34a2 2 0 0 1 1.42.59L22 7"/><path d="M4 12v8a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-8"/><path d="M15 22v-4a2 2 0 0 0-2-2h-2a2 2 0 0 0-2 2v4"/><path d="M2 7h20"/><path d="M22 7v3a2 2 0 0 1-2 2 2.7 2.7 0 0 1-1.59-.63.7.7 0 0 0-.82 0A2.7 2.7 0 0 1 16 12a2.7 2.7 0 0 1-1.59-.63.7.7 0 0 0-.82 0A2.7 2.7 0 0 1 12 12a2.7 2.7 0 0 1-1.59-.63.7.7 0 0 0-.82 0A2.7 2.7 0 0 1 8 12a2.7 2.7 0 0 1-1.59-.63.7.7 0 0 0-.82 0A2.7 2.7 0 0 1 4 12a2 2 0 0 1-2-2V7"/></svg></span><span>Lintang Bakery<small>Grosir & Reseller Kue Kering</small></span></a>
        <nav class="store-nav">
            <a href="{{ route('store.home') }}">Beranda</a>
            <a href="{{ route('store.products') }}">Produk</a>
            <a class="cart-link" href="{{ route('cart.index') }}" id="openCartDrawerBtn" aria-label="Keranjang belanja, {{ $cartDrawerCount ?? $cartItemCount }} produk" title="Keranjang belanja">
                <svg class="cart-icon" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                    <path d="M3 3h2l2.4 12.1a2 2 0 0 0 2 1.6h8.9a2 2 0 0 0 1.9-1.4L22 8H6" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                    <circle cx="10" cy="20" r="1" fill="currentColor"/>
                    <circle cx="18" cy="20" r="1" fill="currentColor"/>
                </svg>
                <span class="cart-label">Keranjang</span>
                @if (($cartDrawerCount ?? $cartItemCount) > 0)
                    <span class="cart-count">{{ ($cartDrawerCount ?? $cartItemCount) > 99 ? '99+' : ($cartDrawerCount ?? $cartItemCount) }}</span>
                @endif
            </a>
            @auth
                @if (auth()->user()->role === 'admin')
                    <a href="{{ route('admin.dashboard') }}">Dashboard Admin</a>
                @else
                    <a href="{{ route('buyer.dashboard') }}">Akun Saya</a>
                @endif
                <form method="post" action="{{ route('logout') }}">@csrf<button class="link-button" type="submit">Keluar</button></form>
            @else
                <a class="button button-small" href="{{ route('login') }}">Masuk</a>
            @endauth
        </nav>
    </header>
    <main class="container">
        @include('partials.flash')
        @yield('content')
    </main>
    <footer class="store-footer"><span>© {{ date('Y') }} Lintang Bakery</span><span>Kue kering premium untuk reseller dan distributor</span></footer>
    @include('partials.cart-drawer')
</body>
</html>
