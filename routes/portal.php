<?php

/** @var App\Core\Router $router */

// Catálogo público
$router->get('/catalogo', 'Portal\CatalogoController@index');
$router->get('/producto/{id}', 'Portal\CatalogoController@show');

// Carrito de compras
$router->get('/carrito', 'Portal\CarritoController@index');
$router->post('/carrito/agregar', 'Portal\CarritoController@agregar');
$router->post('/carrito/eliminar/{key}', 'Portal\CarritoController@eliminar');
$router->post('/carrito/actualizar/{key}', 'Portal\CarritoController@actualizar');
$router->post('/carrito/checkout', 'Portal\CarritoController@checkout');

// Cartera / Wallet
$router->get('/cartera', 'Portal\CarteraController@index');
$router->post('/cartera/tarjetas/agregar', 'Portal\CarteraController@agregarTarjeta');
$router->post('/cartera/tarjetas/eliminar/{id}', 'Portal\CarteraController@eliminarTarjeta');
$router->post('/cartera/referencias/generar', 'Portal\CarteraController@generarReferencia');
$router->post('/cartera/referencias/cancelar/{id}', 'Portal\CarteraController@cancelarReferencia');

// Factura pública
$router->get('/factura', 'Portal\FacturaPublicaController@buscarForm');
$router->post('/factura/buscar', 'Portal\FacturaPublicaController@buscar');
$router->get('/factura/solicitar/{folio}', 'Portal\FacturaPublicaController@solicitarForm');
$router->post('/factura/solicitar/{folio}', 'Portal\FacturaPublicaController@solicitar');
$router->get('/factura/pdf/{folio}', 'Portal\FacturaPublicaController@pdf');

// Pedidos (agrupados por pedido)
$router->get('/mis-pedidos', 'Portal\PedidosController@index');
$router->get('/mis-pedidos/{id}', 'Portal\PedidosController@detalle');

// Tickets de soporte
$router->get('/tickets', 'Portal\TicketController@index');
$router->get('/tickets/nuevo', 'Portal\TicketController@create');
$router->post('/tickets/guardar', 'Portal\TicketController@store');
$router->get('/tickets/{id}', 'Portal\TicketController@show');
$router->post('/tickets/responder/{id}', 'Portal\TicketController@responder');
$router->post('/tickets/cerrar/{id}', 'Portal\TicketController@cerrar');

// Wishlist / Favoritos
$router->get('/wishlist', 'Portal\WishlistController@index');
$router->post('/wishlist/agregar/{productoId}', 'Portal\WishlistController@toggle');
$router->post('/wishlist/remover/{productoId}', 'Portal\WishlistController@remove');
$router->post('/wishlist/agregar-todos', 'Portal\WishlistController@agregarTodos');

// Notificaciones del cliente
$router->get('/notificaciones-cliente', 'Portal\NotificacionController@index');
$router->post('/notificaciones-cliente/marcar-leidas', 'Portal\NotificacionController@marcarLeidas');

// Direcciones de envío
$router->get('/direcciones', 'Portal\DireccionesController@index');
$router->post('/direcciones/agregar', 'Portal\DireccionesController@agregar');
$router->post('/direcciones/actualizar/{id}', 'Portal\DireccionesController@actualizar');
$router->post('/direcciones/eliminar/{id}', 'Portal\DireccionesController@eliminar');
$router->post('/direcciones/predeterminada/{id}', 'Portal\DireccionesController@predeterminada');
