# Plasti Frus — Sistema de Gestión de Fábrica de Plásticos

Sistema web MVC para la gestión integral de una fábrica de inyección de plásticos. Cubre producción, calidad, ventas, clientes, mantenimiento, inventarios, KPIs y más.

## Stack Tecnológico

| Componente | Tecnología |
|------------|-----------|
| **Backend** | PHP 8.3.6 |
| **Base de datos** | MySQL / MariaDB |
| **Frontend** | Bootstrap 5.3 + DataTables 1.13 + Chart.js + Bootstrap Icons |
| **Dependencias** | Composer (vlucas/phpdotenv) |
| **Arquitectura** | MVC propio (Router → Controller → Model → View + Layout) |
| **ORM** | Active Record simple con PDO parametrizado |
| **Autenticación** | Sesiones PHP + password_hash/bcrypt |

---

## Inicio Rápido

```bash
# 1. Clonar repositorio
cd /var/www/plasti_frus

# 2. Instalar dependencias
composer install

# 3. Crear base de datos y cargar esquema
mysql -u root -p < database/schema.sql

# 4. Ejecutar migraciones de roles y clientes
mysql -u root -p fabrica_plasticos < database/migration_roles.sql
mysql -u root -p fabrica_plasticos < database/migration_clientes.sql

# 5. Configurar .env
cp .env.example .env
# Editar DB_HOST, DB_NAME, DB_USER, DB_PASS, APP_URL

# 6. Iniciar servidor de desarrollo
php -S localhost:8000 -t public
```

> **Nota:** El esquema SQL incluye la estructura de ~100 tablas pero **no incluye datos semilla**. Debes insertar los roles, usuarios y datos de prueba manualmente o mediante un script de seed.

---

## Arquitectura del Proyecto

```
plasti_frus/
├── app/
│   ├── config/          # Configuración de la app, rutas, DB
│   ├── Controllers/     # 20 controladores
│   ├── Core/            # Framework: Router, Controller, Model, Database, View
│   ├── helpers/         # funciones.php + validators.php
│   ├── Models/          # 15 modelos
│   └── views/           # ~55 vistas organizadas por módulo
│       ├── auth/
│       ├── layouts/     # main.php, header.php, sidebar.php, footer.php
│       └── partials/    # Componentes reutilizables (filter_bar.php)
├── database/
│   ├── schema.sql              # Estructura completa de la BD
│   ├── migration_roles.sql     # Roles Vendedor (4) y Cliente (5)
│   └── migration_clientes.sql  # id_vendedor + solicitudes_factura
├── public/
│   ├── index.php        # Punto de entrada
│   ├── .htaccess        # Rewrite rules (Apache)
│   ├── router.php       # Router para servidor built-in de PHP
│   └── assets/
│       ├── css/         # main.css, dashboard.css
│       ├── js/          # main.js, charts.js, validations.js
│       └── images/
└── vendor/              # Dependencias Composer
```

---

## Roles y Permisos

| ID | Rol | Descripción | Módulos accesibles |
|----|-----|-------------|-------------------|
| 1 | **Administrador** | Acceso total a todos los módulos del sistema | dashboard, materiales, productos, recetas, ordenes, maquinas, moldes, clientes, proveedores, ventas, kpi, reportes, usuarios, calidad, kardex, incidencias, mantenimiento, notificaciones |
| 2 | **Operador** | Personal de producción | dashboard, productos, recetas, ordenes, maquinas, moldes |
| 3 | **Supervisor** | Supervisión de producción y calidad | Admin + dashboard, materiales, productos, recetas, ordenes, maquinas, moldes, clientes, proveedores, ventas, kpi, reportes, calidad, kardex, incidencias, mantenimiento, notificaciones |
| 4 | **Vendedor** | Ventas y atención a clientes | dashboard, productos, clientes, ventas |
| 5 | **Cliente** | Portal de cliente (compras, facturas, catálogo) | dashboard, catalogo |

### Sistema de permisos en `requireRol()`:

```php
requireRol(1) → solo Admin
requireRol(2) → solo Operador
requireRol(3) → Admin + Supervisor
requireRol(4) → Admin + Vendedor
requireRol(5) → solo Cliente
requireAuth() → cualquier usuario autenticado (roles 1-5)
```

---

## Estructura de la Base de Datos (~100 tablas)

### Tablas principales (con CRUD implementado)

| Tabla | Descripción | Controlador |
|-------|-------------|-------------|
| `roles` | Roles de usuario (Admin, Operador, Supervisor, Vendedor, Cliente) | — |
| `usuarios` | Usuarios del sistema (con password_hash) | UsuariosController |
| `empleados` | Empleados de la fábrica | — |
| `clientes` | Clientes | ClientesController |
| `proveedores` | Proveedores | ProveedoresController |
| `materiales` | Materias primas con stock y punto de reorden | MaterialesController |
| `productos` | Productos terminados | ProductosController |
| `maquinas` | Máquinas de inyección | MaquinasController |
| `moldes` | Moldes con cavidades | MoldesController |
| `recetas_cabecera` | Recetas de producción (versiones) | RecetasController |
| `recetas_detalle` | Materiales componentes de cada receta | — |
| `ordenes_cabecera` | Órdenes de producción | OrdenesController |
| `ordenes_merma` | Mermas por orden | — |
| `seguimiento_ordenes` | Historial de estatus de órdenes | — |
| `ventas` | Ventas a clientes | VentasController |
| `incidencias_produccion` | Incidentes en producción | IncidenciasController |
| `inspecciones_calidad` | Inspecciones de calidad | CalidadController |
| `rechazos_calidad` | Rechazos de calidad | CalidadController |
| `kardex_materiales` | Movimientos de inventario | KardexController |
| `mantenimientos_maquinas` | Mantenimientos realizados | MantenimientoController |
| `plan_mantenimiento` | Mantenimientos programados | MantenimientoController |
| `bitacora_paros` | Registro de paros de máquina | MantenimientoController |
| `solicitudes_factura` | Solicitudes de factura de clientes | HomeController |

### Tablas adicionales en el esquema (no implementadas en CRUD, disponibles para futuros módulos)

**Costos y KPIs:** `costo_produccion`, `indicadores_kpi`, `indicadores_oee`, `eficiencia_operativa`, `eficiencia_operadores`, `productividad_turnos`

**Logística:** `embarques`, `ordenes_embarque`, `bitacora_embarque`

**Compras:** `cotizaciones_proveedores`, `ordenes_compra`, `ordenes_compra_materiales`, `ordenes_compra_refacciones`, `ordenes_compra_productos`

**Comercial:** `cotizaciones_clientes`, `cotizaciones_productos`, `cotizaciones_servicios`, `ordenes_venta`, `ordenes_servicio`

**Facturación:** `facturas`, `cuentas_por_cobrar`, `cuentas_por_pagar`

**Control de calidad:** `parametros_calidad`, `parametros_procesos`, `control_temperatura`, `control_presion`, `control_tiempo`, `pruebas_laboratorio`, `liberacion_produccion`

**Mantenimiento:** `calibraciones_maquinas`, `mantenimientos_moldes`, `checklist_mantenimiento`, `checklist_calibracion`, `refacciones_maquinas`

**Producción:** `consumo_material_por_orden`, `consumo_energia_por_orden`, `consumo_agua_por_orden`, `control_pesados_materiales`, `solicitud_material`, `surtido_materiales`, `scrap_reciclado`, `indicadores_scrap`, `planeacion_produccion`

**Recursos humanos:** `capacitaciones_empleados`, `permisos_empleados`, `vacaciones_empleados`, `ausencias_empleados`, `accesos_sistema`, `evaluacion_empleados`

**Evaluaciones:** `evaluacion_clientes`, `evaluacion_proveedores`, `evaluacion_maquinas`

**Inventarios:** `inventario_productos_terminados`, `ubicacion_rack`, `historial_ubicacion`, `auditoria_inventarios`, `historial_cambios_inventario`, `alerta_stock_materiales`

