<?php

/** @var App\Core\Router $router */

$router->get('/', 'Dashboard\HomeController@index');
$router->get('/home', 'Dashboard\HomeController@index');
$router->post('/reportar-paro', 'Dashboard\HomeController@reportarParo');
$router->post('/set-turno', 'Dashboard\HomeController@setTurno');
$router->get('/mis-compras', 'Dashboard\HomeController@misCompras');
$router->get('/mis-compras/{id}', 'Dashboard\HomeController@detalleCompra');

$router->post('/facturas/request/{id}', 'Dashboard\HomeController@solicitarFactura');
$router->post('/facturas/cancelar/{id}', 'Dashboard\HomeController@cancelarFactura');
$router->post('/cliente/asignar-vendedor', 'Dashboard\HomeController@asignarVendedor');

$router->post('/notificaciones-operador/marcar-leidas', 'Dashboard\HomeController@marcarNotificacionesOperador');
$router->post('/notificaciones-supervisor/marcar-leidas', 'Dashboard\HomeController@marcarNotificacionesSupervisor');

$router->get('/operador/dashboard', 'Dashboard\OperadorDashboardController@index');
