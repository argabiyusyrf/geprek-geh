<?php

class NotificationController {

    // ─────────── static helpers (header + hooks) ───────────

    public static function push($userId, $type, $title, $message = null, $link = null) {
        $db = Database::getInstance();
        return $db->insert('notifications', [
            'user_id' => $userId,
            'type'    => $type,
            'title'   => $title,
            'message' => $message,
            'link'    => $link,
        ]);
    }

    public static function pushToAdmins($type, $title, $message = null, $link = null) {
        $db = Database::getInstance();
        $admins = $db->fetchAll("SELECT id FROM users WHERE role = 'admin'");
        foreach ($admins as $admin) {
            self::push($admin['id'], $type, $title, $message, $link);
        }
    }

    public static function unreadCount() {
        if (!Auth::check()) return 0;
        return (int) Database::getInstance()->fetchColumn(
            "SELECT COUNT(*) FROM notifications WHERE user_id = ? AND is_read = 0",
            [Auth::id()]
        );
    }

    public static function fetchAll($limit = 8) {
        if (!Auth::check()) return [];
        return Database::getInstance()->fetchAll(
            "SELECT * FROM notifications WHERE user_id = ? ORDER BY is_read ASC, created_at DESC LIMIT " . (int)$limit,
            [Auth::id()]
        );
    }

    // ─────────── route actions ───────────

    public function readAll() {
        Auth::requireLogin();
        if (!verify_csrf()) {
            flash_set('error', 'Token tidak valid.');
            redirect('/geprek-geh/');
        }
        Database::getInstance()->update('notifications', ['is_read' => 1], 'user_id = ?', [Auth::id()]);
        $back = $_SERVER['HTTP_REFERER'] ?? '/geprek-geh/';
        header('Location: ' . $back);
        exit;
    }

    public function read($id) {
        Auth::requireLogin();
        if (!verify_csrf()) {
            http_response_code(403);
            exit;
        }
        Database::getInstance()->update(
            'notifications',
            ['is_read' => 1],
            'id = ? AND user_id = ?',
            [(int)$id, Auth::id()]
        );
        exit;
    }
}