# Plasti Frus — Sistema de Gestión para Fábrica de Plásticos

**Plasti Frus** es un sistema ERP web desarrollado en PHP nativo con arquitectura MVC para la gestión integral de una fábrica de inyección de plásticos. Cubre producción, calidad, ventas, CRM, inventarios, contabilidad, mantenimiento, KPIs, portal de cliente, y más — todo en un solo sistema modular basado en roles.

---

## Stack Tecnológico

| Componente | Tecnología |
|---|---|
| **Backend** | PHP 8.3 — MVC propio (Router → Controller → Service → Model → View) |
| **Base de datos** | MySQL / MariaDB — 140+ tablas normalizadas |
| **Frontend** | Bootstrap 5.3, DataTables 1.13, Chart.js 4, Bootstrap Icons |
| **Testing** | PHPUnit 11 — 63 tests funcionales (127 assertions) |
| **Dependencias** | Composer — PHPMailer, Dompdf, PhpSpreadsheet, vlucas/phpdotenv |
| **ORM** | Active Record simple con PDO parametrizado (sin inyección SQL) |
| **Autenticación** | Sesiones PHP + bcrypt + recuperación de contraseña por email |

---

## Inicio Rápido

### En Windows con Docker

Para mostrar el sistema en otra PC Windows sin instalar PHP, Composer, Apache ni MariaDB manualmente, usa la guía [DOCKER_WINDOWS.md](DOCKER_WINDOWS.md).

```powershell
docker compose up -d --build
```

Luego abre `http://localhost:8000`.

### Instalación local

```bash
# 1. Clonar e instalar dependencias
cd /var/www/plasti_frus
composer install

# 2. Configurar base de datos
# Ejecutar el esquema SQL completo y migraciones/correcciones necesarias
mysql -u root -p < database/schema.sql
php bin/plasti migrate
mysql -u root -p fabrica_plasticos < database/corrections_2026_05_27_audit_phase2.sql

# 3. Configurar entorno
cp .env.example .env
# Editar DB_HOST, DB_NAME, DB_USER, DB_PASS, APP_URL, y configuración SMTP (MAIL_*)

# 4. Cargar datos de demostración
php seed.php

# 5. Iniciar servidor de desarrollo
php -S localhost:8000 -t public/ public/router.php

# 6. Acceder al sistema
# Abrir http://localhost:8000 en el navegador
# Usuario: admin / Contraseña: password
```

---

## Usuarios de Demostración

| Usuario | Contraseña | Rol |
|---|---|---|
| admin | password | Administrador |
| supervisor | password | Supervisor |
| operador1 | password | Operador |
| vendedor1 | password | Vendedor |
| cliente1 | password | Cliente (portal) |
| contador | password | Contador |

---

## Ejecutar Pruebas

```bash
# Todas las pruebas
vendor/bin/phpunit

# Solo pruebas unitarias
vendor/bin/phpunit --testsuite=Unit

# Solo pruebas funcionales
vendor/bin/phpunit --testsuite=Feature
```

### Cobertura de pruebas

| Suite | Tests | Descripción |
|---|---|---|
| `tests/Feature/LoginTest` | 6 | Login válido, inválido, contraseña incorrecta, sesión, logout |
| `tests/Feature/RegisterTest` | 4 | Registro cliente, duplicados, hash de contraseña |
| `tests/Feature/InventoryTest` | 4 | Crear material/producto, cálculos stock, detección bajo inventario |
| `tests/Feature/ProductionOrderTest` | 5 | Crear orden, FK inválida, cambio de estatus, cantidad límite |
| `tests/Unit/Helpers/ValidatorsTest` | 16 | Validación de email, RFC, teléfono, rangos, fechas, etc. |
| `tests/Unit/Helpers/FuncionesTest` | 12 | format_money, safe_string, csrf_token, time_ago, etc. |
| `tests/Unit/Core/PaginationTest` | 7 | Páginas única/múltiple/central/última, renderizado |
| `tests/Unit/Services/*` | 9 | Existencia de clases AuditService, ExportService, MailService, AuthService |

---

## Cargar Datos de Demostración

```bash
# Carga inicial (no duplica datos existentes)
php seed.php

# Reiniciar datos demo (limpia tablas antes de insertar)
php seed.php --fresh
```

