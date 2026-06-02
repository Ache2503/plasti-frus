# Módulos del Sistema Plasti Frus

**Plasti Frus** está organizado en **17 módulos independientes** que cubren todas las áreas de negocio de una fábrica de plásticos.

---

## 1. 📚 MÓDULO: Autenticación (Auth)

**Archivo de rutas:** `routes/auth.php`  
**Controlador:** `AuthController.php`  
**Propósito:** Gestionar login, registro y control de acceso

### Funcionalidades
- ✅ Login con email y contraseña (bcrypt)
- ✅ Registro de usuarios (clientes, vendedores)
- ✅ Logout seguro con sesión regenerada
- ✅ Recuperación de contraseña por email
- ✅ Validación de horario laboral para operadores
- ✅ Protección CSRF

### Rutas
| Método | Ruta | Controlador | Función |
|--------|------|------------|---------|
| GET | `/login` | AuthController@showLogin | Mostrar formulario login |
| POST | `/login` | AuthController@login | Autenticar usuario |
| GET | `/register` | AuthController@showRegister | Formulario registro |
| POST | `/register` | AuthController@register | Registrar nuevo usuario |
| GET | `/logout` | AuthController@logout | Cerrar sesión |
| GET | `/olvide-contrasena` | AuthController@showForgot | Form recuperación |
| POST | `/olvide-contrasena` | AuthController@sendReset | Enviar email reset |
| GET | `/restablecer-contrasena/{token}` | AuthController@showReset | Form nueva contraseña |
| POST | `/restablecer-contrasena` | AuthController@resetPassword | Guardar nueva contraseña |

### Modelos
- `User` — Usuarios del sistema con roles

### Vistas
- `auth/login.php` — Formulario de acceso
- `auth/register.php` — Registro de usuario
- `auth/forgot_password.php` — Recuperación contraseña
- `auth/reset_password.php` — Nueva contraseña

### Estado
✅ **100% Implementado**

---

## 2. 🏠 MÓDULO: Dashboard / Home

**Archivo de rutas:** `routes/dashboard.php`  
**Controlador:** `HomeController.php`  
**Propósito:** Panel principal personalizado por rol

### Funcionalidades
- ✅ 6 dashboards distintos según rol (admin, operador, supervisor, vendedor, cliente, contador)
- ✅ Estadísticas en tiempo real (gráficas, KPIs)
- ✅ Acceso rápido a módulos principales
- ✅ Notificaciones e incidencias pendientes
- ✅ Reportar paros de máquina
- ✅ Cambio de turno para operadores

### Rutas Principales
| Ruta | Descripción |
|------|------------|
| GET `/` | Dashboard según rol autenticado |
| GET `/home` | Dashboard alternativo |
| POST `/reportar-paro` | Registrar paro de máquina |
| POST `/set-turno` | Cambiar turno actual |
| GET `/mis-compras` | Historial de compras (cliente) |
| POST `/facturas/request/{id}` | Solicitar factura |
| POST `/cliente/asignar-vendedor` | Asignar vendedor (cliente) |

### Dashboards por Rol

**Admin Dashboard:**
- Counts: Materiales, Productos, Órdenes, Clientes, Ventas
- Gráfica: Órdenes por estado
- Tabla: Últimas órdenes
- Botones: Acceso a todas las áreas

**Supervisor Dashboard:**
- Producción del día: Órdenes activas
- Máquinas: Estado y eficiencia
- Incidencias: Activas sin resolver
- Paros: Últimas 24 horas

**Operador Dashboard:**
- Órdenes asignadas por turno
- Máquinas disponibles
- Botón: Reportar paro
- Alertas de mantenimiento

**Vendedor Dashboard:**
- Pipeline de oportunidades
- Ventas del mes (gráfica)
- Comisiones acumuladas
- Top 5 clientes

**Cliente Dashboard:**
- Mis compras (últimas transacciones)
- Carrito activo
- Facturas pendientes
- Cartera (deuda)

**Contador Dashboard:**
- Balance General (activos, pasivos, capital)
- Pólizas del mes (ingresos, egresos)
- Facturas pendientes de contabilizar
- Cierres contables próximos

### Estado
✅ **100% Implementado**

---

## 3. 🏭 MÓDULO: Producción

**Archivo de rutas:** `routes/production.php`  
**Controladores:** `MaterialesController`, `ProductosController`, `RecetasController`, `OrdenesController`  
**Propósito:** Gestión integral de producción

### 3.1 Sub-módulo: Materiales

**Ruta base:** `/materiales`

#### Funcionalidades
- ✅ CRUD de materiales (crear, listar, editar, eliminar)
- ✅ Control de stock en tiempo real
- ✅ Alertas automáticas de stock bajo
- ✅ Asociación con proveedores
- ✅ Historial de cambios (auditoría)

