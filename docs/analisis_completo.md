# Análisis Completo del Proyecto — Plasti Frus

> **Fecha:** Mayo 2026
> **Propósito:** Documentar el estado actual del sistema y la implementación de tickets con información, código único para facturación externa y panel de administración de facturas.
>
> **Estado:** ✅ Tickets + Portal facturación + Panel admin implementados. ⏳ Método de pago pendiente.

---

## 1. Arquitectura General del Sistema

### Stack Tecnológico

| Componente | Tecnología |
|------------|-----------|
| Backend | PHP 8.3.6 — MVC propio |
| Base de datos | MySQL / MariaDB — 88 tablas |
| Frontend | Bootstrap 5.3 + DataTables 1.13 + Chart.js + Bootstrap Icons |
| ORM | Active Record simple con PDO parametrizado |
| Autenticación | Sesiones PHP + password_hash/bcrypt |
| Dependencias | Composer (vlucas/phpdotenv) |

### Estructura del Proyecto

```
plasti_frus/
├── app/
│   ├── config/          (3 archivos: app.php, database.php, routes.php)
│   ├── Controllers/     (20 controladores)
│   ├── Core/            (5 clases: Router, Controller, Model, Database, View)
│   ├── helpers/         (2 archivos: 23 funciones + 15 validadores)
│   ├── Models/          (15 modelos)
│   └── views/           (61 vistas en 19 carpetas temáticas)
├── database/
│   ├── schema.sql       (88 tablas)
│   └── migration_*.sql  (3 migraciones)
├── public/
│   ├── index.php
│   ├── .htaccess
│   ├── router.php
│   └── assets/
│       ├── css/         (2 archivos)
│       ├── js/          (3 archivos)
│       └── images/      (logo.png)
└── vendor/              (Composer)
```

### Roles del Sistema (5 roles)

| ID | Rol | Helper | Módulos clave |
|----|-----|--------|---------------|
| 1 | Administrador | `es_admin()` | Todo el sistema |
| 2 | Operador | `es_operador()` | Producción, máquinas, moldes |
| 3 | Supervisor | `es_supervisor()` | Admin + calidad, mantenimiento |
| 4 | Vendedor | `es_vendedor()` | Clientes, ventas, productos |
| 5 | Cliente | `es_cliente()` | Portal propio, catálogo |

---

## 2. Estado Actual por Módulo

### ✅ Módulos Completos / Funcionales

| Módulo | Controlador | Modelo | Vistas | Estado |
|--------|-------------|--------|--------|--------|
| Auth / Login | AuthController | User | 2 | Login, registro, logout funcional |
| Materiales | MaterialesController | Material | 3 | CRUD, stock bajo, tipos |
| Productos | ProductosController | Producto | 4 | CRUD, familias, líneas, colores |
| Recetas | RecetasController | RecetaCabe | 3 | CRUD con detalle de materiales |
| Órdenes | OrdenesController | OrdenCabe | 3 | CRUD, seguimiento, mermas |
| Máquinas | MaquinasController | Maquina | 3 | CRUD, OEE, consumos, mantenimientos |
| Moldes | MoldesController | Molde | 3 | CRUD, ciclos, mantenimientos |
| Clientes | ClientesController | Cliente | 3 | CRUD, soft delete, búsqueda, paginación |
| Proveedores | ProveedoresController | Proveedor | 3 | CRUD |
| Usuarios | UsuariosController | User | 3 | CRUD (admin only) |
| Incidencias | IncidenciasController | IncidenciaProduccion | 2 | CRUD, cerrar |
| Kardex | KardexController | KardexMaterial | 3 | CRUD, detalle por material |
| Mantenimiento | MantenimientoController | MantenimientoMaquina | 5 | CRUD, plan, paros |
| Calidad | CalidadController | InspeccionCalidad, RechazoCalidad | 4 | Inspecciones, rechazos |
| Reportes | ReportesController | — | 2 | KPIs, producción |
| Dashboard vendedor | HomeController | — | 1 | Ventas mes, top clientes |
| Dashboard cliente | HomeController | — | 1 | Compras, facturas, vendedor |
| Perfil | ProfileController | — | 1 | Cambio contraseña |
| Catálogo | CatalogoController | — | 1 | Productos públicos |

### 🟡 Módulos Parciales / Con Carencias

