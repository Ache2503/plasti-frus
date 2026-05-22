<?php

/** @var App\Core\Router $router */

// Legacy controllers for client portal features

// Pedidos
$router->get('/legacy/mis-pedidos', 'PedidosController@index');
$router->get('/legacy/mis-pedidos/{id}', 'PedidosController@detalle');

// Tickets
$router->get('/legacy/tickets', 'TicketController@index');
$router->get('/legacy/tickets/nuevo', 'TicketController@create');
$router->post('/legacy/tickets/guardar', 'TicketController@store');
$router->get('/legacy/tickets/{id}', 'TicketController@show');
$router->post('/legacy/tickets/responder/{id}', 'TicketController@responder');
$router->post('/legacy/tickets/cerrar/{id}', 'TicketController@cerrar');

// Wishlist
$router->get('/legacy/wishlist', 'WishlistController@index');
$router->post('/legacy/wishlist/agregar/{productoId}', 'WishlistController@toggle');
$router->post('/legacy/wishlist/remover/{productoId}', 'WishlistController@remove');

// Direcciones
$router->get('/legacy/direcciones', 'DireccionesController@index');
$router->post('/legacy/direcciones/agregar', 'DireccionesController@agregar');
$router->post('/legacy/direcciones/actualizar/{id}', 'DireccionesController@actualizar');
$router->post('/legacy/direcciones/eliminar/{id}', 'DireccionesController@eliminar');
$router->post('/legacy/direcciones/predeterminada/{id}', 'DireccionesController@predeterminada');
