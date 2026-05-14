# Documentación del Sistema Plasti Frus

## Visión General

**Plasti Frus** es un sistema ERP/MRP para una fábrica de inyección de plásticos. Construido con PHP 8.3 (MVC propio), MySQL, Bootstrap 5 y DataTables.

### Arquitectura
- **Framework**: MVC personalizado (`app/Core/`)
- **ORM**: Active Record con PDO preparado
- **Frontend**: Bootstrap 5.3, DataTables 1.13, Chart.js
- **Auth**: Sesiones PHP + bcrypt
- **Rol de usuarios**: 1=Admin, 2=Operador, 3=Supervisor, 4=Vendedor, 5=Cliente, 6=Contador

---

## 1. Módulo de Autenticación (Auth)

**Propósito**: Login, registro y control de acceso al sistema.

### Rutas
| Método | Ruta | Función |
|--------|------|---------|
| GET | `/login` | Mostrar formulario de login |
| POST | `/login` | Autenticar usuario |
| GET | `/register` | Formulario de registro |
| POST | `/register` | Registrar nuevo usuario |
| GET | `/logout` | Cerrar sesión |
| GET | `/acceso-denegado` | Página de acceso denegado |

### Controlador: `AuthController`
- `showLogin()` / `login()` — Autenticación con verificación de horario para operadores
- `showRegister()` / `register()` — Registro de vendedores y clientes
- `logout()` / `accesoDenegado()` — Cierre de sesión

### Vistas
- `auth/login.php`, `auth/register.php`

---

## 2. Módulo Dashboard / Home

**Propósito**: Panel principal con vista personalizada según el rol del usuario.

### Rutas
| Método | Ruta | Función |
|--------|------|---------|
| GET | `/`, `/home` | Dashboard según rol |
| POST | `/reportar-paro` | Reportar paro de máquina |
| POST | `/set-turno` | Cambiar turno |
| GET | `/mis-compras` | Historial de compras (cliente) |
| POST | `/facturas/request/{id}` | Solicitar factura |
| POST | `/cliente/asignar-vendedor` | Asignar vendedor |

### Controlador: `HomeController`
- `index()` — Enruta a 6 dashboards distintos según el rol
- `adminDashboard()` — Estadísticas generales (counts de materiales, productos, órdenes, clientes, etc.)
- `supervisorDashboard()` — Producción del día, incidencias activas, estado de máquinas
- `operadorDashboard()` — Órdenes por turno, estados de máquina
- `vendedorDashboard()` — Clientes, ventas del mes, comisiones, top clientes
- `clienteDashboard()` — Compras, facturas, carrito, vendedor asignado
- `contadorDashboard()` — Totales de cuentas, pólizas, facturas pendientes

### Vistas
- `home/index.php` (contiene lógica de renderizado por rol)
- Vistas parciales: `home/partials/`

---

## 3. Módulo de Materiales

**Propósito**: Gestionar materias primas usadas en producción.

### Rutas: CRUD completo `/materiales`

### Controlador: `MaterialesController`
- `index()` — Lista con información del proveedor
- `create()` / `store()` — Crear material
- `edit()` / `update()` — Editar material
- `delete()` — Eliminar material

### Modelo: `Material`
- `getWithProveedor()` — Materiales + JOIN con proveedores
- `getLowStock()` — Materiales con stock bajo (stock <= punto_reorden)
- `updateStock()` — Actualizar stock
- `getByTipo()` / `getTipos()` — Filtros por tipo

### Vista
- `materiales/index.php`, `materiales/create.php`, `materiales/edit.php`

### BD: `materiales`, `kardex_materiales`, `alerta_stock_materiales`

---

## 4. Módulo de Productos

**Propósito**: Catálogo de productos terminados.

### Rutas: CRUD completo + show `/productos`

### Controlador: `ProductosController`
- `index()` — Lista con conteo de recetas asociadas
- `show()` — Detalle del producto con recetas y órdenes
- CRUD estándar

