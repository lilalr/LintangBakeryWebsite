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
