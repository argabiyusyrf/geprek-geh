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
            <h3>Pembayaran — Transfer Bank</h3>
        </header>
        <div class="form-row">
            <div class="form-group">
                <label>Nama Bank</label>
                <input type="text" name="bank_name" class="input" value="<?= $val('bank_name') ?>" placeholder="BNI">
            </div>
            <div class="form-group">
                <label>Nomor Rekening</label>
                <input type="text" name="bank_number" class="input" value="<?= $val('bank_number') ?>" placeholder="1846757370">
            </div>
            <div class="form-group">
                <label>Atas Nama</label>
                <input type="text" name="bank_holder" class="input" value="<?= $val('bank_holder') ?>" placeholder="GEPREK GEH">
            </div>
        </div>
    </div>

    <div class="card order-card settings-section">
        <header class="order-card-head">
            <h3>Pembayaran — E-Wallet</h3>
        </header>
        <div class="form-row">
            <div class="form-group">
                <label>Nama E-Wallet</label>
                <input type="text" name="ewallet_name" class="input" value="<?= $val('ewallet_name') ?>" placeholder="ShopeePay">
            </div>
            <div class="form-group">
                <label>Nomor</label>
                <input type="text" name="ewallet_number" class="input" value="<?= $val('ewallet_number') ?>" placeholder="083137274613">
            </div>
            <div class="form-group">
                <label>Atas Nama</label>
                <input type="text" name="ewallet_holder" class="input" value="<?= $val('ewallet_holder') ?>" placeholder="GEPREK GEH">
            </div>
        </div>
    </div>

    <div class="settings-actions">
        <button type="submit" class="btn btn-primary">Simpan Pengaturan</button>
    </div>
</form>