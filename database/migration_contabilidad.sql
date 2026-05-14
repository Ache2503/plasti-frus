CREATE TABLE IF NOT EXISTS plan_cuentas (
    id_cuenta INT AUTO_INCREMENT PRIMARY KEY,
    codigo VARCHAR(20) NOT NULL UNIQUE,
    nombre VARCHAR(200) NOT NULL,
    tipo ENUM('activo','pasivo','capital','ingreso','gasto') NOT NULL,
    nivel INT NOT NULL DEFAULT 1,
    id_padre INT DEFAULT NULL,
    naturaleza ENUM('deudora','acreedora') NOT NULL,
    activo TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (id_padre) REFERENCES plan_cuentas(id_cuenta) ON DELETE SET NULL
);

CREATE TABLE IF NOT EXISTS polizas (
    id_poliza INT AUTO_INCREMENT PRIMARY KEY,
    folio VARCHAR(30) NOT NULL UNIQUE,
    tipo ENUM('ingreso','egreso','diario') NOT NULL DEFAULT 'diario',
    concepto TEXT NOT NULL,
    fecha DATE NOT NULL,
    moneda VARCHAR(10) DEFAULT 'MXN',
    tipo_cambio DECIMAL(10,4) DEFAULT 1.0000,
    estatus ENUM('activo','cancelado') DEFAULT 'activo',
    created_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES usuarios(id_usuario)
);

CREATE TABLE IF NOT EXISTS polizas_detalle (
    id_detalle INT AUTO_INCREMENT PRIMARY KEY,
    id_poliza INT NOT NULL,
    id_cuenta INT NOT NULL,
    concepto VARCHAR(255) DEFAULT NULL,
    cargo DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    abono DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    referencia_tipo VARCHAR(50) DEFAULT NULL,
    referencia_id INT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_poliza) REFERENCES polizas(id_poliza) ON DELETE CASCADE,
    FOREIGN KEY (id_cuenta) REFERENCES plan_cuentas(id_cuenta)
);

CREATE TABLE IF NOT EXISTS periodos_contables (
    id_periodo INT AUTO_INCREMENT PRIMARY KEY,
    mes INT NOT NULL,
    anio INT NOT NULL,
    fecha_inicio DATE NOT NULL,
    fecha_fin DATE NOT NULL,
    cerrado TINYINT(1) DEFAULT 0,
    fecha_cierre DATETIME DEFAULT NULL,
    cerrado_por INT DEFAULT NULL,
    UNIQUE KEY uk_periodo (mes, anio),
    FOREIGN KEY (cerrado_por) REFERENCES usuarios(id_usuario)
);

INSERT IGNORE INTO periodos_contables (mes, anio, fecha_inicio, fecha_fin)
SELECT m, 2026, DATE(CONCAT('2026-', LPAD(m,2,'0'), '-01')), LAST_DAY(DATE(CONCAT('2026-', LPAD(m,2,'0'), '-01')))
FROM (SELECT 1 m UNION SELECT 2 UNION SELECT 3 UNION SELECT 4 UNION SELECT 5 UNION SELECT 6
      UNION SELECT 7 UNION SELECT 8 UNION SELECT 9 UNION SELECT 10 UNION SELECT 11 UNION SELECT 12) meses;

INSERT IGNORE INTO periodos_contables (mes, anio, fecha_inicio, fecha_fin)
SELECT m, 2025, DATE(CONCAT('2025-', LPAD(m,2,'0'), '-01')), LAST_DAY(DATE(CONCAT('2025-', LPAD(m,2,'0'), '-01')))
FROM (SELECT 1 m UNION SELECT 2 UNION SELECT 3 UNION SELECT 4 UNION SELECT 5 UNION SELECT 6
      UNION SELECT 7 UNION SELECT 8 UNION SELECT 9 UNION SELECT 10 UNION SELECT 11 UNION SELECT 12) meses;

