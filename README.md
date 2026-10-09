# Lintang Bakery

Aplikasi toko Lintang Bakery menggunakan Laravel 13, PHP 8.3, Blade, dan SQLite. Area user dan admin memiliki route, layout, controller, serta view yang terpisah.

## Menjalankan aplikasi

Pastikan PHP 8.3+, Composer, Node.js, dan npm tersedia.

```powershell
if (-not (Test-Path .env)) { Copy-Item .env.example .env }
composer install
if (-not (Test-Path 'database\database.sqlite')) { New-Item -ItemType File 'database\database.sqlite' | Out-Null }
php artisan key:generate
npm install
php artisan migrate --seed
php artisan storage:link
npm run build
php artisan serve
```

Buka `http://127.0.0.1:8000`.

## Akun demo

- Admin: `admin@lintangbakery.id` / `admin123`
- User: `buyer@test.com` / `password`

Gunakan akun demo hanya untuk pengembangan lokal. Ganti password dan konfigurasi aplikasi sebelum deployment.

## Area aplikasi

- User: `/`, `/produk`, `/keranjang`, `/checkout`, `/akun`
- Admin: `/admin`, `/admin/produk`, `/admin/stok`, `/admin/pesanan`, `/admin/transaksi-offline`, `/admin/laporan`
- Pengamanan role admin/user dilakukan di server melalui middleware; pendaftaran akun publik hanya membuat role user.
- `app/Models`, `app/Http/Controllers`, dan `database/migrations` berisi logika serta skema data.
- `resources/views/store` berisi tampilan user; `resources/views/admin` berisi tampilan admin; `resources/views/layouts` berisi layout keduanya.
- Sumber prototype React lama disimpan di `legacy-react/` sebagai referensi dan tidak digunakan oleh Laravel.

Database lokal menggunakan SQLite (`database/database.sqlite`). Setelah perubahan skema, jalankan `php artisan migrate`.