Los seeders generan datos realistas y coherentes para demostraciones en vivo, incluyendo productos, órdenes en varios estados, movimientos de inventario, ventas y registros de auditoría.

---

## Arquitectura del Proyecto

```
plasti_frus/
├── app/
│   ├── Config/              # Configuración de la app, DB, permisos
│   ├── Core/                # Framework MVC: Database, Router, Controller, Model, View, Pagination
│   ├── Exceptions/          # Manejador global de excepciones
│   ├── Helpers/             # funciones.php (URLs, sesión, formato, permisos, auditoría) + validators.php
│   ├── Http/
│   │   ├── Controllers/     # 54+ controladores organizados por módulo
│   │   │   ├── Accounting/  # Contabilidad completa (balance, pólizas, flujo, presupuestos)
│   │   │   ├── Api/         # Endpoints REST para dashboard, clientes, productos, ventas
│   │   │   ├── Auth/        # Autenticación, registro, recuperación de contraseña
│   │   │   ├── Crm/         # Clientes, oportunidades (pipeline Kanban), actividades, mensajes
│   │   │   ├── Dashboard/   # Paneles por rol (admin, supervisor, operador, vendedor, cliente, contador)
│   │   │   ├── Inventory/   # Kardex de materiales
│   │   │   ├── Maintenance/ # Mantenimiento de máquinas, paros, plan de mantenimiento
│   │   │   ├── Portal/      # Catálogo público, carrito, wishlist, tickets, cartera
│   │   │   ├── Production/  # Materiales, productos, recetas, órdenes, máquinas, moldes
│   │   │   ├── Purchasing/  # Proveedores
│   │   │   ├── Quality/     # Inspecciones y rechazos de calidad
│   │   │   ├── Sales/       # Ventas, tickets, comisiones, pipeline vendedor
│   │   │   └── System/      # Usuarios, perfil, reportes KPI/producción, auditoría, incidencias
│   │   └── Requests/        # Form requests con validación
│   ├── Models/              # 15+ modelos Active Record
│   ├── Repositories/        # Capa de repositorios para consultas especializadas
│   └── Services/            # Servicios: Auth, Audit, Export (PDF/Excel), Mail, Kardex
├── bootstrap/               # Punto de entrada de la aplicación (autoload, constantes, entorno)
├── config/                  # Archivos de configuración (app, database, permissions)
├── database/
│   ├── migrations/          # Migraciones PHP incrementales (12 aplicadas)
│   └── seeds/               # Seeders de datos de demostración
├── public/
│   ├── index.php            # Punto de entrada
│   ├── router.php           # Router para servidor built-in de PHP
│   └── assets/
│       ├── css/             # main.css, dashboard.css, enhanced.css (UI profesional)
│       ├── js/              # main.js, charts.js, validations.js
│       └── images/
├── resources/views/         # ~70 vistas organizadas por módulo
│   ├── auth/                # Login, registro, recuperación de contraseña
│   ├── layouts/             # main.php, header.php, sidebar.php, footer.php
│   ├── partials/            # Componentes reutilizables (filter_bar, pagination, export_buttons)
│   └── home/                # Dashboards por rol (admin, supervisor, operador, vendedor, etc.)
├── routes/                  # 17 archivos de rutas organizados por módulo
├── storage/
│   ├── cache/               # Caché de configuración
│   ├── logs/                # Logs de aplicación
│   └── exports/             # Exportaciones temporales
├── tests/                   # PHPUnit tests (63 tests funcionales + unitarios)
├── seed.php                 # CLI para cargar datos de demostración
├── composer.json
└── phpunit.xml.dist
```

---

## Roles y Permisos

| ID | Rol | Descripción |
|---|---|---|
| 1 | **Administrador** | Acceso total al sistema |
| 2 | **Operador** | Producción: órdenes, máquinas, inspecciones, incidencias, bitácora |
| 3 | **Supervisor** | Administración de producción, calidad, inventarios |
| 4 | **Vendedor** | Pipeline de ventas, CRM, comisiones, reportes |
| 5 | **Cliente** | Portal: catálogo, compras, facturas, tickets, cartera |
| 6 | **Contador** | Contabilidad completa: pólizas, balances, impuestos |

Cada módulo tiene control de acceso granular vía `puede_acceder()` y `requireRol()`.

---

## Funcionalidades Principales

