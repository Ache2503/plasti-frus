<?php
use App\Core\Router;

/** @var Router $router */

// Auth
$router->get('/login', 'AuthController@showLogin');
$router->post('/login', 'AuthController@login');
$router->get('/register', 'AuthController@showRegister');
$router->post('/register', 'AuthController@register');
$router->get('/logout', 'AuthController@logout');

// Home / Dashboard
$router->get('/', 'HomeController@index');
$router->get('/home', 'HomeController@index');
$router->post('/reportar-paro', 'HomeController@reportarParo');
$router->post('/set-turno', 'HomeController@setTurno');
$router->get('/mis-compras', 'HomeController@misCompras');
$router->get('/mis-compras/{id}', 'HomeController@detalleCompra');

// Materiales
$router->get('/materiales', 'MaterialesController@index');
$router->get('/materiales/create', 'MaterialesController@create');
$router->post('/materiales/store', 'MaterialesController@store');
$router->get('/materiales/edit/{id}', 'MaterialesController@edit');
$router->post('/materiales/update/{id}', 'MaterialesController@update');
$router->get('/materiales/delete/{id}', 'MaterialesController@delete');

// Productos
$router->get('/productos', 'ProductosController@index');
$router->get('/productos/create', 'ProductosController@create');
$router->post('/productos/store', 'ProductosController@store');
$router->get('/productos/show/{id}', 'ProductosController@show');
$router->get('/productos/edit/{id}', 'ProductosController@edit');
$router->post('/productos/update/{id}', 'ProductosController@update');
$router->get('/productos/delete/{id}', 'ProductosController@delete');

// Órdenes de Producción
$router->get('/ordenes', 'OrdenesController@index');
$router->get('/mis-ordenes', 'OrdenesController@misOrdenes');
$router->get('/ordenes/create', 'OrdenesController@create');
$router->post('/ordenes/store', 'OrdenesController@store');
$router->get('/ordenes/detalle/{id}', 'OrdenesController@detalle');
$router->post('/ordenes/completar/{id}', 'OrdenesController@completar');
$router->post('/ordenes/iniciar/{id}', 'OrdenesController@iniciar');
$router->get('/ordenes/delete/{id}', 'OrdenesController@delete');

// Clientes
$router->get('/clientes', 'ClientesController@index');
$router->get('/clientes/create', 'ClientesController@create');
$router->post('/clientes/store', 'ClientesController@store');
$router->get('/clientes/edit/{id}', 'ClientesController@edit');
$router->post('/clientes/update/{id}', 'ClientesController@update');
$router->post('/clientes/delete/{id}', 'ClientesController@delete');
$router->get('/clientes/show/{id}', 'ClientesController@show');
$router->post('/clientes/reclamar/{id}', 'ClientesController@reclamarCliente');
$router->get('/mis-clientes', 'ClientesController@misClientes');
$router->get('/mis-comisiones', 'VendedorController@comisiones');
$router->get('/comisiones', 'VendedorController@adminComisiones');
$router->post('/comisiones/pagar/{id}', 'VendedorController@pagarComision');
$router->post('/notificaciones/marcar-leidas', 'VendedorController@marcarNotificacionesLeidas');
$router->post('/notificaciones-operador/marcar-leidas', 'HomeController@marcarNotificacionesOperador');
$router->post('/notificaciones-supervisor/marcar-leidas', 'HomeController@marcarNotificacionesSupervisor');

// Proveedores
$router->get('/proveedores', 'ProveedoresController@index');
$router->get('/proveedores/create', 'ProveedoresController@create');
$router->post('/proveedores/store', 'ProveedoresController@store');
$router->get('/proveedores/edit/{id}', 'ProveedoresController@edit');
$router->post('/proveedores/update/{id}', 'ProveedoresController@update');
$router->get('/proveedores/delete/{id}', 'ProveedoresController@delete');

// Recetas
$router->get('/recetas', 'RecetasController@index');
$router->get('/recetas/create', 'RecetasController@create');
$router->post('/recetas/store', 'RecetasController@store');
$router->get('/recetas/edit/{id}', 'RecetasController@edit');
$router->post('/recetas/update/{id}', 'RecetasController@update');
$router->get('/recetas/delete/{id}', 'RecetasController@delete');

// Moldes
$router->get('/moldes', 'MoldesController@index');
$router->get('/moldes/create', 'MoldesController@create');
$router->post('/moldes/store', 'MoldesController@store');
$router->get('/moldes/edit/{id}', 'MoldesController@edit');
$router->post('/moldes/update/{id}', 'MoldesController@update');
$router->get('/moldes/delete/{id}', 'MoldesController@delete');

