CREATE TABLE IF NOT EXISTS notificaciones_operador (
    id_notificacion INT PRIMARY KEY AUTO_INCREMENT,
    id_operador INT NOT NULL,
    tipo VARCHAR(50) DEFAULT 'info',
    titulo VARCHAR(255) NOT NULL,
    mensaje TEXT,
    id_referencia INT DEFAULT NULL,
    leida TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_id_operador (id_operador),
    INDEX idx_leida (leida)
);