### Producción
- Gestión de materiales con control de stock y punto de reorden
- Catálogo de productos con precios, pesos, dimensiones y familias
- Recetas de producción con versiones y detalle de materiales
- Órdenes de producción con seguimiento de estatus (pendiente → en_proceso → completada)
- Asignación de máquinas, moldes y operadores por turno

### Calidad
- Inspecciones de calidad con parámetros configurables
- Registro de rechazos con causas y análisis
- Control de mermas y scrap por orden

### CRM y Ventas
- Pipeline Kanban de oportunidades comerciales
- Gestión completa de clientes con historial de interacciones
- Comisiones por vendedor con gráficas y pagos
- Agenda de actividades y mensajería interna
- Portal de cliente con catálogo, carrito de compras y facturación

### Inventarios
- Kardex de materiales con entradas y salidas
- Alertas de stock bajo
- Historial completo de movimientos

### Contabilidad
- Plan de cuentas completo
- Pólizas contables (diario, ingresos, egresos)
- Balance General, Estado de Resultados, Balanza de Comprobación
- Libro Diario y Libro Mayor
- Flujo de Efectivo y Presupuestos
- Cierre contable por periodos
- Exportación de reportes contables

### Sistema
- Gestión de usuarios con roles
- Perfil de usuario con cambio de contraseña y datos personales
- Reportes KPI y de producción con gráficas
- Bitácora de turno con paros de máquina
- Incidencias de producción
- **Auditoría**: Registro detallado de todas las acciones del sistema
- **Exportación**: Reportes en PDF y Excel con diseño profesional

---

## Funcionalidades Técnicas Destacadas

### Seguridad
- Contraseñas hasheadas con `password_hash()` (bcrypt)
- Consultas parametrizadas con PDO (100% libre de inyección SQL)
- Autenticación por sesión con regeneración de ID
- Control de acceso granular por roles (`requireRol()`)
- Protección CSRF con tokens criptográficos
- Escape de salida con `safe_string()` (htmlspecialchars)
- Validación de horario laboral para operadores con acceso extraordinario

### Framework MVC Propio
- Router con soporte de parámetros `{id}` y verbos HTTP
- Sistema de layouts con header, sidebar y footer intercambiables
- Helpers para sesión, formato, permisos y notificaciones
- Paginación reutilizable en todas las vistas de listado

### Frontend Profesional
- Diseño responsive con Bootstrap 5.3
- Sidebar con glassmorphism y animaciones suaves
- Tarjetas KPI con gradientes, sombras y micro-interacciones
- Tablas con hover effects, estados vacíos visuales y badges de estatus
- Toast notifications con barra de progreso y auto-dismiss
- DataTables con búsqueda en vivo, ordenamiento y español
- Chart.js para gráficas dinámicas (producción, OEE, tendencias, stock)
- Loading spinner y transiciones de página

### Infraestructura de Pruebas
- TestCase base con transacciones y rollback automático
- Pruebas funcionales que ejercitan la base de datos real
- Pruebas unitarias para helpers, validadores y servicios

---

## Módulos y Rutas

### Autenticación
| Método | Ruta | Descripción |
|---|---|---|
| GET | `/login` | Iniciar sesión |
| POST | `/login` | Validar credenciales |
| GET | `/register` | Registro de usuario |
| POST | `/register` | Crear cuenta |
| GET | `/logout` | Cerrar sesión |
| GET | `/olvide-contrasena` | Solicitar recuperación de contraseña |
| POST | `/olvide-contrasena` | Enviar correo de recuperación |
| GET | `/restablecer-contrasena/{token}` | Formulario de nueva contraseña |
| POST | `/restablecer-contrasena` | Guardar nueva contraseña |

### Dashboard
| Ruta | Rol |
|---|---|
| `/` | Dashboard según rol autenticado |
| `/operador/dashboard` | Dashboard específico para operadores |

### Producción
| Ruta | Descripción |
|---|---|
| `/materiales` | CRUD de materias primas |
| `/productos` | CRUD de productos terminados |
| `/recetas` | CRUD de recetas de producción |
| `/ordenes` | CRUD de órdenes de producción |
| `/maquinas` | CRUD de máquinas de inyección |
| `/moldes` | CRUD de moldes |

