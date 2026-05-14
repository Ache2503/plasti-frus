<?php
session_start();
require_once __DIR__ . '/../app/config/app.php';

use App\Core\Router;

$router = new Router();
require_once __DIR__ . '/../app/config/routes.php';
$router->dispatch($_SERVER['REQUEST_URI'], $_SERVER['REQUEST_METHOD']);