### Modelo: `Producto`
- `getWithRelations()` — Productos + conteo de recetas
- `getFamilias()` / `getLineas()` / `getColores()` — Filtros
- `getRecetasByProducto()` — Recetas asociadas
- `getOrdenesByProducto()` — Órdenes de producción

### Vistas
- `productos/index.php`, `productos/create.php`, `productos/edit.php`, `productos/show.php`

### BD: `productos`, `recetas_cabecera`, `ordenes_cabecera`

---

## 5. Módulo de Recetas

**Propósito**: Definir recetas de producción (combinación de materiales, máquina y parámetros).

### Rutas: CRUD `/recetas`

### Controlador: `RecetasController`
- `index()` — Lista con producto y máquina
- `create()` / `store()` — Crear receta con detalles (materiales + porcentajes)
- `edit()` / `update()` — Editar receta

### Modelo: `RecetaCabe`
- `getWithRelations()` — JOIN con productos y máquinas
- `getDetallesByReceta()` — Materiales con porcentajes
- `addDetalle()` / `removeDetalle()` — Gestión de materiales

### Vistas
- `recetas/index.php`, `recetas/create.php`, `recetas/edit.php`

### BD: `recetas_cabecera`, `recetas_detalle`, `historial_cambios_recetas`

---

## 6. Módulo de Órdenes de Producción

**Propósito**: Gestionar órdenes de producción (planificar, iniciar, completar).

### Rutas
| Método | Ruta | Función |
|--------|------|---------|
| GET | `/ordenes` | Lista con filtros |
| GET | `/mis-ordenes` | Órdenes del operador |
| GET | `/ordenes/create` | Crear orden |
| POST | `/ordenes/store` | Guardar orden |
| GET | `/ordenes/detalle/{id}` | Detalle con mermas y seguimiento |
| POST | `/ordenes/iniciar/{id}` | Iniciar producción |
| POST | `/ordenes/completar/{id}` | Completar con mermas |
| GET | `/ordenes/delete/{id}` | Eliminar |

### Controlador: `OrdenesController`
- `index()` — Filtros por fecha, turno, producto
- `misOrdenes()` — Filtrado por turno para operadores
- `store()` — Inserta orden + notifica operadores y supervisores
- `iniciar()` — Cambia estatus a 'en_progreso'
- `completar()` — Registra cantidad real, mermas, actualiza ciclos del molde

### Modelo: `OrdenCabe`
- `getWithRelations()` — JOIN completo + filtros dinámicos
- `getMermasByOrden()` / `getSeguimientoByOrden()`
- `getByDateRange()` / `getPending()` / `getStats()`

### Vistas
- `ordenes/index.php`, `ordenes/create.php`, `ordenes/detalle.php`, `ordenes/edit.php`

### BD: `ordenes_cabecera`, `ordenes_merma`, `seguimiento_ordenes`, `historial_estatus_ordenes`

---

## 7. Módulo de Máquinas

**Propósito**: Catálogo de máquinas de inyección con historial de mantenimiento.

### Rutas: CRUD `/maquinas`

### Controlador: `MaquinasController`
- CRUD estándar

### Modelo: `Maquina`
- `getMantenimientos()` / `getPlanMantenimiento()`
- `getCalibraciones()` / `getConsumosEnergia()`
- `getIndicadoresOEE()` / `getBitacoraParos()`
- `getActiveMachines()` — Máquinas activas

### Vistas
- `maquinas/index.php`, `maquinas/create.php`, `maquinas/edit.php`

### BD: `maquinas`, `mantenimientos_maquinas`, `plan_mantenimiento`, `calibraciones_maquinas`, `energia_consumo`, `indicadores_oee`, `bitacora_paros`

---

## 8. Módulo de Moldes

**Propósito**: Gestionar moldes de inyección, su vida útil y ciclos acumulados.