// Máquinas
$router->get('/maquinas', 'MaquinasController@index');
$router->get('/maquinas/create', 'MaquinasController@create');
$router->post('/maquinas/store', 'MaquinasController@store');
$router->get('/maquinas/edit/{id}', 'MaquinasController@edit');
$router->post('/maquinas/update/{id}', 'MaquinasController@update');
$router->get('/maquinas/delete/{id}', 'MaquinasController@delete');

// Ventas
$router->get('/ventas', 'VentasController@index');
$router->get('/ventas/create', 'VentasController@create');
$router->post('/ventas/store', 'VentasController@store');
$router->get('/ventas/edit/{id}', 'VentasController@edit');
$router->post('/ventas/update/{id}', 'VentasController@update');
$router->get('/ventas/delete/{id}', 'VentasController@delete');

// Usuarios (Admin)
$router->get('/usuarios', 'UsuariosController@index');
$router->get('/usuarios/create', 'UsuariosController@create');
$router->post('/usuarios/store', 'UsuariosController@store');
$router->get('/usuarios/edit/{id}', 'UsuariosController@edit');
$router->post('/usuarios/update/{id}', 'UsuariosController@update');
$router->get('/usuarios/delete/{id}', 'UsuariosController@delete');

// Profile
$router->get('/profile', 'ProfileController@index');
$router->post('/profile/update-password', 'ProfileController@updatePassword');
$router->post('/profile/update-cliente', 'ProfileController@updateCliente');
$router->post('/profile/update-personal', 'ProfileController@updatePersonal');
$router->post('/profile/update-contacto', 'ProfileController@updateContacto');

// Cartera / Wallet
$router->get('/cartera', 'CarteraController@index');
$router->post('/cartera/tarjetas/agregar', 'CarteraController@agregarTarjeta');
$router->post('/cartera/tarjetas/eliminar/{id}', 'CarteraController@eliminarTarjeta');
$router->post('/cartera/referencias/generar', 'CarteraController@generarReferencia');
$router->post('/cartera/referencias/cancelar/{id}', 'CarteraController@cancelarReferencia');

// Calidad
$router->get('/calidad/inspecciones', 'CalidadController@inspecciones');
$router->get('/calidad/inspecciones/create', 'CalidadController@inspeccionCreate');
$router->post('/calidad/inspecciones/store', 'CalidadController@inspeccionStore');
$router->get('/calidad/rechazos', 'CalidadController@rechazos');
$router->get('/calidad/rechazos/create', 'CalidadController@rechazoCreate');
$router->post('/calidad/rechazos/store', 'CalidadController@rechazoStore');

// Kardex
$router->get('/kardex', 'KardexController@index');
$router->get('/kardex/create', 'KardexController@create');
$router->post('/kardex/store', 'KardexController@store');
$router->get('/kardex/detalle/{id}', 'KardexController@detalle');

// Incidencias
$router->get('/incidencias', 'IncidenciasController@index');
$router->get('/incidencias/create', 'IncidenciasController@create');
$router->post('/incidencias/store', 'IncidenciasController@store');
$router->post('/incidencias/cerrar/{id}', 'IncidenciasController@cerrar');
$router->get('/incidencias/delete/{id}', 'IncidenciasController@delete');

// Mantenimiento
$router->get('/mantenimiento', 'MantenimientoController@index');
$router->get('/mantenimiento/create', 'MantenimientoController@create');
$router->post('/mantenimiento/store', 'MantenimientoController@store');
$router->get('/mantenimiento/plan', 'MantenimientoController@plan');
$router->post('/mantenimiento/plan/store', 'MantenimientoController@planStore');
$router->get('/mantenimiento/paros', 'MantenimientoController@paros');
$router->get('/mantenimiento/paros/create', 'MantenimientoController@paroCreate');
$router->post('/mantenimiento/paros/store', 'MantenimientoController@paroStore');
$router->get('/mantenimiento/delete/{id}', 'MantenimientoController@delete');
$router->get('/mantenimiento/paros/delete/{id}', 'MantenimientoController@paroDelete');

// Calidad
$router->get('/calidad/inspecciones', 'CalidadController@inspecciones');
$router->get('/calidad/inspecciones/create', 'CalidadController@inspeccionCreate');
$router->post('/calidad/inspecciones/store', 'CalidadController@inspeccionStore');
$router->get('/calidad/inspecciones/delete/{id}', 'CalidadController@inspeccionDelete');
$router->get('/calidad/rechazos', 'CalidadController@rechazos');
$router->get('/calidad/rechazos/create', 'CalidadController@rechazoCreate');
$router->post('/calidad/rechazos/store', 'CalidadController@rechazoStore');
$router->get('/calidad/rechazos/delete/{id}', 'CalidadController@rechazoDelete');