| Módulo | Problemas |
|--------|-----------|
| **Ventas** | CRUD sin validación, sin control de stock, sin folio, sin método de pago, sin descuentos/impuestos. Venta de 1 solo producto por registro (sin detalle/carrito). |
| **Facturación** | Tabla `facturas` existe pero NO se usa. `solicitudes_factura` inserta solicitudes del cliente pero nadie las procesa (falta panel admin). Sin generación de CFDI/comprobante. |
| **Notificaciones** | Vista existe pero sin controlador funcional (no envía notificaciones reales). |

### ❌ Módulos No Implementados (tablas en esquema, sin código)

| Grupo | Tablas sin implementar |
|-------|----------------------|
| Costos | `costo_produccion`, `indicadores_kpi`, `indicadores_oee`, `eficiencia_operativa`, `eficiencia_operadores`, `productividad_turnos` |
| Compras | `ordenes_compra`, `ordenes_compra_materiales`, `ordenes_compra_refacciones`, `ordenes_compra_productos` |
| Cotizaciones | `cotizaciones_clientes`, `cotizaciones_productos`, `cotizaciones_servicios`, `cotizaciones_proveedores` |
| Embarques | `embarques`, `ordenes_embarque`, `bitacora_embarque` |
| Cuentas | `cuentas_por_cobrar`, `cuentas_por_pagar` |
| RH | `capacitaciones_empleados`, `permisos_empleados`, `vacaciones_empleados`, `ausencias_empleados`, `evaluacion_empleados` |
| Trazabilidad | 8 tablas de trazabilidad (operadores, máquinas, moldes, materiales, calidad, embarque, venta, devolución) |
| Inventario PT | `inventario_productos_terminados`, `ubicacion_rack`, `historial_ubicacion`, `auditoria_inventarios` |
| Control proceso | `control_temperatura`, `control_presion`, `control_tiempo`, `parametros_procesos` |
| Scrap | `scrap_reciclado`, `indicadores_scrap` |
| Evaluaciones | `evaluacion_clientes`, `evaluacion_proveedores`, `evaluacion_maquinas` |
| RH adicional | `capacitaciones_empleados`, `permisos_empleados`, `vacaciones_empleados`, `ausencias_empleados`, `accesos_sistema` |

---

## 3. Análisis Detallado del Módulo de Ventas

### Tabla `ventas` (estructura actual)

```sql
CREATE TABLE ventas (
    id_venta INT PRIMARY KEY AUTO_INCREMENT,
    id_cliente INT,
    id_producto INT,
    cantidad_vendida DECIMAL(10,2),
    precio_unitario DECIMAL(10,2),
    moneda VARCHAR(10) DEFAULT 'MXN',
    condiciones_pago TEXT,        -- Texto libre: "Crédito 30 días", "Contado"
    fecha_venta DATE,
    estatus VARCHAR(50),          -- 'completado', 'pendiente', etc.
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (id_cliente) REFERENCES clientes(id_cliente),
    FOREIGN KEY (id_producto) REFERENCES productos(id_producto)
);
```

### Flujo actual de venta → factura

```
Vendedor crea venta → INSERT en ventas
    ↓
Cliente ve la venta en su portal "Mis Compras"
    ↓
Cliente hace clic "Solicitar Factura" → INSERT en solicitudes_factura (pendiente)
    ↓
[NADIE procesa la solicitud — no hay panel admin]
    ↓
Tabla facturas EXISTE pero NUNCA se insertan registros
```

### Carencias Identificadas en Ventas

| # | Carencia | Impacto |
|---|----------|---------|
| 1 | **Sin método de pago** — `condiciones_pago` es texto libre | No hay control sobre formas de pago |
| 2 | **Sin folio** — se usa `id_venta` auto-incremental | Sin identificador único para tickets/facturas |
| 3 | **Sin control de impuestos** — no hay IVA, subtotal, total | Factura incompleta |
| 4 | **Un solo producto por venta** — sin tabla detalle | No se puede vender múltiples productos |
| 5 | **Sin descuento** — no hay campo en tabla | Sin capacidad de aplicar descuentos |
| 6 | **Sin actualización de stock** — al vender no descuenta inventario | El stock queda desfasado |
| 7 | **Sin validación** — store() recibe datos sin validar | Datos inconsistentes |
| 8 | **Sin generación de factura** — solicitudes sin procesar | El cliente solicita pero nunca recibe |

---

## 4. Requerimientos a Implementar

### A. Método de Pago

**Qué se necesita:**
- Catálogo de métodos de pago (Efectivo, Transferencia, Tarjeta, Crédito, Cheque)
- Seleccionar método de pago al crear/editar una venta
- Asociar el método de pago a cada venta

**Impacto en BD:**
```sql
-- Nueva tabla
CREATE TABLE metodos_pago (
    id_metodo_pago INT PRIMARY KEY AUTO_INCREMENT,
    nombre VARCHAR(100) NOT NULL,
    descripcion TEXT,
    activo TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Modificar ventas
ALTER TABLE ventas ADD COLUMN id_metodo_pago INT AFTER condiciones_pago;
ALTER TABLE ventas ADD FOREIGN KEY (id_metodo_pago) REFERENCES metodos_pago(id_metodo_pago);
```

**Impacto en código:**
| Archivo | Cambio |
|---------|--------|
| `database/migration_metodos_pago.sql` | Crear tabla + datos iniciales |
| `VentasController@create` | Cargar métodos de pago |
| `VentasController@edit` | Cargar métodos de pago |
| `VentasController@store` | Guardar id_metodo_pago |
| `VentasController@update` | Guardar id_metodo_pago |
| `views/ventas/create.php` | Select de método de pago |
| `views/ventas/edit.php` | Select de método de pago |
| `views/ventas/index.php` | Columna método de pago |

### B. Tickets con Información

**Qué se necesita:**
- Generar un ticket (comprobante) por cada venta
- El ticket debe incluir: folio único, datos del cliente, producto, cantidad, precio, total, método de pago, fecha
- Vista para imprimir/ver el ticket
- El ticket debe tener un **código único** que servirá para la facturación externa

**Impacto en BD:**
```sql
CREATE TABLE tickets (
    id_ticket INT PRIMARY KEY AUTO_INCREMENT,
    id_venta INT NOT NULL,
    folio_unico VARCHAR(20) NOT NULL UNIQUE,  -- Ej: TKT-20260513-A1B2C3
    fecha_emision DATETIME DEFAULT CURRENT_TIMESTAMP,
    datos_json JSON,                          -- Snapshot de datos al momento de la venta
    estatus VARCHAR(20) DEFAULT 'emitido',    -- emitido, cancelado
    FOREIGN KEY (id_venta) REFERENCES ventas(id_venta),
    INDEX idx_folio (folio_unico)
);
```

**Impacto en código:**
| Archivo | Cambio |
|---------|--------|
| `database/migration_tickets.sql` | Crear tabla tickets |
| `app/Controllers/TicketsController.php` | Nuevo controlador |
| `app/helpers/funciones.php` | Actualizar `generate_folio()` para soportar 'TKT' |
| `VentasController@store` | Generar ticket automáticamente al crear venta |
| `views/ventas/index.php` | Botón "Ver Ticket" en cada venta |
| `views/tickets/show.php` | Vista del ticket (imprimible) |
| `routes.php` | `GET /tickets/{folio}` |
| `views/home/cliente.php` | Mostrar tickets del cliente |

**Datos del ticket (contenido):**
```
══════════════════════════════
     PLÁSTI FRUS
     Ticket de Venta
══════════════════════════════
Folio: TKT-20260513-A1B2C3
Fecha: 13/05/2026 14:30
──────────────────────────────
Cliente: Juan Pérez López
RFC: JUPL890101XXX
──────────────────────────────
Producto: Caja Plástica 40x30
Cantidad: 50
P. Unitario: $25.50
Total: $1,275.00
──────────────────────────────
Método de pago: Transferencia
──────────────────────────────
Código de facturación:
      A1B2C3D4E5
══════════════════════════════
```

### C. Código Único + Portal de Facturación Externa

**Qué se necesita:**
- El ticket tiene un **código único** (`folio_unico`)
- Página pública (SIN autenticación) donde el cliente ingresa su código
- El sistema busca la venta asociada al código
- Muestra los datos de la venta y permite al cliente ingresar datos fiscales
- Genera/almacena la solicitud de factura

**Impacto en código:**

