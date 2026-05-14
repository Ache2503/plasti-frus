CREATE TABLE tickets (
    id_ticket INT PRIMARY KEY AUTO_INCREMENT,
    id_venta INT NOT NULL,
    folio_unico VARCHAR(20) NOT NULL UNIQUE,
    fecha_emision DATETIME DEFAULT CURRENT_TIMESTAMP,
    datos_json JSON DEFAULT NULL,
    estatus VARCHAR(20) DEFAULT 'emitido',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_venta) REFERENCES ventas(id_venta),
    INDEX idx_folio (folio_unico),
    INDEX idx_venta (id_venta)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
