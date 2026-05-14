# Módulo de Clientes — Análisis Completo

> **Última actualización:** Sprint 3 — Correcciones de seguridad + UX ✅

## 1. Arquitectura del Módulo

El módulo de clientes está distribuido en **8 controladores, 1 modelo, 9 vistas principales y 5 migraciones**.

```
app/
├── Controllers/
│   ├── ClientesController.php    ← CRUD con validación, CSRF, paginación, búsqueda, soft delete, logs
│   ├── HomeController.php        ← Dashboard cliente + mis compras + facturas + vendedor
│   ├── CatalogoController.php    ← Catálogo público de productos (con ordenamiento)
│   ├── ProfileController.php     ← Perfil, cambio contraseña, datos cliente/representante
│   ├── CarteraController.php     ← Cartera/wallet, tarjetas, referencias de depósito
│   └── CarritoController.php     ← Carrito de compras (agregar, eliminar, actualizar cantidad, checkout)
├── Models/
│   └── Cliente.php               ← Soft delete, search + pagination, consultas específicas
├── helpers/
│   └── funciones.php             ← Incluye registrar_log(), truncate() con mb_*
└── views/
    ├── clientes/
    │   ├── index.php             ← Listado con buscador + paginación Bootstrap
    │   ├── create.php            ← Formulario con validación + CSRF + old values
    │   └── edit.php              ← Formulario con validación + CSRF + old values
    ├── home/
    │   ├── cliente.php           ← Portal con pedidos (historial eager-loaded), compras, facturas, vendedor
    │   ├── catalogo.php          ← Catálogo con filtros + ordenamiento (precio/nombre)
    │   ├── carrito.php           ← Carrito con cantidad editable inline + subtotales
    │   ├── cartera.php           ← Cartera con saldo, movimientos, tarjetas, referencias
    │   ├── mis_compras.php       ← Historial completo de compras + facturación
    │   └── producto.php          ← Detalle con indicador "ya está en tu carrito"
    ├── profile/
    │   └── index.php             ← Perfil completo: datos usuario, representante, empresa, contraseña
    └── layouts/
        ├── main.php              ← Flash toasts con json_encode() seguro
        └── sidebar.php           ← Vendedor puede ver enlace "Clientes"
database/
├── migration_clientes.sql            ← id_vendedor + solicitudes_factura
├── migration_softdelete_clientes.sql ← Columna activo (soft delete)
├── migration_log_actividad.sql       ← Tabla de log de actividad
├── migration_cartera.sql             ← Tablas cartera + tarjetas_cliente + depositos_referencia
└── migration_tickets.sql            ← Tabla tickets
```

---

## 2. Funcionalidades Actuales

### 2.1 CRUD de Clientes (ClientesController)

| Método | Ruta | Acceso | Descripción |
|--------|------|--------|-------------|
| `index` | `GET /clientes` | Roles 1, 3, 4 | Listado con buscador + paginación (15 por página) |
| `create` | `GET /clientes/create` | Roles 1, 3, 4 | Formulario para nuevo cliente |
| `store` | `POST /clientes/store` | Roles 1, 3, 4 | Guarda con validación + CSRF + log |
| `edit` | `GET /clientes/edit/{id}` | Roles 1, 3, 4 | Formulario con datos precargados + old values |
| `update` | `POST /clientes/update/{id}` | Roles 1, 3, 4 | Actualiza con validación + CSRF + log |
| `delete` | `POST /clientes/delete/{id}` | Roles 1, 3, 4 | Soft delete (activo=0) + CSRF + log |

**Campos del formulario:** Razón Social, RFC, Sector (datalist con sectores existentes), Ciudad, Estado, Teléfono, Correo.

**Validaciones aplicadas:**
- `razon_social`: requerido, mínimo 3 caracteres
- `rfc`: formato RFC válido (opcional)
- `correo`: email válido (opcional)
- `telefono`: formato teléfono 7-20 dígitos (opcional)

### 2.2 Portal de Autoservicio del Cliente (Rol 5)

El cliente autenticado (rol 5) tiene un ecosistema completo con:

