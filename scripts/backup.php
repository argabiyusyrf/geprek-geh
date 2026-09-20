<?php
/**
 * Backup database Geprek Geh ke logs/backups/ (misal + cron harian):
 *   0 3 * * * cd /var/www/html && php scripts/backup.php >> logs/backup.log 2>&1
 */

if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit('Hanya bisa dijalankan dari CLI');
}

$config = require __DIR__ . '/../config/database.php';

$dir = __DIR__ . '/../logs/backups';
if (!is_dir($dir)) {
    @mkdir($dir, 0775, true);
}
@chmod($dir, 0777);

$stamp = date('Ymd-His');
$file = $dir . '-' . $stamp . '.sql.gz';

$cmd = sprintf(
    'mysqldump --no-tablespaces --single-transaction --quick -h %s -P %d -u %s %s %s 2>&1 | gzip > %s',
    escapeshellarg($config['host']),
    3306,
    escapeshellarg($config['username']),
    $config['password'] !== '' ? '-p' . escapeshellarg($config['password']) : '',
    escapeshellarg($config['dbname']),
    escapeshellarg($file)
);

exec($cmd, $out, $code);

if ($code !== 0 || !is_file($file) || filesize($file) < 100) {
    @unlink($file);
    fwrite(STDERR, '[backup] Gagal: ' . implode("\n", $out) . "\n");
    exit(1);
}

// Hapus backup lama (tetap 7 file terakhir)
$keep = 7;
$files = glob($dir . '-*.sql.gz');
if (is_array($files) && count($files) > $keep) {
    usort($files, fn($a, $b) => filemtime($a) <=> filemtime($b));
    foreach (array_slice($files, 0, count($files) - $keep) as $old) {
        @unlink($old);
    }
}

echo '[backup] OK: ' . realpath($file) . ' (' . round(filesize($file) / 1024, 1) . " KB)\n";