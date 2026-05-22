<?php

/** @var App\Core\Router $router */

$router->get('/kardex', 'Inventory\KardexController@index');
$router->get('/kardex/create', 'Inventory\KardexController@create');
$router->post('/kardex/store', 'Inventory\KardexController@store');
$router->get('/kardex/detalle/{id}', 'Inventory\KardexController@detalle');
