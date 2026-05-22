<?php

declare(strict_types=1);

error_reporting(E_ALL);
ini_set('display_errors', '1');
ini_set('log_errors', '1');

require_once __DIR__ . '/../vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

$configCache = __DIR__ . '/../storage/cache/config.php';
if (file_exists($configCache)) {
    $config = require $configCache;
} else {
    $config = [
        'config' => require __DIR__ . '/../config/app.php',
        'database' => require __DIR__ . '/../config/database.php',
        'permissions' => require __DIR__ . '/../config/permissions.php',
    ];
}

date_default_timezone_set($_ENV['APP_TIMEZONE'] ?? 'America/Mexico_City');

define('APP_URL', $_ENV['APP_URL'] ?? 'http://localhost/fabrica_plasticos');
define('APP_NAME', $_ENV['APP_NAME'] ?? 'Plasti Frus - Sistema de Gestión');
define('APP_TIMEZONE', $_ENV['APP_TIMEZONE'] ?? 'America/Mexico_City');
define('APP_ENV', $_ENV['APP_ENV'] ?? 'production');
define('APP_DEBUG', (bool)($_ENV['APP_DEBUG'] ?? false));
define('SESSION_TIME', (int)($_ENV['SESSION_TIME'] ?? 3600));
define('APP_CURRENCY', $_ENV['APP_CURRENCY'] ?? 'MXN');
define('ROL_VENDEDOR', 4);
define('COMISION_PORCENTAJE', 5);
define('VIEWS_PATH', __DIR__ . '/../resources/views');
define('STORAGE_PATH', __DIR__ . '/../storage');

if (APP_DEBUG) {
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
} else {
    error_reporting(0);
    ini_set('display_errors', '0');
}

\App\Exceptions\Handler::register();

return $config;