| Archivo | Cambio |
|---------|--------|
| `app/Controllers/FacturaPublicaController.php` | Nuevo controlador (público) |
| `views/factura_publica/buscar.php` | Formulario para ingresar código |
| `views/factura_publica/formulario.php` | Datos de venta + formulario fiscal |
| `views/factura_publica/confirmacion.php` | Confirmación de solicitud |
| `routes.php` | 3 rutas nuevas GET/POST |

**Flujo del portal externo:**

```
CLIENTE                          SISTEMA
  │                                │
  ├─ Accede a /factura             │
  │                                │
  ├─ Ingresa código: A1B2C3D4E5   │
  │───────────────────────────────>│
  │                                ├─ Busca ticket por código
  │                                ├─ Obtiene venta + datos cliente
  │                                │
  │<───────────────────────────────┤
  │  Muestra:                      │
  │  - Datos de venta              │
  │  - Formulario datos fiscales   │
  │    (RFC, razón social, CP,     │
  │     régimen fiscal, uso CFDI)  │
  │                                │
  ├─ Completa datos y envía        │
  │───────────────────────────────>│
  │                                ├─ Guarda en solicitudes_factura
  │                                ├─ Genera registro en facturas
  │                                ├─ Cambia estatus ticket
  │                                │
  │<───────────────────────────────┤
  │  Factura solicitada ✓          │
```

**Datos fiscales adicionales que debe capturar el formulario:**
- RFC (ya existe en clientes, pero se pide confirmar)
- Razón Social (ya existe, confirmar)
- Código Postal (NUEVO — no existe en tabla clientes)
- Régimen Fiscal (NUEVO — no existe)
- Uso CFDI (NUEVO — catálogo SAT)
- Correo electrónico fiscal (NUEVO — para envío de factura)

**Migración necesaria:**
```sql
ALTER TABLE clientes ADD COLUMN codigo_postal VARCHAR(10) AFTER estado;
ALTER TABLE clientes ADD COLUMN regimen_fiscal VARCHAR(10) AFTER codigo_postal;
ALTER TABLE clientes ADD COLUMN uso_cfdi VARCHAR(10) AFTER regimen_fiscal;
ALTER TABLE clientes ADD COLUMN correo_fiscal VARCHAR(100) AFTER correo;
```

---

## 5. Plan de Implementación

### Fase 1 — Método de Pago (Estimación: 2-3 horas)

| Paso | Acción | Archivos |
|------|--------|----------|
| 1.1 | Crear migración `metodos_pago` con datos iniciales (Efectivo, Transferencia, Tarjeta débito, Tarjeta crédito, Cheque, Otro) | `database/migration_metodos_pago.sql` |
| 1.2 | Ejecutar migración y ALTER TABLE ventas | Bash |
| 1.3 | Modificar `VentasController` para cargar/guardar método de pago | `VentasController.php` |
| 1.4 | Agregar select de método de pago en formularios create/edit | `views/ventas/create.php`, `edit.php` |
| 1.5 | Agregar columna en listado de ventas | `views/ventas/index.php` |
| 1.6 | Verificar sintaxis y probar | `php -l` |

### Fase 2 — Tickets (Estimación: 4-6 horas)

| Paso | Acción | Archivos |
|------|--------|----------|
| 2.1 | Crear migración tabla `tickets` | `database/migration_tickets.sql` |
| 2.2 | Ejecutar migración | Bash |
| 2.3 | Crear modelo `Ticket` con métodos: `getByFolio()`, `getByVenta()`, `ultimoFolio()` | `app/Models/Ticket.php` |
| 2.4 | Crear `TicketsController` con: `show($folio)`, `pdf($id)` | `app/Controllers/TicketsController.php` |
| 2.5 | En `VentasController@store`: generar ticket automáticamente al crear venta con `generate_folio('TKT')` | `VentasController.php` |
| 2.6 | Crear vista de ticket `show.php` (imprimible, con estilos) | `views/tickets/show.php` |
| 2.7 | Agregar botón "Ver Ticket" en `ventas/index.php` y `home/cliente.php` | vistas correspondientes |
| 2.8 | Registrar rutas en `routes.php` | `routes.php` |
| 2.9 | Verificar sintaxis | `php -l` |

### Fase 3 — Portal de Facturación Externa (Estimación: 6-8 horas)

