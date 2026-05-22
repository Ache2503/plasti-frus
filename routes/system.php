<?php

/** @var App\Core\Router $router */

// Usuarios
$router->get('/usuarios', 'System\UsuariosController@index');
$router->get('/usuarios/create', 'System\UsuariosController@create');
$router->post('/usuarios/store', 'System\UsuariosController@store');
$router->get('/usuarios/edit/{id}', 'System\UsuariosController@edit');
$router->post('/usuarios/update/{id}', 'System\UsuariosController@update');
$router->post('/usuarios/delete/{id}', 'System\UsuariosController@delete');

// Profile
$router->get('/profile', 'System\ProfileController@index');
$router->post('/profile/update-password', 'System\ProfileController@updatePassword');
$router->post('/profile/update-cliente', 'System\ProfileController@updateCliente');
$router->post('/profile/update-personal', 'System\ProfileController@updatePersonal');
$router->post('/profile/update-contacto', 'System\ProfileController@updateContacto');

// Admin
$router->get('/admin/horarios', 'System\AdminController@horariosOperador');
$router->post('/admin/horarios/guardar', 'System\AdminController@guardarHorario');
$router->post('/admin/horarios/autorizar', 'System\AdminController@autorizarAcceso');

// Notificaciones
$router->get('/notificaciones', 'System\NotificacionesController@index');

// Reportes
$router->get('/reportes/kpi', 'System\ReportesController@kpi');
$router->get('/reportes/produccion', 'System\ReportesController@produccion');

// Incidencias
$router->get('/incidencias', 'System\IncidenciasController@index');
$router->get('/incidencias/create', 'System\IncidenciasController@create');
$router->post('/incidencias/store', 'System\IncidenciasController@store');
$router->post('/incidencias/cerrar/{id}', 'System\IncidenciasController@cerrar');
$router->post('/incidencias/delete/{id}', 'System\IncidenciasController@delete');

// Bitácora de turno
$router->get('/bitacora', 'System\ShiftLogController@index');
$router->post('/bitacora', 'System\ShiftLogController@store');

// Auditoría
$router->get('/admin/logs', 'System\AuditLogsController@index');
