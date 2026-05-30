-- Corrección manual equivalente a la migración PHP 2026_05_28_000003.
-- Ejecutar solo si no se usa `php bin/plasti migrate`.

CREATE TABLE IF NOT EXISTS oportunidades (
    id_oportunidad INT AUTO_INCREMENT PRIMARY KEY,
    id_vendedor INT NOT NULL,
    id_cliente INT DEFAULT NULL,
    titulo VARCHAR(255) NOT NULL,
    valor DECIMAL(12,2) DEFAULT 0,
    etapa VARCHAR(50) NOT NULL DEFAULT 'prospeccion',
    probabilidad INT DEFAULT 0,
    fecha_cierre_estimada DATE DEFAULT NULL,
    notas TEXT DEFAULT NULL,
    activo TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_vendedor (id_vendedor),
    INDEX idx_etapa (etapa),
    INDEX idx_cliente (id_cliente)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
