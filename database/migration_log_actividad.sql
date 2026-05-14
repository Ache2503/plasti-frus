CREATE TABLE IF NOT EXISTS log_actividad (
  id_log INT PRIMARY KEY AUTO_INCREMENT,
  id_usuario INT DEFAULT NULL,
  accion VARCHAR(100) NOT NULL,
  entidad VARCHAR(50) NOT NULL,
  id_entidad INT DEFAULT NULL,
  detalle TEXT DEFAULT NULL,
  ip VARCHAR(45) DEFAULT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_entidad (entidad, id_entidad),
  INDEX idx_usuario (id_usuario),
  INDEX idx_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
