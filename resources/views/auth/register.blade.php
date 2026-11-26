@extends('layouts.store')

@section('title', 'Daftar · Lintang Bakery')

@section('content')
<section class="auth-wrap">
    <div class="auth-card">
        <div class="auth-heading"><span class="brand-mark"><svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m2 7 4.41-4.41A2 2 0 0 1 7.83 2h8.34a2 2 0 0 1 1.42.59L22 7"/><path d="M4 12v8a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-8"/><path d="M15 22v-4a2 2 0 0 0-2-2h-2a2 2 0 0 0-2 2v4"/><path d="M2 7h20"/><path d="M22 7v3a2 2 0 0 1-2 2 2.7 2.7 0 0 1-1.59-.63.7.7 0 0 0-.82 0A2.7 2.7 0 0 1 16 12a2.7 2.7 0 0 1-1.59-.63.7.7 0 0 0-.82 0A2.7 2.7 0 0 1 12 12a2.7 2.7 0 0 1-1.59-.63.7.7 0 0 0-.82 0A2.7 2.7 0 0 1 8 12a2.7 2.7 0 0 1-1.59-.63.7.7 0 0 0-.82 0A2.7 2.7 0 0 1 4 12a2 2 0 0 1-2-2V7"/></svg></span><h1>Buat akun reseller</h1><p>Akun baru otomatis memiliki akses user, bukan admin.</p></div>
        <form method="post" action="{{ route('register.store') }}" class="stack-form">
            @csrf
            <label>Nama lengkap<input name="name" value="{{ old('name') }}" required maxlength="120"></label>
            <label>Email<input type="email" name="email" value="{{ old('email') }}" required></label>
            <label>No. telepon / WhatsApp<input name="phone" value="{{ old('phone') }}" required></label>
            <label>Alamat<textarea name="address" rows="3" required>{{ old('address') }}</textarea></label>
            <label>Password<input type="password" name="password" required minlength="8"></label>
            <label>Konfirmasi password<input type="password" name="password_confirmation" required minlength="8"></label>
            <button class="button button-full" type="submit">Daftar sekarang</button>
        </form>
        <p class="auth-switch">Sudah punya akun? <a href="{{ route('login') }}">Masuk</a></p>
    </div>
</section>
@endsection
