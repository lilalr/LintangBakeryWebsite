@extends('layouts.store')

@section('title', 'Masuk · Lintang Bakery')

@section('content')
<section class="auth-wrap">
    <div class="auth-card">
        <div class="auth-heading"><span class="brand-mark"><svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m2 7 4.41-4.41A2 2 0 0 1 7.83 2h8.34a2 2 0 0 1 1.42.59L22 7"/><path d="M4 12v8a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-8"/><path d="M15 22v-4a2 2 0 0 0-2-2h-2a2 2 0 0 0-2 2v4"/><path d="M2 7h20"/><path d="M22 7v3a2 2 0 0 1-2 2 2.7 2.7 0 0 1-1.59-.63.7.7 0 0 0-.82 0A2.7 2.7 0 0 1 16 12a2.7 2.7 0 0 1-1.59-.63.7.7 0 0 0-.82 0A2.7 2.7 0 0 1 12 12a2.7 2.7 0 0 1-1.59-.63.7.7 0 0 0-.82 0A2.7 2.7 0 0 1 8 12a2.7 2.7 0 0 1-1.59-.63.7.7 0 0 0-.82 0A2.7 2.7 0 0 1 4 12a2 2 0 0 1-2-2V7"/></svg></span><h1>Selamat datang</h1><p>Masuk ke portal Lintang Bakery</p></div>
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
