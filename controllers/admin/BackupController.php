<?php
namespace Admin;
class BackupController {

    private function dir(): string {
        return __DIR__ . '/../../logs/backups';
    }

    private function files(): array {
        $dir = $this->dir();
        if (!is_dir($dir)) return [];
        $files = glob($dir . '/geprek-geh-*.sql.gz');
        if (!$files) return [];
        usort($files, fn($a, $b) => filemtime($b) <=> filemtime($a));
        $out = [];
        foreach ($files as $f) {
            $out[] = [
                'name' => basename($f),
                'path' => realpath($f),
                'size' => filemtime($f) ? round(filesize($f) / 1024, 1) : 0,
                'time' => filemtime($f),
            ];
        }
        return $out;
    }

    public function index() {
        \Auth::requireAdmin();
        $backups = $this->files();
        $admin_page_title = 'Backup Database';
        require __DIR__ . '/../../views/layouts/admin-header.php';
        require __DIR__ . '/../../views/admin/backup/index.php';
        require __DIR__ . '/../../views/layouts/admin-footer.php';
    }

    public function run() {
        \Auth::requireAdmin();
        if (!\verify_csrf()) {
            \flash_set('error', 'Token tidak valid.');
            header('Location: /geprek-geh/admin/backup');
            exit;
        }
        $config = require __DIR__ . '/../../config/database.php';
        $dir = $this->dir();
        if (!is_dir($dir)) @mkdir($dir, 0775, true);
        $file = $dir . '/geprek-geh-' . date('Ymd-His') . '.sql.gz';
        $cmd = sprintf(
            'mysqldump --no-tablespaces --single-transaction --quick -h %s -u %s %s %s 2>&1 | gzip > %s',
            escapeshellarg($config['host']),
            escapeshellarg($config['username']),
            $config['password'] !== '' ? '-p' . escapeshellarg($config['password']) : '',
            escapeshellarg($config['dbname']),
            escapeshellarg($file)
        );
        exec($cmd, $out, $code);
        if ($code !== 0 || !is_file($file)) {
            @unlink($file);
            \flash_set('error', 'Backup gagal dibuat.');
            header('Location: /geprek-geh/admin/backup');
            exit;
        }
        \flash_set('success', 'Backup berhasil dibuat: ' . basename($file));
        header('Location: /geprek-geh/admin/backup');
        exit;
    }

    public function download($name) {
        \Auth::requireAdmin();
        $name = basename((string) $name);
        if (!preg_match('/^geprek-geh-[0-9]{8}-[0-9]{6}\.sql\.gz$/', $name)) {
            http_response_code(400);
            exit('Nama backup tidak valid.');
        }
        $file = $this->dir() . '/' . $name;
        if (!is_file($file)) {
            http_response_code(404);
            exit('Backup tidak ditemukan.');
        }
        header('Content-Type: application/gzip');
        header('Content-Disposition: attachment; filename="' . $name . '"');
        header('Content-Length: ' . filesize($file));
        readfile($file);
        exit;
    }

    public function delete($name) {
        \Auth::requireAdmin();
        if (!\verify_csrf()) {
            \flash_set('error', 'Token tidak valid.');
            header('Location: /geprek-geh/admin/backup');
            exit;
        }
        $name = basename((string) $name);
        if (!preg_match('/^geprek-geh-[0-9]{8}-[0-9]{6}\.sql\.gz$/', $name)) {
            \flash_set('error', 'Nama backup tidak valid.');
            header('Location: /geprek-geh/admin/backup');
            exit;
        }
        $file = $this->dir() . '/' . $name;
        if (is_file($file)) @unlink($file);
        \flash_set('success', 'Backup ' . $name . ' dihapus.');
        header('Location: /geprek-geh/admin/backup');
        exit;
    }
}