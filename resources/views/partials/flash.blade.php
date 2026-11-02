@if (request()->routeIs('store.*', 'cart.*', 'checkout.*', 'login', 'register', 'buyer.*'))
    @if (session('success') || session('error') || $errors->any())
        <div class="toast-container" data-toast-container>
            @if (session('success'))
                <div class="toast" data-toast role="status">{{ session('success') }}<button class="toast-close" type="button" aria-label="Tutup notifikasi">×</button></div>
            @endif
            @if (session('error'))
                <div class="toast" data-toast role="alert">{{ session('error') }}<button class="toast-close" type="button" aria-label="Tutup notifikasi">×</button></div>
            @endif
            @if ($errors->any())
                <div class="toast" data-toast role="alert">
                    <div><strong>Periksa kembali isian berikut:</strong><ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
                    <button class="toast-close" type="button" aria-label="Tutup notifikasi">×</button>
                </div>
            @endif
        </div>
    @endif
@else
    @if (session('success') && !request()->routeIs('admin.transactions.*'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div class="alert alert-error">{{ session('error') }}</div>
    @endif
    @if ($errors->any())
        <div class="alert alert-error">
            <strong>Periksa kembali isian berikut:</strong>
            <ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
        </div>
    @endif
@endif
