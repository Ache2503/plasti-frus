CREATE TABLE IF NOT EXISTS cierres_contables (
    id INT AUTO_INCREMENT PRIMARY KEY,
    anio INT NOT NULL,
    mes INT NOT NULL,
    tipo ENUM('mensual', 'anual') NOT NULL DEFAULT 'mensual',
    cerrado_por INT NOT NULL,
    fecha_cierre DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    observaciones TEXT DEFAULT NULL,
    UNIQUE KEY uk_cierre (anio, mes, tipo),
    FOREIGN KEY (cerrado_por) REFERENCES usuarios(id_usuario)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
