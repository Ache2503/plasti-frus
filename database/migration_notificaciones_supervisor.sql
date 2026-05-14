CREATE TABLE IF NOT EXISTS notificaciones_supervisor (
    id_notificacion INT AUTO_INCREMENT PRIMARY KEY,
    id_supervisor INT NOT NULL,
    tipo VARCHAR(50) NOT NULL DEFAULT 'general',
    titulo VARCHAR(255) NOT NULL,
    mensaje TEXT,
    id_referencia INT DEFAULT NULL,
    leida TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_supervisor) REFERENCES usuarios(id_usuario) ON DELETE CASCADE
);
