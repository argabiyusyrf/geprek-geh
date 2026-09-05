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
├── .htaccess          # Rewrite Apache (base path /geprek-geh)
├── config/            # app.php (pengaturan), database.php (kredensial MySQL)
├── core/              # Router, Database, Auth, Totp, helpers
├── controllers/       # Kontroller (plain class); admin/ ber-namespace Admin\
├── views/             # Tampilan (layout + per halaman)
├── database/schema.sql
└── public/            # CSS, JS, font
```

## Setup

1. Siapkan MySQL, isi kredensial di `config/database.php`.
2. Jalankan installer (membuat schema + seed data):

   ```bash
   php install.php
   ```

   `install.php` bersifat idempotent — jalankan ulang kapan pun untuk reset data seed (hanya menghapus `cart/order_items/orders/products/categories`, akun pengguna tetap).

3. Jalankan server:

   ```bash
   # Opsi A — PHP built-in server (dari root proyek)
   php -S localhost:8080 router.php

   # Opsi B — Apache (docroot /var/www/html), buka:
   #         http://localhost/geprek-geh/
   ```

## Akun seed

| Role     | Email                 | Password     |
| -------- | --------------------- | ------------ |
| Admin    | admin@geprekgeh.com   | admin123     |
| Customer | budi@email.com        | customer123  |

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

- Base path `/geprek-geh` di-hardcode di `.htaccess`, `router.php`, `index.php`, dan setiap link/redirect — jangan digeser tanpa mengubah semuanya.
- `auto-push.sh` (cron tiap menit) otomatis commit + push perubahan ke `origin/main` (`auto: <timestamp>`).
- `logs/` dan `assets/uploads/` (bukti pembayaran) di-gitignore.

## Menghubungi

WhatsApp: 0887-4267-4141 · Buka Setiap hari 09.00–21.00 WIB