@extends('layouts.store')

@section('title', 'Masuk · Lintang Bakery')

@section('content')
<section class="auth-wrap">
    <div class="auth-card">
        <div class="auth-heading"><span class="brand-mark">L</span><h1>Selamat datang</h1><p>Masuk ke portal Lintang Bakery</p></div>
        <form method="post" action="{{ route('login.store') }}" class="stack-form">
            @csrf
            <label>Email<input type="email" name="email" value="{{ old('email') }}" required autocomplete="email"></label>
            <label>Password<input type="password" name="password" required autocomplete="current-password"></label>
            <label class="check-label"><input type="checkbox" name="remember" value="1"> Ingat saya</label>
            <button class="button button-full" type="submit">Masuk</button>
        </form>
        <div class="demo-box"><strong>Akun demo</strong><br>Admin: admin@lintangbakery.id / admin123<br>User: buyer@test.com / password</div>
        <p class="auth-switch">Belum punya akun? <a href="{{ route('register') }}">Daftar sebagai user</a></p>
    </div>
</section>
@endsection