### CRM y Ventas
| Ruta | Descripción |
|---|---|
| `/clientes` | CRUD de clientes |
| `/proveedores` | CRUD de proveedores |
| `/ventas` | CRUD de ventas |
| `/pipeline` | Pipeline Kanban de oportunidades |
| `/oportunidades` | CRUD de oportunidades comerciales |
| `/comisiones` | Gestión de comisiones |
| `/mensajes` | Mensajería interna |

### Portal de Cliente
| Ruta | Descripción |
|---|---|
| `/catalogo` | Catálogo público de productos |
| `/carrito` | Carrito de compras |
| `/mis-pedidos` | Historial de pedidos |
| `/mis-compras` | Historial de compras |
| `/cartera` | Cartera/wallet digital |
| `/tickets` | Tickets de soporte |
| `/wishlist` | Lista de favoritos |

### Contabilidad (44 rutas)
| Ruta | Descripción |
|---|---|
| `/contabilidad` | Dashboard contable |
| `/contabilidad/plan-cuentas` | Catálogo de cuentas |
| `/contabilidad/polizas` | Pólizas contables |
| `/contabilidad/periodos` | Periodos contables |
| `/contabilidad/balance-general` | Balance General |
| `/contabilidad/estado-resultados` | Estado de Resultados |
| `/contabilidad/balanza` | Balanza de comprobación |
| `/contabilidad/flujo-efectivo` | Flujo de Efectivo |
| `/contabilidad/presupuestos` | Presupuestos |
| `/contabilidad/cierres` | Cierre contable |

### Sistema
| Ruta | Descripción |
|---|---|
| `/admin/logs` | Visor de auditoría del sistema |
| `/admin/horarios` | Gestión de horarios de operadores |
| `/usuarios` | CRUD de usuarios |
| `/profile` | Perfil y configuración personal |
| `/reportes/kpi` | KPIs de producción |
| `/reportes/produccion` | Reportes de producción |
| `/incidencias` | Incidencias de producción |
| `/calidad/inspecciones` | Inspecciones de calidad |

---

## Estructura de la Base de Datos

El sistema cuenta con **140+ tablas** organizadas en módulos:

| Módulo | Tablas principales |
|---|---|
| **Producción** | materiales, productos, recetas_cabecera/detalle, ordenes_cabecera, ordenes_merma, seguimiento_ordenes, maquinas, moldes |
| **Calidad** | inspecciones_calidad, rechazos_calidad, parametros_calidad, pruebas_laboratorio |
| **CRM** | clientes, oportunidades, actividades, interacciones, mensajes |
| **Ventas** | ventas, comisiones, tickets, cotizaciones |
| **Inventarios** | kardex_materiales, inventario_productos_terminados, alertas_stock |
| **Compras** | proveedores, ordenes_compra, cotizaciones_proveedores |
| **Contabilidad** | plan_cuentas, polizas, periodos_contables, cuentas_por_cobrar/pagar, presupuestos |
| **Mantenimiento** | mantenimientos_maquinas, plan_mantenimiento, bitacora_paros, refacciones |
| **Portal** | carrito_compras, pedidos, tickets_soporte, wishlist, cartera, direcciones_envio |
| **Sistema** | usuarios, roles, empleados, audit_log, password_resets, horarios_operador, notificaciones |
| **Trazabilidad** | trazabilidad_operadores, maquinas, moldes, materiales, calidad, embarque, venta |

---

## Mantenimiento

### Agregar un nuevo módulo
1. Crear migración en `database/migrations/`
2. Crear el Modelo en `app/Models/`
3. Crear el Repositorio/Servicio en `app/Repositories/` o `app/Services/`
4. Crear el Controlador en `app/Http/Controllers/<Modulo>/`
5. Agregar rutas en `routes/<modulo>.php`
6. Crear vistas en `resources/views/<modulo>/`
7. Registrar permisos en `puede_acceder()` en `app/Helpers/funciones.php`
8. Agregar enlace en sidebar (`resources/views/layouts/sidebar.php`)
9. Ejecutar `composer run lint` para verificar sintaxis
10. Ejecutar `vendor/bin/phpunit` para verificar que no haya regresión

### Comandos útiles
```bash
# Verificar sintaxis de todos los archivos PHP
composer run lint

# Ejecutar pruebas
vendor/bin/phpunit

# Cargar datos demo
php seed.php

# Servidor de desarrollo
php -S localhost:8000 -t public/ public/router.php
```

---

## Licencia

Proyecto desarrollado para concurso tecnológico. Todos los derechos reservados.
