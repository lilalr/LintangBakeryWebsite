@extends('layouts.store')

@section('title', 'Akun Saya · Lintang Bakery')

@section('content')
<section class="page-heading">
    <span class="eyebrow">AKUN SAYA</span>
    <h1>Profil Pengguna</h1>
    <p>Kelola informasi profil akun Anda.</p>
</section>

<div class="dashboard-container" style="max-width: 700px; margin: 0 auto;">
    {{-- TAB INFORMASI PROFIL --}}
    <section class="dashboard-section">
        <form method="POST" action="{{ route('buyer.update') }}" class="form-card stack-form">
            @csrf 
            @method('PUT')
            
            <label>Nama lengkap
                <input type="text" name="name" value="{{ old('name', auth()->user()->name) }}" required>
            </label>

            <label>Email (tidak dapat diubah)
                <input type="email" value="{{ auth()->user()->email }}" disabled>
            </label>

            <label>No. telepon
                <input type="text" name="phone" value="{{ old('phone', auth()->user()->phone) }}" required>
            </label>

            <label>Alamat lengkap
                <textarea name="address" rows="3" required>{{ old('address', auth()->user()->address) }}</textarea>
            </label>

            @include('store.partials.region-select', [
                'province' => old('province', auth()->user()->province),
                'city' => old('city', auth()->user()->city),
            ])

            <button type="submit" class="button">Simpan Perubahan</button>
        </form>
    </section>
</div>
@endsection