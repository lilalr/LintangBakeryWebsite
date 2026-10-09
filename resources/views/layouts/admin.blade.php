<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Admin') · Lintang Bakery</title>
    @vite('resources/css/app.css')
</head>
<body class="admin-body">
    <aside class="admin-sidebar">
        <a class="brand admin-brand" href="{{ route('admin.dashboard') }}"><span class="brand-mark">L</span><span>Lintang Bakery<small>Panel Administrator</small></span></a>
        <div class="side-label">MENU UTAMA</div>
        <nav class="admin-nav">
            <a class="{{ request()->routeIs('admin.dashboard') ? 'active' : '' }}" href="{{ route('admin.dashboard') }}">▦ <span>Dashboard</span></a>
            <a class="{{ request()->routeIs('admin.produk.*') ? 'active' : '' }}" href="{{ route('admin.produk.index') }}">◇ <span>Produk</span></a>
            <a class="{{ request()->routeIs('admin.stock.*') ? 'active' : '' }}" href="{{ route('admin.stock.index') }}">▤ <span>Stok</span></a>
            <a class="{{ request()->routeIs('admin.orders.*') ? 'active' : '' }}" href="{{ route('admin.orders.index') }}">▣ <span>Pesanan</span></a>
            <a class="{{ request()->routeIs('admin.transactions.*') ? 'active' : '' }}" href="{{ route('admin.transactions.index') }}">＋ <span>Transaksi Offline</span></a>
            <a class="{{ request()->routeIs('admin.reports.*') ? 'active' : '' }}" href="{{ route('admin.reports.index') }}">▥ <span>Laporan</span></a>
        </nav>
        <div class="sidebar-bottom">
            <a href="{{ route('store.home') }}">← Lihat halaman user</a>
            <form method="post" action="{{ route('logout') }}">@csrf<button class="link-button" type="submit">Keluar dari admin</button></form>
        </div>
    </aside>
    <div class="admin-main">
        <header class="admin-topbar"><span>Panel Admin <span class="muted">/ @yield('section', 'Dashboard')</span></span><span class="role-pill">ADMIN · {{ auth()->user()->name }}</span></header>
        <main class="admin-content">
            @include('partials.flash')
            @yield('content')
        </main>
    </div>
</body>
</html>
