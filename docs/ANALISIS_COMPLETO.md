# Análisis Completo del Proyecto Plasti Frus

**Fecha:** 2 de junio de 2026  
**Versión:** 3.0 (Análisis Exhaustivo)  
**Estado General:** 62% implementado y funcional

---

## TABLA DE CONTENIDOS

1. [Visión General](#1-visión-general)
2. [Estado por Módulo](#2-estado-por-módulo)
3. [Funcionalidades Completadas](#3-funcionalidades-completadas)
4. [Funcionalidades Parciales](#4-funcionalidades-parciales)
5. [Funcionalidades Faltantes](#5-funcionalidades-faltantes)
6. [Carencias Críticas](#6-carencias-críticas-para-producción)
7. [Recomendaciones Prioritarias](#7-recomendaciones-prioritarias)
8. [Inversión de Desarrollo Estimada](#8-inversión-de-desarrollo-estimada)
9. [Timeline de Completitud](#9-timeline-de-completitud)

---

## 1. Visión General

### 1.1 Descripción del Proyecto

**Plasti Frus** es un sistema ERP/MRP integral para la gestión completa de una **fábrica de inyección de plásticos** con capacidades de:

- **Producción:** Órdenes, recetas, máquinas, moldes, control de mermas
- **Calidad:** Inspecciones, rechazos, parámetros de control
- **Inventarios:** Kardex de materiales, ubicaciones, alertas de stock
- **Ventas y CRM:** Pipeline Kanban, clientes, comisiones, oportunidades
- **Contabilidad:** Plan de cuentas, pólizas, balances, flujo de efectivo
- **Portal de Cliente:** Catálogo, carrito, compras, solicitud de facturas, tickets
- **Mantenimiento:** Plan preventivo, paros, calibraciones, refacciones
- **Sistema:** Auditoría, reportes, usuarios, permisos

### 1.2 Tecnología

```
Backend:        PHP 8.3 (MVC nativo, sin frameworks)
Base de Datos:  MySQL 8.0+ / MariaDB 11.4+ (130+ tablas, 3NF)
Frontend:       Bootstrap 5.3, DataTables 1.13, Chart.js 4
ORM:            Active Record propio con PDO parametrizado
Testing:        PHPUnit 11 (63 tests, 127 assertions)
Deployment:     Docker Compose (PHP-Apache + MariaDB + PhpMyAdmin)
Dependencias:   Composer (PHPMailer, Dompdf, PhpSpreadsheet, phpdotenv)
```

### 1.3 Estadísticas Generales

| Métrica | Valor |
|---------|-------|
| **Líneas de código** | ~45,000 |
| **Controladores** | 54+ |
| **Modelos** | 31 |
| **Servicios** | 9 |
| **Vistas** | ~70 |
| **Tablas BD** | 130+ |
| **Migraciones** | 12+ |
| **Tests** | 63 |
| **Documentación** | 5 archivos .md |
| **Módulos** | 17 |
| **Roles** | 6 |

---

## 2. Estado por Módulo

### Gráfica de Implementación

```
Autenticación    [████████████████████████████████] 100% ✅
Dashboard        [████████████████████████████████] 100% ✅
Producción       [████████████████████████████████] 100% ✅
Calidad          [██████████████████░░░░░░░░░░░░░░] 70% 🟡
Mantenimiento    [██████████████████░░░░░░░░░░░░░░] 80% 🟡
Inventarios      [██████████████████░░░░░░░░░░░░░░] 80% 🟡
Ventas           [████████░░░░░░░░░░░░░░░░░░░░░░░░] 40% 🔴
Contabilidad     [███████████░░░░░░░░░░░░░░░░░░░░░] 50% 🟡
CRM              [█████████████░░░░░░░░░░░░░░░░░░░░] 70% 🟡
Portal Cliente   [███████████████████░░░░░░░░░░░░░░] 85% ✅
Compras          [███░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░] 30% 🔴
Sistema/Admin    [██████████████████░░░░░░░░░░░░░░░░] 90% ✅
Reportes         [████████████████░░░░░░░░░░░░░░░░░░] 85% ✅
API REST         [███████░░░░░░░░░░░░░░░░░░░░░░░░░░░] 50% 🟡
Vendedor         [████████████░░░░░░░░░░░░░░░░░░░░░░] 75% 🟡
Agenda           [█████████░░░░░░░░░░░░░░░░░░░░░░░░░░] 60% 🟡
Notificaciones   [██████░░░░░░░░░░░░░░░░░░░░░░░░░░░░░] 50% 🟡
                   └─ 0% ──────────────────────────────── 100%
```

**Porcentaje Promedio: 62%**

---

## 3. Funcionalidades Completadas

### 3.1 Módulo de Autenticación (100%)
✅ Login con sesión y bcrypt  
✅ Registro de usuarios  
✅ Logout seguro  
✅ Recuperación de contraseña por email  
✅ Protección CSRF  
✅ Control de horario laboral para operadores  

**Impacto:** Crítico — Todos acceden por este módulo

---

### 3.2 Dashboard (100%)
✅ 6 dashboards personalizados por rol (admin, operador, supervisor, vendedor, cliente, contador)  
✅ Widgets KPI en tiempo real  
✅ Gráficas de producción, ventas, stock  
✅ Notificaciones e incidencias  
✅ Acceso rápido a módulos principales  

**Impacto:** Crítico — Punto de entrada principal

---

### 3.3 Producción (100%)

#### Materiales
✅ CRUD completo  
✅ Asociación con proveedores  
✅ Control de stock y punto de reorden  
✅ Alertas automáticas de bajo stock  
✅ Historial de cambios (auditoría)  

#### Productos
✅ CRUD con clasificación (familias, líneas, colores)  
✅ Especificaciones técnicas (peso, dimensiones)  
✅ Precios de venta  
✅ Imágenes y descripción  

#### Recetas
✅ CRUD de recetas de producción  
✅ Asociación de materiales con porcentajes  
✅ Validación de consumo  
✅ Asignación de máquina recomendada  
✅ Versionado y historial  

#### Órdenes de Producción
✅ CRUD con flujo de estados (pendiente → en_proceso → completada)  
✅ Asignación de máquina, operador, turno  
✅ Registro de mermas/scrap  
✅ Cálculo automático de consumo de materiales  
✅ Actualización de kardex al completar  
✅ Trazabilidad completa  
✅ Gráfica de seguimiento  

#### Máquinas
✅ CRUD de máquinas  
✅ OEE (Overall Equipment Effectiveness) calculado en tiempo real  
✅ Moldes asignados  
✅ Consumo de energía  
✅ Paros y calibraciones  
✅ Plan de mantenimiento preventivo  

#### Moldes
✅ CRUD de moldes  
✅ Asignación a máquinas  
✅ Ciclos acumulados y restantes  
✅ Historial de uso  

**Impacto:** Crítico — Núcleo del negocio

---

### 3.4 Calidad (70%)

**Implementado:**
✅ Registro de inspecciones  
✅ Parámetros de control (temperatura, presión, tiempo)  
✅ Registro de rechazos con causas  
✅ Cálculo de tasa de scrap  
✅ Auditorías de QA  

**Faltante:**
❌ Análisis avanzado de defectos  
❌ Reportes de tendencias de calidad  
❌ Control estadístico de proceso (SPC)  

**Impacto:** Alto — Fundamental para cumplimiento de estándares

---

### 3.5 Mantenimiento (80%)

**Implementado:**
✅ Plan de mantenimiento preventivo  
✅ Registro de mantenimientos realizados  
✅ Bitácora de paros (causa, duración, responsable)  
✅ Seguimiento de refacciones  
✅ Historial por máquina  

**Faltante:**
❌ Alertas automáticas de vencimiento de mantenimiento  
❌ Integración IoT con sensores de máquinas  
❌ Predicción de fallos (AI)  

**Impacto:** Alto — Previene paros inesperados

---

### 3.6 Inventarios (80%)

**Implementado:**
✅ Kardex detallado (entradas y salidas)  
✅ Valuación de inventario (FIFO, LIFO, promedio)  
✅ Alertas de stock bajo  
✅ Ubicaciones en almacén  
✅ Historial completo de movimientos  

**Faltante:**
❌ Conteo cíclico automatizado  
❌ Ajustes por robo/merma  
❌ Integración con códigos de barras  

**Impacto:** Alto — Clave para control de costos

---

### 3.7 Portal de Cliente (85%)

**Implementado:**
✅ Catálogo público (búsqueda, filtros, ordenamiento)  
✅ Carrito de compras funcional  
✅ Checkout con creación automática de venta  
✅ Historial de compras  
✅ Sistema de tickets de soporte  
✅ Cartera (deuda del cliente)  
✅ Wishlist  

**Faltante:**
❌ Solicitud de facturas NO SE PROCESA (botón sin efecto)  
❌ Portal de seguimiento de facturas  
❌ Descuentas por volumen  

**Impacto:** Alto — Retención de clientes

---

### 3.8 Sistema/Admin (90%)

**Implementado:**
✅ CRUD de usuarios con roles  
✅ Gestión de permisos granulares  
✅ Perfil de usuario  
✅ Auditoría integral (50,000+ registros)  
✅ Reportes KPI y producción  
✅ Incidencias de producción  
✅ Bitácora de turnos  
✅ Exportación PDF y Excel  

**Faltante:**
❌ Panel de estadísticas de acceso  
❌ Análisis de datos avanzado  

**Impacto:** Crítico — Control y transparencia

---

### 3.9 Reportes (85%)

✅ Reportes de producción (órdenes, máquinas, OEE)  
✅ Reportes de ventas (por vendedor, cliente, periodo)  
✅ KPIs en tiempo real con gráficas  
✅ Análisis de calidad (tasa de scrap, rechazos)  
✅ Reportes contables (balance, flujo, presupuestos)  
✅ Exportación en PDF y Excel  

**Impacto:** Alto — Toma de decisiones

---

## 4. Funcionalidades Parciales

### 4.1 Ventas (40%) 🔴 CRÍTICA

| Aspecto | Estado | Descripción |
|---------|--------|------------|
| CRUD | ✅ | Crear, listar, editar, eliminar ventas |
| Validación | ❌ | **SIN validación de datos** |
| Folio único | ❌ | **NO hay número de venta único** |
| Stock | ❌ | **NO valida disponibilidad** |
| 1 producto | 🔴 | **SIN soporte para múltiples productos** |
| Método pago | ❌ | **NO registra forma de pago** |
| Descuentos | ❌ | **NO calcula descuentos** |
| Impuestos | ❌ | **Impuesto hardcodeado** |
| Factura | ❌ | **NO genera CFDI** |

**Crítica porque:** Sin este módulo completo, NO se pueden facturar ventas

**Carencias:**
```
Venta actual:
- Cliente + Producto (UNO) + Cantidad + Precio

Debería ser:
- Folio único (AUT-2026-00001)
- Cliente
- 1:N Productos (líneas múltiples)
- Descuentos por línea/total
- Impuestos calculados (IVA, IEPS)
- Método de pago (efectivo, cheque, tarjeta, transferencia)
- Límite de crédito validado
- Control de stock antes de vender
- CFDI generado automáticamente
```

**Impacto:** CRÍTICO — Ingresos de la empresa

---

### 4.2 Facturación (30%) 🔴 CRÍTICA

| Aspecto | Estado | Descripción |
|--------|--------|------------|
| Tabla | ✅ | `facturas` existe |
| Venta → Factura | ❌ | **NO hay integración** |
| CFDI | ❌ | **NO se genera comprobante fiscal** |
| Solicitud | 🟡 | Portal permite solicitar, pero **NO procesa** |
| RFC | ✅ | Existe captura de RFC |
| Folio SAT | ❌ | **NO integración con SAT** |
| Timbrado | ❌ | **NO timbra electrónicamente** |

**Carencias:**
```
Flujo actual:
Cliente solicita factura → [SIN PROCESAMIENTO]

Flujo deseado:
Cliente solicita factura
  ↓
Admin ve solicitud en panel
  ↓
Admin genera factura (CFDI XML)
  ↓
Sistema timbra con SAT
  ↓
Factura en email a cliente
```

**Impacto:** CRÍTICO — Obligación fiscal

---

### 4.3 Contabilidad (50%)

**Implementado:**
✅ Plan de cuentas (150+ cuentas)  
✅ Pólizas contables (diario, ingresos, egresos)  
✅ Reportes: Balance, Estado de Resultados, Balanza  
✅ Libro Diario y Libro Mayor  
✅ Flujo de Efectivo  
✅ Presupuestos  

**Faltante:**
❌ Integración con ventas (no registra automáticamente)  
❌ Integración con compras (no registra automáticamente)  
❌ Cierres contables (solo registro, sin lógica de cierre)  
❌ Impuestos (ISR, IVA desglosado)  

**Impacto:** Alto — Obligación legal

---

### 4.4 CRM (70%)

**Implementado:**
✅ CRUD de clientes  
✅ Pipeline Kanban de oportunidades  
✅ Interacciones (llamadas, emails, reuniones)  
✅ Actividades y tareas  
✅ Historial completo  

**Faltante:**
❌ Scoring de clientes (probabilidad de venta)  
❌ Análisis de churn (predicción de pérdida)  
❌ Automaciones (email, tareas automáticas)  
❌ Integración con email marketing  

**Impacto:** Medio — Mejora retención

---

### 4.5 Compras (30%) 🔴 CRÍTICA

| Aspecto | Estado | Descripción |
|--------|--------|------------|
| Proveedores | ✅ | CRUD funcional |
| Órdenes compra | ❌ | **Tablas existen, SIN implementar** |
| Cotizaciones | ❌ | **Tablas existen, SIN implementar** |
| Recepciones | ❌ | **SIN control de recepciones** |
| Devoluciones | 🟡 | Tabla existe, lógica parcial |
| Pagos | ❌ | **NO hay control de pagos a proveedores** |

**Crítica porque:** Sin órdenes de compra, no hay control de adquisiciones

**Carencias:**
```
Tablas vacías:
- ordenes_compra
- ordenes_compra_materiales
- ordenes_compra_refacciones
- cotizaciones_clientes
- cotizaciones_proveedores
- embarques
```

**Impacto:** CRÍTICO — Ciclo de operaciones

---

### 4.6 API REST (50%)

**Implementado:**
✅ Endpoints básicos  
✅ Autenticación token  
✅ Response JSON  

**Faltante:**
❌ Cobertura completa de módulos  
❌ Documentación Swagger  
❌ Rate limiting  
❌ Versionamiento  

**Impacto:** Medio — Aplicaciones externas

---

### 4.7 Notificaciones (50%)

**Implementado:**
✅ Tablas de notificaciones (4 por rol)  
✅ Lógica básica de inserción  

**Faltante:**
❌ Notificaciones en tiempo real (WebSocket/SSE)  
❌ Email transaccional  
❌ SMS  
❌ Push notifications  

**Impacto:** Medio — Experiencia de usuario

---

## 5. Funcionalidades Faltantes

### 5.1 No Implementadas (0%)

| Categoría | Tablas/Módulos | Descripción |
|-----------|-----------------|------------|
| **RH** | 8 tablas | Capacitaciones, permisos, vacaciones, evaluaciones, ausencias, asignaciones de turnos |
| **Control de Proceso** | 3 tablas | Automatización de lectura de temperatura, presión, tiempo de sensores |
| **Trazabilidad Avanzada** | 10 tablas | Rastreo especializado por operador, máquina, molde, material, etc. |
| **Métodos de Pago** | 1 tabla | Formas de pago disponibles |
| **Cotizaciones Clientes** | 2 tablas | Cotizaciones a clientes |
| **Embarques** | 2 tablas | Gestión de embarques y logística |

---

## 6. Carencias Críticas para Producción

### 6.1 VENTAS
```
🔴 BLOQUEADOR CRÍTICO

El módulo de ventas es INSUFICIENTE para operación en producción:

1. Sin folio único
   - Impacto: No hay trazabilidad de ventas
   - Riesgo: Auditoría, duplicados

2. Solo 1 producto por venta
   - Impacto: No se puede vender múltiples items
   - Riesgo: Cliente no puede hacer orden completa
   - Solución: 2 días

3. Sin validación de stock
   - Impacto: Se vende sin disponibilidad
   - Riesgo: Overselling
   - Solución: 1 día

4. Sin método de pago
   - Impacto: No se registra forma de cobro
   - Riesgo: Contabilidad inexacta
   - Solución: 1 día

5. Sin CFDI/Factura
   - Impacto: No hay comprobante fiscal
   - Riesgo: Incumplimiento legal (SAT)
   - Solución: 5 días

6. Sin impuestos/descuentos
   - Impacto: Cálculos incorrectos
   - Riesgo: Diferencias financieras
   - Solución: 2 días
```

---

### 6.2 FACTURACIÓN
```
🔴 BLOQUEADOR CRÍTICO

El módulo de facturación es REQUISITO LEGAL:

1. Sin timbrado SAT
   - Riesgo legal: NO cumple con obligaciones fiscales
   - Multa: $1,000-$5,000 MXN por factura

2. Solicitudes sin procesar
   - Impacto: Cliente no puede obtener factura
   - Riesgo: Pérdida de cliente

3. Sin CFDI
   - Riesgo legal: Comprobante inválido
   - Multa: Hasta $300,000 MXN
```

---

### 6.3 COMPRAS
```
🟡 BLOQUEADOR IMPORTANTE

Sin módulo de compras:

1. No hay control de adquisiciones
2. Kardex no se actualiza con compras
3. No se puede medir lead time de proveedores
4. Presupuesto de compras sin visibilidad
```

---

## 7. Recomendaciones Prioritarias

### 7.1 URGENTES (0-2 semanas)

```
1. ✨ COMPLETAR VENTAS (2 días)
   ├─ Implementar folio único
   ├─ Soportar múltiples productos (1:N)
   ├─ Validar stock antes de vender
   ├─ Registrar método de pago
   └─ Tests para flujo completo

2. ✨ COMPLETAR FACTURACIÓN (5 días)
   ├─ Integrar Venta → Factura
   ├─ Generar CFDI XML
   ├─ Integrar servicio de timbrado (Comexo, Facilibro, etc)
   ├─ Enviar factura por email
   └─ Procesar solicitudes del portal

3. ✨ VALIDACIÓN DE CARTERA (1 día)
   ├─ Verificar límite de crédito antes de venta
   ├─ Bloquear si no hay disponibilidad
   └─ Dashboard de cartera
```

---

### 7.2 ALTOS (2-4 semanas)

```
4. ✨ MÓDULO DE COMPRAS (10 días)
   ├─ Órdenes de compra
   ├─ Cotizaciones de proveedores
   ├─ Recepciones de materiales
   ├─ Actualizaciones de kardex
   └─ Integración contable

5. ✨ CONTABILIDAD AUTOMÁTICA (3 días)
   ├─ Venta → Póliza contable
   ├─ Compra → Póliza contable
   ├─ Validación de impuestos
   └─ Cierres de periodo

6. ✨ TRAZABILIDAD AVANZADA (7 días)
   ├─ Operador por orden
   ├─ Máquina por orden
   ├─ Molde por orden
   ├─ Material por orden
   └─ Dashboard de trazabilidad
```

---

### 7.3 IMPORTANTES (1 mes)

```
7. ✨ NOTIFICACIONES EN TIEMPO REAL (5 días)
   ├─ WebSocket o Server-Sent Events
   ├─ Notificación de órdenes completadas
   ├─ Alerta de stock bajo
   └─ Dashboard live

8. ✨ RH BÁSICO (8 días)
   ├─ Asignación de turnos
   ├─ Control de asistencia
   ├─ Vacaciones y permisos
   └─ Evaluaciones

9. ✨ CONTROL DE PROCESO (7 días)
   ├─ Automatización de lectura de sensores
   ├─ Gráfica de control SPC
   └─ Alertas de desviación
```

---

## 8. Inversión de Desarrollo Estimada

### 8.1 Completar a 90% (Producción Ready)

| Tarea | Esfuerzo | Costo Estimado |
|-------|----------|----------------|
| Completar Ventas | 2 días | $1,000-1,500 |
| Facturación CFDI | 5 días | $2,500-3,500 |
| Compras | 10 días | $5,000-7,500 |
| Contabilidad Automática | 3 días | $1,500-2,000 |
| Trazabilidad | 7 días | $3,500-5,000 |
| Testing & QA | 5 días | $2,500-3,500 |
| **TOTAL** | **32 días** | **$16,500-22,500** |

**Estimación:** 6-8 semanas con equipo de 1 desarrollador senior

### 8.2 Completar a 100% (Enterprise)

Agregar:
- RH completo (+8 días)
- Control de proceso IoT (+7 días)
- Notificaciones real-time (+5 días)
- Mobile app (+20 días)
- API REST completa (+5 días)

**Estimación adicional:** +45 días (9 semanas más)

---

## 9. Timeline de Completitud

### 9.1 Estado Actual (62%)

```
Semana 1-2   [████████████████████] 100%
├─ Auth ✅
├─ Dashboard ✅
├─ Producción ✅
└─ Portal (85%)

Semana 3-4   [████████░░░░░░░░░░░░] 40%
├─ Ventas 🔴 BLOQUEADOR
├─ Compras 🔴 BLOQUEADOR
└─ Contabilidad 🟡 Parcial
```

### 9.2 Target en 8 Semanas (90%)

```
CARRIL 1: CRÍTICOS (Semanas 1-2)
├─ Completar Ventas (2d)
├─ Completar Facturación CFDI (5d)
└─ Validar Cartera (1d)

CARRIL 2: IMPORTANTES (Semanas 3-4)
├─ Módulo Compras (10d)
├─ Contabilidad Automática (3d)
└─ Testing integral (3d)

CARRIL 3: VALOR AGREGADO (Semanas 5-6)
├─ Trazabilidad Avanzada (7d)
├─ Reportes ejecutivos (3d)
└─ Optimización de performance (2d)

CARRIL 4: PULIDO (Semanas 7-8)
├─ Notificaciones real-time (5d)
├─ Mobile responsive (3d)
├─ Security audit (3d)
└─ Documentación (2d)
```

### 9.3 Target en 17 Semanas (100%)

Agregar después de 90%:
- RH completo (8d)
- Control de proceso (7d)
- Notificaciones avanzadas (5d)
- Mobile app (20d)
- Data warehouse (15d)

---

## 10. Conclusión y Recomendación

### 10.1 Veredicto

| Aspecto | Evaluación |
|---------|-----------|
| **Producción** | 🔴 NO LISTO — Faltanventas, facturación, compras |
| **Calidad de código** | ✅ BUENA — MVC limpio, tests, auditoría |
| **Escalabilidad** | ✅ BUENA — BD normalizada, índices, arquitectura modular |
| **Seguridad** | ✅ BUENA — Bcrypt, CSRF, prepared statements, auditoría |
| **Documentación** | ✅ COMPLETA — 5 docs exhaustivos |

### 10.2 Recomendación Final

**Status:** 🟡 PARCIALMENTE FUNCIONAL

**Puede usar para:**
- ✅ Demostración/PoC
- ✅ Entrenamiento de usuarios
- ✅ Desarrollo en paralelo
- ✅ Producción en módulos no críticos (Producción, Calidad, Mantenimiento)

**NO puede usar para:**
- ❌ Facturación/ventas sin completar
- ❌ Operación integral de empresa
- ❌ Ciclo de compras-venta-facturación

### 10.3 Plan de Acción

**RECOMENDACIÓN:** Invertir 6-8 semanas para llevar a 90% de completitud

**Fases:**
1. **Fase 1 (2 semanas):** Completar Ventas + Facturación CFDI
2. **Fase 2 (2 semanas):** Módulo Compras + Contabilidad Automática
3. **Fase 3 (2 semanas):** Trazabilidad + Testing + Optimización
4. **Fase 4 (2 semanas):** Notificaciones + Pulido + Go-Live

**Inversión total:** $16,500-22,500 USD (o 32 días-hombre)

**ROI:** Operación completa de fabrica, compliance fiscal, control total de costos

---

## APÉNDICE: Mapa de Carencias Técnicas

```
CRÍTICAS:
├─ Ventas::folio_unico
├─ Ventas::multiple_productos
├─ Ventas::stock_validation
├─ Ventas::metodo_pago
├─ Facturas::cfdi_generation
├─ Facturas::sat_timbrado
├─ Compras::ordenes_compra
└─ Compras::recepciones

IMPORTANTES:
├─ Contabilidad::venta_to_poliza
├─ Contabilidad::compra_to_poliza
├─ Cartera::limite_credito
├─ Trazabilidad::operadores
├─ Trazabilidad::maquinas_moldes
└─ Notificaciones::real_time

VALOR AGREGADO:
├─ RH::turnos_asistencia
├─ IoT::sensores_temperatura
├─ Analytics::predictive_sales
└─ Mobile::app_responsive
```

---

**Análisis completado:** 2 junio 2026  
**Próxima revisión:** Junio 16, 2026 (después de implementar Fase 1)

---

*Este documento es confidencial y forma parte de la evaluación técnica integral del proyecto Plasti Frus.*