| Funcionalidad | Controlador | Descripción |
|--------------|-------------|-------------|
| **Dashboard** | `HomeController@index` | 4 tarjetas (compras, invertido, facturas, cliente) + pedidos con historial + últimas compras + solicitudes factura + selector vendedor |
| **Mis Compras** | `HomeController@misCompras` | Historial completo de compras con tickets, PDF, solicitar factura |
| **Detalle Compra** | `HomeController@detalleCompra` | Vista individual de cada compra |
| **Catálogo** | `CatalogoController` | Productos con búsqueda, filtros por familia/línea, ordenamiento por precio/nombre, paginación |
| **Detalle Producto** | `CatalogoController@show` | Vista individual con indicador "ya en carrito", stock dinámico, recomendados |
| **Carrito** | `CarritoController` | Agregar, eliminar, cantidad editable inline, checkout con transacción |
| **Perfil** | `ProfileController` | Contraseña (con CSRF), datos personales/empresa, representante |
| **Cartera** | `CarteraController` | Saldo, movimientos, tarjetas (CRUD), referencias de depósito |

### 2.3 Catálogo de Productos (CatalogoController)

| Ruta | Acceso | Descripción |
|------|--------|-------------|
| `GET /catalogo` | Auth | Lista productos donde `publicar_web = 1 OR IS NULL` |

### 2.4 Dashboard de Vendedor (HomeController / Rol 4)

- Tarjeta con total de clientes registrados
- Top 5 clientes por gasto

---

## 3. Estructura de la Base de Datos

### Tabla `clientes`

```sql
clientes (
  id_cliente    INT PRIMARY KEY AUTO_INCREMENT,
  razon_social  VARCHAR(150),
  rfc           VARCHAR(20),
  ciudad        VARCHAR(100),
  estado        VARCHAR(100),
  telefono      VARCHAR(20),
  correo        VARCHAR(120),
  id_vendedor   INT DEFAULT NULL,              -- FK → usuarios
  sector        VARCHAR(100),
  activo        TINYINT(1) NOT NULL DEFAULT 1,  -- Soft delete (migration)
  created_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
)
```

### Tabla `solicitudes_factura`

```sql
solicitudes_factura (
  id_solicitud    INT PRIMARY KEY AUTO_INCREMENT,
  id_cliente      INT NOT NULL,              -- FK → clientes
  id_venta        INT NOT NULL,              -- FK → ventas
  estatus         ENUM('pendiente','procesada') DEFAULT 'pendiente',
  fecha_solicitud TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)
```

### Tabla `log_actividad`

```sql
log_actividad (
  id_log       INT PRIMARY KEY AUTO_INCREMENT,
  id_usuario   INT DEFAULT NULL,             -- FK → usuarios
  accion       VARCHAR(100) NOT NULL,        -- crear, actualizar, eliminar, asignar_vendedor
  entidad      VARCHAR(50) NOT NULL,         -- cliente
  id_entidad   INT DEFAULT NULL,             -- id_cliente
  detalle      TEXT DEFAULT NULL,            -- razón social u otros detalles
  ip           VARCHAR(45) DEFAULT NULL,
  created_at   TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX (entidad, id_entidad),
  INDEX (id_usuario),
  INDEX (created_at)
)
```

### Tablas con FK a `clientes(id_cliente)`

| Tabla | Descripción |
|-------|-------------|
| `ventas` | Ventas realizadas al cliente |
| `devoluciones_clientes` | Devoluciones del cliente |
| `cotizaciones_clientes` | Cotizaciones hechas al cliente |
| `cotizaciones_servicios` | Cotizaciones de servicios |
| `solicitudes_factura` | Solicitudes de factura |
| `cuentas_por_cobrar` | Cuentas por cobrar |
| `ordenes_venta` | Órdenes de venta |
| `ordenes_servicio` | Órdenes de servicio |
| `ordenes_compra_productos` | Órdenes de compra de productos |
| `evaluacion_clientes` | Evaluaciones del cliente |
| `bitacora_ventas` | Bitácora de ventas |
| `bitacora_devoluciones` | Bitácora de devoluciones |