INSERT IGNORE INTO plan_cuentas (codigo, nombre, tipo, nivel, id_padre, naturaleza) VALUES
('1', 'ACTIVO', 'activo', 1, NULL, 'deudora'),
('1.1', 'ACTIVO CIRCULANTE', 'activo', 2, NULL, 'deudora'),
('1.1.1', 'CAJA', 'activo', 3, NULL, 'deudora'),
('1.1.2', 'BANCOS', 'activo', 3, NULL, 'deudora'),
('1.1.3', 'CLIENTES', 'activo', 3, NULL, 'deudora'),
('1.1.4', 'DEUDORES DIVERSOS', 'activo', 3, NULL, 'deudora'),
('1.1.5', 'INVENTARIOS', 'activo', 3, NULL, 'deudora'),
('1.1.5.1', 'MATERIA PRIMA', 'activo', 4, NULL, 'deudora'),
('1.1.5.2', 'PRODUCCION EN PROCESO', 'activo', 4, NULL, 'deudora'),
('1.1.5.3', 'PRODUCTO TERMINADO', 'activo', 4, NULL, 'deudora'),
('1.1.6', 'IVA ACREDITABLE', 'activo', 3, NULL, 'deudora'),
('1.2', 'ACTIVO NO CIRCULANTE', 'activo', 2, NULL, 'deudora'),
('1.2.1', 'MAQUINARIA Y EQUIPO', 'activo', 3, NULL, 'deudora'),
('1.2.2', 'EQUIPO DE COMPUTO', 'activo', 3, NULL, 'deudora'),
('1.2.3', 'MOBILIARIO Y EQUIPO', 'activo', 3, NULL, 'deudora'),
('1.2.4', 'DEPRECIACION ACUMULADA', 'activo', 3, NULL, 'acreedora'),
('2', 'PASIVO', 'pasivo', 1, NULL, 'acreedora'),
('2.1', 'PASIVO A CORTO PLAZO', 'pasivo', 2, NULL, 'acreedora'),
('2.1.1', 'PROVEEDORES', 'pasivo', 3, NULL, 'acreedora'),
('2.1.2', 'ACREEDORES DIVERSOS', 'pasivo', 3, NULL, 'acreedora'),
('2.1.3', 'IVA POR PAGAR', 'pasivo', 3, NULL, 'acreedora'),
('2.1.4', 'ISR POR PAGAR', 'pasivo', 3, NULL, 'acreedora'),
('2.1.5', 'SUELDOS POR PAGAR', 'pasivo', 3, NULL, 'acreedora'),
('2.1.6', 'COMISIONES POR PAGAR', 'pasivo', 3, NULL, 'acreedora'),
('2.2', 'PASIVO A LARGO PLAZO', 'pasivo', 2, NULL, 'acreedora'),
('2.2.1', 'PRESTAMOS BANCARIOS', 'pasivo', 3, NULL, 'acreedora'),
('3', 'CAPITAL CONTABLE', 'capital', 1, NULL, 'acreedora'),
('3.1', 'CAPITAL SOCIAL', 'capital', 2, NULL, 'acreedora'),
('3.2', 'RESULTADO DEL EJERCICIO', 'capital', 2, NULL, 'acreedora'),
('4', 'INGRESOS', 'ingreso', 1, NULL, 'acreedora'),
('4.1', 'VENTAS', 'ingreso', 2, NULL, 'acreedora'),
('4.2', 'DEVOLUCIONES SOBRE VENTAS', 'ingreso', 2, NULL, 'deudora'),
('4.3', 'OTROS INGRESOS', 'ingreso', 2, NULL, 'acreedora'),
('5', 'GASTOS', 'gasto', 1, NULL, 'deudora'),
('5.1', 'COSTO DE VENTAS', 'gasto', 2, NULL, 'deudora'),
('5.2', 'GASTOS DE ADMINISTRACION', 'gasto', 2, NULL, 'deudora'),
('5.2.1', 'SUELDOS Y SALARIOS', 'gasto', 3, NULL, 'deudora'),
('5.2.2', 'RENTA', 'gasto', 3, NULL, 'deudora'),
('5.2.3', 'SERVICIOS PUBLICOS', 'gasto', 3, NULL, 'deudora'),
('5.2.4', 'PAPELERIA Y UTILES', 'gasto', 3, NULL, 'deudora'),
('5.3', 'GASTOS DE VENTA', 'gasto', 2, NULL, 'deudora'),
('5.3.1', 'COMISIONES A VENDEDORES', 'gasto', 3, NULL, 'deudora'),
('5.3.2', 'PUBLICIDAD', 'gasto', 3, NULL, 'deudora'),
('5.4', 'GASTOS DE PRODUCCION', 'gasto', 2, NULL, 'deudora'),
('5.4.1', 'MATERIA PRIMA CONSUMIDA', 'gasto', 3, NULL, 'deudora'),
('5.4.2', 'MANO DE OBRA DIRECTA', 'gasto', 3, NULL, 'deudora'),
('5.4.3', 'ENERGIA ELECTRICA', 'gasto', 3, NULL, 'deudora'),
('5.4.4', 'AGUA', 'gasto', 3, NULL, 'deudora'),
('5.4.5', 'MANTENIMIENTO', 'gasto', 3, NULL, 'deudora'),
('5.5', 'GASTOS FINANCIEROS', 'gasto', 2, NULL, 'deudora'),
('5.5.1', 'INTERESES BANCARIOS', 'gasto', 3, NULL, 'deudora'),
('5.6', 'OTROS GASTOS', 'gasto', 2, NULL, 'deudora'),
('6', 'CUENTAS DE CIERRE', 'gasto', 1, NULL, 'deudora'),
('6.1', 'RESUMEN DE RESULTADOS', 'gasto', 2, NULL, 'deudora');

UPDATE plan_cuentas p
JOIN plan_cuentas padre ON padre.codigo = SUBSTRING_INDEX(p.codigo, '.', LENGTH(p.codigo) - LENGTH(REPLACE(p.codigo, '.', '')))
                       AND padre.nivel = p.nivel - 1
                       AND p.id_padre IS NULL
SET p.id_padre = padre.id_cuenta
WHERE p.nivel > 1;
