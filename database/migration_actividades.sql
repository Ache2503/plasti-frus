CREATE TABLE IF NOT EXISTS actividades (
    id_actividad INT AUTO_INCREMENT PRIMARY KEY,
    id_vendedor INT NOT NULL,
    titulo VARCHAR(255) NOT NULL,
    descripcion TEXT DEFAULT NULL,
    tipo VARCHAR(50) NOT NULL DEFAULT 'tarea',
    fecha_hora DATETIME NOT NULL,
    estado VARCHAR(20) NOT NULL DEFAULT 'pendiente',
    color VARCHAR(7) DEFAULT '#0d6efd',
    recordatorio TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (id_vendedor) REFERENCES usuarios(id_usuario) ON DELETE CASCADE,
    INDEX idx_vendedor (id_vendedor),
    INDEX idx_fecha (fecha_hora),
    INDEX idx_estado (estado)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
