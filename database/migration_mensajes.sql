CREATE TABLE IF NOT EXISTS mensajes (
    id_mensaje INT AUTO_INCREMENT PRIMARY KEY,
    de_user_id INT NOT NULL,
    para_user_id INT NOT NULL,
    asunto VARCHAR(255) NOT NULL,
    mensaje TEXT NOT NULL,
    leido TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (de_user_id) REFERENCES usuarios(id_usuario) ON DELETE CASCADE,
    FOREIGN KEY (para_user_id) REFERENCES usuarios(id_usuario) ON DELETE CASCADE,
    INDEX idx_para (para_user_id, leido),
    INDEX idx_de (de_user_id),
    INDEX idx_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
