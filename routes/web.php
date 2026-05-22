<?php
/**
 * Main Route File
 * Carga todas las rutas organizadas por módulo
 */

/** @var App\Core\Router $router */

require __DIR__ . '/auth.php';
require __DIR__ . '/dashboard.php';
require __DIR__ . '/production.php';
require __DIR__ . '/sales.php';
require __DIR__ . '/purchasing.php';
require __DIR__ . '/accounting.php';
require __DIR__ . '/crm.php';
require __DIR__ . '/quality.php';
require __DIR__ . '/maintenance.php';
require __DIR__ . '/inventory.php';
require __DIR__ . '/portal.php';
require __DIR__ . '/system.php';
require __DIR__ . '/api.php';
require __DIR__ . '/vendedor.php';
require __DIR__ . '/agenda.php';
require __DIR__ . '/client.php';
