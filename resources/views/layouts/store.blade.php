<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Lintang Bakery')</title>
    @vite('resources/css/app.css')
</head>
<body>
    <header class="store-header">
        <a class="brand" href="{{ route('store.home') }}"><span class="brand-mark">L</span><span>Lintang Bakery<small>Portal Reseller & Distributor</small></span></a>
        <nav class="store-nav">
            <a href="{{ route('store.home') }}">Beranda</a>
            <a href="{{ route('store.products') }}">Produk</a>
            <a href="{{ route('cart.index') }}">Keranjang</a>
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