---

## 4. Flujo de Datos

```
GET   /clientes?search=&page=&sort=&order= → ClientesController@index   → Cliente.search() → views/clientes/index.php (paginación + buscador)
GET   /clientes/create                      → ClientesController@create → getSectores()    → views/clientes/create.php
POST  /clientes/store                       → ClientesController@store  → validate() + CSRF + Cliente.create() + registrar_log() → redirect /clientes
GET   /clientes/edit/{id}                   → ClientesController@edit   → Cliente.find($id) → views/clientes/edit.php
POST  /clientes/update/{id}                 → ClientesController@update → validate() + CSRF + Cliente.update() + registrar_log() → redirect /clientes
POST  /clientes/delete/{id}                 → ClientesController@delete → CSRF + Cliente.delete(activo=0) + registrar_log() → redirect /clientes

── Dashboard Cliente ──
GET   /                                       → HomeController@index → clienteDashboard() → views/home/cliente.php (historial eager-loaded, GROUP_CONCAT max_len=100k)
GET   /mis-compras                            → HomeController@misCompras → views/home/mis_compras.php
GET   /mis-compras/{id}                       → HomeController@detalleCompra → views/home/detalle_compra.php
POST  /facturas/request/{id}                  → HomeController@solicitarFactura → CSRF + valida estatus 'completado' + INSERT → redirect /
POST  /facturas/cancelar/{id}                 → HomeController@cancelarFactura  → CSRF + DELETE solicitud → redirect /
POST  /cliente/asignar-vendedor               → HomeController@asignarVendedor  → CSRF + UPDATE + registrar_log() → redirect /

── Catálogo ──
GET   /catalogo?q=&familia=&linea=&sort=&order=&page= → CatalogoController@index → query BD con ordenamiento → views/home/catalogo.php
GET   /producto/{id}                                   → CatalogoController@show  → producto + recomendados + indicador carrito → views/home/producto.php

── Carrito ──
GET   /carrito                              → CarritoController@index     → lista items con precio desde sesión carrito.php
POST  /carrito/agregar                      → CarritoController@agregar   → CSRF + redirect /catalogo
POST  /carrito/actualizar/{key}             → CarritoController@actualizar → CSRF + valida stock + actualiza cantidad
POST  /carrito/eliminar/{key}               → CarritoController@eliminar  → CSRF
POST  /carrito/checkout                     → CarritoController@checkout  → CSRF + transacción + redirect /mis-compras

── Perfil ──
GET   /profile                              → ProfileController@index    → datos usuario + formularios
POST  /profile/update-password              → ProfileController@updatePassword → CSRF + valida + cambia
POST  /profile/update-personal              → ProfileController@updatePersonal → CSRF (empleados)
POST  /profile/update-contacto              → ProfileController@updateContacto → CSRF (representante)
POST  /profile/update-cliente               → ProfileController@updateCliente  → CSRF (datos empresa)

── Cartera ──
GET   /cartera                              → CarteraController@index → saldo + movimientos + tarjetas + referencias
POST  /cartera/tarjetas/agregar             → CarteraController@agregarTarjeta → CSRF
POST  /cartera/tarjetas/eliminar/{id}       → CarteraController@eliminarTarjeta → CSRF
POST  /cartera/referencias/generar          → CarteraController@generarReferencia → CSRF
POST  /cartera/referencias/cancelar/{id}    → CarteraController@cancelarReferencia → CSRF
```

---

## 5. Navegación y Permisos

### Sidebar — Enlace "Clientes"

| Rol | ¿Ve el enlace? | ¿Puede acceder al CRUD? |
|-----|----------------|------------------------|
| 1 - Administrador | Sí | Sí |
| 3 - Supervisor | Sí | Sí |
| 4 - Vendedor | Sí | Sí ✅ — Corregido |
| 2 - Operador | No | No |
| 5 - Cliente | No (ve: Mi Panel, Catálogo, Perfil) | No |

### Sidebar — Rol Cliente (3 enlaces)