### Rutas: CRUD `/moldes`

### Controlador: `MoldesController`
- CRUD estándar

### Modelo: `Molde`
- `getWithCede()` — JOIN con cedes (ubicaciones)
- `updateCiclos()` — Actualizar ciclos acumulados
- `getMantenimientos()` / `getAvailableCedes()`

### Vistas
- `moldes/index.php`, `moldes/create.php`, `moldes/edit.php`

### BD: `moldes`, `mantenimientos_moldes`, `cedes`

---

## 9. Módulo de Clientes

**Propósito**: CRM básico - gestión de clientes con asignación a vendedores.

### Rutas
| Método | Ruta | Función |
|--------|------|---------|
| GET | `/clientes` | Lista con búsqueda y paginación |
| GET | `/clientes/create` | Nuevo cliente |
| POST | `/clientes/store` | Guardar |
| GET | `/clientes/edit/{id}` | Editar |
| POST | `/clientes/update/{id}` | Actualizar |
| POST | `/clientes/delete/{id}` | Eliminar (soft delete) |
| GET | `/clientes/show/{id}` | Detalle con ventas recientes |
| POST | `/clientes/reclamar/{id}` | Vendedor reclama cliente |
| GET | `/mis-clientes` | Clientes del vendedor |

### Controlador: `ClientesController`
- `index()` / `misClientes()` — Búsqueda con paginación (15 por página)
- `store()` / `update()` — Validaciones (RFC, email, teléfono)
- `delete()` — Soft delete (activo=0)
- `reclamarCliente()` — Asigna vendedor + notificación
- `show()` — Detalle con últimas 20 ventas

### Modelo: `Cliente`
- `all()` — Solo activos (activo=1)
- `find()` — JOIN con vendedor
- `search()` — Búsqueda con LIKE en múltiples campos + paginación
- `getVentasByCliente()` / `getDevolucionesByCliente()` / `getCotizacionesByCliente()`

### Vistas
- `clientes/index.php`, `clientes/create.php`, `clientes/edit.php`, `clientes/show.php`

### BD: `clientes`, `ventas`, `devoluciones_clientes`, `cotizaciones_clientes`

---

## 10. Módulo de Proveedores

**Propósito**: Gestión de proveedores de materias primas.

### Rutas: CRUD `/proveedores`

### Controlador: `ProveedoresController`
- CRUD estándar

### Modelo: `Proveedor`
- `getMaterialesByProveedor()` — Materiales que suministra
- `getEvaluacionesByProveedor()` — Evaluaciones
- `getSectores()` / `getEstatusList()` — Filtros

### Vistas
- `proveedores/index.php`, `proveedores/create.php`, `proveedores/edit.php`

### BD: `proveedores`, `materiales`, `evaluacion_proveedores`, `devoluciones_proveedores`, `cotizaciones_proveedores`

---

## 11. Módulo de Ventas

**Propósito**: Registrar ventas, calcular comisiones y generar tickets.

### Rutas: CRUD `/ventas`

### Controlador: `VentasController`
- `index()` — Lista (vendedores solo ven las suyas)
- `create()` / `store()` — Crear venta → calcula comisión → genera ticket
- `edit()` / `update()` — Editar
- `delete()` — Eliminar

### Flujo `store()`:
1. Inserta venta
2. Calcula comisión del vendedor (5%)
3. Inserta registro en `comisiones_vendedor`
4. Notifica al vendedor
5. Genera ticket (`Ticket::createFromVenta()`)

### Vistas
- `ventas/index.php`, `ventas/create.php`, `ventas/edit.php`

### BD: `ventas`, `comisiones_vendedor`, `tickets`, `clientes`, `productos`

---

## 12. Módulo de Comisiones / Vendedor

**Propósito**: Gestión de comisiones de vendedores.

### Rutas
| Método | Ruta | Función |
|--------|------|---------|
| GET | `/mis-comisiones` | Comisiones del vendedor |
| GET | `/comisiones` | Admin: todas las comisiones |
| POST | `/comisiones/pagar/{id}` | Marcar como pagada |
| POST | `/notificaciones/marcar-leidas` | Marcar notificaciones leídas |

### Controlador: `VendedorController`
- `comisiones()` — Resumen para el vendedor
- `adminComisiones()` — Filtros por vendedor/estatus
- `pagarComision()` — Admin paga comisión + notifica
- `marcarNotificacionesLeidas()` — Marcar todo leído

### Modelo: `Vendedor`
- `getComisiones()` / `getResumenComisiones()` — Resumen pendiente/pagado
- `getAllComisiones()` — Con filtros
- `getTotalPendienteGlobal()` / `getTotalPagadoGlobal()`

### BD: `comisiones_vendedor`, `notificaciones_vendedor`

---

## 13. Módulo de Tickets

**Propósito**: Visualización y descarga de tickets de venta.

### Rutas
| Método | Ruta | Función |
|--------|------|---------|
| GET | `/tickets/{folio}` | Ver ticket |
| GET | `/tickets/{folio}/pdf` | Descargar PDF |

### Controlador: `TicketsController`
- `show()` — Muestra ticket con datos del cliente, producto y totales
- `pdf()` — Genera PDF con Dompdf
- `renderInvoiceHtml()` — Template HTML para PDF

### Modelo: `Ticket`
- `getByFolio()` — JOIN completo: tickets + ventas + clientes + productos
- `createFromVenta()` — Genera folio (TKT-prefijo) con JSON data
- `cancelar()` — Cambia estatus

### Vistas
- `tickets/show.php`

### BD: `tickets`, `ventas`

---

## 14. Módulo de Contabilidad

**Propósito**: Contabilidad completa con plan de cuentas, pólizas, periodos y balances.

### 14.1 Dashboard Contable
| Ruta | Función |
|------|---------|
| GET `/contabilidad` | Dashboard: total cuentas, pólizas, cargos/abonos del mes |
| GET `/contabilidad/periodos` | Gestión de periodos contables |
| POST `/contabilidad/periodos/cerrar/{id}` | Cerrar periodo |
| POST `/contabilidad/periodos/reabrir/{id}` | Reabrir periodo |

### 14.2 Plan de Cuentas
| Ruta | Función |
|------|---------|
| GET `/contabilidad/plan-cuentas` | Lista con búsqueda |
| GET `/contabilidad/plan-cuentas/create` | Nueva cuenta |
| POST `/contabilidad/plan-cuentas/store` | Guardar (valida código duplicado) |
| GET `/contabilidad/plan-cuentas/edit/{id}` | Editar |
| POST `/contabilidad/plan-cuentas/update/{id}` | Actualizar |
| POST `/contabilidad/plan-cuentas/delete/{id}` | Eliminar (solo sin hijos/movimientos) |

### 14.3 Pólizas
| Ruta | Función |
|------|---------|
| GET `/contabilidad/polizas` | Lista con filtros y paginación |
| GET `/contabilidad/polizas/create` | Nueva póliza |
| POST `/contabilidad/polizas/store` | Guardar (valida cargos = abonos) |
| GET `/contabilidad/polizas/show/{id}` | Detalle con partidas |
| POST `/contabilidad/polizas/cancelar/{id}` | Cancelar (solo periodo abierto) |

### 14.4 Balances y Reportes
| Ruta | Función |
|------|---------|
| GET `/contabilidad/balanza` | Balanza de comprobación |
| GET `/contabilidad/estado-resultados` | Estado de resultados |
| GET `/contabilidad/balance-general` | Balance general |
| GET `/contabilidad/libro-diario` | Libro diario por fecha |
| GET `/contabilidad/libro-mayor/{id}` | Libro mayor por cuenta |

### Controladores
- `ContabilidadController` — Dashboard y periodos
- `PlanCuentasController` — CRUD plan de cuentas
- `PolizasController` — CRUD pólizas + cancelación
- `BalanceController` — Reportes contables

### BD
- `plan_cuentas` — Catálogo de cuentas contables
- `polizas` — Cabecera de pólizas
- `polizas_detalle` — Partidas (cargo/abono por cuenta)
- `periodos_contables` — Periodos mensuales

### Vistas
- `contabilidad/`, `plan_cuentas/`, `polizas/`, `balance/`

---

## 15. Módulo de Calidad

**Propósito**: Inspecciones de calidad y rechazos.

### Rutas
| Ruta | Función |
|------|---------|
| GET `/calidad/inspecciones` | Lista inspecciones |
| GET `/calidad/inspecciones/create` | Nueva inspección |
| POST `/calidad/inspecciones/store` | Guardar |
| GET `/calidad/rechazos` | Lista rechazos |
| GET `/calidad/rechazos/create` | Nuevo rechazo |
| POST `/calidad/rechazos/store` | Guardar |

### Controlador: `CalidadController`
- Inspecciones: CRUD con filtros
- Rechazos: CRUD con filtros

### Modelos: `InspeccionCalidad`, `RechazoCalidad`

### BD: `inspecciones_calidad`, `rechazos_calidad`

### Vistas
- `calidad/inspecciones/`, `calidad/rechazos/`

---

## 16. Módulo de Kardex

**Propósito**: Control de inventarios (movimientos de materiales).

### Rutas
| Ruta | Función |
|------|---------|
| GET `/kardex` | Lista movimientos con filtros |
| GET `/kardex/create` | Nuevo movimiento |
| POST `/kardex/store` | Guardar + actualizar stock |
| GET `/kardex/detalle/{id}` | Historial por material |

### Controlador: `KardexController`
- `store()` — Inserta movimiento + actualiza stock_actual_kg en materiales

### Modelo: `KardexMaterial`
- `getWithMaterial()` — JOIN con materiales + filtros
- `getByMaterial()` — Historial completo de un material

### BD: `kardex_materiales`, `materiales`

### Vistas
- `kardex/index.php`, `kardex/create.php`, `kardex/detalle.php`

---

## 17. Módulo de Incidencias

**Propósito**: Registrar y dar seguimiento a incidencias de producción.

### Rutas
| Ruta | Función |
|------|---------|
| GET `/incidencias` | Lista con filtros |
| GET `/incidencias/create` | Nueva incidencia |
| POST `/incidencias/store` | Guardar + notificar supervisores |
| POST `/incidencias/cerrar/{id}` | Cerrar con acciones correctivas |
| GET `/incidencias/delete/{id}` | Eliminar |

### Controlador: `IncidenciasController`
- `store()` — Inserta + notifica supervisores
- `cerrar()` — Actualiza estatus y acciones correctivas

### Modelo: `IncidenciaProduccion`
- `getWithOrden()` — JOIN con órdenes y productos + filtros

### BD: `incidencias_produccion`

### Vistas
- `incidencias/index.php`, `incidencias/create.php`

---

## 18. Módulo de Mantenimiento

**Propósito**: Registro de mantenimientos, planificación y bitácora de paros.

### Rutas
| Ruta | Función |
|------|---------|
| GET `/mantenimiento` | Lista mantenimientos + pendientes |
| GET `/mantenimiento/create` | Nuevo mantenimiento |
| POST `/mantenimiento/store` | Guardar |
| GET `/mantenimiento/plan` | Plan de mantenimiento |
| POST `/mantenimiento/plan/store` | Programar mantenimiento |
| GET `/mantenimiento/paros` | Bitácora de paros |
| GET `/mantenimiento/paros/create` | Nuevo paro |
| POST `/mantenimiento/paros/store` | Guardar paro |
| GET `/mantenimiento/delete/{id}` | Eliminar mantenimiento |
| GET `/mantenimiento/paros/delete/{id}` | Eliminar paro |

