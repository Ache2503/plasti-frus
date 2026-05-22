<?php

/** @var App\Core\Router $router */

$router->get('/calidad/inspecciones', 'Quality\CalidadController@inspecciones');
$router->get('/calidad/inspecciones/create', 'Quality\CalidadController@inspeccionCreate');
$router->post('/calidad/inspecciones/store', 'Quality\CalidadController@inspeccionStore');
$router->post('/calidad/inspecciones/delete/{id}', 'Quality\CalidadController@inspeccionDelete');
$router->get('/calidad/rechazos', 'Quality\CalidadController@rechazos');
$router->get('/calidad/rechazos/create', 'Quality\CalidadController@rechazoCreate');
$router->post('/calidad/rechazos/store', 'Quality\CalidadController@rechazoStore');
$router->post('/calidad/rechazos/delete/{id}', 'Quality\CalidadController@rechazoDelete');

$router->get('/calidad/pendientes', 'Quality\CalidadController@pendientes');
$router->post('/calidad/inspecciones/realizar/{id}', 'Quality\CalidadController@realizarInspeccion');
