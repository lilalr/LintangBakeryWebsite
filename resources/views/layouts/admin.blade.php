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
        <nav class="admin-nav">
            <div class="admin-nav-group">
                <div class="side-label">TOKO</div>
                <a class="{{ request()->routeIs('admin.dashboard') ? 'active' : '' }}" href="{{ route('admin.dashboard') }}">
                    <svg class="nav-icon" viewBox="0 0 24 24"><rect width="7" height="9" x="3" y="3" rx="1"/><rect width="7" height="5" x="14" y="3" rx="1"/><rect width="7" height="9" x="14" y="12" rx="1"/><rect width="7" height="5" x="3" y="16" rx="1"/></svg>
                    <span>Dashboard</span>
                </a>
                <a class="{{ request()->routeIs('admin.orders.*') ? 'active' : '' }}" href="{{ route('admin.orders.index') }}">
                    <svg class="nav-icon" viewBox="0 0 24 24"><path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4Z"/><path d="M3 6h18"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>
                    <span>Pesanan</span>
                </a>
                <a class="{{ request()->routeIs('admin.customers.*') ? 'active' : '' }}" href="{{ route('admin.customers.index') }}">
                    <svg class="nav-icon" viewBox="0 0 24 24"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                    <span>Pelanggan</span>
                </a>
                <a class="{{ request()->routeIs('admin.transactions.*') ? 'active' : '' }}" href="{{ route('admin.transactions.index') }}" aria-label="Transaksi offline">
                    <svg class="nav-icon" viewBox="0 0 24 24"><rect width="20" height="14" x="2" y="5" rx="2"/><line x1="2" x2="22" y1="10" y2="10"/></svg>
                    <span>Kasir</span>
                </a>
                <a class="{{ request()->routeIs('admin.reports.*') ? 'active' : '' }}" href="{{ route('admin.reports.index') }}">
                    <svg class="nav-icon" viewBox="0 0 24 24"><line x1="12" x2="12" y1="20" y2="10"/><line x1="18" x2="18" y1="20" y2="4"/><line x1="6" x2="6" y1="20" y2="16"/></svg>
                    <span>Laporan</span>
                </a>
            </div>
            <div class="admin-nav-group">
                <div class="side-label">INVENTORI</div>
                <a class="{{ request()->routeIs('admin.produk.*') ? 'active' : '' }}" href="{{ route('admin.produk.index') }}">
                    <svg class="nav-icon" viewBox="0 0 24 24"><path d="m7.5 4.27 9 5.15"/><path d="M21 8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16Z"/><path d="m3.3 7 8.7 5 8.7-5"/><path d="M12 22V12"/></svg>
                    <span>Produk</span>
                </a>
                <a class="{{ request()->routeIs('admin.stock.*') ? 'active' : '' }}" href="{{ route('admin.stock.index') }}">
                    <svg class="nav-icon" viewBox="0 0 24 24"><path d="m12.83 2.18a2 2 0 0 0-1.66 0L2.6 6.08a1 1 0 0 0 0 1.83l8.58 3.91a2 2 0 0 0 1.66 0l8.58-3.9a1 1 0 0 0 0-1.83Z"/><path d="m22 12.5-8.58 3.91a2 2 0 0 1-1.66 0L2 12.5"/><path d="m22 17.5-8.58 3.91a2 2 0 0 1-1.66 0L2 17.5"/></svg>
                    <span>Stok</span>
                </a>
            </div>
        </nav>
        <div class="sidebar-bottom">
            <a href="{{ route('store.home') }}">
                <svg class="nav-icon" viewBox="0 0 24 24"><path d="M15 3h6v6"/><path d="M10 14 21 3"/><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/></svg>
                <span>Halaman Toko</span>
            </a>
            <form method="post" action="{{ route('logout') }}" style="margin: 0;">
                @csrf
                <button type="submit">
                    <svg class="nav-icon" viewBox="0 0 24 24"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" x2="9" y1="12" y2="12"/></svg>
                    <span>Keluar dari Admin</span>
                </button>
            </form>
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
