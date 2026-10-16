@extends('layouts.store')

@section('title', 'Daftar · Lintang Bakery')

@section('content')
<section class="auth-wrap">
    <div class="auth-card">
        <div class="auth-heading"><span class="brand-mark">L</span><h1>Buat akun reseller</h1><p>Akun baru otomatis memiliki akses user, bukan admin.</p></div>
        <form method="post" action="{{ route('register.store') }}" class="stack-form">
            @csrf
            <label>Nama lengkap<input name="name" value="{{ old('name') }}" required maxlength="120"></label>
            <label>Email<input type="email" name="email" value="{{ old('email') }}" required></label>
            <label>No. telepon / WhatsApp<input name="phone" value="{{ old('phone') }}" required></label>
            <label>Alamat<textarea name="address" rows="3" required>{{ old('address') }}</textarea></label>
            
            {{-- TAMBAHAN: Dropdown pilihan Provinsi dan Kota saat registrasi --}}
            @include('store.partials.region-select', [
                'province' => old('province'),
                'city' => old('city'),
            ])

            <label>Password<input type="password" name="password" required minlength="8"></label>
            <label>Konfirmasi password<input type="password" name="password_confirmation" required minlength="8"></label>
            <button class="button button-full" type="submit">Daftar sekarang</button>
        </form>
        <p class="auth-switch">Sudah punya akun? <a href="{{ route('login') }}">Masuk</a></p>
    </div>
</section>
@endsection