#### Rutas
| Ruta | Método | Controlador |
|------|--------|------------|
| `/materiales` | GET | MaterialesController@index |
| `/materiales/create` | GET | MaterialesController@create |
| `/materiales` | POST | MaterialesController@store |
| `/materiales/{id}/edit` | GET | MaterialesController@edit |
| `/materiales/{id}` | PUT | MaterialesController@update |
| `/materiales/{id}` | DELETE | MaterialesController@delete |

#### Datos Capturados
```
- Nombre (único, requerido)
- Descripción
- Tipo (plástico, tinte, aditivo, combustible, otro)
- Proveedor
- Precio de compra
- Stock actual
- Punto de reorden
- Unidad de medida
```

#### Estado
✅ **100% Implementado**

---

### 3.2 Sub-módulo: Productos

**Ruta base:** `/productos`

#### Funcionalidades
- ✅ CRUD de productos terminados
- ✅ Clasificación por familias y líneas
- ✅ Especificaciones técnicas (peso, dimensiones)
- ✅ Colores disponibles
- ✅ Precios de venta
- ✅ Imágenes/fotos

#### Rutas
| Ruta | Método | Controlador |
|------|--------|------------|
| `/productos` | GET | ProductosController@index |
| `/productos/create` | GET | ProductosController@create |
| `/productos` | POST | ProductosController@store |
| `/productos/{id}` | GET | ProductosController@show |
| `/productos/{id}/edit` | GET | ProductosController@edit |
| `/productos/{id}` | PUT | ProductosController@update |
| `/productos/{id}` | DELETE | ProductosController@delete |

#### Datos Capturados
```
- Código (único)
- Nombre (requerido)
- Descripción
- Familia (clasificación)
- Línea (sub-clasificación)
- Color
- Peso (gramos)
- Dimensiones (largo, ancho, alto)
- Precio venta unitario
- Stock disponible
- Imagen principal
```

#### Estado
✅ **100% Implementado**

---

### 3.3 Sub-módulo: Recetas

**Ruta base:** `/recetas`

#### Funcionalidades
- ✅ Crear recetas de producción
- ✅ Agregar materiales a receta con porcentajes
- ✅ Validar consumo de materiales
- ✅ Asignación de máquina recomendada
- ✅ Versionado de recetas
- ✅ Historial de cambios

#### Rutas
| Ruta | Método | Controlador |
|------|--------|------------|
| `/recetas` | GET | RecetasController@index |
| `/recetas/create` | GET | RecetasController@create |
| `/recetas` | POST | RecetasController@store |
| `/recetas/{id}` | GET | RecetasController@show |
| `/recetas/{id}/edit` | GET | RecetasController@edit |
| `/recetas/{id}` | PUT | RecetasController@update |
| `/recetas/{id}` | DELETE | RecetasController@delete |
| `/recetas/{id}/detalle` | GET | RecetasController@showDetalle |
| `/recetas/{id}/detalle` | POST | RecetasController@addMaterial |

#### Estructura de Datos
```
Receta Cabecera:
- Nombre (único, requerido)
- Descripción
- Máquina asignada (recomendada)
- Versión
- Temperatura de proceso
- Presión de inyección
- Tiempo de ciclo estimado

Receta Detalle (1:N):
- Material (FK)
- Porcentaje (%): ej 70% plástico, 20% tinte, 10% aditivo
- Cantidad en gramos (calculada)
- Orden de agregación
```

#### Estado
✅ **100% Implementado**

---

### 3.4 Sub-módulo: Órdenes de Producción

**Ruta base:** `/ordenes`

#### Funcionalidades
- ✅ CRUD de órdenes de producción
- ✅ Seguimiento de estatus (pendiente → en_proceso → completada)
- ✅ Asignación de máquina y operador
- ✅ Registro de mermas/scrap
- ✅ Cálculo automático de consumo de materiales
- ✅ Actualización de kardex al completar
- ✅ Gráfica de seguimiento
- ✅ Validación de stock antes de iniciar

#### Rutas
| Ruta | Método | Controlador |
|------|--------|------------|
| `/ordenes` | GET | OrdenesController@index |
| `/ordenes/create` | GET | OrdenesController@create |
| `/ordenes` | POST | OrdenesController@store |
| `/ordenes/{id}` | GET | OrdenesController@show |
| `/ordenes/{id}/edit` | GET | OrdenesController@edit |
| `/ordenes/{id}` | PUT | OrdenesController@update |
| `/ordenes/{id}/iniciar` | POST | OrdenesController@iniciar |
| `/ordenes/{id}/completar` | POST | OrdenesController@completar |
| `/ordenes/{id}/cancelar` | POST | OrdenesController@cancelar |

#### Flujo de Orden
```
CREACIÓN
├─ Seleccionar Receta
├─ Cantidad a producir
├─ Validar stock de materiales
└─ Estado: pendiente

INICIO
├─ Asignar máquina
├─ Operador/turno
├─ Validación de máquina disponible
└─ Estado: en_proceso

DURANTE
├─ Registro de mermas/scrap
├─ Paros de máquina (tiempo, causa)
├─ Calibraciones
└─ Bitácora de producción

FINALIZACIÓN
├─ Cantidad real producida
├─ Mermas finales
├─ Cálculo de eficiencia
├─ Actualizar kardex materiales
├─ Registrar en trazabilidad
└─ Estado: completada
```

#### Datos Capturados
```
- Número de orden (auto-generado, único)
- Receta (FK)
- Cantidad ordenada
- Cantidad completada
- Máquina asignada
- Operador asignado
- Turno (matutino, vespertino, nocturno)
- Fecha/hora inicio
- Fecha/hora fin
- Mermas registradas (% de scrap)
- Observaciones
```

#### Estado
✅ **100% Implementado**

---

### 3.5 Sub-módulo: Máquinas

**Ruta base:** `/maquinas`

#### Funcionalidades
- ✅ CRUD de máquinas de producción
- ✅ Estado (activa, inactiva, mantenimiento)
- ✅ OEE (Overall Equipment Effectiveness) en tiempo real
- ✅ Moldes asignados por máquina
- ✅ Consumo de energía por máquina
- ✅ Historial de paros y calibraciones
- ✅ Plan de mantenimiento preventivo

#### Rutas
| Ruta | Método | Controlador |
|------|--------|------------|
| `/maquinas` | GET | MaquinasController@index |
| `/maquinas/create` | GET | MaquinasController@create |
| `/maquinas` | POST | MaquinasController@store |
| `/maquinas/{id}` | GET | MaquinasController@show |
| `/maquinas/{id}/edit` | GET | MaquinasController@edit |
| `/maquinas/{id}` | PUT | MaquinasController@update |
| `/maquinas/{id}/oee` | GET | MaquinasController@oee |
| `/maquinas/{id}/calibrar` | POST | MaquinasController@calibrate |
| `/maquinas/{id}/paros` | GET | MaquinasController@paros |

#### Datos Capturados
```
- Código (único, ej: MAQ-001)
- Nombre/modelo
- Tipo (inyectora, prensa, trituradora, etc)
- Ubicación (cede/planta)
- Fabricante
- Año de fabricación
- Capacidad (kg/hora)
- Voltaje/potencia
- Estado (activa, inactiva, mantenimiento)
- Moldes asignados
- Temperatura máxima
- Presión máxima
```

#### KPIs Calculados
```
- OEE = Disponibilidad × Rendimiento × Calidad
- Disponibilidad = Tiempo de producción / Tiempo total
- Rendimiento = Piezas reales / Piezas esperadas
- Calidad = Piezas correctas / Total de piezas
```

#### Estado
✅ **100% Implementado**

---

### 3.6 Sub-módulo: Moldes

**Ruta base:** `/moldes`

#### Funcionalidades
- ✅ CRUD de moldes
- ✅ Asignación a máquina
- ✅ Ciclos acumulados y restantes
- ✅ Mantenimiento de moldes
- ✅ Historial de uso

#### Rutas
| Ruta | Método | Controlador |
|------|--------|------------|
| `/moldes` | GET | MoldesController@index |
| `/moldes/create` | GET | MoldesController@create |
| `/moldes` | POST | MoldesController@store |
| `/moldes/{id}` | GET | MoldesController@show |
| `/moldes/{id}/edit` | GET | MoldesController@edit |
| `/moldes/{id}` | PUT | MoldesController@update |

#### Datos Capturados
```
- Código (único)
- Descripción/especificaciones
- Máquina asignada
- Ciclos totales permitidos
- Ciclos acumulados (consumidos)
- Ciclos restantes
- Año de adquisición
- Proveedor/fabricante
- Estado (activo, dañado, descartado)
```

#### Estado
✅ **100% Implementado**

---

## 4. 📊 MÓDULO: Calidad

**Archivo de rutas:** `routes/quality.php`  
**Controlador:** `CalidadController.php`  
**Propósito:** Control de calidad e inspecciones

### Funcionalidades
- ✅ Registro de inspecciones de calidad
- ✅ Parámetros de control (temperatura, presión, tiempo)
- ✅ Registro de rechazos con causas
- ✅ Cálculo de tasa de scrap por máquina
- ✅ Auditorías de calidad
- ✅ Historial de defectos

### Rutas
| Ruta | Método | Controlador |
|------|--------|------------|
| `/calidad` | GET | CalidadController@index |
| `/calidad/inspecciones` | GET | CalidadController@inspecciones |
| `/calidad/inspecciones/create` | GET | CalidadController@createInspeccion |
| `/calidad/inspecciones` | POST | CalidadController@storeInspeccion |
| `/calidad/rechazos` | GET | CalidadController@rechazos |
| `/calidad/rechazos/crear` | POST | CalidadController@storeRechazo |
| `/calidad/parametros/{maquina_id}` | GET | CalidadController@parametros |
| `/calidad/reportes` | GET | CalidadController@reportes |

### Datos Capturados
```
Inspección:
- Orden de producción (FK)
- Inspector (usuario)
- Fecha y hora
- Cantidad inspeccionada
- Cantidad aceptada
- Temperatura
- Presión
- Tiempo de ciclo
- Observaciones

Rechazo:
- Inspección (FK)
- Causa (deformación, color, tamaño, otros)
- Cantidad rechazada
- Descripción del defecto
```

### Estado
✅ **70% Implementado** (Datos capturados, falta análisis avanzado y reportes)

---

## 5. 🔧 MÓDULO: Mantenimiento

**Archivo de rutas:** `routes/maintenance.php`  
**Controladores:** `MaquinasController`, `MantenimientoController`, `MoldesController`  
**Propósito:** Gestión de mantenimiento preventivo y correctivo

### Funcionalidades
- ✅ Plan de mantenimiento preventivo por máquina
- ✅ Registro de mantenimientos realizados
- ✅ Alertas automáticas de mantenimiento vencido
- ✅ Bitácora de paros (causa, duración, responsable)
- ✅ Seguimiento de refacciones utilizadas
- ✅ Histórico de mantenimiento por máquina

### Rutas
| Ruta | Método | Controlador |
|------|--------|------------|
| `/mantenimiento` | GET | MantenimientoController@index |
| `/mantenimiento/plan` | GET | MantenimientoController@plan |
| `/mantenimiento/plan/create` | POST | MantenimientoController@createPlan |
| `/mantenimiento/maquinas/{id}` | GET | MantenimientoController@maquina |
| `/mantenimiento/registrar` | POST | MantenimientoController@registrar |
| `/bitacora-paros` | GET | MantenimientoController@paros |
| `/bitacora-paros/crear` | POST | MantenimientoController@crearParo |

### Datos Capturados
```
Plan de Mantenimiento:
- Máquina (FK)
- Tipo (preventivo, correctivo)
- Descripción de tarea
- Frecuencia (cada X horas/días)
- Última ejecución
- Próxima fecha

Mantenimiento Realizado:
- Máquina (FK)
- Plan (FK, opcional)
- Tipo de mantenimiento
- Fecha inicio/fin
- Responsable (técnico)
- Refacciones utilizadas
- Costo
- Observaciones

Paro de Máquina:
- Máquina (FK)
- Fecha/hora inicio
- Fecha/hora fin
- Duración (minutos)
- Causa (mecánica, eléctrica, software, otro)
- Responsable
- Acción correctiva
- Impacto en producción
```

### Estado
✅ **80% Implementado** (Planes existen, falta automatización de alertas)

---

## 6. 📦 MÓDULO: Inventarios

**Archivo de rutas:** `routes/inventory.php`  
**Controlador:** `KardexController.php`  
**Propósito:** Control de inventario de materiales

### Funcionalidades
- ✅ Kardex detallado por material (entradas y salidas)
- ✅ Valuación de inventario (FIFO, LIFO, promedio)
- ✅ Alertas de stock bajo
- ✅ Ubicaciones en almacén
- ✅ Historial de movimientos
- ✅ Reportes de inversión en inventario

### Rutas
| Ruta | Método | Controlador |
|------|--------|------------|
| `/kardex` | GET | KardexController@index |
| `/kardex/{material_id}` | GET | KardexController@show |
| `/kardex/movimiento/crear` | POST | KardexController@crearMovimiento |
| `/kardex/ajustes` | GET | KardexController@ajustes |
| `/kardex/ajustes/crear` | POST | KardexController@crearAjuste |
| `/kardex/reportes` | GET | KardexController@reportes |
| `/kardex/alertas` | GET | KardexController@alertas |

### Datos Capturados
```
Movimiento de Kardex:
- Material (FK)
- Tipo (entrada, salida, ajuste)
- Cantidad
- Referencia (orden de compra, orden producción, ajuste)
- Valor unitario
- Valor total (cantidad × valor unitario)
- Ubicación origen
- Ubicación destino
- Usuario
- Fecha y observaciones

Ubicación en Almacén:
- Rack/estantería
- Pasillo
- Nivel
- Capacidad
- Contenido (material, cantidad)
```

### Valuación de Inventario
```
FIFO: Primera entrada, primera salida
LIFO: Última entrada, primera salida
Promedio: Costo promedio ponderado
```

### Estado
✅ **80% Implementado**

---

## 7. 🛍️ MÓDULO: Ventas

**Archivo de rutas:** `routes/sales.php`  
**Controladores:** `VentasController`, `PresupuestoController`  
**Propósito:** Gestión de ventas y facturación

### Funcionalidades
- 🟡 CRUD de ventas (sin validación completa)
- 🟡 Presupuestos (sin flujo de aprobación)
- ❌ Métodos de pago (NO implementado)
- ❌ Folio automático de venta (NO implementado)
- ❌ Validación de stock (NO implementado)
- ❌ Cálculo de descuentos/impuestos (NO implementado)
- ❌ Control de límite de crédito (PARCIAL)

