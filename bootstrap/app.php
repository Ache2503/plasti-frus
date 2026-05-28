<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->safeLoad();

$isDebug = filter_var($_ENV['APP_DEBUG'] ?? false, FILTER_VALIDATE_BOOLEAN);

error_reporting($isDebug ? E_ALL : 0);
ini_set('display_errors', $isDebug ? '1' : '0');
ini_set('log_errors', '1');

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
define('APP_DEBUG', $isDebug);
define('SESSION_TIME', (int)($_ENV['SESSION_TIME'] ?? 3600));
define('APP_CURRENCY', $_ENV['APP_CURRENCY'] ?? 'MXN');
define('ROL_VENDEDOR', 4);
define('COMISION_PORCENTAJE', 5);
define('VIEWS_PATH', __DIR__ . '/../resources/views');
define('STORAGE_PATH', __DIR__ . '/../storage');

\App\Exceptions\Handler::register();

return $config;
