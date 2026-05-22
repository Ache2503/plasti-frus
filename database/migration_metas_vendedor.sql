CREATE TABLE IF NOT EXISTS metas_vendedor (
    id_meta INT AUTO_INCREMENT PRIMARY KEY,
    id_vendedor INT NOT NULL,
    anio INT NOT NULL,
    mes INT NOT NULL,
    monto_objetivo DECIMAL(12,2) NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (id_vendedor) REFERENCES usuarios(id_usuario) ON DELETE CASCADE,
    UNIQUE KEY uk_vendedor_periodo (id_vendedor, anio, mes),
    INDEX idx_vendedor (id_vendedor)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
