<?php
$val = fn($k) => e($settings[$k] ?? '');
?>

<div class="page-header page-header--wrap">
    <div>
        <h1>Pengaturan Toko</h1>
        <p class="page-sub">Ongkir, pajak, kontak, dan rekening pembayaran dipakai di seluruh website (keranjang, checkout, halaman produk).</p>
    </div>
</div>

<form method="POST" action="/geprek-geh/admin/settings" class="settings-form">
    <?= csrf_field() ?>

    <div class="card order-card settings-section">
        <header class="order-card-head">
            <h3>Ongkir &amp; Pajak</h3>
        </header>
        <div class="form-row">
            <div class="form-group">
                <label>Ongkos Kirim (Rp)</label>
                <input type="number" name="shipping" class="input" min="0" step="500" value="<?= $val('shipping') ?>" required>
                <span class="field-hint">Biaya pengiriman per pesanan.</span>
            </div>
            <div class="form-group">
                <label>Tarif Pajak (%)</label>
                <input type="number" name="tax_rate" class="input" min="0" max="100" step="0.01" value="<?= e(rtrim(rtrim((string) (float) ($settings['tax_rate'] ?? 0.11), '0'), '.')) ?>">
                <span class="field-hint">Contoh: 0.11 = 11%.</span>
            </div>
            <div class="form-group">
                <label>Ambang Stok Menipis</label>
                <input type="number" name="stock_low_threshold" class="input" min="0" value="<?= $val('stock_low_threshold') ?>">
                <span class="field-hint">Produk dengan stok &#8804; ambang ini ditandai "stok menipis".</span>
            </div>
        </div>
    </div>

    <div class="card order-card settings-section">
        <header class="order-card-head">
            <h3>Kontak Toko</h3>
        </header>
        <div class="form-row">
            <div class="form-group">
                <label>Nomor WhatsApp</label>
                <input type="text" name="contacts_whatsapp" class="input" value="<?= $val('contacts_whatsapp') ?>" placeholder="081234567890">
                <span class="field-hint">Digunakan untuk tombol chat di website.</span>
            </div>
            <div class="form-group">
                <label>Jam Operasional</label>
                <input type="text" name="contacts_hours" class="input" value="<?= $val('contacts_hours') ?>" placeholder="Setiap hari 09.00–21.00 WIB">
            </div>
        </div>
    </div>

    <div class="card order-card settings-section">
        <header class="order-card-head">
            <h3>Metode Pembayaran</h3>
        </header>
        <div class="settings-pay-info">
            <p>Rekening bank dan e-wallet dikelola dari halaman <a href="/geprek-geh/admin/payments"><strong>Metode Pembayaran</strong></a>. Tambahkan, ubah, atau nonaktifkan metode di sana — perubahan langsung berlaku di halaman checkout.</p>
        </div>
    </div>

    <div class="card order-card settings-section">
        <header class="order-card-head">
            <h3>Gateway QRIS</h3>
        </header>
        <div class="form-row">
            <div class="form-group">
                <label>Status Gateway</label>
                <select name="payment_gateway_type" class="input">
                    <option value="none" <?= ($settings['payment_gateway_type'] ?? '') === 'none' ? 'selected' : '' ?>>Nonaktif</option>
                    <option value="qris" <?= ($settings['payment_gateway_type'] ?? '') === 'qris' ? 'selected' : '' ?>>Aktif (QRIS)</option>
                </select>
                <span class="field-hint">Saat aktif, "QRIS" muncul sebagai opsi pembayaran di checkout.</span>
            </div>
            <div class="form-group">
                <label>Label Pembayaran</label>
                <input type="text" name="payment_gateway_label" class="input" value="<?= $val('payment_gateway_label') ?>" placeholder="QRIS">
                <span class="field-hint">Contoh: QRIS, DANA, GoPay.</span>
            </div>
            <div class="form-group">
                <label>Nomor / ID (opsional)</label>
                <input type="text" name="payment_gateway_number" class="input" value="<?= $val('payment_gateway_number') ?>" placeholder="nomor tujuan">
                <span class="field-hint">Ditampilkan sebagai tujuan bayar; kosongkan jika hanya scan kode.</span>
            </div>
        </div>
    </div>

    <div class="card order-card settings-section">
        <header class="order-card-head">
            <h3>Auto-batalkan Pesanan</h3>
        </header>
        <div class="form-row">
            <div class="form-group">
                <label>Batas Waktu (jam)</label>
                <input type="number" name="auto_cancel_hours" class="input" min="1" value="<?= $val('auto_cancel_hours') ?>">
                <span class="field-hint">Pesanan dengan status "Menunggu" dan belum dibayar dalam X jam dibatalkan otomatis (jalan via cron: php scripts/auto-cancel.php).</span>
            </div>
            <div class="form-group">
                <label>Fitur Auto-cancel</label>
                <label class="switch">
                    <input type="checkbox" name="auto_cancel_enabled" value="1" class="switch-input" <?= ($settings['auto_cancel_enabled'] ?? '1') === '1' ? 'checked' : '' ?>>
                    <span class="switch-ui" aria-hidden="true"><span class="switch-knob"></span></span>
                    <span class="switch-meta"><strong>Aktifkan pembatalan otomatis</strong><small>Jalankan via cron dengan php scripts/auto-cancel.php</small></span>
                </label>
            </div>
        </div>
    </div>

    <div class="card order-card settings-section">
        <header class="order-card-head">
            <h3>Templat WhatsApp</h3>
            <span class="badge badge-secondary">Placeholder: {nama} &amp; {invoice}</span>
        </header>
        <div class="settings-note">
            <p>Pesan disematkan ke tombol WhatsApp sehingga pelanggan/penjual hanya menekan kirim. Placeholder <code>{nama}</code> dan <code>{invoice}</code> diganti otomatis sesuai pesanan.</p>
        </div>
        <div class="form-row">
            <div class="form-group">
                <label>Chat Bantuan (halaman pesanan pelanggan)</label>
                <textarea name="wa_template_help" class="input" rows="2" placeholder="Halo Geprek Geh, saya butuh bantuan soal pesanan {invoice}."><?= $val('wa_template_help') ?></textarea>
            </div>
            <div class="form-group">
                <label>Konfirmasi Pembayaran Diterima</label>
                <textarea name="wa_template_paid" class="input" rows="2" placeholder="Halo {nama}, pembayaran pesanan {invoice} sudah kami terima. Terima kasih!"><?= $val('wa_template_paid') ?></textarea>
            </div>
            <div class="form-group">
                <label>Pesanan Dikirim</label>
                <textarea name="wa_template_shipped" class="input" rows="2" placeholder="Halo {nama}, pesanan {invoice} sedang dalam perjalanan. Mohon ditunggu ya!"><?= $val('wa_template_shipped') ?></textarea>
            </div>
            <div class="form-group">
                <label>Pesanan Dibatalkan</label>
                <textarea name="wa_template_cancelled" class="input" rows="2" placeholder="Halo {nama}, mohon maaf pesanan {invoice} terpaksa dibatalkan. Hubungi kami untuk pertanyaan lebih lanjut."><?= $val('wa_template_cancelled') ?></textarea>
            </div>
        </div>
    </div>

    <div class="settings-actions">
        <button type="submit" class="btn btn-primary">Simpan Pengaturan</button>
    </div>
</form>