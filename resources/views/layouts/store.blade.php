<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Lintang Bakery')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    @php($cartItemCount = array_sum(session('cart', [])))
    <header class="store-header">
        <a class="brand" href="{{ route('store.home') }}"><span class="brand-mark">L</span><span>Lintang Bakery<small>Portal Reseller & Distributor</small></span></a>
        <nav class="store-nav">
            <a href="{{ route('store.home') }}">Beranda</a>
            <a href="{{ route('store.products') }}">Produk</a>
            <a class="cart-link" href="{{ route('cart.index') }}" aria-label="Keranjang belanja, {{ $cartItemCount }} produk" title="Keranjang belanja">
                <svg class="cart-icon" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                    <path d="M3 3h2l2.4 12.1a2 2 0 0 0 2 1.6h8.9a2 2 0 0 0 1.9-1.4L22 8H6" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                    <circle cx="10" cy="20" r="1" fill="currentColor"/>
                    <circle cx="18" cy="20" r="1" fill="currentColor"/>
                </svg>
                <span class="cart-label">Keranjang</span>
                @if ($cartItemCount > 0)
                    <span class="cart-count">{{ $cartItemCount > 99 ? '99+' : $cartItemCount }}</span>
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
</body>
</html>
