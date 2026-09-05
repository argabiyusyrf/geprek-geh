<?php
$env = [];
$envFile = __DIR__ . '/../.env';
if (is_file($envFile)) {
    foreach (file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#') || !str_contains($line, '=')) continue;
        [$k, $v] = explode('=', $line, 2);
        $env[trim($k)] = trim($v);
    }
}

return [
    'host'     => getenv('GG_DB_HOST') ?: ($env['GG_DB_HOST'] ?? 'localhost'),
    'dbname'   => getenv('GG_DB_NAME') ?: ($env['GG_DB_NAME'] ?? 'geprek_geh'),
    'username' => getenv('GG_DB_USER') ?: ($env['GG_DB_USER'] ?? 'root'),
    'password' => getenv('GG_DB_PASS') ?: ($env['GG_DB_PASS'] ?? ''),
    'charset'  => getenv('GG_DB_CHARSET') ?: ($env['GG_DB_CHARSET'] ?? 'utf8mb4'),
];