**Devoluciones:** `devoluciones_clientes`, `devoluciones_proveedores`

**Trazabilidad:** `trazabilidad_operadores`, `trazabilidad_maquinas`, `trazabilidad_moldes`, `trazabilidad_materiales`, `trazabilidad_calidad`, `trazabilidad_embarque`, `trazabilidad_venta`, `trazabilidad_devolucion`

**Bitácoras:** `bitacora_calibraciones`, `bitacora_mantenimientos`, `bitacora_produccion`, `bitacora_calidad`, `bitacora_ventas`, `bitacora_devoluciones`

**Configuración:** `parametros_configuracion`, `cedes`, `turno_produccion`, `asignar_operador`, `checklist_arranque_maquina`, `checklist_cierre_maquina`

---

## Rutas del Sistema

### Autenticación
| Método | Ruta | Controlador |
|--------|------|-------------|
| GET | `/login` | AuthController@showLogin |
| POST | `/login` | AuthController@login |
| GET | `/register` | AuthController@showRegister |
| POST | `/register` | AuthController@register |
| GET | `/logout` | AuthController@logout |

### Dashboard (por rol)
| Ruta | Admin | Supervisor | Operador | Vendedor | Cliente |
|------|-------|------------|----------|----------|---------|
| `/` | Dashboard admin | Dashboard supervisor | Dashboard operador | Dashboard vendedor | Panel cliente |

### Módulos de producción
| Ruta | Controlador | Acceso |
|------|-------------|--------|
| `/materiales` | MaterialesController | Rol 3+ |
| `/productos` | ProductosController | Auth |
| `/recetas` | RecetasController | Auth |
| `/ordenes` | OrdenesController | Auth |
| `/maquinas` | MaquinasController | Auth |
| `/moldes` | MoldesController | Auth |

### Módulos comerciales
| Ruta | Controlador | Acceso |
|------|-------------|--------|
| `/clientes` | ClientesController | Rol 3+ |
| `/proveedores` | ProveedoresController | Rol 3+ |
| `/ventas` | VentasController | Rol 3+ |

### Módulos de calidad y control
| Ruta | Controlador | Acceso |
|------|-------------|--------|
| `/calidad/inspecciones` | CalidadController | Rol 3+ |
| `/calidad/rechazos` | CalidadController | Rol 3+ |
| `/kardex` | KardexController | Rol 3+ |
| `/incidencias` | IncidenciasController | Rol 3+ |
| `/mantenimiento` | MantenimientoController | Rol 3+ |
| `/mantenimiento/paros` | MantenimientoController | Rol 3+ |
| `/notificaciones` | NotificacionesController | Rol 3+ |

### Módulos de administración
| Ruta | Controlador | Acceso |
|------|-------------|--------|
| `/usuarios` | UsuariosController | Admin |
| `/reportes/kpi` | ReportesController | Rol 3+ |
| `/reportes/produccion` | ReportesController | Rol 3+ |
| `/profile` | ProfileController | Auth |

### Portal de cliente
| Ruta | Controlador | Acceso |
|------|-------------|--------|
| `/catalogo` | CatalogoController | Auth |
| `/facturas/request/{id}` | HomeController@solicitarFactura | Cliente |
| `/facturas/cancelar/{id}` | HomeController@cancelarFactura | Cliente |
| `/cliente/asignar-vendedor` | HomeController@asignarVendedor | Cliente |

---

## Funcionalidades por Dashboard

### Administrador
- 8 tarjetas de estadísticas con gradientes (materiales, productos, órdenes, clientes, proveedores, máquinas, usuarios)
- Gráfica de barras (Chart.js) con producción planificada
- Tabla de últimas 5 órdenes con datos completos
- Tabla de materiales con stock bajo (resaltados en rojo)
- Accesos rápidos a KPIs, Reportes, Calidad, Mantenimiento

### Supervisor
- 6 tarjetas de estadísticas (materiales, productos, órdenes, clientes, proveedores, máquinas)
- Tabla de últimas 8 órdenes
- Tabla de alertas de stock bajo

