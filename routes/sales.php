<?php

/** @var App\Core\Router $router */

// Ventas
$router->get('/ventas', 'Sales\VentasController@index');
$router->get('/ventas/create', 'Sales\VentasController@create');
$router->post('/ventas/store', 'Sales\VentasController@store');
$router->get('/ventas/edit/{id}', 'Sales\VentasController@edit');
$router->post('/ventas/update/{id}', 'Sales\VentasController@update');
$router->post('/ventas/delete/{id}', 'Sales\VentasController@delete');

// Tickets
$router->get('/tickets/{folio}', 'Sales\TicketsController@show');
$router->get('/tickets/{folio}/pdf', 'Sales\TicketsController@pdf');

// Comisiones
$router->get('/mis-comisiones', 'Sales\VendedorController@comisiones');
$router->get('/comisiones', 'Sales\VendedorController@adminComisiones');
$router->post('/comisiones/pagar/{id}', 'Sales\VendedorController@pagarComision');
$router->post('/notificaciones/marcar-leidas', 'Sales\VendedorController@marcarNotificacionesLeidas');
