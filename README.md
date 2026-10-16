# Lintang Bakery

Aplikasi toko Lintang Bakery menggunakan Laravel 13, PHP 8.3, Blade, dan SQLite. Area user dan admin memiliki route, layout, controller, serta view yang terpisah.

## Cara menjalankan aplikasi

Pastikan PHP 8.3+, Composer, Node.js, dan npm sudah terpasang. Buka folder proyek ini di VS Code, lalu buka terminal baru. Perintah di bawah menggunakan PowerShell.

### Pertama kali menjalankan proyek

Jalankan perintah berikut satu per satu dari folder utama proyek:

```powershell
Set-Location .\backend
if (-not (Test-Path .env)) { Copy-Item .env.example .env }
composer install
if (-not (Test-Path 'database\database.sqlite')) { New-Item -ItemType File 'database\database.sqlite' | Out-Null }
php artisan key:generate
npm --prefix ..\frontend install
php artisan migrate --seed
if (-not (Test-Path 'public\storage')) { php artisan storage:link }
npm --prefix ..\frontend run build
```

### Menjalankan aplikasi berikutnya

Setiap kali ingin menggunakan aplikasi, buka terminal di VS Code dan jalankan:

```powershell
Set-Location .\backend
php artisan serve
```

Biarkan terminal tersebut tetap terbuka, lalu buka `http://127.0.0.1:8000` di browser.

### Jika sedang mengubah CSS atau JavaScript

Build dari langkah pertama sudah cukup untuk menjalankan aplikasi. Jika sedang mengedit CSS atau JavaScript dan ingin melihat perubahan secara langsung, buka **terminal kedua** di VS Code, lalu jalankan:

```powershell
Set-Location .\frontend
npm run dev
```

Biarkan kedua terminal tetap terbuka saat bekerja. Hentikan server dengan menekan `Ctrl+C` di masing-masing terminal.

> `php artisan key:generate` dan `php artisan migrate --seed` cukup dijalankan saat menyiapkan proyek pertama kali. Jangan membuat `APP_KEY` baru setiap kali menjalankan aplikasi.

## Akun demo

- Admin: `admin@lintangbakery.id` / `admin123`
- User: `buyer@test.com` / `password`

Gunakan akun demo hanya untuk pengembangan lokal. Ganti password dan konfigurasi aplikasi sebelum deployment.

## Area aplikasi

- User: `/`, `/produk`, `/keranjang`, `/checkout`, `/akun`
- Admin: `/admin`, `/admin/produk`, `/admin/stok`, `/admin/pesanan`, `/admin/transaksi-offline`, `/admin/laporan`
- Pengamanan role admin/user dilakukan di server melalui middleware; pendaftaran akun publik hanya membuat role user.
- `backend/app/Models`, `backend/app/Http/Controllers`, dan `backend/database/migrations` berisi logika serta skema data.
- `frontend/resources/views/store` berisi tampilan user; `frontend/resources/views/admin` berisi tampilan admin; `frontend/resources/views/layouts` berisi layout keduanya.
- `frontend/resources/css` dan `frontend/resources/js` berisi aset aplikasi Laravel.
- Sumber prototype React lama disimpan di `frontend/legacy-react/` sebagai referensi dan tidak digunakan oleh Laravel.

Database lokal menggunakan SQLite (`backend/database/database.sqlite`). Jalankan perintah Artisan dari folder `backend`; setelah perubahan skema, jalankan `php artisan migrate`.
