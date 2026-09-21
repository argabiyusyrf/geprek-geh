# Geprek Geh 🍗

Toko online ayam geprek berbasis **PHP 8.4 MVC** murni (tanpa Composer, tanpa framework). Sudah termasuk autentikasi + 2FA (TOTP), keranjang, checkout, katalog produk, dashboard admin, dan notifikasi.

> ⚠️ Aplikasi ini butuh **PHP + MySQL** — tidak bisa dijalankan di GitHub Pages/static hosting.

## Teknologi

- PHP 8.4 (vanilla MVC sendiri, `spl_autoload`, router custom)
- MySQL (PDO, prepared statements)
- HTML/CSS/JS vanilla, font Fraunces + Plus Jakarta Sans, efek Lenis smooth scroll
- TOTP 2FA (library sendiri di `core/Totp.php`)

## Struktur

```
├── index.php          # Entrypoint tunggal + registrasi semua route
├── install.php        # Installer & seeder database (idempotent)
├── router.php         # Router untuk php -S (meniru .htaccess)
├── .htaccess          # Rewrite Apache (base path )
├── config/            # app.php (pengaturan), database.php (kredensial MySQL)
├── core/              # Router, Database, Auth, Totp, helpers
├── controllers/       # Kontroller (plain class); admin/ ber-namespace Admin\
├── views/             # Tampilan (layout + per halaman)
├── database/schema.sql
└── public/            # CSS, JS, font
```

## Setup

1. Salin `.env.example` → `.env`, isi kredensial MySQL (`GG_DB_HOST`, `GG_DB_NAME`, `GG_DB_USER`, `GG_DB_PASS`). File `.env` di-gitignore — jangan ikut tercommit.
2. Jalankan installer — ada 2 mode:

   ```bash
   php install.php          # dev: schema + seed penuh (admin + customer + kategori + produk + order)
   php install.php --empty  # production: DB benar-benar kosong, HANYA 1 akun admin
   ```

   `install.php` bersifat idempotent — jalankan ulang kapan pun untuk reset data. Nama/email/password admin dari env `GEPREK_ADMIN_NAME` / `GEPREK_ADMIN_EMAIL` / `GEPREK_ADMIN_PASS` (fallback `Admin Geprek Geh` / `admin@geprekgeh.com` / `AdminGeprek123`).

3. Jalankan server:

   ```bash
   # Opsi A — PHP built-in server (dari root proyek)
   php -S localhost:8080 router.php

   # Opsi B — Apache (docroot /var/www/html), buka:
   #         http://localhost/
   ```

## Deployment production (InfinityFree)

1. Deploy semua file ke webroot via FTP (`.htaccess` aktif sebagai garda keamanan). Ada skrip deploy satu-tombol: `cp scripts/.deploy.env.example scripts/.deploy.env` → isi kredensial FTP+DB → `bash scripts/deploy.sh` (build paket bersih + generate `.env` production + upload otomatis).
2. Upload `.env` berisi `GG_DB_HOST=sql102.infinityfree.com`, `GG_DB_USER=if0_...`, `GG_DB_PASS=...`, `GG_DB_NAME=if0_..._geprekgeh` (lihat bagian "PRODUCTION" di `.env.example`).
3. Seed DB — salah satu:
   - Jika hosting menyediakan CLI/terminal: `php install.php --empty`
   - Tanpa CLI: import `database/production.sql` (schema + hanya admin) via phpMyAdmin di hPanel. **Jangan import `schema.sql`** — baris `CREATE DATABASE/USE` menunjuk nama DB lokal.
4. Result: DB production benar-benar kosong kecuali 1 akun admin.

## Akun seed

Login admin dibuat oleh `php install.php` — nama/email/password dari env `GEPREK_ADMIN_NAME` / `GEPREK_ADMIN_EMAIL` / `GEPREK_ADMIN_PASS` (fallback tercetak di terminal). Mode penuh juga membuat customer seed `argaabiyyu@email.com`. Setelah login pertama, ganti password via menu akun.

## Fitur

- **Katalog**: produk per kategori, pencarian, halaman detail, stok & produk unggulan
- **Keranjang**: tambah/ubah/hapus, merge cart tamu saat login
- **Checkout**: hitung pajak + ongkir, pilih metode pembayaran (bank/ewallet), upload bukti transfer
- **Order**: riwayat pesanan, cek status, batalkan/terima, reorder
- **Autentikasi**: register/login, 2FA TOTP + kode recovery
- **Admin** (`/admin`): dashboard statistik, kelola produk/kategori/order/pengguna, verifikasi pembayaran, ubah status order

## Menambah halaman/route

Semua route didaftarkan di `index.php` via `$router->get()/post()` — pola `{param}` didukung, contoh: `/products/{slug}`. Kontroler admin memakai namespace `Admin\` (`controllers/admin/`). Form POST wajib menyertakan `csrf_field()` dan memanggil `verify_csrf()` di handler.

## Catatan pengembangan

- Base path `/` di-hardcode di `.htaccess`, `router.php`, `config/bootstrap.php`, dan setiap link/redirect — jangan digeser tanpa mengubah semuanya.
- `auto-push.sh` (cron tiap menit) otomatis commit + push perubahan ke `origin/main` (`auto: <timestamp>`).
- `logs/` dan `assets/uploads/` (bukti pembayaran) di-gitignore.

## Menghubungi

Buka Setiap hari 09.00–21.00 WIB