### Controlador: `MantenimientoController`
- `store()` — Actualiza estatus de máquina
- `planStore()` — Programa mantenimiento preventivo
- `paroStore()` — Calcula duración

### Modelo: `MantenimientoMaquina`
- `getWithMaquina()` — JOIN + filtros
- `getPendientes()` — Mantenimientos programados pendientes

### BD: `mantenimientos_maquinas`, `plan_mantenimiento`, `bitacora_paros`, `maquinas`

### Vistas
- `mantenimiento/` (index, create, plan, paros)

---

## 19. Módulo de Notificaciones

**Propósito**: Alertas del sistema y notificaciones por rol.

### Rutas
| Ruta | Función |
|------|---------|
| GET `/notificaciones` | Alertas del sistema |
| POST `/notificaciones/marcar-leidas` | Marcar leídas (vendedor) |
| POST `/notificaciones-operador/marcar-leidas` | Marcar leídas (operador) |
| POST `/notificaciones-supervisor/marcar-leidas` | Marcar leídas (supervisor) |

### Controlador: `NotificacionesController`
- `index()` — Reúne:
  - Materiales con stock bajo
  - Mantenimientos pendientes
  - Incidencias abiertas
  - Resumen: órdenes hoy, máquinas activas, alertas stock
  - Notificaciones del supervisor (solo para supervisores)

### Vistas
- `notificaciones/index.php`

### BD: `notificaciones_vendedor`, `notificaciones_operador`, `notificaciones_supervisor`

---

## 20. Módulo de Usuarios

**Propósito**: Administración de usuarios del sistema.

### Rutas: CRUD `/usuarios`

### Controlador: `UsuariosController`
- CRUD completo (admin/supervisor)
- `store()` — Crea usuario + opcionalmente empleado
- `update()` — Actualiza rol, password, activo
- `delete()` — No permite eliminarse a sí mismo

### Vistas
- `usuarios/index.php`, `usuarios/create.php`, `usuarios/edit.php`

### BD: `usuarios`, `empleados`, `roles`

---

## 21. Módulo de Perfil

**Propósito**: Configuración de perfil para cada tipo de usuario.

### Rutas
| Ruta | Función |
|------|---------|
| GET `/profile` | Ver perfil |
| POST `/profile/update-password` | Cambiar contraseña |
| POST `/profile/update-personal` | Datos personales (empleado) |
| POST `/profile/update-contacto` | Contacto (cliente) |
| POST `/profile/update-cliente` | Datos empresa (cliente) |

### Controlador: `ProfileController`
- `index()` — Muestra datos según el rol
- `updatePassword()` — Verifica contraseña actual antes de cambiar

### Vistas
- `profile/index.php`

---

## 22. Módulo de Cartera / Wallet

**Propósito**: Gestión de pagos y métodos de pago para clientes.

### Rutas
| Ruta | Función |
|------|---------|
| GET `/cartera` | Ver cartera |
| POST `/cartera/tarjetas/agregar` | Agregar tarjeta |
| POST `/cartera/tarjetas/eliminar/{id}` | Eliminar tarjeta |
| POST `/cartera/referencias/generar` | Generar referencia de depósito |
| POST `/cartera/referencias/cancelar/{id}` | Cancelar referencia |

### Controlador: `CarteraController`
- `index()` — Saldo, movimientos, tarjetas, referencias
- `agregarTarjeta()` — Registro con enmascaramiento
- `generarReferencia()` — Código para depósito bancario

### BD: `tarjetas_cliente`, `movimientos_cartera`, `depositos_referencia`

### Vistas
- `cartera/index.php`

---

## 23. Módulo de Catálogo / Tienda Pública

**Propósito**: Catálogo de productos visible para clientes sin autenticación.

