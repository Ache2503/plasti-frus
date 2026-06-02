# Base de Datos - Plasti Frus

## 1. Visión General

**Plasti Frus** utiliza una base de datos relacional normalizada en **3NF** con **130+ tablas** organizadas en 10 áreas funcionales principales. Motor: MySQL 8.0+ o MariaDB 11.4+. Charset: UTF-8MB4.

```
Estadísticas de BD:
├─ Total de tablas:     130+
├─ Normalizadas:        Sí (3NF)
├─ Motor:               InnoDB (transacciones ACID)
├─ Charset:             utf8mb4_unicode_ci
├─ Relaciones FK:       150+ (integridad referencial)
├─ Índices:             200+ (búsqueda y performance)
└─ Auditoría:           8 tablas de trazabilidad
```

---

## 2. Diccionario de Tablas por Categoría

### 2.1 MAESTROS - Datos Base del Sistema

| Tabla | Propósito | Filas | PK | FK |
|-------|----------|-------|-----|-----|
| `roles` | Definición de 6 roles | 6 | id | - |
| `usuarios` | Users del sistema (autenticación) | ~20 | id | roles(1) |
| `empleados` | Personal de empresa | ~50 | id | - |
| `clientes` | Clientes (soft-delete) | ~500 | id | - |
| `direcciones_clientes` | Direcciones de clientes | ~600 | id | clientes(1) |
| `proveedores` | Proveedores | ~80 | id | - |
| `cedes` | Sucursales/plantas | ~3 | id | - |
| `maquinas` | Máquinas de producción | ~15 | id | cedes(1) |
| `moldes` | Moldes de inyección | ~30 | id | maquinas(1) |
| `refacciones_maquinas` | Refacciones disponibles | ~200 | id | - |

**Diagrama:**
```
usuarios ──FK────> roles
empleados
clientes ──1:N──> direcciones_clientes
proveedores
cedes ──1:N──> maquinas ──1:N──> moldes
refacciones_maquinas
```

---

### 2.2 PRODUCCIÓN - Núcleo Operacional

| Tabla | Propósito | Filas | PK | FK |
|-------|----------|-------|-----|-----|
| `materiales` | Materias primas | ~200 | id | proveedores(1) |
| `productos` | Productos terminados | ~300 | id | - |
| `recetas_cabecera` | Definición de recetas | ~100 | id | maquinas(1) |
| `recetas_detalle` | Materiales por receta | ~500 | id | recetas_cabecera(1), materiales(1) |
| `ordenes_cabecera` | Órdenes de producción | ~2000 | id | recetas_cabecera(1), maquinas(1), usuarios(1) |
| `ordenes_merma` | Mermas por orden | ~3000 | id | ordenes_cabecera(1), materiales(1) |
| `seguimiento_ordenes` | Histórico estatus | ~5000 | id | ordenes_cabecera(1) |
| `consumo_material_por_orden` | Consumo real de materiales | ~2500 | id | ordenes_cabecera(1), materiales(1) |
| `consumo_energia_por_orden` | Energía gastada | ~2000 | id | ordenes_cabecera(1), maquinas(1) |

**Diagrama:**
```
recetas_cabecera ──1:N──> recetas_detalle ──FK──> materiales
                          ↓
ordenes_cabecera ──FK──> recetas_cabecera
ordenes_cabecera ──1:N──> ordenes_merma ──FK──> materiales
ordenes_cabecera ──1:N──> seguimiento_ordenes
ordenes_cabecera ──1:N──> consumo_material_por_orden ──FK──> materiales
ordenes_cabecera ──1:N──> consumo_energia_por_orden ──FK──> maquinas
```

---

### 2.3 MÁQUINAS & MANTENIMIENTO

| Tabla | Propósito | Filas | PK | FK |
|-------|----------|-------|-----|-----|
| `maquinas` | Máquinas disponibles | ~15 | id | cedes(1) |
| `moldes` | Moldes por máquina | ~30 | id | maquinas(1) |
| `bitacora_calibraciones` | Registro de calibraciones | ~500 | id | maquinas(1), usuarios(1) |
| `bitacora_paros` | Paros de producción | ~1000 | id | maquinas(1), usuarios(1), incidencias_produccion(1) |
| `plan_mantenimiento` | Plan preventivo | ~50 | id | maquinas(1) |
| `mantenimientos_maquinas` | Ejecución mantenimiento | ~300 | id | maquinas(1), plan_mantenimiento(1), usuarios(1) |
| `mantenimientos_moldes` | Mantenimiento de moldes | ~200 | id | moldes(1), usuarios(1) |
| `indicadores_oee` | Overall Equipment Effectiveness | ~500 | id | maquinas(1), ordenes_cabecera(1) |
| `calibraciones_maquinas` | Historial de calibraciones | ~400 | id | maquinas(1) |

