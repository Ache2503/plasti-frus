-- Corrección manual equivalente a la migración PHP 2026_05_28_000001.
-- Ejecutar solo si no se usa `php bin/plasti migrate`.

CREATE TABLE IF NOT EXISTS tipos_mantenimiento (
    id_tipo_mantenimiento INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    slug VARCHAR(100) NOT NULL UNIQUE,
    activo TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS motivos_paro (
    id_motivo_paro INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(150) NOT NULL,
    slug VARCHAR(150) NOT NULL UNIQUE,
    activo TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS motivos_rechazo (
    id_motivo_rechazo INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(150) NOT NULL,
    slug VARCHAR(150) NOT NULL UNIQUE,
    activo TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO tipos_mantenimiento (nombre, slug, activo) VALUES
('Preventivo', 'preventivo', 1),
('Correctivo', 'correctivo', 1),
('Predictivo', 'predictivo', 1),
('Calibracion', 'calibracion', 1)
ON DUPLICATE KEY UPDATE nombre = VALUES(nombre), activo = VALUES(activo);

INSERT INTO motivos_paro (nombre, slug, activo) VALUES
('Falla mecanica', 'falla_mecanica', 1),
('Falla electrica', 'falla_electrica', 1),
('Cambio de molde', 'cambio_molde', 1),
('Falta de material', 'falta_material', 1),
('Mantenimiento programado', 'mantenimiento_programado', 1),
('Otro', 'otro', 1)
ON DUPLICATE KEY UPDATE nombre = VALUES(nombre), activo = VALUES(activo);

INSERT INTO motivos_rechazo (nombre, slug, activo) VALUES
('Dimensiones fuera de especificacion', 'dimensiones_fuera_especificacion', 1),
('Defecto visual', 'defecto_visual', 1),
('Contaminacion de material', 'contaminacion_material', 1),
('Color incorrecto', 'color_incorrecto', 1),
('Rechazado en inspeccion', 'rechazado_en_inspeccion', 1),
('Otro', 'otro', 1)
ON DUPLICATE KEY UPDATE nombre = VALUES(nombre), activo = VALUES(activo);

-- Agregar columnas id_* con ALTER TABLE ... ADD COLUMN en caso de no existir.
-- MySQL no soporta IF NOT EXISTS en todas las versiones; la migración PHP valida existencia antes de alterar.