### Rutas
| Ruta | Método | Controlador |
|------|--------|------------|
| `/ventas` | GET | VentasController@index |
| `/ventas/create` | GET | VentasController@create |
| `/ventas` | POST | VentasController@store |
| `/ventas/{id}` | GET | VentasController@show |
| `/ventas/{id}/facturar` | POST | VentasController@facturar |
| `/presupuestos` | GET | PresupuestoController@index |
| `/presupuestos/create` | POST | PresupuestoController@store |

### ⚠️ CARENCIAS CRÍTICAS
1. **Sin método de pago** — No se registra forma de pago (efectivo, cheque, tarjeta, transferencia)
2. **Sin folio único** — Cada venta debería tener número único de control
3. **1 producto por venta** — Debería permitir múltiples líneas
4. **Sin impuestos/descuentos** — Cálculos incompletos
5. **Sin control de stock** — No valida disponibilidad antes de vender
6. **Sin factura CFDI** — No genera comprobante fiscal válido

### Datos Capturados (Actual)
```
- Cliente (FK)
- Producto (FK) — SOLO 1 PRODUCTO
- Cantidad
- Precio unitario
- Subtotal (calculated)
- Impuesto (hardcoded)
- Total
- Vendedor asignado
- Estado (pendiente, completada, cancelada)
- Observaciones
```

### Estado
🟡 **40% Implementado** (REQUIERE COMPLETAR PARA PRODUCCIÓN)

---

## 8. 💰 MÓDULO: Contabilidad

**Archivo de rutas:** `routes/accounting.php`  
**Controladores:** `ContabilidadController`, `FacturasController`, `PolizasController`, `CierreContableController`  
**Propósito:** Gestión contable y financiera

### Funcionalidades
- ✅ Plan de cuentas completo (activo, pasivo, capital, resultados)
- ✅ Pólizas contables (diario, ingresos, egresos)
- ✅ Reportes: Balance General, Estado de Resultados, Balanza de Comprobación
- ✅ Libro Diario y Libro Mayor
- ✅ Flujo de Efectivo
- ✅ Presupuestos por departamento
- ✅ Cierre contable por periodo
- 🟡 Facturación (tabla existe, no usada)
- 🟡 Integración con ventas/compras (PARCIAL)

### Rutas
| Ruta | Método | Controlador |
|------|--------|------------|
| `/contabilidad` | GET | ContabilidadController@index |
| `/contabilidad/plan-cuentas` | GET | ContabilidadController@planCuentas |
| `/contabilidad/pólizas` | GET | ContabilidadController@polizas |
| `/contabilidad/pólizas/crear` | POST | PolizasController@store |
| `/contabilidad/balance` | GET | ContabilidadController@balanceGeneral |
| `/contabilidad/estado-resultados` | GET | ContabilidadController@estadoResultados |
| `/contabilidad/flujo-efectivo` | GET | ContabilidadController@flujoEfectivo |
| `/contabilidad/libro-diario` | GET | ContabilidadController@libroDiario |
| `/contabilidad/libro-mayor` | GET | ContabilidadController@libroMayor |
| `/contabilidad/cierre` | GET | CierreContableController@index |
| `/contabilidad/cierre/crear` | POST | CierreContableController@crear |
| `/facturas` | GET | FacturasController@index |
| `/facturas/{id}` | GET | FacturasController@show |

### Datos Capturados
```
Póliza Contable:
- Número de póliza (único)
- Tipo (Diario, Ingresos, Egresos, General)
- Fecha
- Referencia (venta, compra, ajuste)
- Cuenta deudora (FK a plan_cuentas)
- Cuenta acreedora (FK a plan_cuentas)
- Monto
- Descripción
- Usuario que registra
- Estado (vigente, anulada)

Plan de Cuentas:
- Código (ej: 1010 Bancos)
- Nombre
- Tipo (activo, pasivo, capital, ingresos, gastos, costos)
- Grupo
- Saldo inicial
- Saldo actual
```

### Reportes
```
Balance General:
├─ Activos Circulantes
├─ Activos No Circulantes
├─ Pasivos Circulantes
├─ Pasivos No Circulantes
└─ Capital Contable

Estado de Resultados:
├─ Ingresos
├─ Costos de producción
├─ Gastos operacionales
└─ Utilidad/Pérdida neta

Flujo de Efectivo:
├─ Operacionales
├─ Inversión
└─ Financiamiento
```

### Estado
🟡 **50% Implementado** (Plan de cuentas + pólizas funcionan, falta integración completa con ventas/compras y CFDI)

---

## 9. 👥 MÓDULO: CRM y Clientes

**Archivo de rutas:** `routes/crm.php`, `routes/client.php`  
**Controladores:** `ClientesController`, `OportunidadController`, `InteraccionController`, `ActividadController`  
**Propósito:** Gestión de relaciones con clientes

