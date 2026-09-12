# Todos & Plan

Catatan rencana fitur Geprek Geh. Item dicentang bila sudah selesai.

## Kata kunci (recovery tanpa email) — selesai
- [x] Kolom `users.recovery_keyword` (hash bcrypt) + migration + schema.sql
- [x] Lupa password: pilihan metode radio (link email / kata kunci)
- [x] Verifikasi kata kunci → halaman pemulihan (login langsung / ubah password)
- [x] Login langsung hormati 2FA; konfirmasi password sama seperti reset
- [x] Wizard setup pasca-register (kata kunci wajib + alamat opsional), bisa dilewati
- [x] Form atur/ganti kata kunci di Akun → Keamanan
- [x] Rate limit `keyword:<email>` 5/300 + `keyword-ip:<IP>` 10/600, respon anti-enumeration

## Pengiriman email — PENDING (belum diputuskan)
Masalah: driver `php` (`mail()`) sering tidak sampai di environment tanpa MTA/SES. Tidak ada Composer di proyek ini.

Opsi yang dimau pemilik: **HTTP API** (Brevo / Resend / Mailgun) via `curl`, **tanpa Composer**; dan/atau **SMTP** via PHPMailer yang di-vendor manual (library `PHPMailer/PHPMailer` sudah direferensikan `core/Mail.php`).

Catatan:
- Tambahkan `GEPREK_MAIL_*` ke `.env.example`: driver (`api`/`smtp`), host/port/key/from.
- Butuh DNS SPF + DKIM di domain pengirim.
- Saat terdengar notif email, cek `logs/mail.log`.

## Backlog kecil
- Konfirmasi ID `twofa-form` / `twofa-code` di `views/auth/twofactor.php` saat QA 2FA.
- Dokumentasi ulang `database/schema.sql` agar tidak kedaluwarsa dari live DB (lihat AGENTS.md Gotchas).