| Paso | Acción | Archivos |
|------|--------|----------|
| 3.1 | Crear migración para nuevos campos fiscales en clientes | `database/migration_facturacion.sql` |
| 3.2 | Ejecutar migración | Bash |
| 3.3 | Crear `FacturaPublicaController` con métodos: `buscarForm()`, `buscar()` (POST), `solicitarForm($folio)`, `solicitar()` (POST), `confirmacion()` | `app/Controllers/FacturaPublicaController.php` |
| 3.4 | Crear vistas: `buscar.php`, `formulario.php`, `confirmacion.php` | `views/factura_publica/` |
| 3.5 | Integrar con `solicitudes_factura` + `facturas` | Lógica en controlador |
| 3.6 | Agregar validación de datos fiscales (RFC, régimen, uso CFDI) | `FacturaPublicaController@store` |
| 3.7 | Enviar notificación al admin de nueva solicitud de factura | Integrar con `registrar_log()` |
| 3.8 | Registrar rutas públicas (sin autenticación) | `routes.php` |
| 3.9 | Verificar sintaxis | `php -l` |

### Fase 4 — Panel de Administración de Facturas (Estimación: 3-4 horas)

| Paso | Acción | Archivos |
|------|--------|----------|
| 4.1 | Crear `FacturasController` con: `solicitudes()`, `procesar($id)`, `rechazar($id)`, `historial()` | `app/Controllers/FacturasController.php` |
| 4.2 | Crear vistas de administración de facturas | `views/facturas/` |
| 4.3 | Al procesar: cambiar estatus a 'procesada' + insertar en `facturas` + notificar al cliente | Lógica |
| 4.4 | Registrar rutas (solo admin/supervisor) | `routes.php` |
| 4.5 | Agregar enlace en sidebar | `sidebar.php` |
| 4.6 | Verificar sintaxis | `php -l` |

---

## 6. Diagrama de Arquitectura Propuesta

```
                    ┌─────────────────────┐
                    │   metodos_pago       │  ← NUEVO
                    │   (catálogo)         │
                    └──────────┬──────────┘
                               │
┌──────────┐     ┌────────────▼───────────┐     ┌──────────────┐
│ clientes │────>│        ventas           │────>│   tickets    │ ← NUEVO
│ (RFC,    │     │ (id_metodo_pago, folio,│     │ (folio_unico,│
│  razón   │     │  subtotal, iva, total) │     │  codigo_qr)  │
│  social) │     └──────┬─────────────────┘     └──────┬───────┘
└──────────┘            │                              │
                        │                              │ folio_unico
                        │                              ▼
                        │                    ┌─────────────────────┐
                        │                    │  Portal Facturación │ ← NUEVO
                        │                    │  /factura           │
                        │                    │  (ingresa código)   │
                        │                    └──────────┬──────────┘
                        │                               │
                        ▼                               ▼
              ┌──────────────────┐          ┌───────────────────┐
              │ solicitudes_fact │          │   facturas        │
              │ (pendiente/      │          │ (ya existe, hoy   │
              │  procesada)      │          │  sin usar)        │
              └────────┬─────────┘          └────────┬──────────┘
                       │                             │
                       ▼                             ▼
              ┌───────────────────────────────────────────┐
              │        Panel Admin Facturas               │ ← NUEVO
              │  (ver solicitudes, procesar, rechazar)    │
              └───────────────────────────────────────────┘
```

---

## 7. Resumen de Nuevos Archivos

### Controladores nuevos (3)
| Controlador | Métodos |
|-------------|---------|
| `TicketsController` | `show($folio)`, `pdf($id)` |
| `FacturaPublicaController` | `buscarForm()`, `buscar()`, `solicitarForm($folio)`, `solicitar()` |
| `FacturasController` | `solicitudes()`, `procesar($id)`, `rechazar($id)` |

### Modelos nuevos (1)
| Modelo | Métodos clave |
|--------|--------------|
| `Ticket` | `getByFolio()`, `getByVenta()`, `createFromVenta()` |

### Vistas nuevas (6-8)
| Vista | Propósito |
|-------|-----------|
| `tickets/show.php` | Ticket imprimible |
| `factura_publica/buscar.php` | Ingreso de código |
| `factura_publica/formulario.php` | Datos fiscales |
| `factura_publica/confirmacion.php` | Confirmación |
| `facturas/solicitudes.php` | Panel admin: lista solicitudes |
| `facturas/detalle.php` | Detalle de solicitud + procesar |

