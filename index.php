<?php
// Satu-satunya entrypoint aplikasi.
// Bootstrap (sesi, autoload, core, handler): config/bootstrap.php
// Registrasi rute: config/routes.php
$router = require __DIR__ . '/config/bootstrap.php';

require __DIR__ . '/config/routes.php';

$router->dispatch();