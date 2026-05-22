<?php

/** @var App\Core\Router $router */

$router->get('/mantenimiento', 'Maintenance\MantenimientoController@index');
$router->get('/mantenimiento/create', 'Maintenance\MantenimientoController@create');
$router->post('/mantenimiento/store', 'Maintenance\MantenimientoController@store');
$router->get('/mantenimiento/plan', 'Maintenance\MantenimientoController@plan');
$router->post('/mantenimiento/plan/store', 'Maintenance\MantenimientoController@planStore');
$router->get('/mantenimiento/paros', 'Maintenance\MantenimientoController@paros');
$router->get('/mantenimiento/paros/create', 'Maintenance\MantenimientoController@paroCreate');
$router->post('/mantenimiento/paros/store', 'Maintenance\MantenimientoController@paroStore');
$router->post('/mantenimiento/delete/{id}', 'Maintenance\MantenimientoController@delete');
$router->post('/mantenimiento/paros/delete/{id}', 'Maintenance\MantenimientoController@paroDelete');