### Migraciones nuevas (3)
| Migración | Contenido |
|-----------|-----------|
| `migration_metodos_pago.sql` | Tabla metodos_pago + datos iniciales + FK en ventas |
| `migration_tickets.sql` | Tabla tickets |
| `migration_facturacion.sql` | Campos fiscales en clientes (codigo_postal, regimen_fiscal, uso_cfdi, correo_fiscal) |

### Rutas nuevas (8-10)
| Método | Ruta | Acceso | Propósito |
|--------|------|--------|-----------|
| GET | `/factura` | Público | Formulario buscar código |
| POST | `/factura/buscar` | Público | Buscar venta por código |
| GET | `/factura/solicitar/{folio}` | Público | Formulario datos fiscales |
| POST | `/factura/solicitar/{folio}` | Público | Enviar solicitud |
| GET | `/tickets/{folio}` | Auth | Ver ticket |
| GET | `/facturas/solicitudes` | Admin/Sup | Lista solicitudes pendientes |
| POST | `/facturas/procesar/{id}` | Admin/Sup | Procesar solicitud |
| POST | `/facturas/rechazar/{id}` | Admin/Sup | Rechazar solicitud |

---

## 8. Archivos Existentes a Modificar

| Archivo | Cambios |
|---------|---------|
| `app/Controllers/VentasController.php` | Agregar método de pago, generar ticket al store, validación |
| `app/views/ventas/create.php` | Select método de pago |
| `app/views/ventas/edit.php` | Select método de pago |
| `app/views/ventas/index.php` | Columna método de pago + botón ticket |
| `app/views/home/cliente.php` | Mostrar tickets en portal, enlace a facturación |
| `app/config/routes.php` | 8-10 rutas nuevas |
| `app/views/layouts/sidebar.php` | Enlace a panel facturas (admin/sup) |
| `app/helpers/funciones.php` | Actualizar `generate_folio()` para 'TKT' |
| `database/schema.sql` | (Opcional) Actualizar con nuevas tablas para referencia |

---

## 9. Prioridades Recomendadas

```
FASE 1 ─── Método de Pago (2-3 hrs)
  └── Base para tickets y facturación

FASE 2 ─── Tickets (4-6 hrs)
  └── Genera el código único necesario para Fase 3

FASE 3 ─── Portal Facturación Externa (6-8 hrs)
  └── Depende del código único de tickets

FASE 4 ─── Panel Admin Facturas (3-4 hrs)
  └── Cierra el ciclo: solicitud → procesamiento
```

**Orden sugerido:** Fase 1 → Fase 2 → Fase 3 → Fase 4

Tiempo total estimado: **15-21 horas** de desarrollo.

---

## 10. Resumen de Implementación Realizada

### ✅ Implementado: Fase 2 — Tickets

| Componente | Archivo | Descripción |
|------------|---------|-------------|
| Migración | `database/migration_tickets.sql` | Tabla `tickets` con folio único, FK a ventas, datos JSON |
| Modelo | `app/Models/Ticket.php` | CRUD + `getByFolio()`, `getByVenta()`, `getByCliente()`, `createFromVenta()` |
| Controlador | `app/Controllers/TicketsController.php` | `show($folio)` — vista pública del ticket |
| Vista | `app/views/tickets/show.php` | Ticket imprimible con estilo tipo ticket (
) |
| Integración | `VentasController@store` | Genera ticket automáticamente al crear venta con folio `TKT-YYYYMMDD-XXXXXX` |
| Botón ticket | `views/ventas/index.php` | Columna "Ticket" con icono de acceso |
| Botón ticket | `views/home/cliente.php` | Icono de ticket en cada compra del portal cliente |
| CSRF + validación | `VentasController` | Validación con `validate()` y CSRF en store/update |
| CSRF token | `views/ventas/create.php` | Input oculto `csrf_token` |
| CSRF token | `views/ventas/edit.php` | Input oculto `csrf_token` |

### ✅ Implementado: Fase 3 — Portal de Facturación Externa

| Componente | Archivo | Descripción |
|------------|---------|-------------|
| Migración | `database/migration_facturacion.sql` | Campos fiscales en `clientes` (CP, régimen, uso CFDI, correo) + columnas en `solicitudes_factura` |
| Controlador | `app/Controllers/FacturaPublicaController.php` | 4 métodos: buscarForm, buscar, solicitarForm, solicitar |
| Vista | `views/factura_publica/buscar.php` | Formulario público para ingresar código del ticket |
| Vista | `views/factura_publica/formulario.php` | Datos de venta + formulario con RFC, CP, régimen fiscal, uso CFDI (catálogos SAT completos) |
| Vista | `views/factura_publica/confirmacion.php` | Confirmación de solicitud exitosa |
| Ruta | `GET /factura` | Página pública (sin autenticación) |
| Ruta | `POST /factura/buscar` | Busca ticket por código |
| Ruta | `GET /factura/solicitar/{folio}` | Formulario fiscal |
| Ruta | `POST /factura/solicitar/{folio}` | Envía solicitud + actualiza datos del cliente |
| Permisos | `puede_acceder('facturas')` | Agregado para roles 1 y 3 |

### ✅ Implementado: Fase 4 — Panel de Administración de Facturas

| Componente | Archivo | Descripción |
|------------|---------|-------------|
| Controlador | `app/Controllers/FacturasController.php` | `solicitudes()`, `procesar($id)`, `rechazar($id)` |
| Vista | `views/facturas/solicitudes.php` | Panel con solicitudes pendientes y procesadas, botones Procesar/Rechazar |
| Ruta | `GET /facturas/solicitudes` | Lista de solicitudes (admin/supervisor) |
| Ruta | `POST /facturas/procesar/{id}` | Cambia estatus + crea registro en `facturas` |
| Ruta | `POST /facturas/rechazar/{id}` | Rechaza solicitud |
| Sidebar | `views/layouts/sidebar.php` | Enlace "Facturas" visible para admin/supervisor |
| Lógica | `FacturasController@procesar` | Inserta en `facturas` con monto, vencimiento a 30 días, estatus 'emitida' |

### ⏳ Pendiente: Fase 1 — Método de Pago

| Componente | Estado |
|------------|--------|
| Tabla `metodos_pago` | Pendiente |
| FK en `ventas` | Pendiente |
| Select en formularios | Pendiente |

### Nuevas Rutas Agregadas (8 rutas)

| Método | Ruta | Controlador@Método | Acceso |
|--------|------|-------------------|--------|
| GET | `/tickets/{folio}` | TicketsController@show | Auth |
| GET | `/factura` | FacturaPublicaController@buscarForm | Público |
| POST | `/factura/buscar` | FacturaPublicaController@buscar | Público |
| GET | `/factura/solicitar/{folio}` | FacturaPublicaController@solicitarForm | Público |
| POST | `/factura/solicitar/{folio}` | FacturaPublicaController@solicitar | Público |
| GET | `/facturas/solicitudes` | FacturasController@solicitudes | Admin/Supervisor |
| POST | `/facturas/procesar/{id}` | FacturasController@procesar | Admin/Supervisor |
| POST | `/facturas/rechazar/{id}` | FacturasController@rechazar | Admin/Supervisor |

### Archivos Nuevos (11 archivos)

```
database/
├── migration_tickets.sql
└── migration_facturacion.sql

app/
├── Controllers/
│   ├── TicketsController.php
│   ├── FacturaPublicaController.php
│   └── FacturasController.php
├── Models/
│   └── Ticket.php
└── views/
    ├── tickets/
    │   └── show.php
    ├── factura_publica/
    │   ├── buscar.php
    │   ├── formulario.php
    │   └── confirmacion.php
    └── facturas/
        └── solicitudes.php
```

### Archivos Modificados (7 archivos)

| Archivo | Cambio |
|---------|--------|
| `app/Controllers/VentasController.php` | +Ticket, +CSRF, +validación |
| `app/Controllers/HomeController.php` | +Ticket JOIN en query del cliente |
| `app/views/ventas/index.php` | +Columna ticket |
| `app/views/ventas/create.php` | +CSRF token |
| `app/views/ventas/edit.php` | +CSRF token |
| `app/views/home/cliente.php` | +Botón ticket + enlace facturación |
| `app/views/layouts/sidebar.php` | +Enlace Facturas |
| `app/config/routes.php` | +8 rutas nuevas |
| `app/helpers/funciones.php` | +Permiso 'facturas' en puede_acceder() |