### Operador
- 3 tarjetas de estadísticas (órdenes hoy, productos, máquinas)
- Tabla de órdenes del día con turno
- Tabla de máquinas activas
- Accesos rápidos (nueva orden, mis órdenes, productos, recetas)

### Vendedor
- 3 tarjetas de estadísticas (clientes, productos, ventas del mes)
- Monto total facturado en el mes
- Tabla de ventas recientes (10 últimas)
- Top 5 clientes por gasto
- Accesos directos a clientes y ventas

### Cliente
- 4 tarjetas de estadísticas (compras, total invertido, facturas solicitadas, datos del cliente)
- Tabla de compras con botón "Solicitar Factura" en cada completada
- Sección de solicitudes de factura con estatus (pendiente/procesada)
- Selector de vendedor asignado
- Datos de la cuenta (razón social, RFC, teléfono, correo, ubicación)

---

## Características Técnicas

### Framework MVC propio
- **Router** con soporte de parámetros `{id}` en URLs
- **Controller** base con `requireAuth()`, `requireRol()`, helpers para GET/POST params
- **Model** con Active Record básico (`all()`, `find()`, `create()`, `update()`, `delete()`, `where()`)
- **View** con sistema de layouts (main.php → header, sidebar, content, footer)

### Frontend
- Bootstrap 5.3 con tema oscuro personalizado
- **DataTables 1.13** con búsqueda en vivo, ordenamiento y paginación (configurado en español)
- **Chart.js** para gráficas en dashboard admin
- **Bootstrap Icons** en toda la interfaz
- **Toast notifications** con auto-cierre (4s)
- **CSS con gradientes** en sidebar, tarjetas de estadísticas, login
- Sidebar colapsable con secciones separadas por rol
- Filtros por fecha en listados (partial reutilizable `filter_bar.php`)
- **Ticket imprimible** estilo (
) con código único de facturación
- **Portal público de facturación** en `/factura` (sin autenticación)
- **Panel admin de facturas** para procesar/rechazar solicitudes
- Diseño responsive

### Seguridad
- Contraseñas hasheadas con `password_hash()` (bcrypt)
- Consultas parametrizadas (PDO prepared statements)
- Autenticación por sesión
- Control de acceso por roles (`requireRol()`)
- Escape de salida con `safe_string()` (htmlspecialchars)
- CSRF token disponible (`csrf_token()`, `verify_csrf()`)

---

## Mantenimiento

### Agregar un nuevo módulo
1. Crear migración SQL para las tablas necesarias
2. Crear el Modelo en `app/Models/`
3. Crear el Controlador en `app/Controllers/`
4. Agregar rutas en `app/config/routes.php`
5. Crear vistas en `app/views/<modulo>/`
6. Agregar permisos en `puede_acceder()` en `app/helpers/funciones.php`
7. Agregar enlace en `app/views/layouts/sidebar.php`
8. Ejecutar `find . -name "*.php" -not -path './vendor/*' -exec php -l {} \;` para verificar sintaxis

### Agregar un nuevo rol
1. Insertar en tabla `roles`
2. Agregar `case` en `requireRol()` en `app/Core/Controller.php`
3. Agregar permisos en `puede_acceder()` en `app/helpers/funciones.php`
4. Agregar función helper (`es_nuevorol()`)
5. Agregar dashboard en `HomeController` y vista en `app/views/home/`

---

## Notas de Desarrollo

- Servidor: `php -S localhost:8000 -t public`
- PHP 8.3 requiere extensión `php8.3-mysql`
- Las sesiones se almacenan en `/var/lib/php/sessions` (requiere sticky bit)
- Después de modificar archivos PHP el servidor debe reiniciarse
- Los filtros por fecha usan GET params y se procesan en el controlador
- DataTables se auto-inicializa en tablas con clase `datatable`; usar clase `no-sort` en columnas sin ordenamiento
- Los flash messages se serializan como JSON via `set_flash()` y JS los procesa como toasts
