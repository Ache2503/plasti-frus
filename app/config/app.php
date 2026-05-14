<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');
ini_set('log_errors', '1');

require_once __DIR__ . '/../../vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../../');
$dotenv->load();

define('APP_URL', $_ENV['APP_URL'] ?? 'http://localhost/fabrica_plasticos');
define('APP_NAME', 'Plasti Frus - Sistema de Gestión de Fábrica de Plásticos');
define('APP_TIMEZONE', 'America/Mexico_City');
define('SESSION_TIME', 3600);
define('APP_CURRENCY', 'MXN');
define('ROL_VENDEDOR', 4);
define('COMISION_PORCENTAJE', 5);

date_default_timezone_set(APP_TIMEZONE);

require_once __DIR__ . '/../helpers/funciones.php';
require_once __DIR__ . '/../helpers/validators.php';

spl_autoload_register(function ($class) {
    $prefix = 'App\\';
    $baseDir = __DIR__ . '/../';
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }
    $relativeClass = substr($class, $len);
    $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';
    if (file_exists($file)) {
        require $file;
    }
});
