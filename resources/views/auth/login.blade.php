@extends('layouts.store')

@section('title', 'Masuk · Lintang Bakery')

@section('content')
<section class="auth-wrap">
    <div class="auth-card">
        <div class="auth-heading"><span class="brand-mark">L</span><h1>Selamat datang</h1><p>Masuk ke portal Lintang Bakery</p></div>
        <form method="post" action="{{ route('login.store') }}" class="stack-form">
            @csrf
            <label>Email<input type="email" name="email" value="{{ old('email') }}" required autocomplete="email"></label>
            <label>Password
                <span class="password-field" data-password-field>
                    <input type="password" name="password" aria-label="Password" required autocomplete="current-password">
                    <button class="password-toggle" type="button" aria-label="Lihat password" aria-pressed="false">
                        <svg data-password-icon viewBox="0 0 24 24" fill="none" aria-hidden="true">
                            <path d="M2.5 12s3.5-6 9.5-6 9.5 6 9.5 6-3.5 6-9.5 6-9.5-6-9.5-6Z" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/>
                            <circle cx="12" cy="12" r="2.5" stroke="currentColor" stroke-width="1.7"/>
                        </svg>
                        <svg data-password-icon viewBox="0 0 24 24" fill="none" aria-hidden="true" hidden>
                            <path d="m3 3 18 18M10.6 6.2A9.8 9.8 0 0 1 12 6c6 0 9.5 6 9.5 6a16 16 0 0 1-3.1 3.7M6.2 6.2C3.8 7.8 2.5 12 2.5 12s3.5 6 9.5 6c1 0 1.9-.2 2.7-.5" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </button>
                </span>
            </label>
            <label class="check-label"><input type="checkbox" name="remember" value="1"> Ingat saya</label>
            <button class="button button-full" type="submit">Masuk</button>
        </form>
        <div class="demo-box"><strong>Akun demo</strong><br>Admin: admin@lintangbakery.id / admin123<br>User: buyer@test.com / password</div>
        <p class="auth-switch">Belum punya akun? <a href="{{ route('register') }}">Daftar sebagai user</a></p>
    </div>
</section>
@endsection
