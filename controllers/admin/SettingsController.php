<?php
namespace Admin;
class SettingsController {

    public function index() {
        \Auth::requireAdmin();
        $db = \Database::getInstance();
        $rows = $db->fetchAll("SELECT skey, svalue FROM toko_settings");
        $settings = [];
        foreach ($rows as $r) { $settings[$r['skey']] = $r['svalue']; }

        $admin_page_title = 'Pengaturan Toko';
        require __DIR__ . '/../../views/layouts/admin-header.php';
        require __DIR__ . '/../../views/admin/settings/index.php';
        require __DIR__ . '/../../views/layouts/admin-footer.php';
    }

    public function save() {
        \Auth::requireAdmin();
        if (!\verify_csrf()) {
            \flash_set('error', 'Token tidak valid.');
            header('Location: /geprek-geh/admin/settings');
            exit;
        }

        $db = \Database::getInstance();

        $fields = [
            'shipping'             => 'int',
            'tax_rate'             => 'float',
            'stock_low_threshold'  => 'int',
            'contacts_whatsapp'    => 'str',
            'contacts_hours'       => 'str',
            'bank_name'            => 'str',
            'bank_number'          => 'str',
            'bank_holder'          => 'str',
            'ewallet_name'         => 'str',
            'ewallet_number'       => 'str',
            'ewallet_holder'       => 'str',
        ];

        foreach ($fields as $key => $type) {
            $val = trim($_POST[$key] ?? '');
            if ($type === 'int') {
                $val = (int) $val;
                if ($val < 0) $val = 0;
            } elseif ($type === 'float') {
                $val = (float) $val;
                if ($val < 0) $val = 0;
            } else {
                if (mb_strlen($val) > 255) $val = mb_substr($val, 0, 255);
            }
            $db->query(
                "INSERT INTO toko_settings (skey, svalue) VALUES (?, ?) ON DUPLICATE KEY UPDATE svalue = VALUES(svalue)",
                [$key, (string) $val]
            );
        }

        \flash_set('success', 'Pengaturan toko berhasil disimpan.');
        header('Location: /geprek-geh/admin/settings');
        exit;
    }
}