### Funcionalidades
- ✅ CRUD de clientes con soft-delete
- ✅ Pipeline Kanban de oportunidades (prospecto → negociación → ganado/perdido)
- ✅ Interacciones (llamadas, emails, reuniones)
- ✅ Actividades y tareas
- ✅ Historial completo de cliente
- ✅ Asignación de vendedor por cliente
- ✅ Búsqueda avanzada con filtros

### Rutas
| Ruta | Método | Controlador |
|------|--------|------------|
| `/clientes` | GET | ClientesController@index |
| `/clientes/create` | GET | ClientesController@create |
| `/clientes` | POST | ClientesController@store |
| `/clientes/{id}` | GET | ClientesController@show |
| `/clientes/{id}/edit` | GET | ClientesController@edit |
| `/clientes/{id}` | PUT | ClientesController@update |
| `/clientes/{id}` | DELETE | ClientesController@delete |
| `/oportunidades` | GET | OportunidadController@index |
| `/oportunidades/kanban` | GET | OportunidadController@kanban |
| `/oportunidades/create` | POST | OportunidadController@store |
| `/oportunidades/{id}/mover` | PUT | OportunidadController@mover |
| `/interacciones` | GET | InteraccionController@index |
| `/interacciones/crear` | POST | InteraccionController@store |
| `/actividades` | GET | ActividadController@index |
| `/actividades/crear` | POST | ActividadController@store |

### Datos Capturados
```
Cliente:
- Nombre
- Email (único)
- Teléfono
- RFC
- Razón social
- Tipo (mayorista, minorista, distribuidor)
- Límite de crédito
- Vendedor asignado
- Dirección principal + alternativas
- deleted_at (soft-delete)

Oportunidad:
- Cliente (FK)
- Descripción/detalle
- Monto estimado
- Estatus (prospecto, negociación, ganado, perdido)
- Fecha cierre estimada
- Probabilidad de ganar (%)
- Vendedor asignado

Interacción:
- Cliente (FK)
- Tipo (llamada, email, reunión, visita)
- Fecha y hora
- Duración
- Notas/resumen
- Usuario registra
- Resultado

Actividad:
- Cliente (FK) 
- Tipo (llamada, email, reunión, cotización)
- Descripción
- Fecha planificada
- Estado (pendiente, completada, cancelada)
- Asignado a
```

### Estado
✅ **70% Implementado** (CRUD y pipeline existen, falta análisis avanzado)

---

## 10. 🏪 MÓDULO: Portal de Cliente

**Archivo de rutas:** `routes/portal.php`  
**Controladores:** `Portal\CatalogoController`, `Portal\CarritoController`, `Portal\TicketController`, `Portal\FacturaPublicaController`  
**Propósito:** Acceso para clientes (sin autenticación requerida en parts)

### Funcionalidades
- ✅ Catálogo público de productos (búsqueda, filtros, ordenamiento)
- ✅ Carrito de compras (agregar, eliminar, editar cantidades)
- ✅ Checkout con creación automática de venta
- ✅ Historial de compras (solo para clientes autenticados)
- ✅ Solicitud de facturas
- ✅ Sistema de tickets de soporte
- ✅ Cartera (deuda del cliente)

### Rutas
| Ruta | Método | Controlador |
|------|--------|------------|
| `/catalogo` | GET | Portal\CatalogoController@index |
| `/catalogo/buscar` | GET | Portal\CatalogoController@buscar |
| `/catalogo/{id}` | GET | Portal\CatalogoController@detalle |
| `/carrito` | GET | Portal\CarritoController@index |
| `/carrito/agregar/{producto_id}` | POST | Portal\CarritoController@agregar |
| `/carrito/eliminar/{producto_id}` | DELETE | Portal\CarritoController@eliminar |
| `/carrito/checkout` | POST | Portal\CarritoController@checkout |
| `/mis-compras` | GET | Portal\CarritoController@misCcompras |
| `/mis-facturas` | GET | Portal\CatalogoController@misFacturas |
| `/solicitar-factura/{codigo}` | POST | Portal\FacturaPublicaController@solicitar |
| `/tickets` | GET | Portal\TicketController@index |
| `/tickets/create` | GET | Portal\TicketController@create |
| `/tickets` | POST | Portal\TicketController@store |
| `/tickets/{id}` | GET | Portal\TicketController@show |
| `/tickets/{id}/responder` | POST | Portal\TicketController@responder |
| `/cartera` | GET | Portal\CatalogoController@cartera |

