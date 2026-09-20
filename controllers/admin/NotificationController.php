<?php
namespace Admin;
class NotificationController {

    public function index() {
        \Auth::requireStaff();
        $db = \Database::getInstance();

        $customers = $db->fetchAll(
            "SELECT id, name, email FROM users WHERE role = 'customer' AND is_blocked = 0 ORDER BY name ASC"
        );

        $recent = $db->fetchAll(
            "SELECT n.*, u.name AS user_name FROM notifications n
             JOIN users u ON u.id = n.user_id
             WHERE n.type = 'admin'
             ORDER BY n.created_at DESC LIMIT 30"
        );

        $admin_page_title = 'Notifikasi ke Pelanggan';
        render('admin/notifications/index', get_defined_vars());
    }

    public function send() {
        \Auth::requireStaff();
        if (!\verify_csrf()) {
            \flash_set('error', 'Token tidak valid.');
            header('Location: /geprek-geh/admin/notifications');
            exit;
        }
        $db = \Database::getInstance();

        $target = $_POST['target'] ?? 'all';
        $user_id = (int) ($_POST['user_id'] ?? 0);
        $title = trim($_POST['title'] ?? '');
        $message = trim($_POST['message'] ?? '');
        $link = trim($_POST['link'] ?? '');

        if ($title === '') {
            \flash_set('error', 'Judul notifikasi wajib diisi.');
            header('Location: /geprek-geh/admin/notifications');
            exit;
        }
        if (mb_strlen($title) > 150) $title = mb_substr($title, 0, 150);
        if (mb_strlen($message) > 255) $message = mb_substr($message, 0, 255);
        if (mb_strlen($link) > 255) $link = mb_substr($link, 0, 255);
        if ($link !== '' && !str_starts_with($link, '/')) $link = '/' . $link;

        if ($target === 'all') {
            $users = $db->fetchAll("SELECT id FROM users WHERE role = 'customer'");
            if (!$users) {
                \flash_set('error', 'Belum ada pelanggan terdaftar.');
                header('Location: /geprek-geh/admin/notifications');
                exit;
            }
            foreach ($users as $u) {
                $db->insert('notifications', [
                    'user_id' => $u['id'],
                    'type'    => 'admin',
                    'title'   => $title,
                    'message' => $message !== '' ? $message : null,
                    'link'    => $link !== '' ? $link : null,
                ]);
            }
            \flash_set('success', 'Notifikasi dikirim ke ' . count($users) . ' pelanggan.');
        } else {
            if ($user_id < 1) {
                \flash_set('error', 'Pilih pelanggan tujuan.');
                header('Location: /geprek-geh/admin/notifications');
                exit;
            }
            $user = $db->fetchOne("SELECT id FROM users WHERE id = ? AND role = 'customer'", [$user_id]);
            if (!$user) {
                \flash_set('error', 'Pelanggan tidak ditemukan.');
                header('Location: /geprek-geh/admin/notifications');
                exit;
            }
            $db->insert('notifications', [
                'user_id' => $user_id,
                'type'    => 'admin',
                'title'   => $title,
                'message' => $message !== '' ? $message : null,
                'link'    => $link !== '' ? $link : null,
            ]);
            \flash_set('success', 'Notifikasi terkirim ke pelanggan.');
        }

        header('Location: /geprek-geh/admin/notifications');
        exit;
    }
}