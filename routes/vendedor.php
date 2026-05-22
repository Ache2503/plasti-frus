<?php
/** @var App\Core\Router $router */

// Pipeline de Ventas (Kanban)
$router->get('/pipeline', 'Crm\OportunidadController@index');
$router->post('/oportunidades', 'Crm\OportunidadController@store');
$router->put('/oportunidades/{id}', 'Crm\OportunidadController@update');
$router->delete('/oportunidades/{id}', 'Crm\OportunidadController@destroy');
$router->post('/pipeline/store', 'Crm\OportunidadController@store');
$router->post('/pipeline/update/{id}', 'Crm\OportunidadController@update');
$router->post('/pipeline/etapa/{id}', 'Crm\OportunidadController@updateEtapa');
$router->post('/pipeline/delete/{id}', 'Crm\OportunidadController@destroy');
$router->get('/pipeline/data', 'Crm\OportunidadController@data');
$router->get('/pipeline/resumen', 'Crm\OportunidadController@resumen');

// Interacciones con clientes
$router->get('/clientes/historial/{id}', 'Crm\ClientesController@historial');
$router->post('/interacciones', 'Crm\InteraccionController@store');
$router->post('/interacciones/store', 'Crm\InteraccionController@store');

// Mensajería interna
$router->get('/mensajes', 'Crm\MensajeController@index');
$router->post('/mensajes', 'Crm\MensajeController@store');
$router->post('/mensajes/enviar', 'Crm\MensajeController@store');
$router->get('/mensajes/{id}', 'Crm\MensajeController@show');
$router->post('/mensajes/leer/{id}', 'Crm\MensajeController@marcarLeido');
$router->get('/mensajes/no-leidos', 'Crm\MensajeController@noLeidos');

// Reportes vendedor
$router->get('/vendedor/reportes', 'System\ReportesVendedorController@index');
$router->post('/vendedor/reportes/generar', 'System\ReportesVendedorController@generar');
$router->get('/vendedor/reportes/export-excel/{tipo}', 'System\ReportesVendedorController@exportExcel');
$router->get('/vendedor/reportes/export-pdf/{tipo}', 'System\ReportesVendedorController@exportPDF');
$router->get('/reportes-vendedor', 'System\ReportesVendedorController@index');
$router->get('/reportes-vendedor/export', 'System\ReportesVendedorController@exportExcel');

// Datos para gráficos de comisiones
$router->get('/mis-comisiones/data', 'Sales\VendedorController@comisionesData');
$router->get('/mis-comisiones/filtro', 'Sales\VendedorController@comisionesFiltro');
$router->get('/mis-comisiones/datos-grafico', 'Sales\VendedorController@comisionesData');

// Legacy routes (without namespace prefix for app/Controllers/)
$router->get('/legacy/pipeline', 'OportunidadController@index');
$router->post('/legacy/oportunidades', 'OportunidadController@store');
$router->get('/legacy/agenda', 'ActividadController@index');
$router->get('/legacy/mensajes', 'MensajeController@index');
$router->get('/legacy/reportes-vendedor', 'ReportesVendedorController@index');
