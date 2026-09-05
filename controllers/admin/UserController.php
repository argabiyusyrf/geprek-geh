<?php
namespace Admin;
class UserController {
    public function index() {
        \Auth::requireAdmin();
        $db = \Database::getInstance();
        $users = $db->fetchAll("SELECT * FROM users ORDER BY created_at DESC");

        require __DIR__ . '/../../views/layouts/admin-header.php';
        require __DIR__ . '/../../views/admin/users/index.php';
        require __DIR__ . '/../../views/layouts/admin-footer.php';
    }
}