**Flujo:**
```
plan_mantenimiento ──1:N──> mantenimientos_maquinas
maquinas ──1:N──> bitacora_calibraciones
maquinas ──1:N──> bitacora_paros ──FK──> incidencias_produccion
maquinas ──1:N──> mantenimientos_maquinas
moldes ──1:N──> mantenimientos_moldes
maquinas ──1:N──> indicadores_oee ──FK──> ordenes_cabecera
```

---

### 2.4 CALIDAD & CONTROL

| Tabla | Propósito | Filas | PK | FK |
|-------|----------|-------|-----|-----|
| `inspecciones_calidad` | Inspecciones realizadas | ~1500 | id | ordenes_cabecera(1), usuarios(1) |
| `rechazos_calidad` | Defectos encontrados | ~500 | id | inspecciones_calidad(1), materiales(1) |
| `control_temperatura` | Temperatura de proceso | ~5000 | id | maquinas(1), ordenes_cabecera(1) |
| `control_presion` | Presión de inyección | ~5000 | id | maquinas(1), ordenes_cabecera(1) |
| `control_tiempo` | Tiempos de ciclo | ~5000 | id | maquinas(1), ordenes_cabecera(1) |
| `parametros` | Parámetros de máquina | ~100 | id | maquinas(1), productos(1) |
| `indicadores_scrap` | Porcentaje de rechazo | ~500 | id | maquinas(1) |
| `auditorias_calidad` | Auditorías de QA | ~100 | id | usuarios(1) |

**Relaciones:**
```
ordenes_cabecera ──1:N──> inspecciones_calidad ──1:N──> rechazos_calidad
maquinas ──1:N──> control_temperatura
maquinas ──1:N──> control_presion
maquinas ──1:N──> control_tiempo
maquinas ──1:N──> parametros ──FK──> productos
maquinas ──1:N──> indicadores_scrap
```

---

### 2.5 VENTAS & CRM

| Tabla | Propósito | Filas | PK | FK |
|-------|----------|-------|-----|-----|
| `vendedores` | Vendedores activos | ~20 | id | usuarios(1) |
| `clientes` | Base de clientes | ~500 | id | - |
| `oportunidades` | Pipeline de ventas | ~300 | id | clientes(1), vendedores(1) |
| `actividades` | Actividades comerciales | ~1000 | id | clientes(1), usuarios(1) |
| `interacciones` | Llamadas, emails, reuniones | ~2000 | id | clientes(1), usuarios(1) |
| `mensajes` | Mensajería interna | ~5000 | id | usuarios(1) |
| `ventas` | Transacciones de venta | ~1000 | id | clientes(1), vendedores(1), productos(1) |
| `presupuestos` | Presupuestos no aprobados | ~200 | id | clientes(1) |
| `metas_vendedor` | Cuotas de venta | ~100 | id | vendedores(1) |
| `comisiones_vendedores` | Comisiones pagadas | ~500 | id | vendedores(1) |

**Pipeline:**
```
vendedores ──FK──> usuarios
clientes ──1:N──> oportunidades ──FK──> vendedores
clientes ──1:N──> actividades
clientes ──1:N──> interacciones
clientes ──1:N──> ventas ──FK──> vendedores, productos
vendedores ──1:N──> metas_vendedor
vendedores ──1:N──> comisiones_vendedores
```

---

### 2.6 INVENTARIOS & ALMACÉN

| Tabla | Propósito | Filas | PK | FK |
|-------|----------|-------|-----|-----|
| `kardex_materiales` | Movimiento de materiales | ~10000 | id | materiales(1), ordenes_cabecera(1) |
| `alerta_stock_materiales` | Alertas de bajo stock | ~200 | id | materiales(1) |
| `inventario_productos_terminados` | Stock de productos | ~500 | id | productos(1) |
| `ubicacion_rack` | Ubicaciones en almacén | ~1000 | id | - |
| `historial_ubicacion` | Historial de movimientos | ~5000 | id | ubicacion_rack(1), materiales(1) |
| `devoluciones_clientes` | Devoluciones de clientes | ~100 | id | clientes(1), ventas(1), productos(1) |
| `devoluciones_proveedores` | Devoluciones a proveedores | ~80 | id | proveedores(1), materiales(1) |

**Estructura:**
```
materiales ──1:N──> kardex_materiales ──FK──> ordenes_cabecera
materiales ──1:N──> alerta_stock_materiales
productos ──1:N──> inventario_productos_terminados
ubicacion_rack ──1:N──> historial_ubicacion ──FK──> materiales
clientes ──1:N──> devoluciones_clientes ──FK──> ventas, productos
proveedores ──1:N──> devoluciones_proveedores ──FK──> materiales
```

---

### 2.7 CONTABILIDAD & FINANZAS

| Tabla | Propósito | Filas | PK | FK |
|-------|----------|-------|-----|-----|
| `plan_cuentas` | Plan de cuentas (activo, pasivo, capital) | ~150 | id | - |
| `polizas` | Pólizas contables (diario, ingresos, egresos) | ~2000 | id | plan_cuentas(1), usuarios(1) |
| `facturas` | Facturas (con CFDI) | ~500 | id | clientes(1), ventas(1) |
| `cierre_contable` | Cierres por periodo | ~50 | id | usuarios(1) |
| `cuentas_por_cobrar` | Cartera de clientes | ~500 | id | clientes(1), facturas(1) |
| `cuentas_por_pagar` | Cartera de proveedores | ~300 | id | proveedores(1) |
| `metodos_pago` | Formas de pago disponibles | ~10 | id | - |
| `cartera` | Pagos recibidos | ~1000 | id | clientes(1) |
| `referencias_deposito` | Referencias bancarias | ~500 | id | cartera(1) |
| `tarjetas_cliente` | Tarjetas de crédito registradas | ~300 | id | clientes(1) |

**Flujo Contable:**
```
plan_cuentas ──1:N──> polizas
clientes ──1:N──> facturas ──FK──> ventas, plan_cuentas
clientes ──1:N──> cuentas_por_cobrar ──FK──> facturas
clientes ──1:N──> cartera ──1:N──> referencias_deposito
clientes ──1:N──> tarjetas_cliente
proveedores ──1:N──> cuentas_por_pagar
cierre_contable (registra balances finales)
```

---

### 2.8 PORTAL DE CLIENTE

| Tabla | Propósito | Filas | PK | FK |
|-------|----------|-------|-----|-----|
| `carrito_cliente` | Carrito de compras (sesión) | ~5000 | id | clientes(1), productos(1) |
| `tickets_soporte` | Tickets de soporte | ~300 | id | clientes(1), usuarios(1) |
| `respuestas_ticket` | Respuestas a tickets | ~800 | id | tickets_soporte(1), usuarios(1) |
| `wishlist` | Productos guardados | ~2000 | id | clientes(1), productos(1) |
| `notificaciones_cliente` | Notificaciones para clientes | ~10000 | id | clientes(1) |
| `notificaciones_vendedor` | Notificaciones para vendedores | ~5000 | id | vendedores(1) |
| `notificaciones_operador` | Notificaciones para operadores | ~5000 | id | usuarios(1) |
| `notificaciones_supervisor` | Notificaciones para supervisores | ~5000 | id | usuarios(1) |

**Relaciones:**
```
clientes ──1:N──> carrito_cliente ──FK──> productos
clientes ──1:N──> tickets_soporte ──1:N──> respuestas_ticket
clientes ──1:N──> wishlist ──FK──> productos
clientes ──1:N──> notificaciones_cliente
```

---

### 2.9 AUDITORÍA & TRAZABILIDAD

| Tabla | Propósito | Filas | PK | FK |
|-------|----------|-------|-----|-----|
| `auditorias` | Log de todas las operaciones | ~50000 | id | usuarios(1) |
| `bitacora_produccion` | Bitácora diaria de producción | ~2000 | id | usuarios(1) |
| `trazabilidad_operadores` | Quién operó qué | ~5000 | id | ordenes_cabecera(1), usuarios(1) |
| `trazabilidad_maquinas` | Máquinas usadas en órdenes | ~2500 | id | ordenes_cabecera(1), maquinas(1) |
| `trazabilidad_moldes` | Moldes usados en órdenes | ~2500 | id | ordenes_cabecera(1), moldes(1) |
| `trazabilidad_materiales` | Materiales consumidos | ~5000 | id | ordenes_cabecera(1), materiales(1) |
| `trazabilidad_calidad` | Inspecciones por lote | ~3000 | id | ordenes_cabecera(1), inspecciones_calidad(1) |
| `trazabilidad_embarque` | Embarques de productos | ~500 | id | ventas(1), usuarios(1) |
| `trazabilidad_venta` | Historial de venta | ~2000 | id | ventas(1), usuarios(1) |
| `trazabilidad_devolucion` | Devoluciones rastreadas | ~300 | id | devoluciones_clientes(1), usuarios(1) |

**Propósito:**
```
auditorias ──completa──> Registro de todas las acciones
bitacora_produccion ──diaria──> Resumen de operaciones
trazabilidad_* ──específica──> Rastreo por entidad
```

---

### 2.10 REPORTES & KPIs

| Tabla | Propósito | Filas | PK | FK |
|-------|----------|-------|-----|-----|
| `indicadores_kpi` | KPIs generales del negocio | ~100 | id | - |
| `indicadores_oee` | Overall Equipment Effectiveness | ~500 | id | maquinas(1) |
| `indicadores_scrap` | Tasa de rechazo por máquina | ~500 | id | maquinas(1) |
| `costo_produccion` | Costo por orden | ~2000 | id | ordenes_cabecera(1) |
| `eficiencia_operativa` | Eficiencia por turno | ~2000 | id | - |
| `eficiencia_operadores` | Eficiencia individual | ~3000 | id | usuarios(1) |
| `productividad_turnos` | Productividad por turno | ~2000 | id | - |
| `energia_consumo` | Consumo de energía | ~2000 | id | maquinas(1) |
| `accesos_sistema` | Registro de logins | ~50000 | id | usuarios(1) |
| `auditorias_kpi` | Auditoría de cambios KPI | ~1000 | id | usuarios(1) |

**Análisis:**
```
indicadores_* ──reportes──> Dashboards
costo_produccion ──calcula──> Rentabilidad
eficiencia_* ──mide──> Performance
energia_consumo ──analiza──> Sustentabilidad
```

---

## 3. Esquemas de Tablas Principales

### `usuarios`
```sql
CREATE TABLE usuarios (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nombre VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    rol_id INT NOT NULL,
    estado ENUM('activo','inactivo') DEFAULT 'activo',
    ultimo_login DATETIME,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (rol_id) REFERENCES roles(id)
);
INDEX idx_email (email)
INDEX idx_rol_id (rol_id)
```

### `materiales`
```sql
CREATE TABLE materiales (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nombre VARCHAR(100) NOT NULL UNIQUE,
    descripcion TEXT,
    tipo ENUM('plastico','tinte','aditivo','combustible','otro'),
    proveedor_id INT,
    precio_compra DECIMAL(10,2),
    stock INT DEFAULT 0,
    punto_reorden INT DEFAULT 100,
    unidad VARCHAR(20),
    estado ENUM('activo','inactivo') DEFAULT 'activo',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (proveedor_id) REFERENCES proveedores(id)
);
INDEX idx_nombre (nombre)
INDEX idx_stock (stock)
INDEX idx_proveedor_id (proveedor_id)
```

### `ordenes_cabecera`
```sql
CREATE TABLE ordenes_cabecera (
    id INT PRIMARY KEY AUTO_INCREMENT,
    numero_orden VARCHAR(50) UNIQUE,
    receta_id INT NOT NULL,
    cantidad_ordenada INT NOT NULL,
    cantidad_completada INT,
    maquina_id INT,
    turno VARCHAR(20),
    estado ENUM('pendiente','en_proceso','completada','cancelada') DEFAULT 'pendiente',
    usuario_id INT,
    fecha_inicio DATETIME,
    fecha_fin DATETIME,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (receta_id) REFERENCES recetas_cabecera(id),
    FOREIGN KEY (maquina_id) REFERENCES maquinas(id),
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
);
INDEX idx_numero_orden (numero_orden)
INDEX idx_estado (estado)
INDEX idx_maquina_id (maquina_id)
INDEX idx_receta_id (receta_id)
```

### `clientes`
```sql
CREATE TABLE clientes (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nombre VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE,
    telefono VARCHAR(20),
    rfc VARCHAR(13),
    razon_social VARCHAR(200),
    estado ENUM('activo','inactivo') DEFAULT 'activo',
    deleted_at DATETIME,  -- Soft delete
    vendedor_id INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (vendedor_id) REFERENCES vendedores(id)
);
INDEX idx_email (email)
INDEX idx_deleted_at (deleted_at)
INDEX idx_vendedor_id (vendedor_id)
```

### `ventas`
```sql
CREATE TABLE ventas (
    id INT PRIMARY KEY AUTO_INCREMENT,
    folio VARCHAR(50) UNIQUE,
    cliente_id INT NOT NULL,
    producto_id INT NOT NULL,
    cantidad INT NOT NULL,
    precio_unitario DECIMAL(10,2),
    subtotal DECIMAL(10,2),
    impuesto DECIMAL(10,2),
    total DECIMAL(10,2),
    vendedor_id INT,
    metodo_pago_id INT,
    estado ENUM('pendiente','completada','cancelada') DEFAULT 'pendiente',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (cliente_id) REFERENCES clientes(id),
    FOREIGN KEY (producto_id) REFERENCES productos(id),
    FOREIGN KEY (vendedor_id) REFERENCES vendedores(id),
    FOREIGN KEY (metodo_pago_id) REFERENCES metodos_pago(id)
);
INDEX idx_folio (folio)
INDEX idx_cliente_id (cliente_id)
INDEX idx_estado (estado)
```

### `polizas`
```sql
CREATE TABLE polizas (
    id INT PRIMARY KEY AUTO_INCREMENT,
    numero_poliza VARCHAR(50) UNIQUE,
    fecha DATE NOT NULL,
    tipo ENUM('diario','ingresos','egresos','general') DEFAULT 'diario',
    descripcion TEXT,
    cuenta_deudora_id INT,
    cuenta_acreedora_id INT,
    monto DECIMAL(12,2) NOT NULL,
    usuario_id INT,
    estado ENUM('vigente','anulada') DEFAULT 'vigente',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (cuenta_deudora_id) REFERENCES plan_cuentas(id),
    FOREIGN KEY (cuenta_acreedora_id) REFERENCES plan_cuentas(id),
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
);
INDEX idx_numero_poliza (numero_poliza)
INDEX idx_fecha (fecha)
INDEX idx_tipo (tipo)
```

### `auditorias`
```sql
CREATE TABLE auditorias (
    id INT PRIMARY KEY AUTO_INCREMENT,
    usuario_id INT,
    tabla VARCHAR(50),
    operacion ENUM('INSERT','UPDATE','DELETE'),
    registro_id INT,
    datos_anteriores JSON,
    datos_nuevos JSON,
    ip_origen VARCHAR(45),
    user_agent VARCHAR(255),
    estado ENUM('exitosa','error') DEFAULT 'exitosa',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
);
INDEX idx_tabla (tabla)
INDEX idx_operacion (operacion)
INDEX idx_fecha (created_at)
```

---

## 4. Índices Clave

### Índices por Tipo

**Búsqueda rápida:**
```
usuarios (email, rol_id)
clientes (email, deleted_at)
productos (nombre, familia)
materiales (nombre, stock)
ordenes_cabecera (numero_orden, estado)
```

**Rendimiento de consultas:**
```
ventas (cliente_id, estado, created_at)
kardex_materiales (material_id, tipo_movimiento)
polizas (fecha, tipo, cuenta_*)
```

**Integridad referencial:**
```
ordenes_cabecera (receta_id, maquina_id)
recetas_detalle (receta_id, material_id)
bitacora_paros (maquina_id, usuario_id)
```

---

## 5. Volumen de Datos Estimado

| Tabla | Estimado | Crecimiento Mensual |
|-------|----------|-------------------|
| usuarios | 50 | 0-2 |
| clientes | 500 | 20 |
| productos | 300 | 5 |
| materiales | 200 | 2 |
| ordenes_cabecera | 2000 | 300-500 |
| ventas | 1000 | 150-200 |
| kardex_materiales | 10000 | 1500-2000 |
| auditorias | 50000 | 5000-10000 |
| **TOTAL** | **~120K** | **~8K-12K/mes** |

---

## 6. Integridad Referencial

Todas las claves foráneas usan:
```sql
FOREIGN KEY (columna) REFERENCES tabla(id)
ON DELETE RESTRICT
ON UPDATE CASCADE
```

Esto garantiza:
- ❌ No se puede eliminar registro con dependencias
- ✅ Actualizaciones de PK se propagan automáticamente

