<?php

/** @var App\Core\Router $router */

$router->get('/contabilidad', 'Accounting\ContabilidadController@index');
$router->get('/contabilidad/periodos', 'Accounting\ContabilidadController@periodos');
$router->post('/contabilidad/periodos/cerrar/{id}', 'Accounting\ContabilidadController@cerrarPeriodo');
$router->post('/contabilidad/periodos/reabrir/{id}', 'Accounting\ContabilidadController@reabrirPeriodo');

$router->get('/contabilidad/plan-cuentas', 'Accounting\PlanCuentasController@index');
$router->get('/contabilidad/plan-cuentas/create', 'Accounting\PlanCuentasController@create');
$router->post('/contabilidad/plan-cuentas/store', 'Accounting\PlanCuentasController@store');
$router->get('/contabilidad/plan-cuentas/edit/{id}', 'Accounting\PlanCuentasController@edit');
$router->post('/contabilidad/plan-cuentas/update/{id}', 'Accounting\PlanCuentasController@update');
$router->post('/contabilidad/plan-cuentas/delete/{id}', 'Accounting\PlanCuentasController@delete');

$router->get('/contabilidad/polizas', 'Accounting\PolizasController@index');
$router->get('/contabilidad/polizas/create', 'Accounting\PolizasController@create');
$router->post('/contabilidad/polizas/store', 'Accounting\PolizasController@store');
$router->get('/contabilidad/polizas/show/{id}', 'Accounting\PolizasController@show');
$router->post('/contabilidad/polizas/cancelar/{id}', 'Accounting\PolizasController@cancelar');

$router->get('/contabilidad/balance-general', 'Accounting\BalanceController@balanceGeneral');
$router->get('/contabilidad/estado-resultados', 'Accounting\BalanceController@estadoResultados');
$router->get('/contabilidad/balanza', 'Accounting\BalanceController@balanza');
$router->get('/contabilidad/libro-diario', 'Accounting\BalanceController@libroDiario');
$router->get('/contabilidad/libro-mayor/{id}', 'Accounting\BalanceController@libroMayor');

$router->get('/contabilidad/flujo-efectivo', 'Accounting\FlujoEfectivoController@index');
$router->get('/contabilidad/presupuestos', 'Accounting\PresupuestoController@index');
$router->post('/contabilidad/presupuestos/guardar', 'Accounting\PresupuestoController@guardar');
$router->get('/contabilidad/presupuestos/comparar', 'Accounting\PresupuestoController@comparar');
$router->get('/contabilidad/cierres', 'Accounting\CierreContableController@index');
$router->post('/contabilidad/cierres/cerrar', 'Accounting\CierreContableController@cerrar');
$router->post('/contabilidad/cierres/reabrir/{id}', 'Accounting\CierreContableController@reabrir');
$router->get('/contabilidad/exportar', 'Accounting\ExportController@index');
$router->get('/exportar/{tipo}/{reporte}', 'Accounting\ExportController@exportar');

// Facturas (admin)
$router->get('/facturas', 'Accounting\FacturasController@index');
$router->get('/facturas/solicitudes', 'Accounting\FacturasController@solicitudes');
$router->post('/facturas/procesar/{id}', 'Accounting\FacturasController@procesar');
$router->post('/facturas/rechazar/{id}', 'Accounting\FacturasController@rechazar');
$router->post('/facturas/contabilizar/{id}', 'Accounting\FacturasController@contabilizar');