### Datos Capturados
```
Catálogo (Producto):
- Código, nombre, descripción
- Familia, línea, color
- Precio
- Stock disponible
- Imagen principal
- Especificaciones técnicas

Carrito:
- Cliente (sesión o autenticado)
- Productos (1:N)
- Cantidad
- Subtotal
- Fecha creación

Venta (generada en checkout):
- Auto-genera póliza
- Cliente (FK)
- Productos agregados
- Total
- Crear registro auditoría

Ticket de Soporte:
- Cliente
- Asunto
- Descripción
- Estado (abierto, en_proceso, cerrado)
- Prioridad
- Respuestas (1:N)

Factura Solicitud:
- Cliente
- Venta a facturar
- Fecha solicitud
- Estado (solicitada, procesada, enviada)
- RFC a facturar
```

### Estado
✅ **85% Implementado** (Catálogo, carrito, tickets funcionan; solicitudes de factura SIN procesar)

---

## 11. 🛒 MÓDULO: Compras / Purchasing

**Archivo de rutas:** `routes/purchasing.php`  
**Controlador:** `ProveedoresController.php`  
**Propósito:** Gestión de proveedores y compras

### Funcionalidades
- ✅ CRUD de proveedores
- ❌ Órdenes de compra (NO implementado)
- ❌ Cotizaciones de proveedores (NO implementado)
- ❌ Recepción de materiales (NO implementado)
- ❌ Pagos a proveedores (NO implementado)

### Rutas
| Ruta | Método | Controlador |
|------|--------|------------|
| `/proveedores` | GET | ProveedoresController@index |
| `/proveedores/create` | GET | ProveedoresController@create |
| `/proveedores` | POST | ProveedoresController@store |
| `/proveedores/{id}` | GET | ProveedoresController@show |
| `/proveedores/{id}/edit` | GET | ProveedoresController@edit |
| `/proveedores/{id}` | PUT | ProveedoresController@update |

### Datos Capturados
```
Proveedor:
- Nombre (único)
- RFC
- Email
- Teléfono
- Dirección
- Contacto principal
- Productos que suministra
- Términos de pago
- Calificación (★★★★★)
- Estado (activo, inactivo)
```

### Estado
🟡 **30% Implementado** (Solo proveedores, falta órdenes de compra, cotizaciones, recepciones)

---

## 12. 💼 MÓDULO: Sistema / Administración

**Archivo de rutas:** `routes/system.php`  
**Controladores:** `UsuariosController`, `AdminController`, `ReportesController`  
**Propósito:** Administración del sistema

### Funcionalidades
- ✅ CRUD de usuarios con roles
- ✅ Gestión de permisos por rol
- ✅ Perfil de usuario (cambio contraseña, datos personales)
- ✅ Reportes KPI y producción con gráficas
- ✅ Auditoría integral (log de todas las acciones)
- ✅ Incidencias de producción
- ✅ Bitácora de turnos
- ✅ Exportación de reportes (PDF, Excel)

### Rutas
| Ruta | Método | Controlador |
|------|--------|------------|
| `/usuarios` | GET | UsuariosController@index |
| `/usuarios/create` | GET | UsuariosController@create |
| `/usuarios` | POST | UsuariosController@store |
| `/usuarios/{id}/edit` | GET | UsuariosController@edit |
| `/usuarios/{id}` | PUT | UsuariosController@update |
| `/usuarios/{id}/cambiar-contraseña` | POST | UsuariosController@cambiarContrasena |
| `/perfil` | GET | UsuariosController@perfil |
| `/perfil` | PUT | UsuariosController@actualizarPerfil |
| `/auditorias` | GET | AdminController@auditorias |
| `/incidencias` | GET | AdminController@incidencias |
| `/incidencias/crear` | POST | AdminController@crearIncidencia |
| `/bitacora-turnos` | GET | AdminController@bitacoraTurnos |
| `/reportes/kpi` | GET | ReportesController@kpi |
| `/reportes/produccion` | GET | ReportesController@produccion |
| `/reportes/ventas` | GET | ReportesController@ventas |

### Datos Capturados
```
Usuario:
- Nombre completo
- Email (único)
- Contraseña (bcrypt)
- Rol (admin, operador, supervisor, vendedor, cliente, contador)
- Estado (activo, inactivo)
- Último login

Incidencia:
- Descripción
- Tipo (mecánica, eléctrica, software, RH, otro)
- Severidad (baja, media, alta, crítica)
- Estado (abierta, en_proceso, resuelta, cerrada)
- Responsable asignado
- Fecha/hora inicio
- Fecha/hora resolución
- Tiempo resolución
```

### Estado
✅ **90% Implementado** (Usuarios, auditoría, reportes funcionan bien; reportes avanzados en progreso)

---

## 13. 📊 MÓDULO: Reportes y Analytics

**Archivo de rutas:** `routes/system.php` (bajo ReportesController)  
**Controlador:** `ReportesController.php`  
**Propósito:** Reportes ejecutivos y análisis

### Funcionalidades
- ✅ Reportes de producción (órdenes, máquinas, OEE)
- ✅ Reportes de ventas (por vendedor, cliente, periodo)
- ✅ KPIs en tiempo real (gráficas)
- ✅ Análisis de calidad (tasa de scrap, rechazos)
- ✅ Reportes contables (balance, flujo, presupuestos)
- ✅ Exportación en PDF y Excel

