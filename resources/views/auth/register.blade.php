@extends('layouts.store')

@section('title', 'Daftar · Lintang Bakery')
@section('body_class', 'auth-page-register')

@section('content')
<section class="auth-wrap">
    <div class="auth-card">
        <div class="auth-heading">
            <span class="brand-mark">
                <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m2 7 4.41-4.41A2 2 0 0 1 7.83 2h8.34a2 2 0 0 1 1.42.59L22 7"/><path d="M4 12v8a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-8"/><path d="M15 22v-4a2 2 0 0 0-2-2h-2a2 2 0 0 0-2 2v4"/><path d="M2 7h20"/><path d="M22 7v3a2 2 0 0 1-2 2 2.7 2.7 0 0 1-1.59-.63.7.7 0 0 0-.82 0A2.7 2.7 0 0 1 16 12a2.7 2.7 0 0 1-1.59-.63.7.7 0 0 0-.82 0A2.7 2.7 0 0 1 12 12a2.7 2.7 0 0 1-1.59-.63.7.7 0 0 0-.82 0A2.7 2.7 0 0 1 8 12a2.7 2.7 0 0 1-1.59-.63.7.7 0 0 0-.82 0A2.7 2.7 0 0 1 4 12a2 2 0 0 1-2-2V7"/></svg>
            </span>
            <h1>Buat akun reseller</h1>
            <p>Buat akun untuk lanjutkan pemesanan</p>
        </div>
        <form method="post" action="{{ route('register.store') }}" class="stack-form">
            @csrf
            <label>Nama lengkap<input name="name" value="{{ old('name') }}" required maxlength="120"></label>
            <label>Email<input type="email" name="email" value="{{ old('email') }}" required autocomplete="email" autocapitalize="none" spellcheck="false" inputmode="email" oninput="this.value = this.value.toLowerCase()"></label>
            <label>No. telepon / WhatsApp<input type="tel" name="phone" value="{{ old('phone') }}" required maxlength="15" pattern="[0-9]{10,15}" inputmode="numeric" title="Nomor telepon 10-15 digit angka" oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 15)"></label>
            <label>Alamat<textarea name="address" rows="2" required>{{ old('address') }}</textarea></label>
            <label>Password
                <span class="password-field" data-password-field>
                    <input type="password" name="password" required minlength="8" autocomplete="new-password">
                    <button class="password-toggle" type="button" aria-label="Lihat password" aria-pressed="false" onclick="togglePassword(this)">
                        <svg class="icon-eye-closed" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                            <path d="m3 3 18 18M10.6 6.2A9.8 9.8 0 0 1 12 6c6 0 9.5 6 9.5 6a16 16 0 0 1-3.1 3.7M6.2 6.2C3.8 7.8 2.5 12 2.5 12s3.5 6 9.5 6c1 0 1.9-.2 2.7-.5" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                        <svg class="icon-eye-open" viewBox="0 0 24 24" fill="none" aria-hidden="true" hidden>
                            <path d="M2.5 12s3.5-6 9.5-6 9.5 6 9.5 6-3.5 6-9.5 6-9.5-6Z" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/>
                            <circle cx="12" cy="12" r="2.5" stroke="currentColor" stroke-width="1.7"/>
                        </svg>
                    </button>
                </span>
            </label>
            <label>Konfirmasi password
                <span class="password-field" data-password-field>
                    <input type="password" name="password_confirmation" required minlength="8" autocomplete="new-password">
                    <button class="password-toggle" type="button" aria-label="Lihat konfirmasi password" aria-pressed="false" onclick="togglePassword(this)">
                        <svg class="icon-eye-closed" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                            <path d="m3 3 18 18M10.6 6.2A9.8 9.8 0 0 1 12 6c6 0 9.5 6 9.5 6a16 16 0 0 1-3.1 3.7M6.2 6.2C3.8 7.8 2.5 12 2.5 12s3.5 6 9.5 6c1 0 1.9-.2 2.7-.5" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                        <svg class="icon-eye-open" viewBox="0 0 24 24" fill="none" aria-hidden="true" hidden>
                            <path d="M2.5 12s3.5-6 9.5-6 9.5 6 9.5 6-3.5 6-9.5 6-9.5-6Z" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/>
                            <circle cx="12" cy="12" r="2.5" stroke="currentColor" stroke-width="1.7"/>
                        </svg>
                    </button>
                </span>
            </label>
            <button class="button button-full" type="submit">Daftar sekarang</button>
        </form>
        <p class="auth-switch">Sudah punya akun? <a href="{{ route('login') }}">Masuk</a></p>
    </div>
</section>

<script>
function togglePassword(btn) {
    const field = btn.closest('.password-field');
    if (!field) return;
    const input = field.querySelector('input');
    if (!input) return;

    const eyeClosed = field.querySelector('.icon-eye-closed');
    const eyeOpen = field.querySelector('.icon-eye-open');

    if (input.type === 'password') {
        input.type = 'text';
        btn.setAttribute('aria-pressed', 'true');
        btn.setAttribute('aria-label', 'Sembunyikan password');
        if (eyeClosed) eyeClosed.setAttribute('hidden', '');
        if (eyeOpen) eyeOpen.removeAttribute('hidden');
    } else {
        input.type = 'password';
        btn.setAttribute('aria-pressed', 'false');
        btn.setAttribute('aria-label', 'Lihat password');
        if (eyeClosed) eyeClosed.removeAttribute('hidden');
        if (eyeOpen) eyeOpen.setAttribute('hidden', '');
    }
}
</script>
@endsection