// Notificaciones
$router->get('/notificaciones', 'NotificacionesController@index');

// Reportes
$router->get('/reportes/kpi', 'ReportesController@kpi');
$router->get('/reportes/produccion', 'ReportesController@produccion');

// Catálogo público / clientes
$router->get('/catalogo', 'CatalogoController@index');
$router->get('/producto/{id}', 'CatalogoController@show');

// Carrito de compras (clientes)
$router->get('/carrito', 'CarritoController@index');
$router->post('/carrito/agregar', 'CarritoController@agregar');
$router->post('/carrito/eliminar/{key}', 'CarritoController@eliminar');
$router->post('/carrito/actualizar/{key}', 'CarritoController@actualizar');
$router->post('/carrito/checkout', 'CarritoController@checkout');

$router->post('/facturas/request/{id}', 'HomeController@solicitarFactura');
$router->post('/facturas/cancelar/{id}', 'HomeController@cancelarFactura');
$router->post('/cliente/asignar-vendedor', 'HomeController@asignarVendedor');

// Tickets
$router->get('/tickets/{folio}', 'TicketsController@show');
$router->get('/tickets/{folio}/pdf', 'TicketsController@pdf');

// Portal público de facturación
$router->get('/factura', 'FacturaPublicaController@buscarForm');
$router->post('/factura/buscar', 'FacturaPublicaController@buscar');
$router->get('/factura/solicitar/{folio}', 'FacturaPublicaController@solicitarForm');
$router->post('/factura/solicitar/{folio}', 'FacturaPublicaController@solicitar');
$router->get('/factura/pdf/{folio}', 'FacturaPublicaController@pdf');

// Panel de administración de facturas
$router->get('/facturas', 'FacturasController@index');
$router->get('/facturas/solicitudes', 'FacturasController@solicitudes');
$router->post('/facturas/procesar/{id}', 'FacturasController@procesar');
$router->post('/facturas/rechazar/{id}', 'FacturasController@rechazar');
$router->post('/facturas/contabilizar/{id}', 'FacturasController@contabilizar');

// Control de acceso operador
$router->get('/acceso-denegado', 'AuthController@accesoDenegado');
$router->get('/admin/horarios', 'AdminController@horariosOperador');
$router->post('/admin/horarios/guardar', 'AdminController@guardarHorario');
$router->post('/admin/horarios/autorizar', 'AdminController@autorizarAcceso');

// Contabilidad
$router->get('/contabilidad', 'ContabilidadController@index');
$router->get('/contabilidad/periodos', 'ContabilidadController@periodos');
$router->post('/contabilidad/periodos/cerrar/{id}', 'ContabilidadController@cerrarPeriodo');
$router->post('/contabilidad/periodos/reabrir/{id}', 'ContabilidadController@reabrirPeriodo');

$router->get('/contabilidad/plan-cuentas', 'PlanCuentasController@index');
$router->get('/contabilidad/plan-cuentas/create', 'PlanCuentasController@create');
$router->post('/contabilidad/plan-cuentas/store', 'PlanCuentasController@store');
$router->get('/contabilidad/plan-cuentas/edit/{id}', 'PlanCuentasController@edit');
$router->post('/contabilidad/plan-cuentas/update/{id}', 'PlanCuentasController@update');
$router->post('/contabilidad/plan-cuentas/delete/{id}', 'PlanCuentasController@delete');

$router->get('/contabilidad/polizas', 'PolizasController@index');
$router->get('/contabilidad/polizas/create', 'PolizasController@create');
$router->post('/contabilidad/polizas/store', 'PolizasController@store');
$router->get('/contabilidad/polizas/show/{id}', 'PolizasController@show');
$router->post('/contabilidad/polizas/cancelar/{id}', 'PolizasController@cancelar');

$router->get('/contabilidad/balance-general', 'BalanceController@balanceGeneral');
$router->get('/contabilidad/estado-resultados', 'BalanceController@estadoResultados');
$router->get('/contabilidad/balanza', 'BalanceController@balanza');
$router->get('/contabilidad/libro-diario', 'BalanceController@libroDiario');
$router->get('/contabilidad/libro-mayor/{id}', 'BalanceController@libroMayor');
