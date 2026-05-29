ALTER TABLE clientes
    ADD COLUMN IF NOT EXISTS domicilio VARCHAR(255) NULL AFTER estado,
    ADD COLUMN IF NOT EXISTS codigo_postal VARCHAR(10) NULL AFTER domicilio,
    ADD COLUMN IF NOT EXISTS contacto_nombre VARCHAR(150) NULL AFTER codigo_postal,
    ADD COLUMN IF NOT EXISTS contacto_cargo VARCHAR(100) NULL AFTER contacto_nombre,
    ADD COLUMN IF NOT EXISTS contacto_telefono VARCHAR(20) NULL AFTER contacto_cargo,
    ADD COLUMN IF NOT EXISTS contacto_correo VARCHAR(120) NULL AFTER contacto_telefono,
    ADD COLUMN IF NOT EXISTS regimen_fiscal VARCHAR(10) NULL AFTER contacto_correo,
    ADD COLUMN IF NOT EXISTS uso_cfdi VARCHAR(10) NULL AFTER regimen_fiscal,
    ADD COLUMN IF NOT EXISTS correo_fiscal VARCHAR(100) NULL AFTER correo,
    ADD COLUMN IF NOT EXISTS id_vendedor INT NULL AFTER correo_fiscal,
    ADD COLUMN IF NOT EXISTS activo TINYINT(1) NOT NULL DEFAULT 1 AFTER sector;

CREATE TABLE IF NOT EXISTS audit_log (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT UNSIGNED NULL,
    usuario_nombre VARCHAR(100) NOT NULL DEFAULT 'Sistema',
    accion VARCHAR(50) NOT NULL,
    entidad VARCHAR(100) NOT NULL,
    entidad_id VARCHAR(50) NULL,
    detalle TEXT NULL,
    ip VARCHAR(45) NULL,
    user_agent VARCHAR(500) NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_audit_log_accion (accion),
    INDEX idx_audit_log_entidad (entidad),
    INDEX idx_audit_log_created_at (created_at),
    INDEX idx_audit_log_usuario (usuario_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE solicitudes_factura
    ADD COLUMN IF NOT EXISTS id_usuario_procesa INT DEFAULT NULL AFTER estatus,
    ADD COLUMN IF NOT EXISTS fecha_procesada DATETIME DEFAULT NULL AFTER id_usuario_procesa,
    ADD COLUMN IF NOT EXISTS id_factura INT DEFAULT NULL AFTER fecha_procesada;

INSERT INTO cedes (id_cedes, nombre_cede, ubicacion, responsable)
SELECT 1, 'Planta principal', 'Mexico', 'Sistema'
WHERE NOT EXISTS (SELECT 1 FROM cedes WHERE id_cedes = 1);

INSERT INTO moldes (nombre_molde, numero_cavidades, material_molde, vida_util_golpes, ciclos_acumulados, estatus, id_cedes)
SELECT 'Molde demo', 4, 'Acero', 1000000, 0, 'activo', 1
WHERE NOT EXISTS (SELECT 1 FROM moldes);

INSERT INTO maquinas (nombre, modelo, numero_serie, estatus)
SELECT 'Inyectora demo', 'Demo-100', 'DEMO-001', 'operando'
WHERE NOT EXISTS (SELECT 1 FROM maquinas);