1. **Mi Panel** → `/`
2. **Catálogo** → `/catalogo`
3. **Perfil** → `/profile`

---

## 6. Problemas Resueltos y Pendientes

### ✅ RESUELTOS (Sprint 1 — Correcciones críticas)

| # | Problema | Solución |
|---|----------|----------|
| 1 | Sin validación de datos | Validación con `validate()` en `store()` y `update()` |
| 2 | Sin protección CSRF | `csrf_token()` + `verify_csrf()` en todos los formularios |
| 3 | DELETE vía GET | Cambiado a POST con CSRF |
| 4 | Vendedor no podía acceder | `checkClientesAccess()` permite roles [1, 3, 4] |

### ✅ RESUELTOS (Sprint 2 — Mejoras funcionales)

| # | Problema | Solución |
|---|----------|----------|
| 5 | Sin paginación | Método `search()` con paginación, 15 por página |
| 6 | Sin búsqueda/filtro | Buscador SQL por nombre, RFC, ciudad, correo, teléfono |
| 7 | `SUM()` sin COALESCE | `COALESCE(SUM(...), 0)` en query del dashboard cliente |
| 8 | Sin validación factura | Verifica `estatus = 'completado'` antes de permitir |
| 9 | Sin soft delete | Columna `activo`, `delete()` hace UPDATE activo=0 |
| 10 | Sin logging | Función `registrar_log()`, tabla `log_actividad`, integrado en CRUD |

### ✅ RESUELTOS (Sprint 3 — Seguridad + UX)

| # | Problema | Dónde | Solución |
|---|----------|-------|----------|
| 11 | Sin CSRF en cambio de contraseña | `ProfileController@updatePassword` | Agregado `verify_csrf()` + input oculto en vista |
| 12 | Sin CSRF en cartera | `CarteraController` (4 métodos) | `verify_csrf()` en agregarTarjeta, eliminarTarjeta, generarReferencia, cancelarReferencia |
| 13 | Toast/flash con HTML se rompe | `main.php:49` | Cambiado de string con `safe_string()` a `json_encode()` con flags JSON_UNESCAPED_UNICODE |
| 14 | Gráfica stock no funciona | `charts.js:104` | `type: 'horizontalBar'` → `type: 'bar'` (eliminado en Chart.js v4) |
| 15 | Sin modificar cantidad en carrito | `carrito.php` | Input editable con botones +/- + método `actualizar()` + ruta |
| 16 | Precio en carrito de DB, no del carrito | `carrito.php:36` | Cambiado a `$item['precio_unitario']` (precio del carrito) |
| 17 | `truncate()` corta multi-byte | `funciones.php:69` | `strlen`/`substr` → `mb_strlen`/`mb_substr` |
| 18 | N+1 queries en pedidos | `cliente.php:126` | Eager-load de historial con 1 sola query IN() |
| 19 | Stock no se muestra en max del input | `producto.php:38` | `max="999"` → `max="<?= $producto['stock_actual'] ?: 999 ?>"` |
| 20 | Redirect post-checkout a `/` | `CarritoController@checkout` | Cambiado a `/mis-compras` |
| 21 | Agregar carrito redirige a `/` | `CarritoController@agregar` | Cambiado a `/catalogo` |
| 22 | Sin ordenamiento en catálogo | `CatalogoController` + `catalogo.php` | Parámetros sort/order (nombre, precio_venta, ASC/DESC) |
| 23 | pageTitle "Dashboard" para cliente | `HomeController@index` | Cambiado a "Mi Panel" para rol cliente |
| 24 | Sin indicador "ya en tu carrito" | `producto.php` | Alerta informativa con cantidad si ya está en el carrito |
| 25 | Sin enlace a pedidos en Mis Compras | `mis_compras.php` | Botón "Mis Pedidos" → `/` (dashboard) |
| 26 | GROUP_CONCAT limitado a 1024 | `HomeController` | `SET SESSION group_concat_max_len = 100000` antes de queries |

### 📋 PENDIENTES (Sprint 4+)

