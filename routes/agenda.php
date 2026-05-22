<?php
/** @var App\Core\Router $router */

$router->get('/agenda', 'Crm\ActividadController@index');
$router->get('/agenda/data', 'Crm\ActividadController@data');
$router->post('/actividades/store', 'Crm\ActividadController@store');
$router->post('/actividades/update/{id}', 'Crm\ActividadController@update');
$router->post('/actividades/delete/{id}', 'Crm\ActividadController@destroy');
$router->put('/actividades/{id}', 'Crm\ActividadController@update');
$router->delete('/actividades/{id}', 'Crm\ActividadController@destroy');
