CREATE TABLE IF NOT EXISTS notificaciones_vendedor (
    id_notificacion INT AUTO_INCREMENT PRIMARY KEY,
    id_vendedor INT NOT NULL,
    tipo VARCHAR(50) NOT NULL COMMENT 'cliente_asignado, comision_calculada, comision_pagada',
    titulo VARCHAR(255) NOT NULL,
    mensaje TEXT,
    id_referencia INT DEFAULT NULL,
    leida TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_vendedor) REFERENCES usuarios(id_usuario) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