### Rutas
| Ruta | Función |
|------|---------|
| GET `/catalogo` | Catálogo con búsqueda y filtros |
| GET `/producto/{id}` | Detalle del producto |

### Controlador: `CatalogoController`
- `index()` — Búsqueda + filtro por familia/línea + paginación
- `show()` — Detalle con productos recomendados

### Vistas
- `catalogo/index.php`, `catalogo/show.php`

---

## 24. Módulo de Carrito / Compras

**Propósito**: Carrito de compras para clientes registrados.

### Rutas
| Ruta | Función |
|------|---------|
| GET `/carrito` | Ver carrito |
| POST `/carrito/agregar` | Agregar producto (valida stock) |
| POST `/carrito/eliminar/{key}` | Quitar producto |
| POST `/carrito/actualizar/{key}` | Cambiar cantidad |
| POST `/carrito/checkout` | Procesar compra |

### Controlador: `CarritoController`
- `checkout()` — Crea pedido → ventas → tickets → limpia carrito
- Carrito en sesión (`$_SESSION['cart']`)

### Vistas
- `carrito/index.php`

### BD: `pedidos`, `pedidos_historial`, `ventas`, `tickets`

---

## 25. Módulo de Facturación Pública

**Propósito**: Portal público para que clientes soliciten facturas electrónicas.

### Rutas
| Ruta | Función |
|------|---------|
| GET `/factura` | Buscar ticket por folio |
| POST `/factura/buscar` | Buscar |
| GET `/factura/solicitar/{folio}` | Formulario de solicitud |
| POST `/factura/solicitar/{folio}` | Enviar solicitud |
| GET `/factura/pdf/{folio}` | Descargar PDF |

### Controlador: `FacturaPublicaController`
- `buscar()` — Busca ticket y valida estatus
- `solicitar()` — Guarda datos fiscales + crea solicitud
- `pdf()` — Genera PDF público

### Vistas
- `factura_publica/buscar.php`, `factura_publica/solicitar.php`

### BD: `tickets`, `solicitudes_factura`

---

## 26. Módulo de Facturas (Admin)

**Propósito**: Administración de solicitudes de factura y contabilización.

### Rutas
| Ruta | Función |
|------|---------|
| GET `/facturas` | Lista facturas emitidas |
| GET `/facturas/solicitudes` | Solicitudes pendientes |
| POST `/facturas/procesar/{id}` | Procesar solicitud (crea factura + IVA) |
| POST `/facturas/rechazar/{id}` | Rechazar solicitud |
| POST `/facturas/contabilizar/{id}` | Crear póliza contable |

### Controlador: `FacturasController`
- `procesar()` — Crea factura con cálculo de IVA
- `contabilizar()` — Crea póliza de ingreso automática

### BD: `facturas`, `solicitudes_factura`, `polizas`, `polizas_detalle`, `plan_cuentas`

### Vistas
- `facturas/index.php`, `facturas/solicitudes.php`

---

## 27. Módulo de Reportes / KPI

**Propósito**: Indicadores y reportes de producción.

### Rutas
| Ruta | Función |
|------|---------|
| GET `/reportes/kpi` | KPIs, OEE, eficiencia operativa |
| GET `/reportes/produccion` | Reporte de producción con consumos |

### Controlador: `ReportesController`
- `kpi()` — Indicadores de calidad, OEE, eficiencia
- `produccion()` — Producción por fecha, consumos, incidencias, scrap

### Vistas
- `reportes/kpi.php`, `reportes/produccion.php`

### BD: `indicadores_kpi`, `indicadores_oee`, `eficiencia_operativa`, `eficiencia_operadores`, `productividad_turnos`, `indicadores_scrap`

---

## 28. Módulo Admin / Horarios

**Propósito**: Gestión de horarios de operadores y control de acceso.