**Restricciones críticas:**
```
roles > usuarios (no se puede eliminar rol con usuarios)
recetas_cabecera > ordenes_cabecera (no se puede eliminar receta en uso)
clientes > ventas (no se puede eliminar cliente con historial)
plan_cuentas > polizas (no se puede eliminar cuenta en uso)
```

---

## 7. Normas de Base de Datos

### Nomenclatura
- **Tablas:** `snake_case` singular (usuario, cliente, orden)
- **Columnas:** `snake_case` (fecha_creacion, monto_total)
- **IDs Foráneos:** `tabla_id` (usuario_id, cliente_id)
- **Booleanos:** `ENUM('si','no')` o `TINYINT(1)`
- **Moneda:** `DECIMAL(12,2)` para precisión
- **Fechas:** `DATE` para fechas, `DATETIME` para timestamps
- **Estados:** `ENUM('activo','inactivo','pendiente',...)` 

### Timestamps
```sql
created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
deleted_at DATETIME NULL  -- Para soft delete
```

### Soft Delete Pattern
```sql
-- Lógica de borrado suave
UPDATE clientes SET deleted_at = NOW() WHERE id = 1;

-- Consulta de registros activos
SELECT * FROM clientes WHERE deleted_at IS NULL;

-- Restaurar eliminado
UPDATE clientes SET deleted_at = NULL WHERE id = 1;
```

---

## 8. Performance & Optimización

### Queries Frecuentes Optimizadas
```sql
-- Top 10 productos más vendidos
SELECT p.*, COUNT(v.id) AS total_ventas
FROM productos p
LEFT JOIN ventas v ON p.id = v.producto_id
GROUP BY p.id
ORDER BY total_ventas DESC
LIMIT 10;

-- Stock bajo
SELECT * FROM materiales
WHERE stock <= punto_reorden
AND estado = 'activo'
ORDER BY stock ASC;

-- KPI diario
SELECT DATE(o.created_at) AS fecha,
       COUNT(*) AS total_ordenes,
       SUM(CASE WHEN o.estado='completada' THEN 1 ELSE 0 END) AS completadas,
       AVG(DATEDIFF(o.fecha_fin, o.fecha_inicio)) AS prom_dias
FROM ordenes_cabecera o
WHERE DATE(o.created_at) = CURDATE()
GROUP BY DATE(o.created_at);
```

### Transacciones ACID
```sql
START TRANSACTION;
  INSERT INTO ordenes_cabecera (...) VALUES (...);
  UPDATE materiales SET stock = stock - 100 WHERE id = 5;
  INSERT INTO kardex_materiales (...) VALUES (...);
COMMIT;
```

---

## 9. Backup & Recuperación

### Backup Completo
```bash
mysqldump -u plastifrus -p fabrica_plasticos > backup_$(date +%Y%m%d).sql
```

### Restore
```bash
mysql -u plastifrus -p fabrica_plasticos < backup_20260602.sql
```

### Backup Diferencial
```bash
mysqldump -u plastifrus -p --single-transaction \
  --master-data=2 fabrica_plasticos > backup_incremental.sql
```

---

## 10. Monitoreo y Mantenimiento

### Verificar Integridad
```sql
CHECK TABLE usuarios, clientes, ordenes_cabecera;
ANALYZE TABLE usuarios;
OPTIMIZE TABLE usuarios;
```

### Estadísticas de Tabla
```sql
SELECT TABLE_NAME, TABLE_ROWS, DATA_LENGTH
FROM information_schema.TABLES
WHERE TABLE_SCHEMA = 'fabrica_plasticos'
ORDER BY DATA_LENGTH DESC;
```

### Variables de Performance
```sql
SHOW VARIABLES LIKE 'max_connections';
SHOW VARIABLES LIKE 'query_cache%';
SHOW STATUS LIKE 'Threads%';
```

---

## 11. Conclusión

La base de datos de **Plasti Frus** está diseñada para:

✅ **Escalabilidad:** 130+ tablas normalizadas soportan crecimiento sin restructuración
✅ **Integridad:** Claves foráneas, constraints y triggers protegen los datos
✅ **Performance:** Índices estratégicos, queries optimizadas, transacciones ACID
✅ **Auditoría:** 10 tablas de trazabilidad registran todo cambio
✅ **Seguridad:** Soft deletes, backups automáticos, acceso controlado

**Próximas mejoras:**
- Particionamiento de tablas grandes (auditorias, kardex)
- Replicación maestro-esclavo para HA
- Caché de consultas frecuentes (Redis)
- Data warehouse para análisis histórico
