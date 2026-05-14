CREATE DATABASE fabrica_plasticos;
USE fabrica_plasticos;

-- Tablas de dominio sin dependencias externas
CREATE TABLE roles (
    id_rol INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(50) NOT NULL,
    descripcion TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE cedes (
    id_cedes INT PRIMARY KEY AUTO_INCREMENT,
    nombre_cede VARCHAR(150),
    ubicacion VARCHAR(150),
    responsable VARCHAR(20),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE proveedores (
    id_proveedor INT PRIMARY KEY AUTO_INCREMENT,
    razon_social VARCHAR(150),
    rfc VARCHAR(20),
    tipo_material VARCHAR(100),
    telefono VARCHAR(20),
    correo VARCHAR(120),
    ciudad VARCHAR(100),
    estado VARCHAR(100),
    pais VARCHAR(100),
    sector VARCHAR(100),
    estatus VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE clientes (
    id_cliente INT PRIMARY KEY AUTO_INCREMENT,
    razon_social VARCHAR(150),
    rfc VARCHAR(20),
    ciudad VARCHAR(100),
    estado VARCHAR(100),
    telefono VARCHAR(20),
    correo VARCHAR(120),
    sector VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE empleados (
    id_empleado INT PRIMARY KEY AUTO_INCREMENT,
    nombre VARCHAR(150),
    apellido_paterno VARCHAR(100),
    apellido_materno VARCHAR(100),
    puesto VARCHAR(100),
    departamento VARCHAR(100),
    telefono VARCHAR(20),
    correo VARCHAR(120),
    fecha_contratacion DATE,
    estatus VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE usuarios (
    id_usuario INT AUTO_INCREMENT PRIMARY KEY,
    id_empleado INT,
    nombre_usuario VARCHAR(50) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    id_rol INT,
    activo BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (id_empleado) REFERENCES empleados(id_empleado),
    FOREIGN KEY (id_rol) REFERENCES roles(id_rol)
);

CREATE TABLE maquinas (
    id_maquina INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(150),
    modelo VARCHAR(100),
    numero_serie VARCHAR(100),
    estatus VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE productos (
    id_producto INT PRIMARY KEY AUTO_INCREMENT,
    codigo VARCHAR(50),
    nombre VARCHAR(150),
    familia VARCHAR(100),
    linea VARCHAR(100),
    color VARCHAR(50),
    peso_unitario_grs DECIMAL(10,2),
    dimensiones VARCHAR(150),
    descripcion_comercial TEXT,
    publicar_web BOOLEAN,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE moldes (
    id_molde INT PRIMARY KEY AUTO_INCREMENT,
    nombre_molde VARCHAR(150),
    numero_cavidades INT,
    material_molde VARCHAR(100),
    vida_util_golpes BIGINT,
    ciclos_acumulados BIGINT,
    estatus VARCHAR(50),
    id_cedes INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (id_cedes) REFERENCES cedes(id_cedes)
);

CREATE TABLE materiales (
    id_material INT PRIMARY KEY AUTO_INCREMENT,
    id_proveedor INT,
    tipo VARCHAR(50),
    nombre VARCHAR(150),
    presentacion VARCHAR(100),
    unidad_medida VARCHAR(20),
    stock_actual_kg DECIMAL(10,2),
    punto_reorden_kg DECIMAL(10,2),
    lote_recepcion VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (id_proveedor) REFERENCES proveedores(id_proveedor),
    INDEX idx_id_proveedor (id_proveedor)
);

-- Recetas
CREATE TABLE recetas_cabecera (
    id_receta_cabe INT PRIMARY KEY AUTO_INCREMENT,
    id_producto INT,
    id_maquina INT,
    version VARCHAR(20),
    fecha_version DATE,
    temperatura_inyeccion_C INT,
    presion_inyeccion_bar INT,
    tiempo_enfriamiento_s INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (id_producto) REFERENCES productos(id_producto),
    FOREIGN KEY (id_maquina) REFERENCES maquinas(id_maquina),
    INDEX idx_id_producto (id_producto),
    INDEX idx_id_maquina (id_maquina)
);

CREATE TABLE recetas_detalle (
    id_receta_detalle INT PRIMARY KEY AUTO_INCREMENT,
    id_receta_cabe INT,
    id_material INT,
    porcentaje_peso DECIMAL(10,2),
    tolerancia_percent DECIMAL(10,2),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (id_receta_cabe) REFERENCES recetas_cabecera(id_receta_cabe),
    FOREIGN KEY (id_material) REFERENCES materiales(id_material),
    INDEX idx_id_receta_cabe (id_receta_cabe),
    INDEX idx_id_material (id_material)
);

-- Órdenes de producción
CREATE TABLE ordenes_cabecera (
    id_orden_cabe INT PRIMARY KEY AUTO_INCREMENT,
    id_producto INT,
    id_receta INT,
    id_molde INT,
    id_maquina INT,
    cantidad_planificada INT,
    cantidad_real_buenas INT,
    fecha DATE,
    turno VARCHAR(30),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (id_producto) REFERENCES productos(id_producto),
    FOREIGN KEY (id_receta) REFERENCES recetas_cabecera(id_receta_cabe),
    FOREIGN KEY (id_molde) REFERENCES moldes(id_molde),
    FOREIGN KEY (id_maquina) REFERENCES maquinas(id_maquina),
    INDEX idx_id_producto (id_producto),
    INDEX idx_id_receta (id_receta),
    INDEX idx_id_molde (id_molde),
    INDEX idx_id_maquina (id_maquina)
);

CREATE TABLE ordenes_merma (
    id_orden_merma INT AUTO_INCREMENT PRIMARY KEY,
    id_orden_cabe INT,
    tipo_merma VARCHAR(100),
    cantidad_kg DECIMAL(10,2),
    destino VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (id_orden_cabe) REFERENCES ordenes_cabecera(id_orden_cabe),
    INDEX idx_id_orden_cabe (id_orden_cabe)
);

-- Plan de mantenimiento (unificado, incluye estatus)
CREATE TABLE plan_mantenimiento (
    id_plan_mantenimiento INT PRIMARY KEY AUTO_INCREMENT,
    id_maquina INT,
    fecha_programada DATE,
    tipo_mantenimiento VARCHAR(100),
    descripcion TEXT,
    frecuencia_horas DECIMAL(10,2),
    ultimo_mantenimiento DATE,
    tecnico_responsable VARCHAR(20),
    estatus VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (id_maquina) REFERENCES maquinas(id_maquina),
    INDEX idx_id_maquina (id_maquina)
);

-- Tablas de producción, calidad y trazabilidad adicionales
CREATE TABLE turno_produccion (
    id_turno INT PRIMARY KEY AUTO_INCREMENT,
    id_empleado INT,
    id_cede INT,
    nombre_turno VARCHAR(50),
    hora_inicio TIME,
    hora_fin TIME,
    descripcion TEXT,
    estatus VARCHAR(50),
    FOREIGN KEY (id_empleado) REFERENCES empleados(id_empleado),
    FOREIGN KEY (id_cede) REFERENCES cedes(id_cedes),
    INDEX idx_id_empleado (id_empleado),
    INDEX idx_id_cede (id_cede)
);

CREATE TABLE asignar_operador (
    id_asignacion INT PRIMARY KEY AUTO_INCREMENT,
    id_empleado INT,
    id_maquina INT,
    id_turno INT,
    fecha DATE,
    actividad_desarrollada VARCHAR(150),
    FOREIGN KEY (id_empleado) REFERENCES empleados(id_empleado),
    FOREIGN KEY (id_maquina) REFERENCES maquinas(id_maquina),
    FOREIGN KEY (id_turno) REFERENCES turno_produccion(id_turno),
    INDEX idx_id_empleado (id_empleado),
    INDEX idx_id_maquina (id_maquina),
    INDEX idx_id_turno (id_turno)
);

CREATE TABLE costo_produccion (
    id_costo INT PRIMARY KEY AUTO_INCREMENT,
    id_orden_cabe INT,
    costo_materiales DECIMAL(10,2),
    costo_energia DECIMAL(10,2),
    costo_mano_obra DECIMAL(10,2),
    costo_total DECIMAL(10,2),
    costo_unitario DECIMAL(10,2),
    fecha_calculo DATE,
    FOREIGN KEY (id_orden_cabe) REFERENCES ordenes_cabecera(id_orden_cabe),
    INDEX idx_id_orden_cabe (id_orden_cabe)
);

CREATE TABLE indicadores_kpi (
    id_kpi VARCHAR(10) PRIMARY KEY,
    indicador VARCHAR(150),
    valor DECIMAL(10,2),
    unidad VARCHAR(30),
    fecha_calculo DATE,
    objetivo DECIMAL(10,2),
    estatus VARCHAR(50)
);

CREATE TABLE auditorias_kpi (
    id_auditoria VARCHAR(10) PRIMARY KEY,
    id_empleado INT,
    id_kpi VARCHAR(10),
    fecha_auditoria DATE,
    auditor VARCHAR(20),
    hallazgos TEXT,
    acciones_correctivas TEXT,
    estatus VARCHAR(50),
    FOREIGN KEY (id_kpi) REFERENCES indicadores_kpi(id_kpi),
    FOREIGN KEY (id_empleado) REFERENCES empleados(id_empleado),
    INDEX idx_id_kpi (id_kpi),
    INDEX idx_id_empleado (id_empleado)
);

CREATE TABLE auditorias_calidad (
    id_auditoria_calidad VARCHAR(10) PRIMARY KEY,
    id_producto INT,
    fecha_auditoria DATE,
    auditor VARCHAR(20),
    hallazgos TEXT,
    acciones_correctivas TEXT,
    estatus VARCHAR(50),
    FOREIGN KEY (id_producto) REFERENCES productos(id_producto),
    INDEX idx_id_producto (id_producto)
);

CREATE TABLE ventas (
    id_venta INT PRIMARY KEY AUTO_INCREMENT,
    id_cliente INT,
    id_producto INT,
    cantidad_vendida INT,
    fecha_venta DATE,
    precio_unitario DECIMAL(10,2),
    moneda VARCHAR(20),
    condiciones_pago TEXT,
    estatus VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (id_cliente) REFERENCES clientes(id_cliente),
    FOREIGN KEY (id_producto) REFERENCES productos(id_producto),
    INDEX idx_id_cliente (id_cliente),
    INDEX idx_id_producto (id_producto)
);

CREATE TABLE embarques (
    id_embarque VARCHAR(10) PRIMARY KEY,
    id_venta INT,
    fecha_salida DATE,
    transportista VARCHAR(150),
    placas_unidad VARCHAR(20),
    destino VARCHAR(120),
    estatus VARCHAR(50),
    fecha_entrega_estimada DATE,
    FOREIGN KEY (id_venta) REFERENCES ventas(id_venta),
    INDEX idx_id_venta (id_venta)
);

CREATE TABLE facturas (
    id_factura INT PRIMARY KEY AUTO_INCREMENT,
    id_venta INT,
    fecha_emision DATE,
    fecha_vencimiento DATE,
    monto_total DECIMAL(10,2),
    moneda VARCHAR(20),
    estatus VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (id_venta) REFERENCES ventas(id_venta),
    INDEX idx_id_venta (id_venta)
);

CREATE TABLE cuentas_por_cobrar (
    id_cuenta INT PRIMARY KEY AUTO_INCREMENT,
    id_factura INT,
    id_cliente INT,
    monto DECIMAL(10,2),
    moneda VARCHAR(20),
    fecha_emision DATE,
    fecha_vencimiento DATE,
    dias_atraso INT,
    estatus VARCHAR(50),
    FOREIGN KEY (id_factura) REFERENCES facturas(id_factura),
    FOREIGN KEY (id_cliente) REFERENCES clientes(id_cliente),
    INDEX idx_id_factura (id_factura),
    INDEX idx_id_cliente (id_cliente)
);

CREATE TABLE cuentas_por_pagar (
    id_cuenta INT PRIMARY KEY AUTO_INCREMENT,
    id_proveedor INT,
    monto DECIMAL(10,2),
    moneda VARCHAR(20),
    fecha_emision DATE,
    fecha_vencimiento DATE,
    dias_atraso INT,
    estatus VARCHAR(50),
    FOREIGN KEY (id_proveedor) REFERENCES proveedores(id_proveedor),
    INDEX idx_id_proveedor (id_proveedor)
);

CREATE TABLE refacciones_maquinas (
    id_refaccion INT PRIMARY KEY AUTO_INCREMENT,
    id_maquina INT,
    id_proveedor INT,
    nombre_refaccion VARCHAR(150),
    descripcion TEXT,
    stock_actual INT,
    punto_reorden INT,
    ubicacion VARCHAR(100),
    disponibilidad BOOLEAN,
    FOREIGN KEY (id_proveedor) REFERENCES proveedores(id_proveedor),
    FOREIGN KEY (id_maquina) REFERENCES maquinas(id_maquina),
    INDEX idx_id_proveedor (id_proveedor),
    INDEX idx_id_maquina (id_maquina)
);

CREATE TABLE cotizaciones_clientes (
    id_cotizacion INT PRIMARY KEY AUTO_INCREMENT,
    id_cliente INT,
    id_producto INT,
    cantidad_solicitada INT,
    fecha_cotizacion DATE,
    precio_unitario DECIMAL(10,2),
    moneda VARCHAR(20),
    condiciones_pago TEXT,
    validez_cotizacion DATE,
    estatus VARCHAR(50),
    FOREIGN KEY (id_cliente) REFERENCES clientes(id_cliente),
    FOREIGN KEY (id_producto) REFERENCES productos(id_producto),
    INDEX idx_id_cliente (id_cliente),
    INDEX idx_id_producto (id_producto)
);

CREATE TABLE cotizaciones_proveedores (
    id_cotizacion INT PRIMARY KEY AUTO_INCREMENT,
    id_proveedor INT,
    id_material INT,
    cantidad_solicitada DECIMAL(10,2),
    fecha_cotizacion DATE,
    precio_unitario DECIMAL(10,2),
    moneda VARCHAR(20),
    condiciones_pago TEXT,
    validez_cotizacion DATE,
    estatus VARCHAR(50),
    FOREIGN KEY (id_proveedor) REFERENCES proveedores(id_proveedor),
    FOREIGN KEY (id_material) REFERENCES materiales(id_material),
    INDEX idx_id_proveedor (id_proveedor),
    INDEX idx_id_material (id_material)
);

CREATE TABLE cotizaciones_refacciones (
    id_cotizacion INT PRIMARY KEY AUTO_INCREMENT,
    id_proveedor INT,
    id_refaccion INT,
    cantidad_solicitada INT,
    fecha_cotizacion DATE,
    precio_unitario DECIMAL(10,2),
    moneda VARCHAR(20),
    condiciones_pago TEXT,
    validez_cotizacion DATE,
    estatus VARCHAR(50),
    FOREIGN KEY (id_proveedor) REFERENCES proveedores(id_proveedor),
    FOREIGN KEY (id_refaccion) REFERENCES refacciones_maquinas(id_refaccion),
    INDEX idx_id_proveedor (id_proveedor),
    INDEX idx_id_refaccion (id_refaccion)
);

CREATE TABLE cotizaciones_productos (
    id_cotizacion INT PRIMARY KEY AUTO_INCREMENT,
    id_proveedor INT,
    id_producto INT,
    cantidad_solicitada INT,
    fecha_cotizacion DATE,
    precio_unitario DECIMAL(10,2),
    moneda VARCHAR(20),
    condiciones_pago TEXT,
    validez_cotizacion DATE,
    estatus VARCHAR(50),
    FOREIGN KEY (id_proveedor) REFERENCES proveedores(id_proveedor),
    FOREIGN KEY (id_producto) REFERENCES productos(id_producto),
    INDEX idx_id_proveedor (id_proveedor),
    INDEX idx_id_producto (id_producto)
);

CREATE TABLE cotizaciones_servicios (
    id_cotizacion INT PRIMARY KEY AUTO_INCREMENT,
    id_cliente INT,
    descripcion_servicio VARCHAR(150),
    fecha_cotizacion DATE,
    precio_unitario DECIMAL(10,2),
    moneda VARCHAR(20),
    condiciones_pago TEXT,
    validez_cotizacion DATE,
    estatus VARCHAR(50),
    FOREIGN KEY (id_cliente) REFERENCES clientes(id_cliente),
    INDEX idx_id_cliente (id_cliente)
);

CREATE TABLE ordenes_servicio (
    id_orden_servicio INT PRIMARY KEY AUTO_INCREMENT,
    id_cliente INT,
    descripcion_servicio VARCHAR(150),
    fecha_orden DATE,
    fecha_entrega_estimada DATE,
    precio_unitario DECIMAL(10,2),
    moneda VARCHAR(20),
    condiciones_pago TEXT,
    estatus VARCHAR(50),
    FOREIGN KEY (id_cliente) REFERENCES clientes(id_cliente),
    INDEX idx_id_cliente (id_cliente)
);

CREATE TABLE ordenes_venta (
    id_orden_venta INT PRIMARY KEY AUTO_INCREMENT,
    id_cliente INT,
    id_producto INT,
    cantidad_solicitada INT,
    fecha_orden DATE,
    fecha_entrega_estimada DATE,
    precio_unitario DECIMAL(10,2),
    moneda VARCHAR(20),
    estatus VARCHAR(50),
    FOREIGN KEY (id_cliente) REFERENCES clientes(id_cliente),
    FOREIGN KEY (id_producto) REFERENCES productos(id_producto),
    INDEX idx_id_cliente (id_cliente),
    INDEX idx_id_producto (id_producto)
);

CREATE TABLE ordenes_compra (
    id_orden_compra INT PRIMARY KEY AUTO_INCREMENT,
    id_proveedor INT,
    id_material INT,
    cantidad_solicitada DECIMAL(10,2),
    fecha_orden DATE,
    fecha_entrega_estimada DATE,
    precio_unitario DECIMAL(10,2),
    moneda VARCHAR(20),
    estatus VARCHAR(50),
    FOREIGN KEY (id_proveedor) REFERENCES proveedores(id_proveedor),
    FOREIGN KEY (id_material) REFERENCES materiales(id_material),
    INDEX idx_id_proveedor (id_proveedor),
    INDEX idx_id_material (id_material)
);

CREATE TABLE ordenes_compra_refacciones (
    id_orden_compra INT PRIMARY KEY AUTO_INCREMENT,
    id_refaccion INT,
    id_proveedor INT,
    cantidad_solicitada INT,
    fecha_orden DATE,
    fecha_entrega_estimada DATE,
    precio_unitario DECIMAL(10,2),
    moneda VARCHAR(20),
    estatus VARCHAR(50),
    FOREIGN KEY (id_refaccion) REFERENCES refacciones_maquinas(id_refaccion),
    FOREIGN KEY (id_proveedor) REFERENCES proveedores(id_proveedor),
    INDEX idx_id_refaccion (id_refaccion),
    INDEX idx_id_proveedor (id_proveedor)
);

CREATE TABLE ordenes_compra_materiales (
    id_orden_compra INT PRIMARY KEY AUTO_INCREMENT,
    id_material INT,
    id_proveedor INT,
    cantidad_solicitada DECIMAL(10,2),
    fecha_orden DATE,
    fecha_entrega_estimada DATE,
    precio_unitario DECIMAL(10,2),
    moneda VARCHAR(20),
    estatus VARCHAR(50),
    FOREIGN KEY (id_material) REFERENCES materiales(id_material),
    FOREIGN KEY (id_proveedor) REFERENCES proveedores(id_proveedor),
    INDEX idx_id_material (id_material),
    INDEX idx_id_proveedor (id_proveedor)
);

CREATE TABLE ordenes_compra_productos (
    id_orden_compra INT PRIMARY KEY AUTO_INCREMENT,
    id_producto INT,
    id_cliente INT,
    cantidad_solicitada INT,
    fecha_orden DATE,
    fecha_entrega_estimada DATE,
    precio_unitario DECIMAL(10,2),
    moneda VARCHAR(20),
    estatus VARCHAR(50),
    FOREIGN KEY (id_producto) REFERENCES productos(id_producto),
    FOREIGN KEY (id_cliente) REFERENCES clientes(id_cliente),
    INDEX idx_id_producto (id_producto),
    INDEX idx_id_cliente (id_cliente)
);

-- Calidad, mantenimiento y operaciones
CREATE TABLE calibraciones_maquinas (
    id_calibracion INT PRIMARY KEY AUTO_INCREMENT,
    id_maquina INT,
    fecha_calibracion DATE,
    tecnico_calibracion VARCHAR(20),
    resultado VARCHAR(50),
    acciones_correctivas TEXT,
    estatus VARCHAR(50),
    FOREIGN KEY (id_maquina) REFERENCES maquinas(id_maquina),
    INDEX idx_id_maquina (id_maquina)
);

CREATE TABLE mantenimientos_maquinas (
    id_mantenimiento INT PRIMARY KEY AUTO_INCREMENT,
    id_maquina INT,
    fecha_mantenimiento DATE,
    tipo_mantenimiento VARCHAR(100),
    tecnico_responsable VARCHAR(20),
    horas_paro DECIMAL(10,2),
    resultado VARCHAR(50),
    FOREIGN KEY (id_maquina) REFERENCES maquinas(id_maquina),
    INDEX idx_id_maquina (id_maquina)
);

CREATE TABLE mantenimientos_moldes (
    id_mantenimiento_molde VARCHAR(10) PRIMARY KEY,
    id_molde INT,
    fecha DATE,
    tipo_mantenimiento VARCHAR(100),
    descripcion TEXT,
    tecnico_responsable VARCHAR(20),
    horas_paro DECIMAL(10,2),
    resultado VARCHAR(100),
    FOREIGN KEY (id_molde) REFERENCES moldes(id_molde),
    INDEX idx_id_molde (id_molde)
);

CREATE TABLE inspecciones_calidad (
    id_inspeccion VARCHAR(10) PRIMARY KEY,
    id_orden INT,
    id_producto INT,
    fecha_inspeccion DATE,
    muestreo_piezas INT,
    piezas_aprobadas INT,
    piezas_rechazadas INT,
    inspector VARCHAR(20),
    resultado VARCHAR(50),
    FOREIGN KEY (id_orden) REFERENCES ordenes_cabecera(id_orden_cabe),
    FOREIGN KEY (id_producto) REFERENCES productos(id_producto),
    INDEX idx_id_orden (id_orden),
    INDEX idx_id_producto (id_producto)
);

CREATE TABLE energia_consumo (
    id_consumo_energia VARCHAR(10) PRIMARY KEY,
    id_maquina INT,
    fecha DATE,
    kwh_consumidos DECIMAL(10,2),
    horas_operacion DECIMAL(10,2),
    costo_total_mxn DECIMAL(10,2),
    supervisor VARCHAR(20),
    FOREIGN KEY (id_maquina) REFERENCES maquinas(id_maquina),
    INDEX idx_id_maquina (id_maquina)
);

CREATE TABLE scrap_reciclado (
    id_scrap VARCHAR(10) PRIMARY KEY,
    id_orden INT,
    fecha DATE,
    tipo_scrap VARCHAR(100),
    cantidad_kg DECIMAL(10,2),
    destino_reciclado VARCHAR(100),
    responsable VARCHAR(20),
    FOREIGN KEY (id_orden) REFERENCES ordenes_cabecera(id_orden_cabe),
    INDEX idx_id_orden (id_orden)
);

-- Capacitación, devoluciones, parámetros
CREATE TABLE capacitaciones_empleados (
    id_capacitacion INT PRIMARY KEY AUTO_INCREMENT,
    id_empleado INT,
    tema VARCHAR(150),
    fecha DATE,
    duracion_horas DECIMAL(10,2),
    capacitador VARCHAR(20),
    estatus VARCHAR(50),
    FOREIGN KEY (id_empleado) REFERENCES empleados(id_empleado),
    INDEX idx_id_empleado (id_empleado)
);

CREATE TABLE devoluciones_clientes (
    id_devolucion INT PRIMARY KEY AUTO_INCREMENT,
    id_cliente INT,
    id_producto INT,
    cantidad_devuelta INT,
    fecha_devolucion DATE,
    motivo_devolucion TEXT,
    estatus VARCHAR(50),
    FOREIGN KEY (id_cliente) REFERENCES clientes(id_cliente),
    FOREIGN KEY (id_producto) REFERENCES productos(id_producto),
    INDEX idx_id_cliente (id_cliente),
    INDEX idx_id_producto (id_producto)
);

CREATE TABLE devoluciones_proveedores (
    id_devolucion INT PRIMARY KEY AUTO_INCREMENT,
    id_proveedor INT,
    id_material INT,
    cantidad_devuelta DECIMAL(10,2),
    fecha_devolucion DATE,
    motivo_devolucion TEXT,
    estatus VARCHAR(50),
    FOREIGN KEY (id_proveedor) REFERENCES proveedores(id_proveedor),
    FOREIGN KEY (id_material) REFERENCES materiales(id_material),
    INDEX idx_id_proveedor (id_proveedor),
    INDEX idx_id_material (id_material)
);

CREATE TABLE parametros_calidad (
    id_parametro INT PRIMARY KEY AUTO_INCREMENT,
    id_producto INT,
    parametro VARCHAR(150),
    valor_minimo DECIMAL(10,2),
    valor_maximo DECIMAL(10,2),
    unidad VARCHAR(30),
    fecha_actualizacion DATE,
    estatus VARCHAR(50),
    FOREIGN KEY (id_producto) REFERENCES productos(id_producto),
    INDEX idx_id_producto (id_producto)
);

CREATE TABLE parametros_procesos (
    id_parametro INT PRIMARY KEY AUTO_INCREMENT,
    id_producto INT,
    parametro VARCHAR(150),
    valor_minimo DECIMAL(10,2),
    valor_maximo DECIMAL(10,2),
    unidad VARCHAR(30),
    fecha_actualizacion DATE,
    estatus VARCHAR(50),
    FOREIGN KEY (id_producto) REFERENCES productos(id_producto),
    INDEX idx_id_producto (id_producto)
);

-- Logística y embarques
CREATE TABLE ordenes_embarque (
    id_orden_embarque INT PRIMARY KEY AUTO_INCREMENT,
    id_venta INT,
    fecha_embarque DATE,
    transportista VARCHAR(150),
    placas_unidad VARCHAR(20),
    destino VARCHAR(120),
    estatus VARCHAR(50),
    fecha_entrega_estimada DATE,
    FOREIGN KEY (id_venta) REFERENCES ventas(id_venta),
    INDEX idx_id_venta (id_venta)
);

-- Producción – incidencias, control de pesados, liberación, checklist
CREATE TABLE incidencias_produccion (
    id_incidencia INT PRIMARY KEY AUTO_INCREMENT,
    id_orden_cabe INT,
    fecha DATE,
    descripcion TEXT,
    impacto VARCHAR(50),
    acciones_correctivas TEXT,
    estatus VARCHAR(50),
    FOREIGN KEY (id_orden_cabe) REFERENCES ordenes_cabecera(id_orden_cabe),
    INDEX idx_id_orden_cabe (id_orden_cabe)
);

CREATE TABLE control_pesados_materiales (
    id_control INT PRIMARY KEY AUTO_INCREMENT,
    id_material INT,
    fecha DATE,
    peso_entrada_kg DECIMAL(10,2),
    peso_salida_kg DECIMAL(10,2),
    diferencia_kg DECIMAL(10,2),
    operador VARCHAR(20),
    estatus VARCHAR(50),
    FOREIGN KEY (id_material) REFERENCES materiales(id_material),
    INDEX idx_id_material (id_material)
);

CREATE TABLE liberacion_produccion (
    id_liberacion INT PRIMARY KEY AUTO_INCREMENT,
    id_orden_cabe INT,
    fecha DATE,
    responsable VARCHAR(20),
    resultado VARCHAR(50),
    acciones_correctivas TEXT,
    estatus VARCHAR(50),
    FOREIGN KEY (id_orden_cabe) REFERENCES ordenes_cabecera(id_orden_cabe),
    INDEX idx_id_orden_cabe (id_orden_cabe)
);

CREATE TABLE checklist_arraque_maquina (
    id_checklist INT PRIMARY KEY AUTO_INCREMENT,
    id_maquina INT,
    fecha DATE,
    operador VARCHAR(20),
    items_revisados TEXT,
    resultado VARCHAR(50),
    acciones_correctivas TEXT,
    estatus VARCHAR(50),
    FOREIGN KEY (id_maquina) REFERENCES maquinas(id_maquina),
    INDEX idx_id_maquina (id_maquina)
);

CREATE TABLE checklist_cierre_maquina (
    id_checklist INT PRIMARY KEY AUTO_INCREMENT,
    id_maquina INT,
    fecha DATE,
    operador VARCHAR(20),
    items_revisados TEXT,
    resultado VARCHAR(50),
    acciones_correctivas TEXT,
    estatus VARCHAR(50),
    FOREIGN KEY (id_maquina) REFERENCES maquinas(id_maquina),
    INDEX idx_id_maquina (id_maquina)
);

CREATE TABLE checklist_mantenimiento (
    id_checklist INT PRIMARY KEY AUTO_INCREMENT,
    id_maquina INT,
    fecha DATE,
    tecnico_responsable VARCHAR(20),
    items_revisados TEXT,
    resultado VARCHAR(50),
    acciones_correctivas TEXT,
    estatus VARCHAR(50),
    FOREIGN KEY (id_maquina) REFERENCES maquinas(id_maquina),
    INDEX idx_id_maquina (id_maquina)
);

CREATE TABLE checklist_calibracion (
    id_checklist INT PRIMARY KEY AUTO_INCREMENT,
    id_maquina INT,
    fecha DATE,
    tecnico_calibracion VARCHAR(20),
    items_revisados TEXT,
    resultado VARCHAR(50),
    acciones_correctivas TEXT,
    estatus VARCHAR(50),
    FOREIGN KEY (id_maquina) REFERENCES maquinas(id_maquina),
    INDEX idx_id_maquina (id_maquina)
);

-- Consumos por orden
CREATE TABLE consumo_material_por_orden (
    id_consumo INT PRIMARY KEY AUTO_INCREMENT,
    id_orden_cabe INT,
    id_material INT,
    cantidad_consumida DECIMAL(10,2),
    fecha DATE,
    operador VARCHAR(20),
    estatus VARCHAR(50),
    FOREIGN KEY (id_orden_cabe) REFERENCES ordenes_cabecera(id_orden_cabe),
    FOREIGN KEY (id_material) REFERENCES materiales(id_material),
    INDEX idx_id_orden_cabe (id_orden_cabe),
    INDEX idx_id_material (id_material)
);

CREATE TABLE consumo_energia_por_orden (
    id_consumo INT PRIMARY KEY AUTO_INCREMENT,
    id_orden_cabe INT,
    id_maquina INT,
    kwh_consumidos DECIMAL(10,2),
    fecha DATE,
    operador VARCHAR(20),
    estatus VARCHAR(50),
    FOREIGN KEY (id_orden_cabe) REFERENCES ordenes_cabecera(id_orden_cabe),
    FOREIGN KEY (id_maquina) REFERENCES maquinas(id_maquina),
    INDEX idx_id_orden_cabe (id_orden_cabe),
    INDEX idx_id_maquina (id_maquina)
);

CREATE TABLE consumo_agua_por_orden (
    id_consumo INT PRIMARY KEY AUTO_INCREMENT,
    id_orden_cabe INT,
    cantidad_agua_consumida DECIMAL(10,2),
    fecha DATE,
    operador VARCHAR(20),
    estatus VARCHAR(50),
    FOREIGN KEY (id_orden_cabe) REFERENCES ordenes_cabecera(id_orden_cabe),
    INDEX idx_id_orden_cabe (id_orden_cabe)
);

-- OEE e indicadores
CREATE TABLE indicadores_oee (
    id_oee VARCHAR(10) PRIMARY KEY,
    id_maquina INT,
    fecha DATE,
    disponibilidad_percent DECIMAL(10,2),
    rendimiento_percent DECIMAL(10,2),
    calidad_percent DECIMAL(10,2),
    oee_percent DECIMAL(10,2),
    FOREIGN KEY (id_maquina) REFERENCES maquinas(id_maquina),
    INDEX idx_id_maquina (id_maquina)
);

CREATE TABLE pruebas_laboratorio (
    id_prueba INT PRIMARY KEY AUTO_INCREMENT,
    id_producto INT,
    fecha_prueba DATE,
    tipo_prueba VARCHAR(100),
    resultado VARCHAR(50),
    tecnico_responsable VARCHAR(20),
    estatus VARCHAR(50),
    FOREIGN KEY (id_producto) REFERENCES productos(id_producto),
    INDEX idx_id_producto (id_producto)
);

-- Almacenes, ubicaciones e inventarios
CREATE TABLE ubicacion_rack (
    id_ubicacion INT PRIMARY KEY AUTO_INCREMENT,
    id_material INT,
    id_refaccion INT,
    id_producto INT,
    ubicacion VARCHAR(100),
    cantidad_actual DECIMAL(10,2),
    capacidad_maxima DECIMAL(10,2),
    estatus VARCHAR(50),
    FOREIGN KEY (id_material) REFERENCES materiales(id_material),
    FOREIGN KEY (id_refaccion) REFERENCES refacciones_maquinas(id_refaccion),
    FOREIGN KEY (id_producto) REFERENCES productos(id_producto),
    INDEX idx_id_material (id_material),
    INDEX idx_id_refaccion (id_refaccion),
    INDEX idx_id_producto (id_producto)
);

CREATE TABLE historial_ubicacion (
    id_historial INT PRIMARY KEY AUTO_INCREMENT,
    id_ubicacion INT,
    fecha DATE,
    cantidad DECIMAL(10,2),
    movimiento VARCHAR(50),
    operador VARCHAR(20),
    estatus VARCHAR(50),
    FOREIGN KEY (id_ubicacion) REFERENCES ubicacion_rack(id_ubicacion),
    INDEX idx_id_ubicacion (id_ubicacion)
);

CREATE TABLE historial_cambios_recetas (
    id_historial INT PRIMARY KEY AUTO_INCREMENT,
    id_receta_cabe INT,
    fecha DATE,
    cambio_descripcion TEXT,
    tecnico_responsable VARCHAR(20),
    estatus VARCHAR(50),
    FOREIGN KEY (id_receta_cabe) REFERENCES recetas_cabecera(id_receta_cabe),
    INDEX idx_id_receta_cabe (id_receta_cabe)
);

CREATE TABLE rechazos_calidad (
    id_rechazo INT PRIMARY KEY AUTO_INCREMENT,
    id_producto INT,
    fecha DATE,
    cantidad_rechazada INT,
    motivo_rechazo TEXT,
    inspector VARCHAR(20),
    estatus VARCHAR(50),
    FOREIGN KEY (id_producto) REFERENCES productos(id_producto),
    INDEX idx_id_producto (id_producto)
);

-- Planeación, seguimiento, alertas, kardex
CREATE TABLE planeacion_produccion (
    id_planeacion INT PRIMARY KEY AUTO_INCREMENT,
    id_producto INT,
    cantidad_planificada INT,
    fecha_inicio DATE,
    fecha_fin DATE,
    estatus VARCHAR(50),
    FOREIGN KEY (id_producto) REFERENCES productos(id_producto),
    INDEX idx_id_producto (id_producto)
);

CREATE TABLE seguimiento_ordenes (
    id_seguimiento INT PRIMARY KEY AUTO_INCREMENT,
    id_orden_cabe INT,
    fecha DATE,
    estatus VARCHAR(50),
    comentarios TEXT,
    FOREIGN KEY (id_orden_cabe) REFERENCES ordenes_cabecera(id_orden_cabe),
    INDEX idx_id_orden_cabe (id_orden_cabe)
);

CREATE TABLE alerta_stock_materiales (
    id_alerta INT PRIMARY KEY AUTO_INCREMENT,
    id_material INT,
    fecha DATE,
    stock_actual DECIMAL(10,2),
    punto_reorden DECIMAL(10,2),
    estatus VARCHAR(50),
    FOREIGN KEY (id_material) REFERENCES materiales(id_material),
    INDEX idx_id_material (id_material)
);

CREATE TABLE kardex_materiales (
    id_kardex INT PRIMARY KEY AUTO_INCREMENT,
    id_material INT,
    fecha DATE,
    movimiento VARCHAR(50),
    cantidad DECIMAL(10,2),
    stock_final DECIMAL(10,2),
    operador VARCHAR(20),
    estatus VARCHAR(50),
    FOREIGN KEY (id_material) REFERENCES materiales(id_material),
    INDEX idx_id_material (id_material)
);

-- Controles de proceso
CREATE TABLE control_temperatura (
    id_control INT PRIMARY KEY AUTO_INCREMENT,
    id_maquina INT,
    fecha DATE,
    temperatura_inyeccion_C INT,
    temperatura_fundicion_C INT,
    operador VARCHAR(20),
    estatus VARCHAR(50),
    FOREIGN KEY (id_maquina) REFERENCES maquinas(id_maquina),
    INDEX idx_id_maquina (id_maquina)
);

CREATE TABLE control_presion (
    id_control INT PRIMARY KEY AUTO_INCREMENT,
    id_maquina INT,
    fecha DATE,
    presion_inyeccion_bar INT,
    presion_sostenimiento_bar INT,
    operador VARCHAR(20),
    estatus VARCHAR(50),
    FOREIGN KEY (id_maquina) REFERENCES maquinas(id_maquina),
    INDEX idx_id_maquina (id_maquina)
);

CREATE TABLE control_tiempo (
    id_control INT PRIMARY KEY AUTO_INCREMENT,
    id_maquina INT,
    fecha DATE,
    tiempo_ciclo_s INT,
    tiempo_enfriamiento_s INT,
    operador VARCHAR(20),
    estatus VARCHAR(50),
    FOREIGN KEY (id_maquina) REFERENCES maquinas(id_maquina),
    INDEX idx_id_maquina (id_maquina)
);

-- Auditorías de inventario
CREATE TABLE auditoria_inventarios (
    id_auditoria INT PRIMARY KEY AUTO_INCREMENT,
    id_material INT,
    id_refaccion INT,
    id_producto INT,
    fecha DATE,
    auditor VARCHAR(20),
    hallazgos TEXT,
    acciones_correctivas TEXT,
    estatus VARCHAR(50),
    FOREIGN KEY (id_material) REFERENCES materiales(id_material),
    FOREIGN KEY (id_refaccion) REFERENCES refacciones_maquinas(id_refaccion),
    FOREIGN KEY (id_producto) REFERENCES productos(id_producto),
    INDEX idx_id_material (id_material),
    INDEX idx_id_refaccion (id_refaccion),
    INDEX idx_id_producto (id_producto)
);

CREATE TABLE historial_cambios_inventario (
    id_historial INT PRIMARY KEY AUTO_INCREMENT,
    id_material INT,
    id_refaccion INT,
    id_producto INT,
    fecha DATE,
    cambio_descripcion TEXT,
    operador VARCHAR(20),
    estatus VARCHAR(50),
    FOREIGN KEY (id_material) REFERENCES materiales(id_material),
    FOREIGN KEY (id_refaccion) REFERENCES refacciones_maquinas(id_refaccion),
    FOREIGN KEY (id_producto) REFERENCES productos(id_producto),
    INDEX idx_id_material (id_material),
    INDEX idx_id_refaccion (id_refaccion),
    INDEX idx_id_producto (id_producto)
);

CREATE TABLE historial_estatus_ordenes (
    id_historial INT PRIMARY KEY AUTO_INCREMENT,
    id_orden_cabe INT,
    fecha DATE,
    estatus VARCHAR(50),
    comentarios TEXT,
    FOREIGN KEY (id_orden_cabe) REFERENCES ordenes_cabecera(id_orden_cabe),
    INDEX idx_id_orden_cabe (id_orden_cabe)
);

CREATE TABLE indicadores_scrap (
    id_indicador VARCHAR(10) PRIMARY KEY,
    id_producto INT,
    fecha DATE,
    cantidad_scrap INT,
    porcentaje_scrap DECIMAL(10,2),
    estatus VARCHAR(50),
    FOREIGN KEY (id_producto) REFERENCES productos(id_producto),
    INDEX idx_id_producto (id_producto)
);

-- Eficiencias
CREATE TABLE eficiencia_operativa (
    id_eficiencia VARCHAR(10) PRIMARY KEY,
    id_maquina INT,
    fecha DATE,
    disponibilidad_percent DECIMAL(10,2),
    rendimiento_percent DECIMAL(10,2),
    calidad_percent DECIMAL(10,2),
    oee_percent DECIMAL(10,2),
    estatus VARCHAR(50),
    FOREIGN KEY (id_maquina) REFERENCES maquinas(id_maquina),
    INDEX idx_id_maquina (id_maquina)
);

CREATE TABLE eficiencia_operadores (
    id_eficiencia VARCHAR(10) PRIMARY KEY,
    id_empleado INT,
    fecha DATE,
    eficiencia_percent DECIMAL(10,2),
    estatus VARCHAR(50),
    FOREIGN KEY (id_empleado) REFERENCES empleados(id_empleado),
    INDEX idx_id_empleado (id_empleado)
);

-- Solicitudes y surtido de materiales
CREATE TABLE solicitud_material (
    id_solicitud INT PRIMARY KEY AUTO_INCREMENT,
    id_empleado INT,
    id_material INT,
    cantidad_solicitada DECIMAL(10,2),
    fecha_solicitud DATE,
    motivo TEXT,
    estatus VARCHAR(50),
    FOREIGN KEY (id_empleado) REFERENCES empleados(id_empleado),
    FOREIGN KEY (id_material) REFERENCES materiales(id_material),
    INDEX idx_id_empleado (id_empleado),
    INDEX idx_id_material (id_material)
);

CREATE TABLE surtido_materiales (
    id_surtido INT PRIMARY KEY AUTO_INCREMENT,
    id_solicitud INT,
    id_material INT,
    cantidad_surtida DECIMAL(10,2),
    fecha_surtido DATE,
    operador VARCHAR(20),
    estatus VARCHAR(50),
    FOREIGN KEY (id_solicitud) REFERENCES solicitud_material(id_solicitud),
    FOREIGN KEY (id_material) REFERENCES materiales(id_material),
    INDEX idx_id_solicitud (id_solicitud),
    INDEX idx_id_material (id_material)
);

-- Evaluaciones
CREATE TABLE evaluacion_proveedores (
    id_evaluacion INT PRIMARY KEY AUTO_INCREMENT,
    id_proveedor INT,
    fecha DATE,
    calidad_servicio VARCHAR(50),
    tiempo_entrega VARCHAR(50),
    precio VARCHAR(50),
    comentarios TEXT,
    estatus VARCHAR(50),
    FOREIGN KEY (id_proveedor) REFERENCES proveedores(id_proveedor),
    INDEX idx_id_proveedor (id_proveedor)
);

CREATE TABLE evaluacion_clientes (
    id_evaluacion INT PRIMARY KEY AUTO_INCREMENT,
    id_cliente INT,
    fecha DATE,
    calidad_producto VARCHAR(50),
    tiempo_entrega VARCHAR(50),
    precio VARCHAR(50),
    comentarios TEXT,
    estatus VARCHAR(50),
    FOREIGN KEY (id_cliente) REFERENCES clientes(id_cliente),
    INDEX idx_id_cliente (id_cliente)
);

CREATE TABLE evaluacion_empleados (
    id_evaluacion INT PRIMARY KEY AUTO_INCREMENT,
    id_empleado INT,
    fecha DATE,
    desempeno VARCHAR(50),
    puntualidad VARCHAR(50),
    trabajo_equipo VARCHAR(50),
    comentarios TEXT,
    estatus VARCHAR(50),
    FOREIGN KEY (id_empleado) REFERENCES empleados(id_empleado),
    INDEX idx_id_empleado (id_empleado)
);

CREATE TABLE evaluacion_maquinas (
    id_evaluacion INT PRIMARY KEY AUTO_INCREMENT,
    id_maquina INT,
    fecha DATE,
    desempeno VARCHAR(50),
    mantenimiento VARCHAR(50),
    confiabilidad VARCHAR(50),
    comentarios TEXT,
    estatus VARCHAR(50),
    FOREIGN KEY (id_maquina) REFERENCES maquinas(id_maquina),
    INDEX idx_id_maquina (id_maquina)
);

-- Permisos, vacaciones, ausencias, accesos
CREATE TABLE permisos_empleados (
    id_permiso INT PRIMARY KEY AUTO_INCREMENT,
    id_empleado INT,
    fecha_inicio DATE,
    fecha_fin DATE,
    tipo_permiso VARCHAR(100),
    motivo TEXT,
    estatus VARCHAR(50),
    FOREIGN KEY (id_empleado) REFERENCES empleados(id_empleado),
    INDEX idx_id_empleado (id_empleado)
);

CREATE TABLE vacaciones_empleados (
    id_vacacion INT PRIMARY KEY AUTO_INCREMENT,
    id_empleado INT,
    fecha_inicio DATE,
    fecha_fin DATE,
    motivo TEXT,
    estatus VARCHAR(50),
    FOREIGN KEY (id_empleado) REFERENCES empleados(id_empleado),
    INDEX idx_id_empleado (id_empleado)
);

CREATE TABLE ausencias_empleados (
    id_ausencia INT PRIMARY KEY AUTO_INCREMENT,
    id_empleado INT,
    fecha DATE,
    motivo TEXT,
    estatus VARCHAR(50),
    FOREIGN KEY (id_empleado) REFERENCES empleados(id_empleado),
    INDEX idx_id_empleado (id_empleado)
);

CREATE TABLE accesos_sistema (
    id_acceso INT PRIMARY KEY AUTO_INCREMENT,
    id_empleado INT,
    fecha DATE,
    hora TIME,
    tipo_acceso VARCHAR(50),
    estatus VARCHAR(50),
    FOREIGN KEY (id_empleado) REFERENCES empleados(id_empleado),
    INDEX idx_id_empleado (id_empleado)
);

-- Parámetros de configuración
CREATE TABLE parametros_configuracion (
    id_parametro INT PRIMARY KEY AUTO_INCREMENT,
    nombre_parametro VARCHAR(150),
    valor_parametro VARCHAR(150),
    descripcion TEXT,
    fecha_actualizacion DATE,
    estatus VARCHAR(50)
);

-- Trazabilidad
CREATE TABLE trazabilidad_operadores (
    id_trazabilidad INT PRIMARY KEY AUTO_INCREMENT,
    id_producto INT,
    lote VARCHAR(50),
    fecha_produccion DATE,
    fecha_vencimiento DATE,
    cantidad_producida INT,
    estatus VARCHAR(50),
    FOREIGN KEY (id_producto) REFERENCES productos(id_producto),
    INDEX idx_id_producto (id_producto)
);

CREATE TABLE trazabilidad_maquinas (
    id_trazabilidad INT PRIMARY KEY AUTO_INCREMENT,
    id_producto INT,
    lote VARCHAR(50),
    fecha_produccion DATE,
    fecha_vencimiento DATE,
    cantidad_producida INT,
    estatus VARCHAR(50),
    FOREIGN KEY (id_producto) REFERENCES productos(id_producto),
    INDEX idx_id_producto (id_producto)
);

CREATE TABLE trazabilidad_moldes (
    id_trazabilidad INT PRIMARY KEY AUTO_INCREMENT,
    id_producto INT,
    lote VARCHAR(50),
    fecha_produccion DATE,
    fecha_vencimiento DATE,
    cantidad_producida INT,
    estatus VARCHAR(50),
    FOREIGN KEY (id_producto) REFERENCES productos(id_producto),
    INDEX idx_id_producto (id_producto)
);

CREATE TABLE trazabilidad_materiales (
    id_trazabilidad INT PRIMARY KEY AUTO_INCREMENT,
    id_producto INT,
    lote VARCHAR(50),
    fecha_produccion DATE,
    fecha_vencimiento DATE,
    cantidad_producida INT,
    estatus VARCHAR(50),
    FOREIGN KEY (id_producto) REFERENCES productos(id_producto),
    INDEX idx_id_producto (id_producto)
);

CREATE TABLE trazabilidad_calidad (
    id_trazabilidad INT PRIMARY KEY AUTO_INCREMENT,
    id_producto INT,
    lote VARCHAR(50),
    fecha_produccion DATE,
    fecha_vencimiento DATE,
    cantidad_producida INT,
    estatus VARCHAR(50),
    FOREIGN KEY (id_producto) REFERENCES productos(id_producto),
    INDEX idx_id_producto (id_producto)
);

CREATE TABLE trazabilidad_embarque (
    id_trazabilidad INT PRIMARY KEY AUTO_INCREMENT,
    id_producto INT,
    lote VARCHAR(50),
    fecha_produccion DATE,
    fecha_vencimiento DATE,
    cantidad_producida INT,
    estatus VARCHAR(50),
    FOREIGN KEY (id_producto) REFERENCES productos(id_producto),
    INDEX idx_id_producto (id_producto)
);

CREATE TABLE trazabilidad_venta (
    id_trazabilidad INT PRIMARY KEY AUTO_INCREMENT,
    id_producto INT,
    lote VARCHAR(50),
    fecha_produccion DATE,
    fecha_vencimiento DATE,
    cantidad_producida INT,
    estatus VARCHAR(50),
    FOREIGN KEY (id_producto) REFERENCES productos(id_producto),
    INDEX idx_id_producto (id_producto)
);

CREATE TABLE trazabilidad_devolucion (
    id_trazabilidad INT PRIMARY KEY AUTO_INCREMENT,
    id_producto INT,
    lote VARCHAR(50),
    fecha_produccion DATE,
    fecha_vencimiento DATE,
    cantidad_producida INT,
    estatus VARCHAR(50),
    FOREIGN KEY (id_producto) REFERENCES productos(id_producto),
    INDEX idx_id_producto (id_producto)
);

-- Bitácoras
CREATE TABLE bitacora_paros (
    id_bitacora INT PRIMARY KEY AUTO_INCREMENT,
    id_maquina INT,
    fecha DATE,
    hora_inicio TIME,
    hora_fin TIME,
    duracion_paro DECIMAL(10,2),
    motivo_paro TEXT,
    operador VARCHAR(20),
    estatus VARCHAR(50),
    FOREIGN KEY (id_maquina) REFERENCES maquinas(id_maquina),
    INDEX idx_id_maquina (id_maquina)
);

CREATE TABLE bitacora_calibraciones (
    id_bitacora INT PRIMARY KEY AUTO_INCREMENT,
    id_maquina INT,
    fecha DATE,
    tecnico_calibracion VARCHAR(20),
    resultado VARCHAR(50),
    acciones_correctivas TEXT,
    estatus VARCHAR(50),
    FOREIGN KEY (id_maquina) REFERENCES maquinas(id_maquina),
    INDEX idx_id_maquina (id_maquina)
);

CREATE TABLE bitacora_mantenimientos (
    id_bitacora INT PRIMARY KEY AUTO_INCREMENT,
    id_maquina INT,
    fecha DATE,
    tipo_mantenimiento VARCHAR(100),
    tecnico_responsable VARCHAR(20),
    horas_paro DECIMAL(10,2),
    resultado VARCHAR(50),
    FOREIGN KEY (id_maquina) REFERENCES maquinas(id_maquina),
    INDEX idx_id_maquina (id_maquina)
);

CREATE TABLE bitacora_produccion (
    id_bitacora INT PRIMARY KEY AUTO_INCREMENT,
    id_orden_cabe INT,
    fecha DATE,
    turno VARCHAR(20),
    cantidad_producida INT,
    operador VARCHAR(20),
    estatus VARCHAR(50),
    FOREIGN KEY (id_orden_cabe) REFERENCES ordenes_cabecera(id_orden_cabe),
    INDEX idx_id_orden_cabe (id_orden_cabe)
);

CREATE TABLE bitacora_calidad (
    id_bitacora INT PRIMARY KEY AUTO_INCREMENT,
    id_producto INT,
    fecha DATE,
    tipo_prueba VARCHAR(100),
    resultado VARCHAR(50),
    tecnico_responsable VARCHAR(20),
    estatus VARCHAR(50),
    FOREIGN KEY (id_producto) REFERENCES productos(id_producto),
    INDEX idx_id_producto (id_producto)
);

CREATE TABLE bitacora_embarque (
    id_bitacora INT PRIMARY KEY AUTO_INCREMENT,
    id_venta INT,
    fecha DATE,
    transportista VARCHAR(150),
    placas_unidad VARCHAR(20),
    destino VARCHAR(120),
    estatus VARCHAR(50),
    fecha_entrega_estimada DATE,
    FOREIGN KEY (id_venta) REFERENCES ventas(id_venta),
    INDEX idx_id_venta (id_venta)
);

CREATE TABLE bitacora_ventas (
    id_bitacora INT PRIMARY KEY AUTO_INCREMENT,
    id_cliente INT,
    id_producto INT,
    cantidad_vendida INT,
    fecha_venta DATE,
    precio_unitario DECIMAL(10,2),
    moneda VARCHAR(20),
    condiciones_pago TEXT,
    estatus VARCHAR(50),
    FOREIGN KEY (id_cliente) REFERENCES clientes(id_cliente),
    FOREIGN KEY (id_producto) REFERENCES productos(id_producto),
    INDEX idx_id_cliente (id_cliente),
    INDEX idx_id_producto (id_producto)
);

CREATE TABLE bitacora_devoluciones (
    id_bitacora INT PRIMARY KEY AUTO_INCREMENT,
    id_cliente INT,
    id_producto INT,
    cantidad_devuelta INT,
    fecha_devolucion DATE,
    motivo_devolucion TEXT,
    estatus VARCHAR(50),
    FOREIGN KEY (id_cliente) REFERENCES clientes(id_cliente),
    FOREIGN KEY (id_producto) REFERENCES productos(id_producto),
    INDEX idx_id_cliente (id_cliente),
    INDEX idx_id_producto (id_producto)
);

CREATE TABLE inventario_productos_terminados (
    id_inventario INT PRIMARY KEY AUTO_INCREMENT,
    id_producto INT,
    lote VARCHAR(50),
    fecha_produccion DATE,
    fecha_vencimiento DATE,
    cantidad_disponible INT,
    estatus VARCHAR(50),
    FOREIGN KEY (id_producto) REFERENCES productos(id_producto),
    INDEX idx_id_producto (id_producto)
);

-- Productividad por turno
CREATE TABLE productividad_turnos (
    id_productividad VARCHAR(10) PRIMARY KEY,
    id_turno INT,
    fecha DATE,
    cantidad_producida INT,
    eficiencia_percent DECIMAL(10,2),
    estatus VARCHAR(50),
    FOREIGN KEY (id_turno) REFERENCES turno_produccion(id_turno),
    INDEX idx_id_turno (id_turno)
);