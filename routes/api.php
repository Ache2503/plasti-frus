<?php
/**
 * API Routes
 * Prefijo: /api/
 */

/** @var App\Core\Router $router */

// Productos API
$router->get('/api/productos', 'Api\ProductoApiController@index');
$router->get('/api/productos/{id}', 'Api\ProductoApiController@show');

// Clientes API
$router->get('/api/clientes', 'Api\ClienteApiController@index');
$router->get('/api/clientes/{id}', 'Api\ClienteApiController@show');

// Ventas API
$router->get('/api/ventas', 'Api\VentaApiController@index');
$router->get('/api/ventas/{id}', 'Api\VentaApiController@show');

// Dashboard Stats API
$router->get('/api/stats/dashboard', 'Api\StatsApiController@dashboard');
$router->get('/api/stats/produccion', 'Api\StatsApiController@produccion');
