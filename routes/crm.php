<?php

/** @var App\Core\Router $router */

$router->get('/clientes', 'Crm\ClientesController@index');
$router->get('/clientes/create', 'Crm\ClientesController@create');
$router->post('/clientes/store', 'Crm\ClientesController@store');
$router->get('/clientes/edit/{id}', 'Crm\ClientesController@edit');
$router->post('/clientes/update/{id}', 'Crm\ClientesController@update');
$router->post('/clientes/delete/{id}', 'Crm\ClientesController@delete');
$router->get('/clientes/show/{id}', 'Crm\ClientesController@show');
$router->post('/clientes/reclamar/{id}', 'Crm\ClientesController@reclamarCliente');
$router->get('/mis-clientes', 'Crm\ClientesController@misClientes');

$router->get('/soporte', 'Crm\SoporteController@index');
$router->get('/soporte/{id}', 'Crm\SoporteController@show');
$router->post('/soporte/responder/{id}', 'Crm\SoporteController@responder');
$router->post('/soporte/asignar/{id}', 'Crm\SoporteController@asignar');
$router->post('/soporte/cerrar/{id}', 'Crm\SoporteController@cerrar');
$router->post('/soporte/abrir/{id}', 'Crm\SoporteController@abrir');
