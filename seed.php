<?php
/**
 * Script para cargar datos de demostracion
 *
 * Uso: php seed.php
 *      php seed.php --fresh  (truncate tables first)
 */

require __DIR__ . '/vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

date_default_timezone_set($_ENV['APP_TIMEZONE'] ?? 'America/Mexico_City');

define('APP_URL', $_ENV['APP_URL'] ?? 'http://localhost:8000');
define('APP_NAME', $_ENV['APP_NAME'] ?? 'Plasti Frus');
define('APP_DEBUG', true);
define('APP_CURRENCY', 'MXN');
define('ROL_VENDEDOR', 4);

echo "+------------------------------------------+\n";
echo "|   Plasti Frus - Datos de Demostracion    |\n";
echo "+------------------------------------------+\n\n";

$fresh = in_array('--fresh', $argv ?? []);

if ($fresh) {
    echo "Modo --fresh: Se limpiaran los datos existentes.\n";
    echo "Presiona Enter para continuar o Ctrl+C para cancelar...\n";
    trim(fgets(STDIN));
}

require __DIR__ . '/database/seeds/DatabaseSeeder.php';

$seeder = new \Database\Seeds\DatabaseSeeder($fresh);
$seeder->run();
