<?php

/** @var App\Core\Router $router */

// Materiales
$router->get('/materiales', 'Production\MaterialesController@index');
$router->get('/materiales/create', 'Production\MaterialesController@create');
$router->post('/materiales/store', 'Production\MaterialesController@store');
$router->get('/materiales/edit/{id}', 'Production\MaterialesController@edit');
$router->post('/materiales/update/{id}', 'Production\MaterialesController@update');
$router->post('/materiales/delete/{id}', 'Production\MaterialesController@delete');

// Productos
$router->get('/productos', 'Production\ProductosController@index');
$router->get('/productos/create', 'Production\ProductosController@create');
$router->post('/productos/store', 'Production\ProductosController@store');
$router->get('/productos/show/{id}', 'Production\ProductosController@show');
$router->get('/productos/edit/{id}', 'Production\ProductosController@edit');
$router->post('/productos/update/{id}', 'Production\ProductosController@update');
$router->post('/productos/delete/{id}', 'Production\ProductosController@delete');

// Órdenes de Producción
$router->get('/ordenes', 'Production\OrdenesController@index');
$router->get('/mis-ordenes', 'Production\OrdenesController@misOrdenes');
$router->get('/ordenes/create', 'Production\OrdenesController@create');
$router->post('/ordenes/store', 'Production\OrdenesController@store');
$router->get('/ordenes/edit/{id}', 'Production\OrdenesController@edit');
$router->post('/ordenes/update/{id}', 'Production\OrdenesController@update');
$router->get('/ordenes/detalle/{id}', 'Production\OrdenesController@detalle');
$router->post('/ordenes/completar/{id}', 'Production\OrdenesController@completar');
$router->post('/ordenes/iniciar/{id}', 'Production\OrdenesController@iniciar');
$router->post('/ordenes/delete/{id}', 'Production\OrdenesController@delete');

// Recetas
$router->get('/recetas', 'Production\RecetasController@index');
$router->get('/recetas/create', 'Production\RecetasController@create');
$router->post('/recetas/store', 'Production\RecetasController@store');
$router->get('/recetas/edit/{id}', 'Production\RecetasController@edit');
$router->post('/recetas/update/{id}', 'Production\RecetasController@update');
$router->post('/recetas/delete/{id}', 'Production\RecetasController@delete');

// Moldes
$router->get('/moldes', 'Production\MoldesController@index');
$router->get('/moldes/create', 'Production\MoldesController@create');
$router->post('/moldes/store', 'Production\MoldesController@store');
$router->get('/moldes/edit/{id}', 'Production\MoldesController@edit');
$router->post('/moldes/update/{id}', 'Production\MoldesController@update');
$router->post('/moldes/delete/{id}', 'Production\MoldesController@delete');

// Máquinas
$router->get('/maquinas', 'Production\MaquinasController@index');
$router->get('/maquinas/create', 'Production\MaquinasController@create');
$router->post('/maquinas/store', 'Production\MaquinasController@store');
$router->get('/maquinas/edit/{id}', 'Production\MaquinasController@edit');
$router->post('/maquinas/update/{id}', 'Production\MaquinasController@update');
$router->post('/maquinas/delete/{id}', 'Production\MaquinasController@delete');
$router->get('/maquinas/estado', 'Production\MaquinasController@estado');
$router->get('/maquinas/estado-json', 'Production\MaquinasController@estadoJSON');