| # | Problema | Impacto |
|---|----------|---------|
| 27 | Sin vista de detalle individual de cliente | No se puede ver perfil completo con historial |
| 28 | Sin exportar/importar clientes | No hay forma de migrar datos masivamente |
| 29 | Dashboard vendedor no filtra por vendedor | Muestra ventas de todos los vendedores |
| 30 | Sin notificaciones al asignar vendedor | El vendedor no sabe que le asignaron un cliente |
| 31 | Sin código_cliente interno | Dificulta identificación en documentos |
| 32 | Sin carga de logo del cliente | No hay imagen asociada al cliente |
| 33 | Sin correo de bienvenida | El cliente no recibe credenciales automáticamente |

---

## 7. Estado de Implementación

### ✅ COMPLETADO (Sprint 1 — Correcciones críticas)

| # | Mejora | Archivos |
|---|--------|----------|
| 1 | Validación server-side | `ClientesController`, `create.php`, `edit.php` |
| 2 | DELETE a POST con CSRF | `ClientesController`, `routes.php`, `index.php` |
| 3 | CSRF en formularios CRUD | `ClientesController`, `create.php`, `edit.php` |
| 4 | Permisos vendedor (rol 4) | `ClientesController`, `sidebar.php` |

### ✅ COMPLETADO (Sprint 2 — Mejoras funcionales)

| # | Mejora | Archivos |
|---|--------|----------|
| 5 | Paginación | `Cliente.php` (search), `index.php` |
| 6 | Búsqueda/filtro | `Cliente.php` (search), `index.php` |
| 7 | COALESCE en SUM | `HomeController.php` |
| 8 | Validación estatus factura | `HomeController@solicitarFactura` |
| 9 | Soft delete | `migration_softdelete_clientes.sql`, `Cliente.php` |
| 10 | Logging | `migration_log_actividad.sql`, `funciones.php` |

### ✅ COMPLETADO (Sprint 3 — Seguridad + UX)

| # | Mejora | Archivos |
|---|--------|----------|
| 11 | CSRF en cambio contraseña | `ProfileController.php`, `profile/index.php` |
| 12 | CSRF en cartera | `CarteraController.php` |
| 13 | Flash JSON seguro | `main.php` |
| 14 | Fix gráfica stock | `charts.js` |
| 15 | Cantidad editable carrito | `CarritoController.php` (nuevo método), `carrito.php`, `routes.php` |
| 16 | Precio desde sesión | `carrito.php` |
| 17 | truncate() multi-byte | `funciones.php` |
| 18 | N+1 queries historial | `HomeController.php`, `cliente.php` |
| 19 | Stock dinámico en input | `producto.php` |
| 20 | Post-checkout → /mis-compras | `CarritoController.php` |
| 21 | Post-agregar → /catalogo | `CarritoController.php` |
| 22 | Ordenamiento catálogo | `CatalogoController.php`, `catalogo.php` |
| 23 | pageTitle "Mi Panel" | `HomeController.php` |
| 24 | Indicador carrito | `producto.php` |
| 25 | Enlace pedidos | `mis_compras.php` |
| 26 | GROUP_CONCAT limit | `HomeController.php` |

---

## 8. Archivos Modificados (Sprint 3)

| Archivo | Cambios realizados |
|---------|-------------------|
| `app/Controllers/ProfileController.php` | ✅ CSRF en `updatePassword()` + ruteado |
| `app/Controllers/CarteraController.php` | ✅ CSRF en 4 métodos (agregar/eliminar tarjeta, generar/cancelar referencia) |
| `app/Controllers/CarritoController.php` | ✅ Nuevo método `actualizar()`; redirects a /catalogo y /mis-compras |
| `app/Controllers/HomeController.php` | ✅ Eager-load historial pedidos, GROUP_CONCAT max_len, pageTitle "Mi Panel" |
| `app/Controllers/CatalogoController.php` | ✅ Parámetros sort/order con whitelist |
| `app/helpers/funciones.php` | ✅ `truncate()` con mb_* |
| `app/config/routes.php` | ✅ Ruta POST actualizar carrito |
| `app/views/layouts/main.php` | ✅ Flash toast con `json_encode()` |
| `app/views/home/carrito.php` | ✅ Precio desde sesión, cantidad editable |
| `app/views/home/cliente.php` | ✅ N+1 eliminado, usa `$historial_pedidos` |
| `app/views/home/producto.php` | ✅ Stock dinámico en max, indicador "ya en carrito" |
| `app/views/home/catalogo.php` | ✅ Selects de ordenamiento |
| `app/views/home/mis_compras.php` | ✅ Botón "Mis Pedidos" |
| `app/views/profile/index.php` | ✅ CSRF input en formulario contraseña |
| `public/assets/js/charts.js` | ✅ `horizontalBar` → `bar` |