### Rutas
| Ruta | Método | Controlador |
|------|--------|------------|
| `/reportes` | GET | ReportesController@index |
| `/reportes/produccion` | GET | ReportesController@produccion |
| `/reportes/ventas` | GET | ReportesController@ventas |
| `/reportes/kpi` | GET | ReportesController@kpi |
| `/reportes/calidad` | GET | ReportesController@calidad |
| `/reportes/exportar/{tipo}/{formato}` | GET | ReportesController@exportar |

### Estado
✅ **85% Implementado**

---

## 14. 🔗 MÓDULO: API REST

**Archivo de rutas:** `routes/api.php`  
**Controladores:** `Api/*Controller.php`  
**Propósito:** Endpoints JSON para aplicaciones externas

### Endpoints
```
GET /api/dashboard/stats
GET /api/clientes
GET /api/clientes/{id}
GET /api/productos
GET /api/productos/familia/{familia}
GET /api/ventas
GET /api/materiales
POST /api/ordenes
GET /api/ordenes/{id}
```

### Estado
🟡 **50% Implementado** (Básicos funcionan, falta cobertura completa)

---

## 15. 📈 MÓDULO: Vendedor / Sales Dashboard

**Archivo de rutas:** `routes/vendedor.php`  
**Controlador:** `VendedorController.php`, `ReportesVendedorController.php`  
**Propósito:** Dashboard y reportes para vendedores

### Funcionalidades
- ✅ Panel de vendedor personalizado
- ✅ Pipeline de oportunidades (Kanban)
- ✅ Metas y comisiones visuales
- ✅ Reportes de vendedor (ventas, comisiones, clientes)
- ✅ Contactos y actividades asignadas

### Estado
✅ **75% Implementado**

---

## 16. 📅 MÓDULO: Agenda / Actividades

**Archivo de rutas:** `routes/agenda.php`  
**Propósito:** Calendario de citas y eventos

### Funcionalidades
- ✅ Calendario de citas
- ✅ Actividades programadas
- ✅ Recordatorios

### Estado
✅ **60% Implementado**

---

## 17. 📱 MÓDULO: Notificaciones

**Controlador:** `NotificacionService.php`  
**Propósito:** Sistema de notificaciones por rol

### Funcionalidades
- 🟡 Tablas de notificaciones existen (clientes, vendedores, operadores, supervisores)
- 🟡 Lógica parcial implementada
- ❌ Notificaciones en tiempo real (falta WebSocket/SSE)
- ❌ Email transaccional (falta integración SMTP completa)

### Estado
🟡 **50% Implementado**

---

## Resumen de Implementación

| # | Módulo | Estado | Completitud |
|----|--------|--------|-------------|
| 1 | 📚 Autenticación | ✅ Completo | 100% |
| 2 | 🏠 Dashboard | ✅ Completo | 100% |
| 3 | 🏭 Producción | ✅ Completo | 100% |
| 4 | 📊 Calidad | 🟡 Parcial | 70% |
| 5 | 🔧 Mantenimiento | 🟡 Parcial | 80% |
| 6 | 📦 Inventarios | 🟡 Parcial | 80% |
| 7 | 🛍️ Ventas | 🟡 Parcial | 40% |
| 8 | 💰 Contabilidad | 🟡 Parcial | 50% |
| 9 | 👥 CRM | 🟡 Parcial | 70% |
| 10 | 🏪 Portal Cliente | ✅ Completo | 85% |
| 11 | 🛒 Compras | 🟡 Mínimo | 30% |
| 12 | 💼 Sistema/Admin | ✅ Completo | 90% |
| 13 | 📊 Reportes | ✅ Completo | 85% |
| 14 | 🔗 API REST | 🟡 Parcial | 50% |
| 15 | 📈 Vendedor | 🟡 Parcial | 75% |
| 16 | 📅 Agenda | 🟡 Parcial | 60% |
| 17 | 📱 Notificaciones | 🟡 Parcial | 50% |

**Total: ~62% del proyecto implementado y funcional**

---

## Prioridades de Completitud

### 🔴 CRÍTICOS PARA PRODUCCIÓN
1. ✨ Completar módulo **Ventas** (métodos pago, CFDI, folio, stock)
2. ✨ Completar módulo **Facturación** (generar CFDI automático)
3. ✨ Completar módulo **Compras** (órdenes, cotizaciones, recepciones)
4. ✨ Control de **Cartera** (crediticio)

### 🟡 IMPORTANTES
5. Módulo **Trazabilidad** avanzada (8 tablas especializadas)
6. **RH** completo (capacitaciones, permisos, vacaciones)
7. **Control de proceso** (temperatura, presión, tiempo automático)

### 🟢 MEJORAS
8. WebSocket para notificaciones en tiempo real
9. Mobile app
10. Dashboard de IoT (sensores de máquinas)
