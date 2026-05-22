<?php

/** @var App\Core\Router $router */

$router->get('/proveedores', 'Purchasing\ProveedoresController@index');
$router->get('/proveedores/create', 'Purchasing\ProveedoresController@create');
$router->post('/proveedores/store', 'Purchasing\ProveedoresController@store');
$router->get('/proveedores/edit/{id}', 'Purchasing\ProveedoresController@edit');
$router->post('/proveedores/update/{id}', 'Purchasing\ProveedoresController@update');
$router->post('/proveedores/delete/{id}', 'Purchasing\ProveedoresController@delete');