### Rutas
| Ruta | Función |
|------|---------|
| GET `/admin/horarios` | Ver horarios |
| POST `/admin/horarios/guardar` | Guardar horario |
| POST `/admin/horarios/autorizar` | Autorizar acceso extraordinario |

### Controlador: `AdminController`
- `horariosOperador()` — Lista horarios
- `guardarHorario()` — Crea/actualiza horario
- `autorizarAcceso()` — Acceso fuera de horario

### BD: `horarios_operador`, `accesos_extraordinarios`

---

## Roles y Permisos

| Módulo | Admin (1) | Supervisor (3) | Vendedor (4) | Contador (6) | Operador (2) | Cliente (5) |
|--------|-----------|----------------|--------------|--------------|--------------|-------------|
| Dashboard | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ |
| Materiales | ✅ | ✅ | ❌ | ❌ | ✅ | ❌ |
| Productos | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ |
| Recetas | ✅ | ✅ | ❌ | ❌ | ✅ | ❌ |
| Órdenes | ✅ | ✅ | ❌ | ❌ | ✅ | ❌ |
| Máquinas | ✅ | ✅ | ❌ | ❌ | ✅ | ❌ |
| Moldes | ✅ | ✅ | ❌ | ❌ | ✅ | ❌ |
| Clientes | ✅ | ✅ | ✅ | ✅ | ❌ | ❌ |
| Proveedores | ✅ | ✅ | ❌ | ✅ | ❌ | ❌ |
| Ventas | ✅ | ✅ | ✅ | ✅ | ❌ | ❌ |
| Contabilidad | ✅ | ✅ | ❌ | ✅ | ❌ | ❌ |
| Usuarios | ✅ | ✅ | ❌ | ❌ | ❌ | ❌ |
| Calidad | ✅ | ✅ | ❌ | ❌ | ❌ | ❌ |
| Kardex | ✅ | ✅ | ❌ | ❌ | ❌ | ❌ |
| Incidencias | ✅ | ✅ | ❌ | ❌ | ❌ | ❌ |
| Mantenimiento | ✅ | ✅ | ❌ | ❌ | ❌ | ❌ |
| Notificaciones | ✅ | ✅ | ✅ | ✅ | ❌ | ❌ |
| Reportes | ✅ | ✅ | ❌ | ✅ | ❌ | ❌ |
| Facturas | ✅ | ✅ | ❌ | ✅ | ❌ | ❌ |
| Catálogo | ❌ | ❌ | ❌ | ❌ | ❌ | ✅ |
| Carrito | ❌ | ❌ | ❌ | ❌ | ❌ | ✅ |
| Cartera | ❌ | ❌ | ❌ | ❌ | ❌ | ✅ |

---

## Core del Sistema

### Router (`app/Core/Router.php`)
- Registro de rutas GET/POST con parámetros `{id}`
- Dispatch: parsea URI, extrae base path, hace matching con regex
- Resuelve controlador desde namespace `App\Controllers\{Controller}`

### Controller (`app/Core/Controller.php`)
- `view($view, $data)` — Renderiza con layout
- `redirect($path)` — Redirección HTTP
- `json($data)` — Respuesta JSON
- `requireAuth()`, `requireRol()`, `requireAdmin()` — Control de acceso

### Model (`app/Core/Model.php`)
- Active Record: `all()`, `find()`, `create()`, `update()`, `delete()`
- Helpers: `where()`, `count()`, `paginate()`
- Raw SQL: `raw()`, `fetchAll()`, `fetchOne()`

### Database (`app/Core/Database.php`)
- Singleton PDO con utf8mb4
- Prepared statements con named parameters
- Transacciones: `beginTransaction()`, `commit()`, `rollback()`

### View (`app/Core/View.php`)
- `render($view, $data)` — Extrae datos, renderiza con layout
- `renderPartial()` — Sin layout
- Layout: `app/views/layouts/main.php` (header + sidebar + content + footer)