### Archivos del ecosistema cliente (no modificados en Sprint 3)

| Archivo | Propósito |
|---------|-----------|
| `database/migration_cartera.sql` | Tablas `movimientos_cartera`, `tarjetas_cliente`, `depositos_referencia` |
| `database/migration_tickets.sql` | Tabla `tickets` para comprobantes |

---

## 9. Diagrama de Relaciones del Módulo

```
                    ┌─────────────────────┐
                    │    USUARIOS (id)     │
                    │  (vendedores, admin) │
                    └──────────┬──────────┘
                               │ id_vendedor (FK)
                    ┌──────────▼──────────┐
                    │      CLIENTES       │
                    │  (id_cliente)       │
                    └──┬──┬──┬──┬──┬──┬──┘
          ┌────────────┘  │  │  │  │  │  └────────────┐
          │               │  │  │  │  │               │
   ┌──────▼──────┐  ┌────▼──▼──▼──▼──▼──┐  ┌─────────▼─────────┐
   │  VENTAS     │  │  SOLICITUDES      │  │  CATÁLOGO          │
   │  (id_cliente)│  │  FACTURA          │  │  (productos pub.)  │
   └─────────────┘  │  (id_cliente,     │  └───────────────────┘
                    │   id_venta)       │
                    └───────────────────┘
```

---

## 10. Checklist de Implementación

### Sprint 1 — Correcciones críticas ✅
- [x] Validación de datos en store/update
- [x] CSRF en todos los formularios CRUD
- [x] DELETE cambiar a POST
- [x] Corregir permiso de vendedor

### Sprint 2 — Mejoras funcionales ✅
- [x] Paginación en listado
- [x] Buscador/filtro SQL
- [x] COALESCE en SUM de dashboard
- [x] Validación server-side en solicitudes factura
- [x] Soft delete
- [x] Log de actividad

### Sprint 3 — Seguridad + UX ✅
- [x] CSRF en cambio de contraseña
- [x] CSRF en toda la cartera (4 endpoints)
- [x] Flash JSON seguro (json_encode en vez de safe_string)
- [x] Fix gráfica stock (horizontalBar → bar)
- [x] Cantidad editable en carrito (input + botón actualizar)
- [x] Precio carrito desde sesión (no desde DB)
- [x] truncate() con soporte multi-byte (mb_*)
- [x] Eager-load historial pedidos (eliminar N+1)
- [x] Stock dinámico en input (max = stock_actual)
- [x] Post-checkout redirect a /mis-compras
- [x] Post-agregar redirect a /catalogo
- [x] Ordenamiento en catálogo (precio, nombre, ASC/DESC)
- [x] pageTitle "Mi Panel" para clientes
- [x] Indicador "ya está en tu carrito" en detalle
- [x] Enlace "Mis Pedidos" en Mis Compras
- [x] GROUP_CONCAT con max_len suficiente (100k)

### Sprint 4+ — Pendiente
- [ ] Vista de detalle individual de cliente
- [ ] Exportar clientes a Excel/CSV
- [ ] Importar clientes desde Excel
- [ ] Filtrar dashboard vendedor por vendedor asignado
- [ ] Notificar al vendedor cuando un cliente se lo asigna
- [ ] Campo código_cliente interno
- [ ] Subir logo del cliente
- [ ] Correo de bienvenida al crear